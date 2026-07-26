<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\MapLocation;

class MapController extends Controller
{
    public function index()
    {
        $locations = MapLocation::query()
            ->with(['mainImage', 'images'])
            ->orderBy('name')
            ->get()
            ->map(function (MapLocation $location) {
                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'description' => $location->description,
                    'coordinates' => $location->coordinates,
                    'lat' => $location->latitude(),
                    'lng' => $location->longitude(),
                    'image' => $location->mainImage?->url(),
                    'images' => $location->images->map(fn ($img) => $img->url())->values()->all(),
                ];
            })
            ->filter(fn (array $row) => is_numeric($row['lat']) && is_numeric($row['lng']))
            ->values();

        return view('map.index', [
            'locations' => $locations,
            'maptilerKey' => config('services.maptiler.key'),
        ]);
    }
}
