<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Location;
use App\Models\InventoryBalance;
use App\Domain\Inventory\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransferController extends Controller
{
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

        // Get balances with stock only
        $balanceByLocation = InventoryBalance::with('product', 'productVariant', 'location.warehouse')
            ->where('qty_on_hand', '>', 0)
            ->get()
            ->groupBy('location_id');

        return view('warehouse.transfer.create', compact('products', 'locations', 'balanceByLocation'));
    }

    public function store(Request $request, InventoryService $inventory)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'from_location_id' => 'required|different:to_location_id|exists:locations,id',
            'to_location_id' => 'required|exists:locations,id',
            'qty' => 'required|numeric|min:0.0001',
            'notes' => 'nullable|string',
        ]);

        $inventory->transfer(
            $validated['product_id'],
            $validated['from_location_id'],
            $validated['to_location_id'],
            $validated['qty'],
            [
                'product_variant_id' => $validated['product_variant_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'reference_type' => 'manual',
                'reference_id' => null,
            ]
        );

        return redirect()->route('warehouse.transfer.create')->with('success', 'Transfer stok berhasil.');
    }
}
