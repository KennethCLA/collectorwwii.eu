<!-- resources/views/books/index.blade.php -->
{{-- This view is shared by the public book list (PublicBookController) and
     the admin book list (Admin\BookController) — the admin route just
     layers admin chrome on top via the layout's route-based header switch.
     Every filter link below must resolve to whichever route rendered this
     page, or admin users get bounced out to the public site the moment
     they click a filter. --}}
@php
    $booksIndexRoute = request()->routeIs('admin.*') ? 'admin.books.index' : 'books.index';
    $isPublicCatalogue = $booksIndexRoute === 'books.index';
    $hasCatalogueFacet = $isPublicCatalogue && request()->hasAny(['search', 'topic', 'series', 'cover', 'for_sale', 'sort']);
    $canonical = $isPublicCatalogue
        ? route('books.index', ! $hasCatalogueFacet && $books->currentPage() > 1 ? ['page' => $books->currentPage()] : [])
        : null;
    $robots = $hasCatalogueFacet ? 'noindex,follow' : null;
    $title = $books->currentPage() > 1 ? 'Books — Page '.$books->currentPage().' — CollectorWWII' : 'Books — CollectorWWII';
@endphp
<x-layout :title="$title"
    metaDescription="Browse the CollectorWWII archive of WWII books, publications and related historical literature."
    :mainClass="'w-full px-0 py-0'" :canonical="$canonical" :robots="$robots">
    <div class="w-full">
        <div x-data="{
            filtersOpen: false,
            isMobile: ! window.matchMedia('(min-width: 1024px)').matches,
            init() {
                const desktopBreakpoint = window.matchMedia('(min-width: 1024px)');
                this.isMobile = ! desktopBreakpoint.matches;
                desktopBreakpoint.addEventListener('change', (event) => {
                    if (! event.matches && this.$refs.filterDrawer?.contains(document.activeElement)) {
                        this.$refs.filterTrigger?.focus();
                    }

                    this.isMobile = ! event.matches;
                    this.filtersOpen = false;
                });
            },
            openFilters() {
                if (! this.isMobile) return;
                this.filtersOpen = true;
                this.$nextTick(() => {
                    const firstControl = this.$refs.filterDrawer.querySelector('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex=&quot;-1&quot;])');
                    firstControl?.focus();
                });
            },
            closeFilters() {
                this.filtersOpen = false;
                this.$nextTick(() => this.$refs.filterTrigger?.focus());
            },
            trapDrawerFocus(event) {
                if (! this.isMobile || ! this.filtersOpen || event.key !== 'Tab') return;
                const focusableSelector = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex=&quot;-1&quot;])';
                const focusable = [...this.$refs.filterDrawer.querySelectorAll(focusableSelector)]
                    .filter((element) => ! element.closest('[inert]') && element.getClientRects().length > 0 && getComputedStyle(element).visibility !== 'hidden');
                if (focusable.length === 0) {
                    event.preventDefault();
                    return;
                }
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (event.shiftKey && (document.activeElement === first || ! this.$refs.filterDrawer.contains(document.activeElement))) {
                    event.preventDefault();
                    last.focus();
                } else if (! event.shiftKey && (document.activeElement === last || ! this.$refs.filterDrawer.contains(document.activeElement))) {
                    event.preventDefault();
                    first.focus();
                }
            }
        }" @keydown.escape.window="if (isMobile && filtersOpen) closeFilters()" class="grid grid-cols-1 lg:grid-cols-[260px_minmax(0,1fr)] lg:gap-4 items-start">

            {{-- Mobile backdrop --}}
            <div x-show="filtersOpen && isMobile" x-transition.opacity @click="closeFilters()"
                 class="fixed inset-0 bg-black/50 z-40 lg:hidden" aria-hidden="true" x-cloak></div>

            {{-- TODO: refactor sidebar filter sections into reusable component once layout is final --}}
            <aside id="books-filter-drawer" x-ref="filterDrawer" x-cloak
                   :class="filtersOpen ? 'translate-x-0' : '-translate-x-full'"
                   :inert="isMobile && !filtersOpen"
                   :aria-hidden="isMobile && !filtersOpen ? 'true' : 'false'"
                   :role="isMobile && filtersOpen ? 'dialog' : null"
                   :aria-modal="isMobile && filtersOpen ? 'true' : null"
                   :aria-labelledby="isMobile && filtersOpen ? 'books-filter-heading' : null"
                   @keydown="trapDrawerFocus($event)"
                   class="fixed inset-y-0 left-0 w-[280px] z-50 flex flex-col
                          lg:relative lg:w-auto lg:translate-x-0 lg:z-auto
                          lg:sticky lg:self-start lg:top-[var(--header-h,113px)] lg:h-[calc(100vh_-_var(--header-h,113px))]
                          transition-transform duration-300 ease-in-out
                          bg-sage border-r border-black/20 text-white">
                {{-- Mobile drawer header --}}
                <div class="lg:hidden flex items-center justify-between px-4 py-3 border-b border-black/20">
                    <h2 id="books-filter-heading" class="font-stencil text-[11px] uppercase tracking-[0.2em] text-white/95">Filters</h2>
                    <button type="button" aria-label="Close filters" @click="closeFilters()" class="text-white/80 hover:text-white">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="flex-1 min-h-0 overflow-y-auto px-4 pb-4">
                    {{-- TOPICS --}}
                    @php
                        $topicsLimit = 5;
                        $activeTopicId = (int) request('topic');
                        $topicFormQuery = collect(request()->query())
                            ->only(['search', 'series', 'cover', 'for_sale', 'sort'])
                            ->filter(fn ($value) => is_scalar($value) && $value !== '')
                            ->all();
                    @endphp

                    <div class="mt-0">
                        <div class="sticky top-0 z-10 -mx-4 px-4 py-2 bg-sage border-b border-black/20 shadow-sm">
                            <h2 class="font-stencil text-[11px] uppercase tracking-[0.2em] text-white/95">Topics</h2>
                        </div>

                        <ul class="mt-2 text-sm space-y-1">
                            @foreach ($topics->take($topicsLimit) as $topic)
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route($booksIndexRoute, array_merge(request()->query(), ['topic' => $topic->id, 'page' => 1])) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline
              {{ $activeTopicId === (int)$topic->id ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">
                                    {{ $topic->name }}
                                </a>
                            </li>
                            @endforeach
                        </ul>

                        <form method="GET" action="{{ route($booksIndexRoute) }}" class="mt-3 space-y-2">
                            @foreach ($topicFormQuery as $name => $value)
                                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                            @endforeach
                            <label for="topic-filter" class="sr-only">All topics</label>
                            <select id="topic-filter" name="topic"
                                class="w-full rounded-md border border-black/30 bg-black/25 px-2 py-2 text-sm text-white">
                                <option value="" @selected(! request()->filled('topic'))>All topics</option>
                                @foreach ($topics as $topic)
                                    <option value="{{ $topic->id }}" @selected($activeTopicId === (int) $topic->id)>{{ $topic->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit"
                                class="w-full rounded-md border border-black/30 bg-black/25 px-3 py-2 font-stencil text-sm uppercase tracking-[0.15em] text-white hover:bg-black/40">
                                Apply
                            </button>
                        </form>
                    </div>

                    {{-- SERIES --}}
                    @php
                        $seriesLimit = 5;
                        $activeSeriesId = (int) request('series');
                        $seriesFormQuery = collect(request()->query())
                            ->only(['search', 'topic', 'cover', 'for_sale', 'sort'])
                            ->filter(fn ($value) => is_scalar($value) && $value !== '')
                            ->all();
                    @endphp

                    <div class="mt-6">
                        <div class="sticky top-0 z-10 -mx-4 px-4 py-2 bg-sage border-b border-black/20 shadow-sm">
                            <h2 class="font-stencil text-[11px] uppercase tracking-[0.2em] text-white/95">Series</h2>
                        </div>

                        <ul class="mt-2 text-sm space-y-1">
                            @foreach ($series->take($seriesLimit) as $serie)
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route($booksIndexRoute, array_merge(request()->query(), ['series' => $serie->id, 'page' => 1])) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline
                   {{ $activeSeriesId === (int)$serie->id ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">
                                    {{ $serie->name }}
                                </a>
                            </li>
                            @endforeach
                        </ul>

                        <form method="GET" action="{{ route($booksIndexRoute) }}" class="mt-3 space-y-2">
                            @foreach ($seriesFormQuery as $name => $value)
                                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                            @endforeach
                            <label for="series-filter" class="sr-only">All series</label>
                            <select id="series-filter" name="series"
                                class="w-full rounded-md border border-black/30 bg-black/25 px-2 py-2 text-sm text-white">
                                <option value="" @selected(! request()->filled('series'))>All series</option>
                                @foreach ($series as $serie)
                                    <option value="{{ $serie->id }}" @selected($activeSeriesId === (int) $serie->id)>{{ $serie->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit"
                                class="w-full rounded-md border border-black/30 bg-black/25 px-3 py-2 font-stencil text-sm uppercase tracking-[0.15em] text-white hover:bg-black/40">
                                Apply
                            </button>
                        </form>
                    </div>

                    {{-- COVERS --}}
                    @php
                    $coversLimit = 5;
                    $hasMoreCovers = $covers->count() > $coversLimit;
                    $coversOpenByDefault = request()->filled('cover');
                    $activeCoverId = (int) request('cover');
                    $coversMoreCount = max(0, $covers->count() - $coversLimit);
                    @endphp

                    <div x-data="{ coversOpen: {{ $coversOpenByDefault ? 'true' : 'false' }} }" class="mt-6">
                        <div class="sticky top-0 z-10 -mx-4 px-4 py-2 bg-sage border-b border-black/20 shadow-sm">
                            <div class="flex items-center justify-between">
                                <h2 class="font-stencil text-[11px] uppercase tracking-[0.2em] text-white/95">Covers</h2>

                                @if($hasMoreCovers)
                                <button type="button"
                                    @click="coversOpen = !coversOpen"
                                    class="font-mono text-[10px] tracking-[0.1em] text-white/95 hover:text-white uppercase">
                                    <span x-text="coversOpen ? 'Less' : 'More'"></span>
                                </button>
                                @endif
                            </div>

                            @if($hasMoreCovers)
                            <div x-show="!coversOpen" class="mt-1 text-[11px] text-white/95">
                                + {{ $coversMoreCount }} more
                            </div>
                            @endif
                        </div>

                        <ul class="mt-2 text-sm space-y-1">
                            @foreach ($covers->take($coversLimit) as $cover)
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route($booksIndexRoute, array_merge(request()->query(), ['cover' => $cover->id, 'page' => 1])) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline
                   {{ $activeCoverId === (int)$cover->id ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">
                                    {{ $cover->name }}
                                </a>
                            </li>
                            @endforeach

                            @if($hasMoreCovers)
                            <li x-show="!coversOpen" class="px-2 py-1 text-white/95 select-none cursor-pointer hover:text-white"
                                @click="coversOpen = true">…</li>
                            @endif
                        </ul>

                        @if($hasMoreCovers)
                        <ul x-show="coversOpen" x-collapse class="text-sm space-y-1 mt-1">
                            @foreach ($covers->skip($coversLimit) as $cover)
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route($booksIndexRoute, array_merge(request()->query(), ['cover' => $cover->id, 'page' => 1])) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline
                       {{ $activeCoverId === (int)$cover->id ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">
                                    {{ $cover->name }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                    {{-- FOR SALE --}}
                    @php $forSale = request('for_sale', null); @endphp

                    <div class="mt-6">
                        <div class="sticky top-0 z-10 -mx-4 px-4 py-2 bg-sage border-b border-black/20 shadow-sm">
                            <h2 class="font-stencil text-[11px] uppercase tracking-[0.2em] text-white/95">Status</h2>
                        </div>
                        <ul class="mt-2 text-sm space-y-1">
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route($booksIndexRoute, collect(request()->query())->except(['for_sale','page'])->all()) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline
                                        {{ !request()->filled('for_sale') ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">
                                    All
                                </a>
                            </li>
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route($booksIndexRoute, array_merge(request()->query(), ['for_sale' => 1, 'page' => 1])) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline
                                        {{ $forSale === '1' ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">
                                    For sale
                                </a>
                            </li>
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route($booksIndexRoute, array_merge(request()->query(), ['for_sale' => 0, 'page' => 1])) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline
                                        {{ $forSale === '0' ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">
                                    Not for sale
                                </a>
                            </li>
                        </ul>
                    </div>

                </div>
            </aside>

            <!-- Books Grid -->
            <div class="min-w-0 pr-4 pl-0">
                {{-- Header row (breadcrumb + sort/search) --}}
                <div class="pt-2">
                    <div class="grid grid-cols-1 lg:grid-cols-[240px_1fr] gap-4 items-center">
                        {{-- Left column: breadcrumb + mobile filter button --}}
                        <div class="flex items-center justify-between">
                            <nav aria-label="Breadcrumb" class="flex items-center pl-1 space-x-2 font-mono text-[11px] tracking-[0.15em] text-white/95 uppercase">
                                <a href="{{ route('home') }}" class="pr-2">Home</a> <span aria-hidden="true">&gt;</span>
                                <h1 aria-current="page" class="inline text-white/95 font-mono text-[11px] tracking-[0.1em] uppercase">Books</h1>
                            </nav>
                            <button type="button" x-ref="filterTrigger" aria-controls="books-filter-drawer"
                                    :aria-expanded="filtersOpen.toString()" @click="openFilters()"
                                    class="lg:hidden inline-flex items-center gap-2 rounded-md bg-white/10 px-3 py-2 text-sm text-white hover:bg-white/20">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                                FILTER
                            </button>
                        </div>

                        {{-- Right column: sort + search --}}
                        <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                            {{-- sort --}}
                            <form method="GET" action="{{ route($booksIndexRoute) }}" class="flex">
                                @if(request()->filled('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                @if(request()->filled('topic')) <input type="hidden" name="topic" value="{{ request('topic') }}"> @endif
                                @if(request()->filled('series')) <input type="hidden" name="series" value="{{ request('series') }}"> @endif
                                @if(request()->filled('cover')) <input type="hidden" name="cover" value="{{ request('cover') }}"> @endif
                                @if(request()->filled('for_sale')) <input type="hidden" name="for_sale" value="{{ request('for_sale') }}"> @endif

                                <select name="sort" aria-label="Sort books"
                                    class="rounded-md border border-black/30 bg-black/25 text-white px-3 py-2 font-mono text-sm min-w-[150px]"
                                    onchange="this.form.submit()">
                                    <option value="" disabled>Sort by</option>
                                    <option value="title_asc" {{ request('sort', 'created_at_asc') == 'title_asc' ? 'selected' : '' }}>Title (A-Z)</option>
                                    <option value="title_desc" {{ request('sort', 'created_at_asc') == 'title_desc' ? 'selected' : '' }}>Title (Z-A)</option>
                                    <option value="author_asc" {{ request('sort', 'created_at_asc') == 'author_asc' ? 'selected' : '' }}>Author (A-Z)</option>
                                    <option value="author_desc" {{ request('sort', 'created_at_asc') == 'author_desc' ? 'selected' : '' }}>Author (Z-A)</option>
                                    <option value="created_at_asc" {{ request('sort', 'created_at_asc') == 'created_at_asc' ? 'selected' : '' }}>Newest First</option>
                                    <option value="created_at_desc" {{ request('sort', 'created_at_asc') == 'created_at_desc' ? 'selected' : '' }}>Oldest First</option>
                                </select>
                            </form>

                            {{-- search --}}
                            <form method="GET" action="{{ route($booksIndexRoute) }}" class="flex items-center gap-2 w-full sm:w-auto">
                                @if(request()->filled('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                                @if(request()->filled('topic')) <input type="hidden" name="topic" value="{{ request('topic') }}"> @endif
                                @if(request()->filled('series')) <input type="hidden" name="series" value="{{ request('series') }}"> @endif
                                @if(request()->filled('cover')) <input type="hidden" name="cover" value="{{ request('cover') }}"> @endif
                                @if(request()->filled('for_sale')) <input type="hidden" name="for_sale" value="{{ request('for_sale') }}"> @endif

                                <div class="relative w-full sm:w-[320px]">
                                    <input type="text"
                                        name="search"
                                        aria-label="Search books"
                                        placeholder="Search books..."
                                        value="{{ request('search') }}"
                                        class="rounded-md border border-black/30 bg-black/25 text-white placeholder-white/40 font-mono text-sm px-3 py-2 pr-10 w-full"
                                        id="searchInput"
                                        autocomplete="off" />

                                    <button type="button"
                                        id="clearSearchBtn"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-white/70 hover:text-white {{ request()->filled('search') ? '' : 'hidden' }}"
                                        aria-label="Clear search"
                                        title="Clear search">×</button>
                                </div>

                                <button type="submit"
                                    class="rounded-md border border-black/30 bg-black/25 hover:bg-black/40 text-white font-stencil tracking-[0.15em] text-sm px-3 py-2 uppercase transition">
                                    Search
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                {{-- Active filters bar --}}
                @php
                $q = request()->query();

                $hasFilters = request()->filled('search')
                || request()->filled('sort')
                || request()->filled('topic')
                || request()->filled('series')
                || request()->filled('cover')
                || request()->filled('for_sale');

                $remove = fn($key) => route($booksIndexRoute, collect($q)->except([$key, 'page'])->all());
                $clearAll = route($booksIndexRoute);

                // Map sort keys -> human labels
                $sortLabels = [
                'title_asc' => 'Title (A–Z)',
                'title_desc' => 'Title (Z–A)',
                'author_asc' => 'Author (A–Z)',
                'author_desc' => 'Author (Z–A)',
                'created_at_asc' => 'Newest first',
                'created_at_desc' => 'Oldest first',
                ];

                $sortLabel = $sortLabels[request('sort')] ?? request('sort');

                // Resolve ids -> names using collections you already have on the page
                $topicName   = request()->filled('topic')    ? optional($topics->firstWhere('id',  (int) request('topic')))->name   : null;
                $seriesName  = request()->filled('series')   ? optional($series->firstWhere('id',  (int) request('series')))->name  : null;
                $coverName   = request()->filled('cover')    ? optional($covers->firstWhere('id',  (int) request('cover')))->name   : null;
                $forSaleName = request()->filled('for_sale') ? (request('for_sale') === '1' ? 'For sale' : 'Not for sale')         : null;
                @endphp

                @if($hasFilters)
                <div class="mb-4 flex flex-wrap items-center gap-2 bg-black/20 ring-1 ring-black/30 text-white rounded-xl px-3 py-2">
                    <span class="text-sm opacity-90 mr-1">Active filters:</span>

                    @if(request()->filled('search'))
                    <a href="{{ $remove('search') }}"
                        class="inline-flex items-center gap-2 text-sm bg-sage hover:bg-[#5a6452] rounded-full px-3 py-1">
                        <span>Search: “{{ request('search') }}”</span>
                        <span class="text-white/80">×</span>
                    </a>
                    @endif

                    @if(request()->filled('sort'))
                    <a href="{{ $remove('sort') }}"
                        class="inline-flex items-center gap-2 text-sm bg-sage hover:bg-[#5a6452] rounded-full px-3 py-1">
                        <span>Sort: {{ $sortLabel }}</span>
                        <span class="text-white/80">×</span>
                    </a>
                    @endif

                    @if(request()->filled('topic'))
                    <a href="{{ $remove('topic') }}"
                        class="inline-flex items-center gap-2 text-sm bg-sage hover:bg-[#5a6452] rounded-full px-3 py-1">
                        <span>Topic: {{ $topicName ?? 'Unknown' }}</span>
                        <span class="text-white/80">×</span>
                    </a>
                    @endif

                    @if(request()->filled('series'))
                    <a href="{{ $remove('series') }}"
                        class="inline-flex items-center gap-2 text-sm bg-sage hover:bg-[#5a6452] rounded-full px-3 py-1">
                        <span>Series: {{ $seriesName ?? 'Unknown' }}</span>
                        <span class="text-white/80">×</span>
                    </a>
                    @endif

                    @if(request()->filled('cover'))
                    <a href="{{ $remove('cover') }}"
                        class="inline-flex items-center gap-2 text-sm bg-sage hover:bg-[#5a6452] rounded-full px-3 py-1">
                        <span>Cover: {{ $coverName ?? 'Unknown' }}</span>
                        <span class="text-white/80">×</span>
                    </a>
                    @endif

                    @if(request()->filled('for_sale'))
                    <a href="{{ $remove('for_sale') }}"
                        class="inline-flex items-center gap-2 text-sm bg-sage hover:bg-[#5a6452] rounded-full px-3 py-1">
                        <span>Status: {{ $forSaleName ?? 'Unknown' }}</span>
                        <span class="text-white/80">×</span>
                    </a>
                    @endif

                    <a href="{{ $clearAll }}"
                        class="ml-auto text-sm underline text-white/90 hover:text-white">
                        Clear all
                    </a>
                </div>
                @endif

                @php
                $total = $books->total();
                $from = $books->firstItem();
                $to = $books->lastItem();

                $hasAnyFilter = request()->filled('search')
                || request()->filled('sort')
                || request()->filled('topic')
                || request()->filled('series')
                || request()->filled('cover')
                || request()->filled('for_sale');
                @endphp

                <div class="mb-3 flex items-center justify-between">
                    <p class="text-sm text-white/90">
                        @if($total > 0)
                        Showing <span class="font-semibold text-white">{{ $from }}</span>–<span class="font-semibold text-white">{{ $to }}</span>
                        of <span class="font-semibold text-white">{{ $total }}</span> books
                        @else
                        <span class="font-semibold text-white">0</span> books found
                        @endif
                    </p>
                </div>

                @if($books->count() === 0)
                <div class="bg-sage text-white rounded-md p-6">
                    <h3 class="font-stencil text-lg uppercase tracking-[0.15em] text-white/70">No Results</h3>
                    <p class="text-sm text-white/90 mt-2">
                        Try adjusting your search or removing some filters.
                    </p>

                    <div class="mt-4">
                        <a href="{{ route($booksIndexRoute) }}"
                            class="inline-block rounded-xl bg-black/30 hover:bg-black/40 ring-1 ring-khaki/30 px-4 py-2 font-stencil tracking-[0.15em] text-sm text-white uppercase transition">
                            Clear filters
                        </a>
                    </div>
                </div>
                @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 h-full">
                    @foreach ($books as $book)
                    <a href="{{ route('books.show', $book) }}" target="_blank"
                        class="collection-card bg-sage text-white p-4 rounded-md shadow-md flex flex-col h-full overflow-hidden">
                        <div class="mb-1 flex-grow h-auto">
                            <p class="font-mono text-[9px] tracking-widest text-white/30 text-right mb-1">#{{ str_pad($book->id, 4, '0', STR_PAD_LEFT) }}</p>
                            <h3 class="text-lg font-bold text-center">{{ $book->title }}</h3>
                            @if($book->condition)
                            <p class="font-mono text-[9px] text-khaki/60 text-center mt-0.5 tracking-wider">{{ $book->condition }}</p>
                            @endif
                            @if ($book->subtitle)
                            <h5 class="text-sm italic text-center text-white/60">{{ $book->subtitle }}</h5>
                            @endif
                            @if ($book->issue_number)
                            <h5 class="text-xs italic text-center text-white/60 pt-4">
                                {{ $book->issue_number }}
                            </h5>
                            @endif
                            @if ($book->for_sale)
                            <div class="flex justify-center mt-2">
                                <span class="font-stencil text-[9px] tracking-[0.15em] text-khaki/65 border border-khaki/35 px-2 py-0.5 rotate-[-6deg] inline-block">ZU VERKAUFEN</span>
                            </div>
                            @endif
                        </div>

                        <p class="text-sm text-center text-white/60 border-t border-white/15 py-1 h-20">
                            @foreach ($book->authors as $author)
                            {{ $author->name }}@if (!$loop->last), @endif
                            @endforeach
                        </p>

                        <div class="flex-1 flex justify-center items-center h-80">
                            <img
                                src="{{ $book->thumbnail_url ?? asset('images/error-image-not-found.png') }}"
                                alt="{{ $book->title }}"
                                loading="lazy" decoding="async"
                                class="w-full h-48 object-contain">

                        </div>
                    </a>
                    @endforeach
                </div>

                <div class="text-white text-center mt-4">
                    {{ $books->appends(request()->query())->links('pagination::tailwind') }}
                </div>
                @endif

            </div>
        </div>
    </div>

    <script>
        (() => {
            const searchInput = document.getElementById('searchInput');
            const clearBtn = document.getElementById('clearSearchBtn');
            if (!searchInput || !clearBtn) return;

            function toggleClearButton() {
                if (searchInput.value.trim().length > 0) clearBtn.classList.remove('hidden');
                else clearBtn.classList.add('hidden');
            }

            // GEEN autosubmit tijdens typen
            searchInput.addEventListener('input', toggleClearButton);

            // Enter = submit
            searchInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    // laat default submit toe, of doe expliciet:
                    // event.preventDefault();
                    // searchInput.form.submit();
                }
            });

            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                toggleClearButton();
                searchInput.form.submit(); // terug naar lijst zonder search
            });

            toggleClearButton();
        })();
    </script>

</x-layout>
