<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-900">Dashboard</h2>
    </x-slot>

    <div class="py-0 sm:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Today Summary -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-primary">
                    <div class="text-gray-500 text-sm font-medium">Pemasukan Hari Ini</div>
                    <div class="text-3xl font-bold text-primary mt-2">Rp {{ number_format($todayIncome, 0, ',', '.') }}</div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-pink">
                    <div class="text-gray-500 text-sm font-medium">Pengeluaran Hari Ini</div>
                    <div class="text-3xl font-bold text-pink mt-2">Rp {{ number_format($todayExpense, 0, ',', '.') }}</div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue">
                    <div class="text-gray-500 text-sm font-medium">Order Menunggu</div>
                    <div class="text-3xl font-bold text-blue mt-2">{{ $pendingOrders }}</div>
                    <div class="text-xs text-gray-500 mt-1">Rp {{ number_format($pendingAmount, 0, ',', '.') }}</div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue">
                    <div class="text-gray-500 text-sm font-medium">Preorder Aktif</div>
                    <div class="text-3xl font-bold text-blue mt-2">{{ $preorders }}</div>
                </div>
            </div>

            <!-- Month Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-semibold text-lg text-gray-900 mb-4">Ringkasan Bulan Ini</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Pemasukan</span>
                            <span class="text-2xl font-bold text-primary">Rp {{ number_format($monthIncome, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Pengeluaran</span>
                            <span class="text-2xl font-bold text-pink">Rp {{ number_format($monthExpense, 0, ',', '.') }}</span>
                        </div>
                        <div class="border-t pt-4 flex justify-between items-center">
                            <span class="text-gray-900 font-semibold">Saldo Bersih</span>
                            <span class="text-2xl font-bold {{ $monthIncome - $monthExpense >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                Rp {{ number_format($monthIncome - $monthExpense, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-semibold text-lg text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-2">
                        @can('create_orders')
                            <a href="{{ route('orders.create') }}" class="block px-4 py-2 bg-primary text-white rounded hover:bg-primary-hover transition text-center">
                                Buat Order Baru
                            </a>
                        @endcan
                        @can('create_purchases')
                            <a href="{{ route('purchases.create') }}" class="block px-4 py-2 bg-blue text-white rounded hover:bg-blue-light transition text-center">
                                Buat Pembelian
                            </a>
                        @endcan
                        @can('create_payments')
                            <a href="{{ route('payments.create') }}" class="block px-4 py-2 bg-pink text-white rounded hover:bg-pink-light transition text-center">
                                Input Pembayaran
                            </a>
                        @endcan
                        @can('create_ledger_entry')
                            <a href="{{ route('ledger.create') }}" class="block px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition text-center">
                                Catatan Kas Manual
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <h3 class="font-semibold text-lg text-gray-900 mb-4">Order Terbaru</h3>
                    @if ($recentOrders->count())
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gray-100 border-b">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Order</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Customer</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Type</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Total</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Status</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach ($recentOrders as $order)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm font-medium">{{ $order->order_number }}</td>
                                            <td class="px-4 py-2 text-sm">{{ $order->customer->name }}</td>
                                            <td class="px-4 py-2 text-sm">
                                                <span class="px-2 py-1 {{ $order->type === 'preorder' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-light-bg text-blue' }} rounded text-xs">
                                                    {{ ucfirst($order->type) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-sm font-medium">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                            <td class="px-4 py-2 text-sm">
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                                    {{ $order->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $order->status === 'waiting_payment' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $order->status === 'dp_paid' ? 'bg-blue-light-bg text-blue' : '' }}
                                                    {{ $order->status === 'packed' ? 'bg-purple-100 text-purple-800' : '' }}
                                                    {{ $order->status === 'shipped' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                                ">
                                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-sm">
                                                <a href="{{ route('orders.show', $order) }}" class="text-blue hover:underline">Lihat</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">Tidak ada order</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
