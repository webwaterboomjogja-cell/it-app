<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Itschedule extends Model
{
    protected $fillable = [
        'user_id',
        'schedule_date',
        'start_time',
        'end_time',
        'type',
        'location',
        'status',
        'notes',
    ];

    protected $table = 'itschedules';

    protected $casts = [
        'schedule_date' => 'date',
    ];

    public const TYPE_WORK = 'kerja';
    public const TYPE_MAINTENANCE = 'maintenance';
    public const TYPE_LEAVE_DP = 'cuti_dp';
    public const TYPE_PERMISSION = 'ijin';

    public const STATUS_PLANNED = 'planned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public static function typeOptions(): array
    {
        return [
            self::TYPE_WORK => 'Kerja',
            self::TYPE_MAINTENANCE => 'Maintenance',
            self::TYPE_LEAVE_DP => 'Cuti / DP',
            self::TYPE_PERMISSION => 'Ijin',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PLANNED => 'Direncanakan',
            self::STATUS_IN_PROGRESS => 'Sedang Berjalan',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
        ];
    }

    public function getTypeLabel(): string
    {
        return self::typeOptions()[$this->type] ?? $this->type;
    }

    public function getStatusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }

    public static function absenceTypes(): array
    {
        return [
            self::TYPE_LEAVE_DP,
            self::TYPE_PERMISSION,
        ];
    }

    public static function requiresTimeAndLocation(?string $type): bool
    {
        return ! in_array($type, self::absenceTypes(), true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
