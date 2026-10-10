<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\MaterialRequest;
use Illuminate\Http\Request;

class MaterialRequestController extends Controller
{
    public function index(Request $request)
    {
        $type = in_array($request->query('type'), MaterialRequest::TYPES, true) ? $request->query('type') : null;

        $requests = MaterialRequest::approved()
            ->with(['offers' => fn ($q) => $q->where('user_id', auth()->id())])
            ->when($type, fn ($q) => $q->where('material_type', $type))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('vendor.materials.index', compact('requests'));
    }
}
