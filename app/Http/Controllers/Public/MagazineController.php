<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Magazine;
use Illuminate\Http\Request;

class MagazineController extends Controller
{
    public function index(Request $request)
    {
        $query = Magazine::query()->with(['mainImage']);

        if ($request->filled('search')) {
            $s = trim($request->input('search'));
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                    ->orWhere('subtitle', 'like', "%{$s}%")
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

        $magazines = $query->paginate(50)->withQueryString();

        return view('magazines.index', compact('magazines', 'sort'));
    }

    public function show(Magazine $magazine)
    {
        $magazine->load(['images', 'mainImage', 'files', 'series']);

        $previousMagazine = Magazine::where('id', '<', $magazine->id)->orderByDesc('id')->first();
        $nextMagazine = Magazine::where('id', '>', $magazine->id)->orderBy('id')->first();

        return view('magazines.show', compact('magazine', 'previousMagazine', 'nextMagazine'));
    }
}
