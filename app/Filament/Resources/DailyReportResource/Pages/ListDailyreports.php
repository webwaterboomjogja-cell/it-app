<?php

namespace App\Filament\Resources\DailyreportResource\Pages;

use App\Filament\Resources\DailyReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Exports\DailyReportsExport;
use App\Filament\Widgets\DailyReportStatsOverview;
use App\Filament\Widgets\FrequentProblemAssetsTable;
use App\Models\Itassests;
use App\Models\User;
use App\Models\Workcategory;
use Filament\Forms;
use Maatwebsite\Excel\Facades\Excel;

class ListDailyreports extends ListRecords
{
    protected static string $resource = DailyReportResource::class;

    protected function isKepalaIt(): bool
    {
        return auth()->user()?->hasAnyRole([
            'super_admin',
            'kepala_it',
        ]) ?? false;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DailyReportStatsOverview::class,
            FrequentProblemAssetsTable::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('export_excel')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn(): bool => $this->isKepalaIt())
                ->form([
                    Forms\Components\Section::make('Filter Export')
                        ->description('Kosongkan filter jika ingin export semua data laporan.')
                        ->schema([
                            Forms\Components\DatePicker::make('start_date')
                                ->label('Tanggal Awal')
                                ->default(now()->startOfMonth()),

                            Forms\Components\DatePicker::make('end_date')
                                ->label('Tanggal Akhir')
                                ->default(now())
                                ->afterOrEqual('start_date'),

                            Forms\Components\Select::make('user_id')
                                ->label('Staff IT')
                                ->options(fn() => User::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray())
                                ->searchable()
                                ->preload()
                                ->placeholder('Semua Staff'),

                            Forms\Components\Select::make('work_category_id')
                                ->label('Kategori Pekerjaan')
                                ->options(fn() => Workcategory::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray())
                                ->searchable()
                                ->preload()
                                ->placeholder('Semua Kategori'),

                            Forms\Components\Select::make('asset_id')
                                ->label('Aset')
                                ->options(fn() => Itassests::query()
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(function ($asset) {
                                        $assetCode = data_get($asset, 'code')
                                            ?? data_get($asset, 'inventory_code');

                                        $label = $assetCode
                                            ? "{$assetCode} - {$asset->name}"
                                            : $asset->name;

                                        return [$asset->id => $label];
                                    })
                                    ->toArray())
                                ->searchable()
                                ->preload()
                                ->placeholder('Semua Aset'),

                            Forms\Components\Select::make('priority')
                                ->label('Prioritas')
                                ->options([
                                    'rendah' => 'Rendah',
                                    'normal' => 'Normal',
                                    'tinggi' => 'Tinggi',
                                    'urgent' => 'Urgent',
                                ])
                                ->placeholder('Semua Prioritas'),

                            Forms\Components\Select::make('work_status')
                                ->label('Status Pekerjaan')
                                ->options([
                                    'selesai' => 'Selesai',
                                    'proses' => 'Proses',
                                    'tertunda' => 'Tertunda',
                                ])
                                ->placeholder('Semua Status Pekerjaan'),

                            Forms\Components\Select::make('review_status')
                                ->label('Status Review')
                                ->options([
                                    'draft' => 'Draft',
                                    'dikirim' => 'Dikirim',
                                    'direview' => 'Direview',
                                ])
                                ->placeholder('Semua Status Review'),
                        ])
                        ->columns(2),
                ])
                ->action(function (array $data) {
                    $generatedAt = now();

                    $generatedBy = auth()->user()?->name
                        ?? 'Sistem';

                    $documentNumber = sprintf(
                        'LHI/%s/%s',
                        $generatedAt->format('Ym'),
                        $generatedAt->format('His')
                    );

                    $fileName = 'laporan-harian-it-'
                        . $generatedAt->format('Y-m-d-His')
                        . '.xlsx';

                    return Excel::download(
                        new DailyReportsExport(
                            filters: $data,
                            generatedBy: $generatedBy,
                            generatedAt: $generatedAt->format(
                                'd/m/Y H:i'
                            ),
                            documentNumber: $documentNumber,
                            documentStatus: 'draft',
                        ),
                        $fileName
                    );
                }),
        ];
    }
}
