<?php

namespace App\Filament\Resources\ExportHistoryResource\Pages;

use App\Filament\Resources\ExportHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExportHistory extends EditRecord
{
    protected static string $resource = ExportHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
