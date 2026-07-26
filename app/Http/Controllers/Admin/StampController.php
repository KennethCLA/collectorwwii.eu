<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesInlineMediaUploads;
use App\Http\Controllers\Admin\Concerns\NormalizesForSaleFields;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Location;
use App\Models\NominalValue;
use App\Models\Stamp;
use App\Models\StampType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StampController extends Controller
{
    use HandlesInlineMediaUploads;
    use NormalizesForSaleFields;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Stamp::class);

        $query = Stamp::query()->with(['country', 'nominalValue', 'stampType']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('michel_number', 'like', "%{$search}%")
                    ->orWhere('yvert_tellier_number', 'like', "%{$search}%")
                    ->orWhere('special_features', 'like', "%{$search}%");
            });
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->integer('country_id'));
        }
        if ($request->filled('type_id')) {
            $query->where('type_id', $request->integer('type_id'));
        }
        if ($request->filled('for_sale')) {
            $query->where('for_sale', (bool) (int) $request->input('for_sale'));
        }

        $stamps = $query->orderByDesc('created_at')->paginate(50);
        $countries = Country::orderBy('name')->get();
        $stampTypes = StampType::orderBy('name')->get();

        return view('admin.stamps.index', compact('stamps', 'countries', 'stampTypes'));
    }

    public function create()
    {
        $this->authorize('create', Stamp::class);

        return view('admin.stamps.create', [
            'countries' => Country::orderBy('name')->get(),
            'currencies' => Currency::orderBy('name')->get(),
            'nominalValues' => NominalValue::orderBy('name')->get(),
            'stampTypes' => StampType::orderBy('name')->get(),
            'locations' => Location::flatTree(),
            'designers' => DB::table('stamp_designers')->orderBy('name')->get(),
            'colours' => DB::table('colours')->orderBy('name')->get(),
            'printTypes' => DB::table('print_types')->orderBy('name')->get(),
            'watermarks' => DB::table('stamp_watermarks')->orderBy('name')->get(),
            'gums' => DB::table('stamp_gums')->orderBy('name')->get(),
            'perforations' => DB::table('stamp_perforations')->orderBy('name')->get(),
            'printingHouses' => DB::table('stamp_printing_houses')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Stamp::class);

        $validated = $request->validate([
            'country_id' => 'nullable|exists:countries,id',
            'currency_id' => 'nullable|exists:currencies,id',
            'nominal_value_id' => 'nullable|exists:nominal_values,id',
            'type_id' => 'nullable|exists:stamp_types,id',
            'designer_id' => 'nullable|exists:stamp_designers,id',
            'colour_id' => 'nullable|exists:colours,id',
            'print_type_id' => 'nullable|exists:print_types,id',
            'watermark_id' => 'nullable|exists:stamp_watermarks,id',
            'gum_id' => 'nullable|exists:stamp_gums,id',
            'perforation_id' => 'nullable|exists:stamp_perforations,id',
            'printing_house_id' => 'nullable|exists:stamp_printing_houses,id',
            'year' => 'nullable|integer|min:1800|max:'.(date('Y') + 1),
            'michel_number' => 'nullable|string|max:255',
            'yvert_tellier_number' => 'nullable|string|max:255',
            'date_of_issue' => 'nullable|string|max:255',
            'occasion' => 'nullable|string|max:255',
            'illustration' => 'nullable|string',
            'special_features' => 'nullable|string',
            'mnh' => 'nullable|boolean',
            'hinged' => 'nullable|boolean',
            'postmarked' => 'nullable|boolean',
            'special_postmark' => 'nullable|boolean',
            'postmark_date' => 'nullable|string|max:255',
            'postmark_location' => 'nullable|string|max:255',
            'postmark_text' => 'nullable|string|max:255',
            'perforation' => 'nullable|boolean',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'print_run' => 'nullable|integer|min:0',
            'for_sale' => 'nullable|boolean',
            'selling_price' => 'nullable|numeric|min:0',
            'purchase_date' => ['nullable', 'date', 'before_or_equal:today'],
            'purchasing_price' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'location_id' => 'nullable|exists:locations,id',
            'location_detail' => 'nullable|string|max:255',
            'personal_remarks' => 'nullable|string',
            'condition' => 'nullable|string|max:50',
            'sold_at' => ['nullable', 'date', 'before_or_equal:today', Rule::requiredIf(fn () => $request->filled('sold_price'))],
            'sold_price' => ['nullable', 'numeric', 'min:0'],
            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
            'pdfs' => ['nullable', 'array'],
            'pdfs.*' => ['file', 'mimetypes:application/pdf', 'max:51200'],
            'main_image_index' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['mnh'] = $request->boolean('mnh');
        $validated['hinged'] = $request->boolean('hinged');
        $validated['postmarked'] = $request->boolean('postmarked');
        $validated['special_postmark'] = $request->boolean('special_postmark');
        $validated['perforation'] = $request->boolean('perforation');
        $validated = $this->normalizeForSaleFields($validated, $request);

        $uploadedForCleanup = [];

        try {
            $stamp = DB::transaction(function () use ($validated, $request, &$uploadedForCleanup) {
                $data = $validated;
                unset($data['images'], $data['pdfs'], $data['main_image_index']);

                /** @var \App\Models\Stamp $stamp */
                $stamp = Stamp::create($data);

                $this->attachInlineMedia(
                    $stamp,
                    "stamps/{$stamp->id}",
                    $request->file('images', []),
                    $request->file('pdfs', []),
                    (int) $request->input('main_image_index', 0),
                    $uploadedForCleanup,
                );

                return $stamp;
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

        return redirect()->route('admin.stamps.edit', $stamp)
            ->with('success', 'Stamp created.');
    }

    public function edit(Stamp $stamp)
    {
        $this->authorize('update', $stamp);

        $stamp->load(['images', 'files']);

        return view('admin.stamps.edit', [
            'stamp' => $stamp,
            'countries' => Country::orderBy('name')->get(),
            'currencies' => Currency::orderBy('name')->get(),
            'nominalValues' => NominalValue::orderBy('name')->get(),
            'stampTypes' => StampType::orderBy('name')->get(),
            'locations' => Location::flatTree(),
            'designers' => DB::table('stamp_designers')->orderBy('name')->get(),
            'colours' => DB::table('colours')->orderBy('name')->get(),
            'printTypes' => DB::table('print_types')->orderBy('name')->get(),
            'watermarks' => DB::table('stamp_watermarks')->orderBy('name')->get(),
            'gums' => DB::table('stamp_gums')->orderBy('name')->get(),
            'perforations' => DB::table('stamp_perforations')->orderBy('name')->get(),
            'printingHouses' => DB::table('stamp_printing_houses')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Stamp $stamp)
    {
        $this->authorize('update', $stamp);

        $validated = $request->validate([
            'country_id' => 'nullable|exists:countries,id',
            'currency_id' => 'nullable|exists:currencies,id',
            'nominal_value_id' => 'nullable|exists:nominal_values,id',
            'type_id' => 'nullable|exists:stamp_types,id',
            'designer_id' => 'nullable|exists:stamp_designers,id',
            'colour_id' => 'nullable|exists:colours,id',
            'print_type_id' => 'nullable|exists:print_types,id',
            'watermark_id' => 'nullable|exists:stamp_watermarks,id',
            'gum_id' => 'nullable|exists:stamp_gums,id',
            'perforation_id' => 'nullable|exists:stamp_perforations,id',
            'printing_house_id' => 'nullable|exists:stamp_printing_houses,id',
            'year' => 'nullable|integer|min:1800|max:'.(date('Y') + 1),
            'michel_number' => 'nullable|string|max:255',
            'yvert_tellier_number' => 'nullable|string|max:255',
            'date_of_issue' => 'nullable|string|max:255',
            'occasion' => 'nullable|string|max:255',
            'illustration' => 'nullable|string',
            'special_features' => 'nullable|string',
            'mnh' => 'nullable|boolean',
            'hinged' => 'nullable|boolean',
            'postmarked' => 'nullable|boolean',
            'special_postmark' => 'nullable|boolean',
            'postmark_date' => 'nullable|string|max:255',
            'postmark_location' => 'nullable|string|max:255',
            'postmark_text' => 'nullable|string|max:255',
            'perforation' => 'nullable|boolean',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'print_run' => 'nullable|integer|min:0',
            'for_sale' => 'nullable|boolean',
            'selling_price' => 'nullable|numeric|min:0',
            'purchase_date' => ['nullable', 'date', 'before_or_equal:today'],
            'purchasing_price' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'location_id' => 'nullable|exists:locations,id',
            'location_detail' => 'nullable|string|max:255',
            'personal_remarks' => 'nullable|string',
            'condition' => 'nullable|string|max:50',
            'sold_at' => ['nullable', 'date', 'before_or_equal:today', Rule::requiredIf(fn () => $request->filled('sold_price'))],
            'sold_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['mnh'] = $request->boolean('mnh');
        $validated['hinged'] = $request->boolean('hinged');
        $validated['postmarked'] = $request->boolean('postmarked');
        $validated['special_postmark'] = $request->boolean('special_postmark');
        $validated['perforation'] = $request->boolean('perforation');
        $validated = $this->normalizeForSaleFields($validated, $request);

        $stamp->update($validated);

        return redirect()->route('admin.stamps.edit', $stamp)
            ->with('success', 'Stamp updated!');
    }

    public function destroy(Stamp $stamp)
    {
        $this->authorize('delete', $stamp);

        $stamp->load(['images', 'files']);

        foreach ($stamp->media as $file) {
            if ($file->path) {
                Storage::disk($file->disk)->delete($file->path);
            }
        }

        Storage::disk('b2')->deleteDirectory('stamps/'.$stamp->id);

        $stamp->media()->delete();
        $stamp->forceDelete();

        return redirect()->route('admin.stamps.index')
            ->with('success', 'Stamp deleted.');
    }
}
