<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-900">Invoice</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-3 lg:px-8">
            {{-- Success/Error Messages --}}
            @if ($message = Session::get('success'))
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ $message }}
                </div>
            @endif
            @if ($message = Session::get('error'))
                <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    {{ $message }}
                </div>
            @endif

            {{-- Table Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
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
                            $storeName = \App\Models\Setting::get('store_name', config('app.name', 'Toko Ambu'));
                            $storePhone = \App\Models\Setting::get('store_phone', '');
                            $storeWebsite = \App\Models\Setting::get('store_website', '');
                            $customerName = $order->customer?->name ?? 'Pelanggan';
                            $shipment = $order->shipment;

                            // Default messages
                            $defaultOrderMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan Anda nomor: {order_number} yaitu:\n{items}\ntelah kami buat/terima, total pembayaran: {total_amount}\nsilahkan melakukan pembayaran melalui:\n{invoice_url}\n\nTerimakasih\n{store_name}\n{store_phone}\n{store_website}";
                            $defaultDpMessage = "Yth. Ibu/Bapak {customer_name}\n\nPembayaran DP untuk pesanan {order_number} sebesar {dp_amount} sudah kami terima.\nSisa pembayaran: {remaining_amount}\nTotal tagihan: {total_amount}\n\nTerimakasih\n{store_name}\n{store_phone}\n{store_website}";
                            $defaultPaidMessage = "Yth. Ibu/Bapak {customer_name}\n\nPembayaran untuk pesanan {order_number} sudah kami terima dengan total {total_amount}.\n\nTerimakasih\n{store_name}\n{store_phone}\n{store_website}";
                            $defaultPackedMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} sudah kami kemas dan siap dikirim.\n\nTerimakasih\n{store_name}";
                            $defaultShippedMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} sudah kami kirim melalui {courier}.\nNo. Resi: {tracking_number}\n\nTerimakasih\n{store_name}";
                            $defaultDeliveredMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} telah sampai di tujuan.\nDiterima oleh: {received_by}\nTanggal & Jam: {delivered_at}\n\nTerimakasih telah berbelanja di {store_name}.\n\nSalam,\n{store_name}\n{store_phone}\n{store_website}";
                            $defaultCancelledMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} telah dibatalkan.\nJika ada pertanyaan, silakan hubungi kami.\n\nTerimakasih\n{store_name}\n{store_phone}";
                            $defaultCancelledRefundPendingMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} telah dibatalkan dan sedang diproses refund sebesar {refund_amount}.\nMohon tunggu konfirmasi lebih lanjut dari kami.\n\nTerimakasih\n{store_name}\n{store_phone}";
                            $defaultRefundedMessage = "Yth. Ibu/Bapak {customer_name}\n\nRefund untuk pesanan {order_number} sebesar {refund_amount} telah diproses.\nSilakan cek rekening Anda.\n\nTerimakasih\n{store_name}\n{store_phone}";

                            // Template mapping
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
                        <div class="p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs text-gray-500">No. Order</div>
                                    <div class="text-sm font-semibold text-gray-900">{{ $order->order_number }}</div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('invoices.show', $order) }}" class="text-blue hover:text-blue-light inline-flex items-center gap-1" title="Lihat">
                                        <x-heroicon name="eye" class="w-4 h-4" />
                                    </a>
                                    @if ($waNumber !== '')
                                        <a href="https://api.whatsapp.com/send?phone={{ $waNumber }}&text={{ $waText }}" class="text-green-600 hover:text-green-700" title="Kirim WhatsApp" target="_blank" rel="noopener">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                <path d="M20.52 3.48A11.84 11.84 0 0012.04 0C5.46 0 .1 5.36.1 11.94c0 2.1.54 4.16 1.58 5.98L0 24l6.28-1.64a11.87 11.87 0 005.76 1.47h.01c6.58 0 11.94-5.36 11.94-11.94 0-3.19-1.24-6.18-3.47-8.41zm-8.48 18.3h-.01a9.9 9.9 0 01-5.05-1.38l-.36-.22-3.73.98 1-3.63-.24-.37a9.88 9.88 0 01-1.51-5.22c0-5.46 4.44-9.9 9.9-9.9 2.64 0 5.12 1.03 6.99 2.9a9.83 9.83 0 012.9 6.99c0 5.46-4.44 9.9-9.9 9.9zm5.44-7.48c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.8-1.49-1.78-1.66-2.08-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.61-.92-2.2-.24-.58-.48-.5-.67-.5h-.57c-.2 0-.52.07-.8.37-.27.3-1.05 1.02-1.05 2.5 0 1.48 1.08 2.9 1.23 3.1.15.2 2.13 3.26 5.15 4.57.72.31 1.28.5 1.72.64.72.23 1.37.2 1.88.12.57-.08 1.76-.72 2.01-1.41.25-.7.25-1.3.17-1.41-.07-.12-.27-.2-.57-.35z"/>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <div class="text-xs text-gray-500">Customer</div>
                                    <div class="text-gray-900">{{ $order->customer?->name ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Tipe</div>
                                    <div class="text-gray-900 capitalize">{{ $order->type ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Status</div>
                                    @php
                                        $statusLabel = [
                                            'waiting_payment' => 'Blm Dibayar',
                                            'dp_paid' => 'DP Paid',
                                            'paid' => 'Paid',
                                            'packed' => 'Packed',
                                            'shipped' => 'Shipped',
                                            'done' => 'Done',
                                            'cancelled' => 'Cancelled',
                                        ][$order->status] ?? ucfirst($order->status ?? '-');
                                    @endphp
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium
                                        @if($order->status === 'done') bg-green-100 text-green-800
                                        @elseif($order->status === 'shipped') bg-blue-100 text-blue-800
                                        @elseif($order->status === 'paid') bg-purple-100 text-purple-800
                                        @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800
                                        @endif">
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Total</div>
                                    <div class="text-gray-900">Rp {{ number_format($order->total_amount ?? 0, 0, ',', '.') }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Invoice Dikirim</div>
                                    <div class="text-gray-900">{{ $order->invoice_sent_at ? $order->invoice_sent_at->format('d M Y H:i') : '-' }}</div>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                <a href="{{ route('invoices.show', $order) }}" class="text-blue hover:text-blue-light inline-flex items-center gap-1" title="Lihat">
                                    <x-heroicon name="eye" class="w-4 h-4" />
                                </a>
                                <a href="{{ route('invoices.download', $order) }}" class="text-blue hover:text-blue-light inline-flex items-center gap-1" title="Download">
                                    <x-heroicon name="arrow-down-tray" class="w-4 h-4" />
                                </a>
                                <a href="{{ route('invoices.print', $order) }}" class="text-blue hover:text-blue-light inline-flex items-center gap-1" title="Print" target="_blank">
                                    <x-heroicon name="printer" class="w-4 h-4" />
                                </a>
                                @if ($order->customer?->email)
                                    <form action="{{ route('invoices.send', $order) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-blue hover:text-blue-light inline-flex items-center gap-1" title="Kirim Email">
                                            <x-heroicon name="envelope" class="w-4 h-4" />
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center text-gray-500 text-sm">
                            Belum ada invoice.
                        </div>
                    @endforelse
                </div>
                <div class="hidden sm:block">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Order</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice Dikirim</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
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
                                    $storeName = \App\Models\Setting::get('store_name', config('app.name', 'Toko Ambu'));
                                    $storePhone = \App\Models\Setting::get('store_phone', '');
                                    $storeWebsite = \App\Models\Setting::get('store_website', '');
                                    $customerName = $order->customer?->name ?? 'Pelanggan';
                                    $shipment = $order->shipment;

                                    // Default messages
                                    $defaultOrderMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan Anda nomor: {order_number} yaitu:\n{items}\ntelah kami buat/terima, total pembayaran: {total_amount}\nsilahkan melakukan pembayaran melalui:\n{invoice_url}\n\nTerimakasih\n{store_name}\n{store_phone}\n{store_website}";
                                    $defaultDpMessage = "Yth. Ibu/Bapak {customer_name}\n\nPembayaran DP untuk pesanan {order_number} sebesar {dp_amount} sudah kami terima.\nSisa pembayaran: {remaining_amount}\nTotal tagihan: {total_amount}\n\nTerimakasih\n{store_name}\n{store_phone}\n{store_website}";
                                    $defaultPaidMessage = "Yth. Ibu/Bapak {customer_name}\n\nPembayaran untuk pesanan {order_number} sudah kami terima dengan total {total_amount}.\n\nTerimakasih\n{store_name}\n{store_phone}\n{store_website}";
                                    $defaultPackedMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} sudah kami kemas dan siap dikirim.\n\nTerimakasih\n{store_name}";
                                    $defaultShippedMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} sudah kami kirim melalui {courier}.\nNo. Resi: {tracking_number}\n\nTerimakasih\n{store_name}";
                                    $defaultDeliveredMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} telah sampai di tujuan.\nDiterima oleh: {received_by}\nTanggal & Jam: {delivered_at}\n\nTerimakasih telah berbelanja di {store_name}.\n\nSalam,\n{store_name}\n{store_phone}\n{store_website}";
                                    $defaultCancelledMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} telah dibatalkan.\nJika ada pertanyaan, silakan hubungi kami.\n\nTerimakasih\n{store_name}\n{store_phone}";
                                    $defaultCancelledRefundPendingMessage = "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} telah dibatalkan dan sedang diproses refund sebesar {refund_amount}.\nMohon tunggu konfirmasi lebih lanjut dari kami.\n\nTerimakasih\n{store_name}\n{store_phone}";
                                    $defaultRefundedMessage = "Yth. Ibu/Bapak {customer_name}\n\nRefund untuk pesanan {order_number} sebesar {refund_amount} telah diproses.\nSilakan cek rekening Anda.\n\nTerimakasih\n{store_name}\n{store_phone}";

                                    // Template mapping
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
                                    <td class="px-3 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $order->order_number }}
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $order->customer?->name ?? '-' }}
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">
                                        {{ $order->type ?? '-' }}
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm">
                                        @php
                                            $statusLabel = [
                                                'waiting_payment' => 'Blm Dibayar',
                                                'dp_paid' => 'DP Paid',
                                                'paid' => 'Paid',
                                                'packed' => 'Packed',
                                                'shipped' => 'Shipped',
                                                'done' => 'Done',
                                                'cancelled' => 'Cancelled',
                                            ][$order->status] ?? ucfirst($order->status ?? '-');
                                        @endphp
                                        <span class="px-3 py-1 rounded-full text-xs font-medium
                                            @if($order->status === 'done') bg-green-100 text-green-800
                                            @elseif($order->status === 'shipped') bg-blue-100 text-blue-800
                                            @elseif($order->status === 'paid') bg-purple-100 text-purple-800
                                            @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                            @else bg-yellow-100 text-yellow-800
                                            @endif">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                        Rp {{ number_format($order->total_amount ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $order->invoice_sent_at ? $order->invoice_sent_at->format('d M Y H:i') : '-' }}
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('invoices.show', $order) }}" class="text-blue hover:text-blue-light inline-flex items-center gap-1" title="Lihat">
                                                <x-heroicon name="eye" class="w-4 h-4" />
                                            </a>
                                            <a href="{{ route('invoices.download', $order) }}" class="text-blue hover:text-blue-light inline-flex items-center gap-1" title="Download">
                                                <x-heroicon name="arrow-down-tray" class="w-4 h-4" />
                                            </a>
                                            <a href="{{ route('invoices.print', $order) }}" class="text-blue hover:text-blue-light inline-flex items-center gap-1" title="Print" target="_blank">
                                                <x-heroicon name="printer" class="w-4 h-4" />
                                            </a>
                                            @if ($order->customer?->email)
                                                <form action="{{ route('invoices.send', $order) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-blue hover:text-blue-light inline-flex items-center gap-1" title="Kirim Email">
                                                        <x-heroicon name="envelope" class="w-4 h-4" />
                                                    </button>
                                                </form>
                                            @endif
                                            @if ($waNumber !== '')
                                                <a href="https://api.whatsapp.com/send?phone={{ $waNumber }}&text={{ $waText }}" class="text-green-600 hover:text-green-700" title="Kirim WhatsApp" target="_blank" rel="noopener">
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
                                    <td colspan="7" class="px-3 py-12 text-center text-gray-500 text-sm">
                                        Belum ada invoice.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination Card --}}
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
                        <div>Total invoice: {{ number_format($orders->total()) }}</div>
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
