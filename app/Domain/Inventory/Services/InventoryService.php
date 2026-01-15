<?php

namespace App\Domain\Inventory\Services;

use App\Models\InventoryBalance;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Notifications\PreorderProductReadyNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function receive(int $productId, int $toLocationId, float $qty, array $context = []): StockMovement
    {
        return $this->applyMovement([
            'product_id' => $productId,
            'product_variant_id' => $context['product_variant_id'] ?? null,
            'from_location_id' => null,
            'to_location_id' => $toLocationId,
            'qty_change' => $qty,
            'movement_type' => 'receive',
            'reason' => $context['reason'] ?? null,
            'notes' => $context['notes'] ?? null,
            'reference_type' => $context['reference_type'] ?? null,
            'reference_id' => $context['reference_id'] ?? null,
            'movement_date' => $context['movement_date'] ?? now(),
            'user_id' => $context['user_id'] ?? null,
        ]);
    }

    public function ship(int $productId, int $fromLocationId, float $qty, array $context = []): StockMovement
    {
        return $this->applyMovement([
            'product_id' => $productId,
            'product_variant_id' => $context['product_variant_id'] ?? null,
            'from_location_id' => $fromLocationId,
            'to_location_id' => null,
            'qty_change' => -1 * $qty,
            'movement_type' => 'ship',
            'reason' => $context['reason'] ?? null,
            'notes' => $context['notes'] ?? null,
            'reference_type' => $context['reference_type'] ?? null,
            'reference_id' => $context['reference_id'] ?? null,
            'movement_date' => $context['movement_date'] ?? now(),
            'user_id' => $context['user_id'] ?? null,
        ]);
    }

    public function transfer(int $productId, int $fromLocationId, int $toLocationId, float $qty, array $context = []): StockMovement
    {
        return DB::transaction(function () use ($productId, $fromLocationId, $toLocationId, $qty, $context) {
            $variantId = $context['product_variant_id'] ?? null;

            // decrease from
            $this->applyMovement([
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'from_location_id' => $fromLocationId,
                'to_location_id' => null,
                'qty_change' => -1 * $qty,
                'movement_type' => 'transfer',
                'reason' => $context['reason'] ?? null,
                'notes' => $context['notes'] ?? null,
                'reference_type' => $context['reference_type'] ?? null,
                'reference_id' => $context['reference_id'] ?? null,
                'movement_date' => $context['movement_date'] ?? now(),
                'user_id' => $context['user_id'] ?? null,
                'skip_transaction' => true,
            ]);

            // increase to
            return $this->applyMovement([
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'from_location_id' => null,
                'to_location_id' => $toLocationId,
                'qty_change' => $qty,
                'movement_type' => 'transfer',
                'reason' => $context['reason'] ?? null,
                'notes' => $context['notes'] ?? null,
                'reference_type' => $context['reference_type'] ?? null,
                'reference_id' => $context['reference_id'] ?? null,
                'movement_date' => $context['movement_date'] ?? now(),
                'user_id' => $context['user_id'] ?? null,
                'skip_transaction' => true,
            ]);
        });
    }

    public function adjust(int $productId, int $locationId, float $qtyChange, string $reason, array $context = []): StockMovement
    {
        $variantId = $context['product_variant_id'] ?? null;

        $movement = $this->applyMovement([
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'from_location_id' => $qtyChange < 0 ? $locationId : null,
            'to_location_id' => $qtyChange > 0 ? $locationId : null,
            'qty_change' => $qtyChange,
            'movement_type' => 'adjust',
            'reason' => $reason,
            'notes' => $context['notes'] ?? null,
            'reference_type' => $context['reference_type'] ?? null,
            'reference_id' => $context['reference_id'] ?? null,
            'movement_date' => $context['movement_date'] ?? now(),
            'user_id' => $context['user_id'] ?? null,
        ]);

        if ($qtyChange > 0 && $movement->wasRecentlyCreated) {
            $this->allocatePreorderBacklog($productId, $qtyChange, $variantId);
        }

        return $movement;
    }

    public function opnameAdjustment(int $productId, int $locationId, float $systemQty, float $physicalQty, array $context = []): ?StockMovement
    {
        $delta = $physicalQty - $systemQty;
        if (abs($delta) < 0.0001) {
            return null; // no movement needed
        }

        return $this->adjust($productId, $locationId, $delta, 'stock_opname', $context);
    }

    public function allocatePreorderBacklog(int $productId, float $qty, ?int $variantId = null): float
    {
        if ($qty <= 0) {
            return 0;
        }

        return DB::transaction(function () use ($productId, $qty, $variantId) {
            $remaining = $qty;
            $items = OrderItem::query()
                ->select('order_items.*')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('order_items.product_id', $productId)
                ->when($variantId, fn($q) => $q->where('order_items.product_variant_id', $variantId))
                ->where('order_items.is_preorder', true)
                ->whereColumn('order_items.preorder_allocated_qty', '<', 'order_items.quantity')
                ->orderBy('orders.created_at')
                ->orderBy('order_items.id')
                ->lockForUpdate()
                ->get();

            foreach ($items as $item) {
                if ($remaining <= 0) {
                    break;
                }

                $needed = (float) $item->quantity - (float) $item->preorder_allocated_qty;
                if ($needed <= 0) {
                    continue;
                }

                $allocate = min($needed, $remaining);
                $item->preorder_allocated_qty = (float) $item->preorder_allocated_qty + $allocate;
                if ($item->preorder_allocated_qty >= (float) $item->quantity && !$item->preorder_ready_at) {
                    $item->preorder_ready_at = now();
                }
                $item->save();

                $this->maybeMarkOrderReady($item->order);

                $remaining -= $allocate;
            }

            return $qty - $remaining;
        });
    }

    protected function maybeMarkOrderReady(?Order $order): void
    {
        if (!$order || $order->status !== 'dp_paid') {
            return;
        }

        $hasPendingItems = $order->items()
            ->where('is_preorder', true)
            ->whereColumn('preorder_allocated_qty', '<', 'quantity')
            ->exists();

        if ($hasPendingItems) {
            return;
        }

        $deadline = now()->addDays(Setting::getPreorderFinalDeadlineDays());
        $order->update([
            'status' => 'product_ready',
            'final_payment_deadline' => $deadline,
        ]);

        $order->customer?->notify(new PreorderProductReadyNotification(
            $order->id,
            $order->order_number,
            $deadline->format('d/m/Y H:i'),
            route('customer.payment.select', $order->id)
        ));
    }

    /**
     * Reserve stock for preorder when DP is paid
     */
    public function reserveStock(int $productId, int $locationId, float $qty, array $context = []): void
    {
        DB::transaction(function () use ($productId, $locationId, $qty, $context) {
            $variantId = $context['product_variant_id'] ?? null;

            $balance = InventoryBalance::where('product_id', $productId)
                ->when($variantId !== null, fn($q) => $q->where('product_variant_id', $variantId), fn($q) => $q->whereNull('product_variant_id'))
                ->where('location_id', $locationId)
                ->lockForUpdate()
                ->first();

            if (!$balance) {
                throw ValidationException::withMessages(['stock' => 'Stock tidak ditemukan untuk produk ini.']);
            }

            // Check if available stock is enough (on_hand - reserved - reserved_qty)
            $available = $balance->qty_on_hand - $balance->qty_reserved - $balance->reserved_qty;
            if ($available < $qty) {
                throw ValidationException::withMessages(['stock' => "Stok tersedia tidak cukup. Tersedia: {$available}, Dibutuhkan: {$qty}"]);
            }

            $balance->reserved_qty = $balance->reserved_qty + $qty;
            $balance->save();

            // Create stock movement for audit trail
            StockMovement::create([
                'movement_date' => now(),
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'from_location_id' => $locationId,
                'to_location_id' => null,
                'qty' => $qty,
                'movement_type' => 'reserve',
                'reference_type' => $context['reference_type'] ?? null,
                'reference_id' => $context['reference_id'] ?? null,
                'reason' => $context['reason'] ?? 'Preorder DP paid',
                'notes' => $context['notes'] ?? null,
                'created_by' => $context['user_id'] ?? auth()->id(),
            ]);
        });
    }

    /**
     * Unreserve stock (when preorder cancelled or expired)
     */
    public function unreserveStock(int $productId, int $locationId, float $qty, array $context = []): void
    {
        DB::transaction(function () use ($productId, $locationId, $qty, $context) {
            $variantId = $context['product_variant_id'] ?? null;

            $balance = InventoryBalance::where('product_id', $productId)
                ->when($variantId !== null, fn($q) => $q->where('product_variant_id', $variantId), fn($q) => $q->whereNull('product_variant_id'))
                ->where('location_id', $locationId)
                ->lockForUpdate()
                ->first();

            if (!$balance) {
                return; // nothing to unreserve
            }

            $balance->reserved_qty = max(0, $balance->reserved_qty - $qty);
            $balance->save();

            // Create stock movement for audit trail
            StockMovement::create([
                'movement_date' => now(),
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'from_location_id' => null,
                'to_location_id' => $locationId,
                'qty' => $qty,
                'movement_type' => 'unreserve',
                'reference_type' => $context['reference_type'] ?? null,
                'reference_id' => $context['reference_id'] ?? null,
                'reason' => $context['reason'] ?? 'Preorder cancelled/expired',
                'notes' => $context['notes'] ?? null,
                'created_by' => $context['user_id'] ?? auth()->id(),
            ]);
        });
    }

    private function applyMovement(array $data): StockMovement
    {
        return DB::transaction(function () use ($data) {
            $qtyChange = $data['qty_change'];
            $productId = $data['product_id'];
            $variantId = $data['product_variant_id'] ?? null;
            $movementType = $data['movement_type'];
            $referenceType = $data['reference_type'] ?? null;
            $referenceId = $data['reference_id'] ?? null;
            $fromLocationId = $data['from_location_id'] ?? null;
            $toLocationId = $data['to_location_id'] ?? null;

            // Idempotency guard: prevent duplicate movement for same reference/product/variant/location/type/qty
            $existing = StockMovement::query()
                ->where('movement_type', $movementType)
                ->where('product_id', $productId)
                ->when($variantId !== null, fn($q) => $q->where('product_variant_id', $variantId), fn($q) => $q->whereNull('product_variant_id'))
                ->where('reference_type', $referenceType)
                ->where('reference_id', $referenceId)
                ->when($fromLocationId, fn($q) => $q->where('from_location_id', $fromLocationId))
                ->when($toLocationId, fn($q) => $q->where('to_location_id', $toLocationId))
                ->where('qty', abs($qtyChange))
                ->first();

            if ($existing) {
                return $existing;
            }

            // Update balance with locking
            if ($qtyChange !== 0) {
                $locationId = $qtyChange < 0 ? $fromLocationId : $toLocationId;
                if (!$locationId) {
                    throw ValidationException::withMessages(['location_id' => 'Lokasi wajib diisi untuk perubahan stok.']);
                }

                $balance = InventoryBalance::where('product_id', $productId)
                    ->when($variantId !== null, fn($q) => $q->where('product_variant_id', $variantId), fn($q) => $q->whereNull('product_variant_id'))
                    ->where('location_id', $locationId)
                    ->lockForUpdate()
                    ->first();

                if (!$balance) {
                    $balance = new InventoryBalance([
                        'product_id' => $productId,
                        'product_variant_id' => $variantId,
                        'location_id' => $locationId,
                        'qty_on_hand' => 0,
                        'qty_reserved' => 0,
                    ]);
                }

                // Validate sufficient stock on hand when decreasing
                if ($qtyChange < 0 && ($balance->qty_on_hand + $qtyChange) < 0) {
                    throw ValidationException::withMessages(['qty' => 'Stok tidak mencukupi di lokasi ini.']);
                }

                $balance->qty_on_hand = $balance->qty_on_hand + $qtyChange;
                $balance->save();
            }

            return StockMovement::create([
                'movement_date' => $data['movement_date'] ?? now(),
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'from_location_id' => $fromLocationId,
                'to_location_id' => $toLocationId,
                'qty' => abs($qtyChange),
                'movement_type' => $movementType,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $data['user_id'] ?? auth()->id(),
            ]);
        }, attempts: 1); // keep single attempt to avoid duplicate creation
    }
}
