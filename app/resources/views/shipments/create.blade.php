<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-900">Catat Pengiriman</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <form action="{{ route('shipments.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <input type="hidden" name="order_id" value="{{ request()->get('order_id') }}">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kurir</label>
                        <select name="courier" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">Pilih kurir</option>
                            @foreach($couriers as $code => $name)
                                <option value="{{ $code }}" @selected(old('courier', $order->shipping_courier) === $code)>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('courier') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. Resi</label>
                        <input type="text" name="tracking_number" value="{{ old('tracking_number') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Masukkan nomor resi">
                        @error('tracking_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Foto Resi</label>
                        <input type="hidden" name="tracking_media_id" id="tracking_media_id">
                        <div class="flex items-center gap-3">
                            <button type="button" id="openTrackingPicker" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                                Pilih / Upload Resi
                            </button>
                            <span id="trackingLabel" class="text-sm text-gray-600">Belum ada resi dipilih</span>
                        </div>
                        @error('tracking_media_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Penerima</label>
                        <input type="text" name="recipient_name" value="{{ old('recipient_name', $order->customer->name) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        @error('recipient_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Penerima</label>
                        <textarea name="recipient_address" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="3">{{ old('recipient_address', $order->shipping_address ?: $order->customer->address) }}</textarea>
                        @error('recipient_address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ongkos Kirim</label>
                        <input type="number" name="shipping_cost" min="0" step="0.01" value="{{ old('shipping_cost', $order->shipping_cost) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        @error('shipping_cost') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                        <textarea name="notes" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="3">{{ old('notes') }}</textarea>
                    </div>

                    <div class="flex gap-3 pt-6">
                        <button type="submit" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-hover transition-colors">
                            Simpan Pengiriman
                        </button>
                        <a href="{{ route('shipments.index') }}" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 text-center">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/media-picker.js') }}"></script>
    <script>
        const trackingButton = document.getElementById('openTrackingPicker');
        const trackingInput = document.getElementById('tracking_media_id');
        const trackingLabel = document.getElementById('trackingLabel');

        trackingButton?.addEventListener('click', () => {
            openMediaPicker({
                type: 'shipment_proof',
                title: 'Pilih Foto Resi',
                listUrl: '{{ route('media.shipment_proof.list') }}',
                uploadUrl: '{{ route('media.store') }}',
                onSelect: (item) => {
                    trackingInput.value = item.id;
                    trackingLabel.textContent = item.filename;
                },
            });
        });
    </script>
</x-app-layout>
