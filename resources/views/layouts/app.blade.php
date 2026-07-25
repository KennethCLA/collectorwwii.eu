{{-- resources/views/layouts/app.blade.php --}}
@php
$title = $title ?? config('app.name', 'CollectorWWII');
$bodyClass = $bodyClass ?? '';
$mainClass = $mainClass ?? 'mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6';

// Automatisch admin header op login/password/admin*
$autoAdmin =
request()->routeIs('login') ||
request()->is('password/*') ||
request()->is('admin*') ||
request()->routeIs('admin.*');
$isHome = request()->routeIs('home');

$useAdminHeader = $useAdminHeader ?? $autoAdmin;
@endphp

<!doctype html>
<html lang="en-GB" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $metaDescription ?? 'CollectorWWII – A curated catalogue of WWII books, items, banknotes, coins, magazines, newspapers, postcards and stamps.' }}">
    <title>{{ $title }}</title>

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'CollectorWWII – A curated catalogue of WWII books, items, banknotes, coins, magazines, newspapers, postcards and stamps.' }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('images/wwii-collector-logo.png') }}">
    <meta property="og:site_name" content="CollectorWWII">

    @vite(['resources/css/app.css','resources/js/app.js'])
    {{-- Ensure print-only elements are hidden on screen regardless of build state --}}
    <style>.print-document-header,.print-main-image{display:none}</style>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Share+Tech+Mono&display=swap" rel="stylesheet" />
    <link rel="shortcut icon" href="{{ asset('images/wwii-tank-icon.ico') }}" type="image/x-icon">

    {{-- PWA: installable on iOS/Android --}}
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icons/apple-touch-icon.png') }}">
    <meta name="theme-color" content="#565e55">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="CollectorWWII">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js'));
        }
    </script>
</head>

<body id="app-body" class="min-h-screen flex flex-col bg-sage-500 {{ $bodyClass }}">
    <a href="#app-main"
        class="sr-only focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-[70] rounded-md bg-black px-3 py-2 text-sm text-white">
        Skip to content
    </a>
    {{-- Fixed header wrapper (1 plek die de hoogte bepaalt) --}}
    <header id="main-navbar" class="fixed top-0 left-0 w-full z-50 transition-shadow">
        @if($useAdminHeader)
        <x-admin-header />
        @else
        <x-nav-bar />
        @endif
    </header>

    {{-- Mobile menu: lives outside the fixed header so it can scroll freely --}}
    <div id="mobile-menu"
         class="md:hidden fixed inset-x-0 bottom-0 top-[var(--header-h,65px)] z-[60] overflow-y-scroll [-webkit-overflow-scrolling:touch] bg-[#636c65] border-t border-black/30"
         x-data="{ open: false, savedY: 0 }"
         @toggle-mobile-menu.window="
             open = !open;
             if (open) {
                 savedY = window.scrollY;
                 document.body.style.overflow = 'hidden';
             } else {
                 document.body.style.overflow = '';
             }
         "
         x-show="open"
         x-cloak>
        <div class="p-4">
            <div class="flex flex-col gap-4">

                {{-- Search --}}
                <form method="GET" action="{{ route('search.index') }}" class="flex gap-2">
                    <input type="search" name="q" value="{{ request('q') }}"
                        aria-label="Search"
                        placeholder="Search the collection…"
                        class="flex-1 rounded-md border border-white/10 bg-black/30 px-3 py-2 text-sm text-white">
                    <button type="submit"
                        class="rounded-md bg-white/10 px-4 py-2 text-sm text-white">
                        Go
                    </button>
                </form>

                {{-- MAIN --}}
                <div class="rounded-xl bg-black/20 p-3">
                    <div class="text-xs tracking-[0.2em] text-white/70 mb-2">MAIN</div>
                    <div class="flex flex-col gap-1">
                        <a href="/blog" class="block rounded-md px-3 py-2 text-sm text-gray-200 no-underline" @click="open=false;document.body.style.overflow=''">Blog</a>
                        <a href="{{ route('map.index') }}" class="block rounded-md px-3 py-2 text-sm text-gray-200 no-underline" @click="open=false;document.body.style.overflow=''">Map</a>
                        <a href="/for-sale" class="block rounded-md px-3 py-2 text-sm text-gray-200 no-underline" @click="open=false;document.body.style.overflow=''">For Sale</a>
                        <a href="/contact" class="block rounded-md px-3 py-2 text-sm text-gray-200 no-underline" @click="open=false;document.body.style.overflow=''">Contact</a>
                    </div>
                </div>

                {{-- COLLECTION --}}
                <div class="rounded-xl bg-black/20 p-3">
                    <div class="text-xs tracking-[0.2em] text-white/70 mb-2">COLLECTION</div>
                    <div class="flex flex-col gap-1">
                        @if(config('collector.enabled_sections.books'))
                        <a href="{{ route('books.index') }}" class="block rounded-md px-3 py-2 text-sm text-gray-200 no-underline" @click="open=false;document.body.style.overflow=''">Books</a>
                        @endif
                        @if(config('collector.enabled_sections.items'))
                        <a href="{{ route('items.index') }}" class="block rounded-md px-3 py-2 text-sm text-gray-200 no-underline" @click="open=false;document.body.style.overflow=''">Items</a>
                        @endif
                        @if(config('collector.enabled_sections.magazines'))
                        <a href="{{ route('magazines.index') }}" class="block rounded-md px-3 py-2 text-sm text-gray-200 no-underline" @click="open=false;document.body.style.overflow=''">Magazines</a>
                        @endif
                        @if(config('collector.enabled_sections.newspapers'))
                        <a href="{{ route('newspapers.index') }}" class="block rounded-md px-3 py-2 text-sm text-gray-200 no-underline" @click="open=false;document.body.style.overflow=''">Newspapers</a>
                        @endif
                        @if(config('collector.enabled_sections.banknotes'))
                        <a href="{{ route('banknotes.index') }}" class="block rounded-md px-3 py-2 text-sm text-gray-200 no-underline" @click="open=false;document.body.style.overflow=''">Banknotes</a>
                        @endif
                        @if(config('collector.enabled_sections.coins'))
                        <a href="{{ route('coins.index') }}" class="block rounded-md px-3 py-2 text-sm text-gray-200 no-underline" @click="open=false;document.body.style.overflow=''">Coins</a>
                        @endif
                        @if(config('collector.enabled_sections.postcards'))
                        <a href="{{ route('postcards.index') }}" class="block rounded-md px-3 py-2 text-sm text-gray-200 no-underline" @click="open=false;document.body.style.overflow=''">Postcards</a>
                        @endif
                        @if(config('collector.enabled_sections.stamps'))
                        <a href="{{ route('stamps.index') }}" class="block rounded-md px-3 py-2 text-sm text-gray-200 no-underline" @click="open=false;document.body.style.overflow=''">Stamps</a>
                        @endif
                    </div>
                </div>

                {{-- ACCOUNT --}}
                <div class="rounded-xl bg-black/20 p-3">
                    <div class="text-xs tracking-[0.2em] text-white/70 mb-2">ACCOUNT</div>
                    @guest
                    <a href="{{ route('login') }}" class="block rounded-md px-3 py-2 text-sm text-gray-200 no-underline">Login</a>
                    @else
                    @can('viewAny', \App\Models\Book::class)
                    <a href="{{ route('admin.dashboard') }}" class="block rounded-md px-3 py-2 text-sm text-gray-200 no-underline" @click="open=false;document.body.style.overflow=''">Dashboard</a>
                    @endcan
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left rounded-md px-3 py-2 text-sm text-gray-200 bg-transparent border-0 cursor-pointer">Logout</button>
                    </form>
                    @endguest
                </div>

            </div>
        </div>
    </div>

    <main id="app-main"
        class="flex-1 min-h-0 {{ $mainClass }} pt-[var(--header-h,0px)]">
        @yield('content')
    </main>

    @unless($isHome)
    <footer class="mt-8 border-t border-black/20 bg-black/15 py-5">
        <div class="mx-auto flex w-full max-w-7xl flex-col items-center justify-between gap-2 px-4 text-sm text-white/70 sm:flex-row sm:px-6 lg:px-8">
            <p>&copy; {{ now()->year }} CollectorWWII</p>
            <p class="font-stencil text-[10px] tracking-[0.25em] text-white/25 uppercase">COLLECTORWWII &middot; 51&deg;N 04&deg;E &middot; EST. MMX</p>
            <div class="flex items-center gap-4">
                <a href="{{ route('blog') }}" class="hover:text-white transition">Blog</a>
                <a href="{{ route('for-sale.index') }}" class="hover:text-white transition">For sale</a>
                <a href="{{ route('contact') }}" class="hover:text-white transition">Contact</a>
            </div>
        </div>
    </footer>
    @endunless

    <script>
        // pak het echte element met hoogte
        const navWrap = document.getElementById('main-navbar');

        function getNavEl() {
            if (!navWrap) return null;
            return navWrap.firstElementChild || navWrap; // child heeft meestal de echte hoogte
        }

        function setBodyOffset() {
            const navEl = getNavEl();
            if (!navEl) return;

            const h = navEl.getBoundingClientRect().height;
            document.documentElement.style.setProperty('--header-h', h + 'px');
        }

        const onScroll = () => {
            const navEl = getNavEl();
            if (!navEl) return;
            if (window.scrollY > 10) navEl.classList.add('shadow-lg', 'shadow-black/30');
            else navEl.classList.remove('shadow-lg', 'shadow-black/30');
        };

        window.addEventListener('scroll', onScroll);
        window.addEventListener('load', () => {
            setBodyOffset();
            requestAnimationFrame(setBodyOffset);
            onScroll();
        });
        window.addEventListener('resize', setBodyOffset);

        setBodyOffset();
        onScroll();
    </script>
    @stack('scripts')
</body>

</html>
