<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class InventoryDashboard extends Page
{
    protected static ?string $navigationGroup = 'Inventaris IT';

    protected static ?string $navigationLabel = 'Dashboard Inventaris';

    protected static ?string $title = 'Dashboard Inventaris IT';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.inventory-dashboard';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->can(
            'page_ItScheduleCalendar'
        );
    }
}
