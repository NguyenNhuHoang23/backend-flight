<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_name',
        'account_holder',
        'account_number',
        'amount',
        'date',
        'time',
        'ampm',
        'note',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date:Y-m-d',
    ];

    /**
     * Refund thuộc về customer
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
