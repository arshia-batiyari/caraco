<?php

namespace App\Http\Controllers;

use App\Models\PropertyRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PropertyRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = PropertyRequest::approved()
            ->when($request->type, fn ($q) => $q->where('property_type', $request->type))
            ->when($request->deal, fn ($q) => $q->where('deal_type', $request->deal))
            ->when($request->province, fn ($q) => $q->where('province', $request->province))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $provinces = PropertyRequest::approved()->whereNotNull('province')->distinct()->pluck('province');

        return view('property-requests.index', compact('requests', 'provinces'));
    }

    public function create()
    {
        return view('property-requests.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'phone'         => ['required', 'string', 'max:20'],
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string', 'max:2000'],
            'property_type' => ['required', 'in:land,apartment,villa,commercial,other'],
            'deal_type'     => ['required', 'in:sale,partnership,barter,both'],
            'min_area_sqm'  => ['nullable', 'integer', 'min:1'],
            'max_area_sqm'  => ['nullable', 'integer', 'min:1'],
            'province'      => ['required', 'string', 'max:100'],
            'city'          => ['nullable', 'string', 'max:100'],
            'budget'        => ['nullable', 'integer', 'min:0'],
        ]);

        PropertyRequest::create([
            'user_id' => auth()->id(),
            'name' => $data['name'],
            'phone' => $data['phone'],
            'title' => ['fa' => $data['title'], 'en' => $data['title']],
            'description' => ['fa' => $data['description'] ?? ''],
            'property_type' => $data['property_type'],
            'deal_type' => $data['deal_type'],
            'min_area_sqm' => $data['min_area_sqm'] ?? null,
            'max_area_sqm' => $data['max_area_sqm'] ?? null,
            'province' => $data['province'],
            'city' => $data['city'] ?? null,
            'budget' => $data['budget'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('property-requests.index')
            ->with('success', 'درخواست شما ثبت شد و بعد از بررسی ادمین در سایت نمایش داده می‌شود.');
    }

    public function show(PropertyRequest $propertyRequest)
    {
        abort_unless($propertyRequest->status === 'approved', 404);

        return view('property-requests.show', ['propertyRequest' => $propertyRequest]);
    }
}
