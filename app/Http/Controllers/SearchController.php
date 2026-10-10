<?php

namespace App\Http\Controllers;

use App\Models\Booth;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $products = collect();
        $booths = collect();

        if ($q !== '') {
            $products = Product::query()
                ->where('status', 'active')
                ->where(function ($query) use ($q) {
                    $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.fa')) LIKE ?", ["%{$q}%"])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) LIKE ?", ["%{$q}%"]);
                })
                ->with('booth')
                ->paginate(12)
                ->withQueryString();

            $booths = Booth::approved()
                ->where(function ($query) use ($q) {
                    $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.fa')) LIKE ?", ["%{$q}%"])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) LIKE ?", ["%{$q}%"])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(description, '$.fa')) LIKE ?", ["%{$q}%"]);
                })
                ->withCount('products')
                ->take(6)
                ->get();
        }

        return view('search.index', compact('q', 'products', 'booths'));
    }
}
