<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active === true;
    }

    public function itTeamSchedules()
    {
        return $this->hasMany(Itschedule::class, 'user_id');
    }

    public function itSchedules(): HasMany
    {
        return $this->hasMany(ItSchedule::class, 'user_id');
    }


    public function dailyReports(): HasMany
    {
        return $this->hasMany(Dailyreport::class);
    }

    public function scopeMissingTodayReport(Builder $query): Builder
    {
        return $query
            ->role('staff_it')
            ->where('is_active', true)
            ->whereHas('itTeamSchedules', function (Builder $scheduleQuery): void {
                $scheduleQuery
                    ->whereDate('schedule_date', today())
                    ->whereIn('type', [
                        'kerja',
                        'maintenance',
                    ]);
            })
            ->whereDoesntHave(
                'dailyReports',
                function (Builder $reportQuery): void {
                    $reportQuery->whereDate('report_date', today());
                }
            );
    }
}
