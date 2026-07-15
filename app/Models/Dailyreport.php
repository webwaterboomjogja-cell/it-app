<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Dailyreport extends Model
{
    protected $fillable = [
        'user_id',
        'itassets_id',
        'work_category_id',
        'priority',
        'report_date',
        'title',
        'location',
        'start_time',
        'end_time',
        'duration_minutes',
        'description',
        'obstacle',
        'solution',
        'work_status',
        'review_status',
        'attachments',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'attachments' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Dailyreport $report) {
            if ($report->start_time && $report->end_time) {
                $reportDate = $report->report_date
                    ? $report->report_date->format('Y-m-d')
                    : now()->format('Y-m-d');

                $startTime = Carbon::parse($reportDate . ' ' . $report->start_time);
                $endTime = Carbon::parse($reportDate . ' ' . $report->end_time);

                if ($endTime->lessThanOrEqualTo($startTime)) {
                    $report->duration_minutes = null;

                    return;
                }

                $report->duration_minutes = $startTime->diffInMinutes($endTime);
            } else {
                $report->duration_minutes = null;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Workcategory::class, 'work_category_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Itassests::class, 'itassets_id');
    }
}
