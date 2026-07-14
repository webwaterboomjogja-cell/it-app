<?php

namespace App\Filament\Resources\ItassetsResource\Pages;

use App\Filament\Resources\ItassetsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditItassets extends EditRecord
{
    protected static string $resource = ItassetsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
