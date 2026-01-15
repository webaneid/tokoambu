<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-900">Edit Pembelian {{ $purchase->purchase_number }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                @php($locked = $purchase->status === 'received')
                <form action="{{ route('purchases.update', $purchase) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                            <select name="supplier_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" @disabled($locked)>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ $purchase->supplier_id === $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" @disabled($locked)>
                                <option value="draft" @selected($purchase->status === 'draft')>Draft</option>
                                <option value="ordered" @selected($purchase->status === 'ordered')>Ordered</option>
                                <option value="received" @selected($purchase->status === 'received')>Received</option>
                                <option value="cancelled" @selected($purchase->status === 'cancelled')>Cancelled</option>
                            </select>
                            @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Purchase Items -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Item Pembelian</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 mb-4">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($purchase->items as $item)
                                        <tr>
                                            <td class="px-6 py-4 text-sm text-gray-900">{{ $item->product->name }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-900">{{ $item->quantity }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-900">Rp {{ number_format($item->unit_price ?? $item->unit_cost ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="border-t pt-6">
                        <div class="text-right">
                            <p class="text-gray-600 mb-2">Total:</p>
                            <p class="text-3xl font-bold text-primary">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-6">
                        <button type="submit" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-hover transition-colors" @disabled($locked)>
                            {{ $locked ? 'Terkunci' : 'Simpan Perubahan' }}
                        </button>
                        <a href="{{ route('purchases.index') }}" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 text-center">
                            Batal
                        </a>
                    </div>

                    @if($locked)
                        <p class="text-sm text-red-600">PO sudah berstatus received dan tidak dapat diubah.</p>
                    @endif
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
