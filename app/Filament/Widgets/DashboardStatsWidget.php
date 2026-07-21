<?php

namespace App\Filament\Widgets;

use App\Models\Assetcategory;
use App\Models\Devisions;

use App\Models\Locations;
use App\Models\Scheduletype;
use App\Models\User;
use App\Models\Workcategory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

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
            'widget_DashboardStatsWidget'
        );
    }

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

            Stat::make('Kategori Aset', Assetcategory::count())
                ->description('Pengelompokan aset IT')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('info'),

            Stat::make('Kategori Pekerjaan', Workcategory::count())
                ->description('Jenis pekerjaan IT')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('danger'),

            Stat::make('Jenis Jadwal', Scheduletype::count())
                ->description('Tipe jadwal maintenance')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('gray'),
        ];
    }
}
