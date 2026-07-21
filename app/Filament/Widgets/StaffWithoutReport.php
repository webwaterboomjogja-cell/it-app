<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class StaffWithoutReport extends BaseWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading =
    'Staff Belum Membuat Laporan Hari Ini';

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole([
            'super_admin'
        ]) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->missingTodayReport()
                    ->with([
                        'roles',
                        'itSchedules' => function ($query): void {
                            $query
                                ->whereDate('schedule_date', today())
                                ->whereIn('type', [
                                    'kerja',
                                    'maintenance',
                                ])
                                ->orderBy('start_time');
                        },
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Staff')
                    ->searchable()
                    ->weight('semibold')
                    ->icon('heroicon-m-user'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('today_schedule_type')
                    ->label('Jadwal')
                    ->getStateUsing(function (User $record): string {
                        $type = $record->itSchedules->first()?->type;

                        return match ($type) {
                            'kerja' => 'Kerja',
                            'maintenance' => 'Maintenance',
                            'cuti_dp' => 'Cuti / DP',
                            'ijin' => 'Ijin',
                            default => '-',
                        };
                    })
                    ->badge()
                    ->color(function (User $record): string {
                        return match ($record->itSchedules->first()?->type) {
                            'kerja' => 'success',
                            'maintenance' => 'warning',
                            'cuti_dp' => 'info',
                            'ijin' => 'gray',
                            default => 'gray',
                        };
                    }),

                Tables\Columns\TextColumn::make('today_schedule_time')
                    ->label('Jam Jadwal')
                    ->getStateUsing(function (User $record): string {
                        $schedule = $record->itSchedules->first();

                        if (!$schedule) {
                            return '-';
                        }

                        $startTime = $schedule->start_time
                            ? substr((string) $schedule->start_time, 0, 5)
                            : '-';

                        $endTime = $schedule->end_time
                            ? substr((string) $schedule->end_time, 0, 5)
                            : '-';

                        return "{$startTime} - {$endTime}";
                    }),

                Tables\Columns\TextColumn::make('status_laporan')
                    ->label('Status Laporan')
                    ->getStateUsing(fn(): string => 'Belum Laporan')
                    ->badge()
                    ->color('danger')
                    ->icon('heroicon-m-exclamation-circle'),
            ])
            ->defaultSort('name')
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Semua staff sudah membuat laporan')
            ->emptyStateDescription(
                'Tidak ada staff terjadwal yang belum membuat laporan hari ini.'
            )
            ->emptyStateIcon('heroicon-o-check-circle')
            ->striped();
    }
}
