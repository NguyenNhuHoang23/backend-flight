<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AirlineDiscount extends Model
{
    protected $fillable = [
        'airline_code',
        'airline_name',
        'discount_rate',
        'is_custom_enabled'
    ];

    protected $casts = [
        'is_custom_enabled' => 'boolean',
        'discount_rate' => 'float',
    ];
}