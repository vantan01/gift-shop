@extends('layouts.admin')

@section('title', 'Sửa Danh mục')
@section('page-title', 'Sửa Danh mục')
@section('breadcrumb', 'Admin › Danh mục › Sửa')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.categories.update', $category) }}">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">
                        Tên danh mục <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $category->name) }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-pink-300
                                  @error('name') border-red-300 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Mô tả</label>
                    <textarea name="description" rows="4"
                              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                     focus:outline-none focus:ring-2 focus:ring-pink-300 resize-none">{{ old('description', $category->description) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Thứ tự hiển thị</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}"
                           min="0"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-pink-300">
                </div>
                
                <div class="flex items-center gap-3 pt-4">
                    <a href="{{ route('admin.categories.index') }}"
                       class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 border border-gray-200">
                        Huỷ
                    </a>
                    <button type="submit"
                            class="px-5 py-2.5 bg-pink-500 hover:bg-pink-600 text-white font-semibold
                                   rounded-xl transition-colors text-sm">
                        Cập nhật
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
