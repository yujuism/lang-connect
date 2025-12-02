<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'stock',
        'rating',
        'image',
        'sku'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'rating' => 'decimal:1'
    ];

    /**
     * Get the category that owns the product
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Format price for display
     */
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Get stock status
     */
    public function getStockStatusAttribute()
    {
        if ($this->stock == 0) {
            return 'Out of Stock';
        } elseif ($this->stock <= 5) {
            return 'Low Stock';
        }
        return 'In Stock';
    }

    /**
     * Get stock badge class
     */
    public function getStockBadgeClassAttribute()
    {
        if ($this->stock == 0) {
            return 'bg-danger';
        } elseif ($this->stock <= 5) {
            return 'bg-warning';
        }
        return 'bg-success';
    }
}
