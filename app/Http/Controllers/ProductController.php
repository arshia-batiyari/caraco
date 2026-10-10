<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        $product->load('images', 'booth', 'category');

        $related = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inStock()
            ->limit(4)
            ->get();

        $product->loadMissing('images');

        return view('product.show', compact('product', 'related'));
    }

    /** افزودن محصول به سبد خرید (سبد در سشن ذخیره می‌شود) */
    public function addToCart(Request $request, Product $product)
    {
        $qty = max(1, (int) $request->input('quantity', 1));
        $cart = Session::get('cart', []);
        $cart[$product->id] = ($cart[$product->id] ?? 0) + $qty;
        Session::put('cart', $cart);

        return back()->with('success', 'محصول به سبد خرید اضافه شد.');
    }

    /** بازگرداندن اطلاعات محصول برای مودال */
    public function modal(Product $product)
    {
        $product->load('images', 'booth', 'category');

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'slug' => $product->slug,
                'name' => $product->localized_name,
                'description' => $product->description[app()->getLocale()] ?? $product->description['fa'] ?? '',
                'price' => (int) $product->price,
                'final_price' => (int) $product->final_price,
                'discount_percent' => (int) ($product->discount_percent ?? 0),
                'stock' => (int) $product->stock,
                'image_url' => $product->image_url,
                'images' => $product->images->map(fn($i) => $i->image_url)->toArray(),
                'booth_name' => $product->booth->localized_name,
                'booth_url' => route('booth.show', $product->booth->slug),
                'booth_online_payment' => (bool) $product->booth->online_payment_enabled,
                'booth_city' => $product->booth->city ?? '',
                'category_name' => $product->category?->localized_name ?? '',
                'add_to_cart_text' => __('site.common.add_to_cart'),
                'toman_text' => __('site.common.toman'),
                'stock_count_text' => __('site.common.items_left', ['count' => $product->stock]),
                'unavailable_text' => __('site.common.unavailable'),
            ],
        ]);
    }

    /** افزودن محصول به سبد خرید (API برای مودال) */
    public function addToCartAjax(Request $request, Product $product)
    {
        $qty = max(1, (int) $request->input('quantity', 1));
        $cart = Session::get('cart', []);
        $cart[$product->id] = ($cart[$product->id] ?? 0) + $qty;
        Session::put('cart', $cart);

        return response()->json([
            'success' => true,
            'message' => __('site.cart.added_title'),
            'cart_count' => array_sum($cart),
        ]);
    }
}