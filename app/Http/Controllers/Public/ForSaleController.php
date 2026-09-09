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
use Illuminate\Http\Request;

class ForSaleController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', 'all');
        $q = trim((string) $request->query('q', ''));
        $sort = $request->query('sort', 'created_at_desc');

        $searchMatches = static function (string $title, string $search): bool {
            if ($search === '') {
                return true;
            }

            return mb_stripos($title, $search) !== false;
        };

        $forSale = collect();

        if ($type === 'all' || $type === 'books') {
            $books = Book::query()
                ->where('for_sale', true)
                ->with(['mainImage', 'authors'])
                ->select(['id', 'title', 'subtitle', 'selling_price', 'created_at'])
                ->get()
                ->map(function (Book $book) {
                    return [
                        'type' => 'books',
                        'type_label' => 'Book',
                        'title' => $book->title,
                        'subtitle' => $book->subtitle,
                        'authors' => $book->authors->pluck('name')->values()->all(),
                        'price' => $book->selling_price,
                        'created_at' => $book->created_at,
                        'image' => $book->mainImage?->url(),
                        'url' => route('books.show', $book),
                    ];
                });

            $forSale = $forSale->concat($books);
        }

        if ($type === 'all' || $type === 'items') {
            $items = Item::query()
                ->where('for_sale', true)
                ->with(['mainImage'])
                ->select(['id', 'title', 'selling_price', 'created_at'])
                ->get()
                ->map(function (Item $item) {
                    return [
                        'type' => 'items',
                        'type_label' => 'Item',
                        'title' => $item->title,
                        'price' => $item->selling_price,
                        'created_at' => $item->created_at,
                        'image' => $item->mainImage?->url(),
                        'url' => route('items.show', $item),
                    ];
                });

            $forSale = $forSale->concat($items);
        }

        if ($type === 'all' || $type === 'banknotes') {
            $banknotes = Banknote::query()
                ->where('for_sale', true)
                ->with(['mainImage', 'nominalValue', 'currency'])
                ->select(['id', 'nominal_value_id', 'currency_id', 'year', 'selling_price', 'created_at'])
                ->get()
                ->map(function (Banknote $banknote) {
                    return [
                        'type' => 'banknotes',
                        'type_label' => 'Banknote',
                        'title' => $banknote->card_title,
                        'price' => $banknote->selling_price,
                        'created_at' => $banknote->created_at,
                        'image' => $banknote->mainImage?->url(),
                        'url' => route('banknotes.show', $banknote),
                    ];
                });

            $forSale = $forSale->concat($banknotes);
        }

        if ($type === 'all' || $type === 'coins') {
            $coins = Coin::query()
                ->where('for_sale', true)
                ->with(['mainImage', 'nominalValue', 'country'])
                ->select(['id', 'nominal_value_id', 'country_id', 'year', 'selling_price', 'created_at'])
                ->get()
                ->map(function (Coin $coin) {
                    return [
                        'type' => 'coins',
                        'type_label' => 'Coin',
                        'title' => $coin->card_title,
                        'price' => $coin->selling_price,
                        'created_at' => $coin->created_at,
                        'image' => $coin->mainImage?->url(),
                        'url' => route('coins.show', $coin),
                    ];
                });

            $forSale = $forSale->concat($coins);
        }

        if ($type === 'all' || $type === 'magazines') {
            $magazines = Magazine::query()
                ->where('for_sale', true)
                ->with(['mainImage'])
                ->select(['id', 'title', 'selling_price', 'created_at'])
                ->get()
                ->map(function (Magazine $magazine) {
                    return [
                        'type' => 'magazines',
                        'type_label' => 'Magazine',
                        'title' => $magazine->title,
                        'price' => $magazine->selling_price,
                        'created_at' => $magazine->created_at,
                        'image' => $magazine->mainImage?->url(),
                        'url' => route('magazines.show', $magazine),
                    ];
                });

            $forSale = $forSale->concat($magazines);
        }

        if ($type === 'all' || $type === 'newspapers') {
            $newspapers = Newspaper::query()
                ->where('for_sale', true)
                ->with(['mainImage'])
                ->select(['id', 'title', 'selling_price', 'created_at'])
                ->get()
                ->map(function (Newspaper $newspaper) {
                    return [
                        'type' => 'newspapers',
                        'type_label' => 'Newspaper',
                        'title' => $newspaper->title,
                        'price' => $newspaper->selling_price,
                        'created_at' => $newspaper->created_at,
                        'image' => $newspaper->mainImage?->url(),
                        'url' => route('newspapers.show', $newspaper),
                    ];
                });

            $forSale = $forSale->concat($newspapers);
        }

        if ($type === 'all' || $type === 'postcards') {
            $postcards = Postcard::query()
                ->where('for_sale', true)
                ->with(['mainImage', 'country'])
                ->select(['id', 'country_id', 'year', 'selling_price', 'created_at'])
                ->get()
                ->map(function (Postcard $postcard) {
                    return [
                        'type' => 'postcards',
                        'type_label' => 'Postcard',
                        'title' => $postcard->card_title,
                        'price' => $postcard->selling_price,
                        'created_at' => $postcard->created_at,
                        'image' => $postcard->mainImage?->url(),
                        'url' => route('postcards.show', $postcard),
                    ];
                });

            $forSale = $forSale->concat($postcards);
        }

        if ($type === 'all' || $type === 'stamps') {
            $stamps = Stamp::query()
                ->where('for_sale', true)
                ->with(['mainImage', 'nominalValue', 'country'])
                ->select(['id', 'nominal_value_id', 'country_id', 'year', 'selling_price', 'created_at'])
                ->get()
                ->map(function (Stamp $stamp) {
                    return [
                        'type' => 'stamps',
                        'type_label' => 'Stamp',
                        'title' => $stamp->card_title,
                        'price' => $stamp->selling_price,
                        'created_at' => $stamp->created_at,
                        'image' => $stamp->mainImage?->url(),
                        'url' => route('stamps.show', $stamp),
                    ];
                });

            $forSale = $forSale->concat($stamps);
        }

        if ($q !== '') {
            $forSale = $forSale->filter(fn (array $row) => $searchMatches((string) ($row['title'] ?? ''), $q));
        }

        // sort
        $forSale = match ($sort) {
            'title_desc' => $forSale->sortByDesc(fn ($x) => mb_strtolower($x['title'])),
            'price_asc' => $forSale->sortBy(fn ($x) => $x['price'] ?? 999999999),
            'price_desc' => $forSale->sortByDesc(fn ($x) => $x['price'] ?? -1),
            'created_at_asc' => $forSale->sortBy(fn ($x) => $x['created_at'] ?? now()),
            'created_at_desc' => $forSale->sortByDesc(fn ($x) => $x['created_at'] ?? now()),
            default => $forSale->sortBy(fn ($x) => mb_strtolower($x['title'])),
        };

        $forSale = $forSale->values();

        // paginate collection (simple manual)
        $perPage = 24;
        $page = (int) ($request->query('page', 1));
        $offset = max(0, ($page - 1) * $perPage);

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $forSale->slice($offset, $perPage)->values(),
            $forSale->count(),
            $perPage,
            $page,
            [
                'path' => route('for-sale.index'),
                'query' => $request->query(),
            ]
        );

        return view('for-sale.index', [
            'forSale' => $paginator,
        ]);
    }
}
