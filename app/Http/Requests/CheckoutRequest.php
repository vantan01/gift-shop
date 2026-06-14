<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth check đã có ở route middleware
    }

    public function rules(): array
    {
        return [
            'recipient_name'           => ['required', 'string', 'max:100'],
            'recipient_phone'          => ['required', 'string', 'max:20'],
            'shipping_address'         => ['required', 'string', 'max:255'],
            'shipping_city'            => ['required', 'string', 'max:100'],
            'gift_message'             => ['nullable', 'string', 'max:300'],
            'scheduled_delivery_date'  => ['nullable', 'date', 'after_or_equal:today'],
            'customer_note'            => ['nullable', 'string', 'max:500'],
            'idempotency_key'          => ['required', 'string', 'size:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_name.required'          => 'Vui lòng nhập tên người nhận.',
            'recipient_phone.required'         => 'Vui lòng nhập số điện thoại.',
            'shipping_address.required'        => 'Vui lòng nhập địa chỉ giao hàng.',
            'shipping_city.required'           => 'Vui lòng chọn tỉnh/thành phố.',
            'gift_message.max'                 => 'Lời nhắn tối đa 300 ký tự.',
            'scheduled_delivery_date.after_or_equal' => 'Ngày giao hàng không thể là ngày trong quá khứ.',
            'idempotency_key.required'         => 'Phiên checkout không hợp lệ. Vui lòng thử lại.',
        ];
    }
}