<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyRequest;
use Illuminate\Http\Request;

class PropertyRequestController extends Controller
{
    public function index(Request $request)
    {
        $propertyRequests = PropertyRequest::withCount('offers')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.property-requests.index', compact('propertyRequests'));
    }

    /** تنها صفحه‌ای که پیشنهادهای رسیده برای یک آگهی درخواست رو نشون می‌ده */
    public function show(PropertyRequest $propertyRequest)
    {
        $offers = $propertyRequest->offers()->latest()->paginate(20);

        return view('admin.property-requests.show', compact('propertyRequest', 'offers'));
    }

    public function approve(PropertyRequest $propertyRequest)
    {
        $propertyRequest->update(['status' => 'approved', 'approved_at' => now(), 'rejection_reason' => null]);

        return back()->with('success', 'آگهی درخواست تأیید و منتشر شد.');
    }

    public function reject(Request $request, PropertyRequest $propertyRequest)
    {
        $data = $request->validate(['rejection_reason' => 'required|string|max:500']);

        $propertyRequest->update(['status' => 'rejected', 'rejection_reason' => $data['rejection_reason']]);

        return back()->with('success', 'آگهی درخواست رد شد.');
    }
}
