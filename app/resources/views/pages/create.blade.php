<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900">Buat Halaman Baru</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('pages.store') }}" class="space-y-6">
                        @csrf

                        {{-- Title --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Judul <span class="text-red-500">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" class="w-full rounded-lg border-gray-300 px-3 py-2 focus:border-primary focus:ring-primary" required>
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Slug (Optional) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Slug (opsional)</label>
                            <input type="text" name="slug" value="{{ old('slug') }}" class="w-full rounded-lg border-gray-300 px-3 py-2 focus:border-primary focus:ring-primary" placeholder="otomatis dibuat dari judul">
                            <p class="mt-1 text-xs text-gray-500">Biarkan kosong untuk generate otomatis dari judul</p>
                            @error('slug')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Featured Image --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Foto Featured</label>
                            <div class="flex flex-col items-start gap-4">
                                <div id="featuredImagePreview" class="w-full max-w-md h-48 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 overflow-hidden">
                                    <span class="text-sm text-gray-400 text-center px-4">Belum ada foto</span>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" id="btnSelectFeaturedImage" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover text-sm font-medium">
                                        Pilih Foto
                                    </button>
                                    <button type="button" id="btnRemoveFeaturedImage" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 text-sm font-medium hidden">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="featured_image_id" id="featuredImageId" value="{{ old('featured_image_id') }}">
                            @error('featured_image_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Content (Quill Editor) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Konten</label>
                            <div id="quillEditor" style="height: 400px;"></div>
                            <input type="hidden" name="content" id="contentInput" value="{{ old('content') }}">
                            @error('content')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Published Status --}}
                        <div>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_published" value="1" {{ old('is_published', true) ? 'checked' : '' }} class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                <span class="ml-2 text-sm text-gray-700">Publish halaman ini</span>
                            </label>
                        </div>

                        {{-- Actions --}}
                        <div class="flex gap-3 border-t pt-6">
                            <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-hover">
                                Simpan Halaman
                            </button>
                            <a href="{{ route('pages.index') }}" class="rounded-lg bg-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-400">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    @endpush

    @push('scripts')
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script src="{{ asset('js/media-picker.js') }}"></script>
    <script>
        // Initialize Quill Editor
        const quill = new Quill('#quillEditor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });

        // Sync Quill content with hidden input
        const contentInput = document.getElementById('contentInput');
        quill.on('text-change', function() {
            contentInput.value = quill.root.innerHTML;
        });

        // Set initial content if editing
        @if(old('content'))
            quill.root.innerHTML = {!! json_encode(old('content')) !!};
        @endif

        // Featured Image Picker
        const featuredImagePreview = document.getElementById('featuredImagePreview');
        const featuredImageId = document.getElementById('featuredImageId');
        const btnSelectFeaturedImage = document.getElementById('btnSelectFeaturedImage');
        const btnRemoveFeaturedImage = document.getElementById('btnRemoveFeaturedImage');

        btnSelectFeaturedImage.addEventListener('click', () => {
            openMediaPicker({
                type: 'banner_image',
                title: 'Pilih Foto Featured',
                listUrl: '{{ route('media.banner_image.list') }}',
                uploadUrl: '{{ route('media.store') }}',
                context: {},
                onSelect: (media) => {
                    featuredImageId.value = media.id;
                    featuredImagePreview.innerHTML = `<img src="${media.url}" alt="Featured Image" class="w-full h-full object-cover">`;
                    btnRemoveFeaturedImage.classList.remove('hidden');
                },
                aiEnabled: false,
                csrfToken: '{{ csrf_token() }}'
            });
        });

        btnRemoveFeaturedImage.addEventListener('click', () => {
            featuredImageId.value = '';
            featuredImagePreview.innerHTML = '<span class="text-sm text-gray-400 text-center px-4">Belum ada foto</span>';
            btnRemoveFeaturedImage.classList.add('hidden');
        });

        // Show remove button if there's already an image
        @if(old('featured_image_id'))
            btnRemoveFeaturedImage.classList.remove('hidden');
        @endif
    </script>
    @endpush
</x-app-layout>
