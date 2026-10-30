<x-layout title="WWII Collection Blog — CollectorWWII"
    metaDescription="Read updates, research notes and stories related to the CollectorWWII collection.">
    <div class="max-w-4xl mx-auto px-4 py-6 sm:px-6 lg:px-8">

        {{-- Page heading --}}
        <div class="text-center mb-8">
            <p class="font-stencil text-xs tracking-[0.5em] text-khaki/60 uppercase mb-2">Heeresdruckvorschrift</p>
            <h1 class="font-stencil text-4xl sm:text-5xl font-black tracking-[0.2em] text-white uppercase">BLOG</h1>
            <div class="mx-auto mt-3 h-px w-32 bg-khaki/30"></div>
            <p class="font-stencil mt-2 text-xs tracking-[0.3em] text-white/40 uppercase">COLLECTORWWII</p>
        </div>

        {{-- Language Switcher --}}
        <div class="flex justify-center gap-2 mb-8">
            @foreach ([
                ['en','flag-united-kingdom.png','English'],
                ['nl','flag-netherlands.png','Nederlands'],
                ['de','flag-germany.png','Deutsch'],
                ['fr','flag-france.png','Français'],
            ] as [$code,$img,$label])
            <a href="{{ route('changeLanguage', $code) }}"
               class="rounded-lg p-1 hover:bg-white/10 transition" aria-label="Switch to {{ $label }}">
                <img src="{{ asset('images/'.$img) }}" alt="{{ $label }}"
                     class="h-8 w-8 rounded-md ring-1 ring-black/40 hover:-translate-y-0.5 transition">
            </a>
            @endforeach
        </div>

        @if ($blogs && count($blogs) > 0)
            @foreach ($blogs as $blog)
            <article class="rounded-xl bg-[#2c3335]/70 ring-1 ring-black/30 px-6 py-5 sm:px-8 sm:py-6 noise-texture mb-2">

                {{-- Date stamp --}}
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-px flex-1 bg-khaki/20"></div>
                    <p class="font-mono text-xs tracking-[0.3em] text-khaki/70">
                        [&nbsp;{{ \Carbon\Carbon::parse($blog['date'])->format('d · m · Y') }}&nbsp;]
                    </p>
                    <div class="h-px flex-1 bg-khaki/20"></div>
                </div>

                {{-- Content --}}
                <div class="space-y-3 text-sm sm:text-base text-white/85 leading-relaxed">
                    @foreach (explode("\n\n", $blog['content'][$language] ?? $blog['content']['en']) as $paragraph)
                    <p>{{ $paragraph }}</p>
                    @endforeach
                </div>

                {{-- Signature --}}
                <div class="mt-5 flex items-center gap-3">
                    <div class="h-px flex-1 bg-khaki/15"></div>
                    <p class="font-stencil text-sm tracking-[0.25em] text-white/50 uppercase">COLLECTORWWII</p>
                    <div class="h-px flex-1 bg-khaki/15"></div>
                </div>

            </article>
            <div class="h-px bg-khaki/15 my-4 mx-8"></div>
            @endforeach
        @else
        <p class="text-white/60 text-center font-mono text-sm tracking-widest">[ NO ENTRIES FOUND ]</p>
        @endif

    </div>
</x-layout>
