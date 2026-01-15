<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Preorder: {{ $product->name }}
            </h2>
             <a href="{{ route('preorders.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-0 sm:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4">
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-sm text-gray-600 mb-1">Total Pesanan</div>
                    <div class="text-2xl font-semibold text-gray-900">{{ $stats['total_orders'] }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-sm text-gray-600 mb-1">Total Qty</div>
                    <div class="text-2xl font-semibold text-primary">{{ $stats['total_qty'] }} pcs</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-sm text-gray-600 mb-1">DP Terkumpul</div>
                    <div class="text-2xl font-semibold text-green-600">Rp {{ number_format($stats['total_dp_collected'], 0, ',', '.') }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-sm text-gray-600 mb-1">Potensi Omzet</div>
                    <div class="text-2xl font-semibold text-blue-600">Rp {{ number_format($stats['total_revenue_potential'], 0, ',', '.') }}</div>
                </div>
            </div>

            <!-- Inventory Summary -->
            @php
                // Calculate reserved from actual DP-paid orders
                $totalReserved = $ordersByStatus['dp_paid']->sum(function ($order) use ($product) {
                    return $order->items->where('product_id', $product->id)->sum('quantity');
                });
                $totalOnHand = $product->qty_on_hand;
                $available = max(0, $totalOnHand - $totalReserved);
            @endphp

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Stock Status</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-4">
                    <div class="p-4 bg-blue-50 rounded-lg border-2 border-blue-200">
                        <p class="text-xs text-blue-600 font-medium uppercase mb-1">Stock Fisik</p>
                        <p class="text-3xl font-bold text-blue-900">{{ number_format($totalOnHand, 0) }}</p>
                        <p class="text-xs text-blue-600 mt-1">Di gudang sekarang</p>
                    </div>

                    <div class="p-4 bg-orange-50 rounded-lg border-2 border-orange-200">
                        <p class="text-xs text-orange-600 font-medium uppercase mb-1">Reserved</p>
                        <p class="text-3xl font-bold text-orange-900">{{ number_format($totalReserved, 0) }}</p>
                        <p class="text-xs text-orange-600 mt-1">Sudah bayar DP ({{ $ordersByStatus['dp_paid']->count() }} customer)</p>
                    </div>

                    <div class="p-4 bg-green-50 rounded-lg border-2 border-green-200">
                        <p class="text-xs text-green-600 font-medium uppercase mb-1">Available</p>
                        <p class="text-3xl font-bold text-green-900">{{ number_format($available, 0) }}</p>
                        <p class="text-xs text-green-600 mt-1">Bisa dijual regular</p>
                    </div>
                </div>

                @if($totalReserved > 0)
                    <div class="mt-4 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                        <p class="text-sm text-orange-900">
                            <strong>{{ number_format($totalReserved, 0) }} pcs</strong> stock di-reserve untuk <strong>{{ $ordersByStatus['dp_paid']->count() }} customer</strong> yang sudah bayar DP. Stock ini tidak bisa dijual ke customer lain sampai preorder selesai atau dibatalkan.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Action Button: Mark Product Ready -->
            @if ($ordersByStatus['dp_paid']->count() > 0)
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-900">Produk Sudah Siap?</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Ada {{ $ordersByStatus['dp_paid']->count() }} pelanggan yang sudah membayar DP dan menunggu produk siap.
                            </p>
                        </div>
                        <form action="{{ route('preorders.mark_ready', $product) }}" method="POST" onsubmit="return confirm('Tandai produk siap dan notifikasi pelanggan untuk pelunasan?')">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 transition">
                                Tandai Produk Siap
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Preorder Periods Management -->
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Periode Preorder</h3>
                        <a href="{{ route('preorders.periods.create', $product) }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition text-sm">
                            + Buat Periode Baru
                        </a>
                    </div>

                    @if ($periods->isEmpty())
                        <div class="text-center py-8 text-gray-500">
                            <p class="mb-2">Belum ada periode preorder</p>
                            <p class="text-sm">Buat periode untuk mengelompokkan order berdasarkan batch/waktu tertentu</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($periods as $period)
                                <div class="border rounded-lg p-4 {{ $period->status === 'active' ? 'border-green-300 bg-green-50' : ($period->status === 'closed' ? 'border-orange-300 bg-orange-50' : 'border-gray-300 bg-gray-50') }}">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <h4 class="font-semibold text-gray-900">{{ $period->name }}</h4>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-{{ $period->status_color }}-100 text-{{ $period->status_color }}-800">
                                                    {{ $period->status_label }}
                                                </span>
                                            </div>

                                            @if ($period->description)
                                                <p class="text-sm text-gray-600 mb-2">{{ $period->description }}</p>
                                            @endif

                                            <div class="flex items-center gap-4 text-sm text-gray-600">
                                                <span>{{ $period->start_date->format('d M Y') }} - {{ $period->end_date->format('d M Y') }}</span>
                                                <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded">
                                                    {{ $period->total_orders }} order | {{ $period->total_quantity }} pcs
                                                </span>
                                                @if ($period->target_quantity)
                                                    <span class="text-xs">Target: {{ $period->target_quantity }} pcs</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex gap-2 ml-4">
                                            @if ($period->status === 'active')
                                                <form action="{{ route('preorders.periods.close', $period) }}" method="POST" onsubmit="return confirm('Tutup periode ini? Order baru tidak bisa masuk ke periode ini.')">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1 text-xs bg-orange-600 text-white rounded hover:bg-orange-700">
                                                        Tutup
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($period->status === 'closed')
                                                <form action="{{ route('preorders.periods.reopen', $period) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">
                                                        Buka Kembali
                                                    </button>
                                                </form>
                                                <form action="{{ route('preorders.periods.archive', $period) }}" method="POST" onsubmit="return confirm('Arsipkan periode ini?')" class="inline">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1 text-xs bg-gray-600 text-white rounded hover:bg-gray-700">
                                                        Arsipkan
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($period->status === 'archived')
                                                <form action="{{ route('preorders.periods.reopen', $period) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">
                                                        Aktifkan Kembali
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div x-data="{ tab: 'all' }">
            <!-- Tab Navigation -->
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <nav class="-mb-6 flex flex-nowrap gap-4 overflow-x-auto whitespace-nowrap">
                        <button @click="tab = 'all'" :class="tab === 'all' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-xs sm:text-sm">
                            Semua ({{ $orders->count() }})
                        </button>
                        <button @click="tab = 'waiting_dp'" :class="tab === 'waiting_dp' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-xs sm:text-sm">
                            Menunggu DP ({{ $ordersByStatus['waiting_dp']->count() }})
                        </button>
                        <button @click="tab = 'dp_paid'" :class="tab === 'dp_paid' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-xs sm:text-sm">
                            DP Lunas ({{ $ordersByStatus['dp_paid']->count() }})
                        </button>
                        <button @click="tab = 'product_ready'" :class="tab === 'product_ready' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-xs sm:text-sm">
                            Produk Siap ({{ $ordersByStatus['product_ready']->count() }})
                        </button>
                        <button @click="tab = 'waiting_payment'" :class="tab === 'waiting_payment' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-xs sm:text-sm">
                            Menunggu Pelunasan ({{ $ordersByStatus['waiting_payment']->count() }})
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="bg-white shadow-sm sm:rounded-lg mt-6">
                <div class="block sm:hidden divide-y divide-gray-200">
                    @forelse ($orders as $order)
                        @php
                            $itemQty = $order->items->where('product_id', $product->id)->sum('quantity');
                            $remaining = $order->remainingFinalAmount();
                        @endphp
                        <div x-show="tab === 'all' || tab === '{{ $order->status }}'" class="p-4 space-y-2">
                            <div class="min-w-0">
                                <a href="{{ route('orders.show', $order) }}" class="text-sm font-semibold text-gray-900 hover:underline">
                                    {{ $order->order_number }}
                                </a>
                                <p class="text-sm text-gray-500">{{ $order->customer->name }}</p>
                                <p class="text-xs text-gray-500">{{ $order->customer->phone }}</p>
                            </div>
                            @if ($order->preorderPeriod)
                                <span class="px-2 py-1 text-xs bg-{{ $order->preorderPeriod->status_color }}-100 text-{{ $order->preorderPeriod->status_color }}-800 rounded">
                                    {{ $order->preorderPeriod->name }}
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                            <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
                                <span>Qty: {{ $itemQty }}</span>
                                <span class="text-gray-300">•</span>
                                <span>Total: Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
                                <span>DP: {{ $order->paid_amount ? 'Rp ' . number_format($order->paid_amount, 0, ',', '.') : '-' }}</span>
                                <span class="text-gray-300">•</span>
                                <span>Sisa: Rp {{ number_format($remaining, 0, ',', '.') }}</span>
                            </div>
                            <div class="text-sm text-gray-500">
                                @if ($order->status === 'waiting_dp' && $order->dp_payment_deadline)
                                    <div>Deadline: {{ $order->dp_payment_deadline->format('d/m/Y') }}</div>
                                    @if ($order->dp_payment_deadline->isPast())
                                        <span class="text-xs text-red-600 font-medium">Expired</span>
                                    @endif
                                @elseif (in_array($order->status, ['product_ready', 'waiting_payment']) && $order->final_payment_deadline)
                                    <div>Deadline: {{ $order->final_payment_deadline->format('d/m/Y') }}</div>
                                    @if ($order->final_payment_deadline->isPast())
                                        <span class="text-xs text-red-600 font-medium">Expired</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">Deadline: -</span>
                                @endif
                            </div>
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $order->getStatusColor() }}-100 text-{{ $order->getStatusColor() }}-800">
                                {{ \App\Models\Order::getStatuses()[$order->status] }}
                            </span>
                            <div class="flex items-center gap-3 pt-2">
                                <a href="{{ route('orders.show', $order) }}" class="text-blue hover:text-blue-light" title="Lihat Detail">
                                    <x-heroicon name="eye" class="w-4 h-4" />
                                </a>
                                @if ($order->status === 'waiting_dp' || $order->status === 'product_ready' || $order->status === 'waiting_payment')
                                    <button
                                        onclick="openWhatsApp({{ $order->id }}, '{{ $order->status === 'waiting_dp' ? 'dp_reminder' : 'final_reminder' }}')"
                                        class="text-green-600 hover:text-green-700"
                                        title="Kirim WhatsApp">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center text-gray-500 text-sm">
                            Belum ada pesanan preorder untuk produk ini
                        </div>
                    @endforelse
                </div>

                <div class="hidden sm:block">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">DP</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Sisa</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deadline</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($orders as $order)
                                @php
                                    $itemQty = $order->items->where('product_id', $product->id)->sum('quantity');
                                    $remaining = $order->remainingFinalAmount();
                                @endphp
                                <tr x-show="tab === 'all' || tab === '{{ $order->status }}'" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <a href="{{ route('orders.show', $order) }}" class="text-primary hover:underline">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $order->customer->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $order->customer->phone }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if ($order->preorderPeriod)
                                            <span class="px-2 py-1 text-xs bg-{{ $order->preorderPeriod->status_color }}-100 text-{{ $order->preorderPeriod->status_color }}-800 rounded">
                                                {{ $order->preorderPeriod->name }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ $itemQty }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                        @if ($order->paid_amount)
                                            <div class="text-green-600 font-medium">Rp {{ number_format($order->paid_amount, 0, ',', '.') }}</div>
                                            @if ($order->dp_paid_at)
                                                <div class="text-xs text-gray-500">{{ $order->dp_paid_at->format('d/m/Y') }}</div>
                                            @endif
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">Rp {{ number_format($remaining, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if ($order->status === 'waiting_dp' && $order->dp_payment_deadline)
                                            <div>{{ $order->dp_payment_deadline->format('d/m/Y') }}</div>
                                            @if ($order->dp_payment_deadline->isPast())
                                                <span class="text-xs text-red-600 font-medium">Expired</span>
                                            @endif
                                        @elseif (in_array($order->status, ['product_ready', 'waiting_payment']) && $order->final_payment_deadline)
                                            <div>{{ $order->final_payment_deadline->format('d/m/Y') }}</div>
                                            @if ($order->final_payment_deadline->isPast())
                                                <span class="text-xs text-red-600 font-medium">Expired</span>
                                            @endif
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $order->getStatusColor() }}-100 text-{{ $order->getStatusColor() }}-800">
                                            {{ \App\Models\Order::getStatuses()[$order->status] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('orders.show', $order) }}" class="text-blue hover:text-blue-light" title="Lihat Detail">
                                                <x-heroicon name="eye" class="w-4 h-4" />
                                            </a>

                                            @if ($order->status === 'waiting_dp' || $order->status === 'product_ready' || $order->status === 'waiting_payment')
                                                <button
                                                    onclick="openWhatsApp({{ $order->id }}, '{{ $order->status === 'waiting_dp' ? 'dp_reminder' : 'final_reminder' }}')"
                                                    class="text-green-600 hover:text-green-700"
                                                    title="Kirim WhatsApp">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-6 py-12 text-center text-gray-500 text-sm">
                                        Belum ada pesanan preorder untuk produk ini
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            </div>
        </div>
    </div>

    <script>
        function openWhatsApp(orderId, type) {
            fetch(`/preorders/${orderId}/whatsapp/${type}`)
                .then(response => response.json())
                .then(data => {
                    window.open(data.url, '_blank');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal membuka WhatsApp');
                });
        }
    </script>
</x-app-layout>
