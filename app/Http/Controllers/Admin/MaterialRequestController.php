<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaterialRequest;
use Illuminate\Http\Request;

class MaterialRequestController extends Controller
{
    public function index(Request $request)
    {
        $materialRequests = MaterialRequest::withCount('offers')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.materials.index', compact('materialRequests'));
    }

    public function show(MaterialRequest $materialRequest)
    {
        $offers = $materialRequest->offers()->with('booth')->latest()->paginate(20);

        return view('admin.materials.show', compact('materialRequest', 'offers'));
    }

    public function approve(MaterialRequest $materialRequest)
    {
        $materialRequest->update([
            'status' => 'approved',
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return back()->with('success', __('site.materials.approved'));
    }

    public function reject(Request $request, MaterialRequest $materialRequest)
    {
        $data = $request->validate(['rejection_reason' => 'required|string|max:500']);

        $materialRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $data['rejection_reason'],
        ]);

        return back()->with('success', __('site.materials.rejected'));
    }
}
