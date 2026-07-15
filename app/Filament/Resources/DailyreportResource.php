<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DailyReportResource\Pages;
use App\Models\DailyReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

use Filament\Forms\Get;
use Illuminate\Support\Carbon;

class DailyReportResource extends Resource
{
    protected static ?string $model = DailyReport::class;

    protected static ?string $navigationGroup = 'Operasional IT';

    protected static ?string $navigationLabel = 'Laporan Harian IT';

    protected static ?string $modelLabel = 'Laporan Harian IT';

    protected static ?string $pluralModelLabel = 'Laporan Harian IT';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 6;

    protected static function isKepalaIt(): bool
    {
        $user = auth()->user();

        return $user?->hasAnyRole([
            'super_admin',
            'kepala_it',
        ]) ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['user', 'category', 'asset', 'reviewer']);

        if (static::isKepalaIt()) {
            return $query;
        }

        return $query->where('user_id', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Laporan')
                    ->schema([
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn() => auth()->id())
                            ->visible(fn() => ! static::isKepalaIt()),

                        Forms\Components\Select::make('user_id')
                            ->label('Staff IT')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn() => auth()->id())
                            ->visible(fn() => static::isKepalaIt()),

                        Forms\Components\DatePicker::make('report_date')
                            ->label('Tanggal Laporan')
                            ->default(now()->toDateString())
                            ->displayFormat('d/m/Y')
                            ->maxDate(now())
                            ->required(),

                        Forms\Components\Select::make('work_category_id')
                            ->label('Kategori Pekerjaan')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('itasset_id')
                            ->label('Aset Terkait')
                            ->relationship('asset', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->getOptionLabelFromRecordUsing(function ($record): string {
                                $assetCode = data_get($record, 'code');
                                $assetName = data_get($record, 'name');

                                if ($assetCode && $assetName) {
                                    return "{$assetCode} - {$assetName}";
                                }

                                return $assetName ?? '-';
                            })
                            ->helperText('Opsional. Pilih aset jika pekerjaan ini berkaitan dengan inventaris IT.'),

                        Forms\Components\Select::make('priority')
                            ->label('Prioritas')
                            ->options([
                                'rendah' => 'Rendah',
                                'normal' => 'Normal',
                                'tinggi' => 'Tinggi',
                                'urgent' => 'Urgent',
                            ])
                            ->default('normal')
                            ->required(),

                        Forms\Components\TextInput::make('location')
                            ->label('Lokasi Pekerjaan')
                            ->placeholder('Contoh: Kantor HRD, Ruang Server, Loket Tiket')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('title')
                            ->label('Judul Pekerjaan')
                            ->placeholder('Contoh: Perbaikan jaringan kantor')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TimePicker::make('start_time')
                            ->label('Jam Mulai')
                            ->seconds(false)
                            ->native(false)
                            ->displayFormat('H:i')
                            ->live()
                            ->dehydrateStateUsing(function ($state) {
                                if (blank($state)) {
                                    return null;
                                }

                                return Carbon::parse($state)->format('H:i:s');
                            })
                            ->rules([
                                'required_with:end_time',
                            ]),

                        Forms\Components\TimePicker::make('end_time')
                            ->label('Jam Selesai')
                            ->seconds(false)
                            ->native(false)
                            ->displayFormat('H:i')
                            ->live()
                            ->dehydrateStateUsing(function ($state) {
                                if (blank($state)) {
                                    return null;
                                }

                                return Carbon::parse($state)->format('H:i:s');
                            })
                            ->rules([
                                'required_with:start_time',
                                'after:start_time',
                            ]),

                        Forms\Components\Placeholder::make('duration_preview')
                            ->label('Durasi Otomatis')
                            ->content(function (Get $get): string {
                                $startTime = $get('start_time');
                                $endTime = $get('end_time');

                                if (blank($startTime) || blank($endTime)) {
                                    return '-';
                                }

                                try {

                                    $start = Carbon::parse($startTime);
                                    $end = Carbon::parse($endTime);

                                    if ($end->lessThanOrEqualTo($start)) {
                                        return 'Jam selesai harus lebih besar dari jam mulai';
                                    }

                                    $minutes = (int) $start->diffInMinutes($end);

                                    $hours = intdiv($minutes, 60);
                                    $remainingMinutes = $minutes % 60;

                                    if ($hours > 0 && $remainingMinutes > 0) {
                                        return "{$hours} jam {$remainingMinutes} menit";
                                    }

                                    if ($hours > 0) {
                                        return "{$hours} jam";
                                    }

                                    return "{$remainingMinutes} menit";
                                } catch (\Throwable $exception) {
                                    return 'Format jam tidak valid';
                                }
                            }),

                        Forms\Components\RichEditor::make('description')
                            ->label('Deskripsi Pekerjaan')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Kendala & Solusi')
                    ->schema([
                        Forms\Components\Textarea::make('obstacle')
                            ->label('Kendala')
                            ->rows(4)
                            ->placeholder('Tuliskan kendala jika ada'),

                        Forms\Components\Textarea::make('solution')
                            ->label('Solusi')
                            ->rows(4)
                            ->placeholder('Tuliskan solusi yang dilakukan'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status & Lampiran')
                    ->schema([
                        Forms\Components\Select::make('work_status')
                            ->label('Status Pekerjaan')
                            ->options([
                                'selesai' => 'Selesai',
                                'proses' => 'Proses',
                                'tertunda' => 'Tertunda',
                            ])
                            ->default('proses')
                            ->required(),

                        Forms\Components\Select::make('review_status')
                            ->label('Status Review')
                            ->options(
                                fn() => static::isKepalaIt()
                                    ? [
                                        'draft' => 'Draft',
                                        'dikirim' => 'Dikirim',
                                        'direview' => 'Direview',
                                    ]
                                    : [
                                        'draft' => 'Draft',
                                        'dikirim' => 'Dikirim',
                                    ]
                            )
                            ->default('draft')
                            ->required(),

                        Forms\Components\FileUpload::make('attachments')
                            ->label('Lampiran')
                            ->multiple()
                            ->disk('public')
                            ->directory('daily-reports')
                            ->preserveFilenames()
                            ->downloadable()
                            ->openable()
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                                'application/pdf',
                            ])
                            ->maxSize(5120)
                            ->helperText('Upload foto bukti pekerjaan atau PDF. Maksimal 5 MB per file.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Review Kepala IT')
                    ->schema([
                        Forms\Components\Textarea::make('review_note')
                            ->label('Catatan Review')
                            ->rows(4)
                            ->disabled(fn() => ! static::isKepalaIt()),

                        Forms\Components\Placeholder::make('reviewer_info')
                            ->label('Direview Oleh')
                            ->content(fn(?DailyReport $record) => $record?->reviewer?->name ?? '-'),

                        Forms\Components\Placeholder::make('reviewed_at_info')
                            ->label('Tanggal Review')
                            ->content(fn(?DailyReport $record) => $record?->reviewed_at?->format('d M Y H:i') ?? '-'),
                    ])
                    ->visible(fn(?DailyReport $record) => static::isKepalaIt() || filled($record?->review_note))
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('report_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Staff')
                    ->searchable()
                    ->sortable()
                    ->visible(fn() => static::isKepalaIt()),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('asset.name')
                    ->label('Aset')
                    ->searchable()
                    ->limit(30)
                    ->placeholder('-')
                    ->description(fn(DailyReport $record): ?string => data_get($record->asset, 'code')),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'rendah' => 'Rendah',
                        'normal' => 'Normal',
                        'tinggi' => 'Tinggi',
                        'urgent' => 'Urgent',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'rendah' => 'gray',
                        'normal' => 'info',
                        'tinggi' => 'warning',
                        'urgent' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable()
                    ->limit(25)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Durasi')
                    ->formatStateUsing(function (?int $state): string {
                        if (! $state) {
                            return '-';
                        }

                        $hours = intdiv($state, 60);
                        $minutes = $state % 60;

                        if ($hours > 0 && $minutes > 0) {
                            return "{$hours} jam {$minutes} menit";
                        }

                        if ($hours > 0) {
                            return "{$hours} jam";
                        }

                        return "{$minutes} menit";
                    }),

                Tables\Columns\TextColumn::make('title')
                    ->label('Pekerjaan')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('work_status')
                    ->label('Status Pekerjaan')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'selesai' => 'Selesai',
                        'proses' => 'Proses',
                        'tertunda' => 'Tertunda',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'selesai' => 'success',
                        'proses' => 'warning',
                        'tertunda' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('review_status')
                    ->label('Review')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'dikirim' => 'Dikirim',
                        'direview' => 'Direview',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'dikirim' => 'info',
                        'direview' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('report_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('itassets_id')
                    ->label('Aset')
                    ->relationship('asset', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('priority')
                    ->label('Prioritas')
                    ->options([
                        'rendah' => 'Rendah',
                        'normal' => 'Normal',
                        'tinggi' => 'Tinggi',
                        'urgent' => 'Urgent',
                    ]),

                Tables\Filters\SelectFilter::make('work_status')
                    ->label('Status Pekerjaan')
                    ->options([
                        'selesai' => 'Selesai',
                        'proses' => 'Proses',
                        'tertunda' => 'Tertunda',
                    ]),

                Tables\Filters\SelectFilter::make('review_status')
                    ->label('Status Review')
                    ->options([
                        'draft' => 'Draft',
                        'dikirim' => 'Dikirim',
                        'direview' => 'Direview',
                    ]),

                Tables\Filters\SelectFilter::make('work_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),

                Tables\Filters\Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn(Builder $query): Builder => $query->whereDate('report_date', today())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('submit')
                    ->label('Kirim')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Kirim laporan?')
                    ->modalDescription('Setelah dikirim, laporan akan masuk ke proses review Kepala IT.')
                    ->visible(
                        fn(DailyReport $record): bool =>
                        $record->user_id === auth()->id()
                            && $record->review_status === 'draft'
                    )
                    ->action(function (DailyReport $record): void {
                        $record->update([
                            'review_status' => 'dikirim',
                        ]);
                    }),

                Tables\Actions\Action::make('review')
                    ->label('Review')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('review_note')
                            ->label('Catatan Review')
                            ->rows(4)
                            ->placeholder('Contoh: Laporan sudah sesuai.')
                            ->required(),
                    ])
                    ->visible(
                        fn(DailyReport $record): bool =>
                        static::isKepalaIt()
                            && $record->review_status === 'dikirim'
                    )
                    ->action(function (DailyReport $record, array $data): void {
                        $record->update([
                            'review_status' => 'direview',
                            'review_note' => $data['review_note'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);
                    }),

                Tables\Actions\EditAction::make()
                    ->visible(
                        fn(DailyReport $record): bool =>
                        static::isKepalaIt()
                            || (
                                $record->user_id === auth()->id()
                                && $record->review_status === 'draft'
                            )
                    ),

                Tables\Actions\DeleteAction::make()
                    ->visible(
                        fn(DailyReport $record): bool =>
                        static::isKepalaIt()
                            || (
                                $record->user_id === auth()->id()
                                && $record->review_status === 'draft'
                            )
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => static::isKepalaIt()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyreports::route('/'),
            'create' => Pages\CreateDailyReport::route('/create'),
            'view' => Pages\ViewDailyReport::route('/{record}'),
            'edit' => Pages\EditDailyReport::route('/{record}/edit'),
        ];
    }
}
