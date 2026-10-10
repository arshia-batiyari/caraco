<?php

namespace App\Http\Controllers;

use App\Models\PropertyRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PropertyRequestOfferController extends Controller
{
    public function store(Request $request, PropertyRequest $propertyRequest): RedirectResponse
    {
        abort_unless($propertyRequest->status === 'approved', 404);

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'phone'   => ['required', 'string', 'max:20'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $propertyRequest->offers()->create([
            'user_id' => auth()->id(),
            'name' => $data['name'],
            'phone' => $data['phone'],
            'message' => $data['message'] ?? null,
            'status' => 'new',
        ]);

        return back()->with('success', 'درخواست ارتباط شما ثبت شد. تیم هاچ‌لند بعد از بررسی، شما را به درخواست‌دهنده وصل می‌کند.');
    }
}
