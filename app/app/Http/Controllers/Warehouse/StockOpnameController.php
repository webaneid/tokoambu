<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\InventoryBalance;
use App\Domain\Inventory\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StockOpnameController extends Controller
{
    public function index()
    {
        $locations = Location::where('is_active', true)->with('warehouse')->get();
        return view('warehouse.opname.index', compact('locations'));
    }

    public function show(Request $request)
    {
        $locationId = $request->get('location_id');
        $locations = Location::where('is_active', true)->with('warehouse')->get();

        $balances = collect();
        if ($locationId) {
            $allowedSorts = ['product', 'qty_on_hand'];
            $sort = $request->query('sort', 'product');
            $direction = $request->query('direction', 'asc');
            if (!in_array($sort, $allowedSorts, true)) {
                $sort = 'product';
            }
            if (!in_array($direction, ['asc', 'desc'], true)) {
                $direction = 'asc';
            }

            $query = InventoryBalance::with('product')
                ->where('location_id', $locationId);

            if ($sort === 'product') {
                $query->leftJoin('products', 'products.id', '=', 'inventory_balances.product_id')
                    ->select('inventory_balances.*')
                    ->orderBy('products.name', $direction);
            } else {
                $query->orderBy($sort, $direction);
            }

            $balances = $query->get();
        }

        return view('warehouse.opname.index', compact('locations', 'balances', 'locationId'));
    }

    public function store(Request $request, InventoryService $inventory)
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.system_qty' => 'required|numeric',
            'items.*.physical_qty' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        foreach ($validated['items'] as $item) {
            $inventory->opnameAdjustment(
                $item['product_id'],
                $validated['location_id'],
                $item['system_qty'],
                $item['physical_qty'],
                [
                    'notes' => $item['notes'] ?? null,
                    'reference_type' => 'stock_opname',
                    'reference_id' => $validated['location_id'],
                ]
            );
        }

        return redirect()->route('warehouse.opname.index', ['location_id' => $validated['location_id']])->with('success', 'Stock opname disimpan.');
    }
}
