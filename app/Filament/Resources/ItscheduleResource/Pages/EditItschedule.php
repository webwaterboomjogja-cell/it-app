<?php

namespace App\Filament\Resources\ItscheduleResource\Pages;

use App\Filament\Resources\ItscheduleResource;
use App\Services\ItscheduleConflictChecker;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditItschedule extends EditRecord
{
    protected static string $resource = ItscheduleResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $checker = app(ItscheduleConflictChecker::class);

        $conflict = $checker->findConflict($data, $this->record->id);

        if ($conflict) {
            throw ValidationException::withMessages([
                'data.schedule_date' => $checker->message($conflict),
            ]);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat'),

            Actions\DeleteAction::make()
                ->label('Hapus'),
        ];
    }
}
