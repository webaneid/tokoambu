<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductCategory;
use App\Models\ProductSupplierPrice;
use App\Models\BankAccount;
use App\Models\Setting;
use App\Models\PurchasePayment;
use App\Models\LedgerEntry;
use App\Models\FinancialCategory;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::query()->with(['supplier.bankAccounts', 'items.product', 'payments']);
        $search = trim((string) $request->query('q', ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('purchase_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                        $supplierQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $allowedSorts = ['purchase_number', 'supplier', 'total_amount', 'status', 'payment_status', 'created_at'];
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        if ($sort === 'supplier') {
            $query->leftJoin('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
                ->select('purchases.*')
                ->orderBy('suppliers.name', $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        $purchases = $query->paginate(15)->withQueryString();
        $userBankAccounts = auth()->check()
            ? BankAccount::where('user_id', auth()->id())->whereNull('supplier_id')->get()
            : collect();

        return view('purchases.index', compact('purchases', 'userBankAccounts'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        $categories = ProductCategory::orderBy('name')->get();
        $priceMap = ProductSupplierPrice::all()
            ->groupBy('supplier_id')
            ->map(fn($rows) => $rows->keyBy('product_id')->map->last_cost);
        $minMargin = (float) Setting::get('min_margin_percent', 20);

        return view('purchases.create', compact('suppliers', 'products', 'categories', 'priceMap', 'minMargin'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'status' => 'nullable|in:draft,ordered,received,cancelled',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $purchase = Purchase::create([
            'purchase_number' => 'PUR-' . date('YmdHis'),
            'supplier_id' => $validated['supplier_id'],
            'status' => $validated['status'] ?? 'draft',
            'payment_status' => 'pending',
            'notes' => $validated['notes'] ?? null,
            'total_amount' => 0,
        ]);

        $totalAmount = 0;
        foreach ($validated['items'] as $item) {
            $subtotal = $item['quantity'] * $item['unit_price'];
            $purchase->items()->create([
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['product_variant_id'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $subtotal,
            ]);
            $totalAmount += $subtotal;

            // Update price list per supplier
            if ($validated['supplier_id']) {
                $existing = ProductSupplierPrice::where('product_id', $item['product_id'])
                    ->where('supplier_id', $validated['supplier_id'])
                    ->first();
                $avg = $existing
                    ? (($existing->avg_cost ?? $existing->last_cost) + $item['unit_price']) / 2
                    : $item['unit_price'];

                $priceRow = ProductSupplierPrice::updateOrCreate(
                    [
                        'product_id' => $item['product_id'],
                        'supplier_id' => $validated['supplier_id'],
                    ],
                    [
                        'last_cost' => $item['unit_price'],
                        'avg_cost' => $avg,
                        'last_purchase_at' => now(),
                    ]
                );

                // Update cost_price: If variant exists, update variant's cost_price; otherwise update product's cost_price
                if (!empty($item['product_variant_id'])) {
                    $variant = ProductVariant::find($item['product_variant_id']);
                    if ($variant && $variant->cost_price != $item['unit_price']) {
                        $variant->update(['cost_price' => $item['unit_price']]);
                    }
                } else {
                    $itemProduct = Product::find($item['product_id']);
                    if ($itemProduct && $itemProduct->cost_price != $priceRow->last_cost) {
                        $itemProduct->update(['cost_price' => $priceRow->last_cost]);
                    }
                }
            }
        }

        $purchase->update(['total_amount' => $totalAmount]);

        return redirect()->route('purchases.show', $purchase)->with('success', 'Pembelian berhasil dibuat');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('supplier', 'items.product', 'items.productVariant');
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        return view('purchases.edit', compact('purchase', 'suppliers', 'products'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        if ($purchase->status === 'received') {
            return redirect()->route('purchases.show', $purchase)->withErrors(['status' => 'PO sudah diterima dan tidak dapat diubah.']);
        }

        $validated = $request->validate([
            'status' => 'required|in:draft,ordered,received,cancelled',
            'notes' => 'nullable|string',
        ]);

        $purchase->update($validated);

        return redirect()->route('purchases.show', $purchase)->with('success', 'Pembelian berhasil diperbarui');
    }

    public function destroy(Purchase $purchase)
    {
        $purchase->items()->delete();
        $purchase->delete();
        return redirect()->route('purchases.index')->with('success', 'Pembelian berhasil dihapus');
    }

    public function pay(Request $request, Purchase $purchase)
    {
        $methods = ['cash', 'debit', 'credit_card', 'transfer', 'qris'];
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transfer_fee' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::in($methods)],
            'payment_date' => ['nullable', 'date'],
            'supplier_bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'payer_bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'notes' => ['nullable', 'string'],
            'payment_media_id' => ['nullable', 'exists:media,id'],
        ]);

        // Require supplier bank for transfer-like payments
        if (in_array($validated['payment_method'], ['debit', 'transfer', 'qris']) && empty($validated['supplier_bank_account_id'])) {
            return response()->json(['message' => 'Pilih rekening tujuan (supplier).'], 422);
        }

        // Require payer bank for all non-cash
        if ($validated['payment_method'] !== 'cash' && empty($validated['payer_bank_account_id'])) {
            return response()->json(['message' => 'Pilih rekening asal.'], 422);
        }

        // Ensure supplier bank belongs to this supplier
        if (!empty($validated['supplier_bank_account_id'])) {
            $belongs = BankAccount::where('id', $validated['supplier_bank_account_id'])
                ->where('supplier_id', $purchase->supplier_id)
                ->exists();
            if (!$belongs) {
                return response()->json(['message' => 'Rekening tujuan tidak valid untuk supplier ini.'], 422);
            }
        }

        // Ensure payer bank belongs to current user
        if (!empty($validated['payer_bank_account_id'])) {
            $belongsUser = BankAccount::where('id', $validated['payer_bank_account_id'])
                ->where('user_id', auth()->id())
                ->exists();
            if (!$belongsUser) {
                return response()->json(['message' => 'Rekening asal tidak valid.'], 422);
            }
        }

        // Prevent overpaying
        $verifiedPaid = $purchase->payments()->where('status', 'verified')->sum('amount');
        $remaining = max($purchase->total_amount - $verifiedPaid, 0);
        if ($purchase->total_amount > 0 && $validated['amount'] > $remaining + 0.0001) {
            return response()->json(['message' => 'Jumlah melebihi sisa tagihan.'], 422);
        }

        // Payment proof
        $media = null;
        if (!empty($validated['payment_media_id'])) {
            $media = Media::find($validated['payment_media_id']);
            if (!$media || $media->type !== 'payment_proof') {
                return response()->json(['message' => 'Lampiran bukan bukti transfer yang valid.'], 422);
            }
        }

        $paidAt = $validated['payment_date']
            ? Carbon::parse($validated['payment_date'])->startOfDay()
            : now();

        $purchasePayment = PurchasePayment::create([
            'purchase_id' => $purchase->id,
            'amount' => $validated['amount'],
            'status' => 'verified',
            'method' => $validated['payment_method'],
            'paid_at' => $paidAt,
            'supplier_bank_account_id' => $validated['supplier_bank_account_id'] ?? null,
            'payer_bank_account_id' => $validated['payer_bank_account_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        if (!empty($media)) {
            if (!$media->purchase_id) {
                $media->purchase_id = $purchase->id;
            }
            $media->purchase_payment_id = $purchasePayment->id;
            $media->save();
        }

        $this->syncPurchasePaymentSummary($purchase);
        $ledgerEntry = $this->createLedgerForPurchasePayment($purchasePayment);
        if ($ledgerEntry) {
            $purchasePayment->update(['ledger_entry_id' => $ledgerEntry->id]);
        }

        $transferFee = (float) ($validated['transfer_fee'] ?? 0);
        if ($transferFee > 0) {
            $this->createLedgerForTransferFee($purchasePayment, $transferFee);
        }

        return response()->json([
            'message' => 'Pembayaran tercatat',
            'purchase' => $purchase->fresh()->only([
                'id',
                'payment_status',
                'payment_method',
                'payment_date',
                'paid_amount',
            ]),
        ]);
    }

    private function syncPurchasePaymentSummary(Purchase $purchase): void
    {
        $verifiedPayments = $purchase->payments()->where('status', 'verified')->get();
        $totalPaid = $verifiedPayments->sum('amount');
        $latestPayment = $verifiedPayments->sortByDesc('paid_at')->first();

        $purchase->update([
            'paid_amount' => $totalPaid,
            'payment_status' => $totalPaid >= $purchase->total_amount ? 'paid' : 'pending',
            'payment_date' => $latestPayment?->paid_at?->toDateString(),
            'payment_method' => $latestPayment->method ?? $purchase->payment_method,
            'supplier_bank_account_id' => $latestPayment->supplier_bank_account_id ?? $purchase->supplier_bank_account_id,
            'payer_bank_account_id' => $latestPayment->payer_bank_account_id ?? $purchase->payer_bank_account_id,
        ]);
    }

    private function createLedgerForPurchasePayment(PurchasePayment $purchasePayment): ?LedgerEntry
    {
        if ($purchasePayment->status !== 'verified') {
            return null;
        }

        $exists = LedgerEntry::where('source_type', 'purchase_payment')
            ->where('source_id', $purchasePayment->id)
            ->exists();
        if ($exists) {
            return null;
        }

        $category = FinancialCategory::firstOrCreate(
            ['name' => 'Pembelian Produk', 'type' => 'expense'],
            ['is_active' => true]
        );

        return LedgerEntry::create([
            'entry_date' => $purchasePayment->paid_at ? $purchasePayment->paid_at->toDateString() : now()->toDateString(),
            'type' => 'expense',
            'category_id' => $category->id,
            'description' => 'Pembayaran supplier #' . $purchasePayment->purchase->purchase_number,
            'amount' => $purchasePayment->amount,
            'reference_id' => $purchasePayment->purchase_id,
            'reference_type' => 'purchase',
            'source_type' => 'purchase_payment',
            'source_id' => $purchasePayment->id,
            'created_by' => Auth::id(),
        ]);
    }

    private function createLedgerForTransferFee(PurchasePayment $purchasePayment, float $fee): ?LedgerEntry
    {
        $exists = LedgerEntry::where('source_type', 'purchase_transfer_fee')
            ->where('source_id', $purchasePayment->id)
            ->exists();
        if ($exists) {
            return null;
        }

        $category = FinancialCategory::firstOrCreate(
            ['name' => 'Biaya Transfer', 'type' => 'expense'],
            ['is_active' => true]
        );

        return LedgerEntry::create([
            'entry_date' => $purchasePayment->paid_at ? $purchasePayment->paid_at->toDateString() : now()->toDateString(),
            'type' => 'expense',
            'category_id' => $category->id,
            'description' => 'Biaya transfer pembayaran supplier #' . $purchasePayment->purchase->purchase_number,
            'amount' => $fee,
            'reference_id' => $purchasePayment->purchase_id,
            'reference_type' => 'purchase',
            'source_type' => 'purchase_transfer_fee',
            'source_id' => $purchasePayment->id,
            'created_by' => Auth::id(),
        ]);
    }
}
