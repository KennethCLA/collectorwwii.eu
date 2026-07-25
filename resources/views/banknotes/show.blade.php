{{-- resources/views/banknotes/show.blade.php --}}

<x-layout :title="$banknote->card_title" :mainClass="'mx-auto w-full max-w-none px-0 py-8'">
    @php
    $images = $banknote->images;
    $main = $banknote->mainImageFile();
    $mainUrl = $main?->url();
    $files = $banknote->files;
    $pdfs = $files->filter(fn($f) => $f->isPdf())->values();
    @endphp


    <div class="w-full max-w-[2200px] mx-auto px-4 sm:px-8 lg:px-16 2xl:px-24">

        <div class="print-document-header">
            <div class="print-logo">CollectorWWII — Catalogue</div>
            <div class="print-section">Banknotes</div>
            <div class="print-title">{{ $banknote->card_title }}</div>
            <div class="print-id">#{{ str_pad($banknote->id, 4, '0', STR_PAD_LEFT) }} &nbsp;·&nbsp; {{ now()->format('d/m/Y') }}</div>
        </div>

        <div class="flex items-center justify-between mb-10 print-hide">
            <nav class="font-mono flex items-center gap-2 text-sm text-white/70">
                <a href="{{ route('home') }}" class="hover:text-white hover:underline">Home</a>
                <span class="opacity-50">›</span>
                <a href="{{ route('banknotes.index') }}" class="hover:text-white hover:underline">Banknotes</a>
                <span class="opacity-50">›</span>
                <span class="text-white font-medium">{{ $banknote->card_title }}</span>
            </nav>
            @if(auth()->user()?->isAdmin())
            <a href="{{ route('admin.pdf', ['banknotes', $banknote->id]) }}"
                class="font-mono text-[10px] tracking-[0.15em] text-white/40 hover:text-white/70 uppercase transition">
                ⬇ PDF
            </a>
            @endif
        </div>

        <div class="item-layout">
            <aside class="item-sticky sticky top-8 space-y-10">
                @include('partials.show-media', [
                    'title' => $banknote->card_title,
                    'subtitle' => null,
                    'images' => $images,
                    'main' => $main,
                    'mainUrl' => $mainUrl,
                    'pdfs' => $pdfs,
                ])
            </aside>

            <div class="space-y-10">
                @if($mainUrl)
                <div class="print-main-image" hidden data-caption="Fotodokumentation">
                    <img src="{{ $mainUrl }}" alt="{{ $banknote->card_title }}">
                </div>
                @endif
                <section class="bg-sage text-white rounded-2xl shadow-lg border border-black/20 overflow-hidden">
                    <div class="px-6 py-3 border-b border-black/25 bg-black/15">
                        <p class="font-stencil text-[10px] uppercase tracking-[0.25em] text-khaki/60">Feldbericht · Objektakte</p>
                    </div>
                    <div class="px-6 py-4">
                        <dl>
                            @if($banknote->condition)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Condition</dt>
                                <dd class="text-sm text-white/90">{{ $banknote->condition }}</dd>
                            </div>
                            @endif
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Country</dt>
                                <dd class="text-sm text-white/90">{{ $banknote->country?->name ?? '—' }}</dd>
                            </div>
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Currency</dt>
                                <dd class="text-sm text-white/90">{{ $banknote->currency?->name ?? '—' }}</dd>
                            </div>
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Nominal value</dt>
                                <dd class="text-sm text-white/90">{{ $banknote->nominalValue?->name ?? '—' }}</dd>
                            </div>
                            @if($banknote->series)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Series</dt>
                                <dd class="text-sm text-white/90">{{ $banknote->series->name }}</dd>
                            </div>
                            @endif
                            @if($banknote->timePeriod)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Time period</dt>
                                <dd class="text-sm text-white/90">{{ $banknote->timePeriod->name }}</dd>
                            </div>
                            @endif
                            @if($banknote->year)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Year</dt>
                                <dd class="text-sm text-white/90">{{ $banknote->year }}</dd>
                            </div>
                            @endif
                            @if($banknote->variation)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Variation</dt>
                                <dd class="text-sm text-white/90">{{ $banknote->variation }}</dd>
                            </div>
                            @endif
                            @if($banknote->for_sale)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">For sale</dt>
                                <dd class="text-sm text-white/90"><span class="inline-block bg-khaki/20 text-khaki px-2 py-0.5 rounded text-xs font-mono">Ja</span></dd>
                            </div>
                            @endif
                            @if($banknote->selling_price !== null && $banknote->for_sale)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Selling price</dt>
                                <dd class="text-sm text-white/90">€ {{ number_format($banknote->selling_price, 2, ',', '.') }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </section>

                @canany(['update','delete'], $banknote)
                <section class="relative bg-sage text-white rounded-2xl shadow-lg border border-black/20 overflow-hidden">
                    <div class="pointer-events-none select-none absolute inset-0 flex items-center justify-center overflow-hidden rounded-2xl">
                        <span class="font-stencil text-[96px] font-black text-red-900/[0.07] tracking-[0.2em] rotate-[-20deg]">GEHEIM</span>
                    </div>
                    <div class="px-6 py-3 border-b border-black/25 bg-black/15">
                        <p class="font-stencil text-[10px] uppercase tracking-[0.25em] text-khaki/60">Geheimakte · Verwaltung</p>
                    </div>
                    <div class="px-6 py-4">
                        <dl>
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Purchase date</dt>
                                <dd class="text-sm text-white/90">{{ $banknote->purchase_date ? $banknote->purchase_date->format('d/m/Y') : '—' }}</dd>
                            </div>
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Purchasing price</dt>
                                <dd class="text-sm text-white/90">{{ $banknote->purchasing_price !== null ? '€ ' . number_format($banknote->purchasing_price, 2, ',', '.') : '—' }}</dd>
                            </div>
                            @if($banknote->location)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Location</dt>
                                <dd class="text-sm text-white/90">{{ $banknote->location->name }}</dd>
                            </div>
                            @endif
                            @if(!empty($banknote->personal_remarks))
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Personal remarks</dt>
                                <dd class="text-sm text-white/90 whitespace-pre-line">{{ $banknote->personal_remarks }}</dd>
                            </div>
                            @endif
                            @if($banknote->sold_at)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Sold on</dt>
                                <dd class="text-sm text-white/90">{{ $banknote->sold_at->format('d/m/Y') }}</dd>
                            </div>
                            @endif
                            @if($banknote->sold_price !== null)
                            <div class="flex items-baseline gap-4 py-2.5 border-t border-white/8 first:border-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wider text-white/70 w-36 shrink-0">Sold price</dt>
                                <dd class="text-sm text-white/90">€ {{ number_format($banknote->sold_price, 2, ',', '.') }}</dd>
                            </div>
                            @endif
                        </dl>

                        <div class="mt-6 pt-4 border-t border-khaki/20 flex flex-wrap items-center justify-end gap-3">
                            @can('update', $banknote)
                            <a href="{{ route('admin.banknotes.edit', $banknote) }}"
                                class="inline-flex items-center gap-2 rounded-md border border-white/20 bg-white/10 px-4 py-2 text-sm text-white hover:bg-white/15 transition">Edit banknote</a>
                            @endcan
                            @can('delete', $banknote)
                            <form method="POST" action="{{ route('admin.banknotes.destroy', $banknote) }}"
                                onsubmit="return confirm('Delete this banknote? This action cannot be undone.');">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-md border border-red-400/30 bg-red-500/10 px-4 py-2 text-sm text-red-100 hover:bg-red-500/20 transition">Delete banknote</button>
                            </form>
                            @endcan
                        </div>
                    </div>
                </section>
                @endcanany
            </div>
        </div>

        @if($previousBanknote || $nextBanknote)
        <div class="mb-10 mt-10 flex items-center justify-between gap-3 print-hide">
            <div class="w-1/2">
                @if($previousBanknote)
                <a href="{{ route('banknotes.show', $previousBanknote) }}"
                    class="group inline-flex w-full items-center gap-3 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white hover:bg-white/10 transition">
                    <span class="text-white/70 group-hover:text-white">←</span>
                    <span class="min-w-0"><span class="block text-xs text-white/60">Previous</span><span class="block truncate font-semibold">{{ $previousBanknote->card_title }}</span></span>
                </a>
                @endif
            </div>
            <div class="w-1/2 text-right">
                @if($nextBanknote)
                <a href="{{ route('banknotes.show', $nextBanknote) }}"
                    class="group inline-flex w-full justify-end items-center gap-3 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white hover:bg-white/10 transition">
                    <span class="min-w-0 text-right"><span class="block text-xs text-white/60">Next</span><span class="block truncate font-semibold">{{ $nextBanknote->card_title }}</span></span>
                    <span class="text-white/70 group-hover:text-white">→</span>
                </a>
                @endif
            </div>
        </div>
        @endif
    </div>
</x-layout>
