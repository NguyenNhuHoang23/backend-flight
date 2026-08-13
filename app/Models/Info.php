<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Info extends Model
{
    protected $table = 'info';

    protected $fillable = [
        'hotline',
        'phone',
        'email',
        'address',
        'website',
        'facebook',
        'zalo',
        'messenger',
    ];
}