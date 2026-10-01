<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['sku', 'name', 'category', 'unit', 'cost', 'price', 'stock', 'reorder_level'];

    protected $casts = [
        'cost' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** A line is low when it has fallen to or below the level the buyer set. */
    public function isLow(): bool
    {
        return $this->stock <= $this->reorder_level;
    }

    public function marginPercent(): float
    {
        if ((float) $this->price <= 0) {
            return 0;
        }

        return round((((float) $this->price - (float) $this->cost) / (float) $this->price) * 100, 1);
    }
}
