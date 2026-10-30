{{-- resources/views/postcards/index.blade.php --}}
@php
    $title = $postcards->currentPage() > 1 ? 'Postcards — Page '.$postcards->currentPage().' — CollectorWWII' : 'Postcards — CollectorWWII';
@endphp
<x-layout :title="$title"
    metaDescription="Browse historical postcards and wartime correspondence from the CollectorWWII collection."
    :mainClass="'w-full px-0 py-0'">
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

            <aside id="postcards-filter-drawer" x-ref="filterDrawer" x-cloak
                   :class="filtersOpen ? 'translate-x-0' : '-translate-x-full'"
                   :inert="isMobile && !filtersOpen"
                   :aria-hidden="isMobile && !filtersOpen ? 'true' : 'false'"
                   :role="isMobile && filtersOpen ? 'dialog' : null"
                   :aria-modal="isMobile && filtersOpen ? 'true' : null"
                   :aria-labelledby="isMobile && filtersOpen ? 'postcards-filter-heading' : null"
                   @keydown="trapDrawerFocus($event)"
                   class="fixed inset-y-0 left-0 w-[280px] z-50 flex flex-col
                          lg:relative lg:w-auto lg:translate-x-0 lg:z-auto
                          lg:sticky lg:self-start lg:top-[var(--header-h,113px)] lg:h-[calc(100vh_-_var(--header-h,113px))]
                          transition-transform duration-300 ease-in-out
                          bg-sage border-r border-black/20 text-white">
                {{-- Mobile drawer header --}}
                <div class="lg:hidden flex items-center justify-between px-4 py-3 border-b border-black/20">
                    <h2 id="postcards-filter-heading" class="font-stencil text-[11px] uppercase tracking-[0.2em] text-white/95">Filters</h2>
                    <button type="button" aria-label="Close filters" @click="closeFilters()" class="text-white/80 hover:text-white">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="flex-1 min-h-0 overflow-y-auto px-4 pb-4">

                    {{-- COUNTRIES --}}
                    @php
                    $countriesLimit = 5;
                    $hasMoreCountries = $countries->count() > $countriesLimit;
                    $countriesOpenByDefault = request()->filled('country_id');
                    $activeCountryId = (int) request('country_id');
                    $countriesMoreCount = max(0, $countries->count() - $countriesLimit);
                    @endphp

                    <div x-data="{ countriesOpen: {{ $countriesOpenByDefault ? 'true' : 'false' }} }" class="mt-0">
                        <div class="sticky top-0 z-10 -mx-4 px-4 py-2 bg-sage border-b border-black/20 shadow-sm">
                            <div class="flex items-center justify-between">
                                <h2 class="font-stencil text-[11px] uppercase tracking-[0.2em] text-white/95">Countries</h2>
                                @if($hasMoreCountries)
                                <button type="button" @click="countriesOpen = !countriesOpen" class="font-mono text-[10px] tracking-[0.1em] text-white/95 hover:text-white uppercase">
                                    <span x-text="countriesOpen ? 'Less' : 'More'"></span>
                                </button>
                                @endif
                            </div>
                            @if($hasMoreCountries)<div x-show="!countriesOpen" class="mt-1 text-[11px] text-white/95">+ {{ $countriesMoreCount }} more</div>@endif
                        </div>
                        <ul class="mt-2 text-sm space-y-1">
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route('postcards.index', collect(request()->query())->except(['country_id','page'])->all()) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline {{ !request()->filled('country_id') ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">All</a>
                            </li>
                            @foreach ($countries->take($countriesLimit) as $c)
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route('postcards.index', array_merge(request()->query(), ['country_id' => $c->id, 'page' => 1])) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline {{ $activeCountryId === (int)$c->id ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">{{ $c->name }}</a>
                            </li>
                            @endforeach
                            @if($hasMoreCountries)<li x-show="!countriesOpen" class="px-2 py-1 text-white/95 select-none cursor-pointer hover:text-white" @click="countriesOpen = true">…</li>@endif
                        </ul>
                        @if($hasMoreCountries)
                        <ul x-show="countriesOpen" x-collapse class="text-sm space-y-1 mt-1">
                            @foreach ($countries->skip($countriesLimit) as $c)
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route('postcards.index', array_merge(request()->query(), ['country_id' => $c->id, 'page' => 1])) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline {{ $activeCountryId === (int)$c->id ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">{{ $c->name }}</a>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>

                    {{-- POSTCARD TYPES --}}
                    @php
                    $typesLimit = 5;
                    $hasMoreTypes = $postcardTypes->count() > $typesLimit;
                    $typesOpenByDefault = request()->filled('postcard_type_id');
                    $activeTypeId = (int) request('postcard_type_id');
                    $typesMoreCount = max(0, $postcardTypes->count() - $typesLimit);
                    @endphp

                    <div x-data="{ typesOpen: {{ $typesOpenByDefault ? 'true' : 'false' }} }" class="mt-6">
                        <div class="sticky top-0 z-10 -mx-4 px-4 py-2 bg-sage border-b border-black/20 shadow-sm">
                            <div class="flex items-center justify-between">
                                <h2 class="font-stencil text-[11px] uppercase tracking-[0.2em] text-white/95">Types</h2>
                                @if($hasMoreTypes)
                                <button type="button" @click="typesOpen = !typesOpen" class="font-mono text-[10px] tracking-[0.1em] text-white/95 hover:text-white uppercase">
                                    <span x-text="typesOpen ? 'Less' : 'More'"></span>
                                </button>
                                @endif
                            </div>
                            @if($hasMoreTypes)<div x-show="!typesOpen" class="mt-1 text-[11px] text-white/95">+ {{ $typesMoreCount }} more</div>@endif
                        </div>
                        <ul class="mt-2 text-sm space-y-1">
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route('postcards.index', collect(request()->query())->except(['postcard_type_id','page'])->all()) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline {{ !request()->filled('postcard_type_id') ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">All</a>
                            </li>
                            @foreach ($postcardTypes->take($typesLimit) as $t)
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route('postcards.index', array_merge(request()->query(), ['postcard_type_id' => $t->id, 'page' => 1])) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline {{ $activeTypeId === (int)$t->id ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">{{ $t->name }}</a>
                            </li>
                            @endforeach
                            @if($hasMoreTypes)<li x-show="!typesOpen" class="px-2 py-1 text-white/95 select-none cursor-pointer hover:text-white" @click="typesOpen = true">…</li>@endif
                        </ul>
                        @if($hasMoreTypes)
                        <ul x-show="typesOpen" x-collapse class="text-sm space-y-1 mt-1">
                            @foreach ($postcardTypes->skip($typesLimit) as $t)
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route('postcards.index', array_merge(request()->query(), ['postcard_type_id' => $t->id, 'page' => 1])) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline {{ $activeTypeId === (int)$t->id ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">{{ $t->name }}</a>
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
                                <a href="{{ route('postcards.index', collect(request()->query())->except(['for_sale','page'])->all()) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline {{ !request()->filled('for_sale') ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">All</a>
                            </li>
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route('postcards.index', array_merge(request()->query(), ['for_sale' => 1, 'page' => 1])) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline {{ $forSale === '1' ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">For sale</a>
                            </li>
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route('postcards.index', array_merge(request()->query(), ['for_sale' => 0, 'page' => 1])) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline {{ $forSale === '0' ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">Not for sale</a>
                            </li>
                        </ul>
                    </div>

                </div>
            </aside>

            <div class="min-w-0 pr-4 pl-0">
                <div class="pt-2">
                    <div class="grid grid-cols-1 lg:grid-cols-[240px_1fr] gap-4 items-center">
                        <div class="flex items-center justify-between">
                            <nav aria-label="Breadcrumb" class="flex items-center pl-1 space-x-2 font-mono text-[11px] tracking-[0.15em] text-white/95 uppercase">
                                <a href="{{ route('home') }}" class="pr-2">Home</a> <span aria-hidden="true">&gt;</span>
                                <h1 aria-current="page" class="inline text-white/95 font-mono text-[11px] tracking-[0.1em] uppercase">Postcards</h1>
                            </nav>
                            <button type="button" x-ref="filterTrigger" aria-controls="postcards-filter-drawer" :aria-expanded="filtersOpen.toString()" @click="openFilters()"
                                    class="lg:hidden inline-flex items-center gap-2 rounded-md bg-white/10 px-3 py-2 text-sm text-white hover:bg-white/20">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                                FILTER
                            </button>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                            <form method="GET" action="{{ route('postcards.index') }}" class="flex">
                                @if(request()->filled('country_id')) <input type="hidden" name="country_id" value="{{ request('country_id') }}"> @endif
                                @if(request()->filled('postcard_type_id')) <input type="hidden" name="postcard_type_id" value="{{ request('postcard_type_id') }}"> @endif
                                @if(request()->filled('for_sale')) <input type="hidden" name="for_sale" value="{{ request('for_sale') }}"> @endif
                                <select name="sort" aria-label="Sort postcards" class="rounded-md border border-black/30 bg-black/25 text-white px-3 py-2 font-mono text-sm min-w-[150px]" onchange="this.form.submit()">
                                    <option value="" disabled>Sort by</option>
                                    <option value="created_at_asc" {{ request('sort', 'created_at_asc') == 'created_at_asc' ? 'selected' : '' }}>Newest First</option>
                                    <option value="created_at_desc" {{ request('sort', 'created_at_asc') == 'created_at_desc' ? 'selected' : '' }}>Oldest First</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>

                @php
                $q = request()->query();
                $hasFilters = request()->filled('sort') || request()->filled('country_id') || request()->filled('postcard_type_id') || request()->filled('for_sale');
                $remove = fn($key) => route('postcards.index', collect($q)->except([$key, 'page'])->all());
                $clearAll = route('postcards.index');
                $countryName = request()->filled('country_id') ? optional($countries->firstWhere('id', (int) request('country_id')))->name : null;
                $typeName    = request()->filled('postcard_type_id') ? optional($postcardTypes->firstWhere('id', (int) request('postcard_type_id')))->name : null;
                $forSaleName = request()->filled('for_sale') ? (request('for_sale') === '1' ? 'For sale' : 'Not for sale') : null;
                @endphp

                @if($hasFilters)
                <div class="mb-4 flex flex-wrap items-center gap-2 bg-black/20 ring-1 ring-black/30 text-white rounded-xl px-3 py-2">
                    <span class="text-sm opacity-90 mr-1">Active filters:</span>
                    @if(request()->filled('country_id'))
                    <a href="{{ $remove('country_id') }}" class="inline-flex items-center gap-2 text-sm bg-sage hover:bg-[#5a6452] rounded-full px-3 py-1">
                        <span>Country: {{ $countryName ?? 'Unknown' }}</span><span class="text-white/80">×</span>
                    </a>
                    @endif
                    @if(request()->filled('postcard_type_id'))
                    <a href="{{ $remove('postcard_type_id') }}" class="inline-flex items-center gap-2 text-sm bg-sage hover:bg-[#5a6452] rounded-full px-3 py-1">
                        <span>Type: {{ $typeName ?? 'Unknown' }}</span><span class="text-white/80">×</span>
                    </a>
                    @endif
                    @if(request()->filled('for_sale'))
                    <a href="{{ $remove('for_sale') }}" class="inline-flex items-center gap-2 text-sm bg-sage hover:bg-[#5a6452] rounded-full px-3 py-1">
                        <span>Status: {{ $forSaleName }}</span><span class="text-white/80">×</span>
                    </a>
                    @endif
                    <a href="{{ $clearAll }}" class="ml-auto text-sm underline text-white/90 hover:text-white">Clear all</a>
                </div>
                @endif

                @php $total = $postcards->total(); $from = $postcards->firstItem(); $to = $postcards->lastItem(); @endphp
                <div class="mb-3"><p class="text-sm text-white/90">
                    @if($total > 0)
                    Showing <span class="font-semibold text-white">{{ $from }}</span>–<span class="font-semibold text-white">{{ $to }}</span>
                    of <span class="font-semibold text-white">{{ $total }}</span> postcards
                    @else
                    <span class="font-semibold text-white">0</span> postcards found
                    @endif
                </p></div>

                @if($postcards->count() === 0)
                <div class="bg-sage text-white rounded-md p-6">
                    <h3 class="font-stencil text-lg uppercase tracking-[0.15em] text-white/70">No Results</h3>
                    <p class="text-sm text-white/90 mt-2">Try adjusting your filters.</p>
                    <div class="mt-4"><a href="{{ route('postcards.index') }}" class="inline-block rounded-xl bg-black/30 hover:bg-black/40 ring-1 ring-khaki/30 px-4 py-2 font-stencil tracking-[0.15em] text-sm text-white uppercase transition">Clear filters</a></div>
                </div>
                @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 h-full">
                    @foreach ($postcards as $postcard)
                    <a href="{{ route('postcards.show', $postcard) }}" target="_blank"
                        class="collection-card bg-sage text-white p-4 rounded-md shadow-md flex flex-col h-full overflow-hidden">
                        <div class="mb-1 flex-grow h-auto">
                            <p class="font-mono text-[9px] tracking-widest text-white/30 text-right mb-1">#{{ str_pad($postcard->id, 4, '0', STR_PAD_LEFT) }}</p>
                            <h3 class="text-lg font-bold text-center">{{ $postcard->card_title }}</h3>
                            @if($postcard->condition)
                            <p class="font-mono text-[9px] text-khaki/60 text-center mt-0.5 tracking-wider">{{ $postcard->condition }}</p>
                            @endif
                            @if($postcard->for_sale)<div class="flex justify-center mt-2"><span class="font-stencil text-[9px] tracking-[0.15em] text-khaki/65 border border-khaki/35 px-2 py-0.5 rotate-[-6deg] inline-block">ZU VERKAUFEN</span></div>@endif
                        </div>
                        <p class="text-sm text-center text-white/60 border-t border-white/15 py-1 h-20 flex flex-col justify-center">
                            <span class="block">{{ $postcard->country?->name ?? '—' }}</span>
                            <span class="block text-xs text-white/60/90">{{ $postcard->postcardType?->name ?? '—' }}</span>
                        </p>
                        <div class="flex-1 flex justify-center items-center h-80">
                            <img src="{{ $postcard->thumbnail_url ?? asset('images/error-image-not-found.png') }}"
                                alt="{{ $postcard->card_title }}" loading="lazy" decoding="async" class="w-full h-48 object-contain">
                        </div>
                    </a>
                    @endforeach
                </div>
                <div class="text-white text-center mt-4">
                    {{ $postcards->appends(request()->query())->links('pagination::tailwind') }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        (() => {
            const nav = document.getElementById('main-navbar');
            if (!nav) return;
            const setHeaderH = () => { const h = nav.getBoundingClientRect().height || 0; document.documentElement.style.setProperty('--header-h', `${h}px`); };
            setHeaderH(); window.addEventListener('resize', setHeaderH);
            const btn = document.querySelector('[aria-controls="mobile-menu"]');
            if (btn) btn.addEventListener('click', () => setTimeout(setHeaderH, 50));
        })();
    </script>
</x-layout>
