<?php

namespace App\Http\Controllers;

use App\Models\Booth;
use Illuminate\Http\Request;

class BoothController extends Controller
{
    public function show(Booth $booth, Request $request)
    {
        abort_unless($booth->status === 'approved', 404);

        $booth->load('categories');

        $products = $booth->products()
            ->inStock()
            ->when($request->category, fn ($q) => $q->where('category_id', $request->category))
            ->orderBy(
                match ($request->sort) {
                    'cheapest' => 'price',
                    'expensive' => 'price',
                    default => 'created_at',
                },
                $request->sort === 'expensive' ? 'desc' : ($request->sort === 'cheapest' ? 'asc' : 'desc')
            )
            ->with(['images', 'category'])
            ->paginate(9)
            ->withQueryString();

        return view('booth.show', compact('booth', 'products'));
    }
}
