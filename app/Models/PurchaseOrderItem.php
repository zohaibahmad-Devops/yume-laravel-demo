<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = ['purchase_order_id', 'product_id', 'quantity', 'quantity_received', 'unit_cost'];

    protected $casts = [
        'unit_cost' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function outstanding(): int
    {
        return max(0, $this->quantity - $this->quantity_received);
    }

    public function lineTotal(): float
    {
        return $this->quantity * (float) $this->unit_cost;
    }
}
