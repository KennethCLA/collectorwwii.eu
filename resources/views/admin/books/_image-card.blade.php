{{-- resources/views/admin/books/_image-card.blade.php --}}
@php
/** @var \App\Models\MediaFile|\App\Models\BookImage $img */
$url = $img->url();
@endphp

<div class="group w-32 shrink-0 rounded-md bg-sage-900 border border-white/10 overflow-hidden cursor-grab active:cursor-grabbing"
    draggable="true"
    data-media-id="{{ $img->id }}">

    {{-- Drag handle --}}
    <div class="flex items-center justify-center h-5 text-white/25 group-hover:text-white/50 select-none text-[11px] tracking-widest">
        ⠿ ⠿ ⠿
    </div>

    {{-- Preview --}}
    @if($url)
    <a href="{{ $url }}" target="_blank" rel="noopener" class="block" draggable="false">
        <div class="w-32 h-44 bg-black/10 flex items-center justify-center overflow-hidden">
            <img
                src="{{ $url }}"
                alt="{{ isset($loop) ? 'Image '.$loop->iteration : ($img->original_name ?? 'Uploaded image') }}"
                class="w-full h-full object-contain block"
                loading="lazy">
        </div>
    </a>
    @else
    <div class="w-32 h-44 bg-black/10 flex items-center justify-center text-white/60 text-xs">
        No preview
    </div>
    @endif

    {{-- Footer --}}
    <div class="p-2 grid grid-cols-[auto_1fr_auto] items-center gap-2">
        {{-- LEFT: Main --}}
        @if($img->is_main)
        <span class="inline-flex items-center justify-center h-7 px-2 text-[10px] leading-none rounded bg-white/15 text-white font-semibold">
            Main
        </span>
        @else
        <form action="{{ route('admin.media.main', ['type' => $type ?? 'books', 'file' => $img->id]) }}" method="POST" class="m-0">
            @csrf
            @method('PATCH')
            <button type="submit"
                class="inline-flex items-center justify-center h-7 px-2 text-[10px] leading-none rounded bg-white/10 text-white hover:bg-white/20 transition">
                Main
            </button>
        </form>
        @endif

        <div></div>

        {{-- RIGHT: Delete --}}
        <form action="{{ route('admin.media.destroy', ['type' => $type ?? 'books', 'file' => $img->id]) }}"
            method="POST"
            onsubmit="return confirm('Delete this file?');"
            class="m-0">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="inline-flex items-center justify-center h-7 px-2 text-[10px] leading-none rounded bg-red-600 text-white hover:bg-red-700 transition">
                Del
            </button>
        </form>
    </div>
</div>