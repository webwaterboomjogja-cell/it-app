<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleTypeResource\Pages;
use App\Models\ScheduleType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ScheduleTypeResource extends Resource
{
    protected static ?string $model = ScheduleType::class;

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Jenis Jadwal';

    protected static ?string $modelLabel = 'Jenis Jadwal';

    protected static ?string $pluralModelLabel = 'Jenis Jadwal';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        return (string) ScheduleType::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Jenis Jadwal')
                    ->description('Kelola jenis jadwal yang digunakan untuk agenda maintenance, pekerjaan rutin, dan jadwal insidental IT.')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Jenis Jadwal')
                                    ->placeholder('Contoh: Harian, Mingguan, Bulanan')
                                    ->required()
                                    ->maxLength(255)
                                    ->autofocus()
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 2,
                                    ]),

                                Forms\Components\TextInput::make('code')
                                    ->label('Kode Jadwal')
                                    ->placeholder('Contoh: DAILY, WEEKLY, MONTHLY')
                                    ->maxLength(50)
                                    ->unique(ignoreRecord: true)
                                    ->dehydrateStateUsing(fn(?string $state): ?string => $state ? strtoupper(trim($state)) : null)
                                    ->helperText('Kode sebaiknya singkat dan unik.'),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Urutan')
                                    ->numeric()
                                    ->required()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Semakin kecil angka, semakin atas posisinya.'),

                                Forms\Components\ColorPicker::make('color')
                                    ->label('Warna Jadwal')
                                    ->placeholder('#2563eb')
                                    ->helperText('Warna ini bisa digunakan sebagai penanda jadwal di kalender atau tabel.')
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 2,
                                    ]),

                                Forms\Components\Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->placeholder('Tulis keterangan singkat mengenai jenis jadwal ini.')
                                    ->rows(4)
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                            ])
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                            ]),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Status Data')
                    ->description('Atur apakah jenis jadwal ini masih aktif digunakan di sistem.')
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->helperText('Aktifkan jika jenis jadwal ini masih dapat dipilih pada modul jadwal maintenance.')
                            ->default(true)
                            ->inline(false)
                            ->onColor('success')
                            ->offColor('danger')
                            ->required(),
                    ])
                    ->collapsible(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Daftar Jenis Jadwal')
            ->description('Data jenis jadwal digunakan sebagai referensi untuk modul jadwal maintenance dan pekerjaan rutin IT.')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Jadwal')
                    ->description(fn(ScheduleType $record): ?string => $record->description)
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-calendar-days')
                    ->iconColor('warning'),

                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->color('gray')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Kode jadwal berhasil disalin')
                    ->copyMessageDuration(1500),

                Tables\Columns\ColorColumn::make('color')
                    ->label('Warna')
                    ->copyable()
                    ->copyMessage('Kode warna berhasil disalin')
                    ->copyMessageDuration(1500)
                    ->placeholder('-'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diupdate')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning'),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->emptyStateHeading('Belum ada jenis jadwal')
            ->emptyStateDescription('Tambahkan jenis jadwal pertama agar bisa digunakan pada modul jadwal maintenance.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Jenis Jadwal')
                    ->icon('heroicon-o-plus'),
            ])
            ->defaultSort('sort_order', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
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
            'index' => Pages\ListScheduleTypes::route('/'),
            'create' => Pages\CreateScheduleType::route('/create'),
            'edit' => Pages\EditScheduleType::route('/{record}/edit'),
        ];
    }
}
