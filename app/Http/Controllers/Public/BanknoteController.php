<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Banknote;
use App\Models\Country;
use App\Models\Currency;
use Illuminate\Http\Request;

class BanknoteController extends Controller
{
    public function index(Request $request)
    {
        $countries = Country::orderBy('name')->get();
        $currencies = Currency::orderBy('name')->get();

        $query = Banknote::query()->with(['mainImage', 'country', 'currency', 'nominalValue']);

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->integer('country_id'));
        }

        if ($request->filled('currency_id')) {
            $query->where('currency_id', $request->integer('currency_id'));
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

        $banknotes = $query->paginate(50)->withQueryString();

        return view('banknotes.index', compact('banknotes', 'countries', 'currencies', 'sort'));
    }

    public function show(Banknote $banknote)
    {
        $banknote->load(['images', 'mainImage', 'files', 'country', 'currency', 'nominalValue', 'series', 'timePeriod', 'location']);

        $previousBanknote = Banknote::where('id', '<', $banknote->id)->orderByDesc('id')->first();
        $nextBanknote = Banknote::where('id', '>', $banknote->id)->orderBy('id')->first();

        return view('banknotes.show', compact('banknote', 'previousBanknote', 'nextBanknote'));
    }
}
