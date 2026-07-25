<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesInlineMediaUploads;
use App\Http\Controllers\Controller;
use App\Models\Banknote;
use App\Models\BanknoteSeries;
use App\Models\BanknoteTimePeriod;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Location;
use App\Models\NominalValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BanknoteController extends Controller
{
    use HandlesInlineMediaUploads;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Banknote::class);

        $query = Banknote::query()->with(['country', 'currency', 'nominalValue']);

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->integer('country_id'));
        }
        if ($request->filled('currency_id')) {
            $query->where('currency_id', $request->integer('currency_id'));
        }
        if ($request->filled('for_sale')) {
            $query->where('for_sale', (bool) (int) $request->input('for_sale'));
        }

        $banknotes = $query->orderByDesc('created_at')->paginate(50);
        $countries = Country::orderBy('name')->get();
        $currencies = Currency::orderBy('name')->get();

        return view('admin.banknotes.index', compact('banknotes', 'countries', 'currencies'));
    }

    public function create()
    {
        $this->authorize('create', Banknote::class);

        return view('admin.banknotes.create', [
            'countries' => Country::orderBy('name')->get(),
            'currencies' => Currency::orderBy('name')->get(),
            'nominalValues' => NominalValue::orderBy('name')->get(),
            'seriesList' => BanknoteSeries::orderBy('name')->get(),
            'timePeriods' => BanknoteTimePeriod::orderBy('name')->get(),
            'locations' => Location::flatTree(),
            'headsOfState' => DB::table('heads_of_state')->orderBy('name')->get(),
            'colours' => DB::table('colours')->orderBy('name')->get(),
            'designers' => DB::table('banknote_designers')->orderBy('name')->get(),
            'watermarks' => DB::table('banknote_watermarks')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Banknote::class);

        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'currency_id' => 'required|exists:currencies,id',
            'nominal_value_id' => 'required|exists:nominal_values,id',
            'series_id' => 'required|exists:banknote_series,id',
            'time_period_id' => 'required|exists:banknote_time_periods,id',
            'head_of_state_id' => 'nullable|exists:heads_of_state,id',
            'colour_id' => 'nullable|exists:colours,id',
            'designer_id' => 'nullable|exists:banknote_designers,id',
            'watermark_id' => 'nullable|exists:banknote_watermarks,id',
            'year' => 'nullable|integer|min:1800|max:'.(date('Y') + 1),
            'variation' => 'nullable|string|max:255',
            'number_on_note' => 'nullable|string|max:255',
            'special_features' => 'nullable|string',
            'number_jaeger' => 'nullable|string|max:255',
            'date_of_issue' => 'nullable|date',
            'front_image' => 'nullable|string',
            'front_text' => 'nullable|string',
            'reverse_image' => 'nullable|string',
            'reverse_text' => 'nullable|string',
            'width' => 'nullable|integer|min:0',
            'height' => 'nullable|integer|min:0',
            'print_run' => 'nullable|integer|min:0',
            'for_sale' => 'nullable|boolean',
            'selling_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'purchasing_price' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'location_id' => 'nullable|exists:locations,id',
            'location_detail' => 'nullable|string|max:255',
            'personal_remarks' => 'nullable|string',
            'condition' => 'nullable|string|max:50',
            'sold_at' => 'nullable|date',
            'sold_price' => 'nullable|numeric|min:0',
            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
            'pdfs' => ['nullable', 'array'],
            'pdfs.*' => ['file', 'mimetypes:application/pdf', 'max:51200'],
            'main_image_index' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['for_sale'] = $request->boolean('for_sale');
        if (! empty($validated['sold_at'])) {
            $validated['for_sale'] = false;
            $validated['selling_price'] = null;
        }

        $uploadedForCleanup = [];

        try {
            $banknote = DB::transaction(function () use ($validated, $request, &$uploadedForCleanup) {
                $data = $validated;
                unset($data['images'], $data['pdfs'], $data['main_image_index']);

                /** @var \App\Models\Banknote $banknote */
                $banknote = Banknote::create($data);

                $this->attachInlineMedia(
                    $banknote,
                    "banknotes/{$banknote->id}",
                    $request->file('images', []),
                    $request->file('pdfs', []),
                    (int) $request->input('main_image_index', 0),
                    $uploadedForCleanup,
                );

                return $banknote;
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

        return redirect()->route('admin.banknotes.edit', $banknote)
            ->with('success', 'Banknote created.');
    }

    public function edit(Banknote $banknote)
    {
        $this->authorize('update', $banknote);

        $banknote->load(['images', 'files']);

        return view('admin.banknotes.edit', [
            'banknote' => $banknote,
            'countries' => Country::orderBy('name')->get(),
            'currencies' => Currency::orderBy('name')->get(),
            'nominalValues' => NominalValue::orderBy('name')->get(),
            'seriesList' => BanknoteSeries::orderBy('name')->get(),
            'timePeriods' => BanknoteTimePeriod::orderBy('name')->get(),
            'locations' => Location::flatTree(),
            'headsOfState' => DB::table('heads_of_state')->orderBy('name')->get(),
            'colours' => DB::table('colours')->orderBy('name')->get(),
            'designers' => DB::table('banknote_designers')->orderBy('name')->get(),
            'watermarks' => DB::table('banknote_watermarks')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Banknote $banknote)
    {
        $this->authorize('update', $banknote);

        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'currency_id' => 'required|exists:currencies,id',
            'nominal_value_id' => 'required|exists:nominal_values,id',
            'series_id' => 'required|exists:banknote_series,id',
            'time_period_id' => 'required|exists:banknote_time_periods,id',
            'head_of_state_id' => 'nullable|exists:heads_of_state,id',
            'colour_id' => 'nullable|exists:colours,id',
            'designer_id' => 'nullable|exists:banknote_designers,id',
            'watermark_id' => 'nullable|exists:banknote_watermarks,id',
            'year' => 'nullable|integer|min:1800|max:'.(date('Y') + 1),
            'variation' => 'nullable|string|max:255',
            'number_on_note' => 'nullable|string|max:255',
            'special_features' => 'nullable|string',
            'number_jaeger' => 'nullable|string|max:255',
            'date_of_issue' => 'nullable|date',
            'front_image' => 'nullable|string',
            'front_text' => 'nullable|string',
            'reverse_image' => 'nullable|string',
            'reverse_text' => 'nullable|string',
            'width' => 'nullable|integer|min:0',
            'height' => 'nullable|integer|min:0',
            'print_run' => 'nullable|integer|min:0',
            'for_sale' => 'nullable|boolean',
            'selling_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'purchasing_price' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'location_id' => 'nullable|exists:locations,id',
            'location_detail' => 'nullable|string|max:255',
            'personal_remarks' => 'nullable|string',
            'condition' => 'nullable|string|max:50',
            'sold_at' => 'nullable|date',
            'sold_price' => 'nullable|numeric|min:0',
        ]);

        $validated['for_sale'] = $request->boolean('for_sale');
        if (! empty($validated['sold_at'])) {
            $validated['for_sale'] = false;
            $validated['selling_price'] = null;
        }

        $banknote->update($validated);

        return redirect()->route('admin.banknotes.edit', $banknote)
            ->with('success', 'Banknote updated!');
    }

    public function destroy(Banknote $banknote)
    {
        $this->authorize('delete', $banknote);

        $banknote->load(['images', 'files']);

        foreach ($banknote->media as $file) {
            if ($file->path) {
                Storage::disk($file->disk)->delete($file->path);
            }
        }

        Storage::disk('b2')->deleteDirectory('banknotes/'.$banknote->id);

        $banknote->media()->delete();
        $banknote->delete();

        return redirect()->route('admin.banknotes.index')
            ->with('success', 'Banknote deleted.');
    }
}
