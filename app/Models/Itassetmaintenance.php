<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Itassetmaintenance extends Model
{
    protected $fillable = [
        'itasset_id',
        'maintenance_date',
        'problem',
        'action_taken',
        'handled_by_user_id',
        'cost',
        'status',
        'notes',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'cost' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::created(function (Itassetmaintenance $maintenance) {
            if ($maintenance->status === 'proses') {
                $maintenance->asset?->update([
                    'status' => 'maintenance',
                ]);
            }

            if ($maintenance->status === 'selesai') {
                $maintenance->asset?->update([
                    'status' => 'aktif',
                ]);
            }

            if ($maintenance->status === 'gagal' || $maintenance->status === 'perlu_penggantian') {
                $maintenance->asset?->update([
                    'status' => 'rusak',
                ]);
            }
        });

        static::updated(function (Itassetmaintenance $maintenance) {
            if ($maintenance->status === 'proses') {
                $maintenance->asset?->update([
                    'status' => 'maintenance',
                ]);
            }

            if ($maintenance->status === 'selesai') {
                $maintenance->asset?->update([
                    'status' => 'aktif',
                ]);
            }

            if ($maintenance->status === 'gagal' || $maintenance->status === 'perlu_penggantian') {
                $maintenance->asset?->update([
                    'status' => 'rusak',
                ]);
            }
        });
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Itassests::class, 'itasset_id');
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by_user_id');
    }
}
