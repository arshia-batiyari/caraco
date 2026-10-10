<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Services\OrderService;
use App\Services\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected ShippingService $shipping,
    ) {}

    /** صفحه تأیید نهایی سفارش قبل از پرداخت */
    public function show()
    {
        $cart = Session::get('cart', []); // [product_id => quantity]

        $products = \App\Models\Product::with('booth')->whereIn('id', array_keys($cart))->get();

        $cartItems = $products->map(fn ($p) => [
            'product' => $p,
            'quantity' => $cart[$p->id],
            'line_total' => $p->price * $cart[$p->id],
        ]);

        $itemsAmount = $cartItems->sum('line_total');

        // برای پیش‌نمایش هزینه ارسال قبل از ثبت سفارش، فقط وقتی آدرس پیش‌فرض
        // مشتری مشخص است این را دقیق محاسبه می‌کنیم؛ وگرنه بعد از انتخاب آدرس در پیام تأیید نشان داده می‌شود.
        $defaultAddress = Auth::user()->addresses->firstWhere('is_default', true) ?? Auth::user()->addresses->first();
        $shippingByBooth = collect();

        if ($defaultAddress) {
            $shippingByBooth = $cartItems->groupBy(fn ($row) => $row['product']->booth_id)->map(function ($rows) use ($defaultAddress) {
                $booth = $rows->first()['product']->booth;
                $weight = $rows->sum(fn ($r) => $r['product']->weight_grams * $r['quantity']);

                return $this->shipping->estimate($booth, $defaultAddress, $weight);
            });
        }

        $shippingAmount = $shippingByBooth->sum('cost');
        $addresses = Auth::user()->addresses;

        return view('customer.checkout.show', compact('cartItems', 'itemsAmount', 'shippingAmount', 'shippingByBooth', 'addresses'));
    }

    /**
     * ثبت سفارش از روی سبد خرید. چون فعلاً درگاه آنلاین وصل نیست، سفارش با
     * وضعیت «در انتظار تأیید واریز» ثبت می‌شود؛ مشتری مبلغ را کارت‌به‌کارت
     * واریز می‌کند و شماره پیگیری را وارد می‌کند، سپس ادمین از پنل بررسی و
     * تأیید می‌کند (نگاه کنید به Admin\OrderController::confirmPayment).
     */
    public function pay(Request $request)
    {
        $request->validate([
            'address_id'         => 'required|exists:addresses,id',
            'payment_reference'  => 'nullable|string|max:100',
            'payment_receipt'    => 'nullable|image|max:4096',
        ]);

        $address = Address::where('user_id', Auth::id())->findOrFail($request->address_id);

        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return back()->with('error', 'سبد خرید شما خالی است.');
        }

        $cartItems = collect($cart)->map(fn ($qty, $productId) => [
            'product_id' => (int) $productId,
            'quantity'   => (int) $qty,
        ])->values()->all();

        try {
            $order = $this->orderService->createFromCart(Auth::user(), $address, $cartItems);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $receiptPath = $request->hasFile('payment_receipt')
            ? $request->file('payment_receipt')->store('payment-receipts', 'public')
            : null;

        $order->update([
            'payment_gateway'       => 'card_transfer',
            'payment_ref_id'        => $request->payment_reference,
            'payment_receipt_path'  => $receiptPath,
            'payment_submitted_at'  => ($request->payment_reference || $receiptPath) ? now() : null,
        ]);

        Session::forget('cart');

        return redirect()->route('customer.orders.show', $order)
            ->with('success', 'سفارش شما ثبت شد. لطفاً مبلغ را کارت‌به‌کارت واریز کنید؛ بعد از تأیید پشتیبانی، سفارش پردازش می‌شود.');
    }
}
