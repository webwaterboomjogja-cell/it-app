<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Support\Str;

class Itassests extends Model
{
    protected $fillable = [
        'code',
        'qr_token',
        'name',
        'asset_category_id',
        'location_id',
        'responsible_user_id',
        'brand',
        'model',
        'serial_number',
        'status',
        'condition',
        'purchase_date',
        'photo',
        'notes',
    ];

    protected $table = 'itassets';

    protected $casts = [
        'purchase_date' => 'date',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Itassests $asset) {
            if (blank($asset->code)) {
                $asset->code = static::generateAssetCode();
            }

            if (blank($asset->qr_token)) {
                $asset->qr_token = (string) Str::uuid();
            }
        });
    }

    public function getQrUrlAttribute(): string
    {
        return route('asset-it.scan', $this->qr_token);
    }


    public static function generateAssetCode(): string
    {
        $year = now()->format('Y');

        $lastAsset = static::where('code', 'like', "AIT-{$year}-%")
            ->latest('id')
            ->first();

        $lastNumber = 0;

        if ($lastAsset && preg_match("/AIT-{$year}-(\d+)/", $lastAsset->code, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        $nextNumber = $lastNumber + 1;

        return 'AIT-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Assetcategory::class, 'asset_category_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Locations::class, 'location_id');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Itassetmaintenance::class, 'itasset_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(Itassetmovement::class, 'itasset_id');
    }

    public function dailyReports(): HasMany
    {
        return $this->hasMany(Dailyreport::class);
    }

    public function scopeProblematic(Builder $query): Builder
    {
        return $query->whereIn('status', [
            'rusak',
            'maintenance',
        ]);
    }
}
