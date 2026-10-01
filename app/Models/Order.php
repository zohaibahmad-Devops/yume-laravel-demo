<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = ['reference', 'dealer_id', 'status', 'total', 'placed_on'];

    protected $casts = [
        'total' => 'decimal:2',
        'placed_on' => 'date',
    ];

    /** The order moves through these in sequence; nothing skips a step. */
    public const STATUSES = ['New', 'Confirmed', 'Packed', 'Dispatched', 'Delivered'];

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function events(): HasMany
    {
        return ->hasMany(OrderEvent::class);
    }

    public function movements(): HasMany
    {
        return ->hasMany(StockMovement::class);
    }

    public function nextStatus(): ?string
    {
        $i = array_search($this->status, self::STATUSES, true);

        return ($i === false || $i === count(self::STATUSES) - 1) ? null : self::STATUSES[$i + 1];
    }
}
