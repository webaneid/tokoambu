<?php

use App\Http\Controllers\Auth\CustomerRegisterController;
use App\Http\Controllers\Auth\CustomerLoginController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Shop\ShopController;
use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Checkout\CheckoutController;
use App\Http\Controllers\Order\OrderConfirmationController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\CustomerNotificationController;
use App\Http\Controllers\Customer\CustomerProfileController;
use App\Http\Controllers\Customer\CustomerWishlistController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

/**
 * STOREFRONT ROUTES
 * Public e-commerce routes for Toko Ambu storefront
 * Separate from admin routes (web guard)
 */

Route::middleware('web')->group(function () {
    
    // ============================================================
    // SHOP ROUTES - Public product browsing
    // ============================================================
    
    /**
     * PRODUCT LISTING & DETAIL ROUTES
     * GET /shop         - List all active products with pagination
     * GET /shop/{slug}  - Show single product detail with related products
     */
    Route::get('/shop', [ShopController::class, 'index'])
        ->name('shop.index');

Route::get('/shop/search', [ShopController::class, 'search'])
    ->name('shop.search');

Route::get('/shop/flash-sale', [ShopController::class, 'flashSale'])
    ->name('shop.flash-sale');
Route::get('/shop/bundles', [ShopController::class, 'bundleSale'])
    ->name('shop.bundle-sale');
Route::get('/shop/all', [ShopController::class, 'all'])
    ->name('shop.all');

    Route::get('/shop/bundles/{promotion}', [ShopController::class, 'bundleShow'])
        ->name('shop.bundle.show');
    
    Route::get('/shop/{slug}', [ShopController::class, 'show'])
        ->name('shop.show');
    
    
    // ============================================================
    // CART ROUTES - Public & Protected
    // ============================================================
    
    /**
     * CART ROUTES
     * GET    /cart           - View shopping cart
     * POST   /cart/add       - Add product to cart (API)
     * PUT    /cart/update    - Update cart item quantity (API)
     * DELETE /cart/{id}      - Remove product from cart (API)
     * POST   /cart/clear     - Clear entire cart
     */
    Route::get('/cart', [CartController::class, 'index'])
        ->name('cart.index');
    
    Route::post('/cart/add', [CartController::class, 'store'])
        ->name('cart.store');

    Route::post('/cart/bundle', [CartController::class, 'storeBundle'])
        ->name('cart.bundle');
    
    Route::put('/cart/update', [CartController::class, 'update'])
        ->name('cart.update');
    
    Route::delete('/cart/{cartItemId}', [CartController::class, 'destroy'])
        ->name('cart.destroy');
    
    Route::post('/cart/clear', [CartController::class, 'clear'])
        ->name('cart.clear')
        ->middleware('auth:customer');

    Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])
        ->name('cart.coupon.apply');
    Route::delete('/cart/coupon', [CartController::class, 'removeCoupon'])
        ->name('cart.coupon.remove');
    
    
    // ============================================================
    // CHECKOUT ROUTES - Require authentication
    // ============================================================
    
    /**
     * CHECKOUT ROUTES
     * GET  /checkout     - Show checkout form
     * POST /checkout     - Process checkout & create order
     */
    Route::middleware('auth:customer')->group(function () {
        Route::get('/checkout', [CheckoutController::class, 'index'])
            ->name('checkout.index');
        
        Route::post('/checkout', [CheckoutController::class, 'store'])
            ->name('checkout.store');
        
        /**
         * ORDER ROUTES
         * GET /order/{order}/confirmation - Show order confirmation page
         */
        Route::get('/order/{order}/confirmation', [OrderConfirmationController::class, 'show'])
            ->name('order.confirmation');
    });
    
    
    // ============================================================
    // PUBLIC ROUTES - No authentication required
    // ============================================================
    
    /**
     * CUSTOMER REGISTRATION ROUTES
     * GET  /account/register  - Show registration form
     * POST /account/register  - Process registration
     */
    Route::get('/account/register', [CustomerRegisterController::class, 'create'])
        ->name('customer.register')
        ->middleware('guest:customer');
    
    Route::post('/account/register', [CustomerRegisterController::class, 'store'])
        ->middleware('guest:customer');
    
    
    /**
     * CUSTOMER LOGIN ROUTES
     * GET  /account/login  - Show login form
     * POST /account/login  - Process login
     */
    Route::get('/account/login', [CustomerLoginController::class, 'create'])
        ->name('customer.login')
        ->middleware('guest:customer');
    
    Route::post('/account/login', [CustomerLoginController::class, 'store'])
        ->middleware('guest:customer');
    
    
    /**
     * PASSWORD RESET ROUTES
     * GET  /account/forgot-password      - Show password reset request form
     * POST /account/forgot-password      - Send password reset email
     * GET  /account/reset-password/{token} - Show password reset form
     * POST /account/reset-password       - Process password reset
     */
    Route::get('/account/forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request')
        ->middleware('guest:customer');
    
    Route::post('/account/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email')
        ->middleware('guest:customer');
    
    Route::get('/account/reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset')
        ->middleware('guest:customer');
    
    Route::post('/account/reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store')
        ->middleware('guest:customer');
    
    
    /**
     * EMAIL VERIFICATION ROUTES
     * GET  /account/verify-email/{id}/{hash} - Verify email with signed URL
     * POST /account/verification-notification - Resend verification email
     */
    Route::get('/account/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    
    Route::post('/account/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    
    
    // ============================================================
    // PROTECTED ROUTES - Authentication required (auth:customer)
    // Prefixed with /customer/* to avoid conflicts with admin routes
    // ============================================================

    Route::middleware('auth:customer')->group(function () {

        /**
         * CUSTOMER LOGOUT ROUTE
         * POST /account/logout - Logout customer
         */
        Route::post('/account/logout', [CustomerLoginController::class, 'destroy'])
            ->name('customer.logout');

        /**
         * CUSTOMER DASHBOARD ROUTES
         * GET /customer/dashboard - Customer dashboard overview
         * GET /customer/orders - Order history list
         * GET /customer/orders/{order} - Order detail/tracking
         */
        Route::get('/customer/dashboard', [CustomerDashboardController::class, 'dashboard'])
            ->name('customer.dashboard');

        Route::get('/customer/orders', [CustomerDashboardController::class, 'orders'])
            ->name('customer.orders');

        Route::get('/customer/orders/{order}', [CustomerDashboardController::class, 'show'])
            ->name('customer.order.show');

        Route::get('/customer/wishlist', [CustomerWishlistController::class, 'index'])
            ->name('customer.wishlist.index');

        Route::post('/customer/wishlist/toggle', [CustomerWishlistController::class, 'toggle'])
            ->name('customer.wishlist.toggle');

        Route::get('/customer/notifications', [CustomerNotificationController::class, 'index'])
            ->name('customer.notifications');

        Route::post('/customer/notifications/{notification}/read', [CustomerNotificationController::class, 'markRead'])
            ->name('customer.notifications.read');

        /**
         * CUSTOMER PROFILE ROUTES
         * GET  /customer/profile - Show profile page
         * PUT  /customer/profile - Update profile information
         * PUT  /customer/profile/password - Update password
         */
        Route::get('/customer/profile', [CustomerProfileController::class, 'show'])
            ->name('customer.profile');

        Route::put('/customer/profile', [CustomerProfileController::class, 'update'])
            ->name('customer.profile.update');

        Route::put('/customer/profile/password', [CustomerProfileController::class, 'updatePassword'])
            ->name('customer.password.update');

    });

    /**
     * CUSTOMER PAYMENT ROUTES (signed link or authenticated customer)
     * GET  /customer/payment/{order}/select - Select payment method
     * GET  /customer/payment/{order}/bank-transfer - Bank transfer details
     * GET  /customer/payment/{order}/ipaymu - iPaymu payment page
     * POST /customer/payment/{order}/ipaymu/create - Create iPaymu transaction
     * GET  /customer/payment/{order}/ipaymu/result - iPaymu payment result
     */
    Route::get('/customer/payment/{order}/select', [\App\Http\Controllers\Customer\CustomerPaymentController::class, 'selectMethod'])
        ->name('customer.payment.select');

    Route::get('/customer/payment/{order}/bank-transfer', [\App\Http\Controllers\Customer\CustomerPaymentController::class, 'bankTransfer'])
        ->name('customer.payment.bank-transfer');

    Route::get('/customer/payment/{order}/bank-transfer/confirm', [\App\Http\Controllers\Customer\CustomerPaymentController::class, 'confirmBankTransfer'])
        ->name('customer.payment.bank-transfer.confirm');

    Route::post('/customer/payment/{order}/bank-transfer/confirm', [\App\Http\Controllers\Customer\CustomerPaymentController::class, 'storeBankTransfer'])
        ->name('customer.payment.bank-transfer.store');

    Route::get('/customer/payment/{order}/ipaymu', [\App\Http\Controllers\Customer\CustomerPaymentController::class, 'ipaymu'])
        ->name('customer.payment.ipaymu');

    Route::post('/customer/payment/{order}/ipaymu/create', [\App\Http\Controllers\Customer\CustomerPaymentController::class, 'createIpaymuPayment'])
        ->name('customer.payment.ipaymu.create');

    Route::get('/customer/payment/{order}/ipaymu/result', [\App\Http\Controllers\Customer\CustomerPaymentController::class, 'ipaymuResult'])
        ->name('customer.payment.ipaymu-result');

    // Public page routes - must be before category routes to avoid slug conflicts
    Route::get('/page/{slug}', [PageController::class, 'show'])
        ->name('page.show');

    Route::get('/{category:slug}', [ShopController::class, 'category'])
        ->name('shop.category');

});
