<?php

namespace App\Filament\Resources\ScheduleTypeResource\Pages;

use App\Filament\Resources\ScheduleTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScheduleType extends EditRecord
{
    protected static string $resource = ScheduleTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
