<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /** لیست سفارش‌های مشتری با وضعیت */
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with('items.booth')
            ->latest()
            ->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }

    /** جزئیات یک سفارش + وضعیت پیگیری هر مرسوله */
    public function show(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        $order->load(['items.booth', 'items.product', 'shipments.booth', 'address']);

        return view('customer.orders.show', compact('order'));
    }

    /**
     * ثبت یا به‌روزرسانی شماره پیگیری/رسید واریز کارت‌به‌کارت — بعد از ثبت
     * سفارش هم قابل انجامه (مثلاً اگه مشتری موقع تسویه رسید رو دستش نداشت)
     * تا تو پنل خودش بمونه و گم نشه.
     */
    public function submitPayment(Request $request, Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);
        abort_if($order->isPaid(), 403);

        $data = $request->validate([
            'payment_reference' => 'nullable|string|max:100',
            'payment_receipt'   => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('payment_receipt')) {
            if ($order->payment_receipt_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($order->payment_receipt_path);
            }
            $data['payment_receipt_path'] = $request->file('payment_receipt')->store('payment-receipts', 'public');
        }

        $order->update([
            'payment_ref_id' => $data['payment_reference'] ?? $order->payment_ref_id,
            'payment_receipt_path' => $data['payment_receipt_path'] ?? $order->payment_receipt_path,
            'payment_submitted_at' => now(),
        ]);

        return back()->with('success', 'اطلاعات پرداخت شما ثبت شد و در انتظار بررسی است.');
    }
}
