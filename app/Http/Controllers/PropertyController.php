<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $properties = Property::approved()
            ->with('owner')
            ->when($request->type, fn ($q) => $q->where('property_type', $request->type))
            ->when($request->deal, fn ($q) => $q->where('deal_type', $request->deal))
            ->when($request->province, fn ($q) => $q->where('province', $request->province))
            ->when($request->city, fn ($q) => $q->where('city', $request->city))
            ->when($request->min_price, fn ($q) => $q->where('price', '>=', (int) $request->min_price))
            ->when($request->max_price, fn ($q) => $q->where('price', '<=', (int) $request->max_price))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $provinces = Property::approved()->whereNotNull('province')->distinct()->pluck('province');

        return view('properties.index', compact('properties', 'provinces'));
    }

    public function show(Property $property)
    {
        abort_unless($property->status === 'approved', 404);

        $related = Property::approved()
            ->where('id', '!=', $property->id)
            ->where('property_type', $property->property_type)
            ->latest()
            ->limit(3)
            ->get();

        return view('properties.show', compact('property', 'related'));
    }
}
