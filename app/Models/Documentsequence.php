<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documentsequence extends Model
{
    protected $fillable = [
        'document_type',
        'year',
        'month',
        'last_number',
    ];

    protected $table = 'document_sequences';

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'last_number' => 'integer',
        ];
    }
}
