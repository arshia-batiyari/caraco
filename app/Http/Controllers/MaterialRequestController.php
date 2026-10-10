<?php

namespace App\Http\Controllers;

use App\Models\Booth;
use App\Models\MaterialRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MaterialRequestController extends Controller
{
    public function index(Request $request)
    {
        $type = in_array($request->query('type'), MaterialRequest::TYPES, true) ? $request->query('type') : null;
        $fulfillment = in_array($request->query('fulfillment'), MaterialRequest::FULFILLMENTS, true) ? $request->query('fulfillment') : null;

        $requests = MaterialRequest::approved()
            ->when($type, fn ($q) => $q->where('material_type', $type))
            ->when($fulfillment, fn ($q) => $q->where('fulfillment', $fulfillment))
            ->when($request->province, fn ($q) => $q->where('province', $request->province))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $provinces = MaterialRequest::approved()->whereNotNull('province')->distinct()->orderBy('province')->pluck('province');

        $suppliers = Booth::materialSuppliers()
            ->with('categories')
            ->withCount('products')
            ->latest()
            ->limit(8)
            ->get();

        return view('materials.index', compact('requests', 'provinces', 'suppliers'));
    }

    public function create()
    {
        return view('materials.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        MaterialRequest::create([
            'user_id' => auth()->id(),
            'name' => $data['name'],
            'phone' => $data['phone'],
            'title' => ['fa' => $data['title'], 'en' => $data['title']],
            'description' => ['fa' => $data['description'] ?? ''],
            'material_type' => $data['material_type'],
            'fulfillment' => $data['fulfillment'],
            'quantity_label' => $data['quantity_label'],
            'needed_on' => $data['needed_on'] ?? null,
            'province' => $data['province'],
            'city' => $data['city'] ?? null,
            'delivery_place' => $data['delivery_place'] ?? null,
            'budget' => $data['budget'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('materials.index')->with('success', __('site.materials.saved'));
    }

    public function show(MaterialRequest $materialRequest)
    {
        abort_unless($materialRequest->status === 'approved', 404);

        $ownOffer = auth()->check()
            ? $materialRequest->offers()->where('user_id', auth()->id())->latest()->first()
            : null;

        return view('materials.show', [
            'materialRequest' => $materialRequest,
            'ownOffer' => $ownOffer,
        ]);
    }

    private function validated(Request $request): array
    {
        if ($request->input('budget') === '') {
            $request->merge(['budget' => null]);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'material_type' => ['required', 'in:'.implode(',', MaterialRequest::TYPES)],
            'fulfillment' => ['required', 'in:'.implode(',', MaterialRequest::FULFILLMENTS)],
            'quantity_label' => ['required', 'string', 'max:120'],
            'needed_on' => ['nullable', 'date'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'delivery_place' => ['nullable', 'string', 'max:255'],
            'budget' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
