<?php

use App\Http\Controllers\AiGatewayController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\FinancialCategoryController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\PreorderController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\Warehouse\StockOutReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\IPaymuWebhookController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

// Root route - redirect to dashboard or login
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

Route::get('/public/invoices/{order}', [InvoiceController::class, 'publicShow'])
    ->name('invoices.public')
    ->middleware('signed');
Route::get('/public/invoices/{order}/download', [InvoiceController::class, 'publicDownload'])
    ->name('invoices.public_download')
    ->middleware('signed');

// iPaymu routes (no CSRF protection)
Route::get('/ipaymu/proxy-qr', [IPaymuWebhookController::class, 'proxyQr'])
    ->name('ipaymu.proxy-qr');
Route::post('/ipaymu/notify', [IPaymuWebhookController::class, 'notify'])
    ->name('ipaymu.notify');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Product routes
    Route::middleware('permission:view_products')->group(function () {
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
    });
    Route::middleware('permission:create_products')->group(function () {
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
    });
    Route::middleware('permission:view_products')->group(function () {
        Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
    });
    Route::middleware('permission:edit_products')->group(function () {
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::patch('products/{product}', [ProductController::class, 'update']);
    });
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy')->middleware('permission:delete_products');

    Route::resource('product-categories', ProductCategoryController::class)->except(['show'])->middleware('permission:view_products');

    // Pages routes
    Route::resource('pages', PageController::class)->except(['show'])->middleware('permission:view_products');

    // Product Variant routes
    Route::middleware('permission:view_products')->group(function () {
        Route::get('products/{product}/variants', [ProductVariantController::class, 'index'])->name('products.variants.index');
    });
    Route::middleware('permission:edit_products')->group(function () {
        Route::post('products/{product}/variants/generate', [ProductVariantController::class, 'generateCombinations'])->name('products.variants.generate');
        Route::post('products/variants/bulk-pricing', [ProductVariantController::class, 'applyBulkPricing'])->name('products.variants.bulk-pricing');
        Route::post('products/{product}/variants', [ProductVariantController::class, 'store'])->name('products.variants.store');
        Route::put('products/{product}/variants', [ProductVariantController::class, 'update'])->name('products.variants.update');
        Route::delete('products/{product}/variants', [ProductVariantController::class, 'destroy'])->name('products.variants.destroy');
        Route::patch('variants/{variant}/toggle-active', [ProductVariantController::class, 'toggleActive'])->name('variants.toggle-active');
    });

    // Supplier routes
    Route::middleware('permission:view_suppliers')->group(function () {
        Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    });
    Route::middleware('permission:create_suppliers')->group(function () {
        Route::get('suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    });
    Route::middleware('permission:view_suppliers')->group(function () {
        Route::get('suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    });
    Route::middleware('permission:edit_suppliers')->group(function () {
        Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::patch('suppliers/{supplier}', [SupplierController::class, 'update']);
        Route::post('suppliers/{supplier}/bank-accounts', [SupplierController::class, 'storeBankAccount'])->name('suppliers.bank_accounts.store');
        Route::delete('suppliers/{supplier}/bank-accounts/{account}', [SupplierController::class, 'deleteBankAccount'])->name('suppliers.bank_accounts.destroy');
    });
    Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy')->middleware('permission:delete_suppliers');

    // Vendor routes
    Route::resource('vendors', VendorController::class)->middleware('permission:view_products');

    // Employee routes
    Route::resource('employees', EmployeeController::class)->middleware('permission:view_products');

    // Customer routes
    Route::middleware('permission:view_customers')->group(function () {
        Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    });
    Route::middleware('permission:create_customers')->group(function () {
        Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
    });
    Route::middleware('permission:view_customers')->group(function () {
        Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    });
    Route::middleware('permission:edit_customers')->group(function () {
        Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::patch('customers/{customer}', [CustomerController::class, 'update']);
        Route::post('customers/{customer}/bank-accounts', [CustomerController::class, 'storeBankAccount'])->name('customers.bank_accounts.store');
        Route::delete('customers/{customer}/bank-accounts/{account}', [CustomerController::class, 'deleteBankAccount'])->name('customers.bank_accounts.destroy');
    });
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy')->middleware('permission:delete_customers');
    
    // Order routes
    Route::middleware('permission:view_orders')->group(function () {
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/packing', [OrderController::class, 'packing'])->name('orders.packing');
        Route::get('orders/bulk-print', [OrderController::class, 'bulkPrint'])->name('orders.bulk-print');
        Route::get('api/products/by-order-type', [OrderController::class, 'getProductsByType'])->name('api.products.by_order_type');
    });
    Route::middleware('permission:create_orders')->group(function () {
        Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
        Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
    });
    Route::middleware('permission:view_orders')->group(function () {
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('orders/{order}/label', [OrderController::class, 'label'])->name('orders.label');
        Route::get('orders/{order}/print', [OrderController::class, 'printLabel'])->name('orders.print');
    });
    Route::middleware('permission:edit_orders')->group(function () {
        Route::get('orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
        Route::put('orders/{order}', [OrderController::class, 'update'])->name('orders.update');
        Route::patch('orders/{order}', [OrderController::class, 'update']);
    });
    Route::middleware('permission:update_order_status')->group(function () {
        Route::post('orders/bulk-mark-packed', [OrderController::class, 'bulkMarkPacked'])->name('orders.bulk-mark-packed');
        Route::post('orders/{order}/items/{item}/pick', [OrderController::class, 'pickItem'])->name('orders.items.pick');
        Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('orders/{order}/cancel-and-refund', [OrderController::class, 'cancelAndRefund'])->name('orders.cancel-and-refund');
        Route::post('orders/{order}/refund', [OrderController::class, 'refund'])->name('orders.refund');
    });
    Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy')->middleware('permission:delete_orders');
    
    // Payment routes
    Route::middleware('permission:view_payments')->group(function () {
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    });
    Route::middleware('permission:create_payments')->group(function () {
        Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
    });
    Route::middleware('permission:view_payments')->group(function () {
        Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    });
    Route::patch('payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify')->middleware('permission:verify_payments');

    // Preorder routes
    Route::middleware('permission:view_orders')->group(function () {
        Route::get('preorders', [PreorderController::class, 'index'])->name('preorders.index');
        Route::get('preorders/{product}', [PreorderController::class, 'show'])->name('preorders.show');
        Route::get('preorders/{order}/whatsapp/{type}', [PreorderController::class, 'getWhatsAppMessage'])->name('preorders.whatsapp');
    });
    Route::middleware('permission:edit_orders')->group(function () {
        Route::post('preorders/{product}/mark-ready', [PreorderController::class, 'markProductReady'])->name('preorders.mark_ready');
        Route::get('preorders/{product}/periods/create', [PreorderController::class, 'createPeriod'])->name('preorders.periods.create');
        Route::post('preorders/{product}/periods', [PreorderController::class, 'storePeriod'])->name('preorders.periods.store');
        Route::post('preorders/periods/{period}/close', [PreorderController::class, 'closePeriod'])->name('preorders.periods.close');
        Route::post('preorders/periods/{period}/archive', [PreorderController::class, 'archivePeriod'])->name('preorders.periods.archive');
        Route::post('preorders/periods/{period}/reopen', [PreorderController::class, 'reopenPeriod'])->name('preorders.periods.reopen');
    });

    // Promo Engine routes
    Route::middleware('permission:view_products')->group(function () {
        Route::get('promotions', [PromotionController::class, 'index'])->name('promotions.index');
    });
    Route::middleware('permission:edit_products')->group(function () {
        Route::get('promotions/create', [PromotionController::class, 'create'])->name('promotions.create');
        Route::post('promotions', [PromotionController::class, 'store'])->name('promotions.store');
    });
    Route::middleware('permission:view_products')->group(function () {
        Route::get('promotions/{promotion}', [PromotionController::class, 'show'])->name('promotions.show');
    });
    Route::middleware('permission:edit_products')->group(function () {
        Route::get('promotions/{promotion}/edit', [PromotionController::class, 'edit'])->name('promotions.edit');
        Route::put('promotions/{promotion}', [PromotionController::class, 'update'])->name('promotions.update');
        Route::patch('promotions/{promotion}', [PromotionController::class, 'update']);
        Route::post('promotions/{promotion}/duplicate', [PromotionController::class, 'duplicate'])->name('promotions.duplicate');
        Route::post('promotions/{promotion}/end', [PromotionController::class, 'endNow'])->name('promotions.end');
        Route::post('promotions/{promotion}/archive', [PromotionController::class, 'archive'])->name('promotions.archive');
    });

    // Purchase routes
    Route::middleware('permission:view_purchases')->group(function () {
        Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    });
    Route::middleware('permission:create_purchases')->group(function () {
        Route::get('purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    });
    Route::middleware('permission:view_purchases')->group(function () {
        Route::get('purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    });
    Route::middleware('permission:edit_purchases')->group(function () {
        Route::get('purchases/{purchase}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');
        Route::put('purchases/{purchase}', [PurchaseController::class, 'update'])->name('purchases.update');
        Route::patch('purchases/{purchase}', [PurchaseController::class, 'update']);
        Route::post('purchases/{purchase}/pay', [PurchaseController::class, 'pay'])->name('purchases.pay');
    });
    Route::delete('purchases/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.destroy')->middleware('permission:delete_purchases');

    // Media gallery
    Route::get('media', [\App\Http\Controllers\MediaController::class, 'index'])->name('media.index');
    Route::post('media', [\App\Http\Controllers\MediaController::class, 'store'])->name('media.store');
    Route::delete('media/{media}', [\App\Http\Controllers\MediaController::class, 'destroy'])->name('media.destroy');
    Route::post('media/gallery-order', [\App\Http\Controllers\MediaController::class, 'updateGalleryOrder'])->name('media.update_gallery_order');
    Route::get('media/payment-proof/list', [\App\Http\Controllers\MediaController::class, 'listPaymentProof'])->name('media.payment_proof.list');
    Route::get('media/product-photo/list', [\App\Http\Controllers\MediaController::class, 'listProductPhoto'])->name('media.product_photo.list');
    Route::get('media/shipment-proof/list', [\App\Http\Controllers\MediaController::class, 'listShipmentProof'])->name('media.shipment_proof.list');
    Route::get('media/banner-image/list', [\App\Http\Controllers\MediaController::class, 'listBannerImage'])->name('media.banner_image.list');

    // AI Studio gateway
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('features', [AiGatewayController::class, 'features'])->name('features');
        Route::post('enhance', [AiGatewayController::class, 'enhance'])->name('enhance');
        Route::get('jobs/{aiLog}', [AiGatewayController::class, 'show'])->name('jobs.show');
    });
    
    // Ledger routes
    Route::middleware('permission:view_ledger')->group(function () {
        Route::get('/ledger', [LedgerController::class, 'index'])->name('ledger.index');
        Route::get('/ledger/report', [LedgerController::class, 'report'])->name('ledger.report');
    });
    Route::middleware('permission:create_ledger_entry')->group(function () {
        Route::get('/ledger/create', [LedgerController::class, 'create'])->name('ledger.create');
        Route::post('/ledger', [LedgerController::class, 'store'])->name('ledger.store');
    });
    Route::middleware('permission:view_ledger')->group(function () {
        Route::get('/ledger/{ledgerEntry}', [LedgerController::class, 'show'])->name('ledger.show');
    });

    // Financial Categories routes
    Route::middleware('permission:view_ledger')->group(function () {
        Route::get('/financial-categories', [FinancialCategoryController::class, 'index'])->name('financial-categories.index');
    });
    Route::middleware('permission:create_ledger_entry')->group(function () {
        Route::get('/financial-categories/create', [FinancialCategoryController::class, 'create'])->name('financial-categories.create');
        Route::post('/financial-categories', [FinancialCategoryController::class, 'store'])->name('financial-categories.store');
        Route::get('/financial-categories/{financialCategory}/edit', [FinancialCategoryController::class, 'edit'])->name('financial-categories.edit');
        Route::put('/financial-categories/{financialCategory}', [FinancialCategoryController::class, 'update'])->name('financial-categories.update');
        Route::delete('/financial-categories/{financialCategory}', [FinancialCategoryController::class, 'destroy'])->name('financial-categories.destroy');
        Route::post('/api/bank-accounts', [App\Http\Controllers\Api\BankAccountController::class, 'store'])->name('api.bank-accounts.store');
    });

    // Refund routes
    Route::resource('refunds', RefundController::class)->except(['edit', 'update']);
    Route::post('/refunds/{refund}/approve', [RefundController::class, 'approve'])->name('refunds.approve');
    Route::post('/refunds/{refund}/reject', [RefundController::class, 'reject'])->name('refunds.reject');
    
    // Invoice routes
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{order}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{order}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::get('/invoices/{order}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::post('/invoices/{order}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    
    // Shipment routes
    Route::middleware('permission:view_shipments')->group(function () {
        Route::get('shipments', [ShipmentController::class, 'index'])->name('shipments.index');
        Route::get('shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show');
        Route::get('/shipments/{shipment}/label', [ShipmentController::class, 'label'])->name('shipments.label');
        Route::get('/shipments/{shipment}/print', [ShipmentController::class, 'printLabel'])->name('shipments.print');
    });
    Route::middleware('permission:create_shipments')->group(function () {
        Route::get('shipments/create', [ShipmentController::class, 'create'])->name('shipments.create');
        Route::post('shipments', [ShipmentController::class, 'store'])->name('shipments.store');
    });
    Route::middleware('permission:update_shipment_status')->group(function () {
        Route::get('shipments/{shipment}/edit', [ShipmentController::class, 'edit'])->name('shipments.edit');
        Route::put('shipments/{shipment}', [ShipmentController::class, 'update'])->name('shipments.update');
        Route::patch('shipments/{shipment}', [ShipmentController::class, 'update']);
        Route::post('/shipments/{shipment}/status', [ShipmentController::class, 'updateStatus'])->name('shipments.updateStatus');
        Route::post('/shipments/{shipment}/track', [ShipmentController::class, 'track'])->name('shipments.track');
        Route::post('/shipments/{shipment}/pick-and-ship', [ShipmentController::class, 'pickAndShip'])->name('shipments.pickAndShip');
    });
    Route::delete('shipments/{shipment}', [ShipmentController::class, 'destroy'])->name('shipments.destroy')->middleware('permission:delete_shipments');
    
    // Settings routes (Super Admin only)
    Route::middleware('role:Super Admin')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/bank-accounts', [SettingController::class, 'storeBankAccount'])->name('settings.bank_accounts.store');
        Route::delete('/settings/bank-accounts/{account}', [SettingController::class, 'deleteBankAccount'])->name('settings.bank_accounts.destroy');

        // User Management
        Route::post('/settings/users', [SettingController::class, 'storeUser'])->name('settings.users.store');
        Route::put('/settings/users/{user}', [SettingController::class, 'updateUser'])->name('settings.users.update');
        Route::delete('/settings/users/{user}', [SettingController::class, 'deleteUser'])->name('settings.users.delete');

        // Admin Settings (General & Payment)
        Route::get('/admin/settings', [SettingsController::class, 'index'])->name('admin.settings.index');
        Route::put('/admin/settings/general', [SettingsController::class, 'updateGeneral'])->name('admin.settings.update-general');
        Route::put('/admin/settings/storefront', [SettingsController::class, 'updateStorefront'])->name('admin.settings.update-storefront');
        Route::put('/admin/settings/payment-methods', [SettingsController::class, 'updatePaymentMethods'])->name('admin.settings.update-payment-methods');
        Route::put('/admin/settings/payment', [SettingsController::class, 'updatePayment'])->name('admin.settings.update-payment');

        // Footer Menu Management
        Route::post('/admin/settings/footer-menu', [SettingsController::class, 'storeFooterMenuItem'])->name('admin.settings.footer-menu.store');
        Route::put('/admin/settings/footer-menu/{footerMenuItem}', [SettingsController::class, 'updateFooterMenuItem'])->name('admin.settings.footer-menu.update');
        Route::delete('/admin/settings/footer-menu/{footerMenuItem}', [SettingsController::class, 'deleteFooterMenuItem'])->name('admin.settings.footer-menu.delete');
        Route::post('/admin/settings/footer-menu/reorder', [SettingsController::class, 'reorderFooterMenuItems'])->name('admin.settings.footer-menu.reorder');

        // User Management
        Route::post('/admin/settings/users', [SettingsController::class, 'storeUser'])->name('admin.settings.users.store');
        Route::put('/admin/settings/users/{user}', [SettingsController::class, 'updateUser'])->name('admin.settings.users.update');
        Route::delete('/admin/settings/users/{user}', [SettingsController::class, 'deleteUser'])->name('admin.settings.users.delete');
    });

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
