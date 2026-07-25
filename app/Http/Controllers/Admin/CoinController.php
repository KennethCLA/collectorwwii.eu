<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesInlineMediaUploads;
use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\CoinMaterial;
use App\Models\CoinOccasion;
use App\Models\CoinShape;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Location;
use App\Models\NominalValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CoinController extends Controller
{
    use HandlesInlineMediaUploads;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Coin::class);

        $query = Coin::query()->with(['country', 'nominalValue', 'material']);

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->integer('country_id'));
        }
        if ($request->filled('material_id')) {
            $query->where('material_id', $request->integer('material_id'));
        }
        if ($request->filled('for_sale')) {
            $query->where('for_sale', (bool) (int) $request->input('for_sale'));
        }

        $coins = $query->orderByDesc('created_at')->paginate(50);
        $countries = Country::orderBy('name')->get();
        $materials = CoinMaterial::orderBy('name')->get();

        return view('admin.coins.index', compact('coins', 'countries', 'materials'));
    }

    public function create()
    {
        $this->authorize('create', Coin::class);

        return view('admin.coins.create', [
            'countries' => Country::orderBy('name')->get(),
            'currencies' => Currency::orderBy('name')->get(),
            'nominalValues' => NominalValue::orderBy('name')->get(),
            'shapes' => CoinShape::orderBy('name')->get(),
            'materials' => CoinMaterial::orderBy('name')->get(),
            'occasions' => CoinOccasion::orderBy('name')->get(),
            'locations' => Location::flatTree(),
            'headsOfState' => DB::table('heads_of_state')->orderBy('name')->get(),
            'strikeMarks' => DB::table('coin_strike_marks')->orderBy('name')->get(),
            'designers' => DB::table('coin_designers')->orderBy('name')->get(),
            'frontImages' => DB::table('coin_front_images')->orderBy('name')->get(),
            'frontTexts' => DB::table('coin_front_texts')->orderBy('name')->get(),
            'reverseImages' => DB::table('coin_reverse_images')->orderBy('name')->get(),
            'reverseTexts' => DB::table('coin_reverse_texts')->orderBy('name')->get(),
            'rims' => DB::table('coin_rims')->orderBy('name')->get(),
            'rimTexts' => DB::table('coin_rim_texts')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Coin::class);

        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'currency_id' => 'required|exists:currencies,id',
            'nominal_value_id' => 'required|exists:nominal_values,id',
            'shape_id' => 'nullable|exists:coin_shapes,id',
            'material_id' => 'nullable|exists:coin_materials,id',
            'year' => 'nullable|integer|min:1800|max:'.(date('Y') + 1),
            'occasion_id' => 'nullable|exists:coin_occasions,id',
            'head_of_state_id' => 'nullable|exists:heads_of_state,id',
            'strike_mark_id' => 'nullable|exists:coin_strike_marks,id',
            'designer_id' => 'nullable|exists:coin_designers,id',
            'front_image_id' => 'nullable|exists:coin_front_images,id',
            'front_text_id' => 'nullable|exists:coin_front_texts,id',
            'reverse_image_id' => 'nullable|exists:coin_reverse_images,id',
            'reverse_text_id' => 'nullable|exists:coin_reverse_texts,id',
            'rim_id' => 'nullable|exists:coin_rims,id',
            'rim_text_id' => 'nullable|exists:coin_rim_texts,id',
            'time_period' => 'nullable|string|max:255',
            'number_jaeger' => 'nullable|string|max:255',
            'date_of_issue' => 'nullable|date',
            'special_features' => 'nullable|string',
            'gold_silver_content' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'diameter' => 'nullable|numeric|min:0',
            'thickness' => 'nullable|numeric|min:0',
            'run' => 'nullable|integer|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'for_sale' => 'nullable|boolean',
            'selling_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'purchasing_price' => 'nullable|numeric|min:0',
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
            $coin = DB::transaction(function () use ($validated, $request, &$uploadedForCleanup) {
                $data = $validated;
                unset($data['images'], $data['pdfs'], $data['main_image_index']);

                /** @var \App\Models\Coin $coin */
                $coin = Coin::create($data);

                $this->attachInlineMedia(
                    $coin,
                    "coins/{$coin->id}",
                    $request->file('images', []),
                    $request->file('pdfs', []),
                    (int) $request->input('main_image_index', 0),
                    $uploadedForCleanup,
                );

                return $coin;
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

        return redirect()->route('admin.coins.edit', $coin)
            ->with('success', 'Coin created.');
    }

    public function edit(Coin $coin)
    {
        $this->authorize('update', $coin);

        $coin->load(['images', 'files']);

        return view('admin.coins.edit', [
            'coin' => $coin,
            'countries' => Country::orderBy('name')->get(),
            'currencies' => Currency::orderBy('name')->get(),
            'nominalValues' => NominalValue::orderBy('name')->get(),
            'shapes' => CoinShape::orderBy('name')->get(),
            'materials' => CoinMaterial::orderBy('name')->get(),
            'occasions' => CoinOccasion::orderBy('name')->get(),
            'locations' => Location::flatTree(),
            'headsOfState' => DB::table('heads_of_state')->orderBy('name')->get(),
            'strikeMarks' => DB::table('coin_strike_marks')->orderBy('name')->get(),
            'designers' => DB::table('coin_designers')->orderBy('name')->get(),
            'frontImages' => DB::table('coin_front_images')->orderBy('name')->get(),
            'frontTexts' => DB::table('coin_front_texts')->orderBy('name')->get(),
            'reverseImages' => DB::table('coin_reverse_images')->orderBy('name')->get(),
            'reverseTexts' => DB::table('coin_reverse_texts')->orderBy('name')->get(),
            'rims' => DB::table('coin_rims')->orderBy('name')->get(),
            'rimTexts' => DB::table('coin_rim_texts')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Coin $coin)
    {
        $this->authorize('update', $coin);

        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'currency_id' => 'required|exists:currencies,id',
            'nominal_value_id' => 'required|exists:nominal_values,id',
            'shape_id' => 'nullable|exists:coin_shapes,id',
            'material_id' => 'nullable|exists:coin_materials,id',
            'year' => 'nullable|integer|min:1800|max:'.(date('Y') + 1),
            'occasion_id' => 'nullable|exists:coin_occasions,id',
            'head_of_state_id' => 'nullable|exists:heads_of_state,id',
            'strike_mark_id' => 'nullable|exists:coin_strike_marks,id',
            'designer_id' => 'nullable|exists:coin_designers,id',
            'front_image_id' => 'nullable|exists:coin_front_images,id',
            'front_text_id' => 'nullable|exists:coin_front_texts,id',
            'reverse_image_id' => 'nullable|exists:coin_reverse_images,id',
            'reverse_text_id' => 'nullable|exists:coin_reverse_texts,id',
            'rim_id' => 'nullable|exists:coin_rims,id',
            'rim_text_id' => 'nullable|exists:coin_rim_texts,id',
            'time_period' => 'nullable|string|max:255',
            'number_jaeger' => 'nullable|string|max:255',
            'date_of_issue' => 'nullable|date',
            'special_features' => 'nullable|string',
            'gold_silver_content' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'diameter' => 'nullable|numeric|min:0',
            'thickness' => 'nullable|numeric|min:0',
            'run' => 'nullable|integer|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'for_sale' => 'nullable|boolean',
            'selling_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'purchasing_price' => 'nullable|numeric|min:0',
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

        $coin->update($validated);

        return redirect()->route('admin.coins.edit', $coin)
            ->with('success', 'Coin updated!');
    }

    public function destroy(Coin $coin)
    {
        $this->authorize('delete', $coin);

        $coin->load(['images', 'files']);

        foreach ($coin->media as $file) {
            if ($file->path) {
                Storage::disk($file->disk)->delete($file->path);
            }
        }

        Storage::disk('b2')->deleteDirectory('coins/'.$coin->id);

        $coin->media()->delete();
        $coin->delete();

        return redirect()->route('admin.coins.index')
            ->with('success', 'Coin deleted.');
    }
}
