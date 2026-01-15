<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Promotion;
use App\Models\PromotionUsage;
use Illuminate\Support\Collection;

class CouponService
{
    /**
     * Validate and apply coupon to cart totals.
     *
     * @return array{valid: bool, message: ?string, discount: float, total: float, promotion: ?Promotion, coupon: ?Coupon}
     */
    public function applyCoupon(
        ?string $code,
        Collection $items,
        float $subtotal,
        ?Customer $customer,
        array $flashSalePromotionIds = [],
        float $shipping = 0.0
    ): array {
        $code = trim((string) $code);
        if ($code === '') {
            return [
                'valid' => false,
                'message' => null,
                'discount' => 0.0,
                'total' => $subtotal + $shipping,
                'promotion' => null,
                'coupon' => null,
            ];
        }

        $coupon = Coupon::query()
            ->where('code', strtoupper($code))
            ->with('promotion.benefits')
            ->first();

        if (! $coupon || ! $coupon->promotion || $coupon->promotion->type !== 'coupon') {
            return $this->invalidResult('Kode kupon tidak ditemukan.', $subtotal, $shipping);
        }

        $promotion = $coupon->promotion;
        $now = now();

        if ($promotion->status !== 'active') {
            return $this->invalidResult('Kupon sedang tidak aktif.', $subtotal, $shipping);
        }

        if ($promotion->start_at && $promotion->start_at->isAfter($now)) {
            return $this->invalidResult('Kupon belum dimulai.', $subtotal, $shipping);
        }

        if ($promotion->end_at && $promotion->end_at->isBefore($now)) {
            return $this->invalidResult('Kupon sudah berakhir.', $subtotal, $shipping);
        }

        if ($coupon->min_order_amount !== null && $subtotal < (float) $coupon->min_order_amount) {
            return $this->invalidResult('Total belanja belum memenuhi minimal kupon.', $subtotal, $shipping);
        }

        if ($coupon->first_purchase_only && $customer && $customer->orders()->exists()) {
            return $this->invalidResult('Kupon hanya berlaku untuk pembelian pertama.', $subtotal, $shipping);
        }

        if ($coupon->global_limit !== null) {
            $used = PromotionUsage::where('promotion_id', $promotion->id)->count();
            if ($used >= $coupon->global_limit) {
                return $this->invalidResult('Kupon sudah mencapai batas pemakaian.', $subtotal, $shipping);
            }
        }

        if ($coupon->per_user_limit !== null && $customer) {
            $used = PromotionUsage::where('promotion_id', $promotion->id)
                ->where('user_id', $customer->id)
                ->count();
            if ($used >= $coupon->per_user_limit) {
                return $this->invalidResult('Kupon sudah mencapai batas pemakaian untuk akun ini.', $subtotal, $shipping);
            }
        }

        $rules = $promotion->rules ?? [];
        $totalQty = $items->sum('quantity');

        if (! empty($rules['min_qty']) && $totalQty < (int) $rules['min_qty']) {
            return $this->invalidResult('Jumlah item belum memenuhi minimal kupon.', $subtotal, $shipping);
        }

        if (! empty($rules['max_qty']) && $totalQty > (int) $rules['max_qty']) {
            return $this->invalidResult('Jumlah item melebihi batas kupon.', $subtotal, $shipping);
        }

        if (! empty($flashSalePromotionIds)) {
            if (! $promotion->stackable) {
                return $this->invalidResult('Promo tidak bisa digabung dengan Flash Sale.', $subtotal, $shipping);
            }

            $nonStackableFlash = Promotion::whereIn('id', $flashSalePromotionIds)
                ->where('stackable', false)
                ->exists();
            if ($nonStackableFlash) {
                return $this->invalidResult('Promo Flash Sale tidak bisa digabung dengan kupon.', $subtotal, $shipping);
            }
        }

        $benefit = $promotion->benefits->first();
        if (! $benefit) {
            return $this->invalidResult('Benefit kupon tidak ditemukan.', $subtotal, $shipping);
        }

        $discount = 0.0;
        if ($benefit->benefit_type === 'free_shipping' || $benefit->apply_scope === 'shipping') {
            $discount = min($shipping, (float) $shipping);
        } else {
            $discount = $this->applyBenefit(
                $benefit->benefit_type,
                (float) $benefit->value,
                $benefit->max_discount ? (float) $benefit->max_discount : null,
                $subtotal
            );
        }

        $discount = min($discount, $subtotal + $shipping);
        $total = max(0.0, $subtotal + $shipping - $discount);

        return [
            'valid' => true,
            'message' => $benefit->benefit_type === 'free_shipping' ? 'Kupon gratis ongkir akan diterapkan saat checkout.' : null,
            'discount' => $discount,
            'total' => $total,
            'promotion' => $promotion,
            'coupon' => $coupon,
        ];
    }

    private function invalidResult(string $message, float $subtotal, float $shipping): array
    {
        return [
            'valid' => false,
            'message' => $message,
            'discount' => 0.0,
            'total' => $subtotal + $shipping,
            'promotion' => null,
            'coupon' => null,
        ];
    }

    private function applyBenefit(string $type, float $value, ?float $maxDiscount, float $base): float
    {
        $discount = 0.0;

        if ($type === 'percent_off') {
            $discount = $base * ($value / 100);
        } elseif ($type === 'amount_off') {
            $discount = $value;
        } elseif ($type === 'fixed_price') {
            return max(0.0, min($base, $value));
        }

        if ($maxDiscount !== null && $discount > $maxDiscount) {
            $discount = $maxDiscount;
        }

        return max(0.0, $discount);
    }
}
