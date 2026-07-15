<?php

namespace App\Services\Exports;

use App\Models\Dailyreport;
use App\Models\Itassests;
use App\Models\User;
use App\Models\Workcategory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DailyReportExportService
{
    /**
     * Query utama yang digunakan oleh Excel dan PDF.
     */
    public function query(array $filters = []): Builder
    {
        $startDate = filled($filters['start_date'] ?? null)
            ? Carbon::parse($filters['start_date'])->toDateString()
            : now()->startOfMonth()->toDateString();

        $endDate = filled($filters['end_date'] ?? null)
            ? Carbon::parse($filters['end_date'])->toDateString()
            : now()->toDateString();

        return Dailyreport::query()
            ->with([
                'user:id,name',
                'category:id,name',
                'asset:id,code,name',
                'reviewer:id,name',
            ])
            ->whereBetween('report_date', [
                $startDate,
                $endDate,
            ])
            ->when(
                filled($filters['user_id'] ?? null),
                fn(Builder $query): Builder => $query->where(
                    'user_id',
                    $filters['user_id']
                )
            )
            ->when(
                filled($filters['work_category_id'] ?? null),
                fn(Builder $query): Builder => $query->where(
                    'work_category_id',
                    $filters['work_category_id']
                )
            )
            ->when(
                filled($filters['work_status'] ?? null),
                fn(Builder $query): Builder => $query->where(
                    'work_status',
                    $filters['work_status']
                )
            )
            ->when(
                filled($filters['review_status'] ?? null),
                fn(Builder $query): Builder => $query->where(
                    'review_status',
                    $filters['review_status']
                )
            )
            ->when(
                filled($filters['priority'] ?? null),
                fn(Builder $query): Builder => $query->where(
                    'priority',
                    $filters['priority']
                )
            )
            ->when(
                filled($filters['location'] ?? null),
                fn(Builder $query): Builder => $query->where(
                    'location',
                    $filters['location']
                )
            )
            ->when(
                filled($filters['duration_min'] ?? null),
                fn(Builder $query): Builder => $query->where(
                    'duration_minutes',
                    '>=',
                    (int) $filters['duration_min']
                )
            )
            ->when(
                filled($filters['duration_max'] ?? null),
                fn(Builder $query): Builder => $query->where(
                    'duration_minutes',
                    '<=',
                    (int) $filters['duration_max']
                )
            )
            ->when(
                filled($filters['asset_id'] ?? null),
                fn(Builder $query): Builder => $query->where(
                    'asset_id',
                    $filters['asset_id']
                )
            )
            ->orderBy('report_date')
            ->orderBy('user_id')
            ->orderBy('start_time');
    }

    public function get(array $filters = []): Collection
    {
        return $this->query($filters)->get();
    }

    /**
     * Informasi filter untuk header laporan.
     */
    public function filterSummary(array $filters = []): array
    {
        $startDate = filled($filters['start_date'] ?? null)
            ? Carbon::parse($filters['start_date'])
            : now()->startOfMonth();

        $endDate = filled($filters['end_date'] ?? null)
            ? Carbon::parse($filters['end_date'])
            : now();

        $staff = 'Semua staff';
        $category = 'Semua kategori';
        $asset = 'Semua aset';

        if (filled($filters['user_id'] ?? null)) {
            $staff = User::query()
                ->find($filters['user_id'])
                ?->name ?? 'Staff tidak ditemukan';
        }

        if (filled($filters['work_category_id'] ?? null)) {
            $category = Workcategory::query()
                ->find($filters['work_category_id'])
                ?->name ?? 'Kategori tidak ditemukan';
        }

        if (filled($filters['asset_id'] ?? null)) {
            $assetModel = Itassests::query()
                ->find($filters['asset_id']);

            $asset = $assetModel
                ? collect([
                    $assetModel->code,
                    $assetModel->name,
                ])->filter()->implode(' — ')
                : 'Aset tidak ditemukan';
        }

        return [
            'period' => sprintf(
                '%s sampai %s',
                $startDate->format('d/m/Y'),
                $endDate->format('d/m/Y')
            ),

            'staff' => $staff,

            'category' => $category,

            'work_status' => filled(
                $filters['work_status'] ?? null
            )
                ? Str::headline($filters['work_status'])
                : 'Semua status',

            'review_status' => filled(
                $filters['review_status'] ?? null
            )
                ? Str::headline($filters['review_status'])
                : 'Semua status review',

            'priority' => filled(
                $filters['priority'] ?? null
            )
                ? Str::headline($filters['priority'])
                : 'Semua prioritas',

            'location' => filled(
                $filters['location'] ?? null
            )
                ? $filters['location']
                : 'Semua lokasi',

            'duration' => $this->durationFilterLabel(
                $filters
            ),

            'asset' => $asset,
        ];
    }

    /**
     * Statistik dari collection agar tidak menjalankan query berulang.
     */
    public function statistics(Collection $reports): array
    {
        $totalDuration = (int) $reports->sum(
            fn(Dailyreport $report): int =>
            (int) ($report->duration_minutes ?? 0)
        );

        return [
            'total' => $reports->count(),

            'total_duration_minutes' => $totalDuration,

            'total_duration_label' => $this->formatDuration(
                $totalDuration
            ),

            'completed' => $reports
                ->where('work_status', 'selesai')
                ->count(),

            'in_progress' => $reports
                ->where('work_status', 'proses')
                ->count(),

            'pending' => $reports
                ->where('work_status', 'tertunda')
                ->count(),

            'urgent' => $reports
                ->where('priority', 'urgent')
                ->count(),

            'reviewed' => $reports
                ->where('review_status', 'direview')
                ->count(),
        ];
    }

    public function formatDuration(
        int|string|null $minutes
    ): string {
        if (! is_numeric($minutes)) {
            return '-';
        }

        $minutes = max(0, (int) $minutes);

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours <= 0) {
            return "{$remainingMinutes} menit";
        }

        if ($remainingMinutes <= 0) {
            return "{$hours} jam";
        }

        return "{$hours} jam {$remainingMinutes} menit";
    }

    private function durationFilterLabel(
        array $filters
    ): string {
        $minimum = $filters['duration_min'] ?? null;
        $maximum = $filters['duration_max'] ?? null;

        if (filled($minimum) && filled($maximum)) {
            return sprintf(
                '%s sampai %s menit',
                number_format((int) $minimum, 0, ',', '.'),
                number_format((int) $maximum, 0, ',', '.')
            );
        }

        if (filled($minimum)) {
            return sprintf(
                'Minimal %s menit',
                number_format((int) $minimum, 0, ',', '.')
            );
        }

        if (filled($maximum)) {
            return sprintf(
                'Maksimal %s menit',
                number_format((int) $maximum, 0, ',', '.')
            );
        }

        return 'Semua durasi';
    }
}
