<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(protected SmsService $sms) {}

    /** سفارش‌هایی که شامل محصولات این غرفه هستند (بر اساس مرسوله) */
    public function index()
    {
        $shipments = Auth::user()->booth->shipments()
            ->with('order.user', 'items.product')
            ->latest()
            ->paginate(15);

        return view('vendor.orders.index', compact('shipments'));
    }

    public function show(Shipment $shipment)
    {
        abort_unless($shipment->booth_id === Auth::user()->booth->id, 403);
        $shipment->load('order.user', 'order.address', 'items.product');

        return view('vendor.orders.show', compact('shipment'));
    }

    /** غرفه‌دار کد رهگیری پست را ثبت می‌کند */
    public function markShipped(Request $request, Shipment $shipment)
    {
        abort_unless($shipment->booth_id === Auth::user()->booth->id, 403);

        $data = $request->validate([
            'carrier' => 'required|string|max:100',
            'tracking_code' => 'required|string|max:100',
        ]);

        $shipment->markShipped($data['carrier'], $data['tracking_code']);

        $customerPhone = $shipment->order->user->phone;
        if ($customerPhone) {
            $this->sms->notifyOrderShipped($customerPhone, $shipment->order->order_number, $data['tracking_code']);
        }

        return back()->with('success', 'کد رهگیری ثبت شد و پیامک اطلاع‌رسانی برای مشتری ارسال شد.');
    }
}
