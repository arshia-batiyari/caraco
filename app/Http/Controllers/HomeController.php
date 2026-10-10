<?php

namespace App\Http\Controllers;

use App\Models\Booth;
use App\Models\Category;
use App\Models\MaterialRequest;
use App\Models\Product;
use App\Models\Property;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::roots()->where('is_active', true)->orderBy('sort_order')->withCount('booths')->with('children')->get();

        $featuredBooths = Booth::approved()
            ->with('categories')
            ->withCount('products')
            ->inRandomOrder()
            ->limit(8)
            ->get();

        $buildingMaterials = Category::where('slug', 'building-materials')->first();
        $buildingBooths = collect();
        if ($buildingMaterials) {
            $buildingMaterials->load('children');
            $buildingChildIds = $buildingMaterials->children->pluck('id');
            $buildingBooths = Booth::approved()
                ->whereHas('categories', fn($q) => $q->whereIn('categories.id', $buildingChildIds))
                ->with('categories')
                ->withCount('products')
                ->limit(8)
                ->get();
        }

        $buildingCategoryIds = Category::where('slug', 'like', 'building-%')->pluck('id');
        $buildingProducts = Product::with(['booth', 'images', 'category'])
            ->where('status', 'active')
            ->whereIn('category_id', $buildingCategoryIds)
            ->latest()
            ->limit(12)
            ->get();

        $latestProperties = Property::approved()->latest()->limit(8)->get();
        $materialRequests = MaterialRequest::approved()->latest()->limit(8)->get();

        $discountedProducts = Product::with(['booth', 'images', 'category'])->where('status', 'active')->where('discount_percent', '>', 0)->inRandomOrder()->limit(12)->get();
        $randomProducts = Product::with(['booth', 'images', 'category'])->where('status', 'active')->latest()->limit(12)->get();

        $stats = [
            'booths' => Booth::approved()->count(),
            'categories' => Category::where('is_active', true)->count(),
            'products' => Product::where('status', 'active')->count(),
        ];

        return view('home.index', compact(
            'categories',
            'featuredBooths',
            'buildingBooths',
            'buildingProducts',
            'latestProperties',
            'materialRequests',
            'discountedProducts',
            'randomProducts',
            'stats'
        ));
    }
}
