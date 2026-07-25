{{-- resources/views/books/show.blade.php --}}

@php
$metaDescription = $book->description
    ? \Illuminate\Support\Str::limit(strip_tags($book->description), 155)
    : "WWII book — {$book->title} — part of the CollectorWWII collection.";
@endphp
<x-layout :title="$book->title" :mainClass="'mx-auto w-full max-w-none px-0 py-8'"
    :metaDescription="$metaDescription" :ogImage="$book->image_url">
    @php
    $images = $book->images()->get();
    $main = $images->first();
    $mainUrl = $main?->url();

    $files = $book->files()->get();
    $pdfs = $files->filter(fn($f) => $f->isPdf())->values();
    @endphp


    {{-- PAGE WRAPPER (écht breed) --}}
    <div class="w-full max-w-[2200px] mx-auto px-4 sm:px-8 lg:px-16 2xl:px-24">

        {{-- Print document header (screen: hidden; print: shown) --}}
        <div class="print-document-header">
            <div class="print-logo">CollectorWWII — Catalogue</div>
            <div class="print-section">Books</div>
            <div class="print-title">{{ $book->title }}</div>
            <div class="print-id">#{{ str_pad($book->id, 4, '0', STR_PAD_LEFT) }} &nbsp;·&nbsp; {{ now()->format('d/m/Y') }}</div>
        </div>

        {{-- Breadcrumbs --}}
        <div class="flex items-center justify-between mb-10 print-hide">
            <nav class="font-mono flex items-center gap-2 text-sm text-white/70">
                <a href="{{ route('home') }}" class="hover:text-white hover:underline">Home</a>
                <span class="opacity-50">›</span>
                <a href="{{ route('books.index') }}" class="hover:text-white hover:underline">Books</a>
                <span class="opacity-50">›</span>
                <span class="text-white font-medium">{{ $book->title }}</span>
            </nav>
            @if(auth()->user()?->isAdmin())
            <a href="{{ route('admin.pdf', ['books', $book->id]) }}"
                class="font-mono text-[10px] tracking-[0.15em] text-white/40 hover:text-white/70 uppercase transition">
                ⬇ PDF
            </a>
            @endif
        </div>

        {{-- CONTENT --}}
        <div class="book-layout">
            {{-- LEFT: MEDIA --}}
            <aside class="book-sticky sticky top-8 space-y-10">
                @include('partials.show-media', [
                    'title' => $book->title,
                    'subtitle' => $book->subtitle ?? null,
                    'images' => $images,
                    'main' => $main,
                    'mainUrl' => $mainUrl,
                    'pdfs' => $pdfs,
                ])
            </aside>

            {{-- RIGHT: INFO (2 aparte containers) --}}
            <div class="space-y-10">
                @if($mainUrl)
                <div class="print-main-image" hidden data-caption="Fotodokumentation">
                    <img src="{{ $mainUrl }}" alt="{{ $book->title }}">
                </div>
                @endif
                {{-- Publieke INFO card --}}
                <section class="bg-sage text-white rounded-2xl shadow-lg border border-black/20 overflow-hidden">
                    <div class="px-6 py-3 border-b border-black/25 bg-black/15">
                        <p class="font-stencil text-[10px] uppercase tracking-[0.25em] text-khaki/60">Feldbericht · Objektakte</p>
                    </div>
                    <div class="px-6 py-4">
                        <dl>
                            @if($book->condition)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Condition</dt>
                                <dd class="text-sm text-white/90">{{ $book->condition }}</dd>
                            </div>
                            @endif
                            @if($book->authors->count())
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Author(s)</dt>
                                <dd class="text-sm text-white/90">{{ $book->authors->pluck('name')->implode(', ') }}</dd>
                            </div>
                            @endif

                            @if($book->isbn)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">ISBN</dt>
                                <dd class="text-sm text-white/90">{{ $book->isbn }}</dd>
                            </div>
                            @endif

                            @if($book->topic)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Topic</dt>
                                <dd class="text-sm text-white/90">{{ $book->topic->name }}</dd>
                            </div>
                            @endif

                            @if($book->publisher_name)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Publisher</dt>
                                <dd class="text-sm text-white/90">{{ $book->publisher_name }}</dd>
                            </div>
                            @endif

                            @if($book->copyright_year)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Year</dt>
                                <dd class="text-sm text-white/90">{{ $book->copyright_year }}</dd>
                            </div>
                            @endif

                            @if($book->translator)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Translator</dt>
                                <dd class="text-sm text-white/90">{{ $book->translator }}</dd>
                            </div>
                            @endif

                            @if($book->issue_number)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Issue Number</dt>
                                <dd class="text-sm text-white/90">{{ $book->issue_number }}</dd>
                            </div>
                            @endif

                            @if($book->issue_year)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Issue Year</dt>
                                <dd class="text-sm text-white/90">{{ $book->issue_year }}</dd>
                            </div>
                            @endif

                            @if($book->series)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Series</dt>
                                <dd class="text-sm text-white/90">
                                    {{ $book->series->name }}
                                    @if($book->series_number)
                                    <span class="text-white/70">#{{ $book->series_number }}</span>
                                    @endif
                                </dd>
                            </div>
                            @endif

                            @if($book->cover)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Cover</dt>
                                <dd class="text-sm text-white/90">{{ $book->cover->name }}</dd>
                            </div>
                            @endif

                            @if($book->pages)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Pages</dt>
                                <dd class="text-sm text-white/90">{{ $book->pages }}</dd>
                            </div>
                            @endif

                            @if($book->title_first_edition)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Title (First Edition)</dt>
                                <dd class="text-sm text-white/90">{{ $book->title_first_edition }}</dd>
                            </div>
                            @endif

                            @if($book->subtitle_first_edition)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Subtitle (First Ed.)</dt>
                                <dd class="text-sm text-white/90">{{ $book->subtitle_first_edition }}</dd>
                            </div>
                            @endif

                            @if($book->publisher_first_issue)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Publisher (First Ed.)</dt>
                                <dd class="text-sm text-white/90">{{ $book->publisher_first_issue }}</dd>
                            </div>
                            @endif

                            @if($book->copyright_year_first_issue)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Copyright (First Ed.)</dt>
                                <dd class="text-sm text-white/90">{{ $book->copyright_year_first_issue }}</dd>
                            </div>
                            @endif

                            @if($book->for_sale)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">For sale</dt>
                                <dd class="text-sm text-white/90"><span class="inline-block bg-khaki/20 text-khaki px-2 py-0.5 rounded text-xs font-mono">Ja</span></dd>
                            </div>
                            @endif

                            @if($book->selling_price !== null && $book->for_sale)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Selling price</dt>
                                <dd class="text-sm text-white/90">€ {{ number_format($book->selling_price, 2, ',', '.') }}</dd>
                            </div>
                            @endif

                            @if(!empty($book->description))
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Description</dt>
                                <dd class="text-sm text-white/90 whitespace-pre-line">{{ $book->description }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </section>

                {{-- Admin INFO card (aparte container) --}}
                @canany(['update','delete'], $book)
                <section class="relative bg-sage text-white rounded-2xl shadow-lg border border-black/20 overflow-hidden">
                    <div class="pointer-events-none select-none absolute inset-0 flex items-center justify-center overflow-hidden rounded-2xl">
                        <span class="font-stencil text-[96px] font-black text-red-900/[0.07] tracking-[0.2em] rotate-[-20deg]">GEHEIM</span>
                    </div>
                    <div class="px-6 py-3 border-b border-black/25 bg-black/15">
                        <p class="font-stencil text-[10px] uppercase tracking-[0.25em] text-khaki/60">Geheimakte · Verwaltung</p>
                    </div>
                    <div class="px-6 py-4">
                        <dl>
                            @if($book->purchase_date)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Purchase date</dt>
                                <dd class="text-sm text-white/90">{{ $book->purchase_date->format('d/m/Y') }}</dd>
                            </div>
                            @endif

                            @if($book->purchase_price !== null)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Purchase price</dt>
                                <dd class="text-sm text-white/90">{{ number_format($book->purchase_price, 2, ',', '.') }}</dd>
                            </div>
                            @endif

                            @if($book->origin)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Purchase origin</dt>
                                <dd class="text-sm text-white/90">{{ $book->origin->name }}</dd>
                            </div>
                            @endif

                            @if($book->weight)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Weight (grams)</dt>
                                <dd class="text-sm text-white/90">{{ $book->weight }}</dd>
                            </div>
                            @endif

                            @if($book->width || $book->height || $book->thickness)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Dimensions (W×H×T)</dt>
                                <dd class="text-sm text-white/90">{{ $book->width ?: '—' }} x {{ $book->height ?: '—' }} x {{ $book->thickness ?: '—' }}</dd>
                            </div>
                            @endif

                            @if($book->location)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Location</dt>
                                <dd class="text-sm text-white/90">{{ $book->location->name }}</dd>
                            </div>
                            @endif

                            @if($book->notes)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Notes</dt>
                                <dd class="text-sm text-white/90 whitespace-pre-line">{{ $book->notes }}</dd>
                            </div>
                            @endif
                            @if($book->sold_at)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Sold on</dt>
                                <dd class="text-sm text-white/90">{{ $book->sold_at->format('d/m/Y') }}</dd>
                            </div>
                            @endif
                            @if($book->sold_price !== null)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Sold price</dt>
                                <dd class="text-sm text-white/90">€ {{ number_format($book->sold_price, 2, ',', '.') }}</dd>
                            </div>
                            @endif
                        </dl>

                        <div class="mt-6 pt-4 border-t border-khaki/20 flex flex-wrap items-center justify-end gap-3">
                            @can('update', $book)
                            <a href="{{ route('admin.books.edit', $book) }}"
                                class="inline-flex items-center gap-2 rounded-md border border-white/20 bg-white/10 px-4 py-2 text-sm text-white hover:bg-white/15 transition">
                                Edit book
                            </a>
                            @endcan

                            @can('delete', $book)
                            <form method="POST"
                                action="{{ route('admin.books.destroy', $book) }}"
                                onsubmit="return confirm('Delete this book? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-md border border-red-400/30 bg-red-500/10 px-4 py-2 text-sm text-red-100 hover:bg-red-500/20 transition">
                                    Delete book
                                </button>
                            </form>
                            @endcan
                        </div>
                    </div>
                </section>
                @endcanany
            </div>
        </div>


        @if($previousBook || $nextBook)
        <div class="mb-10 mt-10 flex items-center justify-between gap-3 print-hide">
            {{-- Previous --}}
            <div class="w-1/2">
                @if($previousBook)
                <a href="{{ route('books.show', $previousBook) }}"
                    class="group inline-flex w-full items-center gap-3 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white hover:bg-white/10 transition">
                    <span class="text-white/70 group-hover:text-white">←</span>
                    <span class="min-w-0">
                        <span class="block text-xs text-white/60">Previous</span>
                        <span class="block truncate font-semibold">{{ $previousBook->title }}</span>
                    </span>
                </a>
                @endif
            </div>

            {{-- Next --}}
            <div class="w-1/2 text-right">
                @if($nextBook)
                <a href="{{ route('books.show', $nextBook) }}"
                    class="group inline-flex w-full justify-end items-center gap-3 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white hover:bg-white/10 transition">
                    <span class="min-w-0 text-right">
                        <span class="block text-xs text-white/60">Next</span>
                        <span class="block truncate font-semibold">{{ $nextBook->title }}</span>
                    </span>
                    <span class="text-white/70 group-hover:text-white">→</span>
                </a>
                @endif
            </div>
        </div>
        @endif
    </div>
</x-layout>
