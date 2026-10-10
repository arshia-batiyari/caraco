<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyInquiry;
use Illuminate\Http\Request;

class PropertyInquiryController extends Controller
{
    public function updateStatus(Request $request, PropertyInquiry $inquiry)
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,contacted,archived'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $inquiry->update($data);

        return back()->with('success', 'وضعیت درخواست به‌روزرسانی شد.');
    }
}
