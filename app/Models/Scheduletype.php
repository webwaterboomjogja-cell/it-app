<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scheduletype extends Model
{
   protected $fillable = [
        'name',
        'code',
        'color',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $table = 'scheduletypes';
}
