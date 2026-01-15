@props(['shipment', 'embedded' => false])

@unless($embedded)
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
@endunless
    <div class="p-6 text-gray-900">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Catat Pengiriman</h3>
        <form action="{{ route('shipments.updateStatus', $shipment) }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">No. Resi</label>
                    <input type="text" name="tracking_number" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg" value="{{ old('tracking_number', $shipment->tracking_number) }}">
                    @error('tracking_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ongkos Kirim</label>
                    <input type="number" name="shipping_cost" min="0" step="0.01" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg" value="{{ old('shipping_cost', $shipment->shipping_cost) }}">
                    @error('shipping_cost') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @if($embedded)
                    <input type="hidden" name="status" value="shipped">
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <div class="h-10 px-4 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 text-gray-700 flex items-center">
                            Shipped
                        </div>
                    </div>
                @else
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            <option value="pending" @selected(old('status', $shipment->status) === 'pending')>Pending</option>
                            <option value="packed" @selected(old('status', $shipment->status) === 'packed')>Packed</option>
                            <option value="shipped" @selected(old('status', $shipment->status) === 'shipped')>Shipped</option>
                            <option value="delivered" @selected(old('status', $shipment->status) === 'delivered')>Delivered</option>
                        </select>
                        @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>
            <input type="hidden" name="tracking_media_id" id="tracking_media_id" value="{{ old('tracking_media_id', $shipment->tracking_media_id) }}">
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" id="openTrackingPicker" class="h-10 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    Pilih / Upload Resi
                </button>
                <span id="trackingLabel" class="text-sm text-gray-600">
                    {{ $shipment->trackingMedia?->filename ?? 'Belum ada resi dipilih' }}
                </span>
                @if($shipment->trackingMedia)
                    <a href="{{ $shipment->trackingMedia->url }}" target="_blank" class="text-blue-600 text-sm">Lihat Resi</a>
                @endif
                <button type="submit" class="h-10 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover ml-auto">
                    Simpan
                </button>
            </div>
        </form>
        <p class="text-xs text-gray-500 mt-2">Status Shipped akan otomatis mencatat ongkos kirim ke laporan pengeluaran.</p>
    </div>
@unless($embedded)
</div>
@endunless
