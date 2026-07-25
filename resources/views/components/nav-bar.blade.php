   {{-- resources/views/components/nav-bar.blade.php --}}
   <div class="transition-shadow" x-data="{ menuOpen: false }" @toggle-mobile-menu.window="menuOpen = !menuOpen">
       {{-- BAR 1 --}}
       <div class="bg-sage-600/95 backdrop-blur-sm">
           <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">

               {{-- LEFT: Logo --}}
               <a href="{{ url('/') }}" class="flex items-center shrink-0">
                   <img class="h-6 w-auto max-w-[140px] sm:max-w-[200px] opacity-90 hover:opacity-100 transition"
                       src="{{ asset('images/wwii-collector-logo.png') }}" alt="CollectorWWII logo">
               </a>

               {{-- CENTER: Main links (desktop only) --}}
               <nav class="hidden md:flex items-center gap-3">
                   <x-nav-link href="/blog" :active="request()->is('blog')">Blog</x-nav-link>
                   <x-nav-link href="{{ route('map.index') }}" :active="request()->routeIs('map.*')">Map</x-nav-link>
                   <x-nav-link href="/for-sale" :active="request()->is('for-sale')">For Sale</x-nav-link>
                   <x-nav-link href="/contact" :active="request()->is('contact')">Contact</x-nav-link>
               </nav>

               {{-- RIGHT: Search + user actions + hamburger --}}
               <div class="flex items-center gap-3">
                   {{-- Search (desktop) --}}
                   <form method="GET" action="{{ route('search.index') }}"
                       class="hidden md:flex items-center">
                       <div class="relative">
                           <input
                               type="search"
                               name="q"
                               value="{{ request('q') }}"
                               aria-label="Search"
                               placeholder="Search…"
                               class="w-40 lg:w-52 rounded-md border border-white/10 bg-white/10 px-3 py-1.5 text-sm text-white placeholder:text-white/40 focus:outline-none focus:ring-1 focus:ring-white/30 focus:w-56 lg:focus:w-64 transition-all">
                       </div>
                   </form>

                   <div class="hidden md:flex items-center gap-4">
                       @guest
                       <a href="{{ route('login') }}" class="opacity-90 hover:opacity-100 transition" aria-label="Login">
                           <img class="size-4" src="{{ asset('images/icon-user-regular.svg') }}" alt="">
                       </a>
                       @else
                       @can('viewAny', \App\Models\Book::class)
                       <a href="{{ route('admin.dashboard') }}" class="opacity-90 hover:opacity-100 transition" aria-label="Admin dashboard">
                           <img class="size-4" src="{{ asset('images/icon-user-regular.svg') }}" alt="">
                       </a>
                       @endcan

                       <form method="POST" action="{{ route('logout') }}">
                           @csrf
                           <button type="submit" class="text-white/80 hover:text-white text-sm transition">Logout</button>
                       </form>
                       @endguest
                   </div>

                   {{-- Mobile toggle --}}
                   <button type="button"
                       @click="$dispatch('toggle-mobile-menu')"
                       class="md:hidden inline-flex items-center justify-center rounded-md bg-black/20 p-2 text-white/80 hover:bg-black/30 hover:text-white focus:outline-none focus:ring-2 focus:ring-white/30"
                       aria-controls="mobile-menu"
                       :aria-expanded="menuOpen.toString()">
                       <span class="sr-only">Open main menu</span>
                       <svg class="block size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                       </svg>
                   </button>
               </div>

           </div>
       </div>

       <div class="relative h-px bg-black/70">
           <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 bg-sage-600/95 px-3">
               <svg class="h-3 w-3 text-white/30" viewBox="0 0 20 20" fill="currentColor">
                   <path d="M7 0h6v7h7v6h-7v7H7v-7H0V7h7V0z"/>
               </svg>
           </div>
       </div>

       {{-- BAR 2 (desktop only) --}}
       <div class="bg-sage-650/95 hidden md:block backdrop-blur-sm">
           <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
               @php
                   $navSep = '<span class="flex items-center px-0.5"><svg class="h-2 w-2 text-white/20" viewBox="0 0 20 20" fill="currentColor"><path d="M7 0h6v7h7v6h-7v7H7v-7H0V7h7V0z"/></svg></span>';
               @endphp
               <div class="h-12 flex items-center justify-center gap-0">

                   @if(config('collector.enabled_sections.books'))
                   <x-nav-link href="{{ route('books.index') }}"
                       :active="request()->routeIs('books.*')"
                       class="tracking-wide">
                       Books
                   </x-nav-link>
                   {!! $navSep !!}
                   @endif

                   @if(config('collector.enabled_sections.items'))
                   <x-nav-link href="{{ route('items.index') }}"
                       :active="request()->routeIs('items.*')"
                       class="tracking-wide">
                       Items
                   </x-nav-link>
                   {!! $navSep !!}
                   @endif

                   @if(config('collector.enabled_sections.magazines'))
                   <x-nav-link href="{{ route('magazines.index') }}" :active="request()->routeIs('magazines.*')" class="tracking-wide">
                       Magazines
                   </x-nav-link>
                   {!! $navSep !!}
                   @endif

                   @if(config('collector.enabled_sections.newspapers'))
                   <x-nav-link href="{{ route('newspapers.index') }}" :active="request()->routeIs('newspapers.*')" class="tracking-wide">
                       Newspapers
                   </x-nav-link>
                   {!! $navSep !!}
                   @endif

                   @if(config('collector.enabled_sections.banknotes'))
                   <x-nav-link href="{{ route('banknotes.index') }}" :active="request()->routeIs('banknotes.*')" class="tracking-wide">
                       Banknotes
                   </x-nav-link>
                   {!! $navSep !!}
                   @endif

                   @if(config('collector.enabled_sections.coins'))
                   <x-nav-link href="{{ route('coins.index') }}" :active="request()->routeIs('coins.*')" class="tracking-wide">
                       Coins
                   </x-nav-link>
                   {!! $navSep !!}
                   @endif

                   @if(config('collector.enabled_sections.postcards'))
                   <x-nav-link href="{{ route('postcards.index') }}" :active="request()->routeIs('postcards.*')" class="tracking-wide">
                       Postcards
                   </x-nav-link>
                   @if(config('collector.enabled_sections.stamps')) {!! $navSep !!} @endif
                   @endif

                   @if(config('collector.enabled_sections.stamps'))
                   <x-nav-link href="{{ route('stamps.index') }}" :active="request()->routeIs('stamps.*')" class="tracking-wide">
                       Stamps
                   </x-nav-link>
                   @endif

               </div>
           </div>
       </div>
   </div>
