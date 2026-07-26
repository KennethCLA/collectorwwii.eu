<x-layout :mainClass="'w-full px-4 py-6 sm:px-6 lg:px-8'">
    <link rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" />

    <style>
        .maplibregl-popup-content {
            background: #2d3b2f;
            color: #f3f0e6;
            border-radius: 10px;
            padding: 12px;
        }
        .maplibregl-popup-tip {
            border-top-color: #2d3b2f !important;
            border-bottom-color: #2d3b2f !important;
        }
        .maplibregl-popup-close-button {
            color: #f3f0e6;
            font-size: 18px;
            padding: 4px 8px;
        }

        .maplibregl-ctrl-group {
            background: #2d3b2f !important;
            border: 1px solid rgba(194,178,128,0.3) !important;
            box-shadow: none !important;
        }
        .maplibregl-ctrl-group button {
            background: transparent !important;
        }
        .maplibregl-ctrl-group button + button {
            border-top: 1px solid rgba(194,178,128,0.2) !important;
        }
        .maplibregl-ctrl-zoom-in .maplibregl-ctrl-icon,
        .maplibregl-ctrl-zoom-out .maplibregl-ctrl-icon,
        .maplibregl-ctrl-compass .maplibregl-ctrl-icon {
            filter: invert(85%) sepia(8%) saturate(400%) hue-rotate(10deg) brightness(95%);
        }
        .maplibregl-ctrl-group button:hover {
            background: rgba(194,178,128,0.15) !important;
        }

        .maplibregl-ctrl-attrib {
            background: rgba(45,59,47,0.85) !important;
            color: #c2b280 !important;
            padding: 2px !important;
        }
        .maplibregl-ctrl-attrib a {
            color: #c2b280 !important;
        }
        .maplibregl-ctrl-attrib-button {
            filter: invert(85%) sepia(8%) saturate(400%) hue-rotate(10deg) brightness(95%);
        }
        /* Force the collapsed state regardless of MapLibre's own width-based
           toggle class — required MapTiler/OSM credit stays reachable via
           the icon, just not shown as a permanent text banner. */
        .maplibregl-ctrl-attrib .maplibregl-ctrl-attrib-inner {
            display: none !important;
        }
        .maplibregl-ctrl-attrib.maplibregl-compact-show .maplibregl-ctrl-attrib-inner,
        .maplibregl-ctrl-attrib:hover .maplibregl-ctrl-attrib-inner,
        .maplibregl-ctrl-attrib:focus-within .maplibregl-ctrl-attrib-inner {
            display: inline !important;
            padding: 0 4px;
        }
    </style>

    <div class="mx-auto w-full max-w-7xl space-y-4 pt-6">
        <div class="rounded-2xl bg-black/20 p-4 ring-1 ring-black/30 sm:p-6 noise-texture">
            <p class="font-stencil text-xs tracking-[0.4em] text-khaki/60 uppercase mb-1">Kriegsschauplatz · Operationskarte</p>
            <h1 class="font-stencil text-3xl font-black tracking-[0.2em] text-white uppercase">LAGEBERICHT</h1>
            <p class="font-mono text-[10px] tracking-[0.25em] text-white/40 mt-1 uppercase">Feldkarte · WK II Standorte · Klicken für Details</p>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="relative rounded-2xl bg-black/20 p-3 ring-1 ring-black/30 sm:p-4">
                @if($maptilerKey)
                <div id="visit-map" class="h-[45vh] min-h-[360px] w-full rounded-xl lg:h-[65vh]"></div>
                @else
                <div class="flex h-[45vh] min-h-[360px] w-full items-center justify-center rounded-xl bg-black/30 text-center text-sm text-white/60 lg:h-[65vh]">
                    Map unavailable — MAPTILER_API_KEY not configured.
                </div>
                @endif
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

    @if($maptilerKey)
    <script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js"></script>
    <script>
        const locations = @json($locations);

        const map = new maplibregl.Map({
            container: 'visit-map',
            style: 'https://api.maptiler.com/maps/dataviz-dark/style.json?key={{ $maptilerKey }}',
            center: [4.3517, 50.8503],
            zoom: 4,
            minZoom: 2,
            attributionControl: false,
        });

        map.addControl(new maplibregl.NavigationControl(), 'top-right');
        // MapTiler/OSM attribution must stay per their free-tier terms —
        // collapsed to a small icon instead of the full text banner.
        map.addControl(new maplibregl.AttributionControl({ compact: true }), 'bottom-right');

        const byId = {};
        locations.forEach((loc) => { byId[loc.id] = loc; });

        const geojson = {
            type: 'FeatureCollection',
            features: locations.map((loc) => ({
                type: 'Feature',
                properties: { id: loc.id },
                geometry: { type: 'Point', coordinates: [loc.lng, loc.lat] },
            })),
        };

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
                <div class="space-y-2" style="min-width: 220px; max-width: 280px;">
                    <div>
                        <div style="font-weight:700; font-size:14px;">${loc.name}</div>
                        <div style="font-size:12px; color:#c2b280;">${loc.coordinates}</div>
                    </div>
                    ${desc ? `<div style="font-size:13px; line-height:1.4;">${desc}</div>` : ''}
                    ${(loc.images || []).length ? `<div class="grid grid-cols-2 gap-1">${imageThumbs}</div>` : ''}
                    ${hiddenLinks}
                </div>`;
        };

        let activePopup = null;

        const openPopupFor = (loc) => {
            if (activePopup) activePopup.remove();
            activePopup = new maplibregl.Popup({ closeButton: true, maxWidth: '300px' })
                .setLngLat([loc.lng, loc.lat])
                .setHTML(popupHtml(loc))
                .addTo(map);

            activePopup.getElement()?.querySelectorAll('[data-fancybox]').forEach((el) => {
                el.addEventListener('click', (ev) => {
                    ev.preventDefault();
                    ev.stopPropagation();
                    const gallery = el.dataset.fancybox;
                    const anchors = Array.from(activePopup.getElement().querySelectorAll('[data-fancybox="' + gallery + '"]'));
                    const items = anchors.map((a) => ({ src: a.href, type: 'image' }));
                    const idx = anchors.indexOf(el);
                    window.Fancybox.show(items, { startIndex: Math.max(0, idx) });
                });
            });
        };

        map.on('load', () => {
            map.addSource('locations', {
                type: 'geojson',
                data: geojson,
                cluster: true,
                clusterMaxZoom: 13,
                clusterRadius: 50,
            });

            map.addLayer({
                id: 'clusters',
                type: 'circle',
                source: 'locations',
                filter: ['has', 'point_count'],
                paint: {
                    'circle-color': 'rgba(194,178,128,0.85)',
                    'circle-stroke-color': '#2d3b2f',
                    'circle-stroke-width': 2,
                    'circle-radius': ['step', ['get', 'point_count'], 16, 5, 20, 15, 26],
                },
            });

            map.addLayer({
                id: 'cluster-count',
                type: 'symbol',
                source: 'locations',
                filter: ['has', 'point_count'],
                layout: {
                    'text-field': '{point_count_abbreviated}',
                    'text-size': 12,
                    'text-font': ['Noto Sans Bold'],
                },
                paint: { 'text-color': '#2d3b2f' },
            });

            map.addLayer({
                id: 'unclustered-point',
                type: 'circle',
                source: 'locations',
                filter: ['!', ['has', 'point_count']],
                paint: {
                    'circle-color': '#c2b280',
                    'circle-stroke-color': '#2d3b2f',
                    'circle-stroke-width': 2,
                    'circle-radius': 8,
                },
            });

            map.on('click', 'clusters', (e) => {
                const features = map.queryRenderedFeatures(e.point, { layers: ['clusters'] });
                const clusterId = features[0].properties.cluster_id;
                map.getSource('locations').getClusterExpansionZoom(clusterId, (err, zoom) => {
                    if (err) return;
                    map.easeTo({ center: features[0].geometry.coordinates, zoom });
                });
            });

            map.on('click', 'unclustered-point', (e) => {
                const loc = byId[e.features[0].properties.id];
                if (loc) openPopupFor(loc);
            });

            ['clusters', 'unclustered-point'].forEach((layer) => {
                map.on('mouseenter', layer, () => { map.getCanvas().style.cursor = 'pointer'; });
                map.on('mouseleave', layer, () => { map.getCanvas().style.cursor = ''; });
            });

            const bounds = locations.map((loc) => [loc.lng, loc.lat]);
            if (bounds.length > 0) {
                const b = bounds.reduce((acc, c) => acc.extend(c), new maplibregl.LngLatBounds(bounds[0], bounds[0]));
                map.fitBounds(b, { padding: 60, maxZoom: 12 });
            }
        });

        window.focusMarker = (id) => {
            const loc = byId[id];
            if (!loc) return;
            map.flyTo({ center: [loc.lng, loc.lat], zoom: 12 });
            map.once('moveend', () => openPopupFor(loc));
        };
    </script>
    @endif

    <script>
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
