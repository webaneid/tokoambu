<?php

namespace App\Http\Controllers;

use App\Domain\Inventory\Services\InventoryService;
use App\Models\Payment;
use App\Models\Order;
use App\Models\LedgerEntry;
use App\Models\FinancialCategory;
use App\Models\Media;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with('order.customer');

        $allowedSorts = ['order_number', 'customer', 'amount', 'status', 'paid_at'];
        $sort = $request->query('sort', 'paid_at');
        $direction = $request->query('direction', 'desc');

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'paid_at';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        if ($sort === 'order_number') {
            $query->join('orders', 'orders.id', '=', 'payments.order_id')
                ->select('payments.*')
                ->orderBy('orders.order_number', $direction);
        } elseif ($sort === 'customer') {
            $query->join('orders', 'orders.id', '=', 'payments.order_id')
                ->join('customers', 'customers.id', '=', 'orders.customer_id')
                ->select('payments.*')
                ->orderBy('customers.name', $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        $payments = $query->paginate(15)->withQueryString();
        return view('payments.index', compact('payments'));
    }

    public function create(Request $request)
    {
        $orders = Order::query()
            ->whereNotIn('status', ['paid', 'done'])
            ->whereColumn('total_amount', '>', 'paid_amount')
            ->whereDoesntHave('payments', function ($query) {
                $query->where('status', 'pending');
            })
            ->get();
        $selectedOrder = null;
        $remainingAmount = null;
        $minimumDp = null;
        $orderOptions = $orders->map(function ($order) {
            $remaining = max(0, $order->total_amount - $order->payments()->where('status', 'verified')->sum('amount'));
            return [
                'id' => $order->id,
                'label' => $order->order_number . ' - ' . ($order->customer->name ?? '-') . ' (Rp ' . number_format($order->total_amount, 0, ',', '.') . ')',
                'search' => strtolower($order->order_number . ' ' . ($order->customer->name ?? '') . ' ' . $order->id),
                'remaining' => $remaining,
            ];
        })->values();

        if ($request->has('order_id')) {
            $selectedOrder = $orders->firstWhere('id', (int) $request->order_id);
            if ($selectedOrder) {
                $remainingAmount = max(0, $selectedOrder->total_amount - $selectedOrder->paid_amount);

                // Calculate minimum DP for preorder
                if ($selectedOrder->type === 'preorder' && $selectedOrder->status === 'waiting_dp') {
                    $dpPercentage = Setting::getPreorderDpPercentage();
                    $minimumDp = ($selectedOrder->total_amount * $dpPercentage) / 100;
                }
            }
        }

        $shopBankAccounts = \App\Models\BankAccount::where('user_id', auth()->id())->get();

        return view('payments.create', compact('orders', 'selectedOrder', 'remainingAmount', 'orderOptions', 'minimumDp', 'shopBankAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,transfer,check,other',
            'sender_name' => 'nullable|string|max:255',
            'sender_bank' => 'nullable|string|max:255',
            'shop_bank_account_id' => 'nullable|exists:bank_accounts,id',
            'notes' => 'nullable|string',
            'payment_media_id' => 'nullable|exists:media,id',
        ]);

        // Validate minimum DP for preorder
        $order = Order::findOrFail($validated['order_id']);
        if ($order->type === 'preorder' && $order->status === 'waiting_dp') {
            $dpPercentage = Setting::getPreorderDpPercentage();
            $minimumDp = ($order->total_amount * $dpPercentage) / 100;

            if ($validated['amount'] < $minimumDp) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors([
                        'amount' => "Minimal transfer {$dpPercentage}% yaitu Rp " . number_format($minimumDp, 0, ',', '.')
                    ]);
            }
        }

        $payment = Payment::create([
            'order_id' => $validated['order_id'],
            'amount' => $validated['amount'],
            'method' => $validated['method'],
            'sender_name' => $validated['sender_name'] ?? null,
            'sender_bank' => $validated['sender_bank'] ?? null,
            'shop_bank_account_id' => $validated['shop_bank_account_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
            'paid_at' => now(),
        ]);

        if (!empty($validated['payment_media_id'])) {
            $media = Media::find($validated['payment_media_id']);
            if (!$media || $media->type !== 'payment_proof') {
                return redirect()->back()->withErrors([
                    'payment_media_id' => 'Lampiran bukan bukti pembayaran yang valid.',
                ])->withInput();
            }
            $media->payment_id = $payment->id;
            $media->metadata = array_merge($media->metadata ?? [], [
                'order_id' => $payment->order_id,
            ]);
            $media->save();
        }

        return redirect()->route('payments.show', $payment)->with('success', 'Pembayaran berhasil dicatat');
    }

    public function show(Payment $payment)
    {
        $payment->load('order.customer', 'paymentProofs', 'attachments', 'shopBankAccount');
        return view('payments.show', compact('payment'));
    }

    public function verify(Payment $payment)
    {
        if ($payment->status === 'verified') {
            return redirect()->route('payments.show', $payment)->with('success', 'Pembayaran sudah diverifikasi');
        }

        DB::transaction(function () use ($payment) {
            $payment->update(['status' => 'verified']);

            // Update order status berdasarkan total pembayaran yang terverifikasi
            $order = $payment->order;
            $totalPaid = $order->payments()->where('status', 'verified')->sum('amount');
            $order->update(['paid_amount' => $totalPaid]);

            // Handle preorder DP payment - reserve stock
            if ($order->isPreorder() && $order->status === 'waiting_dp' && $totalPaid >= $order->dp_amount) {
                $this->handleDpPaid($order);
            }
            // Handle final payment for preorder
            elseif ($order->isPreorder() && in_array($order->status, ['product_ready', 'waiting_payment']) && $totalPaid >= $order->total_amount) {
                $order->update(['status' => 'paid']);
            }
            // Regular order payment
            elseif (!$order->isPreorder()) {
                if ($totalPaid >= $order->total_amount) {
                    $order->update(['status' => 'paid']);
                } elseif ($totalPaid > 0) {
                    $order->update(['status' => 'dp_paid']);
                }
            }

            $this->createLedgerForPayment($payment);
        });

        return redirect()->route('payments.show', $payment)->with('success', 'Pembayaran berhasil diverifikasi dan Order sudah diupdate');
    }

    /**
     * Handle DP paid for preorder - reserve stock
     */
    private function handleDpPaid(Order $order): void
    {
        $inventoryService = new InventoryService();
        $defaultLocationId = 1; // Adjust if you have different logic

        foreach ($order->items as $item) {
            try {
                $inventoryService->reserveStock(
                    $item->product_id,
                    $defaultLocationId,
                    $item->quantity,
                    [
                        'product_variant_id' => $item->product_variant_id,
                        'reference_type' => Order::class,
                        'reference_id' => $order->id,
                        'reason' => 'Preorder DP paid',
                        'notes' => "Order {$order->order_number} DP paid, stock reserved",
                    ]
                );
            } catch (\Exception $e) {
                // Log error but don't block payment verification
                \Log::error("Failed to reserve stock for order {$order->order_number}: {$e->getMessage()}");
            }
        }

        $order->update([
            'status' => 'dp_paid',
            'dp_paid_at' => now(),
        ]);
    }

    private function createLedgerForPayment(Payment $payment): void
    {
        $exists = LedgerEntry::where('source_type', 'payment')
            ->where('source_id', $payment->id)
            ->exists();
        if ($exists) {
            return;
        }

        $category = FinancialCategory::firstOrCreate(
            ['name' => 'Order', 'type' => 'income'],
            ['is_active' => true]
        );

        LedgerEntry::create([
            'entry_date' => $payment->paid_at ? $payment->paid_at->toDateString() : now()->toDateString(),
            'type' => 'income',
            'category_id' => $category->id,
            'description' => 'Pembayaran order #' . $payment->order->order_number,
            'amount' => $payment->amount,
            'reference_id' => $payment->order_id,
            'reference_type' => 'order',
            'source_type' => 'payment',
            'source_id' => $payment->id,
            'created_by' => Auth::id(),
        ]);
    }
}
