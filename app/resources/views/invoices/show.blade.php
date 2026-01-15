<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-900">Invoice #{{ $order->order_number }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-8 mb-6">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Link Publik Invoice (bagikan via WhatsApp)</label>
                    <div class="flex gap-2">
                        <input type="text" readonly value="{{ $publicUrl }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm">
                        <a href="{{ $publicUrl }}" target="_blank" class="px-3 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300">Buka</a>
                    </div>
                    <div class="mt-2">
                        <a href="{{ $publicDownloadUrl }}" class="text-sm text-blue hover:underline">Download PDF (public)</a>
                    </div>
                </div>
                <!-- Header -->
                <div class="flex justify-between items-start mb-12">
                    <div>
                        <h2 class="text-3xl font-bold text-primary">INVOICE</h2>
                        <p class="text-sm text-gray-500 mt-1">{{ $order->order_number }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold">{{ $store['name'] ?? config('app.name', 'Toko Ambu') }}</p>
                        @if(!empty($store['phone']))
                            <p class="text-sm text-gray-600">{{ $store['phone'] }}</p>
                        @endif
                        @if(!empty($store['address']))
                            <p class="text-sm text-gray-600">{{ $store['address'] }}</p>
                        @endif
                        <p class="text-sm text-gray-600">Tanggal: {{ $order->created_at->format('d M Y') }}</p>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="grid grid-cols-2 gap-6 mb-8 pb-8 border-b">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Bill To</p>
                        <p class="font-semibold">{{ $order->customer->name }}</p>
                        <p class="text-sm text-gray-600">{{ $order->customer->address }}</p>
                        <p class="text-sm text-gray-600">{{ $order->customer->phone }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500 uppercase">Tipe Order</p>
                        <span class="px-3 py-1 rounded-full text-sm font-medium {{ $order->type === 'order' ? 'bg-blue-100 text-blue' : 'bg-pink-100 text-pink' }}">
                            {{ ucfirst($order->type) }}
                        </span>
                    </div>
                </div>

                <!-- Items Table -->
                @php
                    $flashSaleSavings = 0;
                @endphp
                <table class="min-w-full mb-8">
                    <thead>
                        <tr class="border-b-2 border-gray-300">
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Produk</th>
                            <th class="text-center py-3 px-2 font-semibold text-gray-700">Qty</th>
                            <th class="text-right py-3 px-2 font-semibold text-gray-700">Harga Satuan</th>
                            <th class="text-right py-3 px-2 font-semibold text-gray-700">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            @php
                                $originalPrice = $item->price ?? null;
                                if ($originalPrice !== null && $originalPrice > $item->unit_price) {
                                    $flashSaleSavings += ($originalPrice - $item->unit_price) * $item->quantity;
                                }
                            @endphp
                            <tr class="border-b">
                                <td class="py-4 px-2">
                                    <div>{{ $item->product->name }}</div>
                                    @if($originalPrice !== null && $originalPrice > $item->unit_price)
                                        <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600">
                                            Flash Sale
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center py-4 px-2">{{ $item->quantity }}</td>
                                <td class="text-right py-4 px-2">
                                    @if($originalPrice !== null && $originalPrice > $item->unit_price)
                                        <div class="text-xs text-gray-400 line-through decoration-2 decoration-red-400">
                                            Rp {{ number_format($originalPrice, 0, ',', '.') }}
                                        </div>
                                    @endif
                                    <div>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</div>
                                </td>
                                <td class="text-right py-4 px-2 font-semibold">Rp {{ number_format($item->subtotal ?? ($item->quantity * $item->unit_price), 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Totals -->
                <div class="flex justify-end mb-8">
                    <div class="w-64">
                        @if($flashSaleSavings > 0)
                            <div class="flex justify-between py-2 border-b text-sm text-red-600">
                                <span>Diskon Flash Sale</span>
                                <span>- Rp {{ number_format($flashSaleSavings, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between py-2 border-b text-sm">
                            <span>Ongkos Kirim{{ $order->shipping_courier ? ' (' . strtoupper($order->shipping_courier) . ')' : '' }}</span>
                            <span>Rp {{ number_format($order->shipping_cost ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b-2 border-gray-300">
                            <span class="font-semibold">Total:</span>
                            <span class="font-bold text-lg text-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                        @if($flashSaleSavings > 0)
                            <div class="mt-2 text-xs text-gray-500 text-right">
                                Anda hemat Rp {{ number_format($flashSaleSavings, 0, ',', '.') }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Notes -->
                @if($order->notes)
                    <div class="mb-8 p-4 bg-gray-50 rounded">
                        <p class="text-sm text-gray-500 uppercase font-semibold">Catatan</p>
                        <p class="text-gray-700">{{ $order->notes }}</p>
                    </div>
                @endif

                <div class="mt-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Histori Pembayaran</h3>
                    @if($order->payments->count())
                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse border border-gray-300 text-sm">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border border-gray-300 px-3 py-2 text-left">Tanggal</th>
                                        <th class="border border-gray-300 px-3 py-2 text-left">Metode</th>
                                        <th class="border border-gray-300 px-3 py-2 text-left">Status</th>
                                        <th class="border border-gray-300 px-3 py-2 text-right">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->payments as $payment)
                                        <tr>
                                            <td class="border border-gray-300 px-3 py-2">{{ $payment->paid_at?->format('d M Y H:i') ?? '-' }}</td>
                                            <td class="border border-gray-300 px-3 py-2">{{ $payment->method ?? '-' }}</td>
                                            <td class="border border-gray-300 px-3 py-2 capitalize">{{ $payment->status ?? '-' }}</td>
                                            <td class="border border-gray-300 px-3 py-2 text-right">Rp {{ number_format($payment->amount ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-50">
                                        <td colspan="3" class="border border-gray-300 px-3 py-2 text-right font-semibold">Total Dibayar</td>
                                        <td class="border border-gray-300 px-3 py-2 text-right font-semibold">Rp {{ number_format($order->paid_amount ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="border border-gray-300 px-3 py-2 text-right font-semibold text-primary">Sisa Tagihan</td>
                                        <td class="border border-gray-300 px-3 py-2 text-right font-semibold text-primary">Rp {{ number_format($order->remainingAmount(), 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Belum ada pembayaran.</p>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                <form action="{{ route('invoices.send', $order) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-center">
                        Kirim ke Customer
                    </button>
                </form>
                <a href="{{ route('invoices.download', $order) }}" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-hover text-center">
                    Download PDF
                </a>
                <a href="{{ route('invoices.print', $order) }}" class="flex-1 bg-blue text-white px-4 py-2 rounded-lg hover:bg-blue-light text-center" target="_blank">
                    Print
                </a>
                <a href="{{ route('orders.show', $order) }}" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 text-center">
                    Kembali
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
