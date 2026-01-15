/**
 * Simple reusable media picker modal.
 * Usage: window.openMediaPicker({
 *   type: 'payment_proof' | 'product_photo',
 *   title: 'Pilih Bukti Transfer',
 *   listUrl: '/media/payment-proof/list',
 *   uploadUrl: '/media',
 *   context: { purchase_id: 1 }, // optional extra form data when upload
 *   onSelect: (item) => {...}    // receives {id, filename, url, mime, size}
 * });
 */
(function () {
    function createEl(tag, cls, html) {
        const el = document.createElement(tag);
        if (cls) el.className = cls;
        if (html) el.innerHTML = html;
        return el;
    }

    function bytesToSize(bytes) {
        if (!bytes && bytes !== 0) return '';
        const sizes = ['B', 'KB', 'MB', 'GB'];
        if (bytes === 0) return '0 B';
        const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)), 10);
        return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
    }

    window.openMediaPicker = async function openMediaPicker(opts) {
        const options = Object.assign({
            type: 'payment_proof',
            title: 'Pilih Media',
            listUrl: null,
            uploadUrl: null,
            context: {},
            onSelect: null,
        }, opts || {});

        if (!options.listUrl || !options.uploadUrl) {
            console.error('Media picker: listUrl dan uploadUrl wajib diisi');
            return;
        }

        let items = [];
        let filtered = [];
        let selected = null;

        const overlay = createEl('div', 'fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50');
        const modal = createEl('div', 'bg-white rounded-2xl shadow-2xl w-full max-w-5xl overflow-hidden flex flex-col');
        overlay.appendChild(modal);

        modal.innerHTML = `
            <div class="flex items-center justify-between px-5 py-3 border-b">
                <h3 class="text-xl font-semibold">${options.title}</h3>
                <button class="text-gray-500 text-2xl leading-none" data-close>&times;</button>
            </div>
            <div class="flex items-center gap-4 px-5 pt-4 text-sm font-medium text-gray-600 border-b">
                <button class="text-blue-600 border-b-2 border-blue-600 pb-3" data-tab="library">Media Library</button>
                <button class="text-gray-400 pb-3 cursor-not-allowed" title="Upload tersedia di bawah">Upload Files</button>
            </div>
            <div class="grid grid-cols-12 h-[520px]">
                <div class="col-span-8 border-r flex flex-col">
                    <div class="p-4 flex items-center gap-2">
                        <input type="text" placeholder="Search media..." class="flex-1 border rounded-lg px-3 py-2 text-sm" data-search>
                        <button class="p-2 border rounded-lg" title="Refresh" data-refresh>&#8635;</button>
                    </div>
                    <div class="px-4 pb-4 grid grid-cols-3 md:grid-cols-4 gap-3 overflow-y-auto flex-1" data-grid></div>
                    <div class="px-4 pb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Upload baru (drag & drop atau pilih file)</label>
                        <div class="border-2 border-dashed rounded-xl p-4 text-center text-gray-600 hover:bg-gray-50 cursor-pointer" data-dropzone>
                            <div class="text-sm">Drop file di sini atau klik untuk pilih</div>
                            <input type="file" class="hidden" data-file />
                        </div>
                    </div>
                </div>
                <div class="col-span-4 flex flex-col" data-detail>
                    <div class="flex-1 flex items-center justify-center text-gray-500 text-sm">
                        <div class="text-center space-y-2">
                            <div class="text-3xl">🖼️</div>
                            <div>Pilih gambar untuk melihat detail</div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        const close = () => overlay.remove();
        overlay.addEventListener('click', (e) => { if (e.target === overlay || e.target.dataset.close !== undefined) close(); });

        const gridEl = modal.querySelector('[data-grid]');
        const searchEl = modal.querySelector('[data-search]');
        const refreshBtn = modal.querySelector('[data-refresh]');
        const detailEl = modal.querySelector('[data-detail]');
        const dropzone = modal.querySelector('[data-dropzone]');
        const fileInput = modal.querySelector('[data-file]');

        function renderDetail(item) {
            if (!item) {
                detailEl.innerHTML = `
                    <div class="flex-1 flex items-center justify-center text-gray-500 text-sm">
                        <div class="text-center space-y-2">
                            <div class="text-3xl">🖼️</div>
                            <div>Pilih gambar untuk melihat detail</div>
                        </div>
                    </div>`;
                return;
            }
            detailEl.innerHTML = `
                <div class="p-4 space-y-2">
                    <div class="text-sm font-semibold">${item.filename}</div>
                    <div class="text-xs text-gray-500">${item.mime || ''}</div>
                    <div class="text-xs text-gray-500">${bytesToSize(item.size)}</div>
                    <a href="${item.url}" target="_blank" class="text-blue-600 text-sm">Buka</a>
                    <div class="pt-2">
                        <button class="px-3 py-2 bg-primary text-white rounded-lg" data-select>Gunakan</button>
                    </div>
                </div>
            `;
            detailEl.querySelector('[data-select]')?.addEventListener('click', () => {
                if (typeof options.onSelect === 'function') {
                    options.onSelect(item);
                }
                close();
            });
        }

        function renderGrid(list) {
            gridEl.innerHTML = '';
            if (!list.length) {
                gridEl.innerHTML = '<div class="text-sm text-gray-500 col-span-3">Belum ada media.</div>';
                renderDetail(null);
                return;
            }
            list.forEach(item => {
                const btn = createEl('button', 'border rounded-lg overflow-hidden bg-white shadow-sm hover:shadow-md transition text-left');
                btn.innerHTML = `
                    <div class="h-28 bg-gray-100 flex items-center justify-center">
                        ${item.mime && item.mime.startsWith('image/') ? `<img src="${item.url}" class="object-cover h-full w-full">` : `<span class="text-xs text-gray-500 px-2 text-center">${item.filename}</span>`}
                    </div>
                    <div class="p-2">
                        <div class="text-sm font-semibold truncate">${item.filename}</div>
                        <div class="text-xs text-gray-500">${item.mime || ''}</div>
                    </div>
                `;
                btn.addEventListener('click', () => {
                    selected = item;
                    renderDetail(item);
                });
                gridEl.appendChild(btn);
            });
        }

        async function loadList() {
            gridEl.innerHTML = '<div class="text-sm text-gray-500 col-span-3">Memuat...</div>';
            try {
                const res = await fetch(options.listUrl, { headers: { 'Accept': 'application/json' }});
                const data = await res.json();
                items = data.data || [];
                filtered = items;
                renderGrid(filtered);
            } catch (err) {
                console.error(err);
                gridEl.innerHTML = '<div class="text-sm text-red-500 col-span-3">Gagal memuat media.</div>';
            }
        }

        searchEl.addEventListener('input', () => {
            const q = searchEl.value.toLowerCase();
            filtered = items.filter(it => (it.filename || '').toLowerCase().includes(q));
            renderGrid(filtered);
        });
        refreshBtn.addEventListener('click', loadList);

        // Drag-drop / click upload
        const triggerPick = () => fileInput.click();
        dropzone.addEventListener('click', triggerPick);
        dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('bg-gray-50'); });
        dropzone.addEventListener('dragleave', () => dropzone.classList.remove('bg-gray-50'));
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('bg-gray-50');
            if (e.dataTransfer.files.length) {
                handleUpload(e.dataTransfer.files[0]);
            }
        });
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length) handleUpload(fileInput.files[0]);
        });

        async function handleUpload(file) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('type', options.type);
            if (options.context) {
                Object.entries(options.context).forEach(([k, v]) => {
                    if (v !== undefined && v !== null) formData.append(k, v);
                });
            }
            try {
                const res = await fetch(options.uploadUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content'),
                    },
                    body: formData,
                });
                const body = await res.json();
                if (res.status === 201) {
                    const item = body.media;
                    if (typeof options.onSelect === 'function') {
                        options.onSelect(item);
                    }
                    close();
                } else {
                    alert(body.message || 'Upload gagal');
                }
            } catch (err) {
                console.error(err);
                alert('Upload gagal');
            }
        }

        document.body.appendChild(overlay);
        loadList();
    };
})();
