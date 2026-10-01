<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dealer extends Model
{
    protected $fillable = ['name', 'city', 'phone', 'credit_limit'];

    protected $casts = [
        'credit_limit' => 'decimal:2',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function outstanding(): float
    {
        return (float) $this->orders()->whereIn('status', ['New', 'Confirmed', 'Packed', 'Dispatched'])->sum('total');
    }
}
