<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderFlight extends Model
{
    protected $fillable = [
        'order_id',
        'trip_type',
        'airline_name',
        'airline_code',
        'flight_number',
        'departure_airport',
        'arrival_airport',
        'departure_at',
        'arrival_at',
    ];

    protected $casts = [
        'departure_at' => 'datetime',
        'arrival_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
