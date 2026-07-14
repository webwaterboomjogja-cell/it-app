<?php

namespace App\Filament\Resources\MonthlyItReportResource\Pages;

use App\Filament\Resources\MonthlyItReportResource;
use App\Models\Monthlyitreport;
use App\Services\MonthlyItReportGeneratorService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMonthlyItReport extends EditRecord
{
    protected static string $resource =
    MonthlyItReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('regenerate')
                ->label('Generate Ulang')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading(
                    'Generate Ulang Rekap'
                )
                ->modalDescription(
                    'Rekap pekerjaan, aset, dan jadwal akan diperbarui. Evaluasi dan rekomendasi yang sudah disimpan tidak akan dihapus.'
                )
                ->modalSubmitActionLabel(
                    'Ya, Generate Ulang'
                )
                ->visible(
                    fn(): bool =>
                    $this->record->isDraft()
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
                        ->title(
                            'Rekap berhasil diperbarui'
                        )
                        ->body(
                            "Data {$updatedReport->period_label} telah digenerate ulang."
                        )
                        ->success()
                        ->send();


                    $this->redirect(
                        MonthlyItReportResource::getUrl(
                            'edit',
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
                ->modalHeading(
                    'Finalisasi Laporan Bulanan'
                )
                ->modalDescription(
                    'Pastikan evaluasi dan rekomendasi sudah disimpan. Setelah finalisasi, laporan akan dikunci dan tidak dapat diedit kembali.'
                )
                ->modalSubmitActionLabel(
                    'Ya, Finalisasi'
                )
                ->visible(
                    fn(): bool =>
                    $this->record->isDraft()
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
                                'Isi dan simpan evaluasi bulanan serta rekomendasi bulan berikutnya terlebih dahulu.'
                            )
                            ->danger()
                            ->send();

                        return;
                    }

                    $record->finalizeBy(
                        auth()->id()
                    );

                    Notification::make()
                        ->title(
                            'Laporan berhasil difinalisasi'
                        )
                        ->body(
                            "Laporan {$record->period_label} telah dikunci."
                        )
                        ->success()
                        ->send();

                    $this->redirect(
                        MonthlyItReportResource::getUrl(
                            'view',
                            [
                                'record' => $record,
                            ]
                        )
                    );
                }),

            Actions\DeleteAction::make()
                ->label('Hapus Laporan')
                ->visible(
                    fn(): bool =>
                    $this->record->isDraft()
                ),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Evaluasi dan rekomendasi berhasil disimpan';
    }

    protected function getRedirectUrl(): string
    {

        return MonthlyItReportResource::getUrl(
            'edit',
            [
                'record' => $this->record,
            ]
        );
    }
}
