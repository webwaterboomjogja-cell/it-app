<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItassetMaintenanceResource\Pages;
use App\Models\Itassetmaintenance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ItassetMaintenanceResource extends Resource
{
    protected static ?string $model = Itassetmaintenance::class;

    protected static ?string $navigationGroup = 'Inventaris IT';

    protected static ?string $navigationLabel = 'Maintenance Aset';

    protected static ?string $modelLabel = 'Maintenance Aset';

    protected static ?string $pluralModelLabel = 'Maintenance Aset';

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Maintenance')
                    ->description('Catat aset yang sedang diperbaiki atau pernah diperbaiki.')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->schema([
                        Forms\Components\Select::make('itasset_id')
                            ->label('Aset IT')
                            ->relationship('asset', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\DatePicker::make('maintenance_date')
                            ->label('Tanggal Maintenance')
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->default(now())
                            ->required(),

                        Forms\Components\Select::make('handled_by_user_id')
                            ->label('Ditangani Oleh')
                            ->relationship('handledBy', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('status')
                            ->label('Status Maintenance')
                            ->options([
                                'proses' => 'Dalam Proses',
                                'selesai' => 'Selesai',
                                'gagal' => 'Gagal Diperbaiki',
                                'perlu_penggantian' => 'Perlu Penggantian',
                            ])
                            ->default('proses')
                            ->required(),

                        Forms\Components\TextInput::make('cost')
                            ->label('Biaya Maintenance')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),

                Forms\Components\Section::make('Detail Kerusakan dan Perbaikan')
                    ->description('Isi masalah aset dan tindakan yang dilakukan.')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Forms\Components\Textarea::make('problem')
                            ->label('Masalah / Kerusakan')
                            ->placeholder('Contoh: Laptop tidak bisa menyala, keyboard rusak, printer tidak menarik kertas.')
                            ->rows(4)
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('action_taken')
                            ->label('Tindakan Perbaikan')
                            ->placeholder('Contoh: Membersihkan RAM, mengganti keyboard, install ulang driver printer.')
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Tambahan')
                            ->placeholder('Tambahkan catatan jika diperlukan.')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Riwayat Maintenance Aset IT')
            ->description('Daftar catatan kerusakan, perbaikan, biaya, dan status maintenance aset.')
            ->columns([
                Tables\Columns\TextColumn::make('maintenance_date')
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

                Tables\Columns\TextColumn::make('problem')
                    ->label('Masalah')
                    ->limit(45)
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('handledBy.name')
                    ->label('Teknisi / PIC')
                    ->icon('heroicon-m-user-circle')
                    ->placeholder('-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('cost')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->icon(fn(string $state): string => match ($state) {
                        'proses' => 'heroicon-m-arrow-path',
                        'selesai' => 'heroicon-m-check-circle',
                        'gagal' => 'heroicon-m-x-circle',
                        'perlu_penggantian' => 'heroicon-m-exclamation-triangle',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'proses' => 'Dalam Proses',
                        'selesai' => 'Selesai',
                        'gagal' => 'Gagal Diperbaiki',
                        'perlu_penggantian' => 'Perlu Penggantian',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'proses' => 'warning',
                        'selesai' => 'success',
                        'gagal' => 'danger',
                        'perlu_penggantian' => 'danger',
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

                Tables\Filters\SelectFilter::make('handled_by_user_id')
                    ->label('Ditangani Oleh')
                    ->relationship('handledBy', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'proses' => 'Dalam Proses',
                        'selesai' => 'Selesai',
                        'gagal' => 'Gagal Diperbaiki',
                        'perlu_penggantian' => 'Perlu Penggantian',
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
            ->emptyStateIcon('heroicon-o-wrench-screwdriver')
            ->emptyStateHeading('Belum ada riwayat maintenance')
            ->emptyStateDescription('Catat maintenance pertama untuk aset yang rusak atau sedang diperbaiki.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Maintenance')
                    ->icon('heroicon-m-plus'),
            ])
            ->defaultSort('maintenance_date', 'desc')
            ->striped()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->persistSortInSession();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItassetMaintenances::route('/'),
            'create' => Pages\CreateItassetMaintenance::route('/create'),
            'edit' => Pages\EditItassetMaintenance::route('/{record}/edit'),
        ];
    }
}
