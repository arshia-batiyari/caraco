<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show(Category $category, Request $request)
    {
        $category->load('parent', 'children');

        $selectedSubIds = $request->has('sub') ? (array) $request->sub : [$category->id, ...$category->children->pluck('id')];

        $booths = \App\Models\Booth::approved()
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $selectedSubIds))
            ->withCount('products')
            ->when($request->online_only, fn ($q) => $q->where('online_payment_enabled', true))
            ->when($request->province, fn ($q) => $q->where('province', $request->province))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('category.show', compact('category', 'booths'));
    }
}
