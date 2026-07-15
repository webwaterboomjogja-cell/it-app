<?php

namespace App\Services\Exports;

use App\Models\MonthlyItReport;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MonthlyReportExportService
{
    /**
     * Mengambil laporan yang sudah pernah dibuat.
     */
    public function findReport(
        int $month,
        int $year
    ): ?Monthlyitreport {
        return Monthlyitreport::query()
            ->with([
                'generatedBy:id,name',
                'approvedBy:id,name',
            ])
            ->where('month', $month)
            ->where('year', $year)
            ->latest('id')
            ->first();
    }

    /**
     * Mengubah snapshot JSON menjadi struktur standar
     * untuk Excel dan PDF.
     */
    public function build(
        Monthlyitreport $report
    ): array {
        $dailySummary = $this->toArray(
            $report->daily_report_summary
        );

        $assetSummary = $this->toArray(
            $report->asset_summary
        );

        $scheduleSummary = $this->toArray(
            $report->schedule_summary
        );

        $staffRows = $this->normalizeStaffRows(
            $report->staff_summary
        );

        $categoryRows = $this->normalizeCategoryRows(
            $report->category_summary
        );

        $statusRows = $this->normalizeMetricRows(
            $report->work_status_summary,
            'Status Pekerjaan'
        );

        $priorityRows = $this->normalizeMetricRows(
            $report->priority_summary,
            'Prioritas'
        );

        $problematicAssets =
            $this->normalizeAssetRows(
                $assetSummary
            );

        $scheduleStaffRows =
            $this->normalizeScheduleRows(
                $scheduleSummary
            );

        $scheduleByType = $this->toArray(
            data_get(
                $scheduleSummary,
                'by_type',
                []
            )
        );

        $totalReports = (int) $this->pick(
            $dailySummary,
            [
                'total_reports',
                'total',
                'reports_total',
            ],
            $report->total_daily_reports ?? 0
        );

        $completed = (int) $this->pick(
            $dailySummary,
            [
                'completed',
                'selesai',
                'total_completed',
            ],
            0
        );

        $inProgress = (int) $this->pick(
            $dailySummary,
            [
                'in_progress',
                'proses',
                'total_in_progress',
            ],
            0
        );

        $pending = (int) $this->pick(
            $dailySummary,
            [
                'pending',
                'tertunda',
                'total_pending',
            ],
            0
        );

        $urgent = (int) $this->pick(
            $dailySummary,
            [
                'urgent',
                'total_urgent',
            ],
            0
        );

        $reviewed = (int) $this->pick(
            $dailySummary,
            [
                'reviewed',
                'direview',
                'total_reviewed',
            ],
            0
        );

        $durationMinutes = (int) $this->pick(
            $dailySummary,
            [
                'total_duration_minutes',
                'duration_minutes',
                'total_duration',
            ],
            collect($staffRows)->sum(
                'duration_minutes'
            )
        );

        $totalAssets = (int) $this->pick(
            $assetSummary,
            [
                'total_assets',
                'total',
            ],
            $report->total_assets ?? 0
        );

        $activeAssets = (int) $this->pick(
            $assetSummary,
            [
                'active_assets',
                'active',
                'aktif',
            ],
            0
        );

        $damagedAssets = (int) $this->pick(
            $assetSummary,
            [
                'damaged_assets',
                'damaged',
                'rusak',
            ],
            0
        );

        $maintenanceAssets = (int) $this->pick(
            $assetSummary,
            [
                'maintenance_assets',
                'maintenance',
            ],
            0
        );

        $workSchedules = (int) $this->pick(
            $scheduleByType,
            ['kerja', 'work'],
            0
        );

        $maintenanceSchedules = (int) $this->pick(
            $scheduleByType,
            ['maintenance'],
            0
        );

        $leaveSchedules = (int) $this->pick(
            $scheduleByType,
            [
                'cuti_dp',
                'cuti',
                'leave',
            ],
            0
        );

        $permissionSchedules = (int) $this->pick(
            $scheduleByType,
            [
                'ijin',
                'izin',
                'permission',
            ],
            0
        );

        $totalSchedules = (int) $this->pick(
            $scheduleSummary,
            [
                'total_schedules',
                'total',
            ],
            $report->total_schedules
                ?? (
                    $workSchedules +
                    $maintenanceSchedules +
                    $leaveSchedules +
                    $permissionSchedules
                )
        );

        return [
            'period' => $this->periodLabel($report),

            'month_name' => $this->monthName(
                (int) $report->month
            ),

            'month' => (int) $report->month,
            'year' => (int) $report->year,

            'status' => Str::headline(
                (string) ($report->status ?? 'draft')
            ),

            'generated_by' =>
            $report->generatedBy?->name
                ?? 'Sistem',

            'approved_by' =>
            $report->approvedBy?->name
                ?? '-',

            'approved_at' =>
            $report->approved_at
                ? Carbon::parse(
                    $report->approved_at
                )->format('d/m/Y H:i')
                : '-',

            'overview' => [
                'total_reports' => $totalReports,
                'completed' => $completed,
                'in_progress' => $inProgress,
                'pending' => $pending,
                'urgent' => $urgent,
                'reviewed' => $reviewed,

                'duration_minutes' =>
                $durationMinutes,

                'duration_label' =>
                $this->formatDuration(
                    $durationMinutes
                ),

                'completion_percentage' =>
                $totalReports > 0
                    ? round(
                        ($completed / $totalReports)
                            * 100,
                        2
                    )
                    : 0,

                'total_assets' => $totalAssets,
                'active_assets' => $activeAssets,
                'damaged_assets' => $damagedAssets,
                'maintenance_assets' =>
                $maintenanceAssets,

                'total_schedules' =>
                $totalSchedules,

                'work_schedules' =>
                $workSchedules,

                'maintenance_schedules' =>
                $maintenanceSchedules,

                'leave_schedules' =>
                $leaveSchedules,

                'permission_schedules' =>
                $permissionSchedules,
            ],

            'staff' => $staffRows,
            'categories' => $categoryRows,

            'status_rows' => $statusRows,
            'priority_rows' => $priorityRows,

            'problematic_assets' =>
            $problematicAssets,

            'schedule_staff' =>
            $scheduleStaffRows,

            'evaluation' => $this->plainText(
                $report->evaluation
                    ?? $report->evaluasi
                    ?? null
            ),

            'recommendations' => $this->plainText(
                $report->recommendations
                    ?? $report->recommendation
                    ?? $report->rekomendasi
                    ?? null
            ),
        ];
    }

    private function normalizeStaffRows(
        mixed $summary
    ): array {
        return collect(
            $this->rows($summary, [
                'items',
                'rows',
                'staff',
                'data',
            ])
        )
            ->values()
            ->map(function (
                array $row,
                int $index
            ): array {
                $total = (int) $this->pick(
                    $row,
                    [
                        'total_reports',
                        'total',
                    ],
                    0
                );

                $completed = (int) $this->pick(
                    $row,
                    [
                        'completed',
                        'selesai',
                    ],
                    0
                );

                $duration = (int) $this->pick(
                    $row,
                    [
                        'total_duration_minutes',
                        'duration_minutes',
                        'duration',
                    ],
                    0
                );

                return [
                    'number' => $index + 1,

                    'name' => $this->pick(
                        $row,
                        [
                            'staff_name',
                            'name',
                            'user_name',
                        ],
                        '-'
                    ),

                    'total' => $total,
                    'completed' => $completed,

                    'in_progress' => (int) $this->pick(
                        $row,
                        [
                            'in_progress',
                            'proses',
                        ],
                        0
                    ),

                    'pending' => (int) $this->pick(
                        $row,
                        [
                            'pending',
                            'tertunda',
                        ],
                        0
                    ),

                    'urgent' => (int) $this->pick(
                        $row,
                        ['urgent'],
                        0
                    ),

                    'duration_minutes' => $duration,

                    'duration_label' =>
                    $this->formatDuration(
                        $duration
                    ),

                    'completion_percentage' =>
                    (float) $this->pick(
                        $row,
                        [
                            'completion_percentage',
                            'percentage',
                        ],
                        $total > 0
                            ? round(
                                ($completed / $total)
                                    * 100,
                                2
                            )
                            : 0
                    ),
                ];
            })
            ->all();
    }

    private function normalizeCategoryRows(
        mixed $summary
    ): array {
        return collect(
            $this->rows($summary, [
                'items',
                'rows',
                'categories',
                'data',
            ])
        )
            ->values()
            ->map(function (
                array $row,
                int $index
            ): array {
                $total = (int) $this->pick(
                    $row,
                    [
                        'total_reports',
                        'total',
                    ],
                    0
                );

                $duration = (int) $this->pick(
                    $row,
                    [
                        'total_duration_minutes',
                        'duration_minutes',
                        'duration',
                    ],
                    0
                );

                return [
                    'number' => $index + 1,

                    'name' => $this->pick(
                        $row,
                        [
                            'category_name',
                            'name',
                        ],
                        '-'
                    ),

                    'total' => $total,

                    'completed' => (int) $this->pick(
                        $row,
                        [
                            'completed',
                            'selesai',
                        ],
                        0
                    ),

                    'in_progress' => (int) $this->pick(
                        $row,
                        [
                            'in_progress',
                            'proses',
                        ],
                        0
                    ),

                    'pending' => (int) $this->pick(
                        $row,
                        [
                            'pending',
                            'tertunda',
                        ],
                        0
                    ),

                    'duration_minutes' => $duration,

                    'duration_label' =>
                    $this->formatDuration(
                        $duration
                    ),
                ];
            })
            ->all();
    }

    private function normalizeMetricRows(
        mixed $summary,
        string $type
    ): array {
        $data = $this->toArray($summary);

        $nested = $this->pick(
            $data,
            [
                'items',
                'rows',
                'data',
            ]
        );

        if (is_array($nested)) {
            $data = $nested;
        }

        $rows = [];

        if (array_is_list($data)) {
            foreach ($data as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }

                $rows[] = [
                    'type' => $type,

                    'name' => Str::headline(
                        (string) $this->pick(
                            $row,
                            [
                                'name',
                                'label',
                                'status',
                                'priority',
                            ],
                            '-'
                        )
                    ),

                    'total' => (int) $this->pick(
                        $row,
                        [
                            'total',
                            'count',
                            'total_reports',
                        ],
                        0
                    ),

                    'percentage' => (float) $this->pick(
                        $row,
                        ['percentage'],
                        0
                    ),
                ];
            }

            return $rows;
        }

        $total = collect($data)
            ->filter(fn($value) => is_numeric($value))
            ->sum();

        foreach ($data as $name => $value) {
            if (! is_numeric($value)) {
                continue;
            }

            $count = (int) $value;

            $rows[] = [
                'type' => $type,
                'name' => Str::headline(
                    (string) $name
                ),
                'total' => $count,

                'percentage' => $total > 0
                    ? round(
                        ($count / $total) * 100,
                        2
                    )
                    : 0,
            ];
        }

        return $rows;
    }

    private function normalizeAssetRows(
        array $summary
    ): array {
        $rows = $this->rows(
            $summary,
            [
                'frequently_problematic_assets',
                'problematic_assets',
                'assets',
                'items',
                'rows',
            ]
        );

        return collect($rows)
            ->values()
            ->map(function (
                array $row,
                int $index
            ): array {
                return [
                    'number' => $index + 1,

                    'code' => $this->pick(
                        $row,
                        [
                            'asset_code',
                            'code',
                        ],
                        '-'
                    ),

                    'name' => $this->pick(
                        $row,
                        [
                            'asset_name',
                            'name',
                        ],
                        '-'
                    ),

                    'status' => Str::headline(
                        (string) $this->pick(
                            $row,
                            [
                                'status',
                                'condition',
                            ],
                            '-'
                        )
                    ),

                    'problem_count' => (int) $this->pick(
                        $row,
                        [
                            'problem_count',
                            'report_count',
                            'total_problems',
                            'total',
                        ],
                        0
                    ),

                    'last_problem' =>
                    $this->plainText(
                        $this->pick(
                            $row,
                            [
                                'last_problem',
                                'problem',
                                'last_issue',
                                'notes',
                            ],
                            '-'
                        )
                    ),
                ];
            })
            ->all();
    }

    private function normalizeScheduleRows(
        array $summary
    ): array {
        $rows = $this->rows(
            $summary,
            [
                'by_staff',
                'staff_summary',
                'staff',
                'items',
                'rows',
            ]
        );

        return collect($rows)
            ->values()
            ->map(function (
                array $row,
                int $index
            ): array {
                $work = (int) $this->pick(
                    $row,
                    ['kerja', 'work'],
                    0
                );

                $maintenance = (int) $this->pick(
                    $row,
                    ['maintenance'],
                    0
                );

                $leave = (int) $this->pick(
                    $row,
                    [
                        'cuti_dp',
                        'cuti',
                        'leave',
                    ],
                    0
                );

                $permission = (int) $this->pick(
                    $row,
                    [
                        'ijin',
                        'izin',
                        'permission',
                    ],
                    0
                );

                return [
                    'number' => $index + 1,

                    'name' => $this->pick(
                        $row,
                        [
                            'staff_name',
                            'name',
                            'user_name',
                        ],
                        '-'
                    ),

                    'work' => $work,

                    'maintenance' =>
                    $maintenance,

                    'leave' => $leave,

                    'permission' =>
                    $permission,

                    'total' => (int) $this->pick(
                        $row,
                        ['total'],
                        $work +
                            $maintenance +
                            $leave +
                            $permission
                    ),
                ];
            })
            ->all();
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

        if ($hours === 0) {
            return "{$remainingMinutes} menit";
        }

        if ($remainingMinutes === 0) {
            return "{$hours} jam";
        }

        return "{$hours} jam {$remainingMinutes} menit";
    }

    private function periodLabel(
        Monthlyitreport $report
    ): string {
        if (
            filled($report->period_start) &&
            filled($report->period_end)
        ) {
            return sprintf(
                '%s sampai %s',
                Carbon::parse(
                    $report->period_start
                )->format('d/m/Y'),
                Carbon::parse(
                    $report->period_end
                )->format('d/m/Y')
            );
        }

        return sprintf(
            '%s %s',
            $this->monthName(
                (int) $report->month
            ),
            $report->year
        );
    }

    private function monthName(int $month): string
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
        ][$month] ?? '-';
    }

    private function toArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof Collection) {
            return $value->toArray();
        }

        if (is_string($value)) {
            $decoded = json_decode(
                $value,
                true
            );

            return is_array($decoded)
                ? $decoded
                : [];
        }

        return [];
    }

    private function rows(
        mixed $value,
        array $paths = []
    ): array {
        $data = $this->toArray($value);

        foreach ($paths as $path) {
            $candidate = data_get(
                $data,
                $path
            );

            if (is_array($candidate)) {
                return array_values(
                    $candidate
                );
            }
        }

        if (array_is_list($data)) {
            return $data;
        }

        if (
            $data !== [] &&
            collect($data)->every(
                fn($item): bool =>
                is_array($item)
            )
        ) {
            return array_values($data);
        }

        return [];
    }

    private function pick(
        array $data,
        array $keys,
        mixed $default = null
    ): mixed {
        foreach ($keys as $key) {
            $value = data_get(
                $data,
                $key
            );

            if (
                $value !== null &&
                $value !== ''
            ) {
                return $value;
            }
        }

        return $default;
    }

    private function plainText(
        mixed $value
    ): string {
        if (blank($value)) {
            return '-';
        }

        $text = html_entity_decode(
            (string) $value,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $text = strip_tags($text);

        $text = str_replace(
            ["\u{00A0}", '&nbsp;'],
            ' ',
            $text
        );

        return trim(
            preg_replace(
                '/[ \t]+/',
                ' ',
                $text
            )
        );
    }
}
