<?php

namespace App\Filament\Resources\ItassetmaintenanceResource\Pages;

use App\Filament\Resources\ItassetmaintenanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditItassetmaintenance extends EditRecord
{
    protected static string $resource = ItassetmaintenanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
