<?php

namespace App\Filament\Widgets\Inventory;

use App\Models\Itassests;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentAssetsTable extends BaseWidget
{
    protected static ?string $heading = 'Aset IT Terbaru';

    protected static ?string $pollingInterval = null;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->can(
            'widget_RecentAssetsTable'
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Itassests::query()
                    ->with(['category', 'location', 'responsibleUser'])
                    ->latest()
            )
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Foto')
                    ->disk('public')
                    ->square()
                    ->size(50),

                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Aset')
                    ->searchable()
                    ->description(function ($record): ?string {
                        $detail = collect([
                            $record->brand,
                            $record->model,
                        ])->filter()->join(' • ');

                        return $detail ?: null;
                    })
                    ->weight('semibold')
                    ->wrap(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('info')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('location.name')
                    ->label('Lokasi')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('responsibleUser.name')
                    ->label('PIC')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
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
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }
}
