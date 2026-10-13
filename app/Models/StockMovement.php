<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = ['product_id', 'order_id', 'purchase_order_id', 'delta', 'balance_after', 'reason'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /** Where this movement came from, for the ledger's "source" column. */
    public function source(): string
    {
        return match (true) {
            $this->order_id !== null => 'Sales order',
            $this->purchase_order_id !== null => 'Purchase order',
            default => 'Adjustment',
        };
    }
}
