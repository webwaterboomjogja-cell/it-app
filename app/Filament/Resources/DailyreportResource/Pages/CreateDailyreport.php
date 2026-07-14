<?php

namespace App\Filament\Resources\DailyReportResource\Pages;

use App\Filament\Resources\DailyReportResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDailyReport extends CreateRecord
{
    protected static string $resource = DailyReportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! auth()->user()?->hasAnyRole(['super_admin', 'kepala_it'])) {
            $data['user_id'] = auth()->id();
        }

        return $data;
    }
}