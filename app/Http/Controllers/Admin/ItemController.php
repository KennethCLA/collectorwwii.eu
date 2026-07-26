<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesInlineMediaUploads;
use App\Http\Controllers\Admin\Concerns\NormalizesForSaleFields;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemNationality;
use App\Models\ItemOrganization;
use App\Models\Origin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    use HandlesInlineMediaUploads;
    use NormalizesForSaleFields;

    public function __construct()
    {
        $this->authorizeResource(Item::class, 'item');
    }

    public function index(Request $request)
    {
        // 1. Base query: haal items op met hun relaties
        $query = Item::with(['category', 'nationality']);

        // 2. Filtering (op basis van request-parameters)
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->has('origin')) {
            $query->where('origin_id', $request->origin);
        }

        if ($request->has('nationality')) {
            $query->where('nationality_id', $request->nationality);
        }

        if ($request->has('organization')) {
            $query->where('organization_id', $request->organization);
        }

        // 3. Zoeken (bijvoorbeeld op de titel)
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%");
        }

        // 4. Sorteerfunctionaliteit
        //    Voorbeeld van enkele opties: titel, aanmaakdatum, enz.
        if ($request->has('sort')) {
            $sort = $request->input('sort');
            switch ($sort) {
                case 'title_asc':
                    $query->orderBy('title', 'asc');
                    break;
                case 'title_desc':
                    $query->orderBy('title', 'desc');
                    break;
                case 'created_at_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'created_at_desc':
                    $query->orderBy('created_at', 'desc');
                    break;
                    // Je kunt hier extra cases toevoegen, bijvoorbeeld sorteren op 'category' of 'origin' etc.
                default:
                    // Geen extra sortering
                    break;
            }
        }

        // 5. Paginatie
        //    Pas het getal (10) aan naar wens. Bijvoorbeeld 20, 50, 100...
        $items = $query->paginate(50);

        // 6. Retourneer de view met de resultaten en de benodigde filters
        return view('admin.items.index', [
            'items' => $items,
            'categories' => ItemCategory::flatTree(),
            'origins' => Origin::flatTree(),
            'nationalities' => ItemNationality::orderBy('name')->get(),
            'organizations' => ItemOrganization::flatTree(),
        ]);
    }

    public function create()
    {
        return view('admin.items.create', [
            'categories' => ItemCategory::flatTree(),
            'origins' => Origin::flatTree(),
            'nationalities' => ItemNationality::orderBy('name')->get(),
            'organizations' => ItemOrganization::flatTree(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:item_categories,id',
            'origin_id' => 'nullable|exists:origins,id',
            'nationality_id' => 'nullable|exists:item_nationalities,id',
            'organization_id' => 'nullable|exists:item_organizations,id',
            'for_sale' => 'nullable|boolean',
            'selling_price' => 'nullable|numeric|min:0',
            'purchase_date' => ['nullable', 'date', 'before_or_equal:today'],
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_location' => 'nullable|string|max:255',
            'storage_location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'condition' => 'nullable|string|max:50',
            'sold_at' => ['nullable', 'date', 'before_or_equal:today', Rule::requiredIf(fn () => $request->filled('sold_price'))],
            'sold_price' => ['nullable', 'numeric', 'min:0'],
            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
            'pdfs' => ['nullable', 'array'],
            'pdfs.*' => ['file', 'mimetypes:application/pdf', 'max:51200'],
            'main_image_index' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated = $this->normalizeForSaleFields($validated, $request);

        $uploadedForCleanup = [];

        try {
            $item = DB::transaction(function () use ($validated, $request, &$uploadedForCleanup) {
                $data = $validated;
                unset($data['images'], $data['pdfs'], $data['main_image_index']);

                /** @var \App\Models\Item $item */
                $item = Item::create($data);

                $this->attachInlineMedia(
                    $item,
                    "items/{$item->id}",
                    $request->file('images', []),
                    $request->file('pdfs', []),
                    (int) $request->input('main_image_index', 0),
                    $uploadedForCleanup,
                );

                return $item;
            });
        } catch (\Throwable $e) {
            foreach ($uploadedForCleanup as [$d, $p]) {
                try {
                    Storage::disk($d)->delete($p);
                } catch (\Throwable $ignore) {
                }
            }
            throw $e;
        }

        return redirect()->route('admin.items.edit', $item)->with('success', 'Item created.');
    }

    public function show(Item $item)
    {
        return view('items.show', compact('item'));
    }

    public function edit(Item $item)
    {
        $item->load(['images', 'files']);

        return view('admin.items.edit', [
            'item' => $item,
            'categories' => ItemCategory::flatTree(),
            'origins' => Origin::flatTree(),
            'nationalities' => ItemNationality::orderBy('name')->get(),
            'organizations' => ItemOrganization::flatTree(),
        ]);
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:item_categories,id',
            'origin_id' => 'nullable|exists:origins,id',
            'nationality_id' => 'nullable|exists:item_nationalities,id',
            'organization_id' => 'nullable|exists:item_organizations,id',
            'for_sale' => 'nullable|boolean',
            'selling_price' => 'nullable|numeric|min:0',
            'purchase_date' => ['nullable', 'date', 'before_or_equal:today'],
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_location' => 'nullable|string|max:255',
            'storage_location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'condition' => 'nullable|string|max:50',
            'sold_at' => ['nullable', 'date', 'before_or_equal:today', Rule::requiredIf(fn () => $request->filled('sold_price'))],
            'sold_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated = $this->normalizeForSaleFields($validated, $request);

        $item->update($validated);

        return redirect()->route('admin.items.edit', $item)->with('success', 'Item updated!');
    }

    public function destroy(Item $item)
    {
        $item->load('media');

        foreach ($item->media as $file) {
            if ($file->path) {
                Storage::disk($file->disk)->delete($file->path);
            }
        }

        Storage::disk('b2')->deleteDirectory('items/'.$item->id);

        $item->media()->delete();
        $item->delete();

        return redirect()->route('admin.items.index')->with('success', 'Item verwijderd!');
    }
}
