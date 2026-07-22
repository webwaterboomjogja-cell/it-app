<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashboardHeroWidget extends Widget
{
    protected static string $view =
        'filament.widgets.dashboard-hero-widget';

    protected static ?int $sort = 1;

    /**
     * Membuat widget memenuhi seluruh kolom dashboard.
     */
    protected int|string|array $columnSpan = 'full';

    /**
     * Memastikan column span tetap full meskipun
     * konfigurasi grid dashboard berubah.
     */
    public function getColumnSpan(): int|string|array
    {
        return 'full';
    }

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
            'widget_DashboardHeroWidget'
        );
    }
}