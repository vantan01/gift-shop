@extends('layouts.admin')

@section('title', 'Quản lý sản phẩm')
@section('page-title', 'Sản phẩm')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div></div>
    <a href="{{ route('admin.products.create') }}"
       class="bg-pink-500 hover:bg-pink-600 text-white text-sm font-semibold
              px-4 py-2 rounded-xl transition-colors">
        + Thêm sản phẩm
    </a>
</div>

{{-- Filter bar --}}
<div class="bg-white rounded-2xl border border-gray-100 p-4 mb-5 flex flex-wrap gap-3">
    <form method="GET" action="{{ route('admin.products.index') }}"
          class="flex flex-wrap gap-3 flex-1">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Tìm sản phẩm..."
               class="border border-gray-200 rounded-xl px-3 py-2 text-sm
                      focus:outline-none focus:ring-2 focus:ring-pink-300 flex-1 min-w-40">

        <select name="category"
                class="border border-gray-200 rounded-xl px-3 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-pink-300">
            <option value="">Tất cả danh mục</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>

        <select name="status"
                class="border border-gray-200 rounded-xl px-3 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-pink-300">
            <option value="">Tất cả trạng thái</option>
            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Đang hiện</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Đang ẩn</option>
            <option value="deleted"  {{ request('status') === 'deleted'  ? 'selected' : '' }}>Đã xóa</option>
        </select>

        <button type="submit"
                class="bg-gray-800 text-white text-sm px-4 py-2 rounded-xl hover:bg-gray-700">
            Lọc
        </button>
        <a href="{{ route('admin.products.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2">
            Reset
        </a>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 font-medium text-gray-500">Sản phẩm</th>
                <th class="text-left px-5 py-3 font-medium text-gray-500">Danh mục</th>
                <th class="text-right px-5 py-3 font-medium text-gray-500">Giá</th>
                <th class="text-right px-5 py-3 font-medium text-gray-500">Tồn kho</th>
                <th class="text-center px-5 py-3 font-medium text-gray-500">Trạng thái</th>
                <th class="text-right px-5 py-3 font-medium text-gray-500">Thao tác</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($products as $product)
                <tr class="{{ $product->trashed() ? 'bg-red-50 opacity-60' : '' }} hover:bg-gray-50/50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $product->name }}</p>
                        <p class="text-xs text-gray-400">{{ $product->slug }}</p>
                    </td>
                    <td class="px-5 py-3 text-gray-500">
                        {{ $product->category->name }}
                    </td>
                    <td class="px-5 py-3 text-right">
                        <p class="font-medium text-gray-700">{{ $product->formattedPrice() }}</p>
                        @if ($product->isOnSale())
                            <p class="text-xs text-gray-400 line-through">
                                {{ $product->formattedComparePrice() }}
                            </p>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right">
                        <span class="{{ $product->isOutOfStock() ? 'text-red-500 font-semibold' : ($product->isLowStock() ? 'text-amber-600 font-semibold' : 'text-gray-700') }}">
                            {{ $product->stock }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        @if ($product->trashed())
                            <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">
                                Đã xóa
                            </span>
                        @elseif ($product->is_active)
                            <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">
                                Hiện
                            </span>
                        @else
                            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">
                                Ẩn
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-2">
                            @if ($product->trashed())
                                <form method="POST"
                                      action="{{ route('admin.products.restore', $product->id) }}">
                                    @csrf
                                    <button type="submit"
                                            class="text-xs text-emerald-600 hover:text-emerald-800">
                                        Khôi phục
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('admin.products.edit', $product) }}"
                                   class="text-xs text-blue-500 hover:text-blue-700">Sửa</a>

                                <form method="POST"
                                      action="{{ route('admin.products.toggle-active', $product) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="text-xs {{ $product->is_active ? 'text-amber-500 hover:text-amber-700' : 'text-emerald-500 hover:text-emerald-700' }}">
                                        {{ $product->is_active ? 'Ẩn' : 'Hiện' }}
                                    </button>
                                </form>

                                <form method="POST"
                                      action="{{ route('admin.products.destroy', $product) }}"
                                      onsubmit="return confirm('Xóa sản phẩm này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-xs text-red-400 hover:text-red-600">
                                        Xóa
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                        Không tìm thấy sản phẩm nào.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($products->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection