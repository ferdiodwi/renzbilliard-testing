<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'price',
        'stock',
        'image',
        'is_available',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'category_id' => 'integer',
        'is_available' => 'boolean',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope for available products
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Check if product is in stock
     */
    public function isInStock(): bool
    {
        return $this->stock === null || $this->stock > 0;
    }

    /**
     * Reduce stock
     */
    public function reduceStock(int $quantity): void
    {
        if ($this->stock !== null) {
            $this->stock -= $quantity;
            $this->save();
        }
    }
}
