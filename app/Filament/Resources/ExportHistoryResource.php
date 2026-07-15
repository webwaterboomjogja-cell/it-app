<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExportHistoryResource\Pages;
use App\Models\Exporthistory;
use App\Services\Exports\ExportArchiveService;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ExportHistoryResource extends Resource
{
    protected static ?string $model =
    Exporthistory::class;

    protected static ?string $navigationIcon =
    'heroicon-o-archive-box';

    protected static ?string $navigationGroup =
    'Laporan';

    protected static ?string $navigationLabel =
    'Arsip Laporan';

    protected static ?string $modelLabel =
    'Arsip Laporan';

    protected static ?string $pluralModelLabel =
    'Arsip Laporan';

    protected static ?int $navigationSort = 91;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can(
            'view_export_history'
        ) ?? false;
    }

    public static function table(
        Table $table
    ): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make(
                    'document_number'
                )
                    ->label('Nomor Dokumen')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make(
                    'report_type'
                )
                    ->label('Jenis Laporan')
                    ->formatStateUsing(
                        fn(
                            Exporthistory $record
                        ): string =>
                        $record
                            ->reportTypeLabel()
                    )
                    ->badge()
                    ->color(
                        fn(
                            string $state
                        ): string => match ($state) {
                            'assets' => 'info',

                            'daily_reports' =>
                            'warning',

                            'monthly_reports' =>
                            'success',

                            default => 'gray',
                        }
                    ),

                Tables\Columns\TextColumn::make(
                    'format'
                )
                    ->label('Format')
                    ->formatStateUsing(
                        fn(
                            string $state
                        ): string =>
                        strtoupper($state)
                    )
                    ->badge(),

                Tables\Columns\TextColumn::make(
                    'document_status'
                )
                    ->label('Status Dokumen')
                    ->formatStateUsing(
                        fn(
                            string $state
                        ): string =>
                        strtoupper($state)
                    )
                    ->badge()
                    ->color(
                        fn(
                            string $state
                        ): string => match ($state) {
                            'final' => 'success',
                            default => 'warning',
                        }
                    ),

                Tables\Columns\TextColumn::make(
                    'generation_status'
                )
                    ->label('Proses')
                    ->formatStateUsing(
                        fn(
                            string $state
                        ): string =>
                        str($state)
                            ->headline()
                            ->toString()
                    )
                    ->badge()
                    ->color(
                        fn(
                            string $state
                        ): string => match ($state) {
                            'completed' =>
                            'success',

                            'failed' => 'danger',

                            default => 'warning',
                        }
                    ),

                Tables\Columns\TextColumn::make(
                    'user.name'
                )
                    ->label('Dibuat Oleh')
                    ->placeholder('Sistem'),

                Tables\Columns\TextColumn::make(
                    'file_size'
                )
                    ->label('Ukuran')
                    ->formatStateUsing(
                        fn(
                            Exporthistory $record
                        ): string =>
                        $record
                            ->formattedFileSize()
                    ),

                Tables\Columns\TextColumn::make(
                    'download_count'
                )
                    ->label('Download')
                    ->numeric()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make(
                    'generated_at'
                )
                    ->label('Tanggal Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make(
                    'report_type'
                )
                    ->label('Jenis Laporan')
                    ->options([
                        'assets' =>
                        'Inventaris Aset',

                        'daily_reports' =>
                        'Laporan Harian',

                        'monthly_reports' =>
                        'Laporan Bulanan',
                    ]),

                Tables\Filters\SelectFilter::make(
                    'document_status'
                )
                    ->label('Status Dokumen')
                    ->options([
                        'draft' => 'Draft',
                        'final' => 'Final',
                    ]),

                Tables\Filters\SelectFilter::make(
                    'format'
                )
                    ->options([
                        'xlsx' => 'Excel',
                        'pdf' => 'PDF',
                    ]),
            ])
            ->actions([

                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->visible(
                        fn(Exporthistory $record): bool =>
                        $record->isCompleted() &&
                            auth()->user()?->can(
                                'download_export_archive'
                            )
                    )
                    ->action(
                        function (Exporthistory $record) {


                            abort_unless(
                                auth()->user()?->can(
                                    'download_export_archive'
                                ),
                                403,
                                'Anda tidak memiliki izin mengunduh arsip laporan.'
                            );


                            if (! $record->fileExists()) {
                                Notification::make()
                                    ->title('File tidak ditemukan')
                                    ->body(
                                        'File arsip tidak ditemukan pada penyimpanan.'
                                    )
                                    ->danger()
                                    ->send();

                                return null;
                            }



                            try {
                                $contents = Storage::disk(
                                    $record->disk
                                )->get(
                                    $record->file_path
                                );
                            } catch (\Throwable $exception) {
                                report($exception);

                                Notification::make()
                                    ->title('File gagal dibaca')
                                    ->body(
                                        'Sistem tidak dapat membaca file arsip.'
                                    )
                                    ->danger()
                                    ->send();

                                return null;
                            }

                            $currentChecksum = hash(
                                'sha256',
                                $contents
                            );

                            if (
                                filled($record->checksum) &&
                                ! hash_equals(
                                    $record->checksum,
                                    $currentChecksum
                                )
                            ) {
                                Notification::make()
                                    ->title(
                                        'Integritas file bermasalah'
                                    )
                                    ->body(
                                        'Checksum file tidak cocok. File mungkin telah berubah atau rusak.'
                                    )
                                    ->danger()
                                    ->persistent()
                                    ->send();

                                return null;
                            }

                            $record->increment(
                                'download_count'
                            );

                            $record->update([
                                'last_downloaded_at' => now(),
                            ]);



                            return response()->streamDownload(
                                static function () use (
                                    $contents
                                ): void {
                                    echo $contents;
                                },
                                $record->original_filename
                                    ?: basename(
                                        $record->file_path
                                    ),
                                [
                                    'Content-Type' =>
                                    $record->format === 'pdf'
                                        ? 'application/pdf'
                                        : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                ]
                            );
                        }
                    ),

                Tables\Actions\Action::make(
                    'finalize'
                )
                    ->label('Finalisasi')
                    ->icon(
                        'heroicon-o-check-badge'
                    )
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(
                        'Finalisasi Dokumen'
                    )
                    ->modalDescription(
                        'Dokumen yang sudah final tidak boleh diubah.'
                    )
                    ->visible(
                        fn(
                            Exporthistory $record
                        ): bool =>
                        ! $record->isFinal() &&
                            $record->isCompleted() &&
                            auth()->user()?->can(
                                'finalize_export_document'
                            )
                    )
                    ->action(
                        function (
                            Exporthistory $record
                        ): void {
                            try {
                                app(
                                    ExportArchiveService::class
                                )->finalize(
                                    $record
                                );

                                Notification::make()
                                    ->title(
                                        'Dokumen berhasil difinalisasi'
                                    )
                                    ->success()
                                    ->send();
                            } catch (
                                Throwable $exception
                            ) {
                                Notification::make()
                                    ->title(
                                        'Finalisasi gagal'
                                    )
                                    ->body(
                                        $exception
                                            ->getMessage()
                                    )
                                    ->danger()
                                    ->send();
                            }
                        }
                    ),
            ])
            ->defaultSort(
                'generated_at',
                'desc'
            )
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' =>
            Pages\ListExportHistories::route(
                '/'
            ),
        ];
    }
}
