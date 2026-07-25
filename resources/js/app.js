// resources/js/app.js

import "./books-attachments-preview";

import { Fancybox } from "@fancyapps/ui";
import "@fancyapps/ui/dist/fancybox/fancybox.css";

import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";

import "../css/app.css";
import "../css/components.css";
import "../css/home-bg.css";

Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();

window.Fancybox = Fancybox;

Fancybox.bind("[data-fancybox]", {
    Toolbar: true,
    infinite: true,
    wheel: "zoom",
    Hash: false,
});

// Media image drag-to-reorder
(() => {
    function initReorder(container) {
        let dragSrc = null;

        container.addEventListener('dragstart', (e) => {
            const card = e.target.closest('[data-media-id]');
            if (!card) return;
            dragSrc = card;
            e.dataTransfer.effectAllowed = 'move';
            requestAnimationFrame(() => card.classList.add('opacity-30'));
        });

        container.addEventListener('dragend', () => {
            container.querySelectorAll('[data-media-id]').forEach((c) => {
                c.classList.remove('opacity-30', 'outline', 'outline-2', 'outline-offset-2', 'outline-white/50');
            });
            dragSrc = null;
        });

        container.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            const card = e.target.closest('[data-media-id]');
            container.querySelectorAll('[data-media-id]').forEach((c) =>
                c.classList.remove('outline', 'outline-2', 'outline-offset-2', 'outline-white/50')
            );
            if (card && card !== dragSrc) {
                card.classList.add('outline', 'outline-2', 'outline-offset-2', 'outline-white/50');
            }
        });

        container.addEventListener('drop', (e) => {
            e.preventDefault();
            const card = e.target.closest('[data-media-id]');
            container.querySelectorAll('[data-media-id]').forEach((c) =>
                c.classList.remove('outline', 'outline-2', 'outline-offset-2', 'outline-white/50')
            );
            if (!card || card === dragSrc || !dragSrc) return;

            const rect = card.getBoundingClientRect();
            if (e.clientX < rect.left + rect.width / 2) {
                container.insertBefore(dragSrc, card);
            } else {
                container.insertBefore(dragSrc, card.nextSibling);
            }

            saveReorder(container);
        });
    }

    async function saveReorder(container) {
        const url = container.dataset.reorderUrl;
        const ids = [...container.querySelectorAll('[data-media-id]')].map((c) =>
            parseInt(c.dataset.mediaId)
        );

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ ids }),
            });
            if (!res.ok) throw new Error('reorder failed');
            container.style.outline = '2px solid rgba(52,211,153,.45)';
            setTimeout(() => { container.style.outline = ''; }, 700);
        } catch {
            container.style.outline = '2px solid rgba(239,68,68,.45)';
            setTimeout(() => { container.style.outline = ''; }, 700);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-reorder-container]').forEach(initReorder);
    });
})();

// Init Choices only where we ask for it — loaded on demand so public
// pages (which never render .js-select) don't pay for the admin-only library.
window.__choicesInstances = window.__choicesInstances || [];

document.addEventListener("DOMContentLoaded", async () => {
    const selects = document.querySelectorAll("select.js-select");
    if (selects.length === 0) return;

    const [{ default: Choices }] = await Promise.all([
        import("choices.js"),
        import("choices.js/public/assets/styles/choices.min.css"),
    ]);

    selects.forEach((el) => {
        if (el.dataset.enhanced) return;
        el.dataset.enhanced = "1";
        const instance = new Choices(el, {
            searchEnabled: true,
            shouldSort: false,
            itemSelectText: "",
            allowHTML: false,
            position: "bottom",
        });
        window.__choicesInstances.push(instance);
    });
});
