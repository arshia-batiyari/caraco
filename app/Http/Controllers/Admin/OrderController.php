<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with('user')
            ->when($request->status, fn ($q) => $q->where('payment_status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.booth', 'items.product', 'address']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * تأیید دستی پرداخت کارت‌به‌کارت بعد از بررسی رسید/شماره پیگیری.
     * از همون مسیر مشترک markOrderPaid استفاده می‌کنه تا هم مرسوله‌ی هر غرفه ساخته بشه
     * (بدون اون سفارش تو پنل غرفه‌دار دیده نمی‌شه) و هم سهم غرفه‌ها به کیف پولشون واریز بشه.
     */
    public function confirmPayment(Order $order, OrderService $orders)
    {
        if ($order->isPaid()) {
            return back()->with('error', 'پرداخت این سفارش قبلاً تأیید شده است.');
        }

        $orders->markOrderPaid($order);

        return back()->with('success', 'پرداخت سفارش تأیید شد و سهم غرفه‌ها به کیف پولشان واریز شد.');
    }

    public function rejectPayment(Request $request, Order $order, WalletService $wallet)
    {
        $data = $request->validate(['reject_reason' => 'nullable|string|max:500']);

        DB::transaction(function () use ($order, $wallet, $data) {
            $wasPaid = $order->isPaid();

            $order->update([
                'payment_status' => 'failed',
                'status' => 'cancelled',
            ]);

            // اگه پرداخت قبلاً تأیید و سهم غرفه‌ها واریز شده بود، برگشت می‌خوره
            if ($wasPaid) {
                $wallet->reverseOrder($order, Auth::user(), $data['reject_reason'] ?? 'لغو/رد پرداخت سفارش '.$order->order_number);
            }
        });

        return back()->with('success', 'پرداخت سفارش رد شد.');
    }
}
