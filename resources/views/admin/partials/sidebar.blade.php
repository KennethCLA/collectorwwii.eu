@php
    $groups = [
        [
            'title' => 'Core',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
            ],
        ],
        [
            'title' => 'Books',
            'items' => [
                ['label' => 'Create book', 'route' => 'admin.books.create', 'active' => 'admin.books.create'],
                ['label' => 'Topics', 'route' => 'admin.lookups.index', 'params' => ['type' => 'book-topics'], 'active' => 'admin.lookups.*', 'active_types' => ['book-topics']],
                ['label' => 'Covers', 'route' => 'admin.lookups.index', 'params' => ['type' => 'book-covers'], 'active' => 'admin.lookups.*', 'active_types' => ['book-covers']],
                ['label' => 'Series', 'route' => 'admin.lookups.index', 'params' => ['type' => 'book-series'], 'active' => 'admin.lookups.*', 'active_types' => ['book-series']],
            ],
        ],
        [
            'title' => 'Items',
            'items' => [
                ['label' => 'Create item', 'route' => 'admin.items.create', 'active' => 'admin.items.create'],
                ['label' => 'Categories', 'route' => 'admin.lookups.index', 'params' => ['type' => 'item-categories'], 'active' => 'admin.lookups.*', 'active_types' => ['item-categories']],
                ['label' => 'Nationalities', 'route' => 'admin.lookups.index', 'params' => ['type' => 'item-nationalities'], 'active' => 'admin.lookups.*', 'active_types' => ['item-nationalities']],
                ['label' => 'Organizations', 'route' => 'admin.lookups.index', 'params' => ['type' => 'item-organizations'], 'active' => 'admin.lookups.*', 'active_types' => ['item-organizations']],
            ],
        ],
        [
            'title' => 'Map locations',
            'items' => [
                ['label' => 'Create location', 'route' => 'admin.map-locations.create', 'active' => 'admin.map-locations.create'],
            ],
        ],
        [
            'title' => 'Banknotes',
            'items' => [
                ['label' => 'Create banknote', 'route' => 'admin.banknotes.create', 'active' => 'admin.banknotes.create'],
                ['label' => 'Series', 'route' => 'admin.lookups.index', 'params' => ['type' => 'banknote-series'], 'active' => 'admin.lookups.*', 'active_types' => ['banknote-series']],
                ['label' => 'Time Periods', 'route' => 'admin.lookups.index', 'params' => ['type' => 'banknote-time-periods'], 'active' => 'admin.lookups.*', 'active_types' => ['banknote-time-periods']],
                ['label' => 'Designers', 'route' => 'admin.lookups.index', 'params' => ['type' => 'banknote-designers'], 'active' => 'admin.lookups.*', 'active_types' => ['banknote-designers']],
                ['label' => 'Watermarks', 'route' => 'admin.lookups.index', 'params' => ['type' => 'banknote-watermarks'], 'active' => 'admin.lookups.*', 'active_types' => ['banknote-watermarks']],
            ],
        ],
        [
            'title' => 'Coins',
            'items' => [
                ['label' => 'Create coin', 'route' => 'admin.coins.create', 'active' => 'admin.coins.create'],
                ['label' => 'Shapes', 'route' => 'admin.lookups.index', 'params' => ['type' => 'coin-shapes'], 'active' => 'admin.lookups.*', 'active_types' => ['coin-shapes']],
                ['label' => 'Materials', 'route' => 'admin.lookups.index', 'params' => ['type' => 'coin-materials'], 'active' => 'admin.lookups.*', 'active_types' => ['coin-materials']],
                ['label' => 'Occasions', 'route' => 'admin.lookups.index', 'params' => ['type' => 'coin-occasions'], 'active' => 'admin.lookups.*', 'active_types' => ['coin-occasions']],
                ['label' => 'Designers', 'route' => 'admin.lookups.index', 'params' => ['type' => 'coin-designers'], 'active' => 'admin.lookups.*', 'active_types' => ['coin-designers']],
                ['label' => 'Strike Marks', 'route' => 'admin.lookups.index', 'params' => ['type' => 'coin-strike-marks'], 'active' => 'admin.lookups.*', 'active_types' => ['coin-strike-marks']],
                ['label' => 'Front Images', 'route' => 'admin.lookups.index', 'params' => ['type' => 'coin-front-images'], 'active' => 'admin.lookups.*', 'active_types' => ['coin-front-images']],
                ['label' => 'Front Texts', 'route' => 'admin.lookups.index', 'params' => ['type' => 'coin-front-texts'], 'active' => 'admin.lookups.*', 'active_types' => ['coin-front-texts']],
                ['label' => 'Reverse Images', 'route' => 'admin.lookups.index', 'params' => ['type' => 'coin-reverse-images'], 'active' => 'admin.lookups.*', 'active_types' => ['coin-reverse-images']],
                ['label' => 'Reverse Texts', 'route' => 'admin.lookups.index', 'params' => ['type' => 'coin-reverse-texts'], 'active' => 'admin.lookups.*', 'active_types' => ['coin-reverse-texts']],
                ['label' => 'Rims', 'route' => 'admin.lookups.index', 'params' => ['type' => 'coin-rims'], 'active' => 'admin.lookups.*', 'active_types' => ['coin-rims']],
                ['label' => 'Rim Texts', 'route' => 'admin.lookups.index', 'params' => ['type' => 'coin-rim-texts'], 'active' => 'admin.lookups.*', 'active_types' => ['coin-rim-texts']],
            ],
        ],
        [
            'title' => 'Stamps',
            'items' => [
                ['label' => 'Create stamp', 'route' => 'admin.stamps.create', 'active' => 'admin.stamps.create'],
                ['label' => 'Types', 'route' => 'admin.lookups.index', 'params' => ['type' => 'stamp-types'], 'active' => 'admin.lookups.*', 'active_types' => ['stamp-types']],
                ['label' => 'Designers', 'route' => 'admin.lookups.index', 'params' => ['type' => 'stamp-designers'], 'active' => 'admin.lookups.*', 'active_types' => ['stamp-designers']],
                ['label' => 'Watermarks', 'route' => 'admin.lookups.index', 'params' => ['type' => 'stamp-watermarks'], 'active' => 'admin.lookups.*', 'active_types' => ['stamp-watermarks']],
                ['label' => 'Gums', 'route' => 'admin.lookups.index', 'params' => ['type' => 'stamp-gums'], 'active' => 'admin.lookups.*', 'active_types' => ['stamp-gums']],
                ['label' => 'Perforations', 'route' => 'admin.lookups.index', 'params' => ['type' => 'stamp-perforations'], 'active' => 'admin.lookups.*', 'active_types' => ['stamp-perforations']],
                ['label' => 'Printing Houses', 'route' => 'admin.lookups.index', 'params' => ['type' => 'stamp-printing-houses'], 'active' => 'admin.lookups.*', 'active_types' => ['stamp-printing-houses']],
            ],
        ],
        [
            'title' => 'Postcards',
            'items' => [
                ['label' => 'Create postcard', 'route' => 'admin.postcards.create', 'active' => 'admin.postcards.create'],
                ['label' => 'Types', 'route' => 'admin.lookups.index', 'params' => ['type' => 'postcard-types'], 'active' => 'admin.lookups.*', 'active_types' => ['postcard-types']],
                ['label' => 'Valuation Images', 'route' => 'admin.lookups.index', 'params' => ['type' => 'postcard-valuation-images'], 'active' => 'admin.lookups.*', 'active_types' => ['postcard-valuation-images']],
            ],
        ],
        [
            'title' => 'Magazines',
            'items' => [
                ['label' => 'Create magazine', 'route' => 'admin.magazines.create', 'active' => 'admin.magazines.create'],
            ],
        ],
        [
            'title' => 'Newspapers',
            'items' => [
                ['label' => 'Create newspaper', 'route' => 'admin.newspapers.create', 'active' => 'admin.newspapers.create'],
            ],
        ],
        [
            'title' => 'Shared lookups',
            'items' => [
                ['label' => 'Countries', 'route' => 'admin.lookups.index', 'params' => ['type' => 'countries'], 'active' => 'admin.lookups.*', 'active_types' => ['countries']],
                ['label' => 'Currencies', 'route' => 'admin.lookups.index', 'params' => ['type' => 'currencies'], 'active' => 'admin.lookups.*', 'active_types' => ['currencies']],
                ['label' => 'Nominal Values', 'route' => 'admin.lookups.index', 'params' => ['type' => 'nominal-values'], 'active' => 'admin.lookups.*', 'active_types' => ['nominal-values']],
                ['label' => 'Origins', 'route' => 'admin.lookups.index', 'params' => ['type' => 'origins'], 'active' => 'admin.lookups.*', 'active_types' => ['origins']],
                ['label' => 'Locations', 'route' => 'admin.lookups.index', 'params' => ['type' => 'locations'], 'active' => 'admin.lookups.*', 'active_types' => ['locations']],
                ['label' => 'Heads of State', 'route' => 'admin.lookups.index', 'params' => ['type' => 'heads-of-state'], 'active' => 'admin.lookups.*', 'active_types' => ['heads-of-state']],
                ['label' => 'Colours', 'route' => 'admin.lookups.index', 'params' => ['type' => 'colours'], 'active' => 'admin.lookups.*', 'active_types' => ['colours']],
                ['label' => 'Print Types', 'route' => 'admin.lookups.index', 'params' => ['type' => 'print-types'], 'active' => 'admin.lookups.*', 'active_types' => ['print-types']],
            ],
        ],
        [
            'title' => 'Content',
            'items' => [
                ['label' => 'Blog posts', 'route' => 'admin.blog.index', 'active' => 'admin.blog.*'],
            ],
        ],
    ];
@endphp

<div x-data="{ q: '', open: {} }"
    class="space-y-3">
    <div class="rounded-lg border border-white/10 bg-black/25 p-2">
        <label class="sr-only" for="admin-nav-search">Search menu</label>
        <input
            id="admin-nav-search"
            x-model="q"
            type="text"
            placeholder="Search menu..."
            class="w-full rounded-md border border-black/20 bg-white/10 px-3 py-2 text-sm text-white placeholder:text-white/45 focus:outline-none focus:ring-2 focus:ring-white/25">
    </div>

    <nav class="max-h-[calc(100vh-220px)] space-y-4 overflow-y-auto pr-1">
        @foreach($groups as $group)
            @php
                $groupKey = \Illuminate\Support\Str::slug(str_replace(['Lookup: ', '/'], ['', ' '], $group['title']));
            @endphp
            <section class="space-y-1.5">
                <button type="button"
                    @click="open['{{ $groupKey }}'] = !(open['{{ $groupKey }}'] ?? true)"
                    class="flex w-full items-center justify-between px-2 text-left font-stencil text-[11px] font-semibold uppercase tracking-[0.15em] text-white/45 hover:text-white/80">
                    <span>{{ $group['title'] }}</span>
                    <span class="text-xs" x-text="(open['{{ $groupKey }}'] ?? true) ? '−' : '+'"></span>
                </button>

                <div x-show="open['{{ $groupKey }}'] ?? true" x-collapse class="space-y-1">
                    @foreach($group['items'] as $item)
                        @php
                            $activePatterns = is_array($item['active']) ? $item['active'] : explode('|', $item['active']);
                            $isRouteMatch = request()->routeIs(...$activePatterns);
                            $isTypeMatch = in_array(request()->route('type'), $item['active_types'] ?? [], true);
                            $isActive = $isRouteMatch && ($isTypeMatch || !isset($item['active_types']));
                            $labelLower = mb_strtolower($item['label']);
                        @endphp

                        <a x-show="!q || @js($labelLower).includes(q.toLowerCase())"
                            href="{{ route($item['route'], $item['params'] ?? []) }}"
                            class="group flex items-center justify-between rounded-lg px-3 py-2 text-sm transition
                                {{ $isActive ? 'bg-white text-[#1f2723] shadow-sm' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                            <span>{{ $item['label'] }}</span>
                            @if($isActive)
                                <span class="h-1.5 w-1.5 rounded-full bg-[#3f5d4f]"></span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
    </nav>
</div>
