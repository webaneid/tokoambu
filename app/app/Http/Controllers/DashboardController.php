<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\LedgerEntry;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::now()->toDateString();
        
        // Today's summary
        $todayIncome = LedgerEntry::where('type', 'income')->whereDate('entry_date', $today)->sum('amount');
        $todayExpense = LedgerEntry::where('type', 'expense')->whereDate('entry_date', $today)->sum('amount');
        
        // This month
        $monthStart = Carbon::now()->startOfMonth();
        $monthIncome = LedgerEntry::where('type', 'income')->where('entry_date', '>=', $monthStart)->sum('amount');
        $monthExpense = LedgerEntry::where('type', 'expense')->where('entry_date', '>=', $monthStart)->sum('amount');
        
        // Pending orders
        $pendingOrders = Order::whereIn('status', ['waiting_payment', 'dp_paid'])->count();
        $pendingAmount = Order::whereIn('status', ['waiting_payment', 'dp_paid'])->sum('remaining_amount');
        
        // Preorders
        $preorders = Order::where('type', 'preorder')->where('status', '!=', 'done')->count();
        
        // Recent orders
        $recentOrders = Order::with('customer')->latest()->limit(5)->get();
        
        return view('dashboard', compact(
            'todayIncome',
            'todayExpense',
            'monthIncome',
            'monthExpense',
            'pendingOrders',
            'pendingAmount',
            'preorders',
            'recentOrders'
        ));
    }
}
