<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Promo</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('promotions.edit', $promotion) }}" class="text-sm text-gray-600 hover:text-primary">Edit</a>
                <a href="{{ route('promotions.index') }}" class="text-sm text-gray-600 hover:text-primary">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Promo</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                    <div>
                        <div class="text-xs text-gray-500">Nama</div>
                        <div class="font-medium">{{ $promotion->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Tipe</div>
                        <div class="font-medium">{{ str_replace('_', ' ', ucfirst($promotion->type)) }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Status</div>
                        <div class="font-medium">{{ ucfirst($promotion->status) }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Prioritas</div>
                        <div class="font-medium">{{ $promotion->priority }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Periode</div>
                        <div class="font-medium">
                            {{ $promotion->start_at?->format('d M Y H:i') ?? '-' }} - {{ $promotion->end_at?->format('d M Y H:i') ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Stackable</div>
                        <div class="font-medium">{{ $promotion->stackable ? 'Ya' : 'Tidak' }}</div>
                    </div>
                    @if($promotion->description)
                        <div class="md:col-span-2">
                            <div class="text-xs text-gray-500">Deskripsi</div>
                            <div class="font-medium">{{ $promotion->description }}</div>
                        </div>
                    @endif
                </div>
            </div>

            @if($promotion->coupon)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Detail Coupon</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                        <div>
                            <div class="text-xs text-gray-500">Kode</div>
                            <div class="font-medium">{{ $promotion->coupon->code }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Limit Global</div>
                            <div class="font-medium">{{ $promotion->coupon->global_limit ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Limit per User</div>
                            <div class="font-medium">{{ $promotion->coupon->per_user_limit ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Minimal Order</div>
                            <div class="font-medium">{{ $promotion->coupon->min_order_amount ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Pembelian Pertama</div>
                            <div class="font-medium">{{ $promotion->coupon->first_purchase_only ? 'Ya' : 'Tidak' }}</div>
                        </div>
                    </div>
                </div>
            @endif

            @if($promotion->bundle)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Detail Bundle</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                        <div>
                            <div class="text-xs text-gray-500">Pricing Mode</div>
                            <div class="font-medium">{{ $promotion->bundle->pricing_mode }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Harga Bundle</div>
                            <div class="font-medium">{{ $promotion->bundle->bundle_price ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Nilai Diskon</div>
                            <div class="font-medium">{{ $promotion->bundle->discount_value ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Harus Lebih Murah</div>
                            <div class="font-medium">{{ $promotion->bundle->must_be_cheaper ? 'Ya' : 'Tidak' }}</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($promotion->bundle->items as $item)
                                @php
                                    $variant = $item->productVariant;
                                    $attrs = $variant ? implode(' / ', array_values($variant->variant_attributes ?? [])) : null;
                                    $bundleLabel = $variant
                                        ? trim(($variant->product?->name ?? $item->product?->name ?? $item->product_id) . ($attrs ? ' — ' . $attrs : ''))
                                        : ($item->product?->name ?? $item->product_id);
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $bundleLabel }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-500">{{ $item->qty }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-6 py-6 text-center text-sm text-gray-500">Belum ada item bundle.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            @if($promotion->benefits->isNotEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Benefit</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-700">
                        @php $benefit = $promotion->benefits->first(); @endphp
                        <div>
                            <div class="text-xs text-gray-500">Tipe</div>
                            <div class="font-medium">{{ $benefit?->benefit_type ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Nilai</div>
                            <div class="font-medium">{{ $benefit?->value ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Apply Scope</div>
                            <div class="font-medium">{{ $benefit?->apply_scope ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Max Diskon</div>
                            <div class="font-medium">{{ $benefit?->max_discount ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scope</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($promotion->targets as $target)
                            @php
                                $rawTargetId = $target->target_id;
                                $resolvedTargetId = $rawTargetId;
                                $decodedTarget = null;
                                if (is_string($rawTargetId)) {
                                    $trimmed = trim($rawTargetId);
                                    if ($trimmed !== '' && $trimmed[0] === '{') {
                                        $decodedTarget = json_decode($trimmed, true);
                                        if (is_array($decodedTarget) && isset($decodedTarget['id'])) {
                                            $resolvedTargetId = $decodedTarget['id'];
                                        }
                                    }
                                }

                                $product = $target->target_type === 'product'
                                    ? $targetProducts->get($resolvedTargetId)
                                    : null;
                                $variant = $target->target_type === 'variant'
                                    ? $targetVariants->get($resolvedTargetId)
                                    : null;
                                $fallbackLabel = $decodedTarget['name'] ?? null;
                                $label = $variant
                                    ? trim(($variant->product?->name ?? 'Produk') . ' — ' . ($variant->display_name ?: ('Variasi #' . $resolvedTargetId)))
                                    : ($product?->name ?? $fallbackLabel ?? ('Produk #' . $rawTargetId));
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $label }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $target->target_type }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-6 py-6 text-center text-sm text-gray-500">Belum ada target.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
