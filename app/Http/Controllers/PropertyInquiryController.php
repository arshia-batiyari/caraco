<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ثبت درخواست ارتباط یک بازدیدکننده (خریدار/شریک احتمالی) با مالک ملک.
 * عمداً کاربر مهمان هم می‌تواند بدون لاگین درخواست بدهد تا اصطکاک کمتر باشد؛
 * فقط شماره تماس و توضیح کوتاه گرفته می‌شود. این اطلاعات فقط برای ادمین
 * قابل مشاهده است، نه برای مالک ملک.
 */
class PropertyInquiryController extends Controller
{
    public function store(Request $request, Property $property): RedirectResponse
    {
        abort_unless($property->status === 'approved', 404);

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'phone'         => ['required', 'string', 'max:20'],
            'inquiry_type'  => ['required', 'in:buy,partnership,other'],
            'message'       => ['nullable', 'string', 'max:1000'],
        ]);

        $property->inquiries()->create([
            'user_id' => Auth::id(),
            'name' => $data['name'],
            'phone' => $data['phone'],
            'inquiry_type' => $data['inquiry_type'],
            'message' => $data['message'] ?? null,
            'status' => 'new',
        ]);

        return back()->with('success', 'درخواست شما ثبت شد. تیم پشتیبانی هاچ‌لند بعد از بررسی با شما تماس می‌گیرد.');
    }
}
