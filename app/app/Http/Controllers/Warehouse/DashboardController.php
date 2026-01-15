<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\InventoryAnalytics;
use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalSku = Product::count();
        $totalOnHand = InventoryBalance::sum('qty_on_hand');

        $analytics = InventoryAnalytics::with(['product', 'location.warehouse'])->get();
        $active = $analytics->where('status', 'active')->count();
        $slow = $analytics->where('status', 'slow_moving')->count();
        $dead = $analytics->where('status', 'dead_stock')->count();

        // Map analytics status by product+location for quick lookup
        $analyticsMap = $analytics->keyBy(fn ($row) => $row->product_id.'-'.$row->location_id);

        $balancesQuery = InventoryBalance::with(['product', 'productVariant', 'location.warehouse']);

        if ($search = $request->get('product')) {
            $balancesQuery->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter by warehouse IDs (multi-select)
        if ($warehouseIds = $request->get('warehouses')) {
            if (is_array($warehouseIds) && count($warehouseIds) > 0) {
                $balancesQuery->whereHas('location.warehouse', function ($q) use ($warehouseIds) {
                    $q->whereIn('id', $warehouseIds);
                });
            }
        }

        // Filter by location IDs (multi-select)
        if ($locationIds = $request->get('locations')) {
            if (is_array($locationIds) && count($locationIds) > 0) {
                $balancesQuery->whereIn('location_id', $locationIds);
            }
        }

        $balancesCollection = $balancesQuery->get();

        $balances = $balancesCollection
            ->sortBy(fn ($b) => sprintf(
                '%s|%s|%s',
                $b->product?->name ?? '',
                $b->location?->warehouse?->code ?? '',
                $b->location?->code ?? ''
            ))
            ->map(function ($balance) use ($analyticsMap) {
                $key = $balance->product_id.'-'.$balance->location_id;
                $analytic = $analyticsMap[$key] ?? null;
                $lastOut = StockMovement::where('product_id', $balance->product_id)
                    ->where(fn ($q) => $q->where('from_location_id', $balance->location_id)->orWhere('to_location_id', $balance->location_id))
                    ->orderByDesc('movement_date')
                    ->value('movement_date');

                // Build product name with variant info
                $productName = $balance->product?->name ?? '-';
                if ($balance->productVariant) {
                    $variantAttrs = implode(' / ', $balance->productVariant->variant_attributes);
                    $productName .= " ({$variantAttrs})";
                }

                // Use variant SKU if available, otherwise use product SKU
                $sku = $balance->productVariant?->sku ?? $balance->product?->sku ?? '-';

                // Display location: if warehouse has only 1 location, show warehouse name only
                // Otherwise show warehouse code + location code
                $warehouseId = $balance->location?->warehouse_id;

                // Count unique locations for this warehouse
                $warehouseLocationCount = \App\Models\Location::where('warehouse_id', $warehouseId)->count();

                $locationDisplay = $warehouseLocationCount === 1
                    ? ($balance->location?->warehouse?->name ?? $balance->location?->warehouse?->code ?? '-')
                    : (($balance->location?->warehouse?->code ?? '').' - '.($balance->location?->code ?? ''));

                return [
                    'product' => $productName,
                    'sku' => $sku,
                    'location' => $locationDisplay,
                    'qty_on_hand' => $balance->qty_on_hand,
                    'status' => $analytic?->status,
                    'last_out_date' => $analytic?->last_out_date ?? $lastOut,
                ];
            });

        $warehouses = Warehouse::orderBy('code')->get();
        $locations = \App\Models\Location::with('warehouse')->orderBy('warehouse_id')->orderBy('code')->get();
        $allowedSorts = ['product', 'sku', 'location', 'qty_on_hand', 'last_out_date', 'status'];
        $sort = $request->query('sort', 'product');
        $direction = $request->query('direction', 'asc');

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'product';
        }
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $sortedBalances = $balances->sortBy(function ($row) use ($sort) {
            return match ($sort) {
                'qty_on_hand' => (float) ($row['qty_on_hand'] ?? 0),
                'last_out_date' => $row['last_out_date'] ? strtotime($row['last_out_date']) : 0,
                default => strtolower((string) ($row[$sort] ?? '')),
            };
        });
        if ($direction === 'desc') {
            $sortedBalances = $sortedBalances->reverse()->values();
        }

        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginatedBalances = new LengthAwarePaginator(
            $sortedBalances->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            $sortedBalances->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('warehouse.dashboard', [
            'totalSku' => $totalSku,
            'totalOnHand' => $totalOnHand,
            'active' => $active,
            'slow' => $slow,
            'dead' => $dead,
            'balances' => $paginatedBalances,
            'warehouses' => $warehouses,
            'locations' => $locations,
            'filters' => [
                'product' => $request->get('product', ''),
                'warehouses' => $request->get('warehouses', []),
                'locations' => $request->get('locations', []),
            ],
        ]);
    }
}
