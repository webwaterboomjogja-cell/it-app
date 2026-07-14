<?php

namespace App\Filament\Resources\ItassetmaintenanceResource\Pages;

use App\Filament\Resources\ItassetmaintenanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListItassetmaintenances extends ListRecords
{
    protected static string $resource = ItassetmaintenanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
