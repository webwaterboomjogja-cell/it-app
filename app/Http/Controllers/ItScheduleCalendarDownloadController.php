<?php

namespace App\Http\Controllers;

use App\Models\Itschedule;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ItScheduleCalendarDownloadController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $user = User::findOrFail($data['user_id']);

        $startOfMonth = Carbon::createFromFormat('Y-m', $data['month'])->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $data['month'])->endOfMonth();

        $startCalendar = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $endCalendar = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        $schedules = Itschedule::query()
            ->where('user_id', $user->id)
            ->whereBetween('schedule_date', [
                $startCalendar->toDateString(),
                $endCalendar->toDateString(),
            ])
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn ($schedule) => $schedule->schedule_date->format('Y-m-d'));

        $weeks = [];
        $currentDate = $startCalendar->copy();

        while ($currentDate->lte($endCalendar)) {
            $week = [];

            for ($i = 0; $i < 7; $i++) {
                $dateKey = $currentDate->format('Y-m-d');

                $week[] = [
                    'date' => $currentDate->copy(),
                    'is_current_month' => $currentDate->month === $startOfMonth->month,
                    'schedules' => $schedules->get($dateKey, collect()),
                ];

                $currentDate->addDay();
            }

            $weeks[] = $week;
        }

        $pdf = Pdf::loadView('pdf.it-schedule-calendar', [
            'user' => $user,
            'month' => $startOfMonth,
            'weeks' => $weeks,
        ])->setPaper('a4', 'landscape');

        $fileName = 'jadwal-it-' .
            str($user->name)->slug() .
            '-' .
            $startOfMonth->format('Y-m') .
            '.pdf';

        return $pdf->download($fileName);
    }
}