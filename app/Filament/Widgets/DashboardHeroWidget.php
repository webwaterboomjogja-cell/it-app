<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashboardHeroWidget extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-hero-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;
}
