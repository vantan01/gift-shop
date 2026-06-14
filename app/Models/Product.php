<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{   
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'compare_price',
        'stock',
        'image',
        'images',
        'is_active',
        'is_featured',
        'low_stock_threshold',
        'sold_count',
    ];

    protected $casts = [
        'price'               => 'integer',
        'compare_price'       => 'integer',
        'stock'               => 'integer',
        'is_active'           => 'boolean',
        'is_featured'         => 'boolean',
        'low_stock_threshold' => 'integer',
        'sold_count'          => 'integer',
        'images'              => 'array',   // JSON tự decode thành array
    ];

    // Relationship: Product thuộc về một Category
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Scope: chỉ lấy product active
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope: chỉ lấy product còn hàng
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    // Helper: kiểm tra sắp hết hàng
    public function isLowStock(): bool
    {
        return $this->stock > 0 && $this->stock <= $this->low_stock_threshold;
    }

    // Helper: kiểm tra hết hàng
    public function isOutOfStock(): bool
    {
        return $this->stock === 0;
    }

    // Helper: có đang giảm giá không
    public function isOnSale(): bool
    {
        return $this->compare_price !== null && $this->compare_price > $this->price;
    }

    // Helper: % giảm giá
    public function discountPercent(): int
    {
        if (! $this->isOnSale()) return 0;

        return (int) round(
            (($this->compare_price - $this->price) / $this->compare_price) * 100
        );
    }

    // Helper: format giá VNĐ
    public function formattedPrice(): string
    {
        return number_format($this->price, 0, ',', '.') . '₫';
    }

    public function formattedComparePrice(): string
    {
        if (! $this->compare_price) return '';
        return number_format($this->compare_price, 0, ',', '.') . '₫';
    }


    public function orderItems(): HasMany
    {
        return $this->hasMany(\App\Models\OrderItem::class);
    }
}
