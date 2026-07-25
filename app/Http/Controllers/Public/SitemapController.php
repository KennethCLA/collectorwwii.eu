<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Banknote;
use App\Models\Book;
use App\Models\Coin;
use App\Models\Item;
use App\Models\Magazine;
use App\Models\Newspaper;
use App\Models\Postcard;
use App\Models\Stamp;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    private const TYPES = [
        'books' => ['model' => Book::class, 'route' => 'books.show', 'section' => 'books'],
        'items' => ['model' => Item::class, 'route' => 'items.show', 'section' => 'items'],
        'magazines' => ['model' => Magazine::class, 'route' => 'magazines.show', 'section' => 'magazines'],
        'newspapers' => ['model' => Newspaper::class, 'route' => 'newspapers.show', 'section' => 'newspapers'],
        'banknotes' => ['model' => Banknote::class, 'route' => 'banknotes.show', 'section' => 'banknotes'],
        'coins' => ['model' => Coin::class, 'route' => 'coins.show', 'section' => 'coins'],
        'postcards' => ['model' => Postcard::class, 'route' => 'postcards.show', 'section' => 'postcards'],
        'stamps' => ['model' => Stamp::class, 'route' => 'stamps.show', 'section' => 'stamps'],
    ];

    public function index(): Response
    {
        $urls = Cache::remember('sitemap.urls', now()->addHours(6), function () {
            $urls = [];

            $staticRoutes = ['home', 'blog', 'for-sale.index', 'map.index', 'contact'];
            foreach ($staticRoutes as $name) {
                $urls[] = ['loc' => route($name), 'lastmod' => null];
            }

            foreach (self::TYPES as $type => $cfg) {
                if (! config("collector.enabled_sections.{$type}")) {
                    continue;
                }

                $urls[] = ['loc' => route("{$type}.index"), 'lastmod' => null];

                $cfg['model']::query()
                    ->select(['id', 'updated_at'])
                    ->orderBy('id')
                    ->chunk(1000, function ($rows) use (&$urls, $cfg) {
                        foreach ($rows as $row) {
                            $urls[] = [
                                'loc' => route($cfg['route'], $row->id),
                                'lastmod' => $row->updated_at?->toAtomString(),
                            ];
                        }
                    });
            }

            return $urls;
        });

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
