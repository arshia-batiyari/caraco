<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booth;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Settlement;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'active_booths' => Booth::approved()->count(),
            'products' => Product::where('status', 'active')->count(),
            'monthly_orders' => Order::where('payment_status', 'paid')->whereMonth('paid_at', now()->month)->count(),
            'monthly_revenue' => Order::where('payment_status', 'paid')->whereMonth('paid_at', now()->month)->sum('total_amount'),
            'monthly_commission' => \App\Models\OrderItem::whereHas('order', fn ($q) =>
                $q->where('payment_status', 'paid')->whereMonth('paid_at', now()->month)
            )->sum('commission_amount'),
        ];

        $pendingBooths = Booth::pending()->with('owner', 'categories')->latest()->limit(4)->get();

        $categoryTree = Category::roots()->with('children')->withCount('booths')->orderBy('sort_order')->get();

        $pendingSettlements = Settlement::with('booth')->where('status', 'pending')->latest('period_end')->limit(4)->get();

        return view('admin.dashboard', compact('stats', 'pendingBooths', 'categoryTree', 'pendingSettlements'));
    }
}
