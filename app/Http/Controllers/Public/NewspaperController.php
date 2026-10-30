<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Newspaper;
use Illuminate\Http\Request;

class NewspaperController extends Controller
{
    public function index(Request $request)
    {
        $query = Newspaper::query()->with(['mainImage']);

        if ($request->filled('search')) {
            $s = trim($request->input('search'));
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                    ->orWhere('publisher', 'like', "%{$s}%");
            });
        }

        if ($request->filled('for_sale')) {
            $query->where('for_sale', (bool) ((int) $request->input('for_sale')));
        }

        $sort = $request->input('sort', 'created_at_asc');

        switch ($sort) {
            case 'title_asc':
                $query->orderBy('title', 'asc')->orderBy('id', 'desc');
                break;
            case 'title_desc':
                $query->orderBy('title', 'desc')->orderBy('id', 'desc');
                break;
            case 'created_at_desc':
                $query->orderBy('created_at', 'asc')->orderBy('id', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
                $sort = 'created_at_asc';
                break;
        }

        $newspapers = $query->paginate(204)->withQueryString();

        return view('newspapers.index', compact('newspapers', 'sort'));
    }

    public function show(Newspaper $newspaper)
    {
        $newspaper->load(['images', 'mainImage', 'files']);

        $previousNewspaper = Newspaper::where('id', '<', $newspaper->id)->orderByDesc('id')->first();
        $nextNewspaper = Newspaper::where('id', '>', $newspaper->id)->orderBy('id')->first();

        return view('newspapers.show', compact('newspaper', 'previousNewspaper', 'nextNewspaper'));
    }
}
