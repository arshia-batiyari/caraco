<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * مسئول ساخت سفارش از روی سبد خرید و شکستن آیتم‌ها بر اساس هر غرفه،
 * محاسبه کمیسیون هلدینگ، محاسبه هزینه ارسال سراسری، و اطلاع‌رسانی پیامکی.
 */
class OrderService
{
    public function __construct(
        protected ShippingService $shipping,
        protected SmsService $sms,
        protected WalletService $wallet,
    ) {}

    /**
     * @param  User  $user
     * @param  Address  $address
     * @param  array<int, array{product_id:int, quantity:int}>  $cartItems
     */
    public function createFromCart(User $user, Address $address, array $cartItems): Order
    {
        return DB::transaction(function () use ($user, $address, $cartItems) {

            $order = Order::create([
                'order_number'    => Order::generateOrderNumber(),
                'user_id'         => $user->id,
                'address_id'      => $address->id,
                'items_amount'    => 0,
                'shipping_amount' => 0,
                'total_amount'    => 0,
                'status'          => 'pending_payment',
                'payment_status'  => 'pending',
            ]);

            $itemsAmount = 0;
            $weightByBooth = []; // [booth_id => total_grams]
            $boothModels = [];   // [booth_id => Booth] برای جلوگیری از کوئری تکراری موقع محاسبه ارسال

            foreach ($cartItems as $row) {
                /** @var Product $product */
                $product = Product::with('booth')->lockForUpdate()->findOrFail($row['product_id']);

                if ($product->stock < $row['quantity']) {
                    throw new \RuntimeException("موجودی «{$product->localized_name}» کافی نیست.");
                }

                $amounts = OrderItem::calculateAmounts(
                    unitPrice: $product->price,
                    quantity: $row['quantity'],
                    commissionPercent: (float) $product->booth->commission_percent,
                );

                OrderItem::create([
                    'order_id'             => $order->id,
                    'product_id'           => $product->id,
                    'booth_id'             => $product->booth_id,
                    'product_name'         => $product->name,
                    'unit_price'           => $product->price,
                    'quantity'             => $row['quantity'],
                    'line_total'           => $amounts['line_total'],
                    'commission_percent'   => $product->booth->commission_percent,
                    'commission_amount'    => $amounts['commission_amount'],
                    'booth_payout_amount'  => $amounts['booth_payout_amount'],
                    'item_status'          => 'pending',
                ]);

                $product->decrement('stock', $row['quantity']);
                $itemsAmount += $amounts['line_total'];

                $boothModels[$product->booth_id] ??= $product->booth;
                $weightByBooth[$product->booth_id] = ($weightByBooth[$product->booth_id] ?? 0)
                    + ($product->weight_grams * $row['quantity']);
            }

            // هزینه ارسال جدا برای هر غرفه، چون هر کدوم ممکنه از استان متفاوتی باشن
            // و هر کدوم مستقل به مقصد مشتری ارسال می‌کنن.
            $shippingBreakdown = [];
            foreach ($weightByBooth as $boothId => $weight) {
                $estimate = $this->shipping->estimate($boothModels[$boothId], $address, $weight);
                $shippingBreakdown[$boothId] = $estimate['cost'];
            }
            $shippingAmount = array_sum($shippingBreakdown);

            $order->update([
                'items_amount'       => $itemsAmount,
                'shipping_amount'    => $shippingAmount,
                'shipping_breakdown' => $shippingBreakdown,
                'total_amount'       => $itemsAmount + $shippingAmount,
            ]);

            return $order->fresh('items');
        });
    }

    /**
     * بعد از تأیید پرداخت (درگاه یا تأیید دستی کارت‌به‌کارت توسط ادمین) فراخوانی می‌شود.
     * برای هر غرفه‌ی درگیر در سفارش، یک مرسوله (Shipment) جدا می‌سازد،
     * سهم هر غرفه رو به کیف پولش واریز می‌کند
     * و پیامک اطلاع‌رسانی به مشتری و هر غرفه‌دار ارسال می‌کند.
     * اگه سفارش قبلاً پرداخت‌شده ثبت شده باشه هیچ کاری نمی‌کنه (جلوگیری از دوباره‌کاری با دوبار کلیک).
     */
    public function markOrderPaid(Order $order, ?string $refId = null): void
    {
        if ($order->isPaid()) {
            return;
        }

        DB::transaction(function () use ($order, $refId) {
            $order->update(array_filter([
                'payment_status' => 'paid',
                'payment_ref_id' => $refId,
                'paid_at'        => now(),
                'status'         => 'processing',
            ], fn ($v) => $v !== null));

            // سهم هر غرفه از هر آیتم → کیف پول (طبق درصد قفل‌شده روی خود آیتم)
            $this->wallet->creditPaidOrder($order);

            $items = $order->items()->with('booth.owner')->get();
            $breakdown = $order->shipping_breakdown ?? [];

            foreach ($items->groupBy('booth_id') as $boothId => $boothItems) {
                $shipment = $order->shipments()->create([
                    'booth_id'      => $boothId,
                    'shipping_cost' => $breakdown[$boothId] ?? 0,
                    'status'        => 'preparing',
                ]);

                $order->items()->where('booth_id', $boothId)
                    ->update(['shipment_id' => $shipment->id, 'item_status' => 'processing']);

                // اطلاع‌رسانی پیامکی به غرفه‌دار برای سفارش جدید
                $booth = $boothItems->first()->booth;
                $boothTotal = $boothItems->sum('line_total');
                if ($booth?->owner?->phone) {
                    $this->sms->notifyNewOrderToVendor(
                        $booth->owner->phone,
                        $booth->localized_name,
                        $order->order_number,
                        number_format($boothTotal),
                    );
                }
            }
        });

        // اطلاع‌رسانی پیامکی به مشتری بعد از تراکنش موفق
        if ($order->user?->phone) {
            $this->sms->notifyOrderPaid($order->user->phone, $order->order_number);
        }
    }
}
