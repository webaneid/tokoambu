<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900">Tambah Kategori Produk</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('product-categories.store') }}" class="space-y-6">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kategori</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                            <textarea name="description" rows="3" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">{{ old('description') }}</textarea>
                            @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Custom Fields -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Custom Fields</label>
                                    <p class="text-xs text-gray-500 mt-1">Tambahkan field khusus untuk informasi detail produk (opsional)</p>
                                </div>
                                <button type="button" id="btnAddCustomField" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover text-sm font-medium">
                                    + Tambah Field
                                </button>
                            </div>
                            <div id="customFieldsContainer"></div>
                            <input type="hidden" name="custom_fields" id="customFieldsData" value="{{ old('custom_fields', '[]') }}">
                            @error('custom_fields') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-hover">Simpan</button>
                            <a href="{{ route('product-categories.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/custom-fields-repeater.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const customFieldsData = document.getElementById('customFieldsData');
            const oldCustomFields = {!! old('custom_fields', '[]') !!};

            const repeater = new CustomFieldsRepeater({
                containerId: 'customFieldsContainer',
                initialFields: oldCustomFields,
                onChange: (fields) => {
                    customFieldsData.value = JSON.stringify(fields);
                }
            });
        });
    </script>
</x-app-layout>
