<?php

namespace App\Filament\Resources\ReportSignatoryResource\Pages;

use App\Filament\Resources\ReportSignatoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReportSignatory extends EditRecord
{
    protected static string $resource = ReportSignatoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
