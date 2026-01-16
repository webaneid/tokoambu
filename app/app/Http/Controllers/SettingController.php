<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use App\Models\BankAccount;
use App\Models\AiIntegration;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        $settings['active_couriers'] = isset($settings['active_couriers'])
            ? (json_decode($settings['active_couriers'], true) ?: [])
            : [];
        $couriers = config('rajaongkir.couriers', []);
        $bankAccounts = BankAccount::where('user_id', auth()->id())->get();
        $geminiIntegration = AiIntegration::forProvider('gemini')->first();
        $geminiKeyExists = $geminiIntegration && !empty($geminiIntegration->api_key);
        $geminiMaskPlaceholder = config('aistudio.key_placeholder', '************');

        // Load logo and favicon media
        $logoMediaId = Setting::get('logo_media_id');
        $faviconMediaId = Setting::get('favicon_media_id');
        $logoMedia = $logoMediaId ? \App\Models\Media::find($logoMediaId) : null;
        $faviconMedia = $faviconMediaId ? \App\Models\Media::find($faviconMediaId) : null;

        // Users and Roles
        $users = User::with('roles')->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('settings.index', compact('settings', 'bankAccounts', 'couriers', 'geminiIntegration', 'geminiKeyExists', 'geminiMaskPlaceholder', 'logoMedia', 'faviconMedia', 'users', 'roles'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'required|string|max:255',
            'store_phone' => 'nullable|string|max:20',
            'store_email' => 'nullable|email',
            'store_whatsapp' => 'nullable|string|max:20',
            'store_website' => 'nullable|string|max:255',
            'store_address' => 'nullable|string',
            'store_city' => 'nullable|string',
            'storefront_meta_description' => 'nullable|string|max:320',
            'logo_media_id' => 'nullable|integer|exists:media,id',
            'favicon_media_id' => 'nullable|integer|exists:media,id',
            'invoice_prefix' => 'nullable|string|max:10',
            'invoice_footer' => 'nullable|string',
            'rajaongkir_key' => 'nullable|string',
            'rajaongkir_mode' => 'nullable|in:starter,basic,pro',
            'origin_province_id' => 'nullable|integer',
            'origin_province_name' => 'nullable|string',
            'origin_city_id' => 'nullable|integer',
            'origin_city_name' => 'nullable|string',
            'origin_district_id' => 'nullable|integer',
            'origin_district_name' => 'nullable|string',
            'origin_postal_code' => 'nullable|string',
            'active_couriers' => 'nullable|array',
            'active_couriers.*' => 'string',
            'dead_stock_slow_days' => 'nullable|numeric|min:1',
            'dead_stock_dead_days' => 'nullable|numeric|min:1',
            'min_margin_percent' => 'nullable|numeric|min:0|max:100',
            'preorder_dp_required' => 'nullable|boolean',
            'preorder_dp_percentage' => 'nullable|numeric|min:0|max:100',
            'preorder_dp_deadline_days' => 'nullable|numeric|min:1',
            'preorder_final_deadline_days' => 'nullable|numeric|min:1',
            'preorder_wa_dp_reminder' => 'nullable|string',
            'preorder_wa_dp_confirmed' => 'nullable|string',
            'preorder_wa_product_ready' => 'nullable|string',
            'preorder_wa_final_reminder' => 'nullable|string',
            'preorder_wa_cancelled' => 'nullable|string',
            'wa_order_message' => 'nullable|string',
            'wa_dp_received_message' => 'nullable|string',
            'wa_paid_message' => 'nullable|string',
            'wa_packed_message' => 'nullable|string',
            'wa_shipped_message' => 'nullable|string',
            'wa_delivered_message' => 'nullable|string',
            'wa_cancelled_message' => 'nullable|string',
            'wa_cancelled_refund_pending_message' => 'nullable|string',
            'wa_refunded_message' => 'nullable|string',
            'gemini_api_key' => 'nullable|string|max:255',
            'gemini_model' => 'nullable|string|max:255',
            'gemini_default_bg_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'gemini_use_solid_background' => 'nullable|boolean',
            'gemini_is_enabled' => 'nullable|boolean',
            'color_primary' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'color_primary_hover' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'color_secondary' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'color_alternative' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'color_dark' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'color_light_gray' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
        ]);

        $geminiMaskPlaceholder = config('aistudio.key_placeholder', '************');
        $geminiInput = $validated['gemini_api_key'] ?? null;
        $geminiKey = ($geminiInput && $geminiInput !== $geminiMaskPlaceholder) ? $geminiInput : null;
        unset($validated['gemini_api_key']);

        $geminiModelInput = $validated['gemini_model'] ?? null;
        unset($validated['gemini_model']);

        $geminiBgInput = $validated['gemini_default_bg_color'] ?? null;
        unset($validated['gemini_default_bg_color']);

        $geminiSolidInput = array_key_exists('gemini_use_solid_background', $validated)
            ? $request->boolean('gemini_use_solid_background')
            : null;
        unset($validated['gemini_use_solid_background']);

        $geminiEnabledInput = array_key_exists('gemini_is_enabled', $validated)
            ? $request->boolean('gemini_is_enabled')
            : null;
        unset($validated['gemini_is_enabled']);

        $colorKeys = [
            'color_primary',
            'color_primary_hover',
            'color_secondary',
            'color_alternative',
            'color_dark',
            'color_light_gray',
        ];

        foreach ($colorKeys as $key) {
            $hexInput = $request->input($key . '_hex');
            if ($hexInput && preg_match('/^#([A-Fa-f0-9]{6})$/', $hexInput)) {
                $validated[$key] = strtoupper($hexInput);
            }
        }

        if (!array_key_exists('active_couriers', $validated)) {
            $validated['active_couriers'] = [];
        }

        $validated['preorder_dp_required'] = $request->boolean('preorder_dp_required');

        if (array_key_exists('wa_order_message', $validated)) {
            $template = $validated['wa_order_message'] ?? '';
            $requiredTokens = [
                '{customer_name}',
                '{order_number}',
                '{items}',
                '{total_amount}',
                '{invoice_url}',
                '{store_name}',
            ];
            $missingTokens = array_filter($requiredTokens, fn ($token) => !str_contains($template, $token));
            if ($missingTokens) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors([
                        'wa_order_message' => 'Pesan wajib memuat token: ' . implode(', ', $missingTokens),
                    ]);
            }
        }

        $waTemplates = [
            'wa_dp_received_message' => [
                '{customer_name}',
                '{order_number}',
                '{dp_amount}',
                '{remaining_amount}',
                '{total_amount}',
                '{store_name}',
            ],
            'wa_paid_message' => [
                '{customer_name}',
                '{order_number}',
                '{total_amount}',
                '{store_name}',
            ],
            'wa_packed_message' => [
                '{customer_name}',
                '{order_number}',
                '{store_name}',
            ],
            'wa_shipped_message' => [
                '{customer_name}',
                '{order_number}',
                '{courier}',
                '{tracking_number}',
                '{store_name}',
            ],
            'wa_delivered_message' => [
                '{customer_name}',
                '{order_number}',
                '{store_name}',
            ],
            'wa_cancelled_message' => [
                '{customer_name}',
                '{order_number}',
                '{store_name}',
            ],
            'wa_cancelled_refund_pending_message' => [
                '{customer_name}',
                '{order_number}',
                '{refund_amount}',
                '{store_name}',
            ],
            'wa_refunded_message' => [
                '{customer_name}',
                '{order_number}',
                '{refund_amount}',
                '{store_name}',
            ],
        ];

        foreach ($waTemplates as $key => $tokens) {
            if (!array_key_exists($key, $validated)) {
                continue;
            }
            $template = $validated[$key] ?? '';
            $missingTokens = array_filter($tokens, fn ($token) => !str_contains($template, $token));
            if ($missingTokens) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors([
                        $key => 'Pesan wajib memuat token: ' . implode(', ', $missingTokens),
                    ]);
            }
        }

        foreach ($validated as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            Setting::set($key, $value);
        }

        $existing = AiIntegration::forProvider('gemini')->first();
        $shouldPersistGemini = $existing || $geminiKey || $geminiModelInput || $geminiBgInput
            || !is_null($geminiSolidInput) || !is_null($geminiEnabledInput);

        if ($shouldPersistGemini) {
            if (!$existing && !$geminiKey) {
                return redirect()->back()->withErrors([
                    'gemini_api_key' => 'Masukkan API key sebelum mengonfigurasi Gemini.',
                ]);
            }

            $payload = [
                'api_key' => $geminiKey ?? $existing?->api_key,
                'model' => $geminiModelInput ?: ($existing->model ?? 'gemini-2.0-flash-exp-image-generation'),
                'is_enabled' => $geminiEnabledInput ?? ($existing->is_enabled ?? true),
                'default_bg_color' => $geminiBgInput ?: ($existing->default_bg_color ?? '#FFFFFF'),
                'use_solid_background' => $geminiSolidInput ?? ($existing->use_solid_background ?? true),
                'metadata' => $existing->metadata ?? [],
            ];

            AiIntegration::updateOrCreate(
                ['provider' => 'gemini'],
                $payload
            );
        }

        return redirect()->back()->with('success', 'Pengaturan berhasil disimpan');
    }

    public function storeBankAccount(Request $request)
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'bank_code' => 'nullable|string|max:20',
            'account_number' => 'required|string|max:50',
            'account_name' => 'required|string|max:255',
        ]);

        BankAccount::create($validated + ['user_id' => auth()->id()]);

        return redirect()->back()->with('success', 'Rekening berhasil ditambahkan');
    }

    public function deleteBankAccount(BankAccount $account)
    {
        if ($account->user_id !== auth()->id()) {
            abort(403);
        }
        $account->delete();
        return redirect()->back()->with('success', 'Rekening berhasil dihapus');
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
