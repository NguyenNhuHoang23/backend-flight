<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = [
        'bank_name',
        'account_number',
        'account_name',
        'transfer_content',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
