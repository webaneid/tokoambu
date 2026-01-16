<?php

namespace App\Http\Controllers;

use App\Domain\Inventory\Services\InventoryService;
use App\Models\Customer;
use App\Models\InventoryBalance;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Services\CouponService;
use App\Services\FlashSaleService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private function applySorting(Request $request, $query)
    {
        $allowedSorts = [
            'order_number',
            'customer',
            'type',
            'total_amount',
            'status',
            'created_at',
        ];
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        if ($sort === 'customer') {
            $query->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
                ->select('orders.*')
                ->orderBy('customers.name', $direction);
        } else {
            $query->orderBy('orders.'.$sort, $direction);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = Order::query()->with(['customer', 'items.product', 'shipment']);

        $this->applySorting($request, $query);

        $orders = $query->paginate(15)->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function packing(Request $request)
    {
        $query = Order::query()
            ->with(['customer', 'items.product', 'shipment'])
            ->where('status', 'paid');

        if ($search = $request->query('search')) {
            $query->where(function ($query) use ($search) {
                $query->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $this->applySorting($request, $query);

        $orders = $query->paginate(15)->withQueryString();

        return view('orders.packing', compact('orders'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)
            ->with(['province', 'city', 'district'])
            ->get();

        // Get products that have stock OR allow preorder
        $productsWithStock = InventoryBalance::where('qty_on_hand', '>', 0)
            ->select('product_id')
            ->distinct()
            ->pluck('product_id');

        $products = Product::where('is_active', true)
            ->where(function ($query) use ($productsWithStock) {
                $query->whereIn('id', $productsWithStock)
                    ->orWhere('allow_preorder', true);
            })
            ->get();

        $origin = [
            'province_id' => Setting::get('origin_province_id'),
            'province_name' => Setting::get('origin_province_name'),
            'city_id' => Setting::get('origin_city_id'),
            'city_name' => Setting::get('origin_city_name'),
            'district_id' => Setting::get('origin_district_id'),
            'district_name' => Setting::get('origin_district_name'),
            'postal_code' => Setting::get('origin_postal_code'),
        ];
        $activeCouriers = json_decode(Setting::get('active_couriers', '[]'), true) ?: [];
        $couriers = config('rajaongkir.couriers', []);

        $flashSalePromos = Promotion::query()
            ->where('type', 'flash_sale')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            })
            ->with(['targets', 'benefits'])
            ->orderByDesc('priority')
            ->get()
            ->map(function ($promo) {
                return [
                    'id' => $promo->id,
                    'name' => $promo->name,
                    'rules' => $promo->rules ?? [],
                    'targets' => $promo->targets->map(fn ($target) => [
                        'type' => $target->target_type,
                        'id' => (int) $target->target_id,
                        'include' => (bool) $target->include,
                    ])->values(),
                    'benefits' => $promo->benefits->map(fn ($benefit) => [
                        'type' => $benefit->benefit_type,
                        'value' => (float) $benefit->value,
                        'max_discount' => $benefit->max_discount !== null ? (float) $benefit->max_discount : null,
                        'apply_scope' => $benefit->apply_scope,
                    ])->values(),
                ];
            });

        return view('orders.create', compact('customers', 'products', 'origin', 'activeCouriers', 'couriers', 'flashSalePromos'));
    }

    public function store(Request $request, FlashSaleService $flashSaleService, CouponService $couponService)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|in:order,preorder',
            'status' => 'nullable|in:draft,waiting_payment,dp_paid,paid,packed,shipped,done,cancelled',
            'notes' => 'nullable|string',
            'coupon_code' => 'nullable|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.is_preorder' => 'nullable|boolean',
            'items.*.preorder_eta_date' => 'nullable|date',
            'shipping_cost' => 'nullable|numeric|min:0',
            'shipping_courier' => 'nullable|string',
            'shipping_service' => 'nullable|string',
            'shipping_etd' => 'nullable|string',
            'shipping_province_id' => 'nullable|integer|exists:provinces,id',
            'shipping_city_id' => 'nullable|integer|exists:cities,id',
            'shipping_district_id' => 'nullable|integer|exists:districts,id',
            'shipping_postal_code' => 'nullable|string',
            'shipping_address' => 'nullable|string',
        ]);

        $productIds = collect($validated['items'])->pluck('product_id')->unique()->values();
        $products = Product::whereIn('id', $productIds)
            ->with('inventoryBalances')
            ->get()
            ->keyBy('id');

        $variantIds = collect($validated['items'])
            ->pluck('product_variant_id')
            ->filter()
            ->unique()
            ->values();
        $variants = $variantIds->isNotEmpty()
            ? ProductVariant::whereIn('id', $variantIds)->with('inventoryBalances')->get()->keyBy('id')
            : collect();

        $items = $flashSaleService->applyToItems($validated['items'], $products, $variants);
        $flashSalePromotionIds = collect($items)
            ->pluck('flash_sale_promotion_id')
            ->filter()
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $totalAmount = 0;
        $hasPreorder = false;
        $preparedItems = [];
        $couponCode = strtoupper(trim((string) ($validated['coupon_code'] ?? '')));
        $couponDiscount = 0.0;
        $couponPromotionId = null;
        $customer = Customer::findOrFail($validated['customer_id']);
        foreach ($items as $item) {
            $product = $products->get($item['product_id']);

            if (!$product) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['items' => 'Produk tidak ditemukan.']);
            }

            // Auto-detect: apakah ini preorder atau order biasa
            $availableStock = $product->qty_on_hand ?? 0;
            $requestedQty = $item['quantity'];

            // Jika stock cukup → order biasa
            // Jika stock tidak cukup DAN allow_preorder → preorder
            // Jika stock tidak cukup DAN tidak allow_preorder → reject
            $isPreorder = false;
            if ($availableStock < $requestedQty) {
                if ($product->allow_preorder) {
                    $isPreorder = true;
                    $hasPreorder = true;
                } else {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->withErrors(['items' => "Stok {$product->name} tidak mencukupi dan tidak bisa di-preorder."]);
                }
            }

            $etaDate = null;
            if ($isPreorder) {
                $etaDate = $item['preorder_eta_date'] ?? $product->preorder_eta_date;
                if (!$etaDate) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->withErrors(['items' => "Tanggal estimasi preorder untuk {$product->name} wajib diisi."]);
                }
            }

            $preparedItems[] = array_merge($item, [
                'is_preorder' => $isPreorder,
                'preorder_eta_date' => $etaDate,
            ]);
            $totalAmount += $item['quantity'] * $item['unit_price'];
        }

        $shippingCost = (float) ($validated['shipping_cost'] ?? 0);
        if ($couponCode !== '') {
            $couponResult = $couponService->applyCoupon(
                $couponCode,
                collect($items),
                $totalAmount,
                $customer,
                $flashSalePromotionIds,
                $shippingCost
            );

            if (! $couponResult['valid']) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['coupon_code' => $couponResult['message'] ?? 'Kupon tidak valid.']);
            }

            $couponDiscount = (float) ($couponResult['discount'] ?? 0);
            $couponPromotionId = $couponResult['promotion']?->id;

            if ($couponDiscount > 0 && $this->shouldApplyDiscountToItems($couponResult)) {
                $preparedItems = $this->applyCouponDiscountToItems($preparedItems, $couponDiscount);
            }
        }

        $totalAmount = 0;
        $order = Order::create([
            'order_number' => 'ORD-'.date('YmdHis'),
            'customer_id' => $validated['customer_id'],
            'type' => $validated['type'],
            'status' => $validated['status'] ?? 'draft',
            'notes' => $validated['notes'] ?? null,
            'total_amount' => 0,
            'shipping_cost' => $validated['shipping_cost'] ?? 0,
            'shipping_courier' => $validated['shipping_courier'] ?? null,
            'shipping_service' => $validated['shipping_service'] ?? null,
            'shipping_etd' => $validated['shipping_etd'] ?? null,
            'shipping_province_id' => $validated['shipping_province_id'] ?? null,
            'shipping_city_id' => $validated['shipping_city_id'] ?? null,
            'shipping_district_id' => $validated['shipping_district_id'] ?? null,
            'shipping_postal_code' => $validated['shipping_postal_code'] ?? null,
            'shipping_address' => $validated['shipping_address'] ?? null,
        ]);
        foreach ($preparedItems as $item) {
            $subtotal = $item['quantity'] * $item['unit_price'];
            $order->items()->create([
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['product_variant_id'] ?? null,
                'quantity' => $item['quantity'],
                'price' => $item['original_price'] ?? null,
                'unit_price' => $item['unit_price'],
                'subtotal' => $subtotal,
                'is_preorder' => $item['is_preorder'] ?? false,
                'preorder_eta_date' => $item['preorder_eta_date'] ?? null,
                'preorder_allocated_qty' => 0,
            ]);
            $totalAmount += $subtotal;
        }

        $finalAmount = max(0, $totalAmount + $shippingCost - $couponDiscount);
        $updateData = [
            'total_amount' => $finalAmount,
            'type' => $hasPreorder ? 'preorder' : 'order',
            'coupon_code' => $couponCode !== '' ? $couponCode : null,
            'coupon_promotion_id' => $couponPromotionId,
            'coupon_discount_amount' => $couponDiscount,
        ];

        // If preorder, calculate DP and set deadlines
        if ($hasPreorder) {
            $dpRequired = Setting::isPreorderDpRequired();
            $dpPercentage = Setting::getPreorderDpPercentage();

            if ($dpRequired) {
                $dpAmount = ($finalAmount * $dpPercentage) / 100;
                $dpDeadlineDays = Setting::getPreorderDpDeadlineDays();

                $updateData['dp_amount'] = $dpAmount;
                $updateData['dp_payment_deadline'] = now()->addDays($dpDeadlineDays);
                $updateData['status'] = 'waiting_dp';
            } else {
                // DP not required, go straight to waiting_payment
                $updateData['status'] = 'waiting_payment';
            }
        }

        $order->update($updateData);

        PromotionUsage::where('order_id', $order->id)->delete();
        if ($couponPromotionId && $couponDiscount > 0) {
            PromotionUsage::create([
                'promotion_id' => $couponPromotionId,
                'order_id' => $order->id,
                'user_id' => $customer->id,
                'coupon_code' => $couponCode !== '' ? $couponCode : null,
                'discount_amount' => $couponDiscount,
                'applied_at' => now(),
            ]);
        }

        return redirect()->route('orders.show', $order)->with('success', 'Order berhasil dibuat');
    }

    public function show(Order $order)
    {
        $order->load('customer.bankAccounts', 'items.product', 'items.productVariant', 'payments', 'shipment', 'refundFromAccount');

        // Load company bank accounts (for refund source)
        $companyBankAccounts = \App\Models\BankAccount::whereNotNull('user_id')->get();

        // Group balances by order item (product_id + product_variant_id combination)
        $balancesByItem = [];
        foreach ($order->items as $item) {
            $query = InventoryBalance::with('location.warehouse')
                ->where('product_id', $item->product_id);

            if ($item->product_variant_id) {
                $query->where('product_variant_id', $item->product_variant_id);
            } else {
                $query->whereNull('product_variant_id');
            }

            $balancesByItem[$item->id] = $query->get();
        }

        $shipMovements = StockMovement::with(['fromLocation.warehouse', 'user'])
            ->where('movement_type', 'ship')
            ->where('reference_type', 'order')
            ->where('reference_id', $order->id)
            ->orderByDesc('movement_date')
            ->get();

        $pickedQtyByItem = [];
        $latestMovementByItem = [];

        foreach ($order->items as $item) {
            $itemMovements = $shipMovements->filter(function ($movement) use ($item) {
                return $movement->product_id == $item->product_id &&
                       $movement->product_variant_id == $item->product_variant_id;
            });

            $pickedQtyByItem[$item->id] = $itemMovements->sum('qty');
            $latestMovementByItem[$item->id] = $itemMovements->first();
        }

        $canPack = $order->items->every(function ($item) use ($pickedQtyByItem) {
            return ($pickedQtyByItem[$item->id] ?? 0) >= $item->quantity;
        });

        return view('orders.show', compact(
            'order',
            'balancesByItem',
            'pickedQtyByItem',
            'latestMovementByItem',
            'canPack',
            'companyBankAccounts'
        ));
    }

    public function edit(Order $order)
    {
        if (in_array($order->status, ['shipped', 'done'], true)) {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', 'Order yang sudah dikirim atau selesai tidak bisa diedit.');
        }

        $customers = Customer::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        $flashSalePromos = Promotion::query()
            ->where('type', 'flash_sale')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            })
            ->with(['targets', 'benefits'])
            ->orderByDesc('priority')
            ->get()
            ->map(function ($promo) {
                return [
                    'id' => $promo->id,
                    'name' => $promo->name,
                    'rules' => $promo->rules ?? [],
                    'targets' => $promo->targets->map(fn ($target) => [
                        'type' => $target->target_type,
                        'id' => (int) $target->target_id,
                        'include' => (bool) $target->include,
                    ])->values(),
                    'benefits' => $promo->benefits->map(fn ($benefit) => [
                        'type' => $benefit->benefit_type,
                        'value' => (float) $benefit->value,
                        'max_discount' => $benefit->max_discount !== null ? (float) $benefit->max_discount : null,
                        'apply_scope' => $benefit->apply_scope,
                    ])->values(),
                ];
            });

        return view('orders.edit', compact('order', 'customers', 'products', 'flashSalePromos'));
    }

    public function label(Order $order)
    {
        $order->load('customer', 'items.product', 'shippingDistrict', 'shippingCity', 'shippingProvince');

        $pdf = Pdf::loadView('orders.label-pdf', compact('order'))
            ->setPaper('a5', 'portrait')
            ->setOption('margin-top', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0);

        return $pdf->download('Label-'.$order->order_number.'.pdf');
    }

    public function printLabel(Order $order)
    {
        $order->load(
            'customer',
            'items.product',
            'shippingDistrict',
            'shippingCity',
            'shippingProvince'
        );

        return view('orders.label-print', compact('order'));
    }

    public function pickItem(Request $request, Order $order, \App\Models\OrderItem $item, InventoryService $inventory)
    {
        if ($item->order_id !== $order->id) {
            abort(404);
        }

        if ($order->status !== 'paid') {
            return back()->with('error', 'Order harus berstatus Paid sebelum stok dikurangi.');
        }

        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
        ]);

        // Calculate picked qty for this specific item (product_id + product_variant_id)
        $query = StockMovement::where('movement_type', 'ship')
            ->where('reference_type', 'order')
            ->where('reference_id', $order->id)
            ->where('product_id', $item->product_id);

        if ($item->product_variant_id) {
            $query->where('product_variant_id', $item->product_variant_id);
        } else {
            $query->whereNull('product_variant_id');
        }

        $pickedQty = $query->sum('qty');

        $remaining = $item->quantity - $pickedQty;

        if ($remaining <= 0) {
            return back()->with('info', 'Stok untuk produk ini sudah dikurangi.');
        }

        $inventory->ship(
            $item->product_id,
            (int) $validated['location_id'],
            $remaining,
            [
                'product_variant_id' => $item->product_variant_id,
                'reference_type' => 'order',
                'reference_id' => $order->id,
                'movement_date' => now(),
                'user_id' => auth()->id(),
            ]
        );

        return back()->with('success', 'Stok berhasil dikurangi.');
    }

    public function bulkPrint(Request $request)
    {
        $ids = $request->query('ids');

        if (empty($ids)) {
            return redirect()->route('orders.packing')->with('error', 'Tidak ada order yang dipilih');
        }

        $orderIds = explode(',', $ids);

        $orders = Order::with([
            'customer',
            'items.product',
            'shippingDistrict',
            'shippingCity',
            'shippingProvince',
        ])->whereIn('id', $orderIds)->get();

        if ($orders->isEmpty()) {
            return redirect()->route('orders.packing')->with('error', 'Order tidak ditemukan');
        }

        return view('orders.bulk-print', compact('orders'));
    }

    public function bulkMarkPacked(Request $request)
    {
        $orderIds = json_decode($request->input('order_ids'), true);

        if (empty($orderIds)) {
            return redirect()->route('orders.packing')->with('error', 'Tidak ada order yang dipilih');
        }

        $orders = Order::whereIn('id', $orderIds)
            ->where('status', 'paid')
            ->with('items')
            ->get();

        if ($orders->isEmpty()) {
            return redirect()->route('orders.packing')->with('error', 'Tidak ada order yang valid untuk ditandai sebagai packed');
        }

        $successCount = 0;

        foreach ($orders as $order) {
            // Group picked qty by item (product_id + product_variant_id)
            $pickedByItem = StockMovement::where('movement_type', 'ship')
                ->where('reference_type', 'order')
                ->where('reference_id', $order->id)
                ->get()
                ->groupBy(function ($movement) {
                    return $movement->product_id.'_'.($movement->product_variant_id ?? 'null');
                })
                ->map(fn ($group) => $group->sum('qty'));

            $canPack = $order->items->every(function ($item) use ($pickedByItem) {
                $key = $item->product_id.'_'.($item->product_variant_id ?? 'null');

                return ($pickedByItem[$key] ?? 0) >= $item->quantity;
            });

            if (! $canPack) {
                continue;
            }

            $order->update(['status' => 'packed']);

            // Create shipment if not exists
            if (! $order->shipment) {
                $recipientAddress = $order->shipping_address
                    ?? $order->customer?->full_address
                    ?? $order->customer?->address;

                $order->shipment()->create([
                    'recipient_name' => $order->customer?->name,
                    'recipient_address' => $recipientAddress,
                    'courier' => $order->shipping_courier,
                    'shipping_cost' => $order->shipping_cost ?? 0,
                    'status' => 'packed',
                    'notes' => $order->notes,
                ]);
            }

            $successCount++;
        }

        return redirect()->route('orders.packing')->with('success', "$successCount order berhasil ditandai sebagai packed");
    }

    public function update(Request $request, Order $order, FlashSaleService $flashSaleService, CouponService $couponService)
    {
        if (in_array($order->status, ['shipped', 'done'], true)) {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', 'Order yang sudah dikirim atau selesai tidak bisa diedit.');
        }

        if (! $request->has('items')) {
            $validated = $request->validate([
                'status' => 'required|in:draft,waiting_payment,dp_paid,paid,packed,shipped,done,cancelled',
                'notes' => 'nullable|string',
            ]);

            if ($validated['status'] === 'packed') {
                $order->load('items');
                // Group picked qty by item (product_id + product_variant_id)
                $pickedByItem = StockMovement::where('movement_type', 'ship')
                    ->where('reference_type', 'order')
                    ->where('reference_id', $order->id)
                    ->get()
                    ->groupBy(function ($movement) {
                        return $movement->product_id.'_'.($movement->product_variant_id ?? 'null');
                    })
                    ->map(fn ($group) => $group->sum('qty'));

                $canPack = $order->items->every(function ($item) use ($pickedByItem) {
                    $key = $item->product_id.'_'.($item->product_variant_id ?? 'null');

                    return ($pickedByItem[$key] ?? 0) >= $item->quantity;
                });

                if (! $canPack) {
                    return back()->with('error', 'Pengurangan stok belum lengkap. Kurangi stok per produk sebelum menandai pesanan sebagai dikemas.');
                }
            }

            $order->update($validated);

            if ($validated['status'] === 'packed' && ! $order->shipment) {
                $recipientAddress = $order->shipping_address
                    ?? $order->customer?->full_address
                    ?? $order->customer?->address;

                $order->shipment()->create([
                    'recipient_name' => $order->customer?->name,
                    'recipient_address' => $recipientAddress,
                    'courier' => $order->shipping_courier,
                    'shipping_cost' => $order->shipping_cost ?? 0,
                    'status' => 'packed',
                    'notes' => $order->notes,
                ]);
            }

            return redirect()->route('orders.show', $order)->with('success', 'Order berhasil diperbarui');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string',
            'coupon_code' => 'nullable|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.is_preorder' => 'nullable|boolean',
            'items.*.preorder_eta_date' => 'nullable|date',
            'shipping_cost' => 'nullable|numeric|min:0',
        ]);

        $productIds = collect($validated['items'])->pluck('product_id')->unique()->values();
        $products = Product::whereIn('id', $productIds)
            ->with('inventoryBalances')
            ->get()
            ->keyBy('id');

        $variantIds = collect($validated['items'])
            ->pluck('product_variant_id')
            ->filter()
            ->unique()
            ->values();
        $variants = $variantIds->isNotEmpty()
            ? ProductVariant::whereIn('id', $variantIds)->with('inventoryBalances')->get()->keyBy('id')
            : collect();

        $items = $flashSaleService->applyToItems($validated['items'], $products, $variants);
        $flashSalePromotionIds = collect($items)
            ->pluck('flash_sale_promotion_id')
            ->filter()
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $totalAmount = 0;
        $hasPreorder = false;
        $preparedItems = [];
        $couponCode = strtoupper(trim((string) ($validated['coupon_code'] ?? '')));
        $couponDiscount = 0.0;
        $couponPromotionId = null;

        foreach ($items as $item) {
            $product = $products->get($item['product_id']);
            $isPreorder = ! empty($item['is_preorder']);
            $etaDate = $item['preorder_eta_date'] ?? $product?->preorder_eta_date;

            if ($isPreorder) {
                $hasPreorder = true;
                if (! $product || ! $product->allow_preorder) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->withErrors(['items' => 'Produk tidak bisa di-preorder.']);
                }
                if (! $etaDate) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->withErrors(['items' => 'Tanggal estimasi preorder wajib diisi.']);
                }
            }

            $preparedItems[] = array_merge($item, [
                'is_preorder' => $isPreorder,
                'preorder_eta_date' => $etaDate,
            ]);
            $totalAmount += $item['quantity'] * $item['unit_price'];
        }

        $shippingCost = (float) ($validated['shipping_cost'] ?? $order->shipping_cost ?? 0);
        if ($couponCode !== '') {
            $couponResult = $couponService->applyCoupon(
                $couponCode,
                collect($items),
                $totalAmount,
                $order->customer,
                $flashSalePromotionIds,
                $shippingCost
            );

            if (! $couponResult['valid']) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['coupon_code' => $couponResult['message'] ?? 'Kupon tidak valid.']);
            }

            $couponDiscount = (float) ($couponResult['discount'] ?? 0);
            $couponPromotionId = $couponResult['promotion']?->id;

            if ($couponDiscount > 0 && $this->shouldApplyDiscountToItems($couponResult)) {
                $preparedItems = $this->applyCouponDiscountToItems($preparedItems, $couponDiscount);
            }
        }

        $totalAmount = 0;
        $order->items()->delete();
        foreach ($preparedItems as $item) {
            $subtotal = $item['quantity'] * $item['unit_price'];
            $order->items()->create([
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['product_variant_id'] ?? null,
                'quantity' => $item['quantity'],
                'price' => $item['original_price'] ?? null,
                'unit_price' => $item['unit_price'],
                'subtotal' => $subtotal,
                'is_preorder' => $item['is_preorder'] ?? false,
                'preorder_eta_date' => $item['preorder_eta_date'] ?? null,
                'preorder_allocated_qty' => 0,
            ]);
            $totalAmount += $subtotal;
        }

        $order->update([
            'notes' => $validated['notes'] ?? null,
            'shipping_cost' => $shippingCost,
            'total_amount' => max(0, $totalAmount + $shippingCost - $couponDiscount),
            'type' => $hasPreorder ? 'preorder' : 'order',
            'status' => 'waiting_payment',
            'coupon_code' => $couponCode !== '' ? $couponCode : null,
            'coupon_promotion_id' => $couponPromotionId,
            'coupon_discount_amount' => $couponDiscount,
        ]);

        PromotionUsage::where('order_id', $order->id)->delete();
        if ($couponPromotionId && $couponDiscount > 0) {
            PromotionUsage::create([
                'promotion_id' => $couponPromotionId,
                'order_id' => $order->id,
                'user_id' => $order->customer?->id,
                'coupon_code' => $couponCode !== '' ? $couponCode : null,
                'discount_amount' => $couponDiscount,
                'applied_at' => now(),
            ]);
        }

        if ($order->shipment) {
            $order->shipment()->delete();
        }

        return redirect()->route('orders.show', $order)->with('success', 'Order berhasil diperbarui');
    }

    private function shouldApplyDiscountToItems(?array $couponResult): bool
    {
        if (! $couponResult || empty($couponResult['promotion'])) {
            return false;
        }

        $benefit = $couponResult['promotion']->benefits->first();
        if (! $benefit) {
            return false;
        }

        if ($benefit->benefit_type === 'free_shipping' || $benefit->apply_scope === 'shipping') {
            return false;
        }

        return true;
    }

    private function applyCouponDiscountToItems(array $items, float $discount): array
    {
        if ($discount <= 0) {
            return $items;
        }

        $total = collect($items)->sum(function ($item) {
            return ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0);
        });

        if ($total <= 0) {
            return $items;
        }

        $remaining = $discount;
        $lastIndex = count($items) - 1;

        foreach ($items as $index => $item) {
            $lineTotal = ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0);
            $lineDiscount = $index === $lastIndex
                ? $remaining
                : round($discount * ($lineTotal / $total), 2);

            $remaining -= $lineDiscount;

            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $unitDiscount = round($lineDiscount / $qty, 2);
            $basePrice = (float) ($item['unit_price'] ?? 0);

            if (empty($item['original_price'])) {
                $item['original_price'] = $basePrice;
            }

            $item['unit_price'] = max(0, $basePrice - $unitDiscount);
            $items[$index] = $item;
        }

        return $items;
    }

    /**
     * Get filtered products based on order type for API
     */
    public function getProductsByType(Request $request)
    {
        $type = $request->query('type', 'order');

        if ($type === 'preorder') {
            // For preorder: only products with stock = 0 AND allow_preorder = true
            $productsWithNoStock = Product::where('allow_preorder', true)
                ->where('is_active', true)
                ->with('inventoryBalances')
                ->get()
                ->filter(function ($product) {
                    $totalStock = $product->inventoryBalances->sum('qty_on_hand');
                    return $totalStock <= 0;
                });

            $products = Product::whereIn('id', $productsWithNoStock->pluck('id'))
                ->where('is_active', true)
                ->get();
        } else {
            // For normal order: only products with stock > 0
            $productsWithStock = InventoryBalance::where('qty_on_hand', '>', 0)
                ->select('product_id')
                ->distinct()
                ->pluck('product_id');

            $products = Product::where('is_active', true)
                ->whereIn('id', $productsWithStock)
                ->get();
        }

        return response()->json([
            'products' => $products,
        ]);
    }

    /**
     * Cancel order and unreserve stock if needed
     */
    public function cancel(Order $order)
    {
        // Prevent canceling already completed orders
        if (in_array($order->status, ['shipped', 'done', 'cancelled'])) {
            return redirect()
                ->back()
                ->with('error', 'Order dengan status ' . $order->status . ' tidak bisa dibatalkan.');
        }

        $inventoryService = new InventoryService();
        $defaultLocationId = 1; // Adjust based on your warehouse setup

        DB::transaction(function () use ($order, $inventoryService, $defaultLocationId) {
            // Check if order has reserved stock (preorder with DP paid)
            $hasReservedStock = $order->isPreorder()
                && in_array($order->status, ['dp_paid', 'product_ready', 'waiting_payment', 'paid']);

            if ($hasReservedStock) {
                // Unreserve stock for each item
                foreach ($order->items as $item) {
                    try {
                        $inventoryService->unreserveStock(
                            $item->product_id,
                            $defaultLocationId,
                            $item->quantity,
                            [
                                'product_variant_id' => $item->product_variant_id,
                                'reference_type' => Order::class,
                                'reference_id' => $order->id,
                                'reason' => 'Order cancelled',
                                'notes' => "Order {$order->order_number} dibatalkan oleh admin",
                            ]
                        );
                    } catch (\Exception $e) {
                        \Log::error("Failed to unreserve stock for cancelled order {$order->order_number}: {$e->getMessage()}");
                    }
                }
            }

            // Update order status to cancelled
            $order->update(['status' => 'cancelled']);
        });

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Order berhasil dibatalkan. Stock yang di-reserve sudah dikembalikan.');
    }

    /**
     * Process refund for cancelled order
     */
    public function refund(Request $request, Order $order)
    {
        if ($order->status !== 'cancelled') {
            return redirect()
                ->back()
                ->with('error', 'Hanya order yang sudah dibatalkan yang bisa di-refund.');
        }

        $validated = $request->validate([
            'refund_amount' => 'required|numeric|min:0',
            'refund_method' => 'required|in:cash,transfer,check,other',
            'refund_notes' => 'nullable|string',
        ]);

        // Validate refund amount doesn't exceed paid amount
        if ($validated['refund_amount'] > $order->paid_amount) {
            return redirect()
                ->back()
                ->withErrors(['refund_amount' => 'Jumlah refund tidak boleh melebihi yang sudah dibayar (Rp ' . number_format($order->paid_amount, 0, ',', '.') . ')']);
        }

        DB::transaction(function () use ($order, $validated) {
            // Create negative ledger entry for refund
            $category = \App\Models\FinancialCategory::firstOrCreate(
                ['name' => 'Refund', 'type' => 'expense'],
                ['is_active' => true]
            );

            \App\Models\LedgerEntry::create([
                'entry_date' => now()->toDateString(),
                'type' => 'expense',
                'category_id' => $category->id,
                'description' => 'Refund order #' . $order->order_number,
                'amount' => $validated['refund_amount'],
                'reference_id' => $order->id,
                'reference_type' => 'order',
                'source_type' => 'refund',
                'source_id' => $order->id,
                'created_by' => auth()->id(),
            ]);

            // Update order with refund info
            $order->update([
                'refund_amount' => ($order->refund_amount ?? 0) + $validated['refund_amount'],
                'refund_method' => $validated['refund_method'],
                'refund_notes' => $validated['refund_notes'],
                'refunded_at' => now(),
            ]);
        });

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Refund sebesar Rp ' . number_format($validated['refund_amount'], 0, ',', '.') . ' berhasil diproses.');
    }

    /**
     * Cancel order with refund in one transaction
     */
    public function cancelWithRefund(Request $request, Order $order)
    {
        // Prevent canceling already completed orders
        if (in_array($order->status, ['shipped', 'done', 'cancelled'])) {
            return redirect()
                ->back()
                ->with('error', 'Order dengan status ' . $order->status . ' tidak bisa dibatalkan.');
        }

        // Validate refund data
        $validated = $request->validate([
            'refund_amount' => 'required|numeric|min:0',
            'refund_method' => 'required|in:cash,transfer,check,other',
            'refund_from_account_id' => 'nullable|exists:bank_accounts,id',
            'refund_notes' => 'nullable|string',
        ]);

        // Validate refund amount doesn't exceed paid amount
        if ($validated['refund_amount'] > $order->paid_amount) {
            return redirect()
                ->back()
                ->withErrors(['refund_amount' => 'Jumlah refund tidak boleh melebihi yang sudah dibayar (Rp ' . number_format($order->paid_amount, 0, ',', '.') . ')']);
        }

        $inventoryService = new InventoryService();
        $defaultLocationId = 1;

        DB::transaction(function () use ($order, $validated, $inventoryService, $defaultLocationId) {
            // 1. Unreserve stock if order has reserved stock
            $hasReservedStock = $order->isPreorder() && in_array($order->status, ['dp_paid', 'product_ready', 'waiting_payment', 'paid']);

            if ($hasReservedStock) {
                foreach ($order->items as $item) {
                    try {
                        $inventoryService->unreserveStock(
                            $item->product_id,
                            $defaultLocationId,
                            $item->quantity,
                            [
                                'product_variant_id' => $item->product_variant_id,
                                'reference_type' => Order::class,
                                'reference_id' => $order->id,
                                'reason' => 'Order cancelled with refund',
                                'notes' => "Order {$order->order_number} dibatalkan dan di-refund oleh admin",
                            ]
                        );
                    } catch (\Exception $e) {
                        \Log::error("Failed to unreserve stock for cancelled order {$order->order_number}: {$e->getMessage()}");
                    }
                }
            }

            // 2. Create ledger entry for refund
            $category = \App\Models\FinancialCategory::firstOrCreate(
                ['name' => 'Refund', 'type' => 'expense'],
                ['is_active' => true]
            );

            \App\Models\LedgerEntry::create([
                'entry_date' => now()->toDateString(),
                'type' => 'expense',
                'category_id' => $category->id,
                'description' => 'Refund order #' . $order->order_number . ' (cancelled)',
                'amount' => $validated['refund_amount'],
                'reference_id' => $order->id,
                'reference_type' => 'order',
                'source_type' => 'refund',
                'source_id' => $order->id,
                'created_by' => auth()->id(),
            ]);

            // 3. Update order status to cancelled + refund info
            $order->update([
                'status' => 'cancelled',
                'refund_amount' => ($order->refund_amount ?? 0) + $validated['refund_amount'],
                'refund_method' => $validated['refund_method'],
                'refund_from_account_id' => $validated['refund_from_account_id'] ?? null,
                'refund_notes' => $validated['refund_notes'],
                'refunded_at' => now(),
            ]);
        });

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Order berhasil dibatalkan dan refund sebesar Rp ' . number_format($validated['refund_amount'], 0, ',', '.') . ' telah diproses. Stock yang di-reserve sudah dikembalikan.');
    }

    public function destroy(Order $order)
    {
        $order->items()->delete();
        $order->delete();

        return redirect()->route('orders.index')->with('success', 'Order berhasil dihapus');
    }

    /**
     * Cancel order and mark for refund processing
     * Order status will be changed to 'cancelled_refund_pending'
     * Admin will process actual refund in /refunds menu
     */
    public function cancelAndRefund(Order $order)
    {
        // Prevent canceling already completed/cancelled orders
        if (in_array($order->status, ['shipped', 'done', 'cancelled', 'cancelled_refund_pending', 'refunded'])) {
            return redirect()
                ->back()
                ->with('error', 'Order dengan status ' . $order->status . ' tidak bisa dibatalkan.');
        }

        // Check if customer has paid
        if ($order->paid_amount <= 0) {
            return redirect()
                ->back()
                ->with('error', 'Order ini belum ada pembayaran, gunakan tombol "Batalkan Order" biasa.');
        }

        $inventoryService = new InventoryService();
        $defaultLocationId = 1;

        DB::transaction(function () use ($order, $inventoryService, $defaultLocationId) {
            // 1. Unreserve stock if order has reserved stock
            $hasReservedStock = $order->isPreorder() && in_array($order->status, ['dp_paid', 'product_ready', 'waiting_payment', 'paid']);

            if ($hasReservedStock) {
                foreach ($order->items as $item) {
                    try {
                        $inventoryService->unreserveStock(
                            $item->product_id,
                            $defaultLocationId,
                            $item->quantity,
                            [
                                'product_variant_id' => $item->product_variant_id,
                                'reference_type' => 'order_cancelled',
                                'reference_id' => $order->id,
                            ]
                        );
                    } catch (\Exception $e) {
                        \Log::warning("Failed to unreserve stock for order {$order->id}, item {$item->id}: " . $e->getMessage());
                    }
                }
            }

            // 2. Update order status to cancelled_refund_pending
            $order->update([
                'status' => 'cancelled_refund_pending',
            ]);
        });

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Order berhasil dibatalkan. Silakan proses refund di menu Refund.');
    }
}
