<?php

namespace App\Filament\Resources\ItscheduletemplateResource\Pages;

use App\Filament\Resources\ItscheduletemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListItscheduletemplates extends ListRecords
{
    protected static string $resource = ItscheduletemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
