<?php

namespace App\Services;

use App\Models\Itschedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ItTeamScheduleGenerator
{
    public function generate(array $data): array
    {
        $staffIds = $data['staff_ids'] ?? [];
        $workDays = $data['work_days'] ?? [];

        $startDate = \Carbon\Carbon::parse($data['start_date'])->startOfDay();
        $endDate = \Carbon\Carbon::parse($data['end_date'])->startOfDay();

        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $conflicts = 0;

        $checker = app(\App\Services\ItscheduleConflictChecker::class);

        foreach ($period as $date) {
            if (! in_array($date->isoWeekday(), $workDays)) {
                continue;
            }

            foreach ($staffIds as $staffId) {
                $values = [
                    'user_id' => $staffId,
                    'schedule_date' => $date->toDateString(),
                    'start_time' => $data['start_time'] ?? null,
                    'end_time' => $data['end_time'] ?? null,
                    'type' => $data['type'],
                    'location' => $data['location'] ?? null,
                    'status' => $data['status'] ?? \App\Models\Itschedule::STATUS_PLANNED,
                    'notes' => $data['notes'] ?? null,
                ];

                $existingSchedule = \App\Models\Itschedule::query()
                    ->where('user_id', $staffId)
                    ->whereDate('schedule_date', $date->toDateString())
                    ->where('type', $data['type'])
                    ->first();

                if ($existingSchedule && ($data['skip_existing'] ?? true)) {
                    $skipped++;
                    continue;
                }

                if ($existingSchedule) {
                    $conflict = $checker->findConflict($values, $existingSchedule->id);

                    if ($conflict) {
                        $conflicts++;
                        continue;
                    }

                    $existingSchedule->update($values);
                    $updated++;
                    continue;
                }

                $conflict = $checker->findConflict($values);

                if ($conflict) {
                    $conflicts++;
                    continue;
                }

                \App\Models\Itschedule::create($values);

                $created++;
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'conflicts' => $conflicts,
        ];
    }
}
