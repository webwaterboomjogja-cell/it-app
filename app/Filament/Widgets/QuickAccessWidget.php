<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AssetCategoryResource;
use App\Filament\Resources\DivisionResource;
use App\Filament\Resources\LocationResource;
use App\Filament\Resources\ScheduleTypeResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\WorkCategoryResource;
use Filament\Widgets\Widget;

class QuickAccessWidget extends Widget
{
    protected static string $view = 'filament.widgets.quick-access-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

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
            'widget_QuickAccessWidget'
        );
    }

    protected function getViewData(): array
    {
        return [
            'menus' => [
                [
                    'title' => 'User',
                    'description' => 'Kelola akun pengguna sistem',
                    'icon' => 'heroicon-o-users',
                    'url' => UserResource::getUrl(),
                ],
                [
                    'title' => 'Divisi',
                    'description' => 'Kelola data divisi perusahaan',
                    'icon' => 'heroicon-o-building-office',
                    'url' => DivisionResource::getUrl(),
                ],
                [
                    'title' => 'Lokasi',
                    'description' => 'Kelola lokasi aset dan pekerjaan',
                    'icon' => 'heroicon-o-map-pin',
                    'url' => LocationResource::getUrl(),
                ],
                [
                    'title' => 'Kategori Aset',
                    'description' => 'Kelompokkan aset IT',
                    'icon' => 'heroicon-o-squares-2x2',
                    'url' => AssetCategoryResource::getUrl(),
                ],
                [
                    'title' => 'Kategori Pekerjaan',
                    'description' => 'Jenis pekerjaan IT',
                    'icon' => 'heroicon-o-wrench-screwdriver',
                    'url' => WorkCategoryResource::getUrl(),
                ],
                [
                    'title' => 'Jenis Jadwal',
                    'description' => 'Tipe jadwal maintenance',
                    'icon' => 'heroicon-o-calendar-days',
                    'url' => ScheduleTypeResource::getUrl(),
                ],
            ],
        ];
    }
}