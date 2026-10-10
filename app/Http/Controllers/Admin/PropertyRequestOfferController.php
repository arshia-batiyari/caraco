<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyRequestOffer;
use Illuminate\Http\Request;

class PropertyRequestOfferController extends Controller
{
    public function updateStatus(Request $request, PropertyRequestOffer $offer)
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,contacted,archived'],
        ]);

        $offer->update($data);

        return back()->with('success', 'وضعیت پیشنهاد به‌روزرسانی شد.');
    }
}
