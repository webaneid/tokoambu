<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-900">Refund</h2>
            <a href="{{ route('refunds.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-hover transition-colors">
                + Buat Refund
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Orders Pending Refund (Belum Diproses) -->
            @if($pendingRefundOrders->count() > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-6 h-6 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Order Belum Diproses Refund ({{ $pendingRefundOrders->count() }})
                </h3>
                <p class="text-sm text-gray-600 mb-4">Order dengan status "Cancelled - Refund Pending" yang belum dibuatkan refund entry.</p>

                <div class="block sm:hidden divide-y divide-yellow-100">
                    @foreach($pendingRefundOrders as $order)
                        <div class="p-4 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900">{{ $order->order_number }}</p>
                                    <p class="text-xs text-gray-500">{{ $order->customer->name ?? 'Customer tidak diketahui' }}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending Refund</span>
                            </div>
                            <div class="text-xs text-gray-600 space-y-1">
                                <p><span class="font-medium text-gray-800">Total Dibayar:</span> Rp {{ number_format($order->payments->where('status', 'verified')->sum('amount'), 0, ',', '.') }}</p>
                                <p><span class="font-medium text-gray-800">Cancel:</span> {{ $order->updated_at->format('d M Y H:i') }}</p>
                            </div>
                            <div class="flex items-center gap-3 text-sm">
                                <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center gap-1 text-blue hover:text-blue-light">
                                    <x-heroicon name="eye" class="w-4 h-4" /> Order
                                </a>
                                <a href="{{ route('refunds.create', ['order_id' => $order->id]) }}" class="inline-flex items-center gap-1 text-primary">
                                    <x-heroicon name="arrow-path" class="w-4 h-4" /> Proses
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-yellow-200">
                        <thead class="bg-yellow-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Order</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Customer</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Total Dibayar</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tanggal Cancel</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-yellow-100">
                            @foreach($pendingRefundOrders as $order)
                            <tr class="hover:bg-yellow-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    <a href="{{ route('orders.show', $order) }}" class="text-blue-600 hover:underline font-medium">
                                        {{ $order->order_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    {{ $order->customer->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-right text-gray-900">
                                    Rp {{ number_format($order->payments->where('status', 'verified')->sum('amount'), 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    {{ $order->updated_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-sm">
                                    <a href="{{ route('refunds.create', ['order_id' => $order->id]) }}"
                                       class="inline-block px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover text-sm font-medium">
                                        Proses Refund
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Filter -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <form method="GET" action="{{ route('refunds.index') }}" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nomor order atau customer" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="submit" class="w-full sm:w-auto px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition-colors">Filter</button>
                        <a href="{{ route('refunds.index') }}" class="w-full sm:w-auto px-6 py-2 border border-gray-300 rounded-lg text-center hover:bg-gray-50 transition-colors">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Refunds List -->
            <div class="bg-white rounded-lg shadow">
                @if($refunds->count())
                    <div class="block sm:hidden divide-y divide-gray-100">
                        @foreach($refunds as $refund)
                            <div class="p-4 space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900">{{ $refund->order->order_number }}</p>
                                        <p class="text-xs text-gray-500">{{ $refund->created_at->format('d M Y') }} · {{ $refund->order->customer->name ?? '-' }}</p>
                                    </div>
                                    @php
                                        $statusColor = match($refund->status) {
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            default => 'bg-yellow-100 text-yellow-800',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }} capitalize">{{ $refund->status }}</span>
                                </div>
                                <div class="text-xs text-gray-600 space-y-1">
                                    <p><span class="font-medium text-gray-800">Jumlah:</span> Rp {{ number_format($refund->amount, 0, ',', '.') }}</p>
                                    <p><span class="font-medium text-gray-800">Alasan:</span> {{ Str::limit($refund->reason, 80) }}</p>
                                </div>
                                <div class="flex items-center gap-3 text-sm">
                                    <a href="{{ route('orders.show', $refund->order) }}" class="inline-flex items-center gap-1 text-blue hover:text-blue-light">
                                        <x-heroicon name="eye" class="w-4 h-4" /> Order
                                    </a>
                                    <a href="{{ route('refunds.show', $refund) }}" class="inline-flex items-center gap-1 text-blue hover:text-blue-light">
                                        <x-heroicon name="information-circle" class="w-4 h-4" /> Detail
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alasan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($refunds as $refund)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $refund->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <a href="{{ route('orders.show', $refund->order) }}" class="text-blue-600 hover:underline">
                                        {{ $refund->order->order_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $refund->order->customer->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ Str::limit($refund->reason, 40) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right text-gray-900">
                                    Rp {{ number_format($refund->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($refund->status === 'pending')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @elseif($refund->status === 'approved')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Approved
                                        </span>
                                    @else
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Rejected
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <a href="{{ route('refunds.show', $refund) }}" class="text-blue-600 hover:underline">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Belum ada refund
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>

                <div class="px-6 py-4">
                    {{ $refunds->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
