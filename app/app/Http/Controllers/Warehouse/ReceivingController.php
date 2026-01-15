<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Location;
use App\Models\StockMovement;
use App\Domain\Inventory\Events\PurchaseReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReceivingController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::query()->with(['supplier', 'items.product', 'items.productVariant']);
        if ($search = $request->get('q')) {
            $query->where('purchase_number', 'like', "%{$search}%")
                ->orWhereHas('supplier', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $allowedSorts = ['purchase_number', 'supplier', 'status', 'created_at'];
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        if ($sort === 'supplier') {
            $query->leftJoin('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
                ->select('purchases.*')
                ->orderBy('suppliers.name', $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        $purchases = $query->paginate(10)->withQueryString();
        $locations = Location::where('is_active', true)->with('warehouse')->get();

        // Hitung qty yang sudah diterima per purchase & produk & variant
        $receivedMap = StockMovement::query()
            ->where('reference_type', 'purchase')
            ->where('movement_type', 'receive')
            ->select('reference_id', 'product_id', 'product_variant_id', DB::raw('SUM(qty) as qty'))
            ->groupBy('reference_id', 'product_id', 'product_variant_id')
            ->get()
            ->groupBy('reference_id')
            ->map(fn($rows) => $rows->keyBy(fn($r) => $r->product_id . '_' . ($r->product_variant_id ?? 'null'))->map->qty);

        $purchaseIds = $purchases->pluck('id');
        $histories = StockMovement::with(['product', 'productVariant', 'toLocation.warehouse', 'user'])
            ->whereIn('reference_id', $purchaseIds)
            ->where('reference_type', 'purchase')
            ->where('movement_type', 'receive')
            ->orderByDesc('movement_date')
            ->get()
            ->groupBy('reference_id');

        return view('warehouse.receiving.index', compact('purchases', 'locations', 'receivedMap', 'histories'));
    }

    public function receive(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.qty' => 'required|numeric|min:0',
            'items.*.location_id' => 'required|exists:locations,id',
        ]);

        $items = collect($validated['items'])->filter(fn($i) => $i['qty'] > 0);
        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['items' => 'Tidak ada qty yang diterima.']);
        }

        // Group by product_id + variant_id for validation
        $orderedMap = $purchase->items->groupBy(fn($i) => $i->product_id . '_' . ($i->product_variant_id ?? 'null'))->map->sum('quantity');
        $receivedMap = StockMovement::query()
            ->where('reference_type', 'purchase')
            ->where('reference_id', $purchase->id)
            ->where('movement_type', 'receive')
            ->select('product_id', 'product_variant_id', DB::raw('SUM(qty) as qty'))
            ->groupBy('product_id', 'product_variant_id')
            ->get()
            ->keyBy(fn($r) => $r->product_id . '_' . ($r->product_variant_id ?? 'null'))
            ->map->qty;

        foreach ($items as $item) {
            $key = $item['product_id'] . '_' . ($item['product_variant_id'] ?? 'null');
            $orderedQty = $orderedMap[$key] ?? 0;
            if ($orderedQty <= 0) {
                throw ValidationException::withMessages(['items' => 'Produk/variant tidak ada di purchase.']);
            }
            $alreadyReceived = $receivedMap[$key] ?? 0;
            $remaining = $orderedQty - $alreadyReceived;
            if ($item['qty'] > $remaining) {
                throw ValidationException::withMessages(['items' => 'Qty diterima melebihi qty purchase untuk salah satu item.']);
            }
        }

        DB::transaction(function () use ($items, $purchase) {
            event(new PurchaseReceived($purchase->id, $items->map(fn($i) => [
                'product_id' => $i['product_id'],
                'product_variant_id' => $i['product_variant_id'] ?? null,
                'location_id' => $i['location_id'],
                'qty' => $i['qty'],
                'movement_date' => now(),
            ])->toArray()));

            // Update status hanya jika semua item sudah diterima
            $orderedMap = $purchase->items->groupBy(fn($i) => $i->product_id . '_' . ($i->product_variant_id ?? 'null'))->map->sum('quantity');
            $receivedMap = StockMovement::query()
                ->where('reference_type', 'purchase')
                ->where('reference_id', $purchase->id)
                ->where('movement_type', 'receive')
                ->select('product_id', 'product_variant_id', DB::raw('SUM(qty) as qty'))
                ->groupBy('product_id', 'product_variant_id')
                ->get()
                ->keyBy(fn($r) => $r->product_id . '_' . ($r->product_variant_id ?? 'null'))
                ->map->qty;

            $allReceived = $orderedMap->every(function ($qty, $key) use ($receivedMap) {
                return ($receivedMap[$key] ?? 0) >= $qty;
            });

            $purchase->update(['status' => $allReceived ? 'received' : 'ordered']);
        });

        return redirect()->route('warehouse.receiving.index')->with('success', 'Receiving berhasil disimpan.');
    }
}
