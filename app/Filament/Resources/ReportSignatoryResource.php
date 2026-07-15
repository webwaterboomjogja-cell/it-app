<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportSignatoryResource\Pages;
use App\Models\Reportsignatory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReportSignatoryResource extends Resource
{
    protected static ?string $model =
    Reportsignatory::class;

    protected static ?string $navigationIcon =
    'heroicon-o-pencil-square';

    protected static ?string $navigationGroup =
    'Laporan';

    protected static ?string $navigationLabel =
    'Pejabat Penandatangan';

    protected static ?string $modelLabel =
    'Pejabat Penandatangan';

    protected static ?string $pluralModelLabel =
    'Pejabat Penandatangan';

    protected static ?int $navigationSort = 92;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can(
            'manage_report_signatories'
        ) ?? false;
    }

    public static function form(
        Form $form
    ): Form {
        return $form
            ->schema([
                Forms\Components\Section::make(
                    'Informasi Pejabat'
                )
                    ->schema([
                        Forms\Components\Select::make(
                            'role'
                        )
                            ->label(
                                'Peran dalam Laporan'
                            )
                            ->options([
                                'prepared_by' =>
                                'Pembuat Laporan',

                                'reviewed_by' =>
                                'Pemeriksa',

                                'approved_by' =>
                                'Penyetuju',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make(
                            'user_id'
                        )
                            ->label('User Sistem')
                            ->relationship(
                                'user',
                                'name'
                            )
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\TextInput::make(
                            'name'
                        )
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make(
                            'position'
                        )
                            ->label('Jabatan')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\FileUpload::make(
                            'signature_path'
                        )
                            ->label(
                                'Gambar Tanda Tangan'
                            )
                            ->disk('local')
                            ->directory(
                                'signatures'
                            )
                            ->image()
                            ->acceptedFileTypes([
                                'image/png',
                                'image/jpeg',
                                'image/webp',
                            ])
                            ->maxSize(2048),

                        Forms\Components\Toggle::make(
                            'is_active'
                        )
                            ->label('Aktif')
                            ->default(true),

                        Forms\Components\TextInput::make(
                            'sort'
                        )
                            ->label('Urutan')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(
        Table $table
    ): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make(
                    'role'
                )
                    ->label('Peran')
                    ->formatStateUsing(
                        fn(
                            string $state
                        ): string => match ($state) {
                            'prepared_by' =>
                            'Pembuat',

                            'reviewed_by' =>
                            'Pemeriksa',

                            'approved_by' =>
                            'Penyetuju',

                            default => $state,
                        }
                    )
                    ->badge(),

                Tables\Columns\TextColumn::make(
                    'name'
                )
                    ->label('Nama')
                    ->searchable(),

                Tables\Columns\TextColumn::make(
                    'position'
                )
                    ->label('Jabatan')
                    ->searchable(),

                Tables\Columns\IconColumn::make(
                    'is_active'
                )
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make(
                    'updated_at'
                )
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' =>
            Pages\ListReportSignatories::route(
                '/'
            ),

            'create' =>
            Pages\CreateReportSignatory::route(
                '/create'
            ),

            'edit' =>
            Pages\EditReportSignatory::route(
                '/{record}/edit'
            ),
        ];
    }
}
