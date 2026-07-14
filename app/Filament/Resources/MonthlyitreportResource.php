<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MonthlyItReportResource\Pages;
use App\Models\Monthlyitreport;
use App\Services\MonthlyItReportGeneratorService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Builder;

class MonthlyItReportResource extends Resource
{
    protected static ?string $model = Monthlyitreport::class;

    protected static ?string $navigationIcon =
    'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup =
    'Laporan IT';

    protected static ?string $navigationLabel =
    'Laporan Bulanan';

    protected static ?string $modelLabel =
    'Laporan Bulanan';

    protected static ?string $pluralModelLabel =
    'Laporan Bulanan IT';

    protected static ?int $navigationSort = 7;

    /**
     * Create standar dinonaktifkan karena pembuatan laporan
     * dilakukan melalui action Generate Rekap.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    /**
     * Hanya laporan draft yang dapat diedit.
     */
    public static function canEdit(Model $record): bool
    {
        return parent::canEdit($record)
            && $record instanceof Monthlyitreport
            && $record->isDraft();
    }

    /**
     * Hanya laporan draft yang dapat dihapus.
     */
    public static function canDelete(Model $record): bool
    {
        return parent::canDelete($record)
            && $record instanceof Monthlyitreport
            && $record->isDraft();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'generatedBy',
                'approvedBy',
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(
                    'Informasi Laporan'
                )
                    ->description(
                        'Informasi periode dan status laporan bulanan.'
                    )
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Forms\Components\Select::make('month')
                            ->label('Bulan')
                            ->options(
                                Monthlyitreport::monthOptions()
                            )
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('year')
                            ->label('Tahun')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\DatePicker::make(
                            'period_start'
                        )
                            ->label('Awal Periode')
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\DatePicker::make(
                            'period_end'
                        )
                            ->label('Akhir Periode')
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('status')
                            ->label('Status Laporan')
                            ->options(
                                Monthlyitreport::statusOptions()
                            )
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Placeholder::make(
                            'generated_by_display'
                        )
                            ->label('Dibuat Oleh')
                            ->content(
                                fn(?Monthlyitreport $record): string =>
                                $record?->generatedBy?->name ?? '-'
                            ),

                        Forms\Components\Placeholder::make(
                            'generated_at_display'
                        )
                            ->label('Terakhir Digenerate')
                            ->content(
                                fn(?Monthlyitreport $record): string =>
                                $record?->generated_at
                                    ?->format('d M Y H:i')
                                    ?? '-'
                            ),

                        Forms\Components\Placeholder::make(
                            'approved_by_display'
                        )
                            ->label('Disetujui Oleh')
                            ->content(
                                fn(?Monthlyitreport $record): string =>
                                $record?->approvedBy?->name ?? '-'
                            ),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 4,
                    ]),

                Forms\Components\Section::make(
                    'Ringkasan Hasil Generate'
                )
                    ->description(
                        'Jumlah pekerjaan, aset, dan jadwal yang masuk dalam laporan.'
                    )
                    ->icon('heroicon-o-chart-bar-square')
                    ->schema([
                        Forms\Components\Placeholder::make(
                            'total_daily_reports_display'
                        )
                            ->label('Total Laporan Harian')
                            ->content(
                                fn(?Monthlyitreport $record): string =>
                                number_format(
                                    $record?->total_daily_reports ?? 0
                                )
                            ),

                        Forms\Components\Placeholder::make(
                            'total_completed_display'
                        )
                            ->label('Pekerjaan Selesai')
                            ->content(
                                fn(?Monthlyitreport $record): string =>
                                number_format(
                                    $record?->total_completed ?? 0
                                )
                            ),

                        Forms\Components\Placeholder::make(
                            'total_pending_display'
                        )
                            ->label('Pekerjaan Tertunda')
                            ->content(
                                fn(?Monthlyitreport $record): string =>
                                number_format(
                                    $record?->total_pending ?? 0
                                )
                            ),

                        Forms\Components\Placeholder::make(
                            'total_urgent_display'
                        )
                            ->label('Pekerjaan Urgent')
                            ->content(
                                fn(?Monthlyitreport $record): string =>
                                number_format(
                                    $record?->total_urgent ?? 0
                                )
                            ),

                        Forms\Components\Placeholder::make(
                            'total_assets_display'
                        )
                            ->label('Total Aset')
                            ->content(
                                fn(?Monthlyitreport $record): string =>
                                number_format(
                                    $record?->total_assets ?? 0
                                )
                            ),

                        Forms\Components\Placeholder::make(
                            'total_problem_assets_display'
                        )
                            ->label('Aset Bermasalah')
                            ->content(
                                fn(?Monthlyitreport $record): string =>
                                number_format(
                                    $record?->total_problem_assets ?? 0
                                )
                            ),

                        Forms\Components\Placeholder::make(
                            'total_maintenance_assets_display'
                        )
                            ->label('Aset Maintenance')
                            ->content(
                                fn(?Monthlyitreport $record): string =>
                                number_format(
                                    $record
                                        ?->total_maintenance_assets
                                        ?? 0
                                )
                            ),

                        Forms\Components\Placeholder::make(
                            'total_schedules_display'
                        )
                            ->label('Total Jadwal')
                            ->content(
                                fn(?Monthlyitreport $record): string =>
                                number_format(
                                    $record?->total_schedules ?? 0
                                )
                            ),
                    ])
                    ->columns([
                        'default' => 2,
                        'md' => 4,
                    ])
                    ->collapsible(),

                Forms\Components\Section::make(
                    'Evaluasi Bulanan'
                )
                    ->description(
                        'Berisi hasil evaluasi kerja tim IT selama periode laporan.'
                    )
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        Forms\Components\RichEditor::make(
                            'evaluation'
                        )
                            ->label('Evaluasi Bulanan')
                            ->placeholder(
                                'Tuliskan pencapaian, kendala, pekerjaan tertunda, aset bermasalah, dan hasil evaluasi tim IT...'
                            )
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                                'undo',
                                'redo',
                            ])
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make(
                            'recommendation'
                        )
                            ->label(
                                'Rekomendasi Bulan Berikutnya'
                            )
                            ->placeholder(
                                'Tuliskan prioritas pekerjaan, maintenance, pengadaan, peningkatan jaringan, dan rekomendasi lainnya...'
                            )
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                                'undo',
                                'redo',
                            ])
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Tambahan')
                            ->placeholder(
                                'Catatan internal atau informasi tambahan...'
                            )
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('period_start', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('month')
                    ->label('Periode')
                    ->formatStateUsing(
                        fn(
                            mixed $state,
                            Monthlyitreport $record
                        ): string => $record->period_label
                    )
                    ->icon('heroicon-m-calendar-days')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn(string $state): string =>
                        Monthlyitreport::statusOptions()[$state]
                            ?? ucfirst($state)
                    )
                    ->color(
                        fn(string $state): string => match ($state) {
                            Monthlyitreport::STATUS_DRAFT =>
                            'warning',

                            Monthlyitreport::STATUS_FINALIZED =>
                            'success',

                            default => 'gray',
                        }
                    ),

                Tables\Columns\TextColumn::make(
                    'total_daily_reports'
                )
                    ->label('Laporan')
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'total_completed'
                )
                    ->label('Selesai')
                    ->numeric()
                    ->alignCenter()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'total_pending'
                )
                    ->label('Tertunda')
                    ->numeric()
                    ->alignCenter()
                    ->color('warning')
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'total_urgent'
                )
                    ->label('Urgent')
                    ->numeric()
                    ->alignCenter()
                    ->color('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'total_problem_assets'
                )
                    ->label('Aset Bermasalah')
                    ->numeric()
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'total_schedules'
                )
                    ->label('Jadwal')
                    ->numeric()
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'generatedBy.name'
                )
                    ->label('Dibuat Oleh')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'generated_at'
                )
                    ->label('Terakhir Generate')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'finalized_at'
                )
                    ->label('Finalisasi')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(
                        Monthlyitreport::statusOptions()
                    ),

                Tables\Filters\SelectFilter::make('month')
                    ->label('Bulan')
                    ->options(
                        Monthlyitreport::monthOptions()
                    ),

                Tables\Filters\SelectFilter::make('year')
                    ->label('Tahun')
                    ->options(self::yearOptions()),
            ])
            ->actions([

                Tables\Actions\ViewAction::make()
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('gray'),


                Tables\Actions\EditAction::make()
                    ->label('Evaluasi')
                    ->icon('heroicon-o-pencil-square')
                    ->visible(
                        fn(MonthlyItReport $record): bool =>
                        $record->isDraft()
                    ),

                Tables\Actions\Action::make('regenerate')
                    ->label('Generate Ulang')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Generate Ulang Laporan')
                    ->modalDescription(
                        'Snapshot rekap akan diperbarui menggunakan data laporan harian, aset, dan jadwal terbaru. Evaluasi serta rekomendasi tidak akan dihapus.'
                    )
                    ->modalSubmitActionLabel(
                        'Ya, Generate Ulang'
                    )
                    ->visible(
                        fn(Monthlyitreport $record): bool =>
                        $record->canRegenerate()
                    )
                    ->action(
                        function (
                            Monthlyitreport $record
                        ): void {
                            app(
                                MonthlyItReportGeneratorService::class
                            )->generate(
                                month: $record->month,
                                year: $record->year,
                                generatedBy: auth()->id(),
                            );

                            Notification::make()
                                ->title(
                                    'Laporan berhasil digenerate ulang'
                                )
                                ->body(
                                    "Rekap {$record->period_label} telah diperbarui."
                                )
                                ->success()
                                ->send();
                        }
                    ),

                Tables\Actions\Action::make('finalize')
                    ->label('Finalisasi')
                    ->icon('heroicon-o-lock-closed')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(
                        'Finalisasi Laporan Bulanan'
                    )
                    ->modalDescription(
                        'Setelah difinalisasi, laporan tidak dapat diedit, dihapus, atau digenerate ulang.'
                    )
                    ->modalSubmitActionLabel(
                        'Ya, Finalisasi'
                    )
                    ->visible(
                        fn(Monthlyitreport $record): bool =>
                        $record->isDraft()
                    )
                    ->action(
                        function (
                            Monthlyitreport $record
                        ): void {
                            $record->refresh();

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
                        }
                    ),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->visible(
                        fn(Monthlyitreport $record): bool =>
                        $record->isDraft()
                    ),
            ])
            ->bulkActions([])
            ->emptyStateHeading(
                'Belum ada laporan bulanan'
            )
            ->emptyStateDescription(
                'Klik tombol Buat Laporan Bulanan untuk melakukan generate rekap.'
            )
            ->emptyStateIcon(
                'heroicon-o-document-chart-bar'
            );
    }

    public static function getPages(): array
    {
        return [
            'index' =>
            Pages\ListMonthlyItReports::route('/'),

            'view' =>
            Pages\ViewMonthlyItReport::route('/{record}'),

            'edit' =>
            Pages\EditMonthlyItReport::route(
                '/{record}/edit'
            ),
        ];
    }
    private static function yearOptions(): array
    {
        $currentYear = now()->year;

        return collect(
            range($currentYear + 1, $currentYear - 5)
        )
            ->mapWithKeys(
                fn(int $year): array => [
                    $year => (string) $year,
                ]
            )
            ->all();
    }
}
