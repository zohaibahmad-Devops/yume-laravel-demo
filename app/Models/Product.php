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

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Committed to sales orders that have been taken but not yet dispatched.
     * Free stock is what is left after those promises — the only figure a new
     * order should ever be checked against.
     */
    public function committed(): int
    {
        return (int) $this->orderItems()
            ->whereHas('order', fn ($q) => $q->whereIn('status', ['New', 'Confirmed', 'Packed']))
            ->sum('quantity');
    }

    public function free(): int
    {
        return $this->stock - $this->committed();
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
