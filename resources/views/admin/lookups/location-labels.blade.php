@extends('layouts.admin')

@section('admin-content')
<div class="space-y-5">
    <div class="print-hide flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-white">Location QR labels</h1>
            <p class="mt-1 text-sm text-white/70">
                Print, cut, and stick one on each box/shelf. Scanning it opens that location's contents page.
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.lookups.index', ['type' => 'locations']) }}"
                class="rounded-md bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/15">
                Back to locations
            </a>
            <button type="button" onclick="window.print()"
                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">
                Print
            </button>
        </div>
    </div>

    <h1 class="print-title hidden">CollectorWWII — Location Labels</h1>

    @if($labels->isEmpty())
    <div class="rounded-xl border border-black/20 bg-black/15 p-8 text-center text-white/60">
        No locations yet. Add some under Locations first.
    </div>
    @else
    <div class="label-sheet grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        @foreach($labels as $label)
        <div class="label-card flex flex-col items-center gap-2 rounded-xl border border-black/20 p-4 text-center"
            style="background: #ffffff; color-scheme: light;">
            <div class="qr-wrap h-32 w-32">{!! $label['svg'] !!}</div>
            <div class="text-sm font-semibold text-black">{{ $label['name'] }}</div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<style>
    /* The SVG carries its own fixed width/height attributes (300px) from
       the QR library — CSS width/height on the svg element itself is
       needed to actually scale it down to the label size. */
    .qr-wrap svg {
        display: block;
        width: 100%;
        height: 100%;
    }

    .label-card {
        background: #ffffff !important;
        color-scheme: light;
    }
    .label-card * {
        color-scheme: light;
    }

    @media print {
        /* Page margin + top body padding are already handled globally
           (resources/css/app.css) — just add breathing room before the
           first row of labels on top of that. */
        .print-title {
            display: block !important;
            margin-top: 6mm;
            margin-bottom: 8mm;
            font-size: 14pt;
            font-weight: 700;
            color: #000 !important;
        }
        .label-sheet {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 8mm !important;
        }
        .label-card {
            break-inside: avoid;
            border: 1px solid #999 !important;
            background: #fff !important;
            padding: 4mm !important;
        }
        .label-card .text-black {
            color: #000 !important;
        }
        .qr-wrap {
            width: 35mm !important;
            height: 35mm !important;
        }
    }
</style>
@endsection
