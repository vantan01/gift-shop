<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
// [Antigravity EDIT - Start] - Import DB facade
use Illuminate\Support\Facades\DB;
// [Antigravity EDIT - End]

class ProductController extends Controller
{
    /**
     * Danh sách sản phẩm — có filter, search, sort, pagination
     */
    public function index(Request $request)
    {
        $query = Product::with('category')
            ->active();

        // Filter theo category slug
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Search theo tên sản phẩm
        if ($request->filled('search')) {
            $search = $request->search;
            // [Antigravity EDIT - Start] - Chọn like/ilike tương thích SQLite vs pgsql
            /* Code cũ:
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('short_description', 'ilike', "%{$search}%");
            });
            */
            $driver = DB::connection()->getDriverName();
            $likeOperator = $driver === 'pgsql' ? 'ilike' : 'like';

            $query->where(function ($q) use ($search, $likeOperator) {
                $q->where('name', $likeOperator, "%{$search}%")
                  ->orWhere('short_description', $likeOperator, "%{$search}%");
            });
            // [Antigravity EDIT - End]
        }

        // Filter còn hàng
        if ($request->boolean('in_stock')) {
            $query->inStock();
        }

        // Filter khoảng giá
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (int) $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (int) $request->max_price);
        }

        // Sort
        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'price_asc'   => $query->orderBy('price', 'asc'),
            'price_desc'  => $query->orderBy('price', 'desc'),
            'popular'     => $query->orderBy('sold_count', 'desc'),
            'name_asc'    => $query->orderBy('name', 'asc'),
            default       => $query->orderBy('created_at', 'desc'), // newest
        };

        $products   = $query->paginate(12)->withQueryString();
        $categories = Category::active()->orderBy('sort_order')->get();

        // Category hiện tại đang chọn (để highlight menu)
        $currentCategory = $request->filled('category')
            ? Category::where('slug', $request->category)->first()
            : null;

        return view('pages.products.index', compact(
            'products',
            'categories',
            'currentCategory',
            'sort',
        ));
    }

    /**
     * Chi tiết sản phẩm
     */
    public function show(string $slug)
    {
        $product = Product::with('category')
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        // Sản phẩm liên quan: cùng category, loại trừ sản phẩm hiện tại
        $relatedProducts = Product::with('category')
            ->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('pages.products.show', compact('product', 'relatedProducts'));
    }
}