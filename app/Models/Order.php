<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'recipient_name',
        'recipient_phone',
        'shipping_address',
        'shipping_city',
        'gift_message',
        'scheduled_delivery_date',
        'subtotal',
        'shipping_fee',
        'discount_amount',
        'total',
        'coupon_code',
        'idempotency_key',
        'customer_note',
        'internal_note',
    ];

    protected $casts = [
        'status'                   => OrderStatus::class,
        'subtotal'                 => 'integer',
        'shipping_fee'             => 'integer',
        'discount_amount'          => 'integer',
        'total'                    => 'integer',
        'scheduled_delivery_date'  => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Format order number: GS-20240101-XXXX
    public static function generateOrderNumber(): string
    {
        $date   = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return "GS-{$date}-{$random}";
    }

    // Format tiền
    public function formattedTotal(): string
    {
        return number_format($this->total, 0, ',', '.') . '₫';
    }

    public function formattedSubtotal(): string
    {
        return number_format($this->subtotal, 0, ',', '.') . '₫';
    }

    // Có thể hủy không
    public function isCancellable(): bool
    {
        return $this->status->isCancellable();
    }
}