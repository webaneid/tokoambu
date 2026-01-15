<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Buat Periode Preorder Baru
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $product->name }}</p>
            </div>
            <a href="{{ route('preorders.show', $product) }}" class="text-sm text-gray-600 hover:text-gray-900">
                ← Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('preorders.periods.store', $product) }}" method="POST" class="space-y-6">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Periode *</label>
                            <input
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Contoh: Periode 1, Batch Januari 2026"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea
                                name="description"
                                rows="3"
                                placeholder="Deskripsi opsional untuk periode ini"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai *</label>
                                <input
                                    type="date"
                                    name="start_date"
                                    value="{{ old('start_date') }}"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                @error('start_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Berakhir *</label>
                                <input
                                    type="date"
                                    name="end_date"
                                    value="{{ old('end_date') }}"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                @error('end_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Target Kuantitas (Opsional)</label>
                            <input
                                type="number"
                                name="target_quantity"
                                value="{{ old('target_quantity') }}"
                                min="1"
                                placeholder="Contoh: 100"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            <p class="text-xs text-gray-500 mt-1">Target jumlah pesanan untuk periode ini (opsional)</p>
                            @error('target_quantity')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button
                                type="submit"
                                class="flex-1 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-hover transition">
                                Buat Periode
                            </button>
                            <a
                                href="{{ route('preorders.show', $product) }}"
                                class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 text-center transition">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-semibold text-gray-900 mb-2">Catatan Penting:</h3>
                <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside">
                    <li>Periode bisa berjalan secara paralel (multiple periode aktif bersamaan)</li>
                    <li>Setelah tanggal berakhir, periode otomatis tidak bisa menerima order baru</li>
                    <li>Anda bisa menutup periode secara manual kapan saja</li>
                    <li>Periode yang sudah selesai bisa diarsipkan untuk merapikan dashboard</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
