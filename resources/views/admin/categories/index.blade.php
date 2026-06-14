@extends('layouts.admin')

@section('title', 'Danh mục')
@section('page-title', 'Danh mục sản phẩm')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- List --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-700">Danh sách danh mục</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-5 py-3 font-medium text-gray-500">Tên</th>
                    <th class="text-center px-5 py-3 font-medium text-gray-500">Sản phẩm</th>
                    <th class="text-center px-5 py-3 font-medium text-gray-500">Trạng thái</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($categories as $category)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-700">{{ $category->name }}</p>
                            <p class="text-xs text-gray-400">{{ $category->slug }}</p>
                        </td>
                        <td class="px-5 py-3 text-center text-gray-500">
                            {{ $category->products_count }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="text-xs px-2 py-0.5 rounded-full
                                         {{ $category->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $category->is_active ? 'Hiện' : 'Ẩn' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <form method="POST"
                                  action="{{ route('admin.categories.toggle-active', $category) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="text-xs {{ $category->is_active ? 'text-amber-500' : 'text-emerald-500' }} hover:underline">
                                    {{ $category->is_active ? 'Ẩn' : 'Hiện' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Add form --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <h2 class="font-semibold text-gray-700 mb-4">Thêm danh mục mới</h2>
        <form method="POST" action="{{ route('admin.categories.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">
                        Tên danh mục <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-pink-300
                                  @error('name') border-red-300 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Mô tả</label>
                    <textarea name="description" rows="3"
                              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                     focus:outline-none focus:ring-2 focus:ring-pink-300 resize-none">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Thứ tự hiển thị</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}"
                           min="0"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-pink-300">
                </div>
                <button type="submit"
                        class="w-full bg-pink-500 hover:bg-pink-600 text-white font-semibold
                               py-2.5 rounded-xl transition-colors text-sm">
                    Thêm danh mục
                </button>
            </div>
        </form>
    </div>
</div>
@endsection