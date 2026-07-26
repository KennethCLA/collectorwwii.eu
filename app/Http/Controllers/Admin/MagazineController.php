<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesInlineMediaUploads;
use App\Http\Controllers\Admin\Concerns\NormalizesForSaleFields;
use App\Http\Controllers\Controller;
use App\Models\Magazine;
use App\Models\MagazineSeries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MagazineController extends Controller
{
    use HandlesInlineMediaUploads;
    use NormalizesForSaleFields;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Magazine::class);

        $query = Magazine::query();

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where('title', 'like', "%{$s}%");
        }

        if ($request->filled('for_sale')) {
            $query->where('for_sale', (bool) (int) $request->input('for_sale'));
        }

        $magazines = $query->with('series')->orderByDesc('created_at')->paginate(50);

        return view('admin.magazines.index', compact('magazines'));
    }

    public function create()
    {
        $this->authorize('create', Magazine::class);

        return view('admin.magazines.create', [
            'series' => MagazineSeries::flatTree(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Magazine::class);

        $validated = $request->validate([
            'series_id' => 'nullable|exists:magazine_series,id',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'issue_number' => 'nullable|integer|min:1',
            'issue_year' => 'nullable|integer|min:1800|max:'.(date('Y') + 1),
            'description' => 'nullable|string',
            'purchase_date' => ['nullable', 'date', 'before_or_equal:today'],
            'purchase_price' => 'nullable|numeric|min:0',
            'for_sale' => 'nullable|boolean',
            'selling_price' => 'nullable|numeric|min:0',
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
            $magazine = DB::transaction(function () use ($validated, $request, &$uploadedForCleanup) {
                $data = $validated;
                unset($data['images'], $data['pdfs'], $data['main_image_index']);

                /** @var \App\Models\Magazine $magazine */
                $magazine = Magazine::create($data);

                $this->attachInlineMedia(
                    $magazine,
                    "magazines/{$magazine->id}",
                    $request->file('images', []),
                    $request->file('pdfs', []),
                    (int) $request->input('main_image_index', 0),
                    $uploadedForCleanup,
                );

                return $magazine;
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

        return redirect()->route('admin.magazines.edit', $magazine)
            ->with('success', 'Magazine created.');
    }

    public function edit(Magazine $magazine)
    {
        $this->authorize('update', $magazine);

        $magazine->load(['images', 'files']);

        return view('admin.magazines.edit', [
            'magazine' => $magazine,
            'series' => MagazineSeries::flatTree(),
        ]);
    }

    public function update(Request $request, Magazine $magazine)
    {
        $this->authorize('update', $magazine);

        $validated = $request->validate([
            'series_id' => 'nullable|exists:magazine_series,id',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'issue_number' => 'nullable|integer|min:1',
            'issue_year' => 'nullable|integer|min:1800|max:'.(date('Y') + 1),
            'description' => 'nullable|string',
            'purchase_date' => ['nullable', 'date', 'before_or_equal:today'],
            'purchase_price' => 'nullable|numeric|min:0',
            'for_sale' => 'nullable|boolean',
            'selling_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'condition' => 'nullable|string|max:50',
            'sold_at' => ['nullable', 'date', 'before_or_equal:today', Rule::requiredIf(fn () => $request->filled('sold_price'))],
            'sold_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated = $this->normalizeForSaleFields($validated, $request);

        $magazine->update($validated);

        return redirect()->route('admin.magazines.edit', $magazine)
            ->with('success', 'Magazine updated!');
    }

    public function destroy(Magazine $magazine)
    {
        $this->authorize('delete', $magazine);

        $magazine->load(['images', 'files']);

        foreach ($magazine->media as $file) {
            if ($file->path) {
                Storage::disk($file->disk)->delete($file->path);
            }
        }

        Storage::disk('b2')->deleteDirectory('magazines/'.$magazine->id);

        $magazine->media()->delete();
        $magazine->delete();

        return redirect()->route('admin.magazines.index')
            ->with('success', 'Magazine deleted.');
    }
}
