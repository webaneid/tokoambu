<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Location;
use App\Models\InventoryBalance;
use App\Domain\Inventory\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StockAdjustmentController extends Controller
{
    private array $reasons = [
        'rusak',
        'hilang',
        'gift',
        'sample',
        'expired',
        'return_to_supplier',
        'stock_opname',
        'lainnya',
    ];

    public function create()
    {
        // Get only products that have stock (qty_on_hand > 0)
        $productsWithStock = InventoryBalance::where('qty_on_hand', '>', 0)
            ->select('product_id')
            ->distinct()
            ->pluck('product_id');

        $products = Product::where('is_active', true)
            ->whereIn('id', $productsWithStock)
            ->get();

        $locations = Location::where('is_active', true)->with('warehouse')->get();

        // Get balances with stock only (for dynamic location filtering)
        $balanceByLocation = InventoryBalance::with('product', 'productVariant', 'location.warehouse')
            ->where('qty_on_hand', '>', 0)
            ->get()
            ->groupBy('location_id');

        return view('warehouse.adjustments.create', [
            'products' => $products,
            'locations' => $locations,
            'balanceByLocation' => $balanceByLocation,
            'reasons' => $this->reasons,
        ]);
    }

    public function store(Request $request, InventoryService $inventory)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'location_id' => 'required|exists:locations,id',
            'qty' => 'required|numeric|min:0.0001',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        if (!in_array($validated['reason'], $this->reasons)) {
            throw ValidationException::withMessages(['reason' => 'Reason tidak valid.']);
        }

        if ($validated['reason'] === 'lainnya' && empty($validated['notes'])) {
            throw ValidationException::withMessages(['notes' => 'Catatan wajib diisi untuk reason lainnya.']);
        }

        // adjustment stock out (negative)
        $inventory->adjust(
            $validated['product_id'],
            $validated['location_id'],
            -1 * $validated['qty'],
            $validated['reason'],
            [
                'product_variant_id' => $validated['product_variant_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'reference_type' => 'manual',
                'reference_id' => null,
            ]
        );

        return redirect()->route('warehouse.adjustments.create')->with('success', 'Pengeluaran stok berhasil dicatat.');
    }
}
