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
        // Handle period selection
        $period = $request->get('period', 'this_month');
        $categoryFilter = $request->get('category_id');

        switch ($period) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today();
                break;
            case 'this_week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'this_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'this_year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'custom':
                $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::now()->startOfMonth();
                $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::now();
                break;
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
        }

        // Base query
        $baseQuery = LedgerEntry::whereBetween('entry_date', [$startDate, $endDate]);

        if ($categoryFilter) {
            $baseQuery->where('category_id', $categoryFilter);
        }

        // Summary calculations
        $income = (clone $baseQuery)->where('type', 'income')->sum('amount');
        $expense = (clone $baseQuery)->where('type', 'expense')->sum('amount');
        $profit = $income - $expense;
        $profitMargin = $income > 0 ? ($profit / $income) * 100 : 0;

        // Previous period for comparison
        $periodDiff = $startDate->diffInDays($endDate) + 1;
        $prevStartDate = (clone $startDate)->subDays($periodDiff);
        $prevEndDate = (clone $startDate)->subDay();

        $prevIncome = LedgerEntry::where('type', 'income')
            ->whereBetween('entry_date', [$prevStartDate, $prevEndDate])
            ->sum('amount');
        $prevExpense = LedgerEntry::where('type', 'expense')
            ->whereBetween('entry_date', [$prevStartDate, $prevEndDate])
            ->sum('amount');
        $prevProfit = $prevIncome - $prevExpense;

        // Calculate growth percentages
        $incomeGrowth = $prevIncome > 0 ? (($income - $prevIncome) / $prevIncome) * 100 : 0;
        $expenseGrowth = $prevExpense > 0 ? (($expense - $prevExpense) / $prevExpense) * 100 : 0;
        $profitGrowth = $prevProfit != 0 ? (($profit - $prevProfit) / abs($prevProfit)) * 100 : 0;

        // Chart data - group by appropriate interval
        $chartData = $this->getChartData($startDate, $endDate, $period, $categoryFilter);

        // Category breakdown
        $incomeByCategory = LedgerEntry::select('category_id', DB::raw('SUM(amount) as total'))
            ->where('type', 'income')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->when($categoryFilter, fn($q) => $q->where('category_id', $categoryFilter))
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->map(fn($item) => [
                'category' => $item->category->name ?? 'Tidak ada kategori',
                'amount' => $item->total,
                'color' => $this->getCategoryColor($item->category_id ?? 0)
            ]);

        $expenseByCategory = LedgerEntry::select('category_id', DB::raw('SUM(amount) as total'))
            ->where('type', 'expense')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->when($categoryFilter, fn($q) => $q->where('category_id', $categoryFilter))
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->map(fn($item) => [
                'category' => $item->category->name ?? 'Tidak ada kategori',
                'amount' => $item->total,
                'color' => $this->getCategoryColor($item->category_id ?? 0)
            ]);

        // Transaction entries
        $entries = LedgerEntry::with('category')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->when($categoryFilter, fn($q) => $q->where('category_id', $categoryFilter))
            ->orderByDesc('entry_date')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // Get all categories for filter
        $categories = FinancialCategory::where('is_active', true)->orderBy('name')->get();

        return view('ledger.report', compact(
            'income', 'expense', 'profit', 'profitMargin',
            'incomeGrowth', 'expenseGrowth', 'profitGrowth',
            'chartData', 'incomeByCategory', 'expenseByCategory',
            'entries', 'startDate', 'endDate', 'period', 'categories', 'categoryFilter'
        ));
    }

    private function getChartData($startDate, $endDate, $period, $categoryFilter = null)
    {
        $labels = [];
        $incomeData = [];
        $expenseData = [];

        if (in_array($period, ['today', 'this_week']) || $startDate->diffInDays($endDate) <= 31) {
            // Daily grouping
            $current = clone $startDate;
            while ($current <= $endDate) {
                $labels[] = $current->format('d M');

                $dayIncome = LedgerEntry::where('type', 'income')
                    ->whereDate('entry_date', $current)
                    ->when($categoryFilter, fn($q) => $q->where('category_id', $categoryFilter))
                    ->sum('amount');
                $dayExpense = LedgerEntry::where('type', 'expense')
                    ->whereDate('entry_date', $current)
                    ->when($categoryFilter, fn($q) => $q->where('category_id', $categoryFilter))
                    ->sum('amount');

                $incomeData[] = (float) $dayIncome;
                $expenseData[] = (float) $dayExpense;

                $current->addDay();
            }
        } elseif ($period === 'this_year' || $startDate->diffInMonths($endDate) > 1) {
            // Monthly grouping
            $current = (clone $startDate)->startOfMonth();
            $end = (clone $endDate)->endOfMonth();

            while ($current <= $end) {
                $labels[] = $current->format('M Y');

                $monthStart = (clone $current)->startOfMonth();
                $monthEnd = (clone $current)->endOfMonth();

                $monthIncome = LedgerEntry::where('type', 'income')
                    ->whereBetween('entry_date', [$monthStart, $monthEnd])
                    ->when($categoryFilter, fn($q) => $q->where('category_id', $categoryFilter))
                    ->sum('amount');
                $monthExpense = LedgerEntry::where('type', 'expense')
                    ->whereBetween('entry_date', [$monthStart, $monthEnd])
                    ->when($categoryFilter, fn($q) => $q->where('category_id', $categoryFilter))
                    ->sum('amount');

                $incomeData[] = (float) $monthIncome;
                $expenseData[] = (float) $monthExpense;

                $current->addMonth();
            }
        } else {
            // Weekly grouping
            $current = (clone $startDate)->startOfWeek();

            while ($current <= $endDate) {
                $weekEnd = (clone $current)->endOfWeek();
                if ($weekEnd > $endDate) $weekEnd = clone $endDate;

                $labels[] = $current->format('d M') . ' - ' . $weekEnd->format('d M');

                $weekIncome = LedgerEntry::where('type', 'income')
                    ->whereBetween('entry_date', [$current, $weekEnd])
                    ->when($categoryFilter, fn($q) => $q->where('category_id', $categoryFilter))
                    ->sum('amount');
                $weekExpense = LedgerEntry::where('type', 'expense')
                    ->whereBetween('entry_date', [$current, $weekEnd])
                    ->when($categoryFilter, fn($q) => $q->where('category_id', $categoryFilter))
                    ->sum('amount');

                $incomeData[] = (float) $weekIncome;
                $expenseData[] = (float) $weekExpense;

                $current->addWeek();
            }
        }

        return [
            'labels' => $labels,
            'income' => $incomeData,
            'expense' => $expenseData,
        ];
    }

    private function getCategoryColor($categoryId)
    {
        $colors = [
            '#F17B0D', '#0D36AA', '#D00086', '#10B981', '#EF4444',
            '#8B5CF6', '#F59E0B', '#06B6D4', '#EC4899', '#6366F1'
        ];
        return $colors[$categoryId % count($colors)];
    }

    public function exportExcel(Request $request)
    {
        $period = $request->get('period', 'this_month');
        $categoryFilter = $request->get('category_id');

        // Get date range (same logic as report method)
        switch ($period) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today();
                break;
            case 'this_week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'this_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'this_year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'custom':
                $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::now()->startOfMonth();
                $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::now();
                break;
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
        }

        // Get entries
        $entries = LedgerEntry::with('category')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->when($categoryFilter, fn($q) => $q->where('category_id', $categoryFilter))
            ->orderByDesc('entry_date')
            ->orderByDesc('created_at')
            ->get();

        // Calculate summary
        $income = $entries->where('type', 'income')->sum('amount');
        $expense = $entries->where('type', 'expense')->sum('amount');
        $profit = $income - $expense;

        // Create CSV content
        $filename = 'laporan-keuangan-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($entries, $income, $expense, $profit, $startDate, $endDate) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Summary section
            fputcsv($file, ['LAPORAN KEUANGAN']);
            fputcsv($file, ['Periode', $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y')]);
            fputcsv($file, []);
            fputcsv($file, ['RINGKASAN']);
            fputcsv($file, ['Total Pemasukan', 'Rp ' . number_format($income, 0, ',', '.')]);
            fputcsv($file, ['Total Pengeluaran', 'Rp ' . number_format($expense, 0, ',', '.')]);
            fputcsv($file, ['Keuntungan', 'Rp ' . number_format($profit, 0, ',', '.')]);
            fputcsv($file, []);
            fputcsv($file, []);

            // Transaction headers (format seperti tabel di website)
            fputcsv($file, ['DETAIL TRANSAKSI']);
            fputcsv($file, ['Tanggal', 'Deskripsi', 'Kategori', 'Debit', 'Kredit']);

            // Transaction data
            foreach ($entries as $entry) {
                fputcsv($file, [
                    $entry->entry_date ? $entry->entry_date->format('d/m/Y') : '-',
                    $entry->description,
                    $entry->category->name ?? '-',
                    $entry->type === 'income' ? 'Rp ' . number_format($entry->amount, 0, ',', '.') : '-',
                    $entry->type === 'expense' ? 'Rp ' . number_format($entry->amount, 0, ',', '.') : '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
