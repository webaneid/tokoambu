<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockOutReportController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::all();
        $locations = Location::with('warehouse')->get();

        $query = StockMovement::with(['product', 'productVariant', 'fromLocation.warehouse', 'user'])
            ->whereIn('movement_type', ['adjust', 'ship'])
            ->where(function ($builder) {
                $builder->whereNotNull('reason')
                    ->orWhere('movement_type', 'ship');
            });

        if ($request->filled('start_date')) {
            $query->whereDate('movement_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('movement_date', '<=', $request->end_date);
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('location_id')) {
            $query->where('from_location_id', $request->location_id);
        }
        if ($request->filled('reason')) {
            if ($request->reason === 'shipment') {
                $query->where('movement_type', 'ship');
            } else {
                $query->where('movement_type', 'adjust')
                    ->where('reason', $request->reason);
            }
        }

        $movements = $query->orderByDesc('movement_date')->paginate(20)->withQueryString();

        $reasons = [
            'shipment',
            'rusak',
            'hilang',
            'gift',
            'sample',
            'expired',
            'return_to_supplier',
            'stock_opname',
            'lainnya',
        ];

        return view('warehouse.reports.stock_out', compact('movements', 'products', 'locations', 'reasons'));
    }
}
