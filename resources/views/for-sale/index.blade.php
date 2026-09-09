{{-- resources/views/for-sale/index.blade.php --}}
<x-layout :mainClass="'w-full px-2 sm:px-4 py-6'">

    {{-- Page header --}}
    <div class="w-full px-4 pt-6 pb-5">
        <div class="rounded-2xl bg-black/20 p-4 ring-1 ring-black/30 sm:p-6 noise-texture">
            <p class="font-stencil text-xs tracking-[0.4em] text-khaki/60 uppercase mb-1">Feldpost-Auktion · Nr. ——</p>
            <h1 class="font-stencil text-3xl font-black tracking-[0.2em] text-white uppercase">ZU VERKAUFEN</h1>
            <p class="font-mono text-[10px] tracking-[0.25em] text-white/40 mt-1 uppercase">Verfügbare Sammlungsstücke · WK II</p>
        </div>
    </div>

    <div class="w-full px-4 pt-0">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            {{-- Breadcrumb --}}
            <nav class="flex items-center pl-1 space-x-2 font-mono text-[11px] tracking-[0.15em] text-white/60 uppercase">
                <a href="{{ route('home') }}" class="hover:text-white transition">Home</a>
                <span class="text-white/30">·</span>
                <span class="text-khaki/70">Zu Verkaufen</span>
            </nav>

            {{-- Controls: type + sort + search --}}
            <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                {{-- type filter --}}
                <form method="GET" action="{{ route('for-sale.index') }}" class="flex">
                    @if(request()->filled('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
                    @if(request()->filled('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif

                    <select name="type"
                        class="rounded-md border border-black/30 bg-black/25 text-white px-3 py-2 font-mono text-sm min-w-[150px] focus:outline-none focus:ring-2 focus:ring-white/20"
                        onchange="this.form.submit()">
                        <option value="all" {{ request('type','all') == 'all' ? 'selected' : '' }}>All</option>
                        <option value="books" {{ request('type') == 'books' ? 'selected' : '' }}>Books</option>
                        <option value="items" {{ request('type') == 'items' ? 'selected' : '' }}>Items</option>
                        <option value="banknotes" {{ request('type') == 'banknotes' ? 'selected' : '' }}>Banknotes</option>
                        <option value="coins" {{ request('type') == 'coins' ? 'selected' : '' }}>Coins</option>
                        <option value="magazines" {{ request('type') == 'magazines' ? 'selected' : '' }}>Magazines</option>
                        <option value="newspapers" {{ request('type') == 'newspapers' ? 'selected' : '' }}>Newspapers</option>
                        <option value="postcards" {{ request('type') == 'postcards' ? 'selected' : '' }}>Postcards</option>
                        <option value="stamps" {{ request('type') == 'stamps' ? 'selected' : '' }}>Stamps</option>
                    </select>
                </form>

                {{-- sort --}}
                <form method="GET" action="{{ route('for-sale.index') }}" class="flex">
                    @if(request()->filled('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
                    @if(request()->filled('type')) <input type="hidden" name="type" value="{{ request('type') }}"> @endif

                    <select name="sort"
                        class="rounded-md border border-black/30 bg-black/25 text-white px-3 py-2 font-mono text-sm min-w-[170px] focus:outline-none focus:ring-2 focus:ring-white/20"
                        onchange="this.form.submit()">
                        <option value="" disabled>Sort by</option>
                        <option value="title_asc" {{ request('sort', 'created_at_desc') == 'title_asc' ? 'selected' : '' }}>Title (A-Z)</option>
                        <option value="title_desc" {{ request('sort', 'created_at_desc') == 'title_desc' ? 'selected' : '' }}>Title (Z-A)</option>
                        <option value="price_asc" {{ request('sort', 'created_at_desc') == 'price_asc' ? 'selected' : '' }}>Price (Low → High)</option>
                        <option value="price_desc" {{ request('sort', 'created_at_desc') == 'price_desc' ? 'selected' : '' }}>Price (High → Low)</option>
                        <option value="created_at_desc" {{ request('sort', 'created_at_desc') == 'created_at_desc' ? 'selected' : '' }}>Newest First</option>
                        <option value="created_at_asc" {{ request('sort', 'created_at_desc') == 'created_at_asc' ? 'selected' : '' }}>Oldest First</option>
                    </select>
                </form>

                {{-- search --}}
                <form method="GET" action="{{ route('for-sale.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
                    @if(request()->filled('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                    @if(request()->filled('type')) <input type="hidden" name="type" value="{{ request('type') }}"> @endif

                    <div class="relative w-full sm:w-[320px]">
                        <input type="text"
                            name="q"
                            placeholder="Search..."
                            value="{{ request('q') }}"
                            class="rounded-md border border-black/30 bg-black/25 text-white placeholder-white/40 font-mono text-sm px-3 py-2 pr-10 w-full focus:outline-none focus:ring-2 focus:ring-white/20"
                            id="searchInput"
                            autocomplete="off" />

                        <button type="button"
                            id="clearSearchBtn"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-white/70 hover:text-white
                                   {{ request()->filled('q') ? '' : 'hidden' }}"
                            aria-label="Clear search"
                            title="Clear search">×</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="w-full px-4 py-6">
        @php
        $total = $forSale->total();
        $from = $forSale->firstItem();
        $to = $forSale->lastItem();

        $hasAnyFilter = request()->filled('q')
        || request()->filled('sort')
        || request()->filled('type');
        @endphp

        <div class="mb-3 flex items-center justify-between">
            <p class="font-mono text-[11px] tracking-[0.15em] text-white/60 uppercase">
                @if($total > 0)
                [ <span class="text-white">{{ $from }}</span>–<span class="text-white">{{ $to }}</span>
                &nbsp;of&nbsp; <span class="text-khaki">{{ $total }}</span> ]
                @else
                [ <span class="text-white">0</span> results ]
                @endif
            </p>

            @if($hasAnyFilter)
            <a href="{{ route('for-sale.index') }}"
                class="font-mono text-[10px] tracking-[0.15em] text-khaki/70 hover:text-khaki uppercase">
                Clear filters
            </a>
            @endif
        </div>

        @if($forSale->count() === 0)
        <div class="rounded-xl bg-black/20 ring-1 ring-black/30 p-6 text-center noise-texture">
            <p class="font-mono text-white/60 text-sm tracking-widest">[ NO RESULTS FOUND ]</p>
            <p class="font-mono text-xs text-white/40 mt-2 tracking-wide">Adjust search or remove filters.</p>
            <div class="mt-4">
                <a href="{{ route('for-sale.index') }}"
                    class="inline-block rounded-xl bg-black/30 hover:bg-black/40 ring-1 ring-khaki/30 px-4 py-2 font-stencil tracking-[0.15em] text-sm text-white uppercase transition">
                    Clear filters
                </a>
            </div>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4 h-full">
            @foreach ($forSale as $row)
            <a href="{{ $row['url'] }}" target="_blank"
                class="collection-card bg-sage text-white p-4 rounded-md shadow-md flex flex-col h-full overflow-hidden">
                <div class="mb-2 flex items-start justify-between gap-2">
                    <span class="text-xs bg-white/15 rounded-full px-3 py-1">
                        {{ $row['type_label'] ?? ucfirst(rtrim((string) ($row['type'] ?? 'item'), 's')) }}
                    </span>

                    @if(!is_null($row['price']))
                    <span class="text-xs font-semibold bg-black/20 rounded-full px-3 py-1">
                        €{{ number_format((float)$row['price'], 2, ',', '.') }}
                    </span>
                    @endif
                </div>

                <div class="mb-2 flex-grow h-auto">
                    <h3 class="text-base font-bold text-center line-clamp-2">{{ $row['title'] }}</h3>

                    @if(($row['type'] ?? null) === 'books' && !empty($row['subtitle']))
                    <h5 class="text-xs italic text-center text-gray-300 mt-1 line-clamp-2">
                        {{ $row['subtitle'] }}
                    </h5>
                    @endif
                    @if(($row['type'] ?? null) === 'books')
                    <p class="text-sm text-center text-gray-300 border-t border-gray-400 py-2 mt-2 min-h-[56px]">
                        @php $authors = $row['authors'] ?? []; @endphp

                        @if(count($authors))
                        {{ implode(', ', $authors) }}
                        @else
                        <span class="italic text-white/60">Unknown author</span>
                        @endif
                    </p>
                    @endif
                </div>

                <div class="flex-1 flex justify-center items-center h-80">
                    <img
                        src="{{ $row['image'] ?? asset('images/error-image-not-found.png') }}"
                        alt="{{ $row['title'] }}"
                        class="w-full h-48 object-contain">
                </div>
            </a>
            @endforeach
        </div>

        <div class="text-white text-center mt-4">
            {{ $forSale->appends(request()->query())->links('pagination::tailwind') }}
        </div>
        @endif
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const clearBtn = document.getElementById('clearSearchBtn');

        let debounceTimer = null;
        let lastValue = searchInput.value;

        function toggleClearButton() {
            if (!clearBtn) return;
            if (searchInput.value.trim().length > 0) clearBtn.classList.remove('hidden');
            else clearBtn.classList.add('hidden');
        }

        function submitSearchForm() {
            const current = searchInput.value;
            if (current === lastValue) return;
            lastValue = current;
            searchInput.form.submit();
        }

        searchInput.addEventListener('input', function() {
            toggleClearButton();
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(submitSearchForm, 300);
        });

        searchInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                clearTimeout(debounceTimer);
                submitSearchForm();
            }
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                toggleClearButton();
                clearTimeout(debounceTimer);
                lastValue = '__cleared__';
                searchInput.form.submit();
            });
        }

        toggleClearButton();
    </script>
</x-layout>
