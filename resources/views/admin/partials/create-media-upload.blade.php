{{-- resources/views/admin/partials/create-media-upload.blade.php --}}
{{-- Images/PDFs picker for a CREATE form. Submits alongside the rest of the
     form (images[]/pdfs[]/main_image_index) — the controller's store()
     processes them inline via HandlesInlineMediaUploads. --}}
<section class="rounded-xl border border-black/20 bg-black/10 p-6 space-y-6">
    <div class="flex items-center justify-between gap-4">
        <h2 class="text-base font-semibold text-white">Media</h2>
        <span class="text-xs text-white/50">Upload now — set "Main" before saving</span>
    </div>

    <input type="hidden" name="main_image_index" id="main_image_index" value="0">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="rounded-md bg-sage-900 p-4 border border-white/10">
            <div class="text-white font-semibold mb-2">Images</div>
            <input type="file" id="images_input" name="images[]" multiple accept="image/*"
                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white file:mr-3 file:rounded-md file:border-0 file:bg-white/10 file:px-3 file:py-2 file:text-white hover:file:bg-white/15">
            <p class="mt-2 text-xs text-white/60">First image becomes <span class="text-white/80 font-semibold">Main</span> by default.</p>
        </div>
        <div class="rounded-md bg-sage-900 p-4 border border-white/10">
            <div class="text-white font-semibold mb-2">PDFs</div>
            <input type="file" id="pdfs_input" name="pdfs[]" multiple accept="application/pdf"
                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white file:mr-3 file:rounded-md file:border-0 file:bg-white/10 file:px-3 file:py-2 file:text-white hover:file:bg-white/15">
            <p class="mt-2 text-xs text-white/60">Max 50MB per file.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-sage rounded-md p-4 border border-black/20">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-white">Selected images</h3>
                <span id="images_count" class="text-xs text-white/60">0</span>
            </div>
            <div id="images_preview" class="flex flex-wrap gap-2 items-start">
                <p class="text-white/80 text-sm" id="images_empty">No images selected.</p>
            </div>
        </div>
        <div class="bg-sage rounded-md p-4 border border-black/20">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-white">Selected PDFs</h3>
                <span id="pdfs_count" class="text-xs text-white/60">0</span>
            </div>
            <div id="pdfs_preview" class="space-y-3">
                <p class="text-white/80 text-sm" id="pdfs_empty">No PDFs selected.</p>
            </div>
        </div>
    </div>
</section>

<script>
    (function() {
        const imagesInput = document.getElementById('images_input');
        const pdfsInput = document.getElementById('pdfs_input');
        const imagesPreview = document.getElementById('images_preview');
        const pdfsPreview = document.getElementById('pdfs_preview');
        const imagesEmpty = document.getElementById('images_empty');
        const pdfsEmpty = document.getElementById('pdfs_empty');
        const imagesCount = document.getElementById('images_count');
        const pdfsCount = document.getElementById('pdfs_count');
        const mainIndexHidden = document.getElementById('main_image_index');

        let imageFiles = [];
        let pdfFiles = [];

        function humanSize(bytes) {
            if (!bytes && bytes !== 0) return '—';
            const units = ['B', 'KB', 'MB', 'GB'];
            let i = 0,
                n = bytes;
            while (n >= 1024 && i < units.length - 1) {
                n /= 1024;
                i++;
            }
            return `${n.toFixed(i === 0 ? 0 : 1)} ${units[i]}`;
        }

        function syncFileList(inputEl, filesArr) {
            const dt = new DataTransfer();
            filesArr.forEach(f => dt.items.add(f));
            inputEl.files = dt.files;
        }

        function renderImages() {
            imagesPreview.innerHTML = '';
            imagesCount.textContent = `${imageFiles.length}`;
            if (imageFiles.length === 0) {
                imagesEmpty.style.display = '';
                imagesPreview.appendChild(imagesEmpty);
                mainIndexHidden.value = '0';
                return;
            }
            imagesEmpty.style.display = 'none';

            let mainIndex = parseInt(mainIndexHidden.value || '0', 10);
            if (Number.isNaN(mainIndex) || mainIndex < 0) mainIndex = 0;
            if (mainIndex > imageFiles.length - 1) mainIndex = 0;
            mainIndexHidden.value = String(mainIndex);

            imageFiles.forEach((file, idx) => {
                const card = document.createElement('div');
                card.className = 'group w-32 shrink-0 rounded-md bg-sage-900 border border-white/10 overflow-hidden';
                const url = URL.createObjectURL(file);

                const preview = document.createElement('div');
                preview.className = 'w-32 h-44 bg-black/10 flex items-center justify-center overflow-hidden';
                const img = document.createElement('img');
                img.src = url;
                img.alt = file.name;
                img.loading = 'lazy';
                img.className = 'w-full h-full object-contain block';
                img.addEventListener('load', () => URL.revokeObjectURL(url));
                img.addEventListener('error', () => URL.revokeObjectURL(url));
                preview.appendChild(img);

                const footer = document.createElement('div');
                footer.className = 'p-2 space-y-2';

                const name = document.createElement('div');
                name.className = 'text-[11px] text-white/80 truncate';
                name.textContent = file.name;

                const meta = document.createElement('div');
                meta.className = 'text-[10px] text-white/50';
                meta.textContent = humanSize(file.size);

                const actions = document.createElement('div');
                actions.className = 'grid grid-cols-2 gap-2 items-center';

                const isMain = idx === parseInt(mainIndexHidden.value || '0', 10);
                const mainBtn = document.createElement('button');
                mainBtn.type = 'button';
                mainBtn.className = isMain ?
                    'h-7 rounded bg-white/15 text-white text-[10px] font-semibold' :
                    'h-7 rounded bg-white/10 text-white text-[10px] hover:bg-white/20 transition';
                mainBtn.textContent = isMain ? 'Main' : 'Set main';
                mainBtn.addEventListener('click', () => {
                    mainIndexHidden.value = String(idx);
                    renderImages();
                });

                const delBtn = document.createElement('button');
                delBtn.type = 'button';
                delBtn.className = 'h-7 rounded bg-red-600 text-white text-[10px] hover:bg-red-700 transition';
                delBtn.textContent = 'Remove';
                delBtn.addEventListener('click', () => {
                    imageFiles.splice(idx, 1);
                    let mi = parseInt(mainIndexHidden.value || '0', 10);
                    if (idx < mi) mi--;
                    if (mi < 0) mi = 0;
                    if (mi > imageFiles.length - 1) mi = 0;
                    mainIndexHidden.value = String(mi);
                    syncFileList(imagesInput, imageFiles);
                    renderImages();
                });

                actions.appendChild(mainBtn);
                actions.appendChild(delBtn);
                footer.appendChild(name);
                footer.appendChild(meta);
                footer.appendChild(actions);
                card.appendChild(preview);
                card.appendChild(footer);
                imagesPreview.appendChild(card);
            });
        }

        function renderPdfs() {
            pdfsPreview.innerHTML = '';
            pdfsCount.textContent = `${pdfFiles.length}`;
            if (pdfFiles.length === 0) {
                pdfsEmpty.style.display = '';
                pdfsPreview.appendChild(pdfsEmpty);
                return;
            }
            pdfsEmpty.style.display = 'none';

            pdfFiles.forEach((file, idx) => {
                const row = document.createElement('div');
                row.className = 'rounded-md bg-sage-900 border border-white/10 p-3 flex items-center justify-between gap-4';

                const left = document.createElement('div');
                left.className = 'min-w-0';
                const name = document.createElement('div');
                name.className = 'text-white font-semibold text-sm truncate';
                name.textContent = file.name;
                const meta = document.createElement('div');
                meta.className = 'text-[11px] text-white/50 mt-1';
                meta.textContent = humanSize(file.size);
                left.appendChild(name);
                left.appendChild(meta);

                const right = document.createElement('div');
                right.className = 'shrink-0 flex items-center gap-2';

                const openBtn = document.createElement('button');
                openBtn.type = 'button';
                openBtn.className = 'h-7 rounded bg-white/10 px-3 text-[10px] text-white hover:bg-white/20 transition';
                openBtn.textContent = 'Open';
                openBtn.addEventListener('click', () => window.open(URL.createObjectURL(file), '_blank', 'noopener'));

                const delBtn = document.createElement('button');
                delBtn.type = 'button';
                delBtn.className = 'h-7 rounded bg-red-600 px-3 text-[10px] text-white hover:bg-red-700 transition';
                delBtn.textContent = 'Remove';
                delBtn.addEventListener('click', () => {
                    pdfFiles.splice(idx, 1);
                    syncFileList(pdfsInput, pdfFiles);
                    renderPdfs();
                });

                right.appendChild(openBtn);
                right.appendChild(delBtn);
                row.appendChild(left);
                row.appendChild(right);
                pdfsPreview.appendChild(row);
            });
        }

        if (imagesInput) {
            imagesInput.addEventListener('change', () => {
                imageFiles = Array.from(imagesInput.files || []);
                mainIndexHidden.value = '0';
                renderImages();
            });
        }
        if (pdfsInput) {
            pdfsInput.addEventListener('change', () => {
                pdfFiles = Array.from(pdfsInput.files || []);
                renderPdfs();
            });
        }

        renderImages();
        renderPdfs();
    })();
</script>
