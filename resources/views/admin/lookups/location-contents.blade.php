@extends('layouts.admin')

@section('admin-content')
<div class="space-y-5">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-white">Contents: {{ $location->name }}</h1>
            <p class="mt-1 text-sm text-white/70">
                {{ $total }} {{ Str::plural('item', $total) }} stored here
                @if($includesChildren)
                (including sub-locations)
                @endif
            </p>
        </div>
        <a href="{{ route('admin.lookups.index', ['type' => 'locations']) }}"
            class="rounded-md bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/15">
            Back to locations
        </a>
    </div>

    @if($total === 0)
    <div class="rounded-xl border border-black/20 bg-black/15 p-8 text-center text-white/60">
        Nothing stored at this location yet.
    </div>
    @endif

    @if($groups['books']->isNotEmpty())
    <div class="rounded-xl border border-black/20 bg-black/15 p-4">
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-white/70">Books ({{ $groups['books']->count() }})</h2>
        <ul class="divide-y divide-white/5">
            @foreach($groups['books'] as $book)
            <li class="flex items-center justify-between gap-4 py-2">
                <div class="min-w-0">
                    <a href="{{ route('admin.books.edit', $book) }}" class="text-white hover:underline">{{ $book->title }}</a>
                    @if($book->authors->isNotEmpty())
                    <span class="text-sm text-white/50">— {{ $book->authors->pluck('name')->join(', ') }}</span>
                    @endif
                </div>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($groups['banknotes']->isNotEmpty())
    <div class="rounded-xl border border-black/20 bg-black/15 p-4">
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-white/70">Banknotes ({{ $groups['banknotes']->count() }})</h2>
        <ul class="divide-y divide-white/5">
            @foreach($groups['banknotes'] as $banknote)
            <li class="flex items-center justify-between gap-4 py-2">
                <a href="{{ route('admin.banknotes.edit', $banknote) }}" class="text-white hover:underline">
                    {{ $banknote->country?->name ?? 'Unknown country' }}
                    @if($banknote->nominalValue) — {{ $banknote->nominalValue->name }} {{ $banknote->currency?->name }} @endif
                    @if($banknote->year) ({{ $banknote->year }}) @endif
                </a>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($groups['coins']->isNotEmpty())
    <div class="rounded-xl border border-black/20 bg-black/15 p-4">
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-white/70">Coins ({{ $groups['coins']->count() }})</h2>
        <ul class="divide-y divide-white/5">
            @foreach($groups['coins'] as $coin)
            <li class="flex items-center justify-between gap-4 py-2">
                <a href="{{ route('admin.coins.edit', $coin) }}" class="text-white hover:underline">
                    {{ $coin->country?->name ?? 'Unknown country' }}
                    @if($coin->nominalValue) — {{ $coin->nominalValue->name }} @endif
                    @if($coin->year) ({{ $coin->year }}) @endif
                </a>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($groups['postcards']->isNotEmpty())
    <div class="rounded-xl border border-black/20 bg-black/15 p-4">
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-white/70">Postcards ({{ $groups['postcards']->count() }})</h2>
        <ul class="divide-y divide-white/5">
            @foreach($groups['postcards'] as $postcard)
            <li class="flex items-center justify-between gap-4 py-2">
                <a href="{{ route('admin.postcards.edit', $postcard) }}" class="text-white hover:underline">
                    {{ $postcard->country?->name ?? 'Unknown country' }}
                    @if($postcard->postcardType) — {{ $postcard->postcardType->name }} @endif
                    @if($postcard->year) ({{ $postcard->year }}) @endif
                </a>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($groups['stamps']->isNotEmpty())
    <div class="rounded-xl border border-black/20 bg-black/15 p-4">
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-white/70">Stamps ({{ $groups['stamps']->count() }})</h2>
        <ul class="divide-y divide-white/5">
            @foreach($groups['stamps'] as $stamp)
            <li class="flex items-center justify-between gap-4 py-2">
                <a href="{{ route('admin.stamps.edit', $stamp) }}" class="text-white hover:underline">
                    {{ $stamp->country?->name ?? 'Unknown country' }}
                    @if($stamp->stampType) — {{ $stamp->stampType->name }} @endif
                    @if($stamp->year) ({{ $stamp->year }}) @endif
                </a>
            </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
@endsection
