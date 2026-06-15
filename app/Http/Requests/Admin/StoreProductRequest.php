<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'category_id'         => ['required', 'exists:categories,id'],
            'name'                => ['required', 'string', 'max:200'],
            'slug'                => ['required', 'string', 'max:200', 'unique:products,slug',
                                      'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'short_description'   => ['nullable', 'string', 'max:300'],
            'description'         => ['nullable', 'string', 'max:5000'],
            'price'               => ['required', 'integer', 'min:1000', 'max:99000000'],
            'compare_price'       => ['nullable', 'integer', 'min:1000', 'gt:price'],
            'stock'               => ['required', 'integer', 'min:0', 'max:99999'],
            'low_stock_threshold' => ['required', 'integer', 'min:1', 'max:100'],
            'is_active'           => ['boolean'],
            'is_featured'         => ['boolean'],
            'image'               => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'images.*'            => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex'            => 'Slug chỉ được chứa chữ thường, số và dấu gạch ngang.',
            'slug.unique'           => 'Slug này đã tồn tại.',
            'compare_price.gt'      => 'Giá gốc phải lớn hơn giá bán.',
            'price.min'             => 'Giá tối thiểu 1.000₫.',
            'category_id.exists'    => 'Danh mục không tồn tại.',
        ];
    }
}