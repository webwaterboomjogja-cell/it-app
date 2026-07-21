<?php

namespace App\Filament\Widgets\Inventory;

use App\Models\Itassests;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AssetCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Aset Berdasarkan Kategori';

    protected static ?string $description = 'Menampilkan jumlah aset dari setiap kategori.';

    protected static ?string $pollingInterval = null;

    protected int | string | array $columnSpan = 2;

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
            'widget_AssetCategoryChart'
        );
    }

    protected function getData(): array
    {
        $records = Itassests::query()
            ->select('asset_category_id', DB::raw('COUNT(*) as total'))
            ->with('category')
            ->groupBy('asset_category_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Aset',
                    'data' => $records->pluck('total')->toArray(),
                ],
            ],
            'labels' => $records
                ->map(fn($record) => $record->category?->name ?? 'Tanpa Kategori')
                ->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
