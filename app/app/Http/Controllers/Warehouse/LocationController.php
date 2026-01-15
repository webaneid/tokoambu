<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Warehouse;
use App\Models\InventoryBalance;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $warehouseId = $request->get('warehouse_id');

        $locationsQuery = Location::with('warehouse');

        if ($warehouseId) {
            $locationsQuery->where('warehouse_id', $warehouseId);
        }

        $locations = $locationsQuery->orderBy('code')->get();

        return response()->json([
            'locations' => $locations,
            'warehouses' => $warehouses,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'location_attributes' => 'required|array',
            'location_attributes.*' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Auto-generate code from location_attributes only (no warehouse code)
        $code = implode('-', array_values($validated['location_attributes']));

        // Check if code already exists for this warehouse
        $exists = Location::where('warehouse_id', $validated['warehouse_id'])
            ->where('code', $code)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Lokasi dengan kode ini sudah ada di gudang tersebut.',
            ], 422);
        }

        $location = Location::create([
            'warehouse_id' => $validated['warehouse_id'],
            'code' => $code,
            'location_attributes' => $validated['location_attributes'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Lokasi berhasil ditambahkan.',
            'location' => $location->load('warehouse'),
        ]);
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'location_attributes' => 'required|array',
            'location_attributes.*' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Auto-generate code from location_attributes only (no warehouse code)
        $code = implode('-', array_values($validated['location_attributes']));

        // Check if code already exists for this warehouse (except current location)
        $exists = Location::where('warehouse_id', $location->warehouse_id)
            ->where('code', $code)
            ->where('id', '!=', $location->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Lokasi dengan kode ini sudah ada di gudang tersebut.',
            ], 422);
        }

        $location->update([
            'code' => $code,
            'location_attributes' => $validated['location_attributes'],
            'description' => $validated['description'] ?? $location->description,
            'is_active' => $validated['is_active'] ?? $location->is_active,
        ]);

        return response()->json([
            'message' => 'Lokasi berhasil diperbarui.',
            'location' => $location->load('warehouse'),
        ]);
    }

    public function destroy(Location $location)
    {
        // Check if location has inventory balances
        $hasBalances = InventoryBalance::where('location_id', $location->id)->exists();

        // Check if location is used in stock movements
        $hasMovements = StockMovement::where('from_location_id', $location->id)
            ->orWhere('to_location_id', $location->id)
            ->exists();

        if ($hasBalances || $hasMovements) {
            return response()->json([
                'message' => 'Lokasi tidak dapat dihapus karena masih memiliki aktivitas stok.',
            ], 422);
        }

        $location->delete();

        return response()->json([
            'message' => 'Lokasi berhasil dihapus.',
        ]);
    }
}
