<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\CoinMaterial;
use App\Models\Country;
use Illuminate\Http\Request;

class CoinController extends Controller
{
    public function index(Request $request)
    {
        $countries = Country::orderBy('name')->get();
        $materials = CoinMaterial::orderBy('name')->get();

        $query = Coin::query()->with(['mainImage', 'country', 'currency', 'nominalValue', 'material']);

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->integer('country_id'));
        }

        if ($request->filled('material_id')) {
            $query->where('material_id', $request->integer('material_id'));
        }

        if ($request->filled('for_sale')) {
            $query->where('for_sale', (bool) ((int) $request->input('for_sale')));
        }

        $sort = $request->input('sort', 'created_at_asc');

        switch ($sort) {
            case 'created_at_desc':
                $query->orderBy('created_at', 'asc')->orderBy('id', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
                $sort = 'created_at_asc';
                break;
        }

        $coins = $query->paginate(50)->withQueryString();

        return view('coins.index', compact('coins', 'countries', 'materials', 'sort'));
    }

    public function show(Coin $coin)
    {
        $coin->load(['images', 'mainImage', 'files', 'country', 'currency', 'nominalValue', 'shape', 'material', 'occasion', 'location']);

        $previousCoin = Coin::where('id', '<', $coin->id)->orderByDesc('id')->first();
        $nextCoin = Coin::where('id', '>', $coin->id)->orderBy('id')->first();

        return view('coins.show', compact('coin', 'previousCoin', 'nextCoin'));
    }
}
