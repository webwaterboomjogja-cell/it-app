<x-filament-panels::page>
    <div class="space-y-6">
        @livewire(\App\Filament\Widgets\Inventory\AssetOverviewStats::class)

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            @livewire(\App\Filament\Widgets\Inventory\AssetStatusChart::class)

            @livewire(\App\Filament\Widgets\Inventory\AssetCategoryChart::class)
        </div>

        @livewire(\App\Filament\Widgets\Inventory\RecentAssetsTable::class)
    </div>
</x-filament-panels::page>
