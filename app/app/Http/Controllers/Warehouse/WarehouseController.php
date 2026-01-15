<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\Location;
use App\Models\InventoryBalance;
use App\Models\InventoryAnalytics;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = Warehouse::query();
        $search = trim((string) $request->query('q', ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $warehouses = $query->latest()->paginate(10)->withQueryString();
        return view('warehouse.warehouses.index', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:warehouses,code',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'location_template' => 'nullable|array',
            'location_template.*' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        // Filter empty values from location_template
        if (isset($validated['location_template'])) {
            $validated['location_template'] = array_values(array_filter($validated['location_template'], fn($v) => !empty($v)));
            if (empty($validated['location_template'])) {
                $validated['location_template'] = null;
            }
        }

        $warehouse = Warehouse::create($validated);

        // Buat lokasi default HANYA jika tidak ada location_template
        if (empty($warehouse->location_template)) {
            Location::firstOrCreate(
                ['warehouse_id' => $warehouse->id, 'code' => 'MAIN'],
                ['description' => 'Default location', 'is_active' => true]
            );
        }

        return redirect()->route('warehouse.warehouses.index')->with('success', 'Gudang berhasil ditambahkan.');
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:warehouses,code,' . $warehouse->id,
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'location_template' => 'nullable|array',
            'location_template.*' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        // Filter empty values from location_template
        if (isset($validated['location_template'])) {
            $validated['location_template'] = array_values(array_filter($validated['location_template'], fn($v) => !empty($v)));
            if (empty($validated['location_template'])) {
                $validated['location_template'] = null;
            }
        }

        $warehouse->update($validated);

        return redirect()->route('warehouse.warehouses.index')->with('success', 'Gudang berhasil diperbarui.');
    }

    public function destroy(Warehouse $warehouse)
    {
        $locationIds = $warehouse->locations()->pluck('id');
        $hasBalances = InventoryBalance::whereIn('location_id', $locationIds)->exists();
        $hasAnalytics = InventoryAnalytics::whereIn('location_id', $locationIds)->exists();
        $hasMovements = StockMovement::whereIn('from_location_id', $locationIds)
            ->orWhereIn('to_location_id', $locationIds)
            ->exists();

        if ($hasBalances || $hasAnalytics || $hasMovements) {
            return redirect()->route('warehouse.warehouses.index')
                ->with('error', 'Gudang tidak dapat dihapus karena masih memiliki aktivitas stok.');
        }

        $warehouse->locations()->delete();
        $warehouse->delete();

        return redirect()->route('warehouse.warehouses.index')->with('success', 'Gudang berhasil dihapus.');
    }
}
