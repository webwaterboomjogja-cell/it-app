<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItscheduleTemplateResource\Pages;
use App\Models\Itschedule;
use App\Models\ItscheduleTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ItscheduletemplateResource extends Resource
{
    protected static ?string $model = ItscheduleTemplate::class;

    protected static ?string $navigationGroup = 'Manajemen IT';

    protected static ?string $navigationLabel = 'Template Jadwal';

    protected static ?string $modelLabel = 'Template Jadwal';

    protected static ?string $pluralModelLabel = 'Template Jadwal';

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Template')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Template')
                            ->placeholder('Contoh: Kerja Normal, Maintenance Server, Cuti / DP')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('type')
                            ->label('Jenis Jadwal')
                            ->options(Itschedule::typeOptions())
                            ->native(false)
                            ->live()
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Waktu & Lokasi Default')
                    ->schema([
                        Forms\Components\TimePicker::make('start_time')
                            ->label('Jam Mulai')
                            ->seconds(false)
                            ->required(fn (Forms\Get $get): bool => Itschedule::requiresTimeAndLocation($get('type'))),

                        Forms\Components\TimePicker::make('end_time')
                            ->label('Jam Selesai')
                            ->seconds(false)
                            ->rule('after:start_time')
                            ->required(fn (Forms\Get $get): bool => Itschedule::requiresTimeAndLocation($get('type'))),

                        Forms\Components\TextInput::make('location')
                            ->label('Lokasi Default')
                            ->placeholder('Contoh: Kantor IT, Server Room')
                            ->maxLength(255)
                            ->required(fn (Forms\Get $get): bool => Itschedule::requiresTimeAndLocation($get('type'))),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Default')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Template')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Itschedule::typeOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        Itschedule::TYPE_WORK => 'primary',
                        Itschedule::TYPE_MAINTENANCE => 'danger',
                        Itschedule::TYPE_LEAVE_DP => 'warning',
                        Itschedule::TYPE_PERMISSION => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Mulai')
                    ->formatStateUsing(fn ($state): string => $state ? date('H:i', strtotime($state)) : '-'),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('Selesai')
                    ->formatStateUsing(fn ($state): string => $state ? date('H:i', strtotime($state)) : '-'),

                Tables\Columns\TextColumn::make('location')
                    ->label('Lokasi')
                    ->placeholder('-')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis Jadwal')
                    ->options(Itschedule::typeOptions()),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItscheduleTemplates::route('/'),
            'create' => Pages\CreateItscheduleTemplate::route('/create'),
            'edit' => Pages\EditItscheduleTemplate::route('/{record}/edit'),
        ];
    }
}