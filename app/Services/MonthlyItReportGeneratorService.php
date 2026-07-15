<?php

namespace App\Services;


use App\Models\Dailyreport;
use App\Models\Itassests;
use App\Models\ItSchedule;
use App\Models\Monthlyitreport;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MonthlyItReportGeneratorService
{

    private const REPORT_DATE_COLUMN = 'report_date';

    private const SCHEDULE_DATE_COLUMN = 'schedule_date';

    public function generate(
        int $month,
        int $year,
        ?int $generatedBy = null
    ): Monthlyitreport {
        $this->validatePeriod($month, $year);

        $periodStart = Carbon::create($year, $month, 1)
            ->startOfMonth()
            ->startOfDay();

        $periodEnd = $periodStart
            ->copy()
            ->endOfMonth()
            ->endOfDay();

        return DB::transaction(function () use (
            $month,
            $year,
            $generatedBy,
            $periodStart,
            $periodEnd
        ): Monthlyitreport {
            /*
            |--------------------------------------------------------------------------
            | Cari laporan bulanan yang sudah ada
            |--------------------------------------------------------------------------
            */

            $monthlyReport = Monthlyitreport::query()
                ->where('month', $month)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($monthlyReport?->isFinalized()) {
                throw ValidationException::withMessages([
                    'period' => 'Laporan bulanan sudah difinalisasi dan tidak dapat digenerate ulang.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Ambil data sumber
            |--------------------------------------------------------------------------
            */

            $dailyReports = $this->getDailyReports(
                $periodStart,
                $periodEnd
            );

            $assets = $this->getAssets();

            $schedules = $this->getSchedules(
                $periodStart,
                $periodEnd
            );

            /*
            |--------------------------------------------------------------------------
            | Buat seluruh rekap
            |--------------------------------------------------------------------------
            */

            $dailyReportSummary = $this->buildDailyReportSummary(
                $dailyReports,
                $periodStart,
                $periodEnd
            );

            $staffSummary = $this->buildStaffSummary($dailyReports);

            $categorySummary = $this->buildCategorySummary($dailyReports);

            $workStatusSummary = $this->buildWorkStatusSummary($dailyReports);

            $prioritySummary = $this->buildPrioritySummary($dailyReports);

            $assetSummary = $this->buildAssetSummary(
                $assets,
                $dailyReports
            );

            $scheduleSummary = $this->buildScheduleSummary($schedules);

            /*
            |--------------------------------------------------------------------------
            | Simpan snapshot laporan
            |--------------------------------------------------------------------------
            */

            $monthlyReport ??= new MonthlyItReport();

            $monthlyReport->fill([
                'month' => $month,
                'year' => $year,

                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),

                'status' => $monthlyReport->exists
                    ? $monthlyReport->status
                    : MonthlyItReport::STATUS_DRAFT,

                /*
                |--------------------------------------------------------------------------
                | Total laporan harian
                |--------------------------------------------------------------------------
                */

                'total_daily_reports' => $dailyReports->count(),

                'total_completed' => $dailyReports
                    ->filter(
                        fn($report): bool => $this->isCompleted(
                            $report->status
                        )
                    )
                    ->count(),

                'total_pending' => $dailyReports
                    ->filter(
                        fn($report): bool => $this->isPending(
                            $report->status
                        )
                    )
                    ->count(),

                'total_urgent' => $dailyReports
                    ->filter(
                        fn($report): bool => $this->isUrgent(
                            $report->priority
                        )
                    )
                    ->count(),

                /*
                |--------------------------------------------------------------------------
                | Total aset
                |--------------------------------------------------------------------------
                */

                'total_assets' => $assetSummary['total_assets'],

                'total_problem_assets' => $assetSummary['total_problem_assets'],

                'total_maintenance_assets' => $assetSummary['total_maintenance_assets'],

                /*
                |--------------------------------------------------------------------------
                | Total jadwal
                |--------------------------------------------------------------------------
                */

                'total_schedules' => $schedules->count(),

                /*
                |--------------------------------------------------------------------------
                | Snapshot JSON
                |--------------------------------------------------------------------------
                */

                'daily_report_summary' => $dailyReportSummary,
                'staff_summary' => $staffSummary,
                'category_summary' => $categorySummary,
                'work_status_summary' => $workStatusSummary,
                'priority_summary' => $prioritySummary,
                'asset_summary' => $assetSummary,
                'schedule_summary' => $scheduleSummary,

                /*
                |--------------------------------------------------------------------------
                | Informasi generator
                |--------------------------------------------------------------------------
                */

                'generated_by' => $generatedBy,
                'generated_at' => now(),
            ]);

            $monthlyReport->save();

            return $monthlyReport->refresh();
        });
    }

    /**
     * Ambil laporan harian dalam periode tertentu.
     */
    private function getDailyReports(
        Carbon $periodStart,
        Carbon $periodEnd
    ): Collection {
        return Dailyreport::query()
            ->with([
                'user:id,name',
                'Category:id,name',
                'asset:id,code,name,status,condition',
            ])
            ->whereBetween(self::REPORT_DATE_COLUMN, [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ])
            ->orderBy(self::REPORT_DATE_COLUMN)
            ->get();
    }

    /**
     * Ambil seluruh aset sebagai snapshot kondisi saat laporan dibuat.
     */
    private function getAssets(): Collection
    {
        return Itassests::query()
            ->select([
                'id',
                'code',
                'name',
                'status',
                'condition',
                'asset_category_id',
                'location_id',
                'responsible_user_id',
            ])
            ->with([
                'category:id,name',
                'location:id,name',
                'responsibleUser:id,name',
            ])
            ->orderBy('name')
            ->get();
    }

    /**
     * Ambil jadwal pada periode laporan.
     */
    private function getSchedules(
        Carbon $periodStart,
        Carbon $periodEnd
    ): Collection {
        return Itschedule::query()
            ->with([
                'user:id,name',
            ])
            ->whereBetween(self::SCHEDULE_DATE_COLUMN, [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ])
            ->orderBy(self::SCHEDULE_DATE_COLUMN)
            ->get();
    }

    /**
     * Ringkasan utama laporan harian.
     */
    private function buildDailyReportSummary(
        Collection $reports,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array {
        $totalDurationMinutes = (int) $reports->sum(
            fn($report): int => (int) ($report->duration_minutes ?? 0)
        );

        $activeDays = $reports
            ->pluck(self::REPORT_DATE_COLUMN)
            ->filter()
            ->map(
                fn($date): string => Carbon::parse($date)
                    ->toDateString()
            )
            ->unique()
            ->count();

        $reportsByDate = $reports
            ->groupBy(
                fn($report): string => Carbon::parse(
                    $report->{self::REPORT_DATE_COLUMN}
                )->toDateString()
            )
            ->map(function (Collection $items, string $date): array {
                $durationMinutes = (int) $items->sum(
                    fn($report): int => (int) (
                        $report->duration_minutes ?? 0
                    )
                );

                return [
                    'date' => $date,

                    'date_label' => Carbon::parse($date)
                        ->locale('id')
                        ->translatedFormat('d F Y'),

                    'total_reports' => $items->count(),

                    'completed' => $items
                        ->filter(
                            fn($report): bool => $this->isCompleted(
                                $report->status
                            )
                        )
                        ->count(),

                    'pending' => $items
                        ->filter(
                            fn($report): bool => $this->isPending(
                                $report->status
                            )
                        )
                        ->count(),

                    'urgent' => $items
                        ->filter(
                            fn($report): bool => $this->isUrgent(
                                $report->priority
                            )
                        )
                        ->count(),

                    'duration_minutes' => $durationMinutes,

                    'duration_hours' => round(
                        $durationMinutes / 60,
                        2
                    ),
                ];
            })
            ->values()
            ->all();

        return [
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),

            'period_label' => $periodStart
                ->locale('id')
                ->translatedFormat('F Y'),

            'total_reports' => $reports->count(),

            'active_report_days' => $activeDays,

            'average_reports_per_active_day' => $activeDays > 0
                ? round($reports->count() / $activeDays, 2)
                : 0,

            'total_duration_minutes' => $totalDurationMinutes,

            'total_duration_hours' => round(
                $totalDurationMinutes / 60,
                2
            ),

            'average_duration_minutes' => $reports->count() > 0
                ? round($totalDurationMinutes / $reports->count())
                : 0,

            'by_date' => $reportsByDate,
        ];
    }

    /**
     * Rekap pekerjaan per staff.
     */
    private function buildStaffSummary(Collection $reports): array
    {
        return $reports
            ->groupBy(
                fn($report): string => (string) (
                    $report->user_id ?? 'unknown'
                )
            )
            ->map(function (Collection $items): array {
                $firstReport = $items->first();

                $durationMinutes = (int) $items->sum(
                    fn($report): int => (int) (
                        $report->duration_minutes ?? 0
                    )
                );

                return [
                    'staff_id' => $firstReport->user_id,

                    'staff_name' => $firstReport->user?->name
                        ?? 'User tidak ditemukan',

                    'total_reports' => $items->count(),

                    'completed' => $items
                        ->filter(
                            fn($report): bool => $this->isCompleted(
                                $report->status
                            )
                        )
                        ->count(),

                    'in_progress' => $items
                        ->filter(
                            fn($report): bool => $this->isInProgress(
                                $report->status
                            )
                        )
                        ->count(),

                    'pending' => $items
                        ->filter(
                            fn($report): bool => $this->isPending(
                                $report->status
                            )
                        )
                        ->count(),

                    'urgent' => $items
                        ->filter(
                            fn($report): bool => $this->isUrgent(
                                $report->priority
                            )
                        )
                        ->count(),

                    'total_duration_minutes' => $durationMinutes,

                    'total_duration_hours' => round(
                        $durationMinutes / 60,
                        2
                    ),

                    'completion_percentage' => $items->count() > 0
                        ? round(
                            $items
                                ->filter(
                                    fn($report): bool => $this->isCompleted(
                                        $report->status
                                    )
                                )
                                ->count() / $items->count() * 100,
                            2
                        )
                        : 0,
                ];
            })
            ->sortByDesc('total_reports')
            ->values()
            ->all();
    }

    /**
     * Rekap pekerjaan per kategori.
     */
    private function buildCategorySummary(Collection $reports): array
    {
        return $reports
            ->groupBy(
                fn($report): string => (string) (
                    $report->work_category_id ?? 'uncategorized'
                )
            )
            ->map(function (Collection $items): array {
                $firstReport = $items->first();

                return [
                    'category_id' => $firstReport->work_category_id,

                    'category_name' => $firstReport
                        ->workCategory?->name
                        ?? 'Tanpa Kategori',

                    'total_reports' => $items->count(),

                    'completed' => $items
                        ->filter(
                            fn($report): bool => $this->isCompleted(
                                $report->status
                            )
                        )
                        ->count(),

                    'pending' => $items
                        ->filter(
                            fn($report): bool => $this->isPending(
                                $report->status
                            )
                        )
                        ->count(),

                    'urgent' => $items
                        ->filter(
                            fn($report): bool => $this->isUrgent(
                                $report->priority
                            )
                        )
                        ->count(),

                    'total_duration_minutes' => (int) $items->sum(
                        fn($report): int => (int) (
                            $report->duration_minutes ?? 0
                        )
                    ),
                ];
            })
            ->sortByDesc('total_reports')
            ->values()
            ->all();
    }

    /**
     * Rekap berdasarkan status pekerjaan.
     */
    private function buildWorkStatusSummary(Collection $reports): array
    {
        return $reports
            ->groupBy(
                fn($report): string => $this->normalizeValue(
                    $report->status
                )
            )
            ->map(function (
                Collection $items,
                string $status
            ) use ($reports): array {
                return [
                    'status' => $status,
                    'label' => $this->workStatusLabel($status),
                    'total' => $items->count(),

                    'percentage' => $reports->count() > 0
                        ? round(
                            $items->count() / $reports->count() * 100,
                            2
                        )
                        : 0,
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    /**
     * Rekap berdasarkan prioritas.
     */
    private function buildPrioritySummary(Collection $reports): array
    {
        return $reports
            ->groupBy(
                fn($report): string => $this->normalizeValue(
                    $report->priority ?: 'normal'
                )
            )
            ->map(function (
                Collection $items,
                string $priority
            ) use ($reports): array {
                return [
                    'priority' => $priority,
                    'label' => $this->priorityLabel($priority),
                    'total' => $items->count(),

                    'percentage' => $reports->count() > 0
                        ? round(
                            $items->count() / $reports->count() * 100,
                            2
                        )
                        : 0,
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    private function buildAssetSummary(
        Collection $assets,
        Collection $reports
    ): array {
        $statusSummary = $assets
            ->groupBy(
                fn($asset): string => $this->normalizeValue(
                    $asset->status
                )
            )
            ->map(function (
                Collection $items,
                string $status
            ): array {
                return [
                    'status' => $status,
                    'label' => $this->assetStatusLabel($status),
                    'total' => $items->count(),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->all();

        $conditionSummary = $assets
            ->groupBy(
                fn($asset): string => $this->normalizeValue(
                    $asset->condition
                )
            )
            ->map(function (
                Collection $items,
                string $condition
            ): array {
                return [
                    'condition' => $condition,
                    'label' => $this->assetConditionLabel($condition),
                    'total' => $items->count(),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->all();

        $assetReports = $reports
            ->filter(
                fn($report): bool => !empty($report->asset_id)
            )
            ->groupBy('asset_id')
            ->map(function (Collection $items): array {
                $firstReport = $items->first();

                $lastReportDate = $items
                    ->pluck(self::REPORT_DATE_COLUMN)
                    ->filter()
                    ->map(
                        fn($date): string => Carbon::parse($date)
                            ->toDateString()
                    )
                    ->max();

                return [
                    'asset_id' => $firstReport->asset_id,

                    'asset_code' => $firstReport->asset?->code ?? '-',

                    'asset_name' => $firstReport->asset?->name
                        ?? 'Aset tidak ditemukan',

                    'asset_status' => $firstReport->asset?->status,

                    'asset_condition' => $firstReport->asset?->condition,

                    'report_count' => $items->count(),

                    'urgent_count' => $items
                        ->filter(
                            fn($report): bool => $this->isUrgent(
                                $report->priority
                            )
                        )
                        ->count(),

                    'last_report_date' => $lastReportDate,
                ];
            })
            ->sortByDesc('report_count')
            ->values();

        $problemAssets = $assets->filter(function ($asset): bool {
            $status = $this->normalizeValue($asset->status);

            $condition = $this->normalizeValue($asset->condition);

            return in_array($status, [
                'rusak',
                'maintenance',
            ], true) || in_array($condition, [
                'rusak_ringan',
                'rusak_berat',
            ], true);
        });

        return [
            'total_assets' => $assets->count(),

            'total_active_assets' => $assets
                ->where('status', 'aktif')
                ->count(),

            'total_problem_assets' => $problemAssets->count(),

            'total_maintenance_assets' => $assets
                ->where('status', 'maintenance')
                ->count(),

            'total_damaged_assets' => $assets
                ->where('status', 'rusak')
                ->count(),

            'total_inactive_assets' => $assets
                ->where('status', 'nonaktif')
                ->count(),

            'total_missing_assets' => $assets
                ->where('status', 'hilang')
                ->count(),

            'status_summary' => $statusSummary,

            'condition_summary' => $conditionSummary,

            'assets_reported_this_month' => $assetReports->count(),

            'asset_report_summary' => $assetReports->all(),


            'frequently_problematic_assets' => $assetReports
                ->filter(
                    fn(array $item): bool => $item['report_count'] >= 2
                )
                ->values()
                ->all(),
        ];
    }

    /**
     * Rekap jadwal keseluruhan dan per staff.
     */
    private function buildScheduleSummary(Collection $schedules): array
    {
        $scheduleTypes = [
            'kerja',
            'maintenance',
            'cuti_dp',
            'ijin',
        ];

        $byType = collect($scheduleTypes)
            ->map(function (string $type) use ($schedules): array {
                return [
                    'type' => $type,
                    'label' => $this->scheduleTypeLabel($type),
                    'total' => $schedules
                        ->where('type', $type)
                        ->count(),
                ];
            })
            ->values()
            ->all();

        $byStaff = $schedules
            ->groupBy(
                fn($schedule): string => (string) (
                    $schedule->user_id ?? 'unknown'
                )
            )
            ->map(function (Collection $items): array {
                $firstSchedule = $items->first();

                return [
                    'staff_id' => $firstSchedule->user_id,

                    'staff_name' => $firstSchedule->user?->name
                        ?? 'User tidak ditemukan',

                    'total_schedules' => $items->count(),

                    'work_days' => $items
                        ->where('type', 'kerja')
                        ->count(),

                    'maintenance_days' => $items
                        ->where('type', 'maintenance')
                        ->count(),

                    'leave_days' => $items
                        ->where('type', 'cuti_dp')
                        ->count(),

                    'permission_days' => $items
                        ->where('type', 'ijin')
                        ->count(),

                    'total_scheduled_minutes' => $items->sum(
                        fn($schedule): int => $this
                            ->calculateScheduleMinutes($schedule)
                    ),

                    'total_scheduled_hours' => round(
                        $items->sum(
                            fn($schedule): int => $this
                                ->calculateScheduleMinutes($schedule)
                        ) / 60,
                        2
                    ),
                ];
            })
            ->sortByDesc('total_schedules')
            ->values()
            ->all();

        return [
            'total_schedules' => $schedules->count(),

            'total_staff' => $schedules
                ->pluck('user_id')
                ->filter()
                ->unique()
                ->count(),

            'by_type' => $byType,

            'by_staff' => $byStaff,
        ];
    }

    /**
     * Hitung durasi jadwal.
     */
    private function calculateScheduleMinutes(object $schedule): int
    {
        if (
            empty($schedule->start_time) ||
            empty($schedule->end_time)
        ) {
            return 0;
        }

        $startTime = Carbon::parse($schedule->start_time);

        $endTime = Carbon::parse($schedule->end_time);

        if ($endTime->lessThanOrEqualTo($startTime)) {
            return 0;
        }

        return (int) $startTime->diffInMinutes($endTime);
    }

    /**
     * Validasi bulan dan tahun.
     */
    private function validatePeriod(int $month, int $year): void
    {
        if ($month < 1 || $month > 12) {
            throw ValidationException::withMessages([
                'month' => 'Bulan harus berada antara 1 sampai 12.',
            ]);
        }

        if ($year < 2000 || $year > 2100) {
            throw ValidationException::withMessages([
                'year' => 'Tahun laporan tidak valid.',
            ]);
        }
    }

    private function isCompleted(?string $status): bool
    {
        return in_array($this->normalizeValue($status), [
            'selesai',
            'completed',
            'done',
        ], true);
    }

    private function isInProgress(?string $status): bool
    {
        return in_array($this->normalizeValue($status), [
            'proses',
            'diproses',
            'in_progress',
            'progress',
        ], true);
    }

    private function isPending(?string $status): bool
    {
        return in_array($this->normalizeValue($status), [
            'tertunda',
            'pending',
        ], true);
    }

    private function isUrgent(?string $priority): bool
    {
        return in_array($this->normalizeValue($priority), [
            'urgent',
            'darurat',
        ], true);
    }

    private function normalizeValue(?string $value): string
    {
        if (blank($value)) {
            return 'unknown';
        }

        return Str::of($value)
            ->lower()
            ->trim()
            ->replace([' ', '-'], '_')
            ->toString();
    }

    private function workStatusLabel(string $status): string
    {
        return match ($status) {
            'selesai', 'completed', 'done' => 'Selesai',

            'proses',
            'diproses',
            'in_progress',
            'progress' => 'Dalam Proses',

            'tertunda', 'pending' => 'Tertunda',

            default => Str::headline($status),
        };
    }

    private function priorityLabel(string $priority): string
    {
        return match ($priority) {
            'rendah', 'low' => 'Rendah',
            'normal', 'medium' => 'Normal',
            'tinggi', 'high' => 'Tinggi',
            'urgent', 'darurat' => 'Urgent',
            default => Str::headline($priority),
        };
    }

    private function assetStatusLabel(string $status): string
    {
        return match ($status) {
            'aktif' => 'Aktif',
            'rusak' => 'Rusak',
            'maintenance' => 'Maintenance',
            'nonaktif' => 'Nonaktif',
            'hilang' => 'Hilang',
            default => Str::headline($status),
        };
    }

    private function assetConditionLabel(string $condition): string
    {
        return match ($condition) {
            'baik' => 'Baik',
            'cukup' => 'Cukup',
            'rusak_ringan' => 'Rusak Ringan',
            'rusak_berat' => 'Rusak Berat',
            default => Str::headline($condition),
        };
    }

    private function scheduleTypeLabel(string $type): string
    {
        return match ($type) {
            'kerja' => 'Kerja',
            'maintenance' => 'Maintenance',
            'cuti_dp' => 'Cuti / DP',
            'ijin' => 'Ijin',
            default => Str::headline($type),
        };
    }
}
