<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
// [Antigravity EDIT - Start] - Import DB facade
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
// [Antigravity EDIT - End]

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->withTrashed(); // Bao gồm soft deleted

        if ($request->filled('search')) {
            // [Antigravity EDIT - Start] - Sửa 'ilike' thành operator tương thích DB driver
            /* Code cũ:
            $query->where('name', 'ilike', '%' . $request->search . '%');
            */
            $driver = DB::connection()->getDriverName();
            $likeOperator = $driver === 'pgsql' ? 'ilike' : 'like';
            $query->where('name', $likeOperator, '%' . $request->search . '%');
            // [Antigravity EDIT - End]
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'active'   => $query->where('is_active', true)->whereNull('deleted_at'),
                'inactive' => $query->where('is_active', false)->whereNull('deleted_at'),
                'deleted'  => $query->whereNotNull('deleted_at'),
                default    => null,
            };
        }

        $products   = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $categories = Category::active()->orderBy('sort_order')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::active()->orderBy('sort_order')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated() + [
            'is_active'   => $request->boolean('is_active', true),
            'is_featured' => $request->boolean('is_featured', false),
        ];

        $imagesData = $this->handleImageUpload($request);
        $data = array_merge($data, $imagesData);

        Product::create($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Thêm sản phẩm thành công!');
    }

    public function edit(Product $product)
    {
        $categories = Category::active()->orderBy('sort_order')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated() + [
            'is_active'   => $request->boolean('is_active', true),
            'is_featured' => $request->boolean('is_featured', false),
        ];

        $imagesData = $this->handleImageUpload($request, $product);
        $data = array_merge($data, $imagesData);

        $product->update($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', "Cập nhật \"{$product->name}\" thành công!");
    }

    /**
     * Handle Image Upload
     */
    private function handleImageUpload(Request $request, Product $product = null)
    {
        $data = [];
        if ($request->hasFile('image')) {
            if ($product && $product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        if ($request->hasFile('images')) {
            $images = $product && is_array($product->images) ? $product->images : [];
            foreach ($request->file('images') as $file) {
                $images[] = $file->store('products', 'public');
            }
            $data['images'] = $images;
        }

        return $data;
    }

    /**
     * Toggle ẩn/hiện sản phẩm (soft visibility)
     */
    public function toggleActive(Product $product)
    {
        $product->update(['is_active' => ! $product->is_active]);

        $status = $product->is_active ? 'hiện' : 'ẩn';
        return back()->with('success', "Đã {$status} sản phẩm \"{$product->name}\".");
    }

    /**
     * Soft delete
     */
    public function destroy(Product $product)
    {
        // Không cho xóa nếu có order đang active
        $hasActiveOrders = $product->orderItems()
            ->whereHas('order', fn($q) => $q->whereNotIn('status', [
                \App\Enums\OrderStatus::DELIVERED->value,
                \App\Enums\OrderStatus::CANCELLED->value,
            ]))
            ->exists();

        if ($hasActiveOrders) {
            return back()->with('error', 'Không thể xóa sản phẩm đang có đơn hàng chưa hoàn thành.');
        }

        $product->delete(); // Soft delete
        return back()->with('success', "Đã xóa sản phẩm \"{$product->name}\".");
    }

    /**
     * Khôi phục soft deleted product
     */
    public function restore(int $product)
    {
        $product = Product::withTrashed()->findOrFail($product);
        $product->restore();
        return back()->with('success', "Đã khôi phục \"{$product->name}\".");
    }

    /**
     * Auto-generate slug từ name
     */
    public function generateSlug(Request $request)
    {
        $slug = Str::slug($request->name);
        // Đảm bảo unique
        $original = $slug;
        $count = 1;
        // [Antigravity EDIT - Start] - Kiểm tra trùng lặp slug bao gồm cả các sản phẩm đã bị xóa mềm (Soft Deleted)
        /* Code cũ:
        while (Product::where('slug', $slug)->exists()) {
        */
        while (Product::withTrashed()->where('slug', $slug)->exists()) {
            // [Antigravity EDIT - End]
            $slug = $original . '-' . $count++;
        }
        return response()->json(['slug' => $slug]);
    }
}