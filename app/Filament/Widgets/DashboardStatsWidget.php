<?php

namespace App\Filament\Widgets;

use App\Models\AssetCategory;
use App\Models\Devisions;
use App\Models\Division;
use App\Models\Location;
use App\Models\Locations;
use App\Models\ScheduleType;
use App\Models\User;
use App\Models\WorkCategory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        return [
            Stat::make('Total User', User::count())
                ->description('Akun pengguna sistem')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Divisi', Devisions::count())
                ->description('Data divisi perusahaan')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('success'),

            Stat::make('Lokasi', Locations::count())
                ->description('Data lokasi aset')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('warning'),

            Stat::make('Kategori Aset', AssetCategory::count())
                ->description('Pengelompokan aset IT')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('info'),

            Stat::make('Kategori Pekerjaan', WorkCategory::count())
                ->description('Jenis pekerjaan IT')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('danger'),

            Stat::make('Jenis Jadwal', ScheduleType::count())
                ->description('Tipe jadwal maintenance')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('gray'),
        ];
    }
}