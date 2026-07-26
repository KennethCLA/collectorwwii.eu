{{-- resources/views/admin/partials/lookup-modal.blade.php --}}
{{-- Reusable inline-add modal for tree and flat lookup selects.
     Usage: place @include('admin.partials.lookup-modal') just before @endsection.
     Trigger: <button data-lookup-add data-type="book-topics" data-select="#topic_id">
     Tree types show a parent selector; flat types show only a name field. --}}

<dialog id="lookupModal" class="rounded-xl p-0 backdrop:bg-black/60">
    <form method="dialog" class="w-[min(520px,92vw)] bg-[#2b322a] text-white">
        <div class="p-4 border-b border-white/10 flex items-center justify-between">
            <h3 id="lookupModalTitle" class="text-lg font-semibold">Add</h3>
            <button class="px-3 py-1 rounded-md bg-white/10">✕</button>
        </div>
        <div class="p-4 space-y-3">
            <div id="lookupParentWrap" class="hidden space-y-1">
                <label class="text-sm text-white/80">Under parent <span class="text-white/50">(optional)</span></label>
                <select id="lookupParent"
                    class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white">
                    <option value="">— root level</option>
                </select>
            </div>
            <div class="space-y-1">
                <label for="lookupName" class="text-sm text-white/80">Name</label>
                <input id="lookupName" type="text"
                    class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white"
                    placeholder="Name..." />
            </div>
            <p id="lookupError" class="text-sm text-red-300 hidden"></p>
        </div>
        <div class="p-4 border-t border-white/10 flex justify-end gap-2">
            <button value="cancel" class="px-4 py-2 rounded-md bg-white/10">Cancel</button>
            <button id="lookupSaveBtn" type="button" class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700">Add</button>
        </div>
    </form>
</dialog>

<script>
    (() => {
        const modal     = document.getElementById('lookupModal');
        const titleEl   = document.getElementById('lookupModalTitle');
        const nameEl    = document.getElementById('lookupName');
        const parentWrap = document.getElementById('lookupParentWrap');
        const parentEl  = document.getElementById('lookupParent');
        const errEl     = document.getElementById('lookupError');
        const saveBtn   = document.getElementById('lookupSaveBtn');

        // Single source of truth — pulled from LookupIndexController::types()
        const TREE_TYPES = @json(\App\Http\Controllers\Admin\Ajax\LookupController::treeTypes());

        const parentsUrl = (type) =>
            `{{ route('admin.lookups.ajax.parents', ['type' => '___']) }}`.replace('___', type);
        const storeUrl = (type) =>
            `{{ route('admin.lookups.ajax.store', ['type' => '___']) }}`.replace('___', type);

        let current = { type: null, select: null };

        async function openModal(type, selectSelector) {
            current.type   = type;
            current.select = document.querySelector(selectSelector);
            titleEl.textContent = `Add ${type.replace(/-/g, ' ')}`;
            errEl.classList.add('hidden');
            errEl.textContent = '';
            nameEl.value = '';

            const isTree = TREE_TYPES.includes(type);
            parentWrap.classList.toggle('hidden', !isTree);

            if (isTree) {
                parentEl.innerHTML = '<option value="">— root level</option>';
                try {
                    const res = await fetch(parentsUrl(type), { headers: { 'Accept': 'application/json' } });
                    if (res.ok) {
                        const options = await res.json();
                        options.forEach(opt => parentEl.add(new Option(opt.name, opt.id)));
                    } else {
                        errEl.textContent = 'Could not load parent options.';
                        errEl.classList.remove('hidden');
                    }
                } catch (_) {
                    errEl.textContent = 'Could not load parent options.';
                    errEl.classList.remove('hidden');
                }
            }

            modal.showModal();
            setTimeout(() => nameEl.focus(), 50);
        }

        function upsertNativeOption(selectEl, value, label) {
            let opt = selectEl.querySelector(`option[value="${CSS.escape(value)}"]`);
            if (!opt) {
                opt = new Option(label, value, true, true);
                selectEl.add(opt);
            } else {
                opt.text = label;
                opt.selected = true;
            }
            selectEl.dispatchEvent(new Event('input', { bubbles: true }));
            selectEl.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function syncChoices(selectEl, value, label) {
            if (!window.__choicesInstances) return false;
            const instance = window.__choicesInstances.find(c => {
                try { return c.passedElement.element === selectEl; } catch { return false; }
            });
            if (!instance) return false;
            instance.setChoices([{ value: String(value), label: label, selected: true }], 'value', 'label', false);
            instance.setChoiceByValue(String(value));
            return true;
        }

        async function saveLookup() {
            const name = (nameEl.value || '').trim();
            if (!name) {
                errEl.textContent = 'Name is required.';
                errEl.classList.remove('hidden');
                return;
            }
            if (!current.select) {
                errEl.textContent = 'Select not found on page.';
                errEl.classList.remove('hidden');
                return;
            }

            saveBtn.disabled = true;

            try {
                const body = { name };
                if (TREE_TYPES.includes(current.type) && parentEl.value) {
                    body.parent_id = parseInt(parentEl.value);
                }

                const res = await fetch(storeUrl(current.type), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(body),
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    errEl.textContent = data?.message || data?.errors?.name?.[0] || 'Failed to add.';
                    errEl.classList.remove('hidden');
                    return;
                }

                const value = String(data.id);
                const label = data.name;

                const handledByChoices = syncChoices(current.select, value, label);
                if (!handledByChoices) {
                    upsertNativeOption(current.select, value, label);
                }

                modal.close();
                current.select.focus();

            } catch (e) {
                errEl.textContent = 'Network error.';
                errEl.classList.remove('hidden');
            } finally {
                saveBtn.disabled = false;
            }
        }

        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-lookup-add]');
            if (!btn) return;
            openModal(btn.dataset.type, btn.dataset.select);
        });

        saveBtn.addEventListener('click', saveLookup);

        nameEl.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); saveLookup(); }
        });
    })();
</script>
