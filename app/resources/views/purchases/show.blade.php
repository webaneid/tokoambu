<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm text-gray-500">No. PO</p>
            <h2 class="font-semibold text-2xl text-gray-900">{{ $purchase->purchase_number }}</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Supplier</p>
                        <p class="text-sm font-medium text-gray-900">{{ $purchase->supplier->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Status</p>
                        @php
                            $status = $purchase->status;
                            $statusClass = [
                                'received' => 'bg-green-100 text-green-800',
                                'ordered' => 'bg-blue-100 text-blue-800',
                                'draft' => 'bg-yellow-100 text-yellow-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                            ][$status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $statusClass }}">
                            {{ ucfirst($status) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Total</p>
                        <p class="text-lg font-semibold text-gray-900">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</p>
                    </div>
                    <div class="md:col-span-3">
                        <p class="text-xs text-gray-500 mb-1">Catatan</p>
                        <p class="text-sm text-gray-700">{{ $purchase->notes ?: '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-900">Item Pembelian</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($purchase->items as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $item->product->name ?? '-' }}
                                        @if($item->productVariant)
                                            <span class="text-xs text-gray-500 ml-2">
                                                ({{ implode(' / ', $item->productVariant->variant_attributes) }})
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">Rp {{ number_format($item->unit_price ?? $item->unit_cost ?? 0, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 font-medium">Rp {{ number_format($item->subtotal ?? ($item->unit_price ?? 0) * $item->quantity, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 flex flex-wrap items-center justify-end gap-3">
                    <a href="{{ route('purchases.edit', $purchase) }}" class="h-10 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center">Edit</a>
                    <a href="{{ route('warehouse.receiving.index', ['q' => $purchase->purchase_number]) }}" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover transition flex items-center">Receiving</a>
                    @if(($purchase->payment_status ?? 'pending') !== 'paid')
                        <a href="{{ route('purchases.index', ['pay' => $purchase->id]) }}" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover transition flex items-center">Bayar</a>
                    @else
                        <span class="text-sm text-gray-500">Paid</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
