<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Itassetmovement extends Model
{
    protected $fillable = [
        'itasset_id',
        'from_location_id',
        'to_location_id',
        'from_user_id',
        'to_user_id',
        'moved_at',
        'type',
        'condition_when_moved',
        'notes',
    ];

    protected $casts = [
        'moved_at' => 'date',
    ];

    protected static function booted(): void
    {
        static::created(function (ItassetMovement $movement) {
            $movement->syncAssetCurrentData();
        });

        static::updated(function (ItassetMovement $movement) {
            $movement->syncAssetCurrentData();
        });
    }

    public function syncAssetCurrentData(): void
    {
        $asset = $this->asset;

        if (!$asset) {
            return;
        }

        $latestMovementId = static::where('itasset_id', $this->itasset_id)
            ->latest('moved_at')
            ->latest('id')
            ->value('id');

        if ($latestMovementId !== $this->id) {
            return;
        }

        $asset->update([
            'location_id' => $this->to_location_id,
            'responsible_user_id' => $this->to_user_id,
        ]);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Itassests::class, 'itasset_id');
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Locations::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Locations::class, 'to_location_id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
