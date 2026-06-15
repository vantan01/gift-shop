@php $isEdit = isset($product); @endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Main fields --}}
    <div class="lg:col-span-2 space-y-5">

        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-700 mb-4">Thông tin cơ bản</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">
                        Tên sản phẩm <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="name"
                           value="{{ old('name', $product->name ?? '') }}"
                           id="product-name"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-pink-300
                                  @error('name') border-red-300 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">
                        Slug <span class="text-red-400">*</span>
                    </label>
                    <div class="flex gap-2">
                        <input type="text" name="slug"
                               value="{{ old('slug', $product->slug ?? '') }}"
                               id="product-slug"
                               class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-pink-300
                                      @error('slug') border-red-300 @enderror">
                        <button type="button" id="gen-slug"
                                class="text-xs bg-gray-100 hover:bg-gray-200 px-3 py-2
                                       rounded-xl transition-colors whitespace-nowrap">
                            Tự tạo
                        </button>
                    </div>
                    @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Mô tả ngắn</label>
                    <input type="text" name="short_description"
                           value="{{ old('short_description', $product->short_description ?? '') }}"
                           maxlength="300"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-pink-300">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Mô tả chi tiết</label>
                    <textarea name="description" rows="5"
                              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                     focus:outline-none focus:ring-2 focus:ring-pink-300 resize-none">{{ old('description', $product->description ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-700 mb-4">Giá & Kho</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">
                        Giá bán (₫) <span class="text-red-400">*</span>
                    </label>
                    <input type="number" name="price"
                           value="{{ old('price', $product->price ?? '') }}"
                           min="1000" step="1000"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-pink-300
                                  @error('price') border-red-300 @enderror">
                    @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">
                        Giá gốc (₫) <span class="text-gray-400 font-normal">tùy chọn</span>
                    </label>
                    <input type="number" name="compare_price"
                           value="{{ old('compare_price', $product->compare_price ?? '') }}"
                           min="1000" step="1000"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-pink-300
                                  @error('compare_price') border-red-300 @enderror">
                    @error('compare_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">
                        Tồn kho <span class="text-red-400">*</span>
                    </label>
                    <input type="number" name="stock"
                           value="{{ old('stock', $product->stock ?? 0) }}"
                           min="0"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-pink-300
                                  @error('stock') border-red-300 @enderror">
                    @error('stock') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">
                        Ngưỡng cảnh báo tồn kho
                    </label>
                    <input type="number" name="low_stock_threshold"
                           value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}"
                           min="1"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-pink-300">
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar options --}}
    <div class="space-y-5">

        {{-- Image Upload --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6" x-data="imagePreview()">
            <h2 class="font-semibold text-gray-700 mb-4">Hình ảnh sản phẩm</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-2">Ảnh chính</label>
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 relative overflow-hidden">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6" x-show="!imageUrl">
                                <span class="text-3xl mb-2 text-gray-400">📸</span>
                                <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Nhấn để tải lên</span></p>
                                <p class="text-xs text-gray-500">PNG, JPG, WEBP (Tối đa 2MB)</p>
                            </div>
                            <img x-show="imageUrl" :src="imageUrl" class="absolute inset-0 w-full h-full object-cover" style="display: none;">
                            <input type="file" name="image" class="hidden" accept="image/*" @change="fileChosen" />
                        </label>
                    </div>
                    @error('image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                
                {{-- Gallery --}}
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-2">Thư viện ảnh</label>
                    <input type="file" name="images[]" multiple accept="image/*"
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100">
                    @error('images.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>


        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-700 mb-4">Danh mục</h2>
            <select name="category_id"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-pink-300
                           @error('category_id') border-red-300 @enderror">
                <option value="">-- Chọn danh mục --</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}"
                            {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-700 mb-4">Hiển thị</h2>
            <div class="space-y-3">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                           class="accent-pink-500 w-4 h-4"
                           {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                    <div>
                        <p class="text-sm font-medium text-gray-700">Hiển thị sản phẩm</p>
                        <p class="text-xs text-gray-400">Bỏ chọn để ẩn khỏi storefront</p>
                    </div>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" value="1"
                           class="accent-pink-500 w-4 h-4"
                           {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }}>
                    <div>
                        <p class="text-sm font-medium text-gray-700">Sản phẩm nổi bật</p>
                        <p class="text-xs text-gray-400">Hiển thị ở trang chủ</p>
                    </div>
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="flex-1 bg-pink-500 hover:bg-pink-600 text-white font-semibold
                           py-2.5 rounded-xl transition-colors text-sm">
                {{ $isEdit ? 'Lưu thay đổi' : 'Thêm sản phẩm' }}
            </button>
            <a href="{{ route('admin.products.index') }}"
               class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600
                      hover:bg-gray-50 transition-colors">
                Hủy
            </a>
        </div>
    </div>
</div>

<script>
document.getElementById('gen-slug')?.addEventListener('click', async () => {
    const name = document.getElementById('product-name').value;
    if (!name) return;
    const res  = await fetch('{{ route('admin.products.generate-slug') }}', {
        method:  'POST',
        headers: {
            'Content-Type':  'application/json',
            'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ name }),
    });
    const data = await res.json();
    document.getElementById('product-slug').value = data.slug;
});
</script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('imagePreview', () => ({
        imageUrl: '{{ $isEdit && $product->image ? Storage::url($product->image) : '' }}',
        fileChosen(event) {
            this.fileToDataUrl(event, src => this.imageUrl = src)
        },
        fileToDataUrl(event, callback) {
            if (! event.target.files.length) return
            let file = event.target.files[0]
            let reader = new FileReader()
            reader.readAsDataURL(file)
            reader.onload = e => callback(e.target.result)
        }
    }))
})
</script>