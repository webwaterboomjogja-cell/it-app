<?php

namespace App\Filament\Resources\ReportSignatoryResource\Pages;

use App\Filament\Resources\ReportSignatoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReportSignatories extends ListRecords
{
    protected static string $resource = ReportSignatoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
