<!-- resources/views/home.blade.php -->

@php
    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'CollectorWWII',
        'url' => route('home'),
    ];
@endphp

<x-layout title="CollectorWWII — WWII Collection & Archive"
    metaDescription="Explore a private collection of WWII books, historical items, magazines, newspapers, banknotes, coins, postcards and stamps."
    :mainClass="'w-full p-0'" :bodyClass="'home home-' . $season . ' overflow-hidden'" :structuredData="$structuredData">
    @php
    $homeText = trim((string) ($latestBlog['content'] ?? ''));
    $excerptParagraphs = array_values(array_filter(array_map('trim', explode("\n\n", $homeText))));
    $excerptParagraphs = array_slice($excerptParagraphs, 0, 2);

    $sectionIcons = [
        'books' => 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
        'items' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
        'banknotes' => 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z',
        'coins' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'magazines' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
        'newspapers' => 'M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5',
        'postcards' => 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
        'stamps' => 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z M6 6h.008v.008H6V6z',
    ];
    @endphp

    <section class="mx-auto flex h-[calc(100dvh-var(--header-h,0px))] w-full max-w-5xl flex-col justify-between px-4 py-3 sm:px-6 lg:px-8">
        <div class="space-y-4">
            <div class="relative rounded-2xl bg-sage-950/65 ring-1 ring-black/30 px-5 py-5 sm:px-8 sm:py-6 text-center">
                <div class="pointer-events-none select-none absolute inset-0 flex items-center justify-center overflow-hidden rounded-2xl">
                    <svg class="h-40 w-40 text-white/[0.04]" viewBox="0 0 40 40" fill="currentColor">
                        <rect x="16" y="0" width="8" height="40"/>
                        <rect x="0" y="16" width="40" height="8"/>
                    </svg>
                </div>
                <h1 class="font-stencil text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-maroon"
                    style="filter: drop-shadow(0 1px 1px rgba(255, 255, 255, 0.6));">
                    COLLECTORWWII
                </h1>
                <div class="mx-auto mt-3 h-px w-40 bg-khaki/30"></div>
                <p class="mt-2 font-mono text-xs sm:text-sm text-white/60 tracking-[0.15em] uppercase"
                    style="filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.6));">
                    Artefakte &middot; Dokumente &middot; Geschichte
                </p>
            </div>

            <!-- Collection Stats -->
            @if($sections->sum() > 0)
            <div class="grid grid-cols-4 sm:grid-cols-4 lg:grid-cols-8 gap-2">
                @foreach($sections as $key => $count)
                <a href="{{ route($key . '.index') }}"
                   class="rounded-xl bg-sage-950/65 ring-1 ring-black/30 px-2 py-3 text-center
                          hover:bg-sage-950/80 hover:-translate-y-0.5 transition-all duration-200 group">
                    <svg class="mx-auto h-5 w-5 text-khaki/70 group-hover:text-khaki transition-colors" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $sectionIcons[$key] ?? '' }}" />
                    </svg>
                    <p class="mt-1 text-lg sm:text-xl font-bold text-white">{{ number_format($count) }}</p>
                    <p class="text-[10px] sm:text-xs text-white/60 capitalize">{{ $key }}</p>
                </a>
                @endforeach
            </div>
            @endif

            <!-- Language Switcher -->
            <div class="flex justify-center">
                <div class="inline-flex gap-2 rounded-2xl bg-sage-950/65 ring-1 ring-black/30 px-3 py-2">
                    @foreach ([
                    ['en','flag-united-kingdom.png','English'],
                    ['nl','flag-netherlands.png','Nederlands'],
                    ['de','flag-germany.png','Deutsch'],
                    ['fr','flag-france.png','Français'],
                    ] as [$code,$img,$label])
                    <a href="{{ route('changeLanguage', $code) }}"
                        class="rounded-xl p-1 hover:bg-white/10"
                        aria-label="Switch to {{ $label }}">
                        <img src="{{ asset('images/'.$img) }}"
                            class="h-8 w-8 sm:h-10 sm:w-10 rounded-lg ring-1 ring-black/40 transition hover:-translate-y-0.5"
                            alt="" />
                    </a>
                    @endforeach
                </div>
            </div>

            <!-- Latest Blog -->
            @if (!empty($excerptParagraphs))
            <div class="mx-auto w-full max-w-3xl rounded-2xl bg-sage-950/65 ring-1 ring-black/30 px-5 py-4 text-white sm:px-6 sm:py-5">
                <p class="text-xs tracking-[0.2em] text-white/70 text-center">
                    {{ \Carbon\Carbon::parse($latestBlog['date'])->format('d-m-Y') }}
                </p>
                <div class="mt-3 h-px w-32 mx-auto bg-khaki/35"></div>
                <div class="mt-3 space-y-2 text-sm sm:text-base text-white/90 leading-relaxed">
                    @foreach ($excerptParagraphs as $paragraph)
                    <p>{{ \Illuminate\Support\Str::limit($paragraph, 340) }}</p>
                    @endforeach
                </div>
                <div class="mt-3 h-px w-32 mx-auto bg-khaki/35"></div>
                <div class="mt-3 flex items-center justify-between">
                    <p class="text-sm sm:text-base font-semibold tracking-wide">COLLECTORWWII</p>
                    <a href="{{ route('blog') }}" class="font-mono text-xs text-khaki/70 hover:text-khaki transition tracking-wider uppercase">
                        Read more →
                    </a>
                </div>
            </div>
            @endif
        </div>

        <div class="flex items-center justify-center gap-4 text-xs text-white/75 sm:text-sm">
            <a href="{{ route('blog') }}" class="hover:text-white transition">Blog</a>
            <a href="{{ route('for-sale.index') }}" class="hover:text-white transition">For sale</a>
            <a href="{{ route('contact') }}" class="hover:text-white transition">Contact</a>
            <span class="text-white/55">|</span>
            <span>&copy; {{ now()->year }} CollectorWWII</span>
        </div>
    </section>
</x-layout>
