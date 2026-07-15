<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class MonthlyTableSheet implements
    FromArray,
    WithHeadings,
    WithCustomStartCell,
    WithColumnWidths,
    WithEvents,
    WithTitle,
    WithStrictNullComparison
{
    public function __construct(
        private readonly string $sheetTitle,
        private readonly string $reportTitle,
        private readonly string $period,
        private readonly string $generatedBy,
        private readonly string $generatedAt,
        private readonly array $headings,
        private readonly array $rows,
        private readonly array $widths = [],
        private readonly string $orientation =
        PageSetup::ORIENTATION_LANDSCAPE
    ) {}

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function columnWidths(): array
    {
        return $this->widths;
    }

    public function title(): string
    {
        return mb_substr(
            $this->sheetTitle,
            0,
            31
        );
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (
                AfterSheet $event
            ): void {
                $sheet = $event->sheet
                    ->getDelegate();

                $lastColumn =
                    Coordinate::stringFromColumnIndex(
                        max(
                            1,
                            count($this->headings)
                        )
                    );

                for ($row = 1; $row <= 5; $row++) {
                    $sheet->mergeCells(
                        "A{$row}:{$lastColumn}{$row}"
                    );
                }

                $companyName = config(
                    'company.name',
                    config('app.name')
                );

                $companyDescription = collect([
                    config('company.division'),
                    config('company.address'),
                ])
                    ->filter()
                    ->implode(' | ');

                $sheet->setCellValue(
                    'A1',
                    strtoupper($companyName)
                );

                $sheet->setCellValue(
                    'A2',
                    $companyDescription
                );

                $sheet->setCellValue(
                    'A3',
                    strtoupper(
                        $this->reportTitle
                    )
                );

                $sheet->setCellValue(
                    'A4',
                    "Periode: {$this->period}"
                );

                $sheet->setCellValue(
                    'A5',
                    sprintf(
                        'Dibuat: %s | Oleh: %s',
                        $this->generatedAt,
                        $this->generatedBy
                    )
                );

                $sheet->getStyle(
                    "A1:{$lastColumn}5"
                )
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    )
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    )
                    ->setWrapText(true);

                $sheet->getStyle(
                    "A1:{$lastColumn}1"
                )
                    ->getFont()
                    ->setBold(true)
                    ->setSize(16);

                $sheet->getStyle(
                    "A3:{$lastColumn}3"
                )
                    ->getFont()
                    ->setBold(true)
                    ->setSize(13);

                $sheet->getStyle(
                    "A6:{$lastColumn}6"
                )->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => [
                            'argb' => 'FFFFFFFF',
                        ],
                    ],

                    'fill' => [
                        'fillType' =>
                        Fill::FILL_SOLID,

                        'startColor' => [
                            'argb' => 'FF1F4E78',
                        ],
                    ],

                    'alignment' => [
                        'horizontal' =>
                        Alignment::HORIZONTAL_CENTER,

                        'vertical' =>
                        Alignment::VERTICAL_CENTER,

                        'wrapText' => true,
                    ],

                    'borders' => [
                        'allBorders' => [
                            'borderStyle' =>
                            Border::BORDER_THIN,

                            'color' => [
                                'argb' => 'FF000000',
                            ],
                        ],
                    ],
                ]);

                $lastRow = $sheet->getHighestRow();

                if ($lastRow >= 7) {
                    $sheet->getStyle(
                        "A7:{$lastColumn}{$lastRow}"
                    )->applyFromArray([
                        'alignment' => [
                            'vertical' =>
                            Alignment::VERTICAL_TOP,

                            'wrapText' => true,
                        ],

                        'borders' => [
                            'allBorders' => [
                                'borderStyle' =>
                                Border::BORDER_THIN,

                                'color' => [
                                    'argb' => 'FFD1D5DB',
                                ],
                            ],
                        ],
                    ]);

                    $sheet->setAutoFilter(
                        "A6:{$lastColumn}{$lastRow}"
                    );
                }

                $sheet->freezePane('A7');

                $sheet->getRowDimension(1)
                    ->setRowHeight(25);

                $sheet->getRowDimension(3)
                    ->setRowHeight(22);

                $sheet->getRowDimension(6)
                    ->setRowHeight(30);

                $sheet->getPageSetup()
                    ->setOrientation(
                        $this->orientation
                    )
                    ->setPaperSize(
                        PageSetup::PAPERSIZE_A4
                    )
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);

                $sheet->getPageSetup()
                    ->setRowsToRepeatAtTopByStartAndEnd(
                        1,
                        6
                    );

                $sheet->getPageMargins()
                    ->setTop(0.4)
                    ->setRight(0.3)
                    ->setBottom(0.5)
                    ->setLeft(0.3);

                $sheet->getHeaderFooter()
                    ->setOddFooter(
                        '&L' .
                            $companyName .
                            '&RHalaman &P dari &N'
                    );
            },
        ];
    }
}
