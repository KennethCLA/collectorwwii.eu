<?php

// app/Http/Controllers/Admin/BookController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesInlineMediaUploads;
use App\Http\Controllers\Admin\Concerns\NormalizesForSaleFields;
use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookCover;
use App\Models\BookSeries;
use App\Models\BookTopic;
use App\Models\Location;
use App\Models\Origin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookController extends Controller
{
    use HandlesInlineMediaUploads;
    use NormalizesForSaleFields;

    public function __construct()
    {
        $this->authorizeResource(Book::class, 'book');
    }

    public function index(Request $request)
    {
        $query = Book::with(['authors', 'images']);

        if ($request->has('topic')) {
            $query->where('topic_id', (int) $request->topic);
        }

        if ($request->has('series')) {
            $query->where('series_id', (int) $request->series);
        }

        if ($request->has('cover')) {
            $query->where('cover_id', (int) $request->cover);
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('authors', function ($subQuery) use ($search) {
                        $subQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('sort')) {
            $sort = $request->input('sort');
            switch ($sort) {
                case 'title_asc':
                    $query->orderBy('title', 'asc');
                    break;
                case 'title_desc':
                    $query->orderBy('title', 'desc');
                    break;
                case 'author_asc':
                    $query->leftJoin('book_authors', 'books.id', '=', 'book_authors.book_id')
                        ->leftJoin('authors', 'authors.id', '=', 'book_authors.author_id')
                        ->orderBy('authors.name', 'asc')
                        ->select('books.*')
                        ->distinct();
                    break;
                case 'author_desc':
                    $query->leftJoin('book_authors', 'books.id', '=', 'book_authors.book_id')
                        ->leftJoin('authors', 'authors.id', '=', 'book_authors.author_id')
                        ->orderBy('authors.name', 'desc')
                        ->select('books.*')
                        ->distinct();
                    break;
                case 'created_at_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'created_at_desc':
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        }

        $books = $query->paginate(500);

        return view('books.index', [
            'books' => $books,
            'topics' => BookTopic::flatTree(),
            'series' => BookSeries::orderBy('name', 'asc')->get(),
            'covers' => BookCover::orderBy('name', 'asc')->get(),
            'locations' => Location::flatTree(),
        ]);
    }

    private function rules(Request $request): array
    {
        return [
            'isbn' => ['nullable', 'string', 'max:32', 'regex:/^[0-9Xx\- ]+$/'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],

            'title_first_edition' => ['nullable', 'string', 'max:255'],
            'subtitle_first_edition' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'translator' => ['nullable', 'string', 'max:255'],
            'pages' => ['nullable', 'integer', 'min:1'],

            'copyright_year' => ['nullable', 'integer', 'min:1000', 'max:'.date('Y')],
            'issue_number' => ['nullable', 'string', 'max:255'],
            'issue_year' => ['nullable', 'integer', 'min:1000', 'max:'.date('Y')],

            'topic_id' => ['nullable', 'exists:book_topics,id'],
            'series_id' => ['nullable', 'exists:book_series,id'],
            'series_number' => ['nullable', 'string', 'max:255'],
            'cover_id' => ['nullable', 'exists:book_covers,id'],

            'copyright_year_first_issue' => ['nullable', 'integer', 'min:1000', 'max:'.date('Y')],
            'publisher_name' => ['nullable', 'string', 'max:255'],
            'publisher_first_issue' => ['nullable', 'string', 'max:255'],

            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'purchase_date' => ['nullable', 'date', 'before_or_equal:today'],
            'origin_id' => ['nullable', 'exists:origins,id'],
            'notes' => ['nullable', 'string'],
            'location_id' => ['nullable', 'exists:locations,id'],

            'for_sale' => ['nullable', 'boolean'],
            'selling_price' => ['nullable', 'numeric', 'min:0', Rule::requiredIf(fn () => $request->boolean('for_sale'))],

            'weight' => ['nullable', 'integer', 'min:0'],
            'width' => ['nullable', 'integer', 'min:0'],
            'height' => ['nullable', 'integer', 'min:0'],
            'thickness' => ['nullable', 'integer', 'min:0'],

            'condition' => ['nullable', 'string', 'max:50'],
            'sold_at' => ['nullable', 'date', 'before_or_equal:today', Rule::requiredIf(fn () => $request->filled('sold_price'))],
            'sold_price' => ['nullable', 'numeric', 'min:0'],

            'authors' => ['required', 'string', 'max:500'],

            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
            'pdfs' => ['nullable', 'array'],
            'pdfs.*' => ['file', 'mimetypes:application/pdf', 'max:51200'],

            'main_image_index' => ['nullable', 'integer', 'min:0'],
            'after_save' => ['nullable', 'in:show,create,index'],
        ];
    }

    private function normalize(array $validated, Request $request): array
    {
        $isbn = trim((string) ($validated['isbn'] ?? ''));
        $isbn = $isbn !== '' ? preg_replace('/[\s-]+/', '', $isbn) : null;
        $validated['isbn'] = $isbn;

        $validated = $this->normalizeForSaleFields($validated, $request);

        return $validated;
    }

    public function create(Request $request)
    {
        $isbn = trim((string) $request->input('isbn', ''));
        $isbnLookupFailed = false;
        $bookData = [];

        if ($isbn !== '') {
            try {
                $response = Http::timeout(5)->get('https://openlibrary.org/api/books', [
                    'bibkeys' => "ISBN:{$isbn}",
                    'jscmd' => 'data',
                    'format' => 'json',
                ]);

                Log::info('Open Library API response', [
                    'isbn' => $isbn,
                    'status' => $response->status(),
                ]);

                if ($response->successful()) {
                    $info = data_get($response->json(), "ISBN:{$isbn}");

                    if (is_array($info)) {
                        $publishDate = data_get($info, 'publish_date');
                        $year = $publishDate && preg_match('/\d{4}/', (string) $publishDate, $m) ? (int) $m[0] : null;

                        $authors = collect(data_get($info, 'authors', []))->pluck('name')->filter();
                        $publishers = collect(data_get($info, 'publishers', []))->pluck('name')->filter();

                        $bookData = [
                            'isbn' => $isbn,
                            'title' => data_get($info, 'title'),
                            'subtitle' => data_get($info, 'subtitle'),
                            'authors' => $authors->isNotEmpty() ? $authors->implode(', ') : null,
                            'publisher_name' => $publishers->first(),
                            'copyright_year' => $year,
                            'pages' => data_get($info, 'number_of_pages'),
                            'description' => null,
                        ];
                    } else {
                        $isbnLookupFailed = true;
                    }
                } else {
                    $isbnLookupFailed = true;
                }
            } catch (\Illuminate\Http\Client\ConnectionException) {
                $isbnLookupFailed = true;
            }
        }

        return view('admin.books.create', [
            'topics' => BookTopic::flatTree(),
            'series' => BookSeries::orderBy('name')->get(),
            'covers' => BookCover::orderBy('name')->get(),
            'locations' => Location::flatTree(),
            'origins' => Origin::flatTree(),
            'isbn' => $isbn,
            'bookData' => $bookData,
            'isbnLookupFailed' => $isbnLookupFailed,
        ]);
    }

    public function store(Request $request)
    {
        $userId = (int) auth()->id();
        $key = "books:create:{$userId}";

        $validated = $request->validate($this->rules($request));
        $validated = $this->normalize($validated, $request);

        if (RateLimiter::tooManyAttempts($key, 10)) {
            abort(429, 'Too many uploads. Please try again later.');
        }
        RateLimiter::hit($key, 60);

        $authorNames = $this->parseAuthorNames((string) $validated['authors']);
        if (count($authorNames) < 1) {
            throw ValidationException::withMessages([
                'authors' => 'Please provide at least one author name.',
            ]);
        }

        $uploadedForCleanup = [];

        try {
            $book = DB::transaction(function () use ($validated, $request, $authorNames, &$uploadedForCleanup) {
                $data = $validated;
                unset($data['images'], $data['pdfs'], $data['authors'], $data['main_image_index'], $data['after_save']);

                /** @var \App\Models\Book $book */
                $book = Book::create($data);

                $this->syncAuthorsByNames($book, $authorNames);

                $this->attachInlineMedia(
                    $book,
                    "books/{$book->id}",
                    $request->file('images', []),
                    $request->file('pdfs', []),
                    (int) $request->input('main_image_index', 0),
                    $uploadedForCleanup,
                );

                return $book;
            });
        } catch (\Throwable $e) {
            foreach ($uploadedForCleanup as [$d, $p]) {
                try {
                    Storage::disk($d)->delete($p);
                } catch (\Throwable $ignore) {
                }
            }
            throw $e;
        }

        $after = $request->input('after_save', 'show');

        return match ($after) {
            'create' => redirect()
                ->route('admin.books.create')
                ->with('success', 'Book saved. You can add another one.'),
            'index' => redirect()
                ->route('admin.books.index')
                ->with('success', 'Book successfully added.'),
            default => redirect()
                ->route('books.show', $book)
                ->with('success', 'Book successfully added.'),
        };
    }

    public function show(Book $book)
    {
        $book->load('images');

        $previousBook = Book::where('id', '<', $book->id)->orderBy('id', 'desc')->first();
        $nextBook = Book::where('id', '>', $book->id)->orderBy('id', 'asc')->first();

        return view('books.show', compact('book', 'previousBook', 'nextBook'));
    }

    public function edit(Book $book)
    {
        $book->load([
            'authors',
            'images',
            'files',
        ]);

        return view('admin.books.edit', [
            'book' => $book,
            'topics' => BookTopic::flatTree(),
            'series' => BookSeries::orderBy('name')->get(),
            'covers' => BookCover::orderBy('name')->get(),
            'locations' => Location::flatTree(),
            'origins' => Origin::flatTree(),
            'bookData' => [],
        ]);
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate($this->rules($request));
        $validated = $this->normalize($validated, $request);

        $authorsRaw = (string) $validated['authors'];

        unset(
            $validated['authors'],
            $validated['images'],
            $validated['pdfs'],
            $validated['main_image_index'],
            $validated['after_save']
        );

        $book->update($validated);

        $names = $this->parseAuthorNames($authorsRaw);
        $this->syncAuthorsByNames($book, $names);

        return redirect()->route('books.show', $book)->with('success', 'Book successfully updated.');
    }

    public function destroy(Book $book)
    {
        $book->load('media');
        $disk = Storage::disk('b2');

        foreach ($book->media as $file) {
            if ($file->path) {
                $disk->delete($file->path);
            }
            $file->delete();
        }

        $book->forceDelete();

        return redirect()->route('admin.books.index')->with('success', 'Book deleted.');
    }

    private function parseAuthorNames(string $authors): array
    {
        return collect(explode(',', $authors))
            ->map(fn ($n) => trim($n))
            ->filter(fn ($n) => $n !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function syncAuthorsByNames(Book $book, array $authorNames): void
    {
        $authorIds = collect($authorNames)
            ->map(fn ($name) => Author::firstOrCreate(['name' => $name])->id)
            ->all();

        $book->authors()->sync($authorIds);
    }
}
