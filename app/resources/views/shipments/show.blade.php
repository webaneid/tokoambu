<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-900">Detail Pengiriman {{ $shipment->shipment_number }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pengiriman</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-500">No. Pengiriman</p>
                                <p class="text-lg font-medium text-gray-900">{{ $shipment->shipment_number ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">No. Order</p>
                                <p class="text-gray-900">{{ $shipment->order->order_number }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Kurir</p>
                                <p class="text-gray-900">{{ $couriers[$shipment->courier] ?? $shipment->courier ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">No. Resi</p>
                                <p class="text-gray-900">{{ $shipment->tracking_number ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Foto Resi</p>
                                @if($shipment->trackingMedia)
                                    <a href="{{ $shipment->trackingMedia->url }}" target="_blank" class="text-blue-600 text-sm">Lihat Resi</a>
                                @else
                                    <p class="text-gray-900">-</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ongkos Kirim</p>
                                <p class="text-lg font-bold text-primary">Rp {{ number_format($shipment->shipping_cost ?? 0, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Status & Biaya</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-500">Status</p>
                                <span class="px-3 py-1 rounded-full text-sm font-medium 
                                    {{ isset($shipment->status) && $shipment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ isset($shipment->status) && $shipment->status === 'packed' ? 'bg-blue-50 text-blue' : '' }}
                                    {{ isset($shipment->status) && $shipment->status === 'shipped' ? 'bg-blue-100 text-blue' : '' }}
                                    {{ isset($shipment->status) && $shipment->status === 'delivered' ? 'bg-green-100 text-green-800' : '' }}
                                ">
                                    {{ isset($shipment->status) ? ucfirst($shipment->status) : 'Pending' }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Tanggal Pengiriman</p>
                                <p class="text-gray-900">{{ $shipment->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Detail Order</h3>
                    <div class="space-y-2">
                        <p class="text-gray-900">Customer: <strong>{{ $shipment->order->customer->name }}</strong></p>
                        <p class="text-gray-900">Alamat: <strong>{{ $shipment->order->customer->address }}</strong></p>
                        <p class="text-gray-900">No. HP: <strong>{{ $shipment->order->customer->phone }}</strong></p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($shipment->order->items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div>{{ $item->product?->name ?? '-' }}</div>
                                    @if($item->productVariant)
                                        <div class="text-xs text-gray-500 mt-1">
                                            Variasi: {{ implode(' / ', array_values($item->productVariant->variant_attributes ?? [])) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $item->productVariant?->sku ?? $item->product?->sku ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($shipment->notes)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Catatan</h3>
                        <p class="text-gray-700">{{ $shipment->notes }}</p>
                    </div>
                </div>
            @endif

            <x-shipment-form :shipment="$shipment" />

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Lacak Pengiriman</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kurir</label>
                            <input type="text" id="track_courier" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50" readonly value="{{ $shipment->courier ?? '' }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">No. Resi</label>
                            <input type="text" id="track_waybill" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg" value="{{ $shipment->tracking_number ?? '' }}">
                        </div>
                        <button type="button" id="btn_track" class="w-full h-10 px-4 py-2 bg-blue text-white rounded-lg hover:bg-blue-light">
                            Lacak
                        </button>
                    </div>
                    <div id="track_message" class="text-xs text-gray-500 mt-2"></div>
                    @php
                        $trackingPayload = $shipment->tracking_payload ?? null;
                        $trackingData = $trackingPayload['data'] ?? $trackingPayload ?? null;
                        $trackingDelivery = $trackingData['delivery_status'] ?? [];
                        $trackingSummary = $trackingData['summary'] ?? [];
                        $trackingManifest = $trackingData['manifest'] ?? [];
                    @endphp
                    <div id="track_result" class="mt-4 text-sm text-gray-700">
                        @if($trackingPayload)
                            <div class="flex flex-wrap items-center gap-3 text-sm text-gray-700">
                                <div class="font-semibold text-gray-900">
                                    {{ $trackingSummary['courier_name'] ?? ($shipment->courier ?? '-') }}
                                </div>
                                @if(!empty($trackingSummary['status']) || !empty($trackingDelivery['status']))
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                        {{ $trackingDelivery['status'] ?? $trackingSummary['status'] }}
                                    </span>
                                @endif
                                @if(!empty($trackingDelivery['pod_receiver']))
                                    <span class="text-gray-600">Penerima: {{ $trackingDelivery['pod_receiver'] }}</span>
                                @endif
                                @if(!empty($trackingDelivery['pod_date']))
                                    <span class="text-gray-600">Diterima: {{ $trackingDelivery['pod_date'] }} {{ $trackingDelivery['pod_time'] ?? '' }}</span>
                                @endif
                            </div>
                            @if(!empty($trackingManifest))
                                <div class="mt-4 space-y-2">
                                    @foreach($trackingManifest as $manifest)
                                        <div class="p-3 border border-gray-200 rounded-lg text-sm">
                                            <div class="text-xs text-gray-500">
                                                {{ $manifest['manifest_date'] ?? '' }} {{ $manifest['manifest_time'] ?? '' }}
                                                @if(!empty($manifest['city_name']))
                                                    · {{ $manifest['city_name'] }}
                                                @endif
                                            </div>
                                            <div class="text-gray-800">{{ $manifest['manifest_description'] ?? '-' }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-3 text-sm text-gray-500">Belum ada histori pelacakan.</div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 flex gap-3">
                    <a href="{{ route('shipments.index') }}" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 text-center">
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const trackButton = document.getElementById('btn_track');
        const trackMessage = document.getElementById('track_message');
        const trackResult = document.getElementById('track_result');

        function renderTracking(data) {
            if (!data) {
                trackResult.textContent = '';
                return;
            }
            const summary = data.summary || data.data?.summary || {};
            const delivery = data.delivery_status || data.data?.delivery_status || {};
            const manifest = data.manifest || data.data?.manifest || [];
            let html = '';
            if (summary.waybill_number || summary.courier_name) {
                html += `<div class="mb-3"><strong>${summary.courier_name || ''}</strong> ${summary.waybill_number ? `- ${summary.waybill_number}` : ''}</div>`;
            }
            if (delivery.status) {
                html += `<div class="mb-3">Status: <strong>${delivery.status}</strong> ${delivery.pod_receiver ? `| Penerima: ${delivery.pod_receiver}` : ''}</div>`;
            }
            if (Array.isArray(manifest) && manifest.length) {
                html += '<div class="space-y-2">';
                manifest.forEach(item => {
                    const date = item.manifest_date ? `${item.manifest_date} ${item.manifest_time || ''}` : '';
                    html += `<div class="p-2 border rounded-lg"><div class="text-xs text-gray-500">${date}</div><div>${item.manifest_description || ''}</div></div>`;
                });
                html += '</div>';
            }
            trackResult.innerHTML = html || '<div class="text-xs text-gray-500">Data pelacakan kosong.</div>';
        }

        trackButton?.addEventListener('click', async () => {
            const courier = (document.getElementById('track_courier')?.value || '').trim().toLowerCase();
            const waybill = (document.getElementById('track_waybill')?.value || '').trim();
            if (!courier || !waybill) {
                trackMessage.textContent = 'Kurir dan resi wajib diisi.';
                return;
            }
            trackMessage.textContent = 'Mengambil data pelacakan...';
            trackResult.textContent = '';
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('{{ route('shipments.track', $shipment) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                    },
                    body: JSON.stringify({ courier, awb: waybill }),
                });
                const payload = await res.json().catch(() => null);
                if (!res.ok) {
                    const errors = payload?.errors;
                    if (errors?.courier || errors?.awb || errors?.waybill || errors?.tracking_number) {
                        const msgs = []
                            .concat(errors.courier || [])
                            .concat(errors.awb || [])
                            .concat(errors.waybill || [])
                            .concat(errors.tracking_number || []);
                        trackMessage.textContent = msgs.join(' ') || 'Kurir dan resi wajib diisi.';
                    } else {
                        trackMessage.textContent = payload?.message || 'Gagal mengambil data pelacakan.';
                    }
                    return;
                }
                trackMessage.textContent = 'Data pelacakan ditemukan.';
                renderTracking(payload);
            } catch (err) {
                trackMessage.textContent = 'Gagal menghubungi layanan pelacakan.';
            }
        });
    </script>

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
