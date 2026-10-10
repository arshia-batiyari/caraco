<?php

namespace App\Http\Controllers\Landowner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PropertyController extends Controller
{
    public function create(): \Illuminate\View\View
    {
        return view('landowner.properties.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $property = Auth::user()->properties()->create([
            'title' => ['fa' => $data['title_fa'], 'en' => $data['title_fa']],
            'description' => ['fa' => $data['description_fa'] ?? ''],
            'property_type' => $data['property_type'],
            'deal_type' => $data['deal_type'],
            'area_sqm' => $data['area_sqm'] ?? null,
            'province' => $data['province'],
            'city' => $data['city'],
            'address' => $data['address'] ?? null,
            'document_type' => $data['document_type'] ?? null,
            'price' => $data['price'] ?? null,
            'status' => 'pending', // هر آگهی جدید باید توسط ادمین بررسی شود
        ]);

        $this->storeImages($request, $property);

        return redirect()->route('landowner.dashboard')
            ->with('success', 'ملک شما با موفقیت ثبت شد و بعد از بررسی و تأیید ادمین در سایت نمایش داده می‌شود.');
    }

    public function edit(Property $property): \Illuminate\View\View
    {
        $this->authorizeOwner($property);

        return view('landowner.properties.edit', compact('property'));
    }

    public function update(Request $request, Property $property): RedirectResponse
    {
        $this->authorizeOwner($property);

        $data = $this->validateData($request);

        $property->update([
            'title' => ['fa' => $data['title_fa'], 'en' => $data['title_fa']],
            'description' => ['fa' => $data['description_fa'] ?? ''],
            'property_type' => $data['property_type'],
            'deal_type' => $data['deal_type'],
            'area_sqm' => $data['area_sqm'] ?? null,
            'province' => $data['province'],
            'city' => $data['city'],
            'address' => $data['address'] ?? null,
            'document_type' => $data['document_type'] ?? null,
            'price' => $data['price'] ?? null,
            // بعد از هر ویرایش، آگهی دوباره باید تأیید بشه چون محتوا عوض شده
            'status' => 'pending',
            'rejection_reason' => null,
            'approved_at' => null,
        ]);

        $this->storeImages($request, $property);

        return redirect()->route('landowner.dashboard')
            ->with('success', 'تغییرات ذخیره شد. آگهی دوباره در صف بررسی ادمین قرار گرفت.');
    }

    public function destroy(Property $property): RedirectResponse
    {
        $this->authorizeOwner($property);

        foreach ($property->images ?? [] as $path) {
            Storage::disk('public')->delete($path);
        }

        $property->delete();

        return back()->with('success', 'آگهی ملک حذف شد.');
    }

    protected function authorizeOwner(Property $property): void
    {
        abort_unless($property->user_id === Auth::id(), 403);
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'title_fa'      => ['required', 'string', 'max:255'],
            'description_fa' => ['nullable', 'string', 'max:2000'],
            'property_type' => ['required', 'in:land,apartment,villa,commercial,other'],
            'deal_type'     => ['required', 'in:sale,partnership,barter,both'],
            'area_sqm'      => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'province'      => ['required', 'string', 'max:100'],
            'city'          => ['required', 'string', 'max:100'],
            'address'       => ['nullable', 'string', 'max:500'],
            'document_type' => ['nullable', 'string', 'max:100'],
            'price'         => ['nullable', 'integer', 'min:0'],
            'images.*'      => ['nullable', 'image', 'max:4096'],
        ]);
    }

    protected function storeImages(Request $request, Property $property): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $existing = $property->images ?? [];

        foreach ($request->file('images', []) as $file) {
            $existing[] = $file->store('properties', 'public');
        }

        // حداکثر ۸ عکس برای هر آگهی نگه می‌داریم تا فضای ذخیره‌سازی کنترل‌شده بماند
        $property->update(['images' => array_slice($existing, -8)]);
    }
}
