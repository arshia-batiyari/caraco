<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\SmsService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function __construct(protected SmsService $sms) {}

    public function index(Request $request)
    {
        $properties = Property::with('owner')
            ->withCount('inquiries')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.properties.index', compact('properties'));
    }

    /**
     * جزئیات یک ملک به‌همراه همه‌ی درخواست‌های ارتباطی که برایش ثبت شده —
     * این تنها صفحه‌ای در کل سایت است که اطلاعات درخواست‌کننده‌ها را نشان می‌دهد.
     */
    public function show(Property $property)
    {
        $property->load('owner');
        $inquiries = $property->inquiries()->latest()->paginate(20);

        return view('admin.properties.show', compact('property', 'inquiries'));
    }

    public function approve(Property $property)
    {
        $property->update(['status' => 'approved', 'approved_at' => now(), 'rejection_reason' => null]);

        if ($property->owner?->phone) {
            $this->sms->sendRaw($property->owner->phone, 'آگهی ملک «'.$property->localized_title.'» شما در هاچ‌لند تأیید و منتشر شد.');
        }

        return back()->with('success', 'آگهی ملک تأیید و منتشر شد.');
    }

    public function reject(Request $request, Property $property)
    {
        $data = $request->validate(['rejection_reason' => 'required|string|max:500']);

        $property->update([
            'status' => 'rejected',
            'rejection_reason' => $data['rejection_reason'],
        ]);

        if ($property->owner?->phone) {
            $this->sms->sendRaw($property->owner->phone, 'آگهی ملک «'.$property->localized_title.'» شما تأیید نشد. دلیل: '.$data['rejection_reason']);
        }

        return back()->with('success', 'آگهی ملک رد شد.');
    }
}
