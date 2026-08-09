<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_code',
        'status',
        'booking_at',
        'contact_name',
        'contact_phone',
        'contact_email',
        'total_amount',
        'payment_method',
        'payment_bill_image',
        'transfer_content',
    ];

    protected $casts = [
        'booking_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function passengers(): HasMany
    {
        return $this->hasMany(OrderPassenger::class);
    }

    public function flights(): HasMany
    {
        return $this->hasMany(OrderFlight::class);
    }
}
