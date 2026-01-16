<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-900">Order {{ $order->order_number }}</h2>
            @if (!in_array($order->status, ['shipped', 'done'], true))
                <a href="{{ route('orders.edit', $order) }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-hover">
                    Edit
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Order Header -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Order</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-500">No. Order</p>
                                <p class="text-lg font-medium text-gray-900">{{ $order->order_number }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Tipe</p>
                                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $order->type === 'order' ? 'bg-blue-100 text-blue' : 'bg-pink-100 text-pink' }}">
                                    {{ ucfirst($order->type) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Status</p>
                                <span class="px-3 py-1 rounded-full text-sm font-medium 
                                    {{ $order->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                                    {{ $order->status === 'waiting_payment' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $order->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $order->status === 'shipped' ? 'bg-blue-100 text-blue' : '' }}
                                    {{ $order->status === 'done' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                ">
                                    {{ str_replace('_', ' ', ucfirst($order->status)) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Tanggal</p>
                                <p class="text-gray-900">{{ $order->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-500">Nama</p>
                                <p class="text-lg font-medium text-gray-900">{{ $order->customer->name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p class="text-gray-900">{{ $order->customer->email ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">No. HP</p>
                                <p class="text-gray-900">{{ $order->customer->phone ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Alamat</p>
                                <p class="text-gray-900">{{ $order->customer->address ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Item Order</h3>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div>{{ $item->product->name }}</div>
                                    @if($item->productVariant)
                                        <div class="text-xs text-gray-500 mt-1">
                                            Variasi: {{ implode(' / ', array_values($item->productVariant->variant_attributes ?? [])) }}
                                        </div>
                                    @endif
                                    @if($item->is_preorder)
                                        <div class="text-xs text-yellow-700 mt-1">
                                            Preorder
                                            @if($item->preorder_eta_date)
                                                · ETA {{ $item->preorder_eta_date->format('d M Y') }}
                                            @endif
                                            @if($item->preorder_allocated_qty !== null)
                                                · Alokasi {{ number_format($item->preorder_allocated_qty, 0) }}/{{ number_format($item->quantity, 0) }}
                                            @endif
                                            @if($item->preorder_ready_at)
                                                · Siap dikirim ({{ $item->preorder_ready_at->format('d M Y H:i') }})
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    @if($item->price !== null && $item->price > $item->unit_price)
                                        <div class="text-xs text-gray-400 line-through decoration-2 decoration-red-400">
                                            Rp {{ number_format($item->price, 0, ',', '.') }}
                                        </div>
                                    @endif
                                    <div>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ongkos Kirim</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kurir</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Layanan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estimasi</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Biaya</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $order->shipping_courier ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $order->shipping_service ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $order->shipping_etd ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 text-right">
                                    Rp {{ number_format($order->shipping_cost ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment History -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Histori Pembayaran</h3>
                
                @if($order->payments->count() > 0)
                    <div class="overflow-x-auto mb-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Metode</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($order->payments as $payment)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $payment->paid_at?->format('d M Y H:i') ?? $payment->created_at->format('d M Y H:i') }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ ucfirst($payment->method) }}</td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 text-right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 text-sm">
                                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $payment->status === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ $payment->status === 'verified' ? 'Terverifikasi' : 'Pending' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Payment Summary -->
                    <div class="grid grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg">
                        <div>
                            <p class="text-sm text-gray-500">Total Order</p>
                            <p class="text-lg font-bold text-gray-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Sudah Dibayar</p>
                            <p class="text-lg font-bold text-green-600">Rp {{ number_format($order->payments()->where('status', 'verified')->sum('amount'), 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Sisa Pembayaran</p>
                            <p class="text-lg font-bold {{ $order->total_amount - $order->payments()->where('status', 'verified')->sum('amount') <= 0 ? 'text-green-600' : 'text-red-600' }}">
                                Rp {{ number_format(max(0, $order->total_amount - $order->payments()->where('status', 'verified')->sum('amount')), 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500">Belum ada pembayaran</p>
                    </div>
                @endif

                @if($order->refunded_at)
                    <div class="mt-4 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                        <h4 class="font-semibold text-orange-900 mb-2">Informasi Refund</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-600">Jumlah Refund</p>
                                <p class="font-semibold text-orange-900">Rp {{ number_format($order->refund_amount, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Metode Refund</p>
                                <p class="font-semibold text-orange-900">{{ ucfirst($order->refund_method) }}</p>
                            </div>
                            @if($order->refundFromAccount)
                                <div class="col-span-2">
                                    <p class="text-gray-600">Dari Rekening</p>
                                    <p class="font-semibold text-orange-900">
                                        {{ $order->refundFromAccount->bank_name }} - {{ $order->refundFromAccount->account_number }}
                                        <span class="text-xs text-gray-700">({{ $order->refundFromAccount->account_name }})</span>
                                    </p>
                                </div>
                            @endif
                            <div class="col-span-2">
                                <p class="text-gray-600">Tanggal Refund</p>
                                <p class="font-semibold text-orange-900">{{ $order->refunded_at->format('d M Y H:i') }}</p>
                            </div>
                            @if($order->refund_notes)
                                <div class="col-span-2">
                                    <p class="text-gray-600">Catatan</p>
                                    <p class="text-gray-900">{{ $order->refund_notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Catatan</h3>
                    <p class="text-gray-700">{{ $order->notes ?? '-' }}</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Total</h3>
                    @php
                        $itemsSubtotal = $order->items->sum(function ($item) {
                            return $item->subtotal ?? ($item->quantity * $item->unit_price);
                        });
                        $shippingCost = $order->shipping_cost ?? 0;
                        $couponDiscount = $order->coupon_discount_amount ?? 0;
                    @endphp
                    <div class="space-y-1 text-sm text-gray-600">
                        <div>Subtotal Item: <span class="text-gray-900">Rp {{ number_format($itemsSubtotal, 0, ',', '.') }}</span></div>
                        <div>Ongkos Kirim: <span class="text-gray-900">Rp {{ number_format($shippingCost, 0, ',', '.') }}</span></div>
                        @if($couponDiscount > 0)
                            <div>Diskon Kupon{{ $order->coupon_code ? " ({$order->coupon_code})" : '' }}: <span class="text-red-600">-Rp {{ number_format($couponDiscount, 0, ',', '.') }}</span></div>
                        @endif
                    </div>
                    <p class="text-3xl font-bold text-primary mt-3">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 mt-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Pengambilan Stok</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Kurangi stok per produk sebelum pesanan dikemas. Semua item harus terambil untuk bisa menandai pesanan sebagai dikemas.
                </p>
                @if($order->status !== 'paid')
                    <div class="mb-4 text-sm text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-2">
                        Pengambilan stok hanya bisa dilakukan saat status order sudah Paid.
                    </div>
                @endif
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Order</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sudah Diambil</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ambil Stok</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($order->items as $item)
                                @php
                                    $pickedQty = $pickedQtyByItem[$item->id] ?? 0;
                                    $remainingQty = max(0, $item->quantity - $pickedQty);
                                    $balances = $balancesByItem[$item->id] ?? collect();
                                    $latestMovement = $latestMovementByItem[$item->id] ?? null;
                                    $canPick = $order->status === 'paid' && $remainingQty > 0;
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div class="font-medium">{{ $item->product->name ?? '-' }}</div>
                                        @if($item->productVariant)
                                            <div class="text-xs text-gray-500 mt-1">
                                                Variasi: {{ implode(' / ', array_values($item->productVariant->variant_attributes ?? [])) }}
                                            </div>
                                        @endif
                                        @if($latestMovement)
                                            <div class="text-xs text-gray-500 mt-1">
                                                Terakhir diambil: {{ $latestMovement->movement_date?->format('d M Y H:i') ?? '-' }}
                                                · {{ $latestMovement->user?->name ?? '-' }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ number_format($pickedQty, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-sm font-medium {{ $remainingQty > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ number_format($remainingQty, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <select name="location_id" form="pick-form-{{ $item->id }}" class="w-full h-9 px-3 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" {{ $canPick ? '' : 'disabled' }}>
                                            <option value="">Pilih lokasi</option>
                                            @foreach($balances as $balance)
                                                @if(($balance->qty_on_hand ?? 0) > 0)
                                                    <option value="{{ $balance->location_id }}">
                                                        {{ ($balance->location->warehouse->code ?? '') . '-' . ($balance->location->display_code ?? $balance->location->code ?? '') }}
                                                        (Stok: {{ number_format($balance->qty_on_hand, 0, ',', '.') }})
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <form id="pick-form-{{ $item->id }}" action="{{ route('orders.items.pick', [$order, $item]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="h-9 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover {{ $canPick ? '' : 'opacity-50 cursor-not-allowed' }}" {{ $canPick ? '' : 'disabled' }}>
                                                Kurangi
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if($order->shipment && $order->shipment->tracking_number)
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Lacak Pengiriman</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kurir</label>
                            <input type="text" class="w-full h-10 px-4 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 text-gray-700" value="{{ $order->shipment->courier ?? '-' }}" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">No. Resi</label>
                            <input type="text" class="w-full h-10 px-4 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 text-gray-700" value="{{ $order->shipment->tracking_number }}" readonly>
                        </div>
                        <div class="md:justify-self-end">
                            <a href="{{ route('shipments.show', $order->shipment) }}" class="w-full md:w-auto inline-flex items-center justify-center h-10 px-6 bg-blue text-white rounded-lg hover:bg-blue-light">
                                Lacak
                            </a>
                        </div>
                    </div>
                    @php
                        $trackingPayload = $order->shipment->tracking_payload ?? null;
                        $trackingData = $trackingPayload['data'] ?? $trackingPayload ?? null;
                        $trackingDelivery = $trackingData['delivery_status'] ?? [];
                        $trackingSummary = $trackingData['summary'] ?? [];
                        $trackingManifest = $trackingData['manifest'] ?? [];
                    @endphp
                    @if($trackingPayload)
                        <div class="mt-6 border-t border-gray-200 pt-4">
                            <div class="flex flex-wrap items-center gap-3 text-sm text-gray-700">
                                <div class="font-semibold text-gray-900">
                                    {{ $trackingSummary['courier_name'] ?? ($order->shipment->courier ?? '-') }}
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
                        </div>
                    @endif
                </div>
            @endif

            <!-- Actions -->
            <div class="mt-6 flex gap-3">
                @if($order->status === 'paid')
                    <a href="{{ route('orders.print', $order) }}" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-hover text-center inline-flex items-center justify-center gap-2" target="_blank" rel="noopener">
                        <x-heroicon name="printer" class="w-5 h-5" />
                        Print Label
                    </a>
                    <button id="mark-packed-btn" class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-center inline-flex items-center justify-center gap-2 {{ $canPack ? '' : 'opacity-50 cursor-not-allowed' }}" {{ $canPack ? '' : 'disabled' }}>
                        <x-heroicon name="check-circle" class="w-5 h-5" />
                        Tandai Sudah Dikemas
                    </button>
                @elseif($order->status === 'waiting_payment' || $order->status === 'dp_paid')
                    <a href="{{ route('payments.create', ['order_id' => $order->id]) }}" class="flex-1 bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 text-center">
                        Catat Pembayaran
                    </a>
                @elseif($order->status === 'packed')
                    <button type="button" x-data class="flex-1 bg-blue text-white px-4 py-2 rounded-lg hover:bg-blue-light text-center {{ $order->shipment ? '' : 'opacity-50 cursor-not-allowed' }}" @click="$dispatch('open-modal', 'record-shipment')" {{ $order->shipment ? '' : 'disabled' }}>
                        Catat Pengiriman
                    </button>
                @endif

                @if($order->status !== 'cancelled' && $order->status !== 'cancelled_refund_pending' && !in_array($order->status, ['shipped', 'done']))
                    @if($order->paid_amount > 0)
                        {{-- Customer sudah bayar, perlu refund --}}
                        <form action="{{ route('orders.cancel-and-refund', $order) }}" method="POST" class="flex-1" onsubmit="return confirm('Batalkan order dan tandai perlu refund? Admin akan proses refund di menu Refund.')">
                            @csrf
                            <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-center">
                                Batalkan Order & Refund
                            </button>
                        </form>
                    @else
                        {{-- Customer belum bayar, cancel langsung --}}
                        <form action="{{ route('orders.cancel', $order) }}" method="POST" class="flex-1" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan order ini? Stock yang di-reserve akan dikembalikan.')">
                            @csrf
                            <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-center">
                                Batalkan Order
                            </button>
                        </form>
                    @endif
                @endif

                @if($order->status === 'cancelled_refund_pending')
                    <a href="{{ route('refunds.create', ['order_id' => $order->id]) }}" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-hover text-center">
                        Proses Refund
                    </a>
                @endif

                <a href="{{ route('orders.index') }}" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 text-center">
                    Kembali
                </a>
            </div>

            @if($order->status === 'packed' && $order->shipment)
                <x-modal name="record-shipment" maxWidth="2xl">
                    <x-shipment-form :shipment="$order->shipment" embedded />
                </x-modal>
            @endif

            @if($order->status === 'paid')
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const markPackedBtn = document.getElementById('mark-packed-btn');

                        markPackedBtn.addEventListener('click', function() {
                            if (markPackedBtn.disabled) {
                                return;
                            }
                            if (!confirm('Tandai order ini sebagai sudah dikemas?')) {
                                return;
                            }

                            // Create form and submit
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '{{ route("orders.update", $order) }}';

                            // CSRF token
                            const csrfInput = document.createElement('input');
                            csrfInput.type = 'hidden';
                            csrfInput.name = '_token';
                            csrfInput.value = '{{ csrf_token() }}';
                            form.appendChild(csrfInput);

                            // Method spoofing for PUT
                            const methodInput = document.createElement('input');
                            methodInput.type = 'hidden';
                            methodInput.name = '_method';
                            methodInput.value = 'PUT';
                            form.appendChild(methodInput);

                            // Status
                            const statusInput = document.createElement('input');
                            statusInput.type = 'hidden';
                            statusInput.name = 'status';
                            statusInput.value = 'packed';
                            form.appendChild(statusInput);

                            // Notes (keep existing)
                            const notesInput = document.createElement('input');
                            notesInput.type = 'hidden';
                            notesInput.name = 'notes';
                            notesInput.value = '{{ $order->notes ?? "" }}';
                            form.appendChild(notesInput);

                            document.body.appendChild(form);
                            form.submit();
                        });
                    });
                </script>
            @endif

            @if($order->status === 'packed' && $order->shipment)
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
            @endif

            {{-- Modal removed: Cancel with Refund now uses simplified workflow via /refunds --}}

            {{-- JavaScript removed: Bank Account Management now handled in /refunds --}}
        </div>
    </div>
</x-app-layout>
