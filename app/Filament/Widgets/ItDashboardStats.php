<?php

namespace App\Filament\Widgets;


use App\Models\Dailyreport;
use App\Models\Itassests;
use App\Models\Itschedule;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ItDashboardStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '60s';

    protected ?string $heading = 'Ringkasan Kondisi Divisi IT';

    protected ?string $description =
    'Monitoring aset, jadwal, dan laporan pekerjaan tim IT.';

    protected function getStats(): array
    {
        $totalAssets = Itassests::query()->count();

        $activeAssets = Itassests::query()
            ->where('status', 'aktif')
            ->count();

        $damagedAssets = Itassests::query()
            ->where('status', 'rusak')
            ->count();

        $maintenanceAssets = Itassests::query()
            ->where('status', 'maintenance')
            ->count();

        $problematicAssets = Itassests::query()
            ->problematic()
            ->count();

        $todayReports = Dailyreport::query()
            ->whereDate('report_date', today())
            ->count();

        $todaySchedules = Itschedule::query()
            ->whereDate('schedule_date', today())
            ->count();

        $monthlyReports = Dailyreport::query()
            ->whereYear('report_date', now()->year)
            ->whereMonth('report_date', now()->month)
            ->count();

        $staffWithoutReport = User::query()
            ->missingTodayReport()
            ->count();

        return [
            Stat::make(
                'Total Aset',
                $this->formatNumber($totalAssets)
            )
                ->description('Seluruh aset IT yang tercatat')
                ->descriptionIcon('heroicon-m-computer-desktop')
                ->color('primary'),

            Stat::make(
                'Aset Aktif',
                $this->formatNumber($activeAssets)
            )
                ->description('Aset dalam kondisi aktif')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(
                'Aset Rusak',
                $this->formatNumber($damagedAssets)
            )
                ->description(
                    $damagedAssets > 0
                        ? 'Memerlukan penanganan'
                        : 'Tidak ada aset rusak'
                )
                ->descriptionIcon(
                    $damagedAssets > 0
                        ? 'heroicon-m-exclamation-triangle'
                        : 'heroicon-m-check-circle'
                )
                ->color(
                    $damagedAssets > 0
                        ? 'danger'
                        : 'success'
                ),

            Stat::make(
                'Aset Maintenance',
                $this->formatNumber($maintenanceAssets)
            )
                ->description('Aset dalam proses maintenance')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color(
                    $maintenanceAssets > 0
                        ? 'warning'
                        : 'success'
                ),

            Stat::make(
                'Laporan Hari Ini',
                $this->formatNumber($todayReports)
            )
                ->description(today()->format('d/m/Y'))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make(
                'Jadwal Hari Ini',
                $this->formatNumber($todaySchedules)
            )
                ->description('Seluruh jadwal tim IT hari ini')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make(
                'Laporan Bulan Ini',
                $this->formatNumber($monthlyReports)
            )
                ->description(now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),

            Stat::make(
                'Staff Belum Laporan',
                $this->formatNumber($staffWithoutReport)
            )
                ->description(
                    $staffWithoutReport > 0
                        ? 'Perlu diingatkan'
                        : 'Semua staff sudah melapor'
                )
                ->descriptionIcon(
                    $staffWithoutReport > 0
                        ? 'heroicon-m-user-minus'
                        : 'heroicon-m-user-group'
                )
                ->color(
                    $staffWithoutReport > 0
                        ? 'warning'
                        : 'success'
                ),

            Stat::make(
                'Aset Bermasalah',
                $this->formatNumber($problematicAssets)
            )
                ->description('Aset rusak dan maintenance')
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->color(
                    $problematicAssets > 0
                        ? 'danger'
                        : 'success'
                ),
        ];
    }

    private function formatNumber(int $value): string
    {
        return number_format(
            num: $value,
            decimals: 0,
            decimal_separator: ',',
            thousands_separator: '.',
        );
    }
}
