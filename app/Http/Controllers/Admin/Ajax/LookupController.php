<?php

// app/Http/Controllers/Admin/Ajax/LookupController.php

namespace App\Http\Controllers\Admin\Ajax;

use App\Http\Controllers\Admin\LookupIndexController;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/**
 * Powers the inline "+" add-lookup modal used across every create/edit form.
 * Reads its type -> table/tree config from LookupIndexController::types()
 * (the same map that drives /admin/lookups/{type}) instead of keeping a
 * second, separately-maintained list — every type with a lookup management
 * page automatically gets inline-add support too, so it can't silently drift
 * out of sync the way the previous hardcoded MODEL_MAP repeatedly did.
 */
class LookupController extends Controller
{
    // Public so the Blade partial can reference it via @json() to avoid duplication
    public static function treeTypes(): array
    {
        return collect(LookupIndexController::types())
            ->filter(fn (array $config) => $config['tree'] ?? false)
            ->keys()
            ->values()
            ->all();
    }

    public function parents(string $type): JsonResponse
    {
        $config = $this->configOrAbort($type);
        abort_unless($config['tree'] ?? false, 404);

        $table = $config['table'];

        $rows = DB::table($table)
            ->select(['id', 'name', 'parent_id'])
            ->when(Schema::hasColumn($table, 'deleted_at'), fn ($q) => $q->whereNull('deleted_at'))
            ->orderBy('name')
            ->get();

        return response()->json($this->flattenTree($rows, null, 0)->values());
    }

    public function store(Request $request, string $type): JsonResponse
    {
        $config = $this->configOrAbort($type);
        $table  = $config['table'];
        $isTree = $config['tree'] ?? false;

        // nominal_values.name is a decimal column (face values like 5.00),
        // unlike every other lookup table's varchar name — validate
        // accordingly so a bad value 422s with a clear message instead of
        // 500ing on the underlying DB type constraint.
        $nameRules = in_array(Schema::getColumnType($table, 'name'), ['decimal', 'float', 'integer'], true)
            ? ['required', 'numeric']
            : ['required', 'string', 'max:255'];

        $data = $request->validate([
            'name'      => $nameRules,
            'parent_id' => $isTree ? $this->parentIdRules($table) : ['prohibited'],
        ]);

        $name     = trim($data['name']);
        $attrs    = ['name' => $name];
        if ($isTree) {
            $attrs['parent_id'] = $data['parent_id'] ?? null;
        }

        $id = $this->findMatchingId($table, $attrs);

        if ($id === null) {
            $payload = $attrs;
            if (Schema::hasColumn($table, 'created_at')) {
                $payload['created_at'] = now();
            }
            if (Schema::hasColumn($table, 'updated_at')) {
                $payload['updated_at'] = now();
            }

            try {
                $id = DB::table($table)->insertGetId($payload);
            } catch (UniqueConstraintViolationException|QueryException) {
                $id = $this->findMatchingId($table, $attrs);
                abort_if($id === null, 500);
            }
        }

        return response()->json([
            'id'   => $id,
            'name' => $this->displayName($table, $id),
        ]);
    }

    private function findMatchingId(string $table, array $attrs): ?int
    {
        $row = DB::table($table)->where($attrs)
            ->when(Schema::hasColumn($table, 'deleted_at'), fn ($q) => $q->whereNull('deleted_at'))
            ->first();

        return $row?->id;
    }

    private function displayName(string $table, int $id): string
    {
        if (! Schema::hasColumn($table, 'parent_id')) {
            return DB::table($table)->where('id', $id)->value('name');
        }

        $depth     = 0;
        $currentId = $id;
        $visited   = [];
        $name      = null;

        while ($currentId !== null) {
            if (isset($visited[$currentId])) {
                break; // cycle guard
            }
            $visited[$currentId] = true;

            $row = DB::table($table)->select('id', 'name', 'parent_id')->where('id', $currentId)->first();
            if (! $row) {
                break;
            }
            if ($name === null) {
                $name = $row->name;
            } else {
                $depth++;
            }
            $currentId = $row->parent_id;
        }

        return str_repeat('— ', $depth).$name;
    }

    /**
     * Validation rules for parent_id. Excludes soft-deleted parents for
     * tables that use soft deletes.
     */
    private function parentIdRules(string $table): array
    {
        $existsRule = Rule::exists($table, 'id');

        if (Schema::hasColumn($table, 'deleted_at')) {
            $existsRule->whereNull('deleted_at');
        }

        return ['nullable', 'integer', 'min:1', $existsRule];
    }

    private function configOrAbort(string $type): array
    {
        $config = LookupIndexController::types()[$type] ?? null;
        abort_unless($config !== null, 404);
        abort_unless(Schema::hasTable($config['table']), 404);

        return $config;
    }

    /**
     * @param  Collection<int, object>  $rows
     */
    private function flattenTree(Collection $rows, ?int $parentId, int $depth): Collection
    {
        return $rows->where('parent_id', $parentId)->flatMap(function ($row) use ($rows, $depth) {
            $item = (object) [
                'id'   => $row->id,
                'name' => str_repeat('— ', $depth).$row->name,
            ];

            return collect([$item])->concat($this->flattenTree($rows, $row->id, $depth + 1));
        });
    }
}
