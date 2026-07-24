<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Postcard;
use App\Models\PostcardType;
use Illuminate\Http\Request;

class PostcardController extends Controller
{
    public function index(Request $request)
    {
        $countries = Country::orderBy('name')->get();
        $postcardTypes = PostcardType::orderBy('name')->get();

        $query = Postcard::query()->with(['mainImage', 'country', 'postcardType']);

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->integer('country_id'));
        }

        if ($request->filled('postcard_type_id')) {
            $query->where('postcard_type_id', $request->integer('postcard_type_id'));
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

        $postcards = $query->paginate(50)->withQueryString();

        return view('postcards.index', compact('postcards', 'countries', 'postcardTypes', 'sort'));
    }

    public function show(Postcard $postcard)
    {
        $postcard->load(['images', 'mainImage', 'files', 'country', 'postcardType', 'location']);

        $previousPostcard = Postcard::where('id', '<', $postcard->id)->orderByDesc('id')->first();
        $nextPostcard = Postcard::where('id', '>', $postcard->id)->orderBy('id')->first();

        return view('postcards.show', compact('postcard', 'previousPostcard', 'nextPostcard'));
    }
}
