<?php

// app/Http/Controllers/Admin/Ajax/LookupController.php

namespace App\Http\Controllers\Admin\Ajax;

use App\Http\Controllers\Controller;
use App\Models\BanknoteSeries;
use App\Models\BookCover;
use App\Models\BookSeries;
use App\Models\BookTopic;
use App\Models\Country;
use App\Models\Currency;
use App\Models\ItemCategory;
use App\Models\ItemNationality;
use App\Models\ItemOrganization;
use App\Models\Location;
use App\Models\MagazineSeries;
use App\Models\NewspaperSeries;
use App\Models\NominalValue;
use App\Models\Origin;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LookupController extends Controller
{
    private const MODEL_MAP = [
        'topic'              => BookTopic::class,
        'series'             => BookSeries::class,
        'cover'              => BookCover::class,
        'banknote-series'    => BanknoteSeries::class,
        'location'           => Location::class,
        'origin'             => Origin::class,
        'item-category'      => ItemCategory::class,
        'item-organization'  => ItemOrganization::class,
        'magazine-series'    => MagazineSeries::class,
        'newspaper-series'   => NewspaperSeries::class,
        'country'            => Country::class,
        'currency'           => Currency::class,
        'nominal-value'      => NominalValue::class,
        'item-nationality'   => ItemNationality::class,
    ];

    // Public so the Blade partial can reference it via @json() to avoid duplication
    public const TREE_TYPES = ['topic', 'location', 'origin', 'item-category', 'item-organization', 'magazine-series', 'newspaper-series'];

    public function parents(string $type): JsonResponse
    {
        abort_unless(in_array($type, self::TREE_TYPES), 404);

        $modelClass = self::MODEL_MAP[$type];
        $rows = $modelClass::flatTree();

        return response()->json($rows->values());
    }

    public function store(Request $request, string $type): JsonResponse
    {
        abort_unless(isset(self::MODEL_MAP[$type]), 404);

        $isTree = in_array($type, self::TREE_TYPES);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'parent_id' => $isTree ? $this->parentIdRules($type) : ['prohibited'],
        ]);

        $name     = trim($data['name']);
        $parentId = $isTree ? ($data['parent_id'] ?? null) : null;

        $modelClass = self::MODEL_MAP[$type];

        $attrs = ['name' => $name];
        if ($isTree) {
            $attrs['parent_id'] = $parentId;
        }

        try {
            $row = $modelClass::firstOrCreate($attrs);
        } catch (UniqueConstraintViolationException) {
            $row = $modelClass::where($attrs)->firstOrFail();
        }

        return response()->json([
            'id'   => $row->id,
            'name' => $this->displayName($modelClass, $row),
        ]);
    }

    private function displayName(string $modelClass, object $row): string
    {
        $depth   = 0;
        $current = $modelClass::find($row->id); // Eloquent model so relations work
        $visited = [$row->id];

        while ($current && $current->parent_id) {
            if (in_array($current->parent_id, $visited)) {
                break; // Cycle guard
            }
            $visited[] = $current->parent_id;
            $current   = $current->parent; // Uses eager-loaded BelongsTo — no extra query if already loaded
            $depth++;
        }

        return str_repeat('— ', $depth) . $row->name;
    }

    /**
     * Validation rules for parent_id.
     * Excludes soft-deleted parents for models that use SoftDeletes.
     */
    private function parentIdRules(string $type): array
    {
        $modelClass = self::MODEL_MAP[$type];
        $table      = (new $modelClass)->getTable();

        $existsRule = Rule::exists($table, 'id');

        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($modelClass))) {
            $existsRule->whereNull('deleted_at');
        }

        return ['nullable', 'integer', 'min:1', $existsRule];
    }
}
