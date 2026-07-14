<?php

namespace App\Filament\Resources\ItassetmovementResource\Pages;

use App\Filament\Resources\ItassetmovementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditItassetmovement extends EditRecord
{
    protected static string $resource = ItassetmovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
