<?php

namespace App\Filament\Resources\MonthlyItReportResource\Pages;

use App\Filament\Resources\MonthlyItReportResource;
use App\Models\MonthlyItReport;
use App\Services\MonthlyItReportGeneratorService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListMonthlyItReports extends ListRecords
{
    protected static string $resource =
        MonthlyItReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generateMonthlyReport')
                ->label('Buat Laporan Bulanan')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->modalHeading(
                    'Buat Laporan Bulanan IT'
                )
                ->modalDescription(
                    'Pilih bulan dan tahun yang akan direkap. Sistem akan mengambil data laporan harian, aset, dan jadwal secara otomatis.'
                )
                ->modalSubmitActionLabel(
                    'Generate Rekap'
                )
                ->form([
                    Forms\Components\Select::make('month')
                        ->label('Bulan')
                        ->options(
                            Monthlyitreport::monthOptions()
                        )
                        ->default(now()->month)
                        ->native(false)
                        ->searchable()
                        ->required(),

                    Forms\Components\Select::make('year')
                        ->label('Tahun')
                        ->options($this->yearOptions())
                        ->default(now()->year)
                        ->native(false)
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $month = (int) $data['month'];
                    $year = (int) $data['year'];

                    /*
                    |--------------------------------------------------------------------------
                    | Cek apakah periode sudah pernah dibuat
                    |--------------------------------------------------------------------------
                    */

                    $existingReport = Monthlyitreport::query()
                        ->where('month', $month)
                        ->where('year', $year)
                        ->first();

                    if ($existingReport?->isFinalized()) {
                        Notification::make()
                            ->title(
                                'Laporan sudah difinalisasi'
                            )
                            ->body(
                                "Laporan {$existingReport->period_label} sudah final dan tidak dapat dibuat ulang."
                            )
                            ->danger()
                            ->send();

                        return;
                    }

                    if ($existingReport) {
                        Notification::make()
                            ->title(
                                'Laporan periode tersebut sudah ada'
                            )
                            ->body(
                                'Anda diarahkan ke laporan draft yang sudah tersedia. Gunakan tombol Generate Ulang untuk memperbarui rekap.'
                            )
                            ->warning()
                            ->send();

                        $this->redirect(
                            MonthlyItReportResource::getUrl(
                                'edit',
                                [
                                    'record' =>
                                        $existingReport,
                                ]
                            )
                        );

                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Generate laporan baru
                    |--------------------------------------------------------------------------
                    */

                    $report = app(
                        MonthlyItReportGeneratorService::class
                    )->generate(
                        month: $month,
                        year: $year,
                        generatedBy: auth()->id(),
                    );

                    Notification::make()
                        ->title(
                            'Laporan bulanan berhasil dibuat'
                        )
                        ->body(
                            "Rekap {$report->period_label} berhasil digenerate."
                        )
                        ->success()
                        ->send();

                    $this->redirect(
                        MonthlyItReportResource::getUrl(
                            'edit',
                            [
                                'record' => $report,
                            ]
                        )
                    );
                }),
        ];
    }

    private function yearOptions(): array
    {
        $currentYear = now()->year;

        return collect(
            range($currentYear + 1, $currentYear - 5)
        )
            ->mapWithKeys(
                fn (int $year): array => [
                    $year => (string) $year,
                ]
            )
            ->all();
    }
}