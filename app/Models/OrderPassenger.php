<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPassenger extends Model
{
    protected $fillable = [
        'order_id',
        'full_name',
        'passenger_type',
        'document_type',
        'document_number',
        'date_of_birth',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
