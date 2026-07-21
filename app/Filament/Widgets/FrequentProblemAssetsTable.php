<?php

namespace App\Filament\Widgets;

use App\Models\Dailyreport;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class FrequentProblemAssetsTable extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Aset Sering Bermasalah - 30 Hari Terakhir';

    public static function canView(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->can(
            'widget_FrequentProblemAssetsTable'
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('asset.name')
                    ->label('Aset')
                    ->searchable()
                    ->description(fn(Dailyreport $record): ?string => data_get($record->asset, 'code')),

                Tables\Columns\TextColumn::make('total_reports')
                    ->label('Total Laporan')
                    ->badge()
                    ->sortable()
                    ->color('info'),

                Tables\Columns\TextColumn::make('total_tertunda')
                    ->label('Tertunda')
                    ->badge()
                    ->sortable()
                    ->color(fn($state): string => $state > 0 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('total_urgent')
                    ->label('Urgent')
                    ->badge()
                    ->sortable()
                    ->color(fn($state): string => $state > 0 ? 'danger' : 'gray'),

                Tables\Columns\TextColumn::make('last_report_date')
                    ->label('Terakhir Dilaporkan')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('total_reports', 'desc')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Belum ada laporan aset')
            ->emptyStateDescription('Belum ada aset yang tercatat di laporan harian dalam 30 hari terakhir.');
    }

    protected function getTableQuery(): Builder
    {
        return Dailyreport::query()
            ->select([
                'itassets_id',
                DB::raw('MAX(id) as id'),
                DB::raw('COUNT(*) as total_reports'),
                DB::raw("SUM(CASE WHEN work_status = 'tertunda' THEN 1 ELSE 0 END) as total_tertunda"),
                DB::raw("SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as total_urgent"),
                DB::raw('MAX(report_date) as last_report_date'),
            ])
            ->with('asset')
            ->whereNotNull('itassets_id')
            ->whereDate('report_date', '>=', now()->subDays(30))
            ->groupBy('itassets_id');
    }
}
