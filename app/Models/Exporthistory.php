<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Exporthistory extends Model
{
    protected $fillable = [
        'user_id',
        'monthly_it_report_id',
        'document_number',
        'report_type',
        'format',
        'generation_status',
        'document_status',
        'filters',
        'signatories',
        'disk',
        'file_path',
        'original_filename',
        'file_size',
        'checksum',
        'error_message',
        'finalized_by',
        'finalized_at',
        'download_count',
        'last_downloaded_at',
        'generated_at',
    ];

     protected $table = 'export_histories';

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'signatories' => 'array',

            'file_size' => 'integer',
            'download_count' => 'integer',

            'finalized_at' => 'datetime',
            'last_downloaded_at' => 'datetime',
            'generated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'finalized_by'
        );
    }

    public function monthlyItReport(): BelongsTo
    {
        return $this->belongsTo(
            Monthlyitreport::class
        );
    }

    public function fileExists(): bool
    {
        if (blank($this->file_path)) {
            return false;
        }

        return Storage::disk(
            $this->disk
        )->exists(
            $this->file_path
        );
    }

    public function isCompleted(): bool
    {
        return $this->generation_status ===
            'completed';
    }

    public function isFinal(): bool
    {
        return $this->document_status ===
            'final';
    }

    public function reportTypeLabel(): string
    {
        return match ($this->report_type) {
            'assets' => 'Inventaris Aset',

            'daily_reports' =>
                'Laporan Harian IT',

            'monthly_reports' =>
                'Laporan Bulanan IT',

            default => 'Laporan',
        };
    }

    public function formatLabel(): string
    {
        return strtoupper(
            $this->format
        );
    }

    public function formattedFileSize(): string
    {
        $bytes = (int) $this->file_size;

        if ($bytes <= 0) {
            return '-';
        }

        if ($bytes >= 1_048_576) {
            return number_format(
                $bytes / 1_048_576,
                2,
                ',',
                '.'
            ) . ' MB';
        }

        return number_format(
            $bytes / 1024,
            2,
            ',',
            '.'
        ) . ' KB';
    }
}