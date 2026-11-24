<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaConversionEvent extends Model
{
    protected $guarded = [];
    protected $hidden = ['payload'];
    protected $casts = [
        'payload' => 'encrypted:array', 'value' => 'decimal:2',
        'occurred_at' => 'datetime', 'next_attempt_at' => 'datetime',
        'first_attempt_at' => 'datetime', 'delivered_at' => 'datetime',
    ];
}
