{{-- resources/views/search/results.blade.php --}}
<x-layout :mainClass="'w-full px-2 sm:px-4 py-6'">

    {{-- Page header --}}
    <div class="w-full px-4 pt-6 pb-5">
        <div class="rounded-2xl bg-black/20 p-4 ring-1 ring-black/30 sm:p-6 noise-texture">
            <p class="font-stencil text-xs tracking-[0.4em] text-khaki/60 uppercase mb-1">Suchanfrage · Recherche</p>
            <h1 class="font-stencil text-3xl font-black tracking-[0.2em] text-white uppercase">Suche</h1>
            <p class="font-mono text-[10px] tracking-[0.25em] text-white/40 mt-1 uppercase">Gesamte Sammlung · Alle Kategorien</p>
        </div>
    </div>

    <div class="w-full px-4 space-y-6">

        {{-- Search form --}}
        <form method="GET" action="{{ route('search.index') }}" class="flex gap-2">
            <input
                type="search"
                name="q"
                value="{{ $q }}"
                autofocus
                aria-label="Search"
                placeholder="Search title, author, country, year…"
                class="flex-1 rounded-md border border-black/30 bg-black/25 px-4 py-2.5 text-white placeholder:text-white/40 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-white/20">
            <button type="submit"
                class="rounded-md bg-khaki/20 border border-khaki/30 px-5 py-2.5 text-sm font-medium text-white hover:bg-khaki/30 transition">
                Search
            </button>
        </form>

        {{-- Status line --}}
        @if(mb_strlen($q) >= 2)
        <div class="font-mono text-xs tracking-[0.15em] text-white/50 uppercase">
            @if($total > 0)
                {{ $total }} result{{ $total === 1 ? '' : 's' }} for
                <span class="text-khaki/80">"{{ $q }}"</span>
            @else
                No results for <span class="text-khaki/80">"{{ $q }}"</span>
            @endif
        </div>
        @endif

        {{-- Results grouped by section --}}
        @foreach($groups as $key => $group)
        <section>
            {{-- Section header --}}
            <div class="flex items-center gap-3 mb-3">
                <span class="font-stencil text-xs tracking-[0.3em] text-white/70 uppercase">
                    {{ $group['label'] }}
                </span>
                <span class="font-mono text-[10px] text-white/35 bg-white/5 rounded px-1.5 py-0.5">
                    {{ $group['results']->count() }}
                </span>
                <div class="flex-1 h-px bg-white/8"></div>
                <a href="{{ route($key . '.index') }}"
                    class="font-mono text-[10px] tracking-[0.15em] text-white/40 hover:text-white/70 uppercase transition">
                    View all →
                </a>
            </div>

            {{-- Result cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                @foreach($group['results'] as $hit)
                <a href="{{ $hit['url'] }}"
                    class="group flex flex-col rounded-xl bg-black/20 border border-white/8 overflow-hidden hover:border-white/20 hover:bg-black/30 transition noise-texture">

                    {{-- Thumbnail --}}
                    <div class="aspect-[3/4] bg-sage-900 overflow-hidden flex items-center justify-center">
                        <img
                            src="{{ $hit['thumb'] }}"
                            alt="{{ $hit['title'] }}"
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                            loading="lazy">
                    </div>

                    {{-- Info --}}
                    <div class="p-2.5 flex flex-col gap-0.5 flex-1">
                        <div class="text-sm font-medium text-white leading-snug line-clamp-2">
                            {{ $hit['title'] }}
                        </div>
                        @if($hit['subtitle'])
                        <div class="text-[11px] text-white/50 line-clamp-1 mt-0.5">
                            {{ $hit['subtitle'] }}
                        </div>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </section>
        @endforeach

        {{-- Empty state --}}
        @if(mb_strlen($q) >= 2 && $total === 0)
        <div class="py-16 text-center space-y-3">
            <div class="font-stencil text-4xl text-white/10 tracking-[0.3em] uppercase">Nichts gefunden</div>
            <p class="text-sm text-white/70">Try a different keyword, or browse a section directly.</p>
        </div>
        @endif

        {{-- Prompt if no query --}}
        @if(mb_strlen($q) < 2 && $q === '')
        <div class="py-16 text-center">
            <div class="font-stencil text-4xl text-white/10 tracking-[0.3em] uppercase">Suchen</div>
            <p class="mt-3 text-sm text-white/70">Enter at least 2 characters to search across the collection.</p>
        </div>
        @endif

    </div>

</x-layout>
