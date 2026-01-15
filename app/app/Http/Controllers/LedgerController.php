<?php

namespace App\Http\Controllers;

use App\Models\LedgerEntry;
use App\Models\FinancialCategory;
use App\Models\BankAccount;
use App\Models\Media;
use App\Models\Vendor;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LedgerController extends Controller
{

    public function index()
    {
        $entries = LedgerEntry::with('category')->latest('entry_date')->latest()->paginate(20);
        $todayIncome = LedgerEntry::where('type', 'income')->whereDate('entry_date', today())->sum('amount');
        $todayExpense = LedgerEntry::where('type', 'expense')->whereDate('entry_date', today())->sum('amount');
        
        return view('ledger.index', compact('entries', 'todayIncome', 'todayExpense'));
    }

    public function create()
    {
        $categories = FinancialCategory::where('is_active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        $userBankAccounts = BankAccount::whereNotNull('user_id')->get();
        $supplierBankAccounts = BankAccount::whereNotNull('supplier_id')
            ->with('supplier:id,name')
            ->get();
        $customerBankAccounts = BankAccount::whereNotNull('customer_id')
            ->with('customer:id,name')
            ->get();
        $vendorBankAccounts = BankAccount::whereNotNull('vendor_id')
            ->with('vendor:id,name')
            ->get();
        $employeeBankAccounts = BankAccount::whereNotNull('employee_id')
            ->with('employee:id,name')
            ->get();

        return view('ledger.create', compact('categories', 'userBankAccounts', 'supplierBankAccounts', 'customerBankAccounts', 'vendorBankAccounts', 'employeeBankAccounts'));
    }

    public function store(Request $request)
    {
        $methods = ['cash', 'debit', 'credit_card', 'transfer', 'qris'];
        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'category_id' => 'required|exists:financial_categories,id',
            'description' => 'required|string|max:255',
            'entry_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'recipient_type' => ['nullable', Rule::in(['supplier', 'vendor', 'customer', 'employee'])],
            'recipient_bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'payment_method' => ['nullable', Rule::in($methods)],
            'shop_bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'payment_media_id' => ['nullable', 'exists:media,id'],
            'transfer_fee' => ['nullable', 'numeric', 'min:0'],
            'notes' => 'nullable|string',
        ]);

        // Validate media if provided
        if (!empty($validated['payment_media_id'])) {
            $media = Media::find($validated['payment_media_id']);
            if (!$media || $media->type !== 'payment_proof') {
                return back()->withErrors(['payment_media_id' => 'Lampiran bukan bukti transfer yang valid.'])->withInput();
            }
        }

        DB::transaction(function () use ($validated, $request) {
            // Determine payee and payer based on type and recipient
            $payeeBankId = null;
            $payerBankId = null;

            if ($validated['type'] === 'expense') {
                // Expense: toko (payer) -> recipient (payee)
                $payeeBankId = $validated['recipient_bank_account_id'] ?? null;
                $payerBankId = $validated['shop_bank_account_id'] ?? null;
            } else {
                // Income: recipient (payer) -> toko (payee)
                $payeeBankId = $validated['shop_bank_account_id'] ?? null;
                $payerBankId = $validated['recipient_bank_account_id'] ?? null;
            }

            // Create main ledger entry
            $ledgerEntry = LedgerEntry::create([
                'type' => $validated['type'],
                'category_id' => $validated['category_id'],
                'description' => $validated['description'],
                'entry_date' => $validated['entry_date'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'] ?? null,
                'payee_bank_account_id' => $payeeBankId,
                'payer_bank_account_id' => $payerBankId,
                'payment_media_id' => $validated['payment_media_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Handle transfer fee if provided
            $transferFee = (float) ($validated['transfer_fee'] ?? 0);
            if ($transferFee > 0) {
                $this->createLedgerForTransferFee($ledgerEntry, $transferFee);
            }
        });

        return redirect()->route('ledger.index')->with('success', 'Catatan kas berhasil ditambahkan');
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

    public function show(LedgerEntry $ledgerEntry)
    {
        $ledgerEntry->load([
            'category',
            'creator',
            'payeeBankAccount',
            'payerBankAccount',
            'paymentMedia',
        ]);

        return view('ledger.show', compact('ledgerEntry'));
    }

    public function report(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now());

        $income = LedgerEntry::where('type', 'income')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->sum('amount');

        $expense = LedgerEntry::where('type', 'expense')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->sum('amount');

        $entries = LedgerEntry::with('category')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->orderByDesc('entry_date')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('ledger.report', compact('income', 'expense', 'entries', 'startDate', 'endDate'));
    }
}
