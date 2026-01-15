<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-900">Media Gallery</h2>
                <p class="text-sm text-gray-500">Bukti transfer & foto produk dalam satu tempat.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-0 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-2 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Filter Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 text-gray-900 sm:p-6">
                    {{-- Type Tabs --}}
                    <div class="-mx-2 mb-6 flex items-center gap-3 overflow-x-auto px-2 pb-2 sm:flex-wrap">
                        @foreach($types as $t)
                            <a href="{{ route('media.index', ['type' => $t]) }}"
                               class="inline-flex shrink-0 items-center rounded-lg px-4 py-2 text-sm font-medium transition {{ $type === $t ? 'bg-primary text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                @if($t === 'payment_proof')
                                    Bukti Transfer
                                @elseif($t === 'product_photo')
                                    Foto Produk
                                @elseif($t === 'shipment_proof')
                                    Bukti Pengiriman
                                @else
                                    {{ $t }}
                                @endif
                            </a>
                        @endforeach
                    </div>

                    {{-- Search & Upload Form --}}
                    <form method="GET" action="{{ route('media.index') }}" class="mb-6">
                        <input type="hidden" name="type" value="{{ $type }}">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                            <div class="w-full sm:flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cari Media</label>
                                <input type="text" name="q" value="{{ $search }}" placeholder="Cari filename..." class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                            </div>
                            <div class="w-full sm:w-auto">
                                <button type="submit" class="w-full h-10 px-6 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-hover transition sm:w-auto">
                                    Cari
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- Upload Button (opens modal) --}}
                    <div class="flex justify-start sm:justify-end">
                        <button type="button" onclick="openUploadModal('upload')" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-hover transition shadow-sm hover:shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            Unggah File
                        </button>
                    </div>
                </div>
            </div>

            {{-- Ambu Magic Studio --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" id="aiStudioCard" data-ai-enabled="{{ $aiEnabled ? 'true' : 'false' }}">
                <div class="space-y-4 p-4 text-gray-900 sm:p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">Ambu Magic Studio</h3>
                            <p class="text-sm text-gray-500">Gunakan Ambu Magic untuk meningkatkan foto produk langsung dari galeri.</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full {{ $aiEnabled ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                            {{ $aiEnabled ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>

                    @if(!$aiEnabled)
                        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg">
                            <p class="text-sm">Ambu Magic Studio belum dikonfigurasi. Hubungi tim yang memiliki akses ke pengaturan untuk menambahkan API key di halaman <a href="{{ route('settings.index') }}" class="underline">Pengaturan</a>.</p>
                        </div>
                    @else
                        <div class="border border-dashed border-gray-200 rounded-lg p-4">
                            <div class="mb-4">
                                <p class="text-sm font-medium text-gray-700 mb-2">Media Sumber</p>
                                <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                                    <div>
                                        <p id="aiMediaSelectorHint" class="text-sm text-gray-700">Belum ada media dipilih.</p>
                                        <p class="text-xs text-gray-500">Gunakan tombol "Ambu Magic" pada kartu media untuk memilih foto.</p>
                                    </div>
                                </div>
                            </div>

                            <x-ai-studio
                                element-prefix="ai"
                                :show-media-selector="true"
                            />
                        </div>
                    @endif
                </div>
            </div>

            {{-- Media Grid Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 text-gray-900 sm:p-6">
                    @php
                        $formatSize = function($bytes) {
                            if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2).' GB';
                            if ($bytes >= 1048576) return number_format($bytes / 1048576, 2).' MB';
                            if ($bytes >= 1024) return number_format($bytes / 1024, 2).' KB';
                            return $bytes.' B';
                        };
                    @endphp
                    @if($media->count() === 0)
                        <div class="text-center text-gray-500 py-12">Belum ada media.</div>
                    @else
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 md:grid-cols-4 lg:grid-cols-5">
                            @foreach($media as $item)
                                <div class="border border-gray-200 rounded-lg overflow-hidden bg-white hover:shadow-md transition cursor-pointer" onclick="openMediaDetail({{ json_encode([
                                    'id' => $item->id,
                                    'filename' => $item->filename,
                                    'url' => $item->url,
                                    'mime' => $item->mime,
                                    'size' => $item->size,
                                    'created_at' => $item->created_at->format('d M Y H:i'),
                                    'uploader' => $item->uploader->name ?? '-',
                                    'extension' => $item->metadata['extension'] ?? ''
                                ]) }})">
                                    <div class="relative w-full bg-gray-50 overflow-hidden" style="padding-top: 100%;">
                                        @if(str_starts_with($item->mime ?? '', 'image/'))
                                            <img src="{{ $item->url }}" alt="{{ $item->filename }}" class="absolute inset-0 w-full h-full object-cover">
                                        @else
                                            <div class="absolute inset-0 flex items-center justify-center text-gray-400 text-xs text-center px-2">
                                                {{ strtoupper($item->metadata['extension'] ?? $item->mime) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-3 space-y-2">
                                        <div class="text-sm font-medium text-gray-900 truncate" title="{{ $item->filename }}">{{ $item->filename }}</div>
                                        <div class="text-xs text-gray-500">{{ $formatSize($item->size) }}</div>
                                        <div class="text-xs text-gray-400">{{ $item->created_at->format('d M Y') }}</div>
                                        @if($aiEnabled && str_starts_with($item->mime ?? '', 'image/') && $item->type === 'product_photo')
                                            <button type="button"
                                                    class="mt-1 w-full text-xs font-semibold text-primary border border-primary rounded-md py-1 hover:bg-primary hover:text-white transition ai-select-btn"
                                                    data-media-id="{{ $item->id }}"
                                                    data-media-filename="{{ $item->filename }}">
                                                Ambu Magic
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Pagination Card --}}
            @if($media->lastPage() > 1)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        {{ $media->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Media Detail Modal --}}
    <div id="mediaDetailModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Detail Media</h3>
                <button onclick="closeMediaDetail()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                {{-- Image Preview --}}
                <div id="modalImagePreview" class="w-full bg-gray-50 rounded-lg flex items-center justify-center overflow-hidden" style="min-height: 300px; max-height: 400px;">
                    <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain">
                    <div id="modalNonImage" class="hidden text-gray-400 text-sm"></div>
                </div>
                {{-- Details --}}
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Nama File:</span>
                        <span id="modalFilename" class="font-medium text-gray-900"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Ukuran:</span>
                        <span id="modalSize" class="text-gray-900"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Diunggah:</span>
                        <span id="modalDate" class="text-gray-900"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Oleh:</span>
                        <span id="modalUploader" class="text-gray-900"></span>
                    </div>
                </div>
                {{-- Actions --}}
                <div class="flex gap-3 pt-4">
                    <a id="modalViewLink" href="" target="_blank" class="flex-1 h-10 flex items-center justify-center bg-blue text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                        Buka di Tab Baru
                    </a>
                    <form id="modalDeleteForm" action="" method="POST" class="flex-1" onsubmit="return confirm('Hapus media ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full h-10 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Media Detail Modal Functions
        function openMediaDetail(media) {
            const modal = document.getElementById('mediaDetailModal');
            const modalImage = document.getElementById('modalImage');
            const modalNonImage = document.getElementById('modalNonImage');

            document.getElementById('modalFilename').textContent = media.filename;
            document.getElementById('modalSize').textContent = formatBytes(media.size);
            document.getElementById('modalDate').textContent = media.created_at;
            document.getElementById('modalUploader').textContent = media.uploader;
            document.getElementById('modalViewLink').href = media.url;
            document.getElementById('modalDeleteForm').action = '/media/' + media.id;

            if (media.mime && media.mime.startsWith('image/')) {
                modalImage.src = media.url;
                modalImage.alt = media.filename;
                modalImage.classList.remove('hidden');
                modalNonImage.classList.add('hidden');
            } else {
                modalImage.classList.add('hidden');
                modalNonImage.textContent = media.extension ? media.extension.toUpperCase() : media.mime;
                modalNonImage.classList.remove('hidden');
            }

            modal.classList.remove('hidden');
        }

        function closeMediaDetail() {
            document.getElementById('mediaDetailModal').classList.add('hidden');
        }

        function formatBytes(bytes) {
            if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' GB';
            if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
            if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
            return bytes + ' B';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeMediaDetail();
        });

        document.getElementById('mediaDetailModal').addEventListener('click', function(e) {
            if (e.target === this) closeMediaDetail();
        });

        // Update hint text when media is selected from gallery
        window.updateMediaSelectorHint = function(mediaName) {
            const hint = document.getElementById('aiMediaSelectorHint');
            if (hint) {
                hint.textContent = mediaName ? `Media dipilih: ${mediaName}` : 'Belum ada media dipilih.';
            }
        };
    </script>

    {{-- Upload Modal with 2 Tabs (Upload Files & Enhance) --}}
    <div id="uploadModal" class="upload-modal">
        <div class="upload-modal__backdrop"></div>
        <div class="upload-modal__container">
            {{-- Header --}}
            <div class="upload-modal__header">
                <h2 class="upload-modal__title">Upload Gambar / Photo</h2>
                <button type="button" class="upload-modal__close" onclick="closeUploadModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            {{-- Tabs Navigation --}}
            <div class="upload-modal__tabs">
                <button type="button" class="upload-modal__tab active" data-tab="upload" onclick="switchUploadTab('upload')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    Upload Files
                </button>
                <button type="button" class="upload-modal__tab" data-tab="enhance" onclick="switchUploadTab('enhance')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 1v6m0 6v6m9-9h-6m-6 0H3m15.364 6.364l-4.243-4.243m0 0l-4.243 4.243m4.243-4.243L9.636 5.636"></path>
                    </svg>
                    Kreasikan
                </button>
            </div>

            {{-- Tab Contents --}}
            <div class="upload-modal__body">
                {{-- Tab 1: Upload Files --}}
                <div id="uploadTabContent" class="upload-modal__tab-content active">
                    <form id="uploadModalForm" action="{{ route('media.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Drag & Drop Area --}}
                        <div class="upload-area" id="uploadDropZone">
                            <input type="file" id="uploadModalInput" name="files[]" multiple accept="image/*" class="upload-area__input" onchange="handleUploadFileSelect(event)">
                            <div class="upload-area__content">
                                <div class="upload-area__icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                </div>
                                <p class="upload-area__text">Seret & Lepas file di sini</p>
                                <p class="upload-area__subtext">atau</p>
                                <button type="button" class="upload-area__button" onclick="document.getElementById('uploadModalInput').click()">
                                    Pilih File
                                </button>
                                <p class="upload-area__hint">Mendukung: JPG, PNG, GIF (Maksimal 10MB per file)</p>
                            </div>
                        </div>

                        {{-- Preview Grid --}}
                        <div id="uploadPreviewContainer" class="upload-preview hidden">
                            <p class="upload-preview__label">File yang akan diunggah (<span id="uploadFileCount">0</span> file):</p>
                            <div id="uploadPreviewGrid" class="upload-preview__grid">
                                <!-- Preview items will be inserted here by JS -->
                            </div>
                        </div>

                        {{-- Category Selection --}}
                        <div class="upload-category" id="uploadCategorySection">
                            <label class="upload-category__label">Kategori</label>
                            <select name="type" class="upload-category__select" id="uploadTypeSelect">
                                <option value="payment_proof" {{ $type === 'payment_proof' ? 'selected' : '' }}>Bukti Transfer</option>
                                <option value="product_photo" {{ $type === 'product_photo' ? 'selected' : '' }}>Foto Produk</option>
                                <option value="shipment_proof" {{ $type === 'shipment_proof' ? 'selected' : '' }}>Bukti Pengiriman</option>
                            </select>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="upload-actions">
                            <button type="button" class="upload-actions__cancel" onclick="closeUploadModal()">
                                Batal
                            </button>
                            <button type="submit" class="upload-actions__submit" id="uploadSubmitBtn" disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                                Insert to Media Library
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Tab 2: Ambu Magic (Enhancement Engine) --}}
                <div id="enhanceTabContent" class="upload-modal__tab-content">
                    @if(!$aiEnabled)
                        <div class="enhance-disabled">
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="enhance-disabled__icon">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <p class="enhance-disabled__text">Ambu Magic Studio belum dikonfigurasi</p>
                            <p class="enhance-disabled__subtext">Hubungi tim yang memiliki akses ke pengaturan untuk menambahkan API key di halaman <a href="{{ route('settings.index') }}" class="text-primary hover:underline">Pengaturan</a>.</p>
                        </div>
                    @else
                        <div class="enhance-layout">
                            {{-- Left: Preview Area --}}
                            <div class="enhance-preview">
                                <div class="enhance-preview__header">
                                    <h3 class="enhance-preview__title">Preview</h3>
                                    <button type="button" class="enhance-preview__select" onclick="triggerEnhanceMediaSelect()">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                            <polyline points="21 15 16 10 5 21"></polyline>
                                        </svg>
                                        Pilih Foto dari Galeri
                                    </button>
                                </div>
                                <div class="enhance-preview__container" id="enhancePreviewContainer">
                                    <div class="enhance-preview__placeholder">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                            <polyline points="21 15 16 10 5 21"></polyline>
                                        </svg>
                                        <p>Belum ada foto dipilih</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Right: Controls --}}
                            <div class="enhance-controls">
                                <input type="hidden" id="enhanceMediaId">

                                {{-- Background Color --}}
                                <div class="enhance-control">
                                    <label class="enhance-control__label">Warna Latar</label>
                                    <input type="color" id="enhanceBackgroundColor" class="enhance-control__color" value="{{ $aiDefaults['background_color'] }}">
                                    <p class="enhance-control__hint">Ambu Magic akan menggunakan HEX color ini saat solid background aktif.</p>
                                </div>

                                {{-- Solid Background Toggle --}}
                                <div class="enhance-control">
                                    <label class="enhance-control__label">Solid Background</label>
                                    <label class="enhance-control__checkbox">
                                        <input type="checkbox" id="enhanceUseSolid" {{ $aiDefaults['use_solid_background'] ? 'checked' : '' }}>
                                        <span>Paksa latar solid rata</span>
                                    </label>
                                </div>

                                {{-- Quick Presets --}}
                                <div class="enhance-control">
                                    <label class="enhance-control__label">Quick Presets</label>
                                    <div id="enhanceQuickPresets" class="ai-quick-presets" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                                        <!-- Will be populated by JavaScript -->
                                    </div>
                                </div>

                                {{-- Custom Features --}}
                                <div class="enhance-control">
                                    <label class="enhance-control__label">Custom Preset & Efek</label>
                                    <div id="enhanceFeaturesContainer" class="enhance-features">
                                        <p class="text-xs text-gray-500">Memuat daftar fitur...</p>
                                    </div>
                                </div>

                                {{-- Action Button --}}
                                <button type="button" id="enhanceRunTrigger" class="enhance-run-button" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="3"></circle>
                                        <path d="M12 1v6m0 6v6"></path>
                                    </svg>
                                    Jalankan Ambu Magic
                                </button>
                                <div id="enhanceStatus" class="enhance-status"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // Upload Modal Management
        function openUploadModal(tab = 'upload') {
            document.getElementById('uploadModal').classList.add('active');
            document.body.style.overflow = 'hidden';
            switchUploadTab(tab);
        }

        function closeUploadModal() {
            document.getElementById('uploadModal').classList.remove('active');
            document.body.style.overflow = '';
            // Reset form
            document.getElementById('uploadModalForm').reset();
            document.getElementById('uploadPreviewContainer').classList.add('hidden');
            document.getElementById('uploadSubmitBtn').disabled = true;
            document.getElementById('uploadFileCount').textContent = '0';
        }

        function switchUploadTab(tabName) {
            // Update tab buttons
            document.querySelectorAll('.upload-modal__tab').forEach(tab => {
                if (tab.dataset.tab === tabName) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });

            // Update tab contents
            document.querySelectorAll('.upload-modal__tab-content').forEach(content => {
                content.classList.remove('active');
            });

            if (tabName === 'upload') {
                document.getElementById('uploadTabContent').classList.add('active');
            } else if (tabName === 'enhance') {
                document.getElementById('enhanceTabContent').classList.add('active');
            }
        }

        // Drag & Drop Handling
        const dropZone = document.getElementById('uploadDropZone');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.remove('dragover');
            }, false);
        });

        dropZone.addEventListener('drop', handleUploadDrop, false);

        function handleUploadDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            document.getElementById('uploadModalInput').files = files;
            handleUploadFileSelect({ target: { files } });
        }

        // Store selected files globally for enhance feature
        let selectedFiles = [];

        // File Selection Handling
        function handleUploadFileSelect(event) {
            selectedFiles = Array.from(event.target.files);
            const previewGrid = document.getElementById('uploadPreviewGrid');
            const previewContainer = document.getElementById('uploadPreviewContainer');
            const submitBtn = document.getElementById('uploadSubmitBtn');
            const fileCount = document.getElementById('uploadFileCount');

            if (selectedFiles.length === 0) {
                previewContainer.classList.add('hidden');
                submitBtn.disabled = true;
                fileCount.textContent = '0';
                return;
            }

            previewGrid.innerHTML = '';
            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'upload-preview__item';
                    previewItem.innerHTML = `
                        <div class="upload-preview__item-image">
                            <img src="${e.target.result}" alt="${file.name}" data-file-index="${index}">
                            <button type="button" class="upload-preview__item-remove" onclick="removeUploadFile(${index})" title="Hapus file">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                        <div class="upload-preview__item-info">
                            <p class="upload-preview__item-name" title="${file.name}">${file.name}</p>
                            <p class="upload-preview__item-size">${formatBytes(file.size)}</p>
                        </div>
                        <div class="upload-preview__item-actions">
                            <button type="button" class="btn-enhance-small" onclick="enhanceSelectedFile(${index})">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <path d="M12 1v6m0 6v6"></path>
                                </svg>
                                Kreasikan
                            </button>
                        </div>
                    `;
                    previewGrid.appendChild(previewItem);
                };
                reader.readAsDataURL(file);
            });

            previewContainer.classList.remove('hidden');
            submitBtn.disabled = false;
            fileCount.textContent = selectedFiles.length;
        }

        function removeUploadFile(index) {
            // Remove file from array
            selectedFiles.splice(index, 1);

            // Update file input
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            document.getElementById('uploadModalInput').files = dataTransfer.files;

            // Re-render preview
            handleUploadFileSelect({ target: { files: selectedFiles } });
        }

        async function enhanceSelectedFile(index) {
            const file = selectedFiles[index];
            if (!file) return;

            // Switch to enhance tab first
            switchUploadTab('enhance');

            // Show loading in preview
            const previewContainer = document.getElementById('enhancePreviewContainer');
            previewContainer.innerHTML = `
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 300px; gap: 16px;">
                    <div style="width: 48px; height: 48px; border: 4px solid #E5E7EB; border-top-color: #F17B0D; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                    <p style="color: #6B7280; font-size: 14px;">Mengunggah file ke server...</p>
                </div>
            `;
            document.getElementById('enhanceRunTrigger').disabled = true;
            document.getElementById('enhanceStatus').textContent = 'Mengunggah file...';

            try {
                // Upload file via AJAX
                const formData = new FormData();
                formData.append('file', file);
                formData.append('type', document.getElementById('uploadTypeSelect').value || 'product_photo');

                const response = await fetch('{{ route('media.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.message || 'Upload gagal');
                }

                const data = await response.json();
                const media = data.media;

                // Show success and image
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContainer.innerHTML = `<img src="${media.url}" alt="${file.name}" style="max-width: 100%; max-height: 500px; object-fit: contain; border-radius: 8px;">`;
                };
                reader.readAsDataURL(file);

                // Set media_id for enhance
                document.getElementById('enhanceMediaId').value = media.id;
                document.getElementById('enhanceMediaId').dataset.fileIndex = index;
                document.getElementById('enhanceMediaId').dataset.mediaUrl = media.url;

                // Enable enhance button
                document.getElementById('enhanceRunTrigger').disabled = false;
                document.getElementById('enhanceStatus').textContent = '✓ File berhasil diunggah! Pilih preset dan jalankan Ambu Magic.';
                document.getElementById('enhanceStatus').classList.add('text-green-600');

                // Update selectedFiles to mark as uploaded
                selectedFiles[index].uploadedMediaId = media.id;

            } catch (error) {
                previewContainer.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 300px; gap: 16px; color: #DC2626;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <p style="font-size: 14px; font-weight: 600;">Upload Gagal</p>
                        <p style="font-size: 13px; color: #6B7280;">${error.message}</p>
                        <button type="button" onclick="enhanceSelectedFile(${index})" style="padding: 8px 16px; background: #F17B0D; color: white; border: none; border-radius: 6px; cursor: pointer;">Coba Lagi</button>
                    </div>
                `;
                document.getElementById('enhanceStatus').textContent = '✗ ' + error.message;
                document.getElementById('enhanceStatus').classList.add('text-red-600');
            }
        }

        // Add CSS animation for spinner
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);

        // Trigger enhance media selection (will scroll to gallery)
        function triggerEnhanceMediaSelect() {
            closeUploadModal();
            // Scroll to media grid
            document.querySelector('.grid.grid-cols-2').scrollIntoView({ behavior: 'smooth', block: 'start' });
            // Show instruction
            alert('Pilih foto dari galeri di bawah dengan klik tombol "Ambu Magic"');
        }

        // Override Ambu Magic buttons to open modal with enhance tab
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.ai-select-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const mediaId = this.dataset.mediaId;
                    const filename = this.dataset.mediaFilename;

                    // Open modal in enhance tab
                    openUploadModal('enhance');

                    // Load media preview
                    document.getElementById('enhanceMediaId').value = mediaId;

                    // Get media URL and display preview
                    const mediaCard = this.closest('.border');
                    if (mediaCard) {
                        const img = mediaCard.querySelector('img');
                        if (img) {
                            const previewContainer = document.getElementById('enhancePreviewContainer');
                            previewContainer.innerHTML = `<img src="${img.src}" alt="${filename}" style="max-width: 100%; max-height: 500px; object-fit: contain; border-radius: 8px;">`;

                            // Enable enhance button
                            document.getElementById('enhanceRunTrigger').disabled = false;
                        }
                    }
                });
            });

            // Initialize AI Studio for modal enhance tab if AI is enabled
            @if($aiEnabled)
            const enhanceConfig = {
                routes: {
                    features: '{{ route('ai.features') }}',
                    enhance: '{{ route('ai.enhance') }}',
                    job: '{{ url('ai/jobs') }}',
                },
                csrfToken: '{{ csrf_token() }}',
                maxPollAttempts: 40,
                pollIntervalMs: 2000,
                // Override element IDs for modal
                elementIds: {
                    mediaIdInput: 'enhanceMediaId',
                    backgroundColor: 'enhanceBackgroundColor',
                    useSolid: 'enhanceUseSolid',
                    featuresContainer: 'enhanceFeaturesContainer',
                    quickPresetsContainer: 'enhanceQuickPresets',
                    enhanceTrigger: 'enhanceRunTrigger',
                    status: 'enhanceStatus',
                }
            };

            // Wait for AI Studio module to load
            let enhanceRetries = 0;
            const maxEnhanceRetries = 5;

            function tryInitEnhanceStudio() {
                if (typeof window.AiStudioManager !== 'undefined') {
                    window.enhanceStudioInstance = new window.AiStudioManager(enhanceConfig);
                } else if (enhanceRetries < maxEnhanceRetries) {
                    enhanceRetries++;
                    setTimeout(tryInitEnhanceStudio, 200);
                }
            }

            tryInitEnhanceStudio();
            @endif
        });
    </script>
</x-app-layout>
