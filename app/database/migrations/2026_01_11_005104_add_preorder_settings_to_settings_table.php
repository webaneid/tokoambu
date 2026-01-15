<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = [
            // DP Required (default: true)
            [
                'key' => 'preorder_dp_required',
                'value' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Preorder DP percentage (default 30%)
            [
                'key' => 'preorder_dp_percentage',
                'value' => '30',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // DP payment deadline in days (default 7 days)
            [
                'key' => 'preorder_dp_deadline_days',
                'value' => '7',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Final payment deadline in days (default 7 days)
            [
                'key' => 'preorder_final_deadline_days',
                'value' => '7',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // WhatsApp template for DP reminder
            [
                'key' => 'preorder_wa_dp_reminder',
                'value' => "Halo *{customer_name}*,\n\nIni adalah pengingat untuk pembayaran DP pesanan preorder Anda:\n\n*Order:* {order_number}\n*Produk:* {product_name}\n*Total DP:* Rp {dp_amount}\n*Batas Waktu:* {deadline}\n\nMohon segera lakukan pembayaran sebelum batas waktu agar pesanan Anda tidak dibatalkan otomatis.\n\nTerima kasih!",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // WhatsApp template for DP confirmation
            [
                'key' => 'preorder_wa_dp_confirmed',
                'value' => "Halo *{customer_name}*,\n\nPembayaran DP Anda telah kami terima untuk:\n\n*Order:* {order_number}\n*Produk:* {product_name}\n*DP Dibayar:* Rp {dp_amount}\n\nProduk sedang kami pesan ke supplier. Kami akan menginformasikan ketika produk sudah siap.\n\nTerima kasih!",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // WhatsApp template for product ready notification
            [
                'key' => 'preorder_wa_product_ready',
                'value' => "Halo *{customer_name}*,\n\nKabar gembira! Produk preorder Anda sudah siap:\n\n*Order:* {order_number}\n*Produk:* {product_name}\n*Sisa Pembayaran:* Rp {remaining_amount}\n*Batas Waktu:* {deadline}\n\nMohon segera lakukan pelunasan agar produk dapat kami kirim.\n\nTerima kasih!",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // WhatsApp template for final payment reminder
            [
                'key' => 'preorder_wa_final_reminder',
                'value' => "Halo *{customer_name}*,\n\nIni adalah pengingat pelunasan pesanan preorder Anda:\n\n*Order:* {order_number}\n*Produk:* {product_name}\n*Sisa Pembayaran:* Rp {remaining_amount}\n*Batas Waktu:* {deadline}\n\nProduk sudah siap dan menunggu pelunasan dari Anda.\n\nTerima kasih!",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // WhatsApp template for order cancelled
            [
                'key' => 'preorder_wa_cancelled',
                'value' => "Halo *{customer_name}*,\n\nMohon maaf, pesanan preorder Anda telah dibatalkan karena melewati batas waktu pembayaran:\n\n*Order:* {order_number}\n*Produk:* {product_name}\n\nJika Anda masih berminat, silakan buat pesanan baru.\n\nTerima kasih atas pengertiannya.",
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'preorder_dp_required',
            'preorder_dp_percentage',
            'preorder_dp_deadline_days',
            'preorder_final_deadline_days',
            'preorder_wa_dp_reminder',
            'preorder_wa_dp_confirmed',
            'preorder_wa_product_ready',
            'preorder_wa_final_reminder',
            'preorder_wa_cancelled',
        ])->delete();
    }
};
