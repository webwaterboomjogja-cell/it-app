<?php

namespace App\Filament\Resources\ItscheduleResource\Pages;

use App\Filament\Resources\ItscheduleResource;
use App\Services\ItscheduleConflictChecker;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateItschedule extends CreateRecord
{
    protected static string $resource = ItscheduleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $checker = app(ItscheduleConflictChecker::class);

        $conflict = $checker->findConflict($data);

        if ($conflict) {
            throw ValidationException::withMessages([
                'data.schedule_date' => $checker->message($conflict),
            ]);
        }

        return $data;
    }
}
