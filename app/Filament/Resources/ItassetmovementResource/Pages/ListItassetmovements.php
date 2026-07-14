<?php

namespace App\Filament\Resources\ItassetmovementResource\Pages;

use App\Filament\Resources\ItassetmovementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListItassetmovements extends ListRecords
{
    protected static string $resource = ItassetmovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
