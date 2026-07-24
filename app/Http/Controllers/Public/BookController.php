<?php

// app/Http/Controllers/Public/BookController.php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookCover;
use App\Models\BookSeries;
use App\Models\BookTopic;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $topics = BookTopic::orderBy('name')->get();
        $series = BookSeries::orderBy('name')->get();
        $covers = BookCover::orderBy('name')->get();

        $booksQuery = Book::query()->with(['authors', 'images']);

        // SEARCH
        if ($request->filled('search')) {
            $s = trim($request->input('search'));

            $booksQuery->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                    ->orWhere('subtitle', 'like', "%{$s}%")
                    ->orWhereHas('authors', function ($qa) use ($s) {
                        $qa->where('name', 'like', "%{$s}%");
                    });
            });
        }

        // TOPIC filter (PAS DIT AAN indien jouw relatie anders heet)
        if ($request->filled('topic')) {
            $topicId = (int) $request->input('topic');

            // Meest voorkomend: many-to-many relatie 'topics'
            $booksQuery->where('topic_id', $topicId);
            // Als jouw relatie singular is 'topic' (belongsTo) met topic_id kolom, gebruik dan i.p.v. bovenstaande:
            // $booksQuery->where('topic_id', $topicId);
        }

        // SERIES filter (PAS AAN)
        if ($request->filled('series')) {
            $seriesId = (int) $request->input('series');

            // belongsTo met series_id kolom:
            $booksQuery->where('series_id', $seriesId);

            // many-to-many alternatief:
            // $booksQuery->whereHas('series', fn ($q) => $q->where('book_series.id', $seriesId));
        }

        // COVER filter (PAS AAN)
        if ($request->filled('cover')) {
            $coverId = (int) $request->input('cover');

            // belongsTo met cover_id kolom:
            $booksQuery->where('cover_id', $coverId);

            // many-to-many alternatief:
            // $booksQuery->whereHas('covers', fn ($q) => $q->where('book_covers.id', $coverId));
        }

        // FOR SALE filter
        if ($request->filled('for_sale')) {
            $booksQuery->where('for_sale', (bool) $request->input('for_sale'));
        }

        // SORT (whitelist + echte author sort)
        $sort = $request->input('sort', 'title_asc');

        switch ($sort) {
            case 'title_desc':
                $booksQuery->orderBy('title', 'desc');
                break;

            case 'created_at_asc': // Newest first
                $booksQuery->orderBy('created_at', 'desc');
                break;

            case 'created_at_desc': // Oldest first
                $booksQuery->orderBy('created_at', 'asc');
                break;

            case 'author_asc':
            case 'author_desc':
                // Sort op "eerste auteur" (alfabetisch). Dit werkt zonder duplicaten via subquery.
                $dir = $sort === 'author_desc' ? 'desc' : 'asc';

                $booksQuery->orderBy(
                    \App\Models\Author::select('name')
                        ->join('book_authors', 'authors.id', '=', 'book_authors.author_id')
                        ->whereColumn('book_authors.book_id', 'books.id')
                        ->orderBy('name', 'asc')
                        ->limit(1),
                    $dir
                )->orderBy('title', 'asc'); // stabiele secondary sort
                break;

            case 'title_asc':
            default:
                $booksQuery->orderBy('books.id', 'asc');
                break;
        }

        // paginate lager (UX + performance)
        $books = $booksQuery->paginate(204)->withQueryString();

        return view('books.index', compact('books', 'topics', 'series', 'covers'));
    }

    public function show(Book $book)
    {
        $book->load([
            'authors',
            'topic',
            'series',
            'cover',
            'location',
            'origin',
            'files',
            'images',
            'mainImage',
        ]);

        $previousBook = Book::where('id', '<', $book->id)->orderByDesc('id')->first();
        $nextBook = Book::where('id', '>', $book->id)->orderBy('id')->first();

        return view('books.show', compact('book', 'previousBook', 'nextBook'));
    }
}
