<x-layout :mainClass="'w-full px-4 py-6 sm:px-6 lg:px-8'">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

    <style>
        .wwii-marker {
            width: 22px;
            height: 22px;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            background: #c2b280;
            border: 2px solid #2d3b2f;
            box-shadow: 0 1px 4px rgba(0,0,0,.5);
        }
        .wwii-marker::after {
            content: '';
            position: absolute;
            top: 5px;
            left: 5px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #2d3b2f;
        }
        .marker-cluster-wwii {
            background: rgba(194,178,128,0.35);
        }
        .marker-cluster-wwii div {
            background: #4a564f;
            color: #f3f0e6;
            font-weight: 700;
            border: 2px solid #c2b280;
        }
        .leaflet-popup-content-wrapper, .leaflet-popup-tip {
            background: #2d3b2f;
            color: #f3f0e6;
        }
        .leaflet-popup-content-wrapper { border-radius: 10px; }
    </style>

    <div class="mx-auto w-full max-w-7xl space-y-4 pt-6">
        <div class="rounded-2xl bg-black/20 p-4 ring-1 ring-black/30 sm:p-6 noise-texture">
            <p class="font-stencil text-xs tracking-[0.4em] text-khaki/60 uppercase mb-1">Kriegsschauplatz · Operationskarte</p>
            <h1 class="font-stencil text-3xl font-black tracking-[0.2em] text-white uppercase">LAGEBERICHT</h1>
            <p class="font-mono text-[10px] tracking-[0.25em] text-white/40 mt-1 uppercase">Feldkarte · WK II Standorte · Klicken für Details</p>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="relative rounded-2xl bg-black/20 p-3 ring-1 ring-black/30 sm:p-4">
                <div id="visit-map" class="h-[45vh] min-h-[360px] w-full rounded-xl lg:h-[65vh]"></div>
                <div class="pointer-events-none absolute inset-3 sm:inset-4 rounded-xl"
                     style="background-image:
                         repeating-linear-gradient(0deg, rgba(194,178,128,0.06) 0px, transparent 1px, transparent 60px, rgba(194,178,128,0.06) 60px),
                         repeating-linear-gradient(90deg, rgba(194,178,128,0.06) 0px, transparent 1px, transparent 60px, rgba(194,178,128,0.06) 60px);
                     background-size: 60px 60px;">
                </div>
            </div>

            <aside x-data="{ open: window.innerWidth >= 1024 }" class="rounded-2xl bg-black/20 p-4 ring-1 ring-black/30">
                <button type="button" @click="open = !open"
                    class="flex w-full items-center justify-between lg:pointer-events-none">
                    <h2 class="font-stencil text-[11px] uppercase tracking-[0.25em] text-khaki/70">
                        STANDORTE <span class="text-white/40" x-text="'(' + {{ $locations->count() }} + ')'"></span>
                    </h2>
                    <svg class="h-4 w-4 text-white/60 transition-transform lg:hidden" :class="open ? 'rotate-180' : ''"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" x-collapse class="mt-3">
                    <input type="text" id="location-search" placeholder="Search…"
                        class="mb-3 w-full rounded-md border border-white/10 bg-black/30 px-3 py-2 text-sm text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20" />

                    <div id="location-list" class="space-y-2 max-h-[45vh] overflow-y-auto pr-1 lg:max-h-[58vh]">
                        @forelse($locations as $loc)
                        <button
                            type="button"
                            data-location-name="{{ strtolower($loc['name']) }}"
                            class="w-full rounded-lg bg-white/10 px-3 py-2 text-left text-white transition hover:bg-white/20"
                            onclick="focusMarker({{ $loc['id'] }})">
                            <div class="text-sm font-semibold">{{ $loc['name'] }}</div>
                            <div class="text-xs text-white/70">{{ $loc['coordinates'] }}</div>
                        </button>
                        @empty
                        <p class="text-sm text-white/60">No map locations yet.</p>
                        @endforelse
                    </div>
                    <p id="location-empty" class="hidden text-sm text-white/60 mt-2">No matches.</p>
                </div>
            </aside>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

    <script>
        const locations = @json($locations);
        const map = L.map('visit-map', {
            zoomControl: true,
            minZoom: 2,
        }).setView([50.8503, 4.3517], 4);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const wwiiIcon = L.divIcon({
            className: '',
            html: '<div class="wwii-marker" style="position:relative"></div>',
            iconSize: [22, 22],
            iconAnchor: [11, 20],
            popupAnchor: [0, -20],
        });

        const clusterGroup = L.markerClusterGroup({
            iconCreateFunction: function (cluster) {
                return L.divIcon({
                    html: '<div>' + cluster.getChildCount() + '</div>',
                    className: 'marker-cluster-wwii marker-cluster',
                    iconSize: [40, 40],
                });
            },
        });
        map.addLayer(clusterGroup);

        const popupHtml = (loc) => {
            const desc = (loc.description || '').replace(/\n/g, '<br>');
            const imageThumbs = (loc.images || []).slice(0, 4).map((url, idx) =>
                `<a href="${url}" data-fancybox="map-${loc.id}" class="block overflow-hidden rounded-md ring-1 ring-white/20">
                    <img src="${url}" alt="${loc.name} photo ${idx + 1}" class="h-16 w-24 object-cover" />
                </a>`
            ).join('');

            const hiddenLinks = (loc.images || []).slice(4).map((url) =>
                `<a href="${url}" data-fancybox="map-${loc.id}" class="hidden">Photo</a>`
            ).join('');

            return `
                <div class="space-y-2" style="min-width: 250px; max-width: 300px;">
                    <div>
                        <div style="font-weight:700; font-size:14px;">${loc.name}</div>
                        <div style="font-size:12px; color:#c2b280;">${loc.coordinates}</div>
                    </div>
                    ${desc ? `<div style="font-size:13px; line-height:1.4;">${desc}</div>` : ''}
                    ${(loc.images || []).length ? `<div class="grid grid-cols-2 gap-1">${imageThumbs}</div>` : ''}
                    ${hiddenLinks}
                </div>`;
        };

        const markers = {};
        const bounds = [];

        locations.forEach((loc) => {
            const marker = L.marker([loc.lat, loc.lng], { icon: wwiiIcon });
            marker.bindPopup(popupHtml(loc));
            markers[loc.id] = marker;
            clusterGroup.addLayer(marker);
            bounds.push([loc.lat, loc.lng]);
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds, {
                padding: [40, 40]
            });
        }

        map.on('popupopen', function(e) {
            const popupEl = e.popup.getElement();
            if (!popupEl) return;
            popupEl.querySelectorAll('[data-fancybox]').forEach(function(el) {
                el.addEventListener('click', function(ev) {
                    ev.preventDefault();
                    ev.stopPropagation();
                    const gallery = el.dataset.fancybox;
                    const anchors = Array.from(popupEl.querySelectorAll('[data-fancybox="' + gallery + '"]'));
                    const items = anchors.map(function(a) { return { src: a.href, type: 'image' }; });
                    const idx = anchors.indexOf(el);
                    window.Fancybox.show(items, { startIndex: Math.max(0, idx) });
                });
            });
        });

        window.focusMarker = (id) => {
            const marker = markers[id];
            if (!marker) return;
            clusterGroup.zoomToShowLayer(marker, function () {
                marker.openPopup();
            });
        };

        // Search: filters the sidebar list; matching markers get their own
        // cluster group so results are visually distinguishable on the map.
        const searchInput = document.getElementById('location-search');
        const listButtons = Array.from(document.querySelectorAll('#location-list [data-location-name]'));
        const emptyMsg = document.getElementById('location-empty');

        searchInput?.addEventListener('input', () => {
            const q = searchInput.value.trim().toLowerCase();
            let visibleCount = 0;

            listButtons.forEach((btn) => {
                const match = q === '' || btn.dataset.locationName.includes(q);
                btn.classList.toggle('hidden', !match);
                if (match) visibleCount++;
            });

            emptyMsg.classList.toggle('hidden', visibleCount > 0);
        });
    </script>
</x-layout>
