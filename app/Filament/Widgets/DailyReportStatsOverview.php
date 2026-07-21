<?php

namespace App\Filament\Widgets;

use App\Models\Dailyreport;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DailyReportStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole([
            'super_admin'
        ]) ?? false;
    }

    protected function getStats(): array
    {
        $totalToday = Dailyreport::query()
            ->whereDate('report_date', today())
            ->count();

        $unreviewedReports = Dailyreport::query()
            ->where('review_status', 'dikirim')
            ->count();

        $pendingWorks = Dailyreport::query()
            ->where('work_status', 'tertunda')
            ->count();

        $urgentWorks = Dailyreport::query()
            ->where('priority', 'urgent')
            ->whereIn('work_status', ['proses', 'tertunda'])
            ->count();

        $completedThisMonth = Dailyreport::query()
            ->where('work_status', 'selesai')
            ->whereMonth('report_date', now()->month)
            ->whereYear('report_date', now()->year)
            ->count();

        return [
            Stat::make('Laporan Hari Ini', $totalToday)
                ->description('Total laporan yang dibuat hari ini')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Belum Direview', $unreviewedReports)
                ->description('Laporan berstatus dikirim')
                ->descriptionIcon('heroicon-m-clock')
                ->color($unreviewedReports > 0 ? 'warning' : 'success'),

            Stat::make('Pekerjaan Tertunda', $pendingWorks)
                ->description('Pekerjaan yang belum selesai')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($pendingWorks > 0 ? 'danger' : 'success'),

            Stat::make('Pekerjaan Urgent', $urgentWorks)
                ->description('Urgent dan masih proses/tertunda')
                ->descriptionIcon('heroicon-m-bolt')
                ->color($urgentWorks > 0 ? 'danger' : 'gray'),

            Stat::make('Selesai Bulan Ini', $completedThisMonth)
                ->description('Total pekerjaan selesai bulan berjalan')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
