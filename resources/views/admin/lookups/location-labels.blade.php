@extends('layouts.admin')

@section('admin-content')
<div class="space-y-5">
    <div class="print-hide flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-white">Location QR labels</h1>
            <p class="mt-1 text-sm text-white/70">
                Tick the ones you need (or print one individually) instead of the whole sheet — check a box, hit
                "Print selected". Scanning a label opens that location's contents page.
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.lookups.index', ['type' => 'locations']) }}"
                class="rounded-md bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/15">
                Back to locations
            </a>
            <button type="button" id="print-selected-btn" onclick="printSelected()" disabled
                class="rounded-md bg-white/10 px-4 py-2 text-sm font-medium text-white/40 disabled:cursor-not-allowed">
                Print selected (<span id="selected-count">0</span>)
            </button>
            <button type="button" onclick="printAll()"
                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">
                Print all
            </button>
        </div>
    </div>

    <h1 class="print-title">CollectorWWII — Location Labels</h1>

    @if($labels->isEmpty())
    <div class="rounded-xl border border-black/20 bg-black/15 p-8 text-center text-white/60">
        No locations yet. Add some under Locations first.
    </div>
    @else
    <div id="label-sheet" class="label-sheet grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        @foreach($labels as $label)
        <div class="label-card relative flex flex-col items-center gap-2 rounded-xl border border-black/20 p-4 text-center"
            style="background: #ffffff; color-scheme: light;">
            <label class="print-hide absolute left-2 top-2 inline-flex cursor-pointer items-center">
                <input type="checkbox" class="label-select h-4 w-4" onchange="onSelectionChange()">
            </label>
            <button type="button" onclick="printOne(this)"
                class="print-hide absolute right-2 top-2 rounded bg-black/70 px-1.5 py-0.5 text-[10px] text-white hover:bg-black/90"
                title="Print just this label">
                Print
            </button>
            <div class="qr-wrap h-32 w-32">{!! $label['svg'] !!}</div>
            <div class="text-sm font-semibold text-black">{{ $label['name'] }}</div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<script>
    function onSelectionChange() {
        const count = document.querySelectorAll('.label-select:checked').length;
        const btn = document.getElementById('print-selected-btn');
        document.getElementById('selected-count').textContent = count;
        btn.disabled = count === 0;
        btn.classList.toggle('text-white/40', count === 0);
        btn.classList.toggle('text-white', count > 0);
        btn.classList.toggle('bg-white/10', count === 0);
        btn.classList.toggle('bg-emerald-600', count > 0);
    }

    function printAll() {
        document.getElementById('label-sheet')?.classList.remove('print-filtered');
        window.print();
    }

    function printSelected() {
        const sheet = document.getElementById('label-sheet');
        sheet.querySelectorAll('.label-card').forEach((card) => {
            card.classList.toggle('print-hide', !card.querySelector('.label-select')?.checked);
        });
        sheet.classList.add('print-filtered');
        window.print();
        window.addEventListener('afterprint', () => {
            sheet.classList.remove('print-filtered');
            sheet.querySelectorAll('.label-card').forEach((card) => card.classList.remove('print-hide'));
        }, { once: true });
    }

    function printOne(button) {
        const sheet = document.getElementById('label-sheet');
        const target = button.closest('.label-card');
        sheet.querySelectorAll('.label-card').forEach((card) => {
            card.classList.toggle('print-hide', card !== target);
        });
        sheet.classList.add('print-filtered');
        window.print();
        window.addEventListener('afterprint', () => {
            sheet.classList.remove('print-filtered');
            sheet.querySelectorAll('.label-card').forEach((card) => card.classList.remove('print-hide'));
        }, { once: true });
    }
</script>

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

    /* Plain self-contained toggle instead of fighting Tailwind's `hidden`
       utility for print visibility — that produced a specificity/!important
       tie that didn't reliably resolve in our favor. */
    .print-title {
        display: none;
    }

    @media print {
        /* "main *" was too broad: layouts/admin.blade.php nests its own
           <main> inside app.blade.php's outer <main id="app-main">, which
           ALSO wraps <aside> (the sidebar) — so resetting every descendant
           of any <main> was un-hiding the sidebar's own display:none rule
           too. Scoped to .admin-content-wrapper specifically (the one
           actual offending div, tagged with that class in
           layouts/admin.blade.php) instead of casting a wide net. */
        .admin-content-wrapper, .admin-content-wrapper * {
            all: unset !important;
            display: revert !important;
        }
        /* The QR code's <rect>/<path> shapes use SVG presentation
           attributes (fill="#000000" etc), not CSS — the blanket reset
           above still overrides those (CSS always wins over presentation
           attributes) and wiped the QR pattern entirely. Revert rolls the
           cascade back to below author CSS, where presentation attributes
           take effect again, for the SVG and everything inside it. */
        .qr-wrap svg, .qr-wrap svg * {
            all: revert !important;
        }
        /* Same specificity as the global .print-hide rule and loaded
           later, so without this it would win the tie and re-show the
           on-screen header/buttons in print. */
        .print-hide {
            display: none !important;
        }

        /* The global stylesheet uses body padding for page margins, but
           body padding only applies once at the very top/bottom of the
           whole flowed document — not at the top of every printed page.
           With a multi-page label sheet, page 2+ ended up with no top
           margin at all. @page margin repeats on every page consistently,
           so use that here instead and zero out the global body padding
           to avoid stacking both on page 1. */
        body {
            padding: 0 !important;
        }
        @page {
            margin: 15mm !important;
        }
        .print-title {
            display: block !important;
            margin-top: 0;
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
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            gap: 2mm !important;
            break-inside: avoid;
            border: 1px solid #999 !important;
            background: #fff !important;
            padding: 4mm !important;
        }
        .label-card .text-black {
            display: block !important;
            color: #000 !important;
            font-weight: 700 !important;
        }
        .qr-wrap {
            display: block !important;
            width: 35mm !important;
            height: 35mm !important;
        }
        .qr-wrap svg {
            display: block !important;
            width: 100% !important;
            height: 100% !important;
        }
        /* .label-card's own display:flex!important above ties with
           .print-hide's display:none!important (same specificity) and
           would win on source order — this combined selector is more
           specific, so cards marked print-hide during a filtered
           (selected/single) print actually stay hidden. */
        .label-card.print-hide {
            display: none !important;
        }
    }
</style>
@endsection
