<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LocationResource\Pages;
use App\Models\Locations;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LocationResource extends Resource
{
    protected static ?string $model = Locations::class;

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Lokasi';

    protected static ?string $modelLabel = 'Lokasi';

    protected static ?string $pluralModelLabel = 'Lokasi';

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        return (string) Locations::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'info';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Lokasi')
                    ->description('Kelola data lokasi yang digunakan untuk penempatan aset IT, laporan pekerjaan, dan jadwal maintenance.')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Lokasi')
                                    ->placeholder('Contoh: Ruang IT')
                                    ->required()
                                    ->maxLength(255)
                                    ->autofocus()
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 2,
                                    ]),

                                Forms\Components\TextInput::make('code')
                                    ->label('Kode Lokasi')
                                    ->placeholder('Contoh: ITROOM')
                                    ->maxLength(50)
                                    ->unique(ignoreRecord: true)
                                    ->dehydrateStateUsing(fn(?string $state): ?string => $state ? strtoupper(trim($state)) : null)
                                    ->helperText('Kode lokasi sebaiknya singkat dan unik.'),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Urutan')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Semakin kecil angka, semakin atas posisinya.'),

                                Forms\Components\Textarea::make('address')
                                    ->label('Alamat / Detail Lokasi')
                                    ->placeholder('Contoh: Gedung utama lantai 2, dekat ruang server.')
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->placeholder('Tulis keterangan tambahan mengenai lokasi ini.')
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
                    ->description('Atur apakah lokasi ini masih aktif digunakan di sistem.')
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->helperText('Aktifkan jika lokasi ini masih dapat dipilih pada modul aset, pekerjaan, atau jadwal.')
                            ->default(true)
                            ->inline(false)
                            ->onColor('success')
                            ->offColor('danger'),
                    ])
                    ->collapsible(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Daftar Lokasi')
            ->description('Data lokasi digunakan sebagai referensi pada modul inventaris aset, laporan pekerjaan, dan jadwal maintenance.')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lokasi')
                    ->description(fn(Locations $record): ?string => $record->address)
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-map-pin')
                    ->iconColor('info'),

                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->color('gray')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Kode lokasi berhasil disalin')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(45)
                    ->placeholder('-')
                    ->toggleable(),

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
            ->emptyStateIcon('heroicon-o-map-pin')
            ->emptyStateHeading('Belum ada data lokasi')
            ->emptyStateDescription('Tambahkan lokasi pertama agar dapat digunakan pada modul inventaris aset, laporan pekerjaan, dan jadwal maintenance.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Lokasi')
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
            'index' => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'edit' => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
