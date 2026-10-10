<?php

namespace App\Http\Controllers;

use App\Models\Booth;
use App\Models\Category;
use App\Models\Product;

class PageController extends Controller
{
    public function about()
    {
        $categories = Category::roots()->where('is_active', true)->orderBy('sort_order')->withCount('booths')->get();

        $stats = [
            'booths' => Booth::approved()->count(),
            'categories' => Category::where('is_active', true)->count(),
            'products' => Product::where('status', 'active')->count(),
        ];

        return view('pages.about', compact('categories', 'stats'));
    }
}