<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::roots()->with('children')->withCount('booths')->orderBy('sort_order')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::roots()->get();

        return view('admin.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_fa' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:4096',
        ]);

        $category = Category::create([
            'parent_id' => $data['parent_id'] ?? null,
            'name' => ['fa' => $data['name_fa'], 'en' => $data['name_en'] ?? $data['name_fa']],
            'slug' => Str::slug($data['name_en'] ?? $data['name_fa']) . '-' . Str::random(4),
            'icon' => $data['icon'] ?? null,
            'is_active' => true,
        ]);

        if ($request->hasFile('image')) {
            $category->update(['image' => $request->file('image')->store('categories', 'public')]);
        }

        return redirect()->route('admin.categories.index')->with('success', 'دسته‌بندی ایجاد شد.');
    }

    public function edit(Category $category)
    {
        $parents = Category::roots()->where('id', '!=', $category->id)->get();

        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name_fa' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:4096',
            'is_active' => 'boolean',
        ]);

        $category->name = ['fa' => $data['name_fa'], 'en' => $data['name_en'] ?? $data['name_fa']];
        $category->icon = $data['icon'] ?? null;
        $category->is_active = $data['is_active'] ?? true;

        if ($request->hasFile('image')) {
            if ($category->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($category->image);
            }
            $category->image = $request->file('image')->store('categories', 'public');
        }

        $category->save();

        return redirect()->route('admin.categories.index')->with('success', 'دسته‌بندی به‌روزرسانی شد.');
    }

    public function destroy(Category $category)
    {
        abort_if($category->children()->exists(), 422, 'ابتدا زیردسته‌ها را حذف کنید.');
        $category->delete();

        return back()->with('success', 'دسته‌بندی حذف شد.');
    }
}
