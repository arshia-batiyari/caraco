<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booth;
use App\Services\SmsService;
use Illuminate\Http\Request;

class BoothController extends Controller
{
    public function __construct(protected SmsService $sms) {}

    public function index(Request $request)
    {
        $booths = Booth::with('owner', 'categories')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return view('admin.booths.index', compact('booths'));
    }

    public function show(Booth $booth)
    {
        $booth->load('owner', 'categories', 'products');

        $contract = $booth->currentContract();
        $walletSummary = app(\App\Services\WalletService::class)->summary($booth);

        return view('admin.booths.show', compact('booth', 'contract', 'walletSummary'));
    }

    /**
     * مدرک هویتی/جواز کسب روی دیسک local ذخیره می‌شه (نه public)، چون یه
     * مدرک حساسه و نباید با لینک مستقیم برای همه قابل‌دسترس باشه — فقط از
     * همین مسیر (که پشت میدل‌ور ادمین قفله) قابل مشاهده‌ست.
     */
    public function document(Booth $booth)
    {
        abort_unless($booth->verification_document, 404);

        return \Illuminate\Support\Facades\Storage::disk('local')->response($booth->verification_document);
    }

    public function approve(Booth $booth)
    {
        $booth->update(['status' => 'approved', 'approved_at' => now(), 'rejection_reason' => null]);

        if ($booth->owner?->phone) {
            $this->sms->notifyBoothApproved($booth->owner->phone, $booth->localized_name);
        }

        return back()->with('success', "غرفه «{$booth->localized_name}» تأیید شد و پیامک اطلاع‌رسانی ارسال شد.");
    }

    public function reject(Request $request, Booth $booth)
    {
        $data = $request->validate(['rejection_reason' => 'required|string|max:500']);

        $booth->update([
            'status' => 'rejected',
            'rejection_reason' => $data['rejection_reason'],
        ]);

        if ($booth->owner?->phone) {
            $this->sms->notifyBoothRejected($booth->owner->phone, $booth->localized_name, $data['rejection_reason']);
        }

        return back()->with('success', "غرفه «{$booth->localized_name}» رد شد و پیامک اطلاع‌رسانی ارسال شد.");
    }

    /** فعال یا غیرفعال کردن یک غرفه‌ی از قبل تأییدشده */
    public function suspend(Booth $booth)
    {
        $booth->update(['status' => 'suspended']);

        return back()->with('success', 'غرفه به حالت تعلیق درآمد.');
    }

    public function updateStatus(Request $request, Booth $booth)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,approved,rejected,suspended',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $wasPending = $booth->status === 'pending';

        $update = ['status' => $data['status']];

        if ($data['status'] === 'approved') {
            $update['approved_at'] = now();
            $update['rejection_reason'] = null;
        }

        if ($data['status'] === 'rejected') {
            $update['rejection_reason'] = $data['rejection_reason'] ?: __('site.panel.rejection_reason_short');
        }

        $booth->update($update);

        if ($wasPending && $booth->owner?->phone) {
            if ($data['status'] === 'approved') {
                $this->sms->notifyBoothApproved($booth->owner->phone, $booth->localized_name);
            } elseif ($data['status'] === 'rejected') {
                $this->sms->notifyBoothRejected($booth->owner->phone, $booth->localized_name, $update['rejection_reason']);
            }
        }

        $statusLabel = __('site.status.booth.'.$data['status']);

        return back()->with('success', "وضعیت غرفه «{$booth->localized_name}» به «{$statusLabel}» تغییر کرد.");
    }

    /**
     * مسیر قدیمی تنظیم سریع درصد. حالا هر تغییر درصد یک قرارداد جدید صادر می‌کنه
     * (با دوره‌ی تسویه و مدت قراردادِ قرارداد قبلی، یا پیش‌فرض ۱۵ روز/۱۲ ماه).
     */
    public function updateCommission(Request $request, Booth $booth, \App\Services\ContractService $contracts)
    {
        $data = $request->validate(['commission_percent' => 'required|numeric|min:0|max:99']);

        $previous = $booth->contracts()->first();

        $contracts->issue(
            $booth,
            100 - (float) $data['commission_percent'],
            (int) ($previous->settlement_cycle_days ?? 15),
            (int) ($previous->term_months ?? 12),
            $previous->special_terms ?? null,
            $request->user(),
        );

        return back()->with('success', __('wallet.issued_short'));
    }
}
