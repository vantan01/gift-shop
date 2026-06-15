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
        $categories = Category::withCount('products')
            ->orderBy('sort_order')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order'  => ['integer', 'min:0'],
        ]);

        Category::create([
            ...$data,
            'slug'      => Str::slug($data['name']),
            'is_active' => true,
        ]);

        return back()->with('success', 'Thêm danh mục thành công!');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order'  => ['integer', 'min:0'],
        ]);

        $category->update([
            ...$data,
            'slug' => Str::slug($data['name']),
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Cập nhật danh mục thành công!');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Không thể xoá danh mục đang có sản phẩm.');
        }

        $category->delete();
        return back()->with('success', 'Đã xoá danh mục thành công.');
    }

    public function toggleActive(Category $category)
    {
        $category->update(['is_active' => ! $category->is_active]);
        $status = $category->is_active ? 'hiện' : 'ẩn';
        return back()->with('success', "Đã {$status} danh mục \"{$category->name}\".");
    }
}