<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index()
    {
        $products = Auth::user()->booth->products()->with('category', 'images')->latest()->paginate(15);

        return view('vendor.products.index', compact('products'));
    }

    public function create()
    {
        $categories = \App\Models\Category::where('is_active', true)->get();

        return view('vendor.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $booth = Auth::user()->booth;

        $data = $request->validate([
            'name_fa'     => 'required|string|max:255',
            'name_en'     => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|integer|min:1000',
            'stock'       => 'required|integer|min:0',
            'weight_grams' => 'required|integer|min:50|max:200000',
            'description_fa' => 'nullable|string',
            'images.*'    => 'nullable|image|max:2048',
            'video' => 'nullable|mimetypes:video/mp4,video/webm|max:51200',
            'discount_percent' => 'nullable|integer|min:0|max:90',
        ]);

        $product = $booth->products()->create([
            'category_id' => $data['category_id'],
            'name' => ['fa' => $data['name_fa'], 'en' => $data['name_en'] ?? $data['name_fa']],
            'description' => ['fa' => $data['description_fa'] ?? ''],
            'price' => $data['price'],
            'stock' => $data['stock'],
            'discount_percent' => $data['discount_percent'] ?? 0,
            'weight_grams' => $data['weight_grams'],
            'status' => 'active',
        ]);

        if ($request->hasFile('video')) {
            $path = $request->file('video')->store('product-videos', 'public');
            $product->update(['video_path' => $path, 'video_status' => 'pending']);
        }

        foreach ($request->file('images', []) as $i => $file) {
            $path = $file->store('products', 'public');
            $product->images()->create(['path' => $path, 'sort_order' => $i]);
        }

        return redirect()->route('vendor.products.index')->with('success', 'محصول با موفقیت ثبت شد.');
    }

    public function update(Request $request, Product $product)
    {
        abort_unless($product->booth_id === Auth::user()->booth->id, 403);

        $data = $request->validate([
            'price' => 'required|integer|min:1000',
            'stock' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $product->update($data);

        return back()->with('success', 'محصول به‌روزرسانی شد.');
    }

    public function destroy(Product $product)
    {
        abort_unless($product->booth_id === Auth::user()->booth->id, 403);
        $product->delete();

        return back()->with('success', 'محصول حذف شد.');
    }
}
