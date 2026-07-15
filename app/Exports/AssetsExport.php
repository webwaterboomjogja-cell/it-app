<?php

namespace App\Exports;

use App\Services\Exports\AssetExportService;
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

class AssetsExport implements
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

    public function __construct(
        private readonly array $filters,
        private readonly string $generatedBy,
        private readonly string $generatedAt,
        private readonly ?string $documentNumber = null,
        private readonly string $documentStatus = 'draft',
    ) {}

    public function collection(): Collection
    {
        return app(
            AssetExportService::class
        )->get($this->filters);
    }

    public function map($asset): array
    {
        $brandModel = collect([
            $asset->brand,
            $asset->model,
        ])
            ->filter()
            ->implode(' / ');

        return [
            ++$this->number,
            $asset->code ?: '-',
            $asset->name ?: '-',
            $asset->assetCategory?->name
                ?: '-',
            $brandModel ?: '-',
            $asset->serial_number ?: '-',
            $asset->location?->name ?: '-',
            $asset->responsibleUser?->name
                ?: '-',
            $this->formatText(
                $asset->status
            ),
            $this->formatText(
                $asset->condition
            ),
            $this->formatDate(
                $asset->purchase_date
            ),
        ];
    }

    public function headings(): array
    {
        return [
            'No.',
            'Kode Aset',
            'Nama Aset',
            'Kategori',
            'Merek / Model',
            'Nomor Seri',
            'Lokasi',
            'Penanggung Jawab',
            'Status',
            'Kondisi',
            'Tanggal Pembelian',
        ];
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 7,
            'B' => 18,
            'C' => 28,
            'D' => 21,
            'E' => 24,
            'F' => 22,
            'G' => 22,
            'H' => 23,
            'I' => 15,
            'J' => 17,
            'K' => 18,
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

                $filterSummary = app(
                    AssetExportService::class
                )->filterSummary(
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

                $filterText = sprintf(
                    'Kategori: %s | Lokasi: %s | Status: %s',
                    $filterSummary['category'],
                    $filterSummary['location'],
                    $filterSummary['status']
                );

                foreach (
                    range(1, 5)
                    as $row
                ) {
                    $sheet->mergeCells(
                        "A{$row}:K{$row}"
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
                    'LAPORAN DATA INVENTARIS ASET IT'
                );

                $sheet->setCellValue(
                    'A4',
                    sprintf(
                        'Nomor Dokumen: %s | Status: %s',
                        $this
                            ->documentNumber
                            ?? '-',
                        strtoupper(
                            $this
                                ->documentStatus
                        )
                    )
                );

                $sheet->setCellValue(
                    'A5',
                    sprintf(
                        '%s | Tanggal dibuat: %s | Dibuat oleh: %s',
                        $filterText,
                        $this->generatedAt,
                        $this->generatedBy
                    )
                );

                $sheet
                    ->getStyle('A1:K5')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    )
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    )
                    ->setWrapText(true);

                $sheet
                    ->getStyle('A1:K1')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(16);

                $sheet
                    ->getStyle('A3:K3')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(13);

                $sheet
                    ->getStyle('A6:K6')
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
                            Alignment::HORIZONTAL_CENTER,

                            'vertical' =>
                            Alignment::VERTICAL_CENTER,

                            'wrapText' =>
                            true,
                        ],

                        'borders' => [
                            'allBorders' => [
                                'borderStyle' =>
                                Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                $lastRow = $sheet
                    ->getHighestRow();

                if ($lastRow >= 7) {
                    $sheet
                        ->getStyle(
                            "A7:K{$lastRow}"
                        )
                        ->applyFromArray([
                            'alignment' => [
                                'vertical' =>
                                Alignment::VERTICAL_TOP,

                                'wrapText' =>
                                true,
                            ],

                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' =>
                                    Border::BORDER_THIN,

                                    'color' => [
                                        'argb' =>
                                        'FFD9D9D9',
                                    ],
                                ],
                            ],
                        ]);

                    $sheet->setAutoFilter(
                        "A6:K{$lastRow}"
                    );
                }

                $sheet->freezePane('A7');

                $sheet->getPageSetup()
                    ->setOrientation(
                        PageSetup::ORIENTATION_LANDSCAPE
                    )
                    ->setPaperSize(
                        PageSetup::PAPERSIZE_A4
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
        return 'Inventaris Aset';
    }

    public function properties(): array
    {
        return [
            'creator' =>
            $this->generatedBy,

            'lastModifiedBy' =>
            $this->generatedBy,

            'title' =>
            'Laporan Inventaris Aset IT',

            'subject' =>
            'Inventaris Aset IT',

            'category' =>
            'Laporan IT',

            'company' =>
            config('company.name'),
        ];
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
}
