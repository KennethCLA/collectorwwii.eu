<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesInlineMediaUploads;
use App\Http\Controllers\Admin\Concerns\NormalizesForSaleFields;
use App\Http\Controllers\Controller;
use App\Models\Newspaper;
use App\Models\NewspaperSeries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class NewspaperController extends Controller
{
    use HandlesInlineMediaUploads;
    use NormalizesForSaleFields;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Newspaper::class);

        $query = Newspaper::query();

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where('title', 'like', "%{$s}%");
        }

        if ($request->filled('for_sale')) {
            $query->where('for_sale', (bool) (int) $request->input('for_sale'));
        }

        $newspapers = $query->with('series')->orderByDesc('created_at')->paginate(50);

        return view('admin.newspapers.index', compact('newspapers'));
    }

    public function create()
    {
        $this->authorize('create', Newspaper::class);

        return view('admin.newspapers.create', [
            'series' => NewspaperSeries::flatTree(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Newspaper::class);

        $validated = $request->validate([
            'series_id' => 'nullable|exists:newspaper_series,id',
            'title' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'publication_date' => 'nullable|date',
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
            $newspaper = DB::transaction(function () use ($validated, $request, &$uploadedForCleanup) {
                $data = $validated;
                unset($data['images'], $data['pdfs'], $data['main_image_index']);

                /** @var \App\Models\Newspaper $newspaper */
                $newspaper = Newspaper::create($data);

                $this->attachInlineMedia(
                    $newspaper,
                    "newspapers/{$newspaper->id}",
                    $request->file('images', []),
                    $request->file('pdfs', []),
                    (int) $request->input('main_image_index', 0),
                    $uploadedForCleanup,
                );

                return $newspaper;
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

        return redirect()->route('admin.newspapers.edit', $newspaper)
            ->with('success', 'Newspaper created.');
    }

    public function edit(Newspaper $newspaper)
    {
        $this->authorize('update', $newspaper);

        $newspaper->load(['images', 'files']);

        return view('admin.newspapers.edit', [
            'newspaper' => $newspaper,
            'series' => NewspaperSeries::flatTree(),
        ]);
    }

    public function update(Request $request, Newspaper $newspaper)
    {
        $this->authorize('update', $newspaper);

        $validated = $request->validate([
            'series_id' => 'nullable|exists:newspaper_series,id',
            'title' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'publication_date' => 'nullable|date',
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

        $newspaper->update($validated);

        return redirect()->route('admin.newspapers.edit', $newspaper)
            ->with('success', 'Newspaper updated!');
    }

    public function destroy(Newspaper $newspaper)
    {
        $this->authorize('delete', $newspaper);

        $newspaper->load(['images', 'files']);

        foreach ($newspaper->media as $file) {
            if ($file->path) {
                Storage::disk($file->disk)->delete($file->path);
            }
        }

        Storage::disk('b2')->deleteDirectory('newspapers/'.$newspaper->id);

        $newspaper->media()->delete();
        $newspaper->forceDelete();

        return redirect()->route('admin.newspapers.index')
            ->with('success', 'Newspaper deleted.');
    }
}
