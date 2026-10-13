<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = ['name', 'city', 'phone', 'lead_time_days'];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /** What is still on order with this supplier and not yet in the warehouse. */
    public function onOrderValue(): float
    {
        return (float) $this->purchaseOrders()
            ->whereIn('status', [PurchaseOrder::ORDERED, PurchaseOrder::PART_RECEIVED])
            ->sum('total');
    }
}
