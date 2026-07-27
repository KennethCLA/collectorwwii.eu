{{-- resources/views/layouts/admin.blade.php --}}
@extends('layouts.app', [
'useAdminHeader' => true,
'bodyClass' => 'bg-gradient-to-b from-[#4a564f] via-[#515d56] to-[#59655d]',
'mainClass' => 'w-full' // <- belangrijk: geen centering classes
    ])

    @section('content')
    <div class="mx-auto w-full max-w-[1600px] px-4 py-6 lg:px-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:gap-6">
        <aside class="order-2 w-full lg:order-1 lg:w-64 lg:shrink-0">
            <div class="rounded-2xl bg-feldgrau/70 p-3.5 text-white ring-1 ring-black/35 backdrop-blur-sm lg:sticky lg:top-4 noise-texture">
                <div class="mb-3 px-2">
                    <p class="font-stencil text-[11px] uppercase tracking-[0.18em] text-white/55">Befehlsstelle</p>
                </div>
                @include('admin.partials.sidebar')
            </div>
        </aside>

        <main class="order-1 min-w-0 flex-1 lg:order-2">
            <div class="admin-content-wrapper rounded-2xl bg-black/20 p-5 text-white ring-1 ring-black/30 backdrop-blur-sm lg:p-6">
                @yield('admin-content')
            </div>
        </main>
    </div>
    </div>
    @endsection
