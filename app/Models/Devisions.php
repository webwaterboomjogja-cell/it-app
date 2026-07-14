<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Devisions extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'sort_order',
    ];
}
