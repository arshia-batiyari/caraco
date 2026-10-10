<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Settlement;
use App\Services\SettlementService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SettlementController extends Controller
{
    public function __construct(
        protected SettlementService $settlementService,
        protected SmsService $sms,
    ) {}

    public function index(Request $request)
    {
        $settlements = Settlement::with('booth')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest('period_end')
            ->paginate(20);

        return view('admin.settlements.index', compact('settlements'));
    }

    /** ساخت دسته‌ی تسویه‌حساب جدید برای یک بازه زمانی، برای همه غرفه‌ها */
    public function generate(Request $request)
    {
        $data = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        $count = $this->settlementService->generateForAllBooths(
            Carbon::parse($data['period_start']),
            Carbon::parse($data['period_end']),
        );

        return back()->with('success', "{$count} دسته تسویه‌حساب جدید ساخته شد.");
    }

    /**
     * ادمین بعد از اینکه دستی مبلغ رو به حساب بانکی غرفه‌دار واریز کرد،
     * اینجا وضعیت رو "پرداخت‌شده" می‌کند و شماره پیگیری واریز رو ثبت می‌کند.
     */
    public function markPaid(Request $request, Settlement $settlement)
    {
        $data = $request->validate(['payment_reference' => 'required|string|max:100']);

        $settlement->markPaid(Auth::user(), $data['payment_reference']);
        $settlement->load('booth.owner');

        if ($settlement->booth?->owner?->phone) {
            $this->sms->notifySettlementPaid(
                $settlement->booth->owner->phone,
                number_format($settlement->payable_amount),
                $data['payment_reference'],
            );
        }

        return back()->with('success', 'تسویه‌حساب با موفقیت ثبت شد و پیامک اطلاع‌رسانی ارسال شد.');
    }
}
