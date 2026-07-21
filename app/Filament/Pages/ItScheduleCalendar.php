<?php

namespace App\Filament\Pages;

use App\Models\Itschedule;
use App\Models\User;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class ItScheduleCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static string $view = 'filament.pages.it-schedule-calendar';

    protected static ?string $navigationGroup = 'Manajemen IT';

    protected static ?string $navigationLabel = 'Kalender Jadwal IT';

    protected static ?string $title = 'Kalender Jadwal Tim IT';

    protected static ?int $navigationSort = 6;

    public ?int $user_id = null;

    public string $month;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->can(
            'page_ItScheduleCalendar'
        );
    }

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function getUsersProperty(): Collection
    {
        return User::query()
            ->orderBy('name')
            ->get();
    }

    public function getSelectedUserProperty(): ?User
    {
        if (! $this->user_id) {
            return null;
        }

        return User::find($this->user_id);
    }

    public function getCalendarProperty(): array
    {
        $startOfMonth = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $this->month)->endOfMonth();

        $startCalendar = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $endCalendar = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        $schedules = Itschedule::query()
            ->with('user')
            ->when($this->user_id, fn($query) => $query->where('user_id', $this->user_id))
            ->whereBetween('schedule_date', [
                $startCalendar->toDateString(),
                $endCalendar->toDateString(),
            ])
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn($schedule) => $schedule->schedule_date->format('Y-m-d'));

        $weeks = [];
        $currentDate = $startCalendar->copy();

        while ($currentDate->lte($endCalendar)) {
            $week = [];

            for ($i = 0; $i < 7; $i++) {
                $dateKey = $currentDate->format('Y-m-d');

                $week[] = [
                    'date' => $currentDate->copy(),
                    'is_current_month' => $currentDate->month === $startOfMonth->month,
                    'is_today' => $currentDate->isToday(),
                    'schedules' => $schedules->get($dateKey, collect()),
                ];

                $currentDate->addDay();
            }

            $weeks[] = $week;
        }

        return $weeks;
    }

    public function getDownloadUrlProperty(): ?string
    {
        if (! $this->user_id) {
            return null;
        }

        return route('it-schedules.calendar.download', [
            'user_id' => $this->user_id,
            'month' => $this->month,
        ]);
    }
}
