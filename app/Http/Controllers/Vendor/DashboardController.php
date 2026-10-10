<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $booth = Auth::user()->booth;

        $monthlyOrders = $booth->orderItems()
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'paid')->whereMonth('paid_at', now()->month))
            ->distinct('order_id')
            ->count('order_id');

        $monthlySales = $booth->orderItems()
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'paid')->whereMonth('paid_at', now()->month))
            ->sum('line_total');

        $activeProducts = $booth->products()->where('status', 'active')->count();
        $unsettledAmount = $booth->unsettled_payout;

        $recentOrderItems = $booth->orderItems()
            ->with('order.user', 'product')
            ->latest()
            ->limit(6)
            ->get();

        $nextSettlement = $booth->settlements()->where('status', 'pending')->latest('period_end')->first();

        return view('vendor.dashboard', compact(
            'booth', 'monthlyOrders', 'monthlySales', 'activeProducts',
            'unsettledAmount', 'recentOrderItems', 'nextSettlement'
        ));
    }
}
