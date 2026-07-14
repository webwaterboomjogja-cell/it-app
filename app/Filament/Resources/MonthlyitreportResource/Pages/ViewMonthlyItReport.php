<?php

namespace App\Filament\Resources\MonthlyItReportResource\Pages;

use App\Filament\Resources\MonthlyItReportResource;
use App\Models\MonthlyItReport;
use App\Services\MonthlyItReportGeneratorService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewMonthlyItReport extends ViewRecord
{
    protected static string $resource =
        MonthlyItReportResource::class;

    /**
     * Menggunakan Blade khusus karena data rekap
     * tersimpan sebagai snapshot JSON.
     */
    protected static string $view =
        'filament.resources.monthly-it-report-resource.pages.view-monthly-it-report';

    public function getTitle(): string
    {
        return "Laporan Bulanan {$this->record->period_label}";
    }

    public function getSubheading(): ?string
    {
        $start = $this->record->period_start
            ?->locale('id')
            ->translatedFormat('d F Y');

        $end = $this->record->period_end
            ?->locale('id')
            ->translatedFormat('d F Y');

        return "Periode {$start} sampai {$end}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit Evaluasi')
                ->icon('heroicon-o-pencil-square')
                ->visible(
                    fn (): bool => $this->record->isDraft()
                ),

            Actions\Action::make('regenerate')
                ->label('Generate Ulang')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Generate Ulang Laporan')
                ->modalDescription(
                    'Data rekap staff, kategori, status, prioritas, aset, dan jadwal akan diperbarui menggunakan data terbaru. Evaluasi dan rekomendasi tidak akan dihapus.'
                )
                ->modalSubmitActionLabel('Ya, Generate Ulang')
                ->visible(
                    fn (): bool => $this->record->canRegenerate()
                )
                ->action(function (): void {
                    /** @var Monthlyitreport $record */
                    $record = $this->record;

                    $updatedReport = app(
                        MonthlyItReportGeneratorService::class
                    )->generate(
                        month: $record->month,
                        year: $record->year,
                        generatedBy: auth()->id(),
                    );

                    Notification::make()
                        ->title('Laporan berhasil digenerate ulang')
                        ->body(
                            "Rekap {$updatedReport->period_label} telah diperbarui."
                        )
                        ->success()
                        ->send();

                    $this->redirect(
                        MonthlyItReportResource::getUrl(
                            'view',
                            [
                                'record' => $updatedReport,
                            ]
                        )
                    );
                }),

            Actions\Action::make('finalize')
                ->label('Finalisasi')
                ->icon('heroicon-o-lock-closed')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Finalisasi Laporan Bulanan')
                ->modalDescription(
                    'Setelah difinalisasi, laporan tidak dapat diedit, dihapus, atau digenerate ulang.'
                )
                ->modalSubmitActionLabel('Ya, Finalisasi')
                ->visible(
                    fn (): bool => $this->record->isDraft()
                )
                ->action(function (): void {
                    /** @var MonthlyItReport $record */
                    $record = $this->record->refresh();

                    if (! $record->isReadyToFinalize()) {
                        Notification::make()
                            ->title(
                                'Laporan belum dapat difinalisasi'
                            )
                            ->body(
                                'Evaluasi bulanan dan rekomendasi bulan berikutnya harus diisi dan disimpan terlebih dahulu.'
                            )
                            ->danger()
                            ->send();

                        return;
                    }

                    $record->finalizeBy(auth()->id());

                    Notification::make()
                        ->title('Laporan berhasil difinalisasi')
                        ->body(
                            "Laporan {$record->period_label} telah dikunci."
                        )
                        ->success()
                        ->send();

                    $this->redirect(
                        MonthlyitReportResource::getUrl(
                            'view',
                            [
                                'record' => $record,
                            ]
                        )
                    );
                }),
        ];
    }
}