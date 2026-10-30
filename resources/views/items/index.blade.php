{{-- resources/views/items/index.blade.php --}}
@php
    $hasCatalogueFacet = request()->hasAny(['search', 'category_id', 'nationality_id', 'origin_id', 'organization_id', 'for_sale', 'sort']);
    $canonical = route('items.index', ! $hasCatalogueFacet && $items->currentPage() > 1 ? ['page' => $items->currentPage()] : []);
    $robots = $hasCatalogueFacet ? 'noindex,follow' : null;
    $title = $items->currentPage() > 1 ? 'Items — Page '.$items->currentPage().' — CollectorWWII' : 'Items — CollectorWWII';
@endphp
<x-layout :title="$title"
    metaDescription="Browse historical WWII items and militaria documented in the CollectorWWII collection."
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

            {{-- Sidebar --}}
            <aside id="items-filter-drawer" x-ref="filterDrawer" x-cloak
                   :class="filtersOpen ? 'translate-x-0' : '-translate-x-full'"
                   :inert="isMobile && !filtersOpen"
                   :aria-hidden="isMobile && !filtersOpen ? 'true' : 'false'"
                   :role="isMobile && filtersOpen ? 'dialog' : null"
                   :aria-modal="isMobile && filtersOpen ? 'true' : null"
                   :aria-labelledby="isMobile && filtersOpen ? 'items-filter-heading' : null"
                   @keydown="trapDrawerFocus($event)"
                   class="fixed inset-y-0 left-0 w-[280px] z-50 flex flex-col
                          lg:relative lg:w-auto lg:translate-x-0 lg:z-auto
                          lg:sticky lg:self-start lg:top-[var(--header-h,113px)] lg:h-[calc(100vh_-_var(--header-h,113px))]
                          transition-transform duration-300 ease-in-out
                          bg-sage border-r border-black/20 text-white">
                {{-- Mobile drawer header --}}
                <div class="lg:hidden flex items-center justify-between px-4 py-3 border-b border-black/20">
                    <h2 id="items-filter-heading" class="font-stencil text-[11px] uppercase tracking-[0.2em] text-white/95">Filters</h2>
                    <button type="button" aria-label="Close filters" @click="closeFilters()" class="text-white/80 hover:text-white">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="flex-1 min-h-0 overflow-y-auto px-4 pb-4">

                    {{-- CATEGORIES --}}
                    @php
                    $categoriesLimit = 5;
                    $hasMoreCategories = $categories->count() > $categoriesLimit;
                    $categoriesOpenByDefault = request()->filled('category_id');
                    $activeCategoryId = (int) request('category_id');
                    $categoriesMoreCount = max(0, $categories->count() - $categoriesLimit);
                    @endphp

                    <div x-data="{ categoriesOpen: {{ $categoriesOpenByDefault ? 'true' : 'false' }} }" class="mt-0">
                        <div class="sticky top-0 z-10 -mx-4 px-4 py-2 bg-sage border-b border-black/20 shadow-sm">
                            <div class="flex items-center justify-between">
                                <h2 class="font-stencil text-[11px] uppercase tracking-[0.2em] text-white/95">Categories</h2>
                                @if($hasMoreCategories)
                                <button type="button"
                                    @click="categoriesOpen = !categoriesOpen"
                                    class="font-mono text-[10px] tracking-[0.1em] text-white/95 hover:text-white uppercase">
                                    <span x-text="categoriesOpen ? 'Show Less' : 'Show More'"></span>
                                </button>
                                @endif
                            </div>
                            @if($hasMoreCategories)
                            <div x-show="!categoriesOpen" class="mt-1 text-[11px] text-white/95">
                                + {{ $categoriesMoreCount }} more
                            </div>
                            @endif
                        </div>

                        <ul class="mt-2 text-sm space-y-1">
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route('items.index', collect(request()->query())->except(['category_id','page'])->all()) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline
                                        {{ !request()->filled('category_id') ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">
                                    All
                                </a>
                            </li>
                            @foreach ($categories->take($categoriesLimit) as $c)
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route('items.index', array_merge(request()->query(), ['category_id' => $c->id, 'page' => 1])) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline
                                        {{ $activeCategoryId === (int)$c->id ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">
                                    {{ $c->name }}
                                </a>
                            </li>
                            @endforeach
                            @if($hasMoreCategories)
                            <li x-show="!categoriesOpen"
                                class="px-2 py-1 text-white/95 select-none cursor-pointer hover:text-white"
                                @click="categoriesOpen = true">
                                …
                            </li>
                            @endif
                        </ul>

                        @if($hasMoreCategories)
                        <ul x-show="categoriesOpen" x-collapse class="text-sm space-y-1 mt-1">
                            @foreach ($categories->skip($categoriesLimit) as $c)
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route('items.index', array_merge(request()->query(), ['category_id' => $c->id, 'page' => 1])) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline
                                        {{ $activeCategoryId === (int)$c->id ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">
                                    {{ $c->name }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>

                    {{-- NATIONALITIES --}}
                    @php
                    $nationalitiesLimit = 5;
                    $hasMoreNationalities = $nationalities->count() > $nationalitiesLimit;
                    $nationalitiesOpenByDefault = request()->filled('nationality_id');
                    $activeNationalityId = (int) request('nationality_id');
                    $nationalitiesMoreCount = max(0, $nationalities->count() - $nationalitiesLimit);
                    @endphp

                    <div x-data="{ nationalitiesOpen: {{ $nationalitiesOpenByDefault ? 'true' : 'false' }} }" class="mt-6">
                        <div class="sticky top-0 z-10 -mx-4 px-4 py-2 bg-sage border-b border-black/20 shadow-sm">
                            <div class="flex items-center justify-between">
                                <h2 class="font-stencil text-[11px] uppercase tracking-[0.2em] text-white/95">Nationalities</h2>
                                @if($hasMoreNationalities)
                                <button type="button"
                                    @click="nationalitiesOpen = !nationalitiesOpen"
                                    class="font-mono text-[10px] tracking-[0.1em] text-white/95 hover:text-white uppercase">
                                    <span x-text="nationalitiesOpen ? 'Less' : 'More'"></span>
                                </button>
                                @endif
                            </div>
                            @if($hasMoreNationalities)
                            <div x-show="!nationalitiesOpen" class="mt-1 text-[11px] text-white/95">
                                + {{ $nationalitiesMoreCount }} more
                            </div>
                            @endif
                        </div>

                        <ul class="mt-2 text-sm space-y-1">
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route('items.index', collect(request()->query())->except(['nationality_id','page'])->all()) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline
                                        {{ !request()->filled('nationality_id') ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">
                                    All
                                </a>
                            </li>
                            @foreach ($nationalities->take($nationalitiesLimit) as $n)
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route('items.index', array_merge(request()->query(), ['nationality_id' => $n->id, 'page' => 1])) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline
                                        {{ $activeNationalityId === (int)$n->id ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">
                                    {{ $n->name }}
                                </a>
                            </li>
                            @endforeach
                            @if($hasMoreNationalities)
                            <li x-show="!nationalitiesOpen"
                                class="px-2 py-1 text-white/95 select-none cursor-pointer hover:text-white"
                                @click="nationalitiesOpen = true">
                                …
                            </li>
                            @endif
                        </ul>

                        @if($hasMoreNationalities)
                        <ul x-show="nationalitiesOpen" x-collapse class="text-sm space-y-1 mt-1">
                            @foreach ($nationalities->skip($nationalitiesLimit) as $n)
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route('items.index', array_merge(request()->query(), ['nationality_id' => $n->id, 'page' => 1])) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline
                                        {{ $activeNationalityId === (int)$n->id ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">
                                    {{ $n->name }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>

                    {{-- ORIGINS --}}
                    @php
                    $originsLimit = 5;
                    $hasMoreOrigins = $origins->count() > $originsLimit;
                    $originsOpenByDefault = request()->filled('origin_id');
                    $activeOriginId = (int) request('origin_id');
                    $originsMoreCount = max(0, $origins->count() - $originsLimit);
                    @endphp

                    <div x-data="{ originsOpen: {{ $originsOpenByDefault ? 'true' : 'false' }} }" class="mt-6">
                        <div class="sticky top-0 z-10 -mx-4 px-4 py-2 bg-sage border-b border-black/20 shadow-sm">
                            <div class="flex items-center justify-between">
                                <h2 class="font-stencil text-[11px] uppercase tracking-[0.2em] text-white/95">Origins</h2>
                                @if($hasMoreOrigins)
                                <button type="button"
                                    @click="originsOpen = !originsOpen"
                                    class="font-mono text-[10px] tracking-[0.1em] text-white/95 hover:text-white uppercase">
                                    <span x-text="originsOpen ? 'Less' : 'More'"></span>
                                </button>
                                @endif
                            </div>
                            @if($hasMoreOrigins)
                            <div x-show="!originsOpen" class="mt-1 text-[11px] text-white/95">
                                + {{ $originsMoreCount }} more
                            </div>
                            @endif
                        </div>

                        <ul class="mt-2 text-sm space-y-1">
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route('items.index', collect(request()->query())->except(['origin_id','page'])->all()) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline
                                        {{ !request()->filled('origin_id') ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">
                                    All
                                </a>
                            </li>
                            @foreach ($origins->take($originsLimit) as $o)
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route('items.index', array_merge(request()->query(), ['origin_id' => $o->id, 'page' => 1])) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline
                                        {{ $activeOriginId === (int)$o->id ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">
                                    {{ $o->name }}
                                </a>
                            </li>
                            @endforeach
                            @if($hasMoreOrigins)
                            <li x-show="!originsOpen"
                                class="px-2 py-1 text-white/95 select-none cursor-pointer hover:text-white"
                                @click="originsOpen = true">
                                …
                            </li>
                            @endif
                        </ul>

                        @if($hasMoreOrigins)
                        <ul x-show="originsOpen" x-collapse class="text-sm space-y-1 mt-1">
                            @foreach ($origins->skip($originsLimit) as $o)
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route('items.index', array_merge(request()->query(), ['origin_id' => $o->id, 'page' => 1])) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline
                                        {{ $activeOriginId === (int)$o->id ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">
                                    {{ $o->name }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>

                    {{-- ORGANIZATIONS --}}
                    @php
                    $organizationsLimit = 5;
                    $hasMoreOrganizations = $organizations->count() > $organizationsLimit;
                    $organizationsOpenByDefault = request()->filled('organization_id');
                    $activeOrganizationId = (int) request('organization_id');
                    $organizationsMoreCount = max(0, $organizations->count() - $organizationsLimit);
                    @endphp

                    <div x-data="{ organizationsOpen: {{ $organizationsOpenByDefault ? 'true' : 'false' }} }" class="mt-6">
                        <div class="sticky top-0 z-10 -mx-4 px-4 py-2 bg-sage border-b border-black/20 shadow-sm">
                            <div class="flex items-center justify-between">
                                <h2 class="font-stencil text-[11px] uppercase tracking-[0.2em] text-white/95">Organizations</h2>
                                @if($hasMoreOrganizations)
                                <button type="button"
                                    @click="organizationsOpen = !organizationsOpen"
                                    class="font-mono text-[10px] tracking-[0.1em] text-white/95 hover:text-white uppercase">
                                    <span x-text="organizationsOpen ? 'Less' : 'More'"></span>
                                </button>
                                @endif
                            </div>
                            @if($hasMoreOrganizations)
                            <div x-show="!organizationsOpen" class="mt-1 text-[11px] text-white/95">
                                + {{ $organizationsMoreCount }} more
                            </div>
                            @endif
                        </div>

                        <ul class="mt-2 text-sm space-y-1">
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route('items.index', collect(request()->query())->except(['organization_id','page'])->all()) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline
                                        {{ !request()->filled('organization_id') ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">
                                    All
                                </a>
                            </li>
                            @foreach ($organizations->take($organizationsLimit) as $org)
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route('items.index', array_merge(request()->query(), ['organization_id' => $org->id, 'page' => 1])) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline
                                        {{ $activeOrganizationId === (int)$org->id ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">
                                    {{ $org->name }}
                                </a>
                            </li>
                            @endforeach
                            @if($hasMoreOrganizations)
                            <li x-show="!organizationsOpen"
                                class="px-2 py-1 text-white/95 select-none cursor-pointer hover:text-white"
                                @click="organizationsOpen = true">
                                …
                            </li>
                            @endif
                        </ul>

                        @if($hasMoreOrganizations)
                        <ul x-show="organizationsOpen" x-collapse class="text-sm space-y-1 mt-1">
                            @foreach ($organizations->skip($organizationsLimit) as $org)
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route('items.index', array_merge(request()->query(), ['organization_id' => $org->id, 'page' => 1])) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline
                                        {{ $activeOrganizationId === (int)$org->id ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">
                                    {{ $org->name }}
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
                                <a href="{{ route('items.index', collect(request()->query())->except(['for_sale','page'])->all()) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline
                                        {{ !request()->filled('for_sale') ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">
                                    All
                                </a>
                            </li>
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route('items.index', array_merge(request()->query(), ['for_sale' => 1, 'page' => 1])) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline
                                        {{ $forSale === '1' ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">
                                    For sale
                                </a>
                            </li>
                            <li class="relative after:block after:mx-3 after:border-b after:border-white/10">
                                <a href="{{ route('items.index', array_merge(request()->query(), ['for_sale' => 0, 'page' => 1])) }}"
                                    class="block rounded px-2 py-1 hover:bg-white/10 hover:underline
                                        {{ $forSale === '0' ? 'bg-white/15 font-semibold ring-1 ring-white/30' : '' }}">
                                    Not for sale
                                </a>
                            </li>
                        </ul>
                    </div>

                </div>
            </aside>

            {{-- Main content --}}
            <div class="min-w-0 pr-4 pl-0">
                {{-- Header row (breadcrumb + sort/search) --}}
                <div class="pt-2">
                    <div class="grid grid-cols-1 lg:grid-cols-[240px_1fr] gap-4 items-center">
                        {{-- Breadcrumb + mobile filter button --}}
                        <div class="flex items-center justify-between">
                            <nav aria-label="Breadcrumb" class="flex items-center pl-1 space-x-2 font-mono text-[11px] tracking-[0.15em] text-white/95 uppercase">
                                <a href="{{ route('home') }}" class="pr-2">Home</a> <span aria-hidden="true">&gt;</span>
                                <h1 aria-current="page" class="inline text-white/95 font-mono text-[11px] tracking-[0.1em] uppercase">Items</h1>
                            </nav>
                            <button type="button" x-ref="filterTrigger" aria-controls="items-filter-drawer"
                                    :aria-expanded="filtersOpen.toString()" @click="openFilters()"
                                    class="lg:hidden inline-flex items-center gap-2 rounded-md bg-white/10 px-3 py-2 text-sm text-white hover:bg-white/20">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                                FILTER
                            </button>
                        </div>

                        {{-- Sort + search --}}
                        <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                            {{-- Sort --}}
                            <form method="GET" action="{{ route('items.index') }}" class="flex">
                                @if(request()->filled('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                @if(request()->filled('category_id')) <input type="hidden" name="category_id" value="{{ request('category_id') }}"> @endif
                                @if(request()->filled('nationality_id')) <input type="hidden" name="nationality_id" value="{{ request('nationality_id') }}"> @endif
                                @if(request()->filled('origin_id')) <input type="hidden" name="origin_id" value="{{ request('origin_id') }}"> @endif
                                @if(request()->filled('organization_id')) <input type="hidden" name="organization_id" value="{{ request('organization_id') }}"> @endif
                                @if(request()->filled('for_sale')) <input type="hidden" name="for_sale" value="{{ request('for_sale') }}"> @endif

                                <select name="sort" aria-label="Sort items"
                                    class="rounded-md border border-black/30 bg-black/25 text-white px-3 py-2 font-mono text-sm min-w-[150px]"
                                    onchange="this.form.submit()">
                                    <option value="" disabled>Sort by</option>
                                    <option value="title_asc" {{ request('sort', 'created_at_asc') == 'title_asc' ? 'selected' : '' }}>Title (A-Z)</option>
                                    <option value="title_desc" {{ request('sort', 'created_at_asc') == 'title_desc' ? 'selected' : '' }}>Title (Z-A)</option>
                                    <option value="created_at_asc" {{ request('sort', 'created_at_asc') == 'created_at_asc' ? 'selected' : '' }}>Newest First</option>
                                    <option value="created_at_desc" {{ request('sort', 'created_at_asc') == 'created_at_desc' ? 'selected' : '' }}>Oldest First</option>
                                </select>
                            </form>

                            {{-- Search --}}
                            <form method="GET" action="{{ route('items.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
                                @if(request()->filled('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                                @if(request()->filled('category_id')) <input type="hidden" name="category_id" value="{{ request('category_id') }}"> @endif
                                @if(request()->filled('nationality_id')) <input type="hidden" name="nationality_id" value="{{ request('nationality_id') }}"> @endif
                                @if(request()->filled('origin_id')) <input type="hidden" name="origin_id" value="{{ request('origin_id') }}"> @endif
                                @if(request()->filled('organization_id')) <input type="hidden" name="organization_id" value="{{ request('organization_id') }}"> @endif
                                @if(request()->filled('for_sale')) <input type="hidden" name="for_sale" value="{{ request('for_sale') }}"> @endif

                                <div class="relative w-full sm:w-[320px]">
                                    <input
                                        type="text"
                                        name="search"
                                        aria-label="Search items"
                                        placeholder="Search items..."
                                        value="{{ request('search') }}"
                                        class="rounded-md border border-black/30 bg-black/25 text-white placeholder-white/40 font-mono text-sm px-3 py-2 pr-10 w-full"
                                        id="searchInput"
                                        autocomplete="off" />

                                    <button type="button"
                                        id="clearSearchBtn"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-white/70 hover:text-white
                                               {{ request()->filled('search') ? '' : 'hidden' }}"
                                        aria-label="Clear search"
                                        title="Clear search">×</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Active filters bar --}}
                @php
                $q = request()->query();

                $hasFilters =
                    request()->filled('search')
                    || request()->filled('sort')
                    || request()->filled('category_id')
                    || request()->filled('nationality_id')
                    || request()->filled('origin_id')
                    || request()->filled('organization_id')
                    || request()->filled('for_sale');

                $remove = fn($key) => route('items.index', collect($q)->except([$key, 'page'])->all());
                $clearAll = route('items.index');

                $sortLabels = [
                    'title_asc'        => 'Title (A–Z)',
                    'title_desc'       => 'Title (Z–A)',
                    'created_at_asc'   => 'Newest first',
                    'created_at_desc'  => 'Oldest first',
                ];
                $sortLabel = $sortLabels[request('sort')] ?? request('sort');

                $categoryName     = request()->filled('category_id')     ? optional($categories->firstWhere('id',     (int) request('category_id')))->name     : null;
                $nationalityName  = request()->filled('nationality_id')  ? optional($nationalities->firstWhere('id',  (int) request('nationality_id')))->name  : null;
                $originName       = request()->filled('origin_id')       ? optional($origins->firstWhere('id',        (int) request('origin_id')))->name       : null;
                $organizationName = request()->filled('organization_id') ? optional($organizations->firstWhere('id',  (int) request('organization_id')))->name : null;
                $forSaleName      = request()->filled('for_sale')        ? (request('for_sale') === '1' ? 'For sale' : 'Not for sale')                        : null;
                @endphp

                @if($hasFilters)
                <div class="mb-4 flex flex-wrap items-center gap-2 bg-black/20 ring-1 ring-black/30 text-white rounded-xl px-3 py-2">
                    <span class="text-sm opacity-90 mr-1">Active filters:</span>

                    @if(request()->filled('search'))
                    <a href="{{ $remove('search') }}"
                        class="inline-flex items-center gap-2 text-sm bg-sage hover:bg-[#5a6452] rounded-full px-3 py-1">
                        <span>Search: "{{ request('search') }}"</span>
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

                    @if(request()->filled('category_id'))
                    <a href="{{ $remove('category_id') }}"
                        class="inline-flex items-center gap-2 text-sm bg-sage hover:bg-[#5a6452] rounded-full px-3 py-1">
                        <span>Category: {{ $categoryName ?? 'Unknown' }}</span>
                        <span class="text-white/80">×</span>
                    </a>
                    @endif

                    @if(request()->filled('nationality_id'))
                    <a href="{{ $remove('nationality_id') }}"
                        class="inline-flex items-center gap-2 text-sm bg-sage hover:bg-[#5a6452] rounded-full px-3 py-1">
                        <span>Nationality: {{ $nationalityName ?? 'Unknown' }}</span>
                        <span class="text-white/80">×</span>
                    </a>
                    @endif

                    @if(request()->filled('origin_id'))
                    <a href="{{ $remove('origin_id') }}"
                        class="inline-flex items-center gap-2 text-sm bg-sage hover:bg-[#5a6452] rounded-full px-3 py-1">
                        <span>Origin: {{ $originName ?? 'Unknown' }}</span>
                        <span class="text-white/80">×</span>
                    </a>
                    @endif

                    @if(request()->filled('organization_id'))
                    <a href="{{ $remove('organization_id') }}"
                        class="inline-flex items-center gap-2 text-sm bg-sage hover:bg-[#5a6452] rounded-full px-3 py-1">
                        <span>Organization: {{ $organizationName ?? 'Unknown' }}</span>
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
                $total = $items->total();
                $from  = $items->firstItem();
                $to    = $items->lastItem();
                @endphp

                <div class="mb-3 flex items-center justify-between">
                    <p class="text-sm text-white/90">
                        @if($total > 0)
                        Showing <span class="font-semibold text-white">{{ $from }}</span>–<span class="font-semibold text-white">{{ $to }}</span>
                        of <span class="font-semibold text-white">{{ $total }}</span> items
                        @else
                        <span class="font-semibold text-white">0</span> items found
                        @endif
                    </p>
                </div>

                @if($items->count() === 0)
                <div class="bg-sage text-white rounded-md p-6">
                    <h3 class="font-stencil text-lg uppercase tracking-[0.15em] text-white/70">No Results</h3>
                    <p class="text-sm text-white/90 mt-2">
                        Try adjusting your search or removing some filters.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('items.index') }}"
                            class="inline-block rounded-xl bg-black/30 hover:bg-black/40 ring-1 ring-khaki/30 px-4 py-2 font-stencil tracking-[0.15em] text-sm text-white uppercase transition">
                            Clear filters
                        </a>
                    </div>
                </div>
                @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 h-full">
                    @foreach ($items as $item)
                    <a href="{{ route('items.show', $item) }}" target="_blank"
                        class="collection-card bg-sage text-white p-4 rounded-md shadow-md flex flex-col h-full overflow-hidden">

                        <div class="mb-1 flex-grow h-auto">
                            <p class="font-mono text-[9px] tracking-widest text-white/30 text-right mb-1">#{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }}</p>
                            <h3 class="text-lg font-bold text-center">{{ $item->title }}</h3>
                            @if($item->condition)
                            <p class="font-mono text-[9px] text-khaki/60 text-center mt-0.5 tracking-wider">{{ $item->condition }}</p>
                            @endif
                            @if($item->for_sale)
                            <div class="flex justify-center mt-2"><span class="font-stencil text-[9px] tracking-[0.15em] text-khaki/65 border border-khaki/35 px-2 py-0.5 rotate-[-6deg] inline-block">ZU VERKAUFEN</span></div>
                            @endif
                        </div>

                        <p class="text-sm text-center text-white/60 border-t border-white/15 py-1 h-20 flex flex-col justify-center">
                            <span class="block">{{ $item->nationality?->name ?? '—' }}</span>
                            <span class="block text-xs text-white/60/90">{{ $item->category?->name ?? '—' }}</span>
                        </p>

                        <div class="flex-1 flex justify-center items-center h-80">
                            <img
                                src="{{ $item->thumbnail_url ?? asset('images/error-image-not-found.png') }}"
                                alt="{{ $item->title }}"
                                loading="lazy" decoding="async"
                                class="w-full h-48 object-contain">
                        </div>
                    </a>
                    @endforeach
                </div>
                <div class="text-white text-center mt-4">
                    {{ $items->appends(request()->query())->links('pagination::tailwind') }}
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

            searchInput.addEventListener('input', toggleClearButton);

            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                toggleClearButton();
                searchInput.form.submit();
            });

            toggleClearButton();
        })();
    </script>
    <script>
        (() => {
            const nav = document.getElementById('main-navbar');
            if (!nav) return;

            const setHeaderH = () => {
                const h = nav.getBoundingClientRect().height || 0;
                document.documentElement.style.setProperty('--header-h', `${h}px`);
            };

            setHeaderH();
            window.addEventListener('resize', setHeaderH);

            const btn = document.querySelector('[aria-controls="mobile-menu"]');
            if (btn) btn.addEventListener('click', () => setTimeout(setHeaderH, 50));
        })();
    </script>
</x-layout>
