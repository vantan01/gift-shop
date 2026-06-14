<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationship: một Category có nhiều Product
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // Scope: chỉ lấy category đang active
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}