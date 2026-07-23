<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItscheduleResource\Pages;

use App\Models\Itschedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Itscheduletemplate;


class ItscheduleResource extends Resource
{
    protected static ?string $model = Itschedule::class;

    protected static ?string $navigationGroup = 'Manajemen IT';

    protected static ?string $navigationLabel = 'Jadwal Tim IT';

    protected static ?string $modelLabel = 'Jadwal Tim IT';

    protected static ?string $pluralModelLabel = 'Jadwal Tim IT';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 5;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Jadwal')
                    ->description('Kelola jadwal kerja, piket, maintenance, event support, dan cuti/izin tim IT.')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Staff IT')
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn(Builder $query) => $query->orderBy('name')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\DatePicker::make('schedule_date')
                            ->label('Tanggal Jadwal')
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->required(),

                        Forms\Components\Select::make('type')
                            ->label('Jenis Jadwal')
                            ->options(Itschedule::typeOptions())
                            ->native(false)
                            ->live()
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Status Jadwal')
                            ->options(Itschedule::statusOptions())
                            ->default(Itschedule::STATUS_PLANNED)
                            ->native(false)
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Select::make('template_id')
                    ->label('Template Jadwal')
                    ->options(fn(): array => Itscheduletemplate::query()
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

                        $template = Itscheduletemplate::find($state);

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

                Forms\Components\Section::make('Waktu & Lokasi')
                    ->description('Isi jam kerja dan lokasi tugas sesuai kebutuhan.')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Forms\Components\TimePicker::make('start_time')
                            ->label('Jam Mulai')
                            ->seconds(false)
                            ->required(fn(Forms\Get $get): bool => ! in_array($get('type'), [
                                Itschedule::TYPE_LEAVE_DP,
                                Itschedule::TYPE_PERMISSION,
                            ])),

                        // Forms\Components\TimePicker::make('start_time')
                        //     ->label('Jam Mulai')
                        //     ->seconds(false)
                        //     ->required(fn(Forms\Get $get): bool => Itschedule::requiresTimeAndLocation($get('type'))),


                        Forms\Components\TimePicker::make('end_time')
                            ->label('Jam Selesai')
                            ->seconds(false)
                            ->rule('after:start_time')
                            ->required(fn(Forms\Get $get): bool => ! in_array($get('type'), [
                                Itschedule::TYPE_LEAVE_DP,
                                Itschedule::TYPE_PERMISSION,
                            ])),

                        // Forms\Components\TimePicker::make('end_time')
                        //     ->label('Jam Selesai')
                        //     ->seconds(false)
                        //     ->rule('after:start_time')
                        //     ->required(fn(Forms\Get $get): bool => Itschedule::requiresTimeAndLocation($get('type'))),

                        Forms\Components\TextInput::make('location')
                            ->label('Lokasi Tugas')
                            ->maxLength(255)
                            ->required(fn(Forms\Get $get): bool => ! in_array($get('type'), [
                                Itschedule::TYPE_LEAVE_DP,
                                Itschedule::TYPE_PERMISSION,
                            ])),

                        // Forms\Components\TextInput::make('location')
                        //     ->label('Lokasi Tugas')
                        //     ->maxLength(255)
                        //     ->required(fn(Forms\Get $get): bool => Itschedule::requiresTimeAndLocation($get('type'))),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->placeholder('Tambahkan detail tugas, agenda maintenance, atau keterangan tambahan.')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('schedule_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('schedule_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Staff IT')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => Itschedule::typeOptions()[$state] ?? $state)
                    ->color(fn(string $state): string => match ($state) {
                        Itschedule::TYPE_WORK => 'primary',
                        Itschedule::TYPE_MAINTENANCE => 'danger',
                        Itschedule::TYPE_LEAVE_DP => 'warning',
                        Itschedule::TYPE_PERMISSION => 'gray',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        Itschedule::TYPE_WORK => 'heroicon-o-briefcase',
                        Itschedule::TYPE_MAINTENANCE => 'heroicon-o-wrench-screwdriver',
                        Itschedule::TYPE_LEAVE_DP => 'heroicon-o-document-text',
                        Itschedule::TYPE_PERMISSION => 'heroicon-o-no-symbol',
                        default => 'heroicon-o-calendar-days',
                    }),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Mulai')
                    ->formatStateUsing(fn($state): string => $state ? date('H:i', strtotime($state)) : '-'),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('Selesai')
                    ->formatStateUsing(fn($state): string => $state ? date('H:i', strtotime($state)) : '-'),

                Tables\Columns\TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable()
                    ->placeholder('-')
                    ->limit(35),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => Itschedule::statusOptions()[$state] ?? $state)
                    ->color(fn(string $state): string => match ($state) {
                        Itschedule::STATUS_PLANNED => 'gray',
                        Itschedule::STATUS_IN_PROGRESS => 'warning',
                        Itschedule::STATUS_COMPLETED => 'success',
                        Itschedule::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Staff')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis Jadwal')
                    ->options(Itschedule::typeOptions()),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(Itschedule::statusOptions()),

                Tables\Filters\Filter::make('schedule_date')
                    ->label('Tanggal Jadwal')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal')
                            ->native(false),

                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('schedule_date', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('schedule_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),

                Tables\Actions\EditAction::make()
                    ->label('Edit'),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateHeading('Belum ada jadwal tim IT')
            ->emptyStateDescription('Tambahkan jadwal manual atau gunakan fitur generate jadwal massal.')
            ->emptyStateIcon('heroicon-o-calendar-days');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItschedules::route('/'),
            'create' => Pages\CreateItschedule::route('/create'),
            'edit' => Pages\EditItschedule::route('/{record}/edit'),
        ];
    }
}
