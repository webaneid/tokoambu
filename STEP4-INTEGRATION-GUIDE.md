# Step 3 Integration Guide - Next Steps for Step 4

## Controllers Ready ✅

All authentication controllers for customer registration, login, and password reset are now complete and tested for PHP syntax.

## What's Needed for Step 4: Routes & Middleware

### Route Structure (routes/storefront.php)

```php
// Public Routes (no auth required)
Route::middleware('web')->group(function () {
    // Registration
    Route::get('/account/register', [CustomerRegisterController::class, 'create'])
        ->name('customer.register');
    Route::post('/account/register', [CustomerRegisterController::class, 'store']);
    
    // Login
    Route::get('/account/login', [CustomerLoginController::class, 'create'])
        ->name('customer.login');
    Route::post('/account/login', [CustomerLoginController::class, 'store']);
    
    // Password Reset
    Route::get('/account/forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('/account/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');
    
    Route::get('/account/reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('/account/reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
    
    // Email Verification
    Route::get('/account/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/account/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

// Protected Routes (auth:customer required)
Route::middleware(['web', 'auth:customer'])->group(function () {
    // Logout
    Route::post('/account/logout', [CustomerLoginController::class, 'destroy'])
        ->name('customer.logout');
    
    // Dashboard (Step 5+)
    // Cart (Step 6+)
    // Checkout (Step 7+)
});
```

### Middleware Implementation

The `auth:customer` middleware needs to be available in your app. Laravel provides this by default, but make sure:

1. `config/auth.php` has `customer` guard configured (✅ Already done in Step 1)
2. `app/Http/Middleware/Authenticate.php` redirects to `customer.login` for storefront

### Views Needed

These blade templates need to be created for Step 4:
- `resources/views/storefront/auth/register.blade.php`
- `resources/views/storefront/auth/login.blade.php`
- `resources/views/storefront/auth/forgot-password.blade.php`
- `resources/views/storefront/auth/reset-password.blade.php`
- `resources/views/storefront/auth/verify-email.blade.php`

## Database Reference

### CustomerUser Model
- Table: `customer_users`
- Key columns: id, email, password, name, phone, email_verified_at
- Methods: hasVerifiedEmail(), markEmailAsVerified(), sendEmailVerificationNotification()

### Cart Integration
- Automatically created on registration
- Migrated from guest session on login
- Related via: customer_user_id FK

## Controller Method Reference

### Registration Flow
```
GET  /account/register      → CustomerRegisterController@create()
POST /account/register      → CustomerRegisterController@store() ✅ Auto-login, Creates cart
```

### Login Flow
```
GET  /account/login         → CustomerLoginController@create()
POST /account/login         → CustomerLoginController@store() ✅ Session regen, Cart migrate
POST /account/logout        → CustomerLoginController@destroy()
```

### Password Reset Flow
```
GET  /account/forgot-password         → PasswordResetLinkController@create()
POST /account/forgot-password         → PasswordResetLinkController@store() ✅ Send email
GET  /account/reset-password/{token}  → NewPasswordController@create()
POST /account/reset-password          → NewPasswordController@store() ✅ Reset password
```

### Email Verification Flow
```
GET  /account/verify-email/{id}/{hash}        → VerifyEmailController@__invoke() ✅ Verify email
POST /account/verification-notification       → EmailVerificationNotificationController@store()
```

## Configuration Already in Place

✅ `config/auth.php`
- `customer` guard with session driver
- `customers` provider pointing to CustomerUser model
- `customers` password broker for password resets

✅ `app/Models/CustomerUser.php`
- Extends Authenticatable
- Uses Notifiable (for emails)
- Has wishlists() relationship

✅ `database/migrations/2026_01_12_000001_create_customer_users_table.php`
- customer_users table created with all required columns

## Testing Commands (After Routes Setup)

```bash
# Test registration
curl -X POST http://localhost:8000/account/register \
  -d "name=John&email=john@example.com&phone=6281234567890&password=Password@123&password_confirmation=Password@123"

# Test login
curl -X POST http://localhost:8000/account/login \
  -d "email=john@example.com&password=Password@123"

# Test logout
curl -X POST http://localhost:8000/account/logout
```

## Step 3 Completion Status

✅ CustomerRegisterController - ready to use
✅ CustomerLoginController - ready to use (with cart migration)
✅ PasswordResetLinkController - ready to use
✅ NewPasswordController - ready to use
✅ VerifyEmailController - ready to use
✅ EmailVerificationNotificationController - ready to use
✅ Form Request Validators - all created with validation rules
✅ PHP Syntax - all files validated

🔄 **NEXT: Create routes/storefront.php and blade templates**
