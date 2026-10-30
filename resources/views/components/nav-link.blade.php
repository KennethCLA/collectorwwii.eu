<!-- Navbar link component (nav-link.blade.php) -->

@props(['active' => false])

@php
$base = 'rounded-md px-3 py-2 text-sm font-medium transition';
$activeClasses = 'bg-black/35 text-white ring-1 ring-black/35 shadow-sm';
$inactiveClasses = 'text-gray-200 hover:bg-black/20 hover:text-white';
@endphp

<a
    {{ $attributes->merge([
        'class' => $base . ' ' . ($active ? $activeClasses : $inactiveClasses)
    ]) }}
    aria-current="{{ $active ? 'page' : 'false' }}">
    {{ $slot }}
</a>
