<?php

namespace App\Filament\Widgets\Inventory;

use App\Models\Itassests;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AssetOverviewStats extends BaseWidget
{
    protected static ?string $pollingInterval = null;

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
            'widget_AssetOverviewStats'
        );
    }

    protected function getStats(): array
    {
        $totalAssets = Itassests::count();

        $activeAssets = Itassests::where('status', 'aktif')->count();

        $maintenanceAssets = Itassests::where('status', 'maintenance')->count();

        $damagedAssets = Itassests::where('status', 'rusak')->count();

        $lostAssets = Itassests::where('status', 'hilang')->count();

        $inactiveAssets = Itassests::where('status', 'nonaktif')->count();

        return [
            Stat::make('Total Aset', $totalAssets)
                ->description('Seluruh aset IT tercatat')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('primary'),

            Stat::make('Aset Aktif', $activeAssets)
                ->description('Aset siap digunakan')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Maintenance', $maintenanceAssets)
                ->description('Aset dalam perbaikan')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('warning'),

            Stat::make('Rusak / Hilang', $damagedAssets + $lostAssets)
                ->description("Rusak: {$damagedAssets} • Hilang: {$lostAssets}")
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('Nonaktif', $inactiveAssets)
                ->description('Aset tidak digunakan')
                ->descriptionIcon('heroicon-m-no-symbol')
                ->color('gray'),
        ];
    }
}
