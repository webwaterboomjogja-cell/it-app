<?php

namespace App\Exports;

use App\Exports\Sheets\MonthlyTableSheet;
use App\Models\Monthlyitreport;
use App\Services\Exports\MonthlyReportExportService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithProperties;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class MonthlyItReportExport implements
    WithMultipleSheets,
    WithProperties
{
    private array $data;

    public function __construct(
        protected Monthlyitreport $report,
        protected string $generatedBy,
        protected string $generatedAt,
        protected ?string $documentNumber = null,
        protected ?string $documentStatus = null,
    ) {
        $this->data = app(
            MonthlyReportExportService::class
        )->build($this->report);
    }

    public function sheets(): array
    {
        return [
            $this->summarySheet(),
            $this->staffSheet(),
            $this->categorySheet(),
            $this->statusPrioritySheet(),
            $this->assetSheet(),
            $this->scheduleSheet(),
        ];
    }

    private function summarySheet(): MonthlyTableSheet
    {
        $overview = $this->data['overview'];

        $rows = [
            ['Periode', $this->data['period']],
            ['Status Laporan', $this->data['status']],

            [
                'Pembuat Laporan',
                $this->data['generated_by'],
            ],

            [
                'Penyetuju',
                $this->data['approved_by'],
            ],

            [
                'Tanggal Persetujuan',
                $this->data['approved_at'],
            ],

            [
                'Total Laporan Harian',
                $overview['total_reports'],
            ],

            [
                'Pekerjaan Selesai',
                $overview['completed'],
            ],

            [
                'Pekerjaan Proses',
                $overview['in_progress'],
            ],

            [
                'Pekerjaan Tertunda',
                $overview['pending'],
            ],

            [
                'Pekerjaan Urgent',
                $overview['urgent'],
            ],

            [
                'Laporan Direview',
                $overview['reviewed'],
            ],

            [
                'Persentase Penyelesaian',
                $overview['completion_percentage'] . '%',
            ],

            [
                'Total Durasi Pekerjaan',
                $overview['duration_label'],
            ],

            [
                'Total Aset',
                $overview['total_assets'],
            ],

            [
                'Aset Aktif',
                $overview['active_assets'],
            ],

            [
                'Aset Rusak',
                $overview['damaged_assets'],
            ],

            [
                'Aset Maintenance',
                $overview['maintenance_assets'],
            ],

            [
                'Total Jadwal',
                $overview['total_schedules'],
            ],

            [
                'Jadwal Kerja',
                $overview['work_schedules'],
            ],

            [
                'Jadwal Maintenance',
                $overview['maintenance_schedules'],
            ],

            [
                'Cuti / DP',
                $overview['leave_schedules'],
            ],

            [
                'Ijin',
                $overview['permission_schedules'],
            ],

            [
                'Evaluasi Bulanan',
                $this->data['evaluation'],
            ],

            [
                'Rekomendasi Bulan Berikutnya',
                $this->data['recommendations'],
            ],
        ];

        return new MonthlyTableSheet(
            sheetTitle: 'Ringkasan',
            reportTitle: 'Ringkasan Laporan Bulanan IT',
            period: $this->data['period'],
            generatedBy: $this->generatedBy,
            generatedAt: $this->generatedAt,
            headings: [
                'Informasi',
                'Nilai',
            ],
            rows: $rows,
            widths: [
                'A' => 34,
                'B' => 90,
            ],
            orientation: PageSetup::ORIENTATION_PORTRAIT
        );
    }

    private function staffSheet(): MonthlyTableSheet
    {
        $rows = collect(
            $this->data['staff']
        )->map(
            fn(array $row): array => [
                $row['number'],
                $row['name'],
                $row['total'],
                $row['completed'],
                $row['in_progress'],
                $row['pending'],
                $row['urgent'],
                $row['duration_label'],
                $row['completion_percentage']
                    . '%',
            ]
        )->all();

        return new MonthlyTableSheet(
            sheetTitle: 'Rekap Staff',
            reportTitle: 'Rekap Laporan Per Staff',
            period: $this->data['period'],
            generatedBy: $this->generatedBy,
            generatedAt: $this->generatedAt,
            headings: [
                'No.',
                'Staff IT',
                'Total',
                'Selesai',
                'Proses',
                'Tertunda',
                'Urgent',
                'Total Durasi',
                'Penyelesaian',
            ],
            rows: $rows,
            widths: [
                'A' => 7,
                'B' => 25,
                'C' => 12,
                'D' => 12,
                'E' => 12,
                'F' => 12,
                'G' => 12,
                'H' => 20,
                'I' => 18,
            ]
        );
    }

    private function categorySheet(): MonthlyTableSheet
    {
        $rows = collect(
            $this->data['categories']
        )->map(
            fn(array $row): array => [
                $row['number'],
                $row['name'],
                $row['total'],
                $row['completed'],
                $row['in_progress'],
                $row['pending'],
                $row['duration_label'],
            ]
        )->all();

        return new MonthlyTableSheet(
            sheetTitle: 'Rekap Kategori',
            reportTitle: 'Rekap Laporan Per Kategori',
            period: $this->data['period'],
            generatedBy: $this->generatedBy,
            generatedAt: $this->generatedAt,
            headings: [
                'No.',
                'Kategori Pekerjaan',
                'Total',
                'Selesai',
                'Proses',
                'Tertunda',
                'Total Durasi',
            ],
            rows: $rows,
            widths: [
                'A' => 7,
                'B' => 30,
                'C' => 14,
                'D' => 14,
                'E' => 14,
                'F' => 14,
                'G' => 20,
            ]
        );
    }

    private function statusPrioritySheet(): MonthlyTableSheet
    {
        $rows = collect([
            ...$this->data['status_rows'],
            ...$this->data['priority_rows'],
        ])->map(
            fn(array $row): array => [
                $row['type'],
                $row['name'],
                $row['total'],
                $row['percentage'] . '%',
            ]
        )->all();

        return new MonthlyTableSheet(
            sheetTitle: 'Status & Prioritas',
            reportTitle: 'Rekap Status dan Prioritas',
            period: $this->data['period'],
            generatedBy: $this->generatedBy,
            generatedAt: $this->generatedAt,
            headings: [
                'Jenis Rekap',
                'Nama',
                'Jumlah',
                'Persentase',
            ],
            rows: $rows,
            widths: [
                'A' => 25,
                'B' => 25,
                'C' => 16,
                'D' => 18,
            ]
        );
    }

    private function assetSheet(): MonthlyTableSheet
    {
        $rows = collect(
            $this->data['problematic_assets']
        )->map(
            fn(array $row): array => [
                $row['number'],
                $row['code'],
                $row['name'],
                $row['status'],
                $row['problem_count'],
                $row['last_problem'],
            ]
        )->all();

        return new MonthlyTableSheet(
            sheetTitle: 'Aset Bermasalah',
            reportTitle: 'Rekap Aset Bermasalah',
            period: $this->data['period'],
            generatedBy: $this->generatedBy,
            generatedAt: $this->generatedAt,
            headings: [
                'No.',
                'Kode Aset',
                'Nama Aset',
                'Status / Kondisi',
                'Jumlah Masalah',
                'Kendala Terakhir',
            ],
            rows: $rows,
            widths: [
                'A' => 7,
                'B' => 20,
                'C' => 30,
                'D' => 20,
                'E' => 18,
                'F' => 55,
            ]
        );
    }

    private function scheduleSheet(): MonthlyTableSheet
    {
        $rows = collect(
            $this->data['schedule_staff']
        )->map(
            fn(array $row): array => [
                $row['number'],
                $row['name'],
                $row['work'],
                $row['maintenance'],
                $row['leave'],
                $row['permission'],
                $row['total'],
            ]
        )->all();

        return new MonthlyTableSheet(
            sheetTitle: 'Rekap Jadwal',
            reportTitle: 'Rekap Jadwal Per Staff',
            period: $this->data['period'],
            generatedBy: $this->generatedBy,
            generatedAt: $this->generatedAt,
            headings: [
                'No.',
                'Staff IT',
                'Kerja',
                'Maintenance',
                'Cuti / DP',
                'Ijin',
                'Total',
            ],
            rows: $rows,
            widths: [
                'A' => 7,
                'B' => 28,
                'C' => 15,
                'D' => 18,
                'E' => 15,
                'F' => 15,
                'G' => 15,
            ]
        );
    }

    public function properties(): array
    {
        return [
            'creator' => $this->generatedBy,
            'lastModifiedBy' =>
            $this->generatedBy,

            'title' =>
            'Laporan Bulanan Divisi IT',

            'description' =>
            'Rekap laporan bulanan Divisi IT',

            'subject' =>
            'Laporan Bulanan IT',

            'keywords' =>
            'laporan, bulanan, IT, aset, jadwal',

            'category' => 'Laporan IT',
            'company' => config('company.name'),
        ];
    }
}
