{{-- resources/views/partials/show-media.blade.php --}}
{{-- Shared media aside for all show pages --}}
{{-- Required: $title, $images, $main, $mainUrl, $pdfs --}}
{{-- Optional: $subtitle --}}

@php
$allImages = collect($main ? [$main] : [])
    ->concat($images)
    ->filter(fn($img) => $img->url())
    ->unique('id')
    ->unique(fn($img) => $img->disk . ':' . $img->path)
    ->values();
$allImageUrls = $allImages->map(fn($img) => $img->url())->values()->all();
$initialUrl = $allImageUrls[0] ?? null;
$thumbCount = $allImages->count();
@endphp

@php $galleryId = 'gallery-' . uniqid(); @endphp
{{-- IMAGE CARD --}}
<section class="bg-sage text-white rounded-2xl border border-black/20 overflow-hidden noise-texture"
    style="box-shadow: 0 2px 8px rgba(0,0,0,0.4), inset 0 0 0 1px rgba(255,255,255,0.06), inset 0 0 0 5px rgba(0,0,0,0.18);"
    x-data="{ active: {{ $initialUrl ? "'" . $initialUrl . "'" : 'null' }} }">

    {{-- Header bar --}}
    <div class="px-5 py-3 border-b border-black/25 bg-black/20 flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="font-stencil text-[10px] uppercase tracking-[0.25em] text-khaki/60">Fotodokumentation · Bildmaterial</p>
            <h1 class="font-stencil text-lg font-bold text-white leading-snug tracking-wide mt-0.5 truncate">{{ $title }}</h1>
            @if(!empty($subtitle))
            <p class="font-mono text-[11px] text-white/50 mt-0.5 italic leading-snug line-clamp-2">{{ $subtitle }}</p>
            @endif
        </div>
        @if($thumbCount > 0)
        <span class="shrink-0 font-mono text-[10px] text-white/25 pt-0.5">{{ $thumbCount }}×</span>
        @endif
    </div>

    {{-- Main image well --}}
    <div class="bg-sage-900 border-b border-black/30 flex items-center justify-center" style="height: var(--media-frame-h);">
        @if ($initialUrl)
        {{-- Hidden anchors for every image so Fancybox builds the full gallery --}}
        @foreach($allImages as $img)
        <a href="{{ $img->url() }}" data-fancybox="{{ $galleryId }}" class="hidden"></a>
        @endforeach
        {{-- Clicking the visible anchor opens Fancybox at the active image --}}
        <a :href="active" @click.prevent="$el.parentElement.querySelectorAll('[data-fancybox]')[{{ Illuminate\Support\Js::from($allImageUrls) }}.indexOf(active)]?.click()" class="block cursor-zoom-in group p-3">
            <img :src="active" alt="{{ $title }}"
                class="w-auto max-w-full object-contain rounded group-hover:opacity-90 transition"
                style="max-height: var(--media-img-max);" loading="lazy">
        </a>
        @else
        <img src="{{ asset('images/error-image-not-found.png') }}" alt="No image"
            class="w-auto max-w-full object-contain rounded opacity-20 p-3"
            style="max-height: var(--media-img-max);" loading="lazy">
        @endif
    </div>

    {{-- Thumbnail strip --}}
    @if ($thumbCount > 1)
    @php $enableScroll = $thumbCount >= 7; @endphp
    <div class="px-3 py-2.5 bg-black/15">
        <div class="{{ $enableScroll ? 'overflow-x-auto snap-x snap-mandatory' : 'overflow-x-hidden' }}">
            <div class="flex gap-1.5 {{ $enableScroll ? 'justify-start' : 'justify-center' }}">
                @foreach ($allImages as $img)
                @php $url = $img->url(); @endphp
                <button type="button" @click="active = '{{ $url }}'"
                    :class="active === '{{ $url }}' ? 'ring-2 ring-khaki/60 opacity-100' : 'ring-1 ring-white/10 opacity-50 hover:opacity-80 hover:ring-white/25'"
                    class="shrink-0 snap-start rounded overflow-hidden bg-sage-900 transition">
                    <img src="{{ $url }}" alt="" class="w-12 h-12 object-cover" loading="lazy">
                </button>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</section>

{{-- PDF CARDS --}}
@if ($pdfs->count() > 0)
@foreach ($pdfs as $i => $pdf)
@php
$pdfUrl = $pdf->url();
$pdfTitle = $pdf->original_name ? pathinfo($pdf->original_name, PATHINFO_FILENAME) : ('Document ' . ($i + 1));
@endphp
@if ($pdfUrl)
<section class="bg-sage text-white rounded-2xl border border-black/20 overflow-hidden mt-4">
    <div class="px-5 py-3 border-b border-black/25 bg-black/20 flex items-center justify-between gap-3">
        <div class="min-w-0">
            <p class="font-stencil text-[10px] uppercase tracking-[0.25em] text-khaki/60">Felddokument · Schriftakte</p>
            <h2 class="font-stencil text-base font-bold text-white tracking-wide mt-0.5 truncate">{{ $pdfTitle }}</h2>
        </div>
        <a href="{{ $pdfUrl }}" target="_blank" rel="noopener"
            class="shrink-0 font-stencil text-[10px] uppercase tracking-[0.15em] px-3 py-1.5 rounded border border-khaki/30 text-khaki/70 hover:bg-khaki/10 hover:text-khaki transition">
            Open
        </a>
    </div>
    <div class="bg-sage-900" style="height: var(--media-frame-h);">
        <iframe src="{{ $pdfUrl }}#toolbar=0&navpanes=0&scrollbar=1" class="w-full h-full" loading="lazy"></iframe>
    </div>
</section>
@endif
@endforeach
@endif
