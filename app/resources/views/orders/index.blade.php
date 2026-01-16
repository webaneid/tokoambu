@php
    $filters = [
        'search' => request('search'),
        'type' => request('type'),
        'status' => request('status'),
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-900">Daftar Order</h2>
            <a href="{{ route('orders.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-hover transition-colors">
                + Buat Order Baru
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="GET" action="{{ route('orders.index') }}">
                        <div class="flex flex-wrap items-end gap-4 md:flex-nowrap">
                            <div class="w-full md:flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Nama pembeli atau nomor order" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            </div>
                            <div class="w-full md:flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Order</label>
                                <select name="type" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                    <option value="">Semua</option>
                                    <option value="order" @selected($filters['type'] === 'order')>Order</option>
                                    <option value="preorder" @selected($filters['type'] === 'preorder')>Preorder</option>
                                </select>
                            </div>
                            <div class="w-full md:flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                    <option value="">Semua</option>
                                    <option value="draft" @selected($filters['status'] === 'draft')>Draft</option>
                                    <option value="waiting_payment" @selected($filters['status'] === 'waiting_payment')>Waiting Payment</option>
                                    <option value="dp_paid" @selected($filters['status'] === 'dp_paid')>DP Paid</option>
                                    <option value="paid" @selected($filters['status'] === 'paid')>Paid</option>
                                    <option value="packed" @selected($filters['status'] === 'packed')>Packed</option>
                                    <option value="shipped" @selected($filters['status'] === 'shipped')>Shipped</option>
                                    <option value="done" @selected($filters['status'] === 'done')>Done</option>
                                    <option value="cancelled" @selected($filters['status'] === 'cancelled')>Cancelled</option>
                                    <option value="cancelled_refund_pending" @selected($filters['status'] === 'cancelled_refund_pending')>Cancelled - Refund Pending</option>
                                    <option value="refunded" @selected($filters['status'] === 'refunded')>Refunded</option>
                                </select>
                            </div>
                            <div class="flex items-end gap-2 md:flex-none pt-6">
                                <button type="submit" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover transition text-sm">Filter</button>
                                <a href="{{ route('orders.index') }}" class="h-10 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-sm flex items-center">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if ($orders->count())
                    @php
                        $currentSort = request('sort', 'created_at');
                        $currentDirection = request('direction', 'desc');
                        $sortUrl = function (string $column) use ($currentSort, $currentDirection) {
                            $direction = $currentSort === $column && $currentDirection === 'asc' ? 'desc' : 'asc';
                            return request()->fullUrlWithQuery(['sort' => $column, 'direction' => $direction]);
                        };
                        $sortIndicator = function (string $column) use ($currentSort, $currentDirection) {
                            if ($currentSort !== $column) {
                                return '';
                            }
                            return $currentDirection === 'asc' ? '▲' : '▼';
                        };
                    @endphp
                    <div class="block sm:hidden divide-y divide-gray-200">
                        @forelse($orders as $order)
                            @php
                                $rawWhatsapp = $order->customer?->whatsapp_number ?? $order->customer?->phone ?? '';
                                $waNumber = preg_replace('/\D+/', '', $rawWhatsapp);
                                if ($waNumber !== '') {
                                    if (str_starts_with($waNumber, '0')) {
                                        $waNumber = '62' . substr($waNumber, 1);
                                    } elseif (!str_starts_with($waNumber, '62')) {
                                        $waNumber = '62' . $waNumber;
                                    }
                                }
                                $publicInvoiceUrl = \App\Http\Controllers\InvoiceController::generatePublicUrl($order);
                                $itemsText = $order->items
                                    ->map(function ($item) {
                                        $name = $item->product->name ?? 'Produk';
                                        return '- ' . $name . ' x' . $item->quantity;
                                    })
                                    ->implode("\n");
                                $shipment = $order->shipment;
                                $storeName = \App\Models\Setting::get('store_name', config('app.name', 'Toko Ambu'));
                                $storePhone = \App\Models\Setting::get('store_phone', '');
                                $storeWebsite = \App\Models\Setting::get('store_website', '');
                                $customerName = $order->customer->name ?? 'Pelanggan';
                                $defaultOrderMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan Anda nomor: {order_number} yaitu:\n{items}\ntelah kami buat/terima, total pembayaran: {total_amount}\nsilahkan melakukan pembayaran melalui:\n{invoice_url}\n\nTerimakasih\n{store_name}\n{store_phone}\n{store_website}";
                                $defaultDpMessage = "Yth. Ibu/Bapak {customer_name}\n\nPembayaran DP untuk pesanan {order_number} sebesar {dp_amount} sudah kami terima.\nSisa pembayaran: {remaining_amount}\nTotal tagihan: {total_amount}\n\nTerimakasih\n{store_name}\n{store_phone}\n{store_website}";
                                $defaultPaidMessage = "Yth. Ibu/Bapak {customer_name}\n\nPembayaran untuk pesanan {order_number} sudah kami terima dengan total {total_amount}.\n\nTerimakasih\n{store_name}\n{store_phone}\n{store_website}";
                                $defaultPackedMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} sudah kami kemas dan siap dikirim.\n\nTerimakasih\n{store_name}";
                                $defaultShippedMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} sudah kami kirim melalui {courier}.\nNo. Resi: {tracking_number}\n\nTerimakasih\n{store_name}";
                                $defaultDeliveredMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} telah sampai di tujuan.\nDiterima oleh: {received_by}\nTanggal & Jam: {delivered_at}\n\nTerimakasih telah berbelanja di {store_name}.\n\nSalam,\n{store_name}\n{store_phone}\n{store_website}";
                                $defaultCancelledMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} telah dibatalkan.\nJika ada pertanyaan, silakan hubungi kami.\n\nTerimakasih\n{store_name}\n{store_phone}";
                                $defaultCancelledRefundPendingMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} telah dibatalkan dan sedang diproses refund sebesar {refund_amount}.\nMohon tunggu konfirmasi lebih lanjut dari kami.\n\nTerimakasih\n{store_name}\n{store_phone}";
                                $defaultRefundedMessage = "Yth. Ibu/Bapak {customer_name}\n\nRefund untuk pesanan {order_number} sebesar {refund_amount} telah diproses.\nSilakan cek rekening Anda.\n\nTerimakasih\n{store_name}\n{store_phone}";
                                $templateKeyMap = [
                                    'draft' => ['wa_order_message', $defaultOrderMessage],
                                    'waiting_payment' => ['wa_order_message', $defaultOrderMessage],
                                    'dp_paid' => ['wa_dp_received_message', $defaultDpMessage],
                                    'paid' => ['wa_paid_message', $defaultPaidMessage],
                                    'packed' => ['wa_packed_message', $defaultPackedMessage],
                                    'shipped' => ['wa_shipped_message', $defaultShippedMessage],
                                    'done' => ['wa_delivered_message', $defaultDeliveredMessage],
                                    'cancelled' => ['wa_cancelled_message', $defaultCancelledMessage],
                                    'cancelled_refund_pending' => ['wa_cancelled_refund_pending_message', $defaultCancelledRefundPendingMessage],
                                    'refunded' => ['wa_refunded_message', $defaultRefundedMessage],
                                ];
                                $templateEntry = $templateKeyMap[$order->status] ?? null;
                                $templateKey = $templateEntry[0] ?? 'wa_order_message';
                                $templateDefault = $templateEntry[1] ?? $defaultOrderMessage;
                                $template = \App\Models\Setting::get($templateKey, $templateDefault);
                                $deliveredAt = $shipment?->delivered_at?->format('d M Y H:i') ?? '';
                                $replacements = [
                                    '{customer_name}' => $customerName,
                                    '{order_number}' => $order->order_number,
                                    '{items}' => $itemsText,
                                    '{total_amount}' => 'Rp ' . number_format($order->total_amount, 0, ',', '.'),
                                    '{invoice_url}' => $publicInvoiceUrl,
                                    '{store_name}' => $storeName,
                                    '{store_phone}' => $storePhone,
                                    '{store_website}' => $storeWebsite,
                                    '{dp_amount}' => 'Rp ' . number_format($order->paid_amount ?? 0, 0, ',', '.'),
                                    '{remaining_amount}' => 'Rp ' . number_format($order->remainingAmount(), 0, ',', '.'),
                                    '{courier}' => $order->shipping_courier ?? ($shipment?->courier ?? ''),
                                    '{tracking_number}' => $shipment?->tracking_number ?? '',
                                    '{received_by}' => $shipment?->received_by ?? '',
                                    '{delivered_at}' => $deliveredAt,
                                    '{refund_amount}' => 'Rp ' . number_format($order->refund_amount ?? $order->paid_amount ?? 0, 0, ',', '.'),
                                ];
                                $message = str_replace(array_keys($replacements), array_values($replacements), $template);
                                $waText = rawurlencode($message);
                            @endphp
                            <div class="p-4 space-y-2">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900">{{ $order->order_number }}</p>
                                    <p class="text-sm text-gray-500">{{ $order->customer->name ?? '-' }}</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
                                    <span>{{ $order->created_at?->format('d/m/Y') ?? '-' }}</span>
                                    <span class="text-gray-300">•</span>
                                    <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium {{ $order->type === 'order' ? 'bg-blue-100 text-blue' : 'bg-pink-100 text-pink' }}">
                                        {{ ucfirst($order->type) }}
                                    </span>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium
                                        {{ $order->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                                        {{ $order->status === 'waiting_payment' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $order->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $order->status === 'shipped' ? 'bg-blue-100 text-blue' : '' }}
                                        {{ $order->status === 'done' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $order->status === 'cancelled_refund_pending' ? 'bg-orange-100 text-orange-800' : '' }}
                                        {{ $order->status === 'refunded' ? 'bg-purple-100 text-purple-800' : '' }}
                                    ">
                                        @if($order->status === 'cancelled_refund_pending')
                                            Cancelled - Refund Pending
                                        @elseif($order->status === 'refunded')
                                            Refunded
                                        @else
                                            {{ str_replace('_', ' ', ucfirst($order->status)) }}
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 pt-2">
                                    <a href="{{ route('orders.show', $order) }}" class="text-blue hover:text-blue-light" title="Lihat">
                                        <x-heroicon name="eye" class="w-4 h-4" />
                                    </a>
                                    @if (!in_array($order->status, ['shipped', 'done', 'cancelled', 'cancelled_refund_pending', 'refunded'], true))
                                        <a href="{{ route('orders.edit', $order) }}" class="text-blue hover:text-blue-light" title="Edit">
                                            <x-heroicon name="pencil-square" class="w-4 h-4" />
                                        </a>
                                    @endif
                                    @if(!in_array($order->status, ['paid', 'done', 'cancelled', 'cancelled_refund_pending', 'refunded'], true) && $order->remainingAmount() > 0)
                                        <a href="{{ route('payments.create', ['order_id' => $order->id]) }}" class="text-green-600 hover:text-green-700">
                                            {{ $order->paid_amount > 0 || $order->status === 'dp_paid' ? 'Lunasi' : 'Bayar' }}
                                        </a>
                                    @endif
                                    @if ($waNumber !== '')
                                        <a href="https://api.whatsapp.com/send?phone={{ $waNumber }}&text={{ $waText }}" class="text-green-600 hover:text-green-700" title="Kirim Invoice" target="_blank" rel="noopener">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                <path d="M20.52 3.48A11.84 11.84 0 0012.04 0C5.46 0 .1 5.36.1 11.94c0 2.1.54 4.16 1.58 5.98L0 24l6.28-1.64a11.87 11.87 0 005.76 1.47h.01c6.58 0 11.94-5.36 11.94-11.94 0-3.19-1.24-6.18-3.47-8.41zm-8.48 18.3h-.01a9.9 9.9 0 01-5.05-1.38l-.36-.22-3.73.98 1-3.63-.24-.37a9.88 9.88 0 01-1.51-5.22c0-5.46 4.44-9.9 9.9-9.9 2.64 0 5.12 1.03 6.99 2.9a9.83 9.83 0 012.9 6.99c0 5.46-4.44 9.9-9.9 9.9zm5.44-7.48c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.8-1.49-1.78-1.66-2.08-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.61-.92-2.2-.24-.58-.48-.5-.67-.5h-.57c-.2 0-.52.07-.8.37-.27.3-1.05 1.02-1.05 2.5 0 1.48 1.08 2.9 1.23 3.1.15.2 2.13 3.26 5.15 4.57.72.31 1.28.5 1.72.64.72.23 1.37.2 1.88.12.57-.08 1.76-.72 2.01-1.41.25-.7.25-1.3.17-1.41-.07-.12-.27-.2-.57-.35z"/>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="hidden sm:block">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('order_number') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                        No. Order <span class="text-[8px] leading-none">{{ $sortIndicator('order_number') }}</span>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('customer') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                        Customer <span class="text-[8px] leading-none">{{ $sortIndicator('customer') }}</span>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('type') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                        Tipe <span class="text-[8px] leading-none">{{ $sortIndicator('type') }}</span>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('total_amount') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                        Total <span class="text-[8px] leading-none">{{ $sortIndicator('total_amount') }}</span>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('status') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                        Status <span class="text-[8px] leading-none">{{ $sortIndicator('status') }}</span>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($orders as $order)
                                @php
                                    $rawWhatsapp = $order->customer?->whatsapp_number ?? $order->customer?->phone ?? '';
                                    $waNumber = preg_replace('/\D+/', '', $rawWhatsapp);
                                    if ($waNumber !== '') {
                                        if (str_starts_with($waNumber, '0')) {
                                            $waNumber = '62' . substr($waNumber, 1);
                                        } elseif (!str_starts_with($waNumber, '62')) {
                                            $waNumber = '62' . $waNumber;
                                        }
                                    }
                                    $publicInvoiceUrl = \App\Http\Controllers\InvoiceController::generatePublicUrl($order);
                                    $itemsText = $order->items
                                        ->map(function ($item) {
                                            $name = $item->product->name ?? 'Produk';
                                            return '- ' . $name . ' x' . $item->quantity;
                                        })
                                        ->implode("\n");
                                    $shipment = $order->shipment;
                                    $storeName = \App\Models\Setting::get('store_name', config('app.name', 'Toko Ambu'));
                                    $storePhone = \App\Models\Setting::get('store_phone', '');
                                    $storeWebsite = \App\Models\Setting::get('store_website', '');
                                    $customerName = $order->customer->name ?? 'Pelanggan';
                                    $defaultOrderMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan Anda nomor: {order_number} yaitu:\n{items}\ntelah kami buat/terima, total pembayaran: {total_amount}\nsilahkan melakukan pembayaran melalui:\n{invoice_url}\n\nTerimakasih\n{store_name}\n{store_phone}\n{store_website}";
                                    $defaultDpMessage = "Yth. Ibu/Bapak {customer_name}\n\nPembayaran DP untuk pesanan {order_number} sebesar {dp_amount} sudah kami terima.\nSisa pembayaran: {remaining_amount}\nTotal tagihan: {total_amount}\n\nTerimakasih\n{store_name}\n{store_phone}\n{store_website}";
                                    $defaultPaidMessage = "Yth. Ibu/Bapak {customer_name}\n\nPembayaran untuk pesanan {order_number} sudah kami terima dengan total {total_amount}.\n\nTerimakasih\n{store_name}\n{store_phone}\n{store_website}";
                                    $defaultPackedMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} sudah kami kemas dan siap dikirim.\n\nTerimakasih\n{store_name}";
                                    $defaultShippedMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} sudah kami kirim melalui {courier}.\nNo. Resi: {tracking_number}\n\nTerimakasih\n{store_name}";
                                    $defaultDeliveredMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} telah sampai di tujuan.\nDiterima oleh: {received_by}\nTanggal & Jam: {delivered_at}\n\nTerimakasih telah berbelanja di {store_name}.\n\nSalam,\n{store_name}\n{store_phone}\n{store_website}";
                                    $defaultCancelledMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} telah dibatalkan.\nJika ada pertanyaan, silakan hubungi kami.\n\nTerimakasih\n{store_name}\n{store_phone}";
                                    $defaultCancelledRefundPendingMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} telah dibatalkan dan sedang diproses refund sebesar {refund_amount}.\nMohon tunggu konfirmasi lebih lanjut dari kami.\n\nTerimakasih\n{store_name}\n{store_phone}";
                                    $defaultRefundedMessage = "Yth. Ibu/Bapak {customer_name}\n\nRefund untuk pesanan {order_number} sebesar {refund_amount} telah diproses.\nSilakan cek rekening Anda.\n\nTerimakasih\n{store_name}\n{store_phone}";
                                    $templateKeyMap = [
                                        'draft' => ['wa_order_message', $defaultOrderMessage],
                                        'waiting_payment' => ['wa_order_message', $defaultOrderMessage],
                                        'dp_paid' => ['wa_dp_received_message', $defaultDpMessage],
                                        'paid' => ['wa_paid_message', $defaultPaidMessage],
                                        'packed' => ['wa_packed_message', $defaultPackedMessage],
                                        'shipped' => ['wa_shipped_message', $defaultShippedMessage],
                                        'done' => ['wa_delivered_message', $defaultDeliveredMessage],
                                        'cancelled' => ['wa_cancelled_message', $defaultCancelledMessage],
                                        'cancelled_refund_pending' => ['wa_cancelled_refund_pending_message', $defaultCancelledRefundPendingMessage],
                                        'refunded' => ['wa_refunded_message', $defaultRefundedMessage],
                                    ];
                                    $templateEntry = $templateKeyMap[$order->status] ?? null;
                                    $templateKey = $templateEntry[0] ?? 'wa_order_message';
                                    $templateDefault = $templateEntry[1] ?? $defaultOrderMessage;
                                    $template = \App\Models\Setting::get($templateKey, $templateDefault);
                                    $deliveredAt = $shipment?->delivered_at?->format('d M Y H:i') ?? '';
                                    $replacements = [
                                        '{customer_name}' => $customerName,
                                        '{order_number}' => $order->order_number,
                                        '{items}' => $itemsText,
                                        '{total_amount}' => 'Rp ' . number_format($order->total_amount, 0, ',', '.'),
                                        '{invoice_url}' => $publicInvoiceUrl,
                                        '{store_name}' => $storeName,
                                        '{store_phone}' => $storePhone,
                                        '{store_website}' => $storeWebsite,
                                        '{dp_amount}' => 'Rp ' . number_format($order->paid_amount ?? 0, 0, ',', '.'),
                                        '{remaining_amount}' => 'Rp ' . number_format($order->remainingAmount(), 0, ',', '.'),
                                        '{courier}' => $order->shipping_courier ?? ($shipment?->courier ?? ''),
                                        '{tracking_number}' => $shipment?->tracking_number ?? '',
                                        '{received_by}' => $shipment?->received_by ?? '',
                                        '{delivered_at}' => $deliveredAt,
                                        '{refund_amount}' => 'Rp ' . number_format($order->refund_amount ?? $order->paid_amount ?? 0, 0, ',', '.'),
                                    ];
                                    $message = str_replace(array_keys($replacements), array_values($replacements), $template);
                                    $waText = rawurlencode($message);
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $order->order_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->customer->name ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $order->type === 'order' ? 'bg-blue-100 text-blue' : 'bg-pink-100 text-pink' }}">
                                            {{ ucfirst($order->type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium
                                            {{ $order->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                                            {{ $order->status === 'waiting_payment' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $order->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $order->status === 'shipped' ? 'bg-blue-100 text-blue' : '' }}
                                            {{ $order->status === 'done' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $order->status === 'cancelled_refund_pending' ? 'bg-orange-100 text-orange-800' : '' }}
                                            {{ $order->status === 'refunded' ? 'bg-purple-100 text-purple-800' : '' }}
                                        ">
                                            @if($order->status === 'cancelled_refund_pending')
                                                Cancelled - Refund Pending
                                            @elseif($order->status === 'refunded')
                                                Refunded
                                            @else
                                                {{ str_replace('_', ' ', ucfirst($order->status)) }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ route('orders.show', $order) }}" class="text-blue hover:text-blue-light" title="Lihat">
                                                <x-heroicon name="eye" class="w-4 h-4" />
                                            </a>
                                            @if (!in_array($order->status, ['shipped', 'done', 'cancelled', 'cancelled_refund_pending', 'refunded'], true))
                                                <a href="{{ route('orders.edit', $order) }}" class="text-blue hover:text-blue-light" title="Edit">
                                                    <x-heroicon name="pencil-square" class="w-4 h-4" />
                                                </a>
                                            @endif
                                            @if(!in_array($order->status, ['paid', 'done', 'cancelled', 'cancelled_refund_pending', 'refunded'], true) && $order->remainingAmount() > 0)
                                                <a href="{{ route('payments.create', ['order_id' => $order->id]) }}" class="text-green-600 hover:text-green-700">
                                                    {{ $order->paid_amount > 0 || $order->status === 'dp_paid' ? 'Lunasi' : 'Bayar' }}
                                                </a>
                                            @endif
                                            @if ($waNumber !== '')
                                                <a href="https://api.whatsapp.com/send?phone={{ $waNumber }}&text={{ $waText }}" class="text-green-600 hover:text-green-700" title="Kirim Invoice" target="_blank" rel="noopener">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                        <path d="M20.52 3.48A11.84 11.84 0 0012.04 0C5.46 0 .1 5.36.1 11.94c0 2.1.54 4.16 1.58 5.98L0 24l6.28-1.64a11.87 11.87 0 005.76 1.47h.01c6.58 0 11.94-5.36 11.94-11.94 0-3.19-1.24-6.18-3.47-8.41zm-8.48 18.3h-.01a9.9 9.9 0 01-5.05-1.38l-.36-.22-3.73.98 1-3.63-.24-.37a9.88 9.88 0 01-1.51-5.22c0-5.46 4.44-9.9 9.9-9.9 2.64 0 5.12 1.03 6.99 2.9a9.83 9.83 0 012.9 6.99c0 5.46-4.44 9.9-9.9 9.9zm5.44-7.48c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.8-1.49-1.78-1.66-2.08-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.61-.92-2.2-.24-.58-.48-.5-.67-.5h-.57c-.2 0-.52.07-.8.37-.27.3-1.05 1.02-1.05 2.5 0 1.48 1.08 2.9 1.23 3.1.15.2 2.13 3.26 5.15 4.57.72.31 1.28.5 1.72.64.72.23 1.37.2 1.88.12.57-.08 1.76-.72 2.01-1.41.25-.7.25-1.3.17-1.41-.07-.12-.27-.2-.57-.35z"/>
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-6 text-center text-sm text-gray-500">
                                        Belum ada order
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500">Belum ada order</p>
                    </div>
                @endif
            </div>

            @if ($orders->lastPage() > 1)
                @php
                    $currentPage = $orders->currentPage();
                    $lastPage = $orders->lastPage();
                    $pages = collect([
                        1,
                        2,
                        $currentPage - 1,
                        $currentPage,
                        $currentPage + 1,
                        $lastPage - 1,
                        $lastPage,
                    ])->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                        ->unique()
                        ->sort()
                        ->values();
                @endphp
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-4 text-sm text-gray-600">
                        <div>Total order: {{ number_format($orders->total()) }}</div>
                        <div class="flex items-center gap-2">
                            <a href="{{ $orders->previousPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $orders->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}"
                               aria-label="Sebelumnya">
                                <x-heroicon name="chevron-left" class="w-4 h-4" />
                            </a>
                            @php $prev = null; @endphp
                            @foreach ($pages as $page)
                                @if ($prev && $page > $prev + 1)
                                    <span class="px-2 text-gray-400">...</span>
                                @endif
                                <a href="{{ $orders->url($page) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg border {{ $page === $currentPage ? 'border-primary bg-primary text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                                    {{ $page }}
                                </a>
                                @php $prev = $page; @endphp
                            @endforeach
                            <a href="{{ $orders->nextPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $orders->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}"
                               aria-label="Berikutnya">
                                <x-heroicon name="chevron-right" class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
