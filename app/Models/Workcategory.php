<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workcategory extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'sort_order',
    ];


    protected $table = 'workcategories';
}
