<?php

namespace App\Filament\Resources\DailyReportResource\Pages;

use App\Filament\Resources\DailyReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDailyReport extends EditRecord
{
    protected static string $resource = DailyReportResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! auth()->user()?->hasAnyRole(['super_admin', 'kepala_it'])) {
            unset($data['review_note']);
            unset($data['reviewed_by']);
            unset($data['reviewed_at']);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () =>
                    auth()->user()?->hasAnyRole(['super_admin', 'kepala_it'])
                    || $this->record->review_status === 'draft'
                ),
        ];
    }
}