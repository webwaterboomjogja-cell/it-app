<?php

namespace App\Services;

use App\Models\Itschedule;
use Carbon\Carbon;

class ItscheduleConflictChecker
{
    public function findConflict(array $data, ?int $ignoreId = null): ?Itschedule
    {
        if (
            empty($data['user_id']) ||
            empty($data['schedule_date']) ||
            empty($data['type'])
        ) {
            return null;
        }

        $type = $data['type'];
        $startTime = $data['start_time'] ?? null;
        $endTime = $data['end_time'] ?? null;

        $isAbsence = in_array($type, Itschedule::absenceTypes(), true);

        $query = Itschedule::query()
            ->with('user')
            ->where('user_id', $data['user_id'])
            ->whereDate('schedule_date', $data['schedule_date'])
            ->where('status', '!=', Itschedule::STATUS_CANCELLED)
            ->when($ignoreId, fn($query) => $query->whereKeyNot($ignoreId));

        /*
         * Cuti / DP dan Ijin dianggap full day.
         * Kalau ada jadwal apa pun di tanggal itu, dianggap bentrok.
         */
        if ($isAbsence) {
            return $query->first();
        }

        /*
         * Kalau Kerja/Maintenance tidak punya jam,
         * cek bentrok berdasarkan tanggal saja.
         */
        if (! $startTime || ! $endTime) {
            return $query->first();
        }

        return $query
            ->where(function ($query) use ($startTime, $endTime) {
                $query
                    ->whereIn('type', Itschedule::absenceTypes())
                    ->orWhere(function ($query) use ($startTime, $endTime) {
                        $query
                            ->whereNotNull('start_time')
                            ->whereNotNull('end_time')
                            ->where('start_time', '<', $endTime)
                            ->where('end_time', '>', $startTime);
                    });
            })
            ->first();
    }

    public function message(Itschedule $conflict): string
    {
        $date = Carbon::parse($conflict->schedule_date)->translatedFormat('d F Y');

        $time = 'Full day';

        if ($conflict->start_time && $conflict->end_time) {
            $time = Carbon::parse($conflict->start_time)->format('H:i') .
                ' - ' .
                Carbon::parse($conflict->end_time)->format('H:i');
        }

        return 'Jadwal bentrok. ' .
            ($conflict->user?->name ?? 'Staff ini') .
            ' sudah memiliki jadwal ' .
            $conflict->getTypeLabel() .
            ' pada ' .
            $date .
            ' jam ' .
            $time .
            '.';
    }
}
