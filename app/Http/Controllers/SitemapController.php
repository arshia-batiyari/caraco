<?php

namespace App\Http\Controllers;

use App\Models\Booth;
use App\Models\Category;
use App\Models\MaterialRequest;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * نقشه‌سایت پویا — خودش تازه می‌شه هروقت دسته‌بندی/غرفه/محصول جدید اضافه بشه.
     * برای جلوگیری از فشار به دیتابیس در هر کراول گوگل، ۱ ساعت کش می‌شه.
     */
    public function index()
    {
        $urls = Cache::remember('sitemap.urls', now()->addHour(), function () {
            $urls = collect();

            $urls->push(['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily']);
            $urls->push(['loc' => route('materials.index'), 'priority' => '0.8', 'changefreq' => 'daily']);

            MaterialRequest::approved()->get()->each(function (MaterialRequest $request) use (&$urls) {
                $urls->push([
                    'loc' => route('materials.show', $request),
                    'priority' => '0.6',
                    'changefreq' => 'weekly',
                    'lastmod' => $request->updated_at->toAtomString(),
                ]);
            });

            Category::where('is_active', true)->get()->each(function (Category $cat) use (&$urls) {
                $urls->push([
                    'loc' => route('category.show', $cat->slug),
                    'priority' => $cat->parent_id ? '0.7' : '0.8',
                    'changefreq' => 'daily',
                    'lastmod' => $cat->updated_at->toAtomString(),
                ]);
            });

            Booth::approved()->get()->each(function (Booth $booth) use (&$urls) {
                $urls->push([
                    'loc' => route('booth.show', $booth->slug),
                    'priority' => '0.7',
                    'changefreq' => 'daily',
                    'lastmod' => $booth->updated_at->toAtomString(),
                ]);
            });

            Product::where('status', 'active')->get()->each(function (Product $product) use (&$urls) {
                $urls->push([
                    'loc' => route('product.show', $product),
                    'priority' => '0.6',
                    'changefreq' => 'weekly',
                    'lastmod' => $product->updated_at->toAtomString(),
                ]);
            });

            return $urls;
        });

        return response()
            ->view('sitemap.index', compact('urls'))
            ->header('Content-Type', 'application/xml');
    }
}
