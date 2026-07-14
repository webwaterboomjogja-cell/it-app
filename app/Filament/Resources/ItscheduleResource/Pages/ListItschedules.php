<?php

namespace App\Filament\Resources\ItscheduleResource\Pages;

use App\Filament\Resources\ItscheduleResource;
use App\Models\Itschedule;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

use App\Models\User;
use App\Services\ItTeamScheduleGenerator;
use Filament\Forms;
use Filament\Notifications\Notification;

use App\Models\ItscheduleTemplate;


class ListItschedules extends ListRecords
{
    protected static string $resource = ItscheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generateSchedules')
                ->label('Generate Jadwal')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->modalHeading('Generate Jadwal Tim IT')
                ->modalDescription('Buat jadwal kerja atau piket untuk banyak staff sekaligus dalam satu periode.')
                ->modalSubmitActionLabel('Generate Sekarang')
                ->modalWidth('4xl')
                ->form([
                    Forms\Components\Section::make('Periode Jadwal')
                        ->description('Pilih rentang tanggal dan hari kerja yang akan dibuatkan jadwal.')
                        ->schema([
                            Forms\Components\DatePicker::make('start_date')
                                ->label('Tanggal Mulai')
                                ->native(false)
                                ->default(now()->startOfMonth())
                                ->required(),

                            Forms\Components\DatePicker::make('end_date')
                                ->label('Tanggal Selesai')
                                ->native(false)
                                ->default(now()->endOfMonth())
                                ->rule('after_or_equal:start_date')
                                ->required(),

                            Forms\Components\CheckboxList::make('work_days')
                                ->label('Hari yang Dijadwalkan')
                                ->options([
                                    1 => 'Senin',
                                    2 => 'Selasa',
                                    3 => 'Rabu',
                                    4 => 'Kamis',
                                    5 => 'Jumat',
                                    6 => 'Sabtu',
                                    7 => 'Minggu',
                                ])
                                ->default([1, 2, 3, 4, 5])
                                ->columns(4)
                                ->required()
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                    Forms\Components\Section::make('Staff & Jenis Jadwal')
                        ->description('Pilih staff IT dan jenis jadwal yang akan dibuat.')
                        ->schema([
                            Forms\Components\Select::make('template_id')
                                ->label('Template Jadwal')
                                ->options(fn(): array => ItscheduleTemplate::query()
                                    ->where('is_active', true)
                                    ->orderBy('sort_order')
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray())
                                ->searchable()
                                ->preload()
                                ->live()
                                ->dehydrated(false)
                                ->helperText('Opsional. Pilih template untuk mengisi jenis, jam, lokasi, dan catatan otomatis.')
                                ->afterStateUpdated(function ($state, Forms\Set $set): void {
                                    if (! $state) {
                                        return;
                                    }

                                    $template = ItscheduleTemplate::find($state);

                                    if (! $template) {
                                        return;
                                    }

                                    $set('type', $template->type);
                                    $set('start_time', $template->start_time ? substr($template->start_time, 0, 5) : null);
                                    $set('end_time', $template->end_time ? substr($template->end_time, 0, 5) : null);
                                    $set('location', $template->location);
                                    $set('notes', $template->notes);
                                })
                                ->columnSpanFull(),

                            Forms\Components\Select::make('staff_ids')
                                ->label('Staff IT')
                                ->options(fn(): array => User::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray())
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->required(),

                            Forms\Components\Select::make('type')
                                ->label('Jenis Jadwal')
                                ->options(Itschedule::typeOptions())
                                ->default(Itschedule::TYPE_WORK)
                                ->native(false)
                                ->required(),

                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options(Itschedule::statusOptions())
                                ->default(Itschedule::STATUS_PLANNED)
                                ->native(false)
                                ->required(),
                        ])
                        ->columns(2),

                    Forms\Components\Section::make('Waktu & Lokasi')
                        ->description('Isi jam kerja dan lokasi default untuk jadwal yang digenerate.')
                        ->schema([
                            Forms\Components\TimePicker::make('start_time')
                                ->label('Jam Mulai')
                                ->seconds(false)
                                ->default('08:00')
                                ->required(),

                            Forms\Components\TimePicker::make('end_time')
                                ->label('Jam Selesai')
                                ->seconds(false)
                                ->default('17:00')
                                ->rule('after:start_time')
                                ->required(),

                            Forms\Components\TextInput::make('location')
                                ->label('Lokasi')
                                ->default('Kantor IT')
                                ->maxLength(255)
                                ->required(),

                            Forms\Components\Textarea::make('notes')
                                ->label('Catatan')
                                ->placeholder('Contoh: Jadwal kerja regular bulan ini.')
                                ->rows(3)
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                    Forms\Components\Section::make('Pengaturan Duplikasi')
                        ->description('Atur apakah jadwal lama yang sudah ada akan dilewati atau diperbarui.')
                        ->schema([
                            Forms\Components\Toggle::make('skip_existing')
                                ->label('Lewati jadwal yang sudah ada')
                                ->helperText('Aktifkan agar sistem tidak membuat atau menimpa jadwal yang sudah pernah dibuat.')
                                ->default(true),
                        ]),
                ])
                ->action(function (array $data): void {
                    $result = app(ItTeamScheduleGenerator::class)->generate($data);

                    Notification::make()
                        ->title('Generate jadwal selesai')
                        ->body(
                            'Dibuat: ' . $result['created'] .
                                ', Diupdate: ' . $result['updated'] .
                                ', Dilewati: ' . $result['skipped'] .
                                ', Bentrok: ' . $result['conflicts']
                        )
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make()
                ->label('Tambah Jadwal Manual')
                ->icon('heroicon-o-plus'),
        ];
    }
}
