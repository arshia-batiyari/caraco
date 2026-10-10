<?php

namespace App\Http\Controllers;

use App\Models\MaterialRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MaterialOfferController extends Controller
{
    public function store(Request $request, MaterialRequest $materialRequest): RedirectResponse
    {
        abort_unless($materialRequest->status === 'approved', 404);

        if (auth()->check() && $materialRequest->offers()->where('user_id', auth()->id())->exists()) {
            return back()->with('success', __('site.materials.offer_exists'));
        }

        if ($request->input('quoted_amount') === '') {
            $request->merge(['quoted_amount' => null]);
        }
        if ($request->input('lead_days') === '') {
            $request->merge(['lead_days' => null]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'quoted_amount' => ['nullable', 'integer', 'min:0'],
            'lead_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $materialRequest->offers()->create([
            'user_id' => auth()->id(),
            'booth_id' => auth()->user()?->booth?->id,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'quoted_amount' => $data['quoted_amount'] ?? null,
            'lead_days' => $data['lead_days'] ?? null,
            'message' => $data['message'] ?? null,
            'status' => 'new',
        ]);

        return back()->with('success', __('site.materials.offer_saved'));
    }
}
