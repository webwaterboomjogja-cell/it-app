<?php

namespace App\Filament\Widgets\Inventory;

use App\Models\Itassests;
use Filament\Widgets\ChartWidget;

class AssetStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Distribusi Status Aset';

    protected static ?string $description = 'Perbandingan jumlah aset berdasarkan status.';

    protected static ?string $pollingInterval = null;

    protected int | string | array $columnSpan = 1;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole([
            'super_admin'
        ]) ?? false;
    }

    protected function getData(): array
    {
        $active = Itassests::where('status', 'aktif')->count();
        $maintenance = Itassests::where('status', 'maintenance')->count();
        $damaged = Itassests::where('status', 'rusak')->count();
        $inactive = Itassests::where('status', 'nonaktif')->count();
        $lost = Itassests::where('status', 'hilang')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Aset',
                    'data' => [
                        $active,
                        $maintenance,
                        $damaged,
                        $inactive,
                        $lost,
                    ],
                ],
            ],
            'labels' => [
                'Aktif',
                'Maintenance',
                'Rusak',
                'Nonaktif',
                'Hilang',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
