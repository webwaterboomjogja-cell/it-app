<?php

namespace App\Exports;

use App\Models\Dailyreport;
use App\Services\Exports\DailyReportExportService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Throwable;

class DailyReportsExport implements
    FromCollection,
    WithMapping,
    WithHeadings,
    WithCustomStartCell,
    WithColumnWidths,
    WithEvents,
    WithTitle,
    WithProperties,
    WithStrictNullComparison
{
    private int $number = 0;

    private int $totalReports = 0;

    private int $totalDuration = 0;

    public function __construct(
        private readonly array $filters,
        private readonly string $generatedBy,
        private readonly string $generatedAt,
        private readonly ?string $documentNumber = null,
        private readonly string $documentStatus = 'draft',
    ) {
    }

    public function collection(): Collection
    {
        $reports = app(
            DailyReportExportService::class
        )->get($this->filters);

        $this->totalReports =
            $reports->count();

        $this->totalDuration =
            (int) $reports->sum(
                'duration_minutes'
            );

        return $reports;
    }

    public function map($report): array
    {
        /** @var Dailyreport $report */

        return [
            ++$this->number,

            $this->formatDate(
                $report->report_date
            ),

            $report->user?->name ?: '-',

            $report->category?->name
                ?: '-',

            $this->plainText(
                $report->title
            ),

            $this->plainText(
                $report->description
            ),

            $this->formatText(
                $report->priority
            ),

            $report->location ?: '-',

            $this->formatTime(
                $report->start_time
            ),

            $this->formatTime(
                $report->end_time
            ),

            $this->formatDuration(
                $report->duration_minutes
            ),

            $this->formatText(
                $report->work_status
            ),

            $this->formatText(
                $report->review_status
            ),

            $this->assetLabel($report),

            $this->plainText(
                $report->obstacle
            ),

            $this->plainText(
                $report->solution
            ),

            $report->reviewer?->name
                ?: '-',

            $this->plainText(
                $report->review_note
            ),
        ];
    }

    public function headings(): array
    {
        return [
            'No.',
            'Tanggal',
            'Staff IT',
            'Kategori Pekerjaan',
            'Judul Pekerjaan',
            'Deskripsi',
            'Prioritas',
            'Lokasi',
            'Jam Mulai',
            'Jam Selesai',
            'Durasi',
            'Status Pekerjaan',
            'Status Review',
            'Aset Terkait',
            'Kendala',
            'Solusi',
            'Reviewer',
            'Catatan Review',
        ];
    }

    public function startCell(): string
    {
        return 'A7';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 7,
            'B' => 14,
            'C' => 22,
            'D' => 22,
            'E' => 32,
            'F' => 38,
            'G' => 14,
            'H' => 22,
            'I' => 13,
            'J' => 13,
            'K' => 18,
            'L' => 18,
            'M' => 17,
            'N' => 25,
            'O' => 35,
            'P' => 35,
            'Q' => 22,
            'R' => 35,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class =>
                function (
                    AfterSheet $event
                ): void {
                    $sheet = $event->sheet
                        ->getDelegate();

                    $service = app(
                        DailyReportExportService::class
                    );

                    $summary =
                        $service->filterSummary(
                            $this->filters
                        );

                    $companyName = config(
                        'company.name',
                        config('app.name')
                    );

                    $companyDescription =
                        collect([
                            config(
                                'company.division'
                            ),
                            config(
                                'company.address'
                            ),
                        ])
                            ->filter()
                            ->implode(' | ');

                    $filterLine = collect([
                        "Staff: {$summary['staff']}",

                        "Kategori: {$summary['category']}",

                        "Status: {$summary['work_status']}",

                        "Review: {$summary['review_status']}",

                        "Prioritas: {$summary['priority']}",

                        "Lokasi: {$summary['location']}",

                        "Durasi: {$summary['duration']}",

                        "Aset: {$summary['asset']}",
                    ])->implode(' | ');

                    $durationLabel =
                        $service->formatDuration(
                            $this->totalDuration
                        );

                    foreach (
                        range(1, 6)
                        as $row
                    ) {
                        $sheet->mergeCells(
                            "A{$row}:R{$row}"
                        );
                    }

                    $sheet->setCellValue(
                        'A1',
                        strtoupper(
                            $companyName
                        )
                    );

                    $sheet->setCellValue(
                        'A2',
                        $companyDescription
                    );

                    $sheet->setCellValue(
                        'A3',
                        'LAPORAN HARIAN DIVISI IT'
                    );

                    $sheet->setCellValue(
                        'A4',
                        sprintf(
                            'Nomor Dokumen: %s | Status: %s | Periode: %s',
                            $this
                                ->documentNumber
                                ?? '-',
                            strtoupper(
                                $this
                                    ->documentStatus
                            ),
                            $summary['period']
                        )
                    );

                    $sheet->setCellValue(
                        'A5',
                        $filterLine
                    );

                    $sheet->setCellValue(
                        'A6',
                        sprintf(
                            'Total laporan: %s | Total durasi: %s | Dibuat: %s | Oleh: %s',
                            number_format(
                                $this
                                    ->totalReports,
                                0,
                                ',',
                                '.'
                            ),
                            $durationLabel,
                            $this->generatedAt,
                            $this->generatedBy
                        )
                    );

                    $sheet
                        ->getStyle('A1:R6')
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::
                                HORIZONTAL_CENTER
                        )
                        ->setVertical(
                            Alignment::
                                VERTICAL_CENTER
                        )
                        ->setWrapText(true);

                    $sheet
                        ->getStyle('A1:R1')
                        ->getFont()
                        ->setBold(true)
                        ->setSize(16);

                    $sheet
                        ->getStyle('A3:R3')
                        ->getFont()
                        ->setBold(true)
                        ->setSize(13);

                    $sheet
                        ->getStyle('A7:R7')
                        ->applyFromArray([
                            'font' => [
                                'bold' => true,

                                'color' => [
                                    'argb' =>
                                        'FFFFFFFF',
                                ],
                            ],

                            'fill' => [
                                'fillType' =>
                                    Fill::FILL_SOLID,

                                'startColor' => [
                                    'argb' =>
                                        'FF1F4E78',
                                ],
                            ],

                            'alignment' => [
                                'horizontal' =>
                                    Alignment::
                                        HORIZONTAL_CENTER,

                                'vertical' =>
                                    Alignment::
                                        VERTICAL_CENTER,

                                'wrapText' =>
                                    true,
                            ],

                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' =>
                                        Border::
                                            BORDER_THIN,
                                ],
                            ],
                        ]);

                    $lastRow = $sheet
                        ->getHighestRow();

                    if ($lastRow >= 8) {
                        $sheet
                            ->getStyle(
                                "A8:R{$lastRow}"
                            )
                            ->applyFromArray([
                                'alignment' => [
                                    'vertical' =>
                                        Alignment::
                                            VERTICAL_TOP,

                                    'wrapText' =>
                                        true,
                                ],

                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' =>
                                            Border::
                                                BORDER_THIN,

                                        'color' => [
                                            'argb' =>
                                                'FFD9D9D9',
                                        ],
                                    ],
                                ],
                            ]);

                        $sheet->setAutoFilter(
                            "A7:R{$lastRow}"
                        );
                    }

                    $sheet->freezePane('A8');

                    $sheet->getPageSetup()
                        ->setOrientation(
                            PageSetup::
                                ORIENTATION_LANDSCAPE
                        )
                        ->setPaperSize(
                            PageSetup::
                                PAPERSIZE_A3
                        )
                        ->setFitToWidth(1)
                        ->setFitToHeight(0);

                    $sheet->getHeaderFooter()
                        ->setOddFooter(
                            '&L' .
                            $companyName .
                            '&RHalaman &P dari &N'
                        );
                },
        ];
    }

    public function title(): string
    {
        return 'Laporan Harian IT';
    }

    public function properties(): array
    {
        return [
            'creator' =>
                $this->generatedBy,

            'lastModifiedBy' =>
                $this->generatedBy,

            'title' =>
                'Laporan Harian Divisi IT',

            'subject' =>
                'Laporan Harian IT',

            'category' =>
                'Laporan IT',

            'company' =>
                config('company.name'),
        ];
    }

    private function assetLabel(
        Dailyreport $report
    ): string {
        if (! $report->asset) {
            return '-';
        }

        return collect([
            $report->asset->code,
            $report->asset->name,
        ])
            ->filter()
            ->implode(' — ');
    }

    private function formatText(
        mixed $value
    ): string {
        return filled($value)
            ? Str::headline(
                (string) $value
            )
            : '-';
    }

    private function formatDate(
        mixed $value
    ): string {
        if (blank($value)) {
            return '-';
        }

        try {
            return Carbon::parse($value)
                ->format('d/m/Y');
        } catch (Throwable) {
            return (string) $value;
        }
    }

    private function formatTime(
        mixed $value
    ): string {
        if (blank($value)) {
            return '-';
        }

        $time = trim(
            explode(
                ',',
                (string) $value
            )[0]
        );

        foreach (
            ['H:i:s', 'H:i']
            as $format
        ) {
            try {
                return Carbon::createFromFormat(
                    $format,
                    $time
                )->format('H:i');
            } catch (Throwable) {
                //
            }
        }

        return $time;
    }

    private function formatDuration(
        mixed $minutes
    ): string {
        return app(
            DailyReportExportService::class
        )->formatDuration(
            is_numeric($minutes)
                ? (int) $minutes
                : null
        );
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