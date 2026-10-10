<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaterialOffer;
use Illuminate\Http\Request;

class MaterialOfferController extends Controller
{
    public function updateStatus(Request $request, MaterialOffer $offer)
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,contacted,archived'],
        ]);

        $offer->update($data);

        return back()->with('success', __('site.materials.offer_updated'));
    }
}
