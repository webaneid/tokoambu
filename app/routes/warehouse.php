<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Warehouse\ReceivingController;
use App\Http\Controllers\Warehouse\TransferController;
use App\Http\Controllers\Warehouse\StockAdjustmentController;
use App\Http\Controllers\Warehouse\StockOpnameController;
use App\Http\Controllers\Warehouse\StockOutReportController;
use App\Http\Controllers\Warehouse\DashboardController as WarehouseDashboardController;
use App\Http\Controllers\Warehouse\WarehouseController;

// Ikuti pola proteksi rute lain (auth + verified) untuk akses gudang
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/warehouse', [WarehouseDashboardController::class, 'index'])->name('warehouse.dashboard')->middleware('permission:warehouse_dashboard');

    // Warehouse Receiving
    Route::middleware('permission:warehouse_receiving')->group(function () {
        Route::get('/warehouse/receiving', [ReceivingController::class, 'index'])->name('warehouse.receiving.index');
        Route::post('/warehouse/receiving/{purchase}', [ReceivingController::class, 'receive'])->name('warehouse.receiving.receive');
    });

    // Warehouse Transfer
    Route::middleware('permission:warehouse_transfer')->group(function () {
        Route::get('/warehouse/transfer', [TransferController::class, 'create'])->name('warehouse.transfer.create');
        Route::post('/warehouse/transfer', [TransferController::class, 'store'])->name('warehouse.transfer.store');
    });

    // Stock Adjustment
    Route::middleware('permission:warehouse_adjustment')->group(function () {
        Route::get('/warehouse/adjustments', [StockAdjustmentController::class, 'create'])->name('warehouse.adjustments.create');
        Route::post('/warehouse/adjustments', [StockAdjustmentController::class, 'store'])->name('warehouse.adjustments.store');
    });

    // Stock Opname
    Route::middleware('permission:warehouse_opname')->group(function () {
        Route::get('/warehouse/opname', [StockOpnameController::class, 'index'])->name('warehouse.opname.index');
        Route::get('/warehouse/opname/view', [StockOpnameController::class, 'show'])->name('warehouse.opname.view');
        Route::post('/warehouse/opname', [StockOpnameController::class, 'store'])->name('warehouse.opname.store');
    });

    // Warehouse Reports
    Route::get('/warehouse/reports/stock-out', [StockOutReportController::class, 'index'])->name('warehouse.reports.stock_out')->middleware('permission:warehouse_report');

    // Warehouse Master Data
    Route::middleware('permission:view_products')->group(function () {
        Route::get('/warehouse/warehouses', [WarehouseController::class, 'index'])->name('warehouse.warehouses.index');
        Route::post('/warehouse/warehouses', [WarehouseController::class, 'store'])->name('warehouse.warehouses.store');
        Route::put('/warehouse/warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouse.warehouses.update');
        Route::delete('/warehouse/warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouse.warehouses.destroy');

        // Location management routes
        Route::get('/warehouse/locations', [\App\Http\Controllers\Warehouse\LocationController::class, 'index'])->name('warehouse.locations.index');
        Route::post('/warehouse/locations', [\App\Http\Controllers\Warehouse\LocationController::class, 'store'])->name('warehouse.locations.store');
        Route::put('/warehouse/locations/{location}', [\App\Http\Controllers\Warehouse\LocationController::class, 'update'])->name('warehouse.locations.update');
        Route::delete('/warehouse/locations/{location}', [\App\Http\Controllers\Warehouse\LocationController::class, 'destroy'])->name('warehouse.locations.destroy');
    });
});
