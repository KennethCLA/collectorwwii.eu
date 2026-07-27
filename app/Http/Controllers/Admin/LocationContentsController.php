<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banknote;
use App\Models\Book;
use App\Models\Coin;
use App\Models\Location;
use App\Models\Postcard;
use App\Models\Stamp;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Collection;

class LocationContentsController extends Controller
{
    /**
     * One printable QR label per location, each pointing at that location's
     * contents page — stick the label on the physical box/shelf, scan it to
     * see what's stored there.
     */
    public function labels()
    {
        $writer = new SvgWriter();

        $labels = Location::flatTree()->map(function ($row) use ($writer) {
            $url = route('admin.lookups.locations.contents', $row->id);
            $qrCode = new QrCode(
                data: $url,
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 300,
                margin: 10,
            );
            $svg = $writer->write($qrCode)->getString();
            // Strip the XML declaration — invalid as inline HTML content and
            // some browsers render it as literal visible text otherwise.
            $svg = preg_replace('/^<\?xml[^>]*\?>\s*/', '', $svg);

            return [
                'name' => $row->name,
                'svg' => $svg,
            ];
        });

        return view('admin.lookups.location-labels', [
            'labels' => $labels,
        ]);
    }

    public function show(Location $location)
    {
        $ids = $this->collectIds($location);

        $groups = [
            'books' => Book::with('authors')->whereIn('location_id', $ids)->orderBy('title')->get(),
            'banknotes' => Banknote::with(['country', 'currency', 'nominalValue'])->whereIn('location_id', $ids)->orderByDesc('created_at')->get(),
            'coins' => Coin::with(['country', 'nominalValue'])->whereIn('location_id', $ids)->orderByDesc('created_at')->get(),
            'postcards' => Postcard::with(['country', 'postcardType'])->whereIn('location_id', $ids)->orderByDesc('created_at')->get(),
            'stamps' => Stamp::with(['country', 'stampType'])->whereIn('location_id', $ids)->orderByDesc('created_at')->get(),
        ];

        return view('admin.lookups.location-contents', [
            'location' => $location,
            'groups' => $groups,
            'total' => collect($groups)->sum->count(),
            'includesChildren' => $ids->count() > 1,
        ]);
    }

    /**
     * This location's id plus every descendant location's id, so contents
     * stored under a sub-location (e.g. a shelf inside a room) still show
     * up when viewing the parent.
     */
    private function collectIds(Location $location): Collection
    {
        $ids = collect([$location->id]);

        foreach ($location->children as $child) {
            $ids = $ids->merge($this->collectIds($child));
        }

        return $ids;
    }
}
