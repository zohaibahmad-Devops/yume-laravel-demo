<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = ['reference', 'supplier_id', 'status', 'total', 'ordered_on', 'expected_on'];

    protected $casts = [
        'total' => 'decimal:2',
        'ordered_on' => 'date',
        'expected_on' => 'date',
    ];

    public const DRAFT = 'Draft';
    public const ORDERED = 'Ordered';
    public const PART_RECEIVED = 'Part received';
    public const RECEIVED = 'Received';
    public const CANCELLED = 'Cancelled';

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(PurchaseOrderEvent::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::ORDERED, self::PART_RECEIVED], true);
    }

    /**
     * The header status is never set by hand — it is read back from the lines,
     * so it cannot disagree with what was actually received.
     */
    public function statusFromLines(): string
    {
        if ($this->status === self::CANCELLED || $this->status === self::DRAFT) {
            return $this->status;
        }

        $outstanding = $this->items->sum(fn ($i) => max(0, $i->quantity - $i->quantity_received));
        $received = $this->items->sum('quantity_received');

        if ($outstanding === 0) {
            return self::RECEIVED;
        }

        return $received > 0 ? self::PART_RECEIVED : self::ORDERED;
    }

    /** Days late against the expected date; negative means still to come. */
    public function daysLate(): ?int
    {
        if (! $this->expected_on || ! $this->isOpen()) {
            return null;
        }

        return (int) $this->expected_on->diffInDays(now(), false);
    }
}
