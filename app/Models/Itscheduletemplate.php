<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Itscheduletemplate extends Model
{
    protected $table = 'itscheduletemplates';

    protected $fillable = [
        'name',
        'type',
        'start_time',
        'end_time',
        'location',
        'notes',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
