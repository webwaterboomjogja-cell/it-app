<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Monthlyitreport extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_FINALIZED = 'finalized';

    protected $fillable = [
        'month',
        'year',
        'period_start',
        'period_end',
        'status',

        'total_daily_reports',
        'total_completed',
        'total_pending',
        'total_urgent',

        'total_assets',
        'total_problem_assets',
        'total_maintenance_assets',

        'total_schedules',

        'daily_report_summary',
        'staff_summary',
        'category_summary',
        'work_status_summary',
        'priority_summary',
        'asset_summary',
        'schedule_summary',

        'evaluation',
        'recommendation',
        'notes',

        'generated_by',
        'approved_by',
        'generated_at',
        'finalized_at',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',

        'period_start' => 'date',
        'period_end' => 'date',

        'total_daily_reports' => 'integer',
        'total_completed' => 'integer',
        'total_pending' => 'integer',
        'total_urgent' => 'integer',

        'total_assets' => 'integer',
        'total_problem_assets' => 'integer',
        'total_maintenance_assets' => 'integer',

        'total_schedules' => 'integer',

        'daily_report_summary' => 'array',
        'staff_summary' => 'array',
        'category_summary' => 'array',
        'work_status_summary' => 'array',
        'priority_summary' => 'array',
        'asset_summary' => 'array',
        'schedule_summary' => 'array',

        'generated_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];

    protected $table = 'monthlyitreports';

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'generated_by'
        );
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }


    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_FINALIZED => 'Final',
        ];
    }

    public static function monthOptions(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }

    public function getPeriodLabelAttribute(): string
    {
        return Carbon::createFromDate(
            $this->year,
            $this->month,
            1
        )
            ->locale('id')
            ->translatedFormat('F Y');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isFinalized(): bool
    {
        return $this->status === self::STATUS_FINALIZED;
    }

    public function canRegenerate(): bool
    {
        return $this->isDraft();
    }

    public function isReadyToFinalize(): bool
    {
        return $this->generated_at !== null
            && $this->hasMeaningfulText($this->evaluation)
            && $this->hasMeaningfulText($this->recommendation);
    }


    public function finalizeBy(?int $approvedBy): void
    {
        if ($this->isFinalized()) {
            throw new \LogicException(
                'Laporan bulanan sudah difinalisasi.'
            );
        }

        if (! $this->isReadyToFinalize()) {
            throw new \LogicException(
                'Evaluasi dan rekomendasi harus diisi sebelum finalisasi.'
            );
        }

        $this->forceFill([
            'status' => self::STATUS_FINALIZED,
            'approved_by' => $approvedBy,
            'finalized_at' => now(),
        ])->save();
    }

    private function hasMeaningfulText(?string $value): bool
    {
        $plainText = trim(
            html_entity_decode(
                strip_tags($value ?? '')
            )
        );

        return $plainText !== '';
    }
}
