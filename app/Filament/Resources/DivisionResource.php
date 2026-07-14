<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DivisionResource\Pages;
use App\Models\Devisions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DivisionResource extends Resource
{
    protected static ?string $model = Devisions::class;

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Divisi';

    protected static ?string $modelLabel = 'Divisi';

    protected static ?string $pluralModelLabel = 'Divisi';

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        return (string) Devisions::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Divisi')
                    ->description('Kelola data divisi yang digunakan untuk penanggung jawab aset, laporan pekerjaan, dan pembagian akses sistem.')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Divisi')
                                    ->placeholder('Contoh: IT')
                                    ->required()
                                    ->maxLength(255)
                                    ->autofocus()
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 2,
                                    ]),

                                Forms\Components\TextInput::make('code')
                                    ->label('Kode Divisi')
                                    ->placeholder('Contoh: IT, MKT, FIN')
                                    ->maxLength(50)
                                    ->unique(ignoreRecord: true)
                                    ->dehydrateStateUsing(fn(?string $state): ?string => $state ? strtoupper(trim($state)) : null)
                                    ->helperText('Kode divisi sebaiknya singkat dan unik.'),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Urutan')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Semakin kecil angka, semakin atas posisinya.'),

                                Forms\Components\Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->placeholder('Tulis keterangan singkat mengenai fungsi divisi ini.')
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
                    ->description('Atur apakah divisi ini masih aktif digunakan di sistem.')
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->helperText('Aktifkan jika divisi ini masih digunakan untuk input data aset, pekerjaan, atau jadwal.')
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
            ->heading('Daftar Divisi')
            ->description('Data divisi digunakan sebagai referensi pada modul inventaris, laporan pekerjaan, jadwal, dan manajemen user.')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Divisi')
                    ->description(fn(Devisions $record): ?string => $record->description)
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-building-office')
                    ->iconColor('success'),

                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->color('gray')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Kode divisi berhasil disalin')
                    ->copyMessageDuration(1500),

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
                Tables\Actions\ViewAction::make()
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('gray'),

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
            ->emptyStateIcon('heroicon-o-building-office')
            ->emptyStateHeading('Belum ada data divisi')
            ->emptyStateDescription('Tambahkan data divisi pertama agar bisa digunakan pada modul aset, pekerjaan, jadwal, dan user.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Divisi')
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
            'index' => Pages\ListDivisions::route('/'),
            'create' => Pages\CreateDivision::route('/create'),
            'edit' => Pages\EditDivision::route('/{record}/edit'),
        ];
    }
}
