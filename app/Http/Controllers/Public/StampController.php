<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Stamp;
use App\Models\StampType;
use Illuminate\Http\Request;

class StampController extends Controller
{
    public function index(Request $request)
    {
        $countries = Country::orderBy('name')->get();
        $stampTypes = StampType::orderBy('name')->get();

        $query = Stamp::query()->with(['mainImage', 'country', 'nominalValue', 'stampType']);

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->integer('country_id'));
        }

        if ($request->filled('type_id')) {
            $query->where('type_id', $request->integer('type_id'));
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

        $stamps = $query->paginate(50)->withQueryString();

        return view('stamps.index', compact('stamps', 'countries', 'stampTypes', 'sort'));
    }

    public function show(Stamp $stamp)
    {
        $stamp->load(['images', 'mainImage', 'files', 'country', 'currency', 'nominalValue', 'stampType', 'location']);

        $previousStamp = Stamp::where('id', '<', $stamp->id)->orderByDesc('id')->first();
        $nextStamp = Stamp::where('id', '>', $stamp->id)->orderBy('id')->first();

        return view('stamps.show', compact('stamp', 'previousStamp', 'nextStamp'));
    }
}
