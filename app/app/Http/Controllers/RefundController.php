<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use App\Models\Order;
use App\Models\LedgerEntry;
use App\Models\FinancialCategory;
use App\Models\BankAccount;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RefundController extends Controller
{
    public function index(Request $request)
    {
        $query = Refund::with(['order.customer', 'createdBy', 'approvedBy']);

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('reason', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($q) use ($search) {
                        $q->where('order_number', 'like', "%{$search}%");
                    });
            });
        }

        $status = $request->query('status');
        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $refunds = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Get orders with cancelled_refund_pending status (not yet processed as refund)
        $pendingRefundOrders = Order::where('status', 'cancelled_refund_pending')
            ->with(['customer', 'payments'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('refunds.index', compact('refunds', 'pendingRefundOrders'));
    }

    public function create(Request $request)
    {
        // Get order_id from query param if provided
        $preselectedOrderId = $request->query('order_id');

        // Only show cancelled orders (without existing refund) that have payments
        $orders = Order::whereIn('status', ['cancelled', 'cancelled_refund_pending'])
            ->with(['customer', 'payments'])
            ->whereHas('payments', function ($query) {
                $query->where('status', 'verified');
            })
            ->whereDoesntHave('refunds')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                // Calculate total paid amount
                $paidAmount = $order->payments()
                    ->where('status', 'verified')
                    ->sum('amount');

                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_id' => $order->customer_id,
                    'customer_name' => $order->customer->name ?? '-',
                    'paid_amount' => $paidAmount,
                    'paid_amount_formatted' => number_format($paidAmount, 0, ',', '.'),
                ];
            });

        // Load bank accounts
        $userBankAccounts = BankAccount::whereNotNull('user_id')->get();
        $customerBankAccounts = BankAccount::whereNotNull('customer_id')
            ->with('customer:id,name')
            ->get()
            ->map(function ($acc) {
                return [
                    'id' => $acc->id,
                    'customer_id' => $acc->customer_id,
                    'bank_name' => $acc->bank_name,
                    'account_number' => $acc->account_number,
                    'account_name' => $acc->account_name,
                ];
            });

        return view('refunds.create', compact('orders', 'userBankAccounts', 'customerBankAccounts', 'preselectedOrderId'));
    }

    public function store(Request $request)
    {
        $methods = ['cash', 'debit', 'credit_card', 'transfer', 'qris'];
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => ['nullable', Rule::in($methods)],
            'customer_bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'shop_bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'payment_media_id' => ['nullable', 'exists:media,id'],
            'transfer_fee' => ['nullable', 'numeric', 'min:0'],
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Validate media if provided
        if (!empty($validated['payment_media_id'])) {
            $media = Media::find($validated['payment_media_id']);
            if (!$media || $media->type !== 'payment_proof') {
                return back()->withErrors(['payment_media_id' => 'Lampiran bukan bukti transfer yang valid.'])->withInput();
            }
        }

        // Check if order already has a refund
        $order = Order::find($validated['order_id']);
        if ($order->refunds()->exists()) {
            return back()->withErrors(['order_id' => 'Order ini sudah memiliki refund request.'])->withInput();
        }

        $validated['status'] = 'pending';
        $validated['created_by'] = Auth::id();

        DB::transaction(function () use ($validated, $order) {
            $refund = Refund::create($validated);

            // Update order status to cancelled_refund_pending
            if ($order && in_array($order->status, ['cancelled', 'cancelled_refund_pending'])) {
                $order->update(['status' => 'cancelled_refund_pending']);
            }
        });

        return redirect()->route('refunds.index')->with('success', 'Refund request berhasil dibuat');
    }

    public function show(Refund $refund)
    {
        $refund->load([
            'order.customer',
            'createdBy',
            'approvedBy',
            'ledgerEntry',
            'customerBankAccount',
            'shopBankAccount',
            'paymentMedia'
        ]);

        return view('refunds.show', compact('refund'));
    }

    public function approve(Refund $refund)
    {
        if ($refund->status !== 'pending') {
            return back()->with('error', 'Refund hanya bisa diapprove jika status pending');
        }

        DB::transaction(function () use ($refund) {
            // Find or create refund category
            $refundCategory = FinancialCategory::firstOrCreate(
                ['name' => 'Refund', 'type' => 'expense'],
                ['is_active' => true]
            );

            // Create ledger entry for refund
            // For refund: toko (payer) -> customer (payee)
            $ledgerEntry = LedgerEntry::create([
                'type' => 'expense',
                'category_id' => $refundCategory->id,
                'description' => "Refund untuk Order #{$refund->order->order_number}",
                'entry_date' => now(),
                'amount' => $refund->amount,
                'payment_method' => $refund->payment_method,
                'payee_bank_account_id' => $refund->customer_bank_account_id,
                'payer_bank_account_id' => $refund->shop_bank_account_id,
                'payment_media_id' => $refund->payment_media_id,
                'notes' => $refund->reason . ($refund->notes ? "\n\n" . $refund->notes : ''),
                'source_type' => 'App\\Models\\Refund',
                'source_id' => $refund->id,
                'created_by' => Auth::id(),
            ]);

            // Handle transfer fee if provided
            if ($refund->transfer_fee > 0) {
                $this->createLedgerForTransferFee($ledgerEntry, $refund->transfer_fee);
            }

            // Update refund status
            $refund->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'ledger_entry_id' => $ledgerEntry->id,
            ]);

            // Update order status to refunded
            $refund->order->update([
                'status' => 'refunded',
                'refund_amount' => $refund->amount,
                'refunded_at' => now(),
            ]);
        });

        return redirect()->route('refunds.show', $refund)->with('success', 'Refund berhasil diapprove dan ledger entry telah dibuat');
    }

    private function createLedgerForTransferFee(LedgerEntry $parentEntry, float $fee): ?LedgerEntry
    {
        $category = FinancialCategory::firstOrCreate(
            ['name' => 'Biaya Transfer', 'type' => 'expense'],
            ['is_active' => true, 'is_default' => true]
        );

        return LedgerEntry::create([
            'entry_date' => $parentEntry->entry_date,
            'type' => 'expense',
            'category_id' => $category->id,
            'description' => 'Biaya transfer - ' . $parentEntry->description,
            'amount' => $fee,
            'payment_method' => $parentEntry->payment_method,
            'payer_bank_account_id' => $parentEntry->payer_bank_account_id,
            'source_type' => 'ledger_transfer_fee',
            'source_id' => $parentEntry->id,
            'created_by' => Auth::id(),
        ]);
    }

    public function reject(Request $request, Refund $refund)
    {
        if ($refund->status !== 'pending') {
            return back()->with('error', 'Refund hanya bisa direject jika status pending');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $refund->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()->route('refunds.show', $refund)->with('success', 'Refund berhasil direject');
    }

    public function destroy(Refund $refund)
    {
        if ($refund->status !== 'pending') {
            return back()->with('error', 'Hanya refund dengan status pending yang bisa dihapus');
        }

        $refund->delete();

        return redirect()->route('refunds.index')->with('success', 'Refund berhasil dihapus');
    }
}
