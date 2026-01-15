<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FooterMenuItem;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SettingsController extends Controller
{
    /**
     * Display payment settings page
     */
    public function index(): View
    {
        $settings = [
            // Store Front - Banners
            'banners' => json_decode(Setting::get('storefront_banners', '[]'), true),

            // Store Front - Colors
            'color_primary' => Setting::get('color_primary', '#F17B0D'),
            'color_primary_hover' => Setting::get('color_primary_hover', '#DD5700'),
            'color_secondary' => Setting::get('color_secondary', '#0D36AA'),
            'color_alternative' => Setting::get('color_alternative', '#D00086'),
            'color_dark' => Setting::get('color_dark', '#1F2937'),
            'color_light_gray' => Setting::get('color_light_gray', '#F9FAFB'),

            // iPaymu Settings
            'ipaymu_va' => Setting::get('ipaymu_va'),
            'ipaymu_api_key' => Setting::get('ipaymu_api_key'),
            'ipaymu_mode' => Setting::get('ipaymu_mode', 'sandbox'),

            // Payment Methods
            'payment_method_cod' => Setting::get('payment_method_cod', false),
            'payment_method_bank_transfer' => Setting::get('payment_method_bank_transfer', false),
            'payment_method_ewallet' => Setting::get('payment_method_ewallet', false),
            'payment_method_ipaymu' => Setting::get('payment_method_ipaymu', false),
        ];

        $storefrontUrls = collect([
            ['id' => '/', 'name' => '/', 'url' => '/', 'label' => 'Beranda'],
            ['id' => '/shop', 'name' => '/shop', 'url' => '/shop', 'label' => 'Shop - Semua Produk'],
            ['id' => '/shop/flash-sale', 'name' => '/shop/flash-sale', 'url' => '/shop/flash-sale', 'label' => 'Flash Sale'],
            ['id' => '/shop/bundles', 'name' => '/shop/bundles', 'url' => '/shop/bundles', 'label' => 'Bundling'],
            ['id' => '/cart', 'name' => '/cart', 'url' => '/cart', 'label' => 'Keranjang'],
            ['id' => '/checkout', 'name' => '/checkout', 'url' => '/checkout', 'label' => 'Checkout'],
            ['id' => '/account/login', 'name' => '/account/login', 'url' => '/account/login', 'label' => 'Login'],
            ['id' => '/account/register', 'name' => '/account/register', 'url' => '/account/register', 'label' => 'Register'],
            ['id' => '/customer/dashboard', 'name' => '/customer/dashboard', 'url' => '/customer/dashboard', 'label' => 'Dashboard Customer'],
            ['id' => '/customer/orders', 'name' => '/customer/orders', 'url' => '/customer/orders', 'label' => 'Pesanan Customer'],
            ['id' => '/customer/profile', 'name' => '/customer/profile', 'url' => '/customer/profile', 'label' => 'Profil Customer'],
        ])
            ->merge(
                ProductCategory::query()
                    ->select(['name', 'slug'])
                    ->orderBy('name')
                    ->get()
                    ->map(fn ($category) => [
                        'id' => "/{$category->slug}",
                        'name' => "/{$category->slug}",
                        'url' => "/{$category->slug}",
                        'label' => "Kategori: {$category->name}",
                    ])
            )
            ->merge(
                Product::query()
                    ->select(['name', 'slug'])
                    ->orderBy('name')
                    ->limit(200)
                    ->get()
                    ->map(fn ($product) => [
                        'id' => "/shop/{$product->slug}",
                        'name' => "/shop/{$product->slug}",
                        'url' => "/shop/{$product->slug}",
                        'label' => "Produk: {$product->name}",
                    ])
            )
            ->values();

        // Footer Menu Items
        $footerMenuItems = FooterMenuItem::with('page')->ordered()->get();

        // Available Pages for Footer Menu
        $pages = Page::where('is_published', true)->orderBy('title')->get();

        // Users and Roles
        $users = User::with('roles')->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('admin.settings.index', compact('settings', 'storefrontUrls', 'footerMenuItems', 'pages', 'users', 'roles'));
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'store_email' => ['required', 'email'],
            'store_phone' => ['required', 'string', 'max:20'],
            'store_address' => ['required', 'string', 'max:500'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan umum berhasil disimpan');
    }

    /**
     * Update storefront settings
     */
    public function updateStorefront(Request $request): RedirectResponse
    {
        // Save Banners
        $banners = $request->input('banners', []);
        $cleanBanners = [];
        foreach ($banners as $banner) {
            $cleanBanners[] = [
                'type' => $banner['type'] ?? 'image',
                'image_url' => $banner['image_url'] ?? null,
                'title' => $banner['title'] ?? null,
                'description' => $banner['description'] ?? null,
                'link' => $banner['link'] ?? null,
                'link_text' => $banner['link_text'] ?? null,
                'is_active' => isset($banner['is_active']) && $banner['is_active'] == '1',
            ];
        }
        Setting::set('storefront_banners', json_encode($cleanBanners));

        // Save Colors - using hex input (color_primary_hex, etc)
        $colors = [
            'color_primary' => $request->input('color_primary_hex', '#F17B0D'),
            'color_primary_hover' => $request->input('color_primary_hover_hex', '#DD5700'),
            'color_secondary' => $request->input('color_secondary_hex', '#0D36AA'),
            'color_alternative' => $request->input('color_alternative_hex', '#D00086'),
            'color_dark' => $request->input('color_dark_hex', '#1F2937'),
            'color_light_gray' => $request->input('color_light_gray_hex', '#F9FAFB'),
        ];

        foreach ($colors as $key => $value) {
            // Validate hex format
            if (preg_match('/^#[0-9A-F]{6}$/i', $value)) {
                Setting::set($key, $value);
            }
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Storefront berhasil disimpan');
    }

    /**
     * Update payment methods
     */
    public function updatePaymentMethods(Request $request): RedirectResponse
    {
        // Store payment method checkboxes (1 = enabled, 0 = disabled)
        Setting::set('payment_method_cod', $request->has('payment_method_cod'));
        Setting::set('payment_method_bank_transfer', $request->has('payment_method_bank_transfer'));
        Setting::set('payment_method_ewallet', $request->has('payment_method_ewallet'));
        Setting::set('payment_method_ipaymu', $request->has('payment_method_ipaymu'));

        return redirect()->route('admin.settings.index')
            ->with('success', 'Metode pembayaran berhasil disimpan');
    }

    /**
     * Update iPaymu settings
     */
    public function updatePayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ipaymu_va' => ['required', 'string', 'max:20'],
            'ipaymu_api_key' => ['required', 'string', 'max:255'],
            'ipaymu_mode' => ['required', 'in:sandbox,production'],
        ], [
            'ipaymu_va.required' => 'VA iPaymu wajib diisi',
            'ipaymu_api_key.required' => 'API Key iPaymu wajib diisi',
            'ipaymu_mode.required' => 'Mode iPaymu wajib dipilih',
            'ipaymu_mode.in' => 'Mode harus Sandbox atau Production',
        ]);

        // Encrypt sensitive data
        Setting::setEncrypted('ipaymu_va', $validated['ipaymu_va']);
        Setting::setEncrypted('ipaymu_api_key', $validated['ipaymu_api_key']);
        Setting::set('ipaymu_mode', $validated['ipaymu_mode']);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan iPaymu berhasil disimpan');
    }

    /**
     * Store new footer menu item
     */
    public function storeFooterMenuItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|in:page,custom_url',
            'page_id' => 'required_if:type,page|nullable|exists:pages,id',
            'custom_url' => 'required_if:type,custom_url|nullable|string',
        ]);

        // Get max order
        $maxOrder = FooterMenuItem::max('order') ?? 0;

        $menuItem = FooterMenuItem::create([
            'label' => $validated['label'],
            'type' => $validated['type'],
            'page_id' => $validated['page_id'] ?? null,
            'custom_url' => $validated['custom_url'] ?? null,
            'order' => $maxOrder + 1,
            'is_active' => true,
        ]);

        $menuItem->load('page');

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil ditambahkan',
            'data' => $menuItem,
        ]);
    }

    /**
     * Update footer menu item
     */
    public function updateFooterMenuItem(Request $request, FooterMenuItem $footerMenuItem): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|in:page,custom_url',
            'page_id' => 'required_if:type,page|nullable|exists:pages,id',
            'custom_url' => 'required_if:type,custom_url|nullable|string',
            'is_active' => 'boolean',
        ]);

        $footerMenuItem->update($validated);
        $footerMenuItem->load('page');

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil diperbarui',
            'data' => $footerMenuItem,
        ]);
    }

    /**
     * Delete footer menu item
     */
    public function deleteFooterMenuItem(FooterMenuItem $footerMenuItem): JsonResponse
    {
        $footerMenuItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil dihapus',
        ]);
    }

    /**
     * Reorder footer menu items
     */
    public function reorderFooterMenuItems(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:footer_menu_items,id',
            'items.*.order' => 'required|integer',
        ]);

        foreach ($validated['items'] as $item) {
            FooterMenuItem::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Urutan menu berhasil disimpan',
        ]);
    }

    /**
     * Store a new user
     */
    public function storeUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ], [
            'name.required' => 'Nama wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'role.required' => 'Role wajib dipilih',
            'role.exists' => 'Role tidak valid',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        $user->load('roles');

        return response()->json([
            'success' => true,
            'message' => 'User berhasil ditambahkan',
            'data' => $user,
        ]);
    }

    /**
     * Update an existing user
     */
    public function updateUser(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ], [
            'name.required' => 'Nama wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'role.required' => 'Role wajib dipilih',
            'role.exists' => 'Role tidak valid',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Update password if provided
        if (!empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        // Sync role
        $user->syncRoles([$validated['role']]);

        $user->load('roles');

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diperbarui',
            'data' => $user,
        ]);
    }

    /**
     * Delete a user
     */
    public function deleteUser(User $user): JsonResponse
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menghapus akun sendiri',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus',
        ]);
    }
}
