<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItassetMovementResource\Pages;
use App\Models\Itassests;
use App\Models\Itassetmovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ItassetMovementResource extends Resource
{
    protected static ?string $model = Itassetmovement::class;

    protected static ?string $navigationGroup = 'Inventaris IT';

    protected static ?string $navigationLabel = 'Mutasi Aset';

    protected static ?string $modelLabel = 'Mutasi Aset';

    protected static ?string $pluralModelLabel = 'Mutasi Aset';

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Mutasi Aset')
                    ->description('Catat perpindahan lokasi, pergantian PIC, serah terima, atau penarikan aset.')
                    ->icon('heroicon-o-arrows-right-left')
                    ->schema([
                        Forms\Components\Select::make('itasset_id')
                            ->label('Aset IT')
                            ->relationship('asset', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?int $state) {
                                $asset = Itassests::find($state);

                                $set('from_location_id', $asset?->location_id);
                                $set('from_user_id', $asset?->responsible_user_id);
                            }),

                        Forms\Components\DatePicker::make('moved_at')
                            ->label('Tanggal Mutasi')
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->default(now())
                            ->required(),

                        Forms\Components\Select::make('type')
                            ->label('Jenis Mutasi')
                            ->options([
                                'perpindahan_lokasi' => 'Perpindahan Lokasi',
                                'pergantian_pic' => 'Pergantian PIC',
                                'serah_terima' => 'Serah Terima',
                                'penarikan' => 'Penarikan Aset',
                            ])
                            ->default('perpindahan_lokasi')
                            ->required(),

                        Forms\Components\Select::make('condition_when_moved')
                            ->label('Kondisi Saat Mutasi')
                            ->options([
                                'baik' => 'Baik',
                                'cukup' => 'Cukup',
                                'rusak_ringan' => 'Rusak Ringan',
                                'rusak_berat' => 'Rusak Berat',
                            ]),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),

                Forms\Components\Section::make('Data Sebelum Mutasi')
                    ->description('Data ini otomatis diambil dari aset sebelum dipindahkan.')
                    ->icon('heroicon-o-arrow-left-circle')
                    ->schema([
                        Forms\Components\Select::make('from_location_id')
                            ->label('Lokasi Sebelumnya')
                            ->relationship('fromLocation', 'name')
                            ->searchable()
                            ->preload()
                            ->disabled()
                            ->dehydrated(true),

                        Forms\Components\Select::make('from_user_id')
                            ->label('PIC Sebelumnya')
                            ->relationship('fromUser', 'name')
                            ->searchable()
                            ->preload()
                            ->disabled()
                            ->dehydrated(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Data Tujuan Mutasi')
                    ->description('Pilih lokasi dan PIC baru aset.')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->schema([
                        Forms\Components\Select::make('to_location_id')
                            ->label('Lokasi Baru')
                            ->relationship('toLocation', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('to_user_id')
                            ->label('PIC Baru')
                            ->relationship('toUser', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Mutasi')
                            ->placeholder('Contoh: Laptop dipindahkan dari IT ke Finance karena digunakan untuk admin baru.')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Riwayat Mutasi Aset IT')
            ->description('Daftar perpindahan lokasi dan pergantian penanggung jawab aset IT.')
            ->columns([
                Tables\Columns\TextColumn::make('moved_at')
                    ->label('Tanggal')
                    ->icon('heroicon-m-calendar-days')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('asset.code')
                    ->label('Kode Aset')
                    ->icon('heroicon-m-qr-code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->color('primary')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('asset.name')
                    ->label('Nama Aset')
                    ->icon('heroicon-m-computer-desktop')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'perpindahan_lokasi' => 'Perpindahan Lokasi',
                        'pergantian_pic' => 'Pergantian PIC',
                        'serah_terima' => 'Serah Terima',
                        'penarikan' => 'Penarikan',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'perpindahan_lokasi' => 'info',
                        'pergantian_pic' => 'warning',
                        'serah_terima' => 'success',
                        'penarikan' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('fromLocation.name')
                    ->label('Lokasi Lama')
                    ->icon('heroicon-m-arrow-left-circle')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('toLocation.name')
                    ->label('Lokasi Baru')
                    ->icon('heroicon-m-arrow-right-circle')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('fromUser.name')
                    ->label('PIC Lama')
                    ->icon('heroicon-m-user-minus')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('toUser.name')
                    ->label('PIC Baru')
                    ->icon('heroicon-m-user-plus')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('condition_when_moved')
                    ->label('Kondisi')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'baik' => 'Baik',
                        'cukup' => 'Cukup',
                        'rusak_ringan' => 'Rusak Ringan',
                        'rusak_berat' => 'Rusak Berat',
                        default => '-',
                    })
                    ->color(fn(?string $state): string => match ($state) {
                        'baik' => 'success',
                        'cukup' => 'info',
                        'rusak_ringan' => 'warning',
                        'rusak_berat' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('itasset_id')
                    ->label('Aset')
                    ->relationship('asset', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('to_location_id')
                    ->label('Lokasi Baru')
                    ->relationship('toLocation', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('to_user_id')
                    ->label('PIC Baru')
                    ->relationship('toUser', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis Mutasi')
                    ->options([
                        'perpindahan_lokasi' => 'Perpindahan Lokasi',
                        'pergantian_pic' => 'Pergantian PIC',
                        'serah_terima' => 'Serah Terima',
                        'penarikan' => 'Penarikan',
                    ]),

                Tables\Filters\SelectFilter::make('condition_when_moved')
                    ->label('Kondisi')
                    ->options([
                        'baik' => 'Baik',
                        'cukup' => 'Cukup',
                        'rusak_ringan' => 'Rusak Ringan',
                        'rusak_berat' => 'Rusak Berat',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Detail')
                        ->icon('heroicon-m-eye')
                        ->color('info'),

                    Tables\Actions\EditAction::make()
                        ->label('Edit')
                        ->icon('heroicon-m-pencil-square')
                        ->color('warning'),

                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->icon('heroicon-m-trash')
                        ->color('danger'),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->button()
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-arrows-right-left')
            ->emptyStateHeading('Belum ada riwayat mutasi aset')
            ->emptyStateDescription('Catat perpindahan lokasi atau pergantian PIC aset IT.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Mutasi Aset')
                    ->icon('heroicon-m-plus'),
            ])
            ->defaultSort('moved_at', 'desc')
            ->striped()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->persistSortInSession();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItassetMovements::route('/'),
            'create' => Pages\CreateItassetMovement::route('/create'),
            'edit' => Pages\EditItassetMovement::route('/{record}/edit'),
        ];
    }
}
