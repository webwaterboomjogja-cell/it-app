<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItassetsResource\Pages;
use App\Models\Itassests;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Illuminate\Support\Str;
use Filament\Tables\Actions\Action;

use App\Models\ItassetMovement;


class ItassetsResource extends Resource
{
    protected static ?string $model = Itassests::class;

    protected static ?string $navigationGroup = 'Inventaris IT';

    protected static ?string $navigationLabel = 'Aset IT';

    protected static ?string $modelLabel = 'Aset IT';

    protected static ?string $pluralModelLabel = 'Aset IT';

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Utama Aset')
                    ->description('Data identitas utama aset IT kantor.')
                    ->icon('heroicon-o-computer-desktop')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Kode Aset')
                            ->placeholder('Otomatis dibuat sistem')
                            ->prefixIcon('heroicon-o-qr-code')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama Aset')
                            ->placeholder('Contoh: Laptop Admin Finance')
                            // ->prefixIcon('heroicon-o')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('asset_category_id')
                            ->label('Kategori Aset')
                            ->relationship('category', 'name')
                            ->placeholder('Pilih kategori aset')
                            ->prefixIcon('heroicon-o-squares-2x2')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('location_id')
                            ->label('Lokasi Aset')
                            ->relationship('location', 'name')
                            ->placeholder('Pilih lokasi aset')
                            ->prefixIcon('heroicon-o-map-pin')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('responsible_user_id')
                            ->label('Penanggung Jawab')
                            ->relationship('responsibleUser', 'name')
                            ->placeholder('Pilih user penanggung jawab')
                            ->prefixIcon('heroicon-o-user-circle')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ]),

                Forms\Components\Section::make('Spesifikasi dan Kondisi')
                    ->description('Lengkapi merek, tipe, serial number, status, dan kondisi aset.')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->schema([
                        Forms\Components\TextInput::make('brand')
                            ->label('Merek')
                            ->placeholder('Contoh: Asus, Lenovo, HP')
                            ->prefixIcon('heroicon-o-tag')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('model')
                            ->label('Model / Tipe')
                            ->placeholder('Contoh: ThinkPad T480')
                            ->prefixIcon('heroicon-o-cube')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('serial_number')
                            ->label('Serial Number')
                            ->placeholder('Masukkan nomor seri perangkat')
                            ->prefixIcon('heroicon-o-finger-print')
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('purchase_date')
                            ->label('Tanggal Pembelian')
                            ->placeholder('Pilih tanggal pembelian')
                            ->prefixIcon('heroicon-o-calendar-days')
                            ->native(false)
                            ->displayFormat('d F Y'),

                        Forms\Components\Select::make('status')
                            ->label('Status Aset')
                            ->placeholder('Pilih status aset')
                            ->prefixIcon('heroicon-o-signal')
                            ->options([
                                'aktif' => 'Aktif',
                                'rusak' => 'Rusak',
                                'maintenance' => 'Maintenance',
                                'nonaktif' => 'Nonaktif',
                                'hilang' => 'Hilang',
                            ])
                            ->default('aktif')
                            ->required(),

                        Forms\Components\Select::make('condition')
                            ->label('Kondisi Aset')
                            ->placeholder('Pilih kondisi aset')
                            ->prefixIcon('heroicon-o-shield-check')
                            ->options([
                                'baik' => 'Baik',
                                'cukup' => 'Cukup',
                                'rusak_ringan' => 'Rusak Ringan',
                                'rusak_berat' => 'Rusak Berat',
                            ])
                            ->default('baik')
                            ->required(),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ]),

                Forms\Components\Section::make('Dokumentasi Aset')
                    ->description('Upload foto aset dan tambahkan catatan jika diperlukan.')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        Forms\Components\FileUpload::make('photo')
                            ->label('Foto Aset')
                            ->image()
                            ->imageEditor()
                            ->columnSpanFull()
                            ->directory('it-assets')
                            ->disk('public')
                            ->imagePreviewHeight('220')
                            ->loadingIndicatorPosition('left')
                            ->panelLayout('integrated')
                            ->downloadable()
                            ->openable(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->placeholder('Contoh: RAM sudah upgrade 8GB, charger original, kondisi layar normal.')
                            ->rows(6)
                            ->columnSpanFull(),
                    ])
                    ->columns([
                        'default' => 1,
                        'lg' => 2,
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Daftar Inventaris Aset IT')
            ->description('Monitoring aset IT kantor berdasarkan kategori, lokasi, penanggung jawab, status, dan kondisi.')
            ->headerActions([
                Action::make('print_all_qr')
                    ->label('Print Semua QR')
                    ->icon('heroicon-m-printer')
                    ->color('warning')
                    ->url(route('asset-it.labels.print-all'))
                    ->openUrlInNewTab(),

                Action::make('export_all')
                    ->label('Export Semua')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('success')
                    ->url(route('it-assets.export'))
                    ->openUrlInNewTab(),

                Action::make('export_filtered')
                    ->label('Export Filter')
                    ->icon('heroicon-m-funnel')
                    ->color('primary')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Status Aset')
                            ->options([
                                'aktif' => 'Aktif',
                                'rusak' => 'Rusak',
                                'maintenance' => 'Maintenance',
                                'nonaktif' => 'Nonaktif',
                                'hilang' => 'Hilang',
                            ]),

                        Forms\Components\Select::make('condition')
                            ->label('Kondisi Aset')
                            ->options([
                                'baik' => 'Baik',
                                'cukup' => 'Cukup',
                                'rusak_ringan' => 'Rusak Ringan',
                                'rusak_berat' => 'Rusak Berat',
                            ]),

                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('location_id')
                            ->label('Lokasi')
                            ->relationship('location', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('responsible_user_id')
                            ->label('Penanggung Jawab')
                            ->relationship('responsibleUser', 'name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (array $data) {
                        $query = array_filter([
                            'status' => $data['status'] ?? null,
                            'condition' => $data['condition'] ?? null,
                            'category_id' => $data['category_id'] ?? null,
                            'location_id' => $data['location_id'] ?? null,
                            'responsible_user_id' => $data['responsible_user_id'] ?? null,
                        ]);

                        return redirect()->to(route('it-assets.export', $query));
                    }),
            ])
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Foto')
                    ->disk('public')
                    ->square()
                    ->size(56)
                    ->defaultImageUrl(url('/images/no-image.png')),

                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->icon('heroicon-m-qr-code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Kode aset berhasil disalin')
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Aset')
                    ->icon('heroicon-m-computer-desktop')
                    ->description(function ($record): ?string {
                        $detail = collect([
                            $record->brand,
                            $record->model,
                        ])->filter()->join(' • ');

                        return $detail ?: null;
                    })
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->wrap(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->icon('heroicon-m-squares-2x2')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('location.name')
                    ->label('Lokasi')
                    ->icon('heroicon-m-map-pin')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('responsibleUser.name')
                    ->label('PIC')
                    ->icon('heroicon-m-user-circle')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->icon(fn(string $state): string => match ($state) {
                        'aktif' => 'heroicon-m-check-circle',
                        'rusak' => 'heroicon-m-x-circle',
                        'maintenance' => 'heroicon-m-wrench-screwdriver',
                        'nonaktif' => 'heroicon-m-no-symbol',
                        'hilang' => 'heroicon-m-exclamation-triangle',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'aktif' => 'Aktif',
                        'rusak' => 'Rusak',
                        'maintenance' => 'Maintenance',
                        'nonaktif' => 'Nonaktif',
                        'hilang' => 'Hilang',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'aktif' => 'success',
                        'rusak' => 'danger',
                        'maintenance' => 'warning',
                        'nonaktif' => 'gray',
                        'hilang' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('condition')
                    ->label('Kondisi')
                    ->badge()
                    ->icon(fn(string $state): string => match ($state) {
                        'baik' => 'heroicon-m-shield-check',
                        'cukup' => 'heroicon-m-exclamation-circle',
                        'rusak_ringan' => 'heroicon-m-exclamation-triangle',
                        'rusak_berat' => 'heroicon-m-x-circle',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'baik' => 'Baik',
                        'cukup' => 'Cukup',
                        'rusak_ringan' => 'Rusak Ringan',
                        'rusak_berat' => 'Rusak Berat',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'baik' => 'success',
                        'cukup' => 'info',
                        'rusak_ringan' => 'warning',
                        'rusak_berat' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('purchase_date')
                    ->label('Tgl Beli')
                    ->icon('heroicon-m-calendar-days')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('asset_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('location_id')
                    ->label('Lokasi')
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('responsible_user_id')
                    ->label('Penanggung Jawab')
                    ->relationship('responsibleUser', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'aktif' => 'Aktif',
                        'rusak' => 'Rusak',
                        'maintenance' => 'Maintenance',
                        'nonaktif' => 'Nonaktif',
                        'hilang' => 'Hilang',
                    ]),

                Tables\Filters\SelectFilter::make('condition')
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

                    Tables\Actions\Action::make('print_qr_label')
                        ->label('Cetak QR')
                        ->icon('heroicon-m-qr-code')
                        ->color('success')
                        ->url(fn($record): string => route('asset-it.label', $record->qr_token))
                        ->openUrlInNewTab()
                        ->visible(fn($record): bool => filled($record->qr_token)),

                    Tables\Actions\Action::make('generate_qr_token')
                        ->label('Generate QR')
                        ->icon('heroicon-m-sparkles')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Generate QR Token')
                        ->modalDescription('Sistem akan membuat QR token baru untuk aset ini.')
                        ->action(function ($record): void {
                            $record->update([
                                'qr_token' => (string) Str::uuid(),
                            ]);
                        })
                        ->visible(fn($record): bool => blank($record->qr_token)),

                    Tables\Actions\EditAction::make()
                        ->label('Edit')
                        ->icon('heroicon-m-pencil-square')
                        ->color('warning'),

                    Tables\Actions\Action::make('movement')
                        ->label('Mutasi')
                        ->icon('heroicon-m-arrows-right-left')
                        ->color('primary')
                        ->form([
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

                            Forms\Components\Select::make('to_location_id')
                                ->label('Lokasi Baru')
                                ->relationship('location', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),

                            Forms\Components\Select::make('to_user_id')
                                ->label('PIC Baru')
                                ->relationship('responsibleUser', 'name')
                                ->searchable()
                                ->preload(),

                            Forms\Components\Select::make('condition_when_moved')
                                ->label('Kondisi Saat Mutasi')
                                ->options([
                                    'baik' => 'Baik',
                                    'cukup' => 'Cukup',
                                    'rusak_ringan' => 'Rusak Ringan',
                                    'rusak_berat' => 'Rusak Berat',
                                ]),

                            Forms\Components\Textarea::make('notes')
                                ->label('Catatan Mutasi')
                                ->rows(4)
                                ->columnSpanFull(),
                        ])
                        ->action(function ($record, array $data): void {
                            Itassetmovement::create([
                                'itasset_id' => $record->id,
                                'from_location_id' => $record->location_id,
                                'to_location_id' => $data['to_location_id'],
                                'from_user_id' => $record->responsible_user_id,
                                'to_user_id' => $data['to_user_id'] ?? null,
                                'moved_at' => $data['moved_at'],
                                'type' => $data['type'],
                                'condition_when_moved' => $data['condition_when_moved'] ?? null,
                                'notes' => $data['notes'] ?? null,
                            ]);
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->icon('heroicon-m-trash')
                        ->color('danger'),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-cpu-chip')
            ->emptyStateHeading('Belum ada data aset IT')
            ->emptyStateDescription('Tambahkan aset pertama seperti laptop, komputer, printer, router, CCTV, atau perangkat IT lainnya.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Aset IT')
                    ->icon('heroicon-m-plus'),
            ])
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->persistSortInSession();
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
            'index' => Pages\ListItassets::route('/'),
            'create' => Pages\CreateItassets::route('/create'),
            'edit' => Pages\EditItassets::route('/{record}/edit'),
        ];
    }
}
