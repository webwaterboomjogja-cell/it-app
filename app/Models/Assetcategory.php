<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assetcategory extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'sort_order',
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(Itassests::class);
    }
}
