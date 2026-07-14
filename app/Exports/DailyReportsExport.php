<?php

namespace App\Exports;

use App\Models\DailyReport;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class DailyReportsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    public function __construct(
        protected array $filters = []
    ) {}

    public function query(): Builder
    {
        return DailyReport::query()
            ->with(['user', 'category', 'asset', 'reviewer'])
            ->when($this->filters['start_date'] ?? null, function (Builder $query, string $date) {
                $query->whereDate('report_date', '>=', $date);
            })
            ->when($this->filters['end_date'] ?? null, function (Builder $query, string $date) {
                $query->whereDate('report_date', '<=', $date);
            })
            ->when($this->filters['user_id'] ?? null, function (Builder $query, string $userId) {
                $query->where('user_id', $userId);
            })
            ->when($this->filters['work_category_id'] ?? null, function (Builder $query, string $categoryId) {
                $query->where('work_category_id', $categoryId);
            })
            ->when($this->filters['asset_id'] ?? null, function (Builder $query, string $assetId) {
                $query->where('asset_id', $assetId);
            })
            ->when($this->filters['work_status'] ?? null, function (Builder $query, string $status) {
                $query->where('work_status', $status);
            })
            ->when($this->filters['review_status'] ?? null, function (Builder $query, string $status) {
                $query->where('review_status', $status);
            })
            ->when($this->filters['priority'] ?? null, function (Builder $query, string $priority) {
                $query->where('priority', $priority);
            })
            ->orderByDesc('report_date')
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Staff IT',
            'Kategori',
            'Aset Terkait',
            'Kode Aset',
            'Prioritas',
            'Judul Pekerjaan',
            'Lokasi',
            'Jam Mulai',
            'Jam Selesai',
            'Durasi',
            'Status Pekerjaan',
            'Status Review',
            'Deskripsi',
            'Kendala',
            'Solusi',
            'Direview Oleh',
            'Tanggal Review',
            'Catatan Review',
            'Dibuat Pada',
        ];
    }

    public function map($report): array
    {
        return [
            $report->report_date?->format('d/m/Y'),
            $report->user?->name ?? '-',
            $report->category?->name ?? '-',
            $report->asset?->name ?? '-',
            $this->getAssetCode($report),
            $this->formatPriority($report->priority),
            $report->title,
            $report->location ?? '-',
            $report->start_time ? substr($report->start_time, 0, 5) : '-',
            $report->end_time ? substr($report->end_time, 0, 5) : '-',
            $this->formatDuration($report->duration_minutes),
            $this->formatWorkStatus($report->work_status),
            $this->formatReviewStatus($report->review_status),
            $this->cleanText($report->description),
            $this->cleanText($report->obstacle),
            $this->cleanText($report->solution),
            $report->reviewer?->name ?? '-',
            $report->reviewed_at?->format('d/m/Y H:i') ?? '-',
            $this->cleanText($report->review_note),
            $report->created_at?->format('d/m/Y H:i'),
        ];
    }

    public function title(): string
    {
        return 'Laporan Harian IT';
    }

    protected function getAssetCode($report): string
    {
        return data_get($report->asset, 'asset_code') ?? '-';
    }

    protected function formatDuration(?int $minutes): string
    {
        if (! $minutes) {
            return '-';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0 && $remainingMinutes > 0) {
            return "{$hours} jam {$remainingMinutes} menit";
        }

        if ($hours > 0) {
            return "{$hours} jam";
        }

        return "{$remainingMinutes} menit";
    }

    protected function formatPriority(?string $priority): string
    {
        return match ($priority) {
            'rendah' => 'Rendah',
            'normal' => 'Normal',
            'tinggi' => 'Tinggi',
            'urgent' => 'Urgent',
            default => '-',
        };
    }

    protected function formatWorkStatus(?string $status): string
    {
        return match ($status) {
            'selesai' => 'Selesai',
            'proses' => 'Proses',
            'tertunda' => 'Tertunda',
            default => '-',
        };
    }

    protected function formatReviewStatus(?string $status): string
    {
        return match ($status) {
            'draft' => 'Draft',
            'dikirim' => 'Dikirim',
            'direview' => 'Direview',
            default => '-',
        };
    }

    protected function cleanText(?string $text): string
    {
        if (! $text) {
            return '-';
        }

        return trim(strip_tags($text));
    }
}
