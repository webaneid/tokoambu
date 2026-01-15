<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\Api\ShippingCostController;
use App\Http\Controllers\Api\ShippingTrackingController;
use App\Services\LocationService;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Product Categories API
Route::get('/product-categories', [ProductCategoryController::class, 'indexCategories']);
Route::post('/product-categories', [ProductCategoryController::class, 'storeCategory']);
Route::get('/product-categories/{id}/custom-fields', [ProductCategoryController::class, 'getCustomFields']);

// Suppliers API
Route::get('/suppliers', [ProductCategoryController::class, 'indexSuppliers']);
Route::post('/suppliers', [ProductCategoryController::class, 'storeSupplier']);

// Location API (Provinces, Cities, Districts)
Route::get('/provinces', function (LocationService $service) {
    return response()->json($service->getProvinces());
});

// Autocomplete APIs - MUST BE BEFORE {id} routes to avoid conflict
Route::get('/provinces/search', function (LocationService $service) {
    $query = request('q', '');
    $limit = request('limit', 6);
    return response()->json($service->searchProvinces($query, $limit));
});

Route::get('/cities/search', function (LocationService $service) {
    $query = request('q', '');
    $provinceCode = request('province_id', request('province_code'));
    $limit = request('limit', 6);
    return response()->json($service->searchCities($query, $provinceCode, $limit));
});

Route::get('/districts/search', function (LocationService $service) {
    $query = request('q', '');
    $cityCode = request('city_id', request('city_code'));
    $limit = request('limit', 6);
    return response()->json($service->searchDistricts($query, $cityCode, $limit));
});

// Get by ID routes - AFTER search routes
Route::get('/cities/{provinceId}', function ($provinceId, LocationService $service) {
    return response()->json($service->getCities($provinceId));
});

// Alternative: Get by query parameter
Route::get('/cities', function (LocationService $service) {
    $provinceId = request('province_id');
    if (!$provinceId) {
        return response()->json([]);
    }
    return response()->json($service->getCities($provinceId));
});

Route::get('/districts/{cityId}', function ($cityId, LocationService $service) {
    return response()->json($service->getDistricts($cityId));
});

// Alternative: Get by query parameter
Route::get('/districts', function (LocationService $service) {
    $cityId = request('city_id');
    if (!$cityId) {
        return response()->json([]);
    }
    return response()->json($service->getDistricts($cityId));
});

// Shipping cost calculation (RajaOngkir)
Route::post('/shipping/cost', [ShippingCostController::class, 'calculate']);
Route::post('/shipping/track', [ShippingTrackingController::class, 'track']);
