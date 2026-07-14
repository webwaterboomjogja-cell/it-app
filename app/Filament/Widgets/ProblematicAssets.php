<?php

namespace App\Filament\Widgets;

use App\Models\Itassests;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ProblematicAssets extends BaseWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Aset Bermasalah';

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Itassests::query()
                    ->problematic()
                    ->with([
                        'Category',
                        'location',
                        'responsibleUser',
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode Aset')
                    ->searchable()
                    ->copyable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Aset')
                    ->searchable()
                    ->description(
                        fn(Itassests $record): ?string =>
                        collect([
                            $record->brand,
                            $record->model,
                        ])
                            ->filter()
                            ->implode(' ')
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn(string $state): string => match ($state) {
                            'aktif' => 'Aktif',
                            'rusak' => 'Rusak',
                            'maintenance' => 'Maintenance',
                            'nonaktif' => 'Nonaktif',
                            'hilang' => 'Hilang',
                            default => ucfirst($state),
                        }
                    )
                    ->color(
                        fn(string $state): string => match ($state) {
                            'aktif' => 'success',
                            'rusak' => 'danger',
                            'maintenance' => 'warning',
                            'nonaktif' => 'gray',
                            'hilang' => 'danger',
                            default => 'gray',
                        }
                    ),

                Tables\Columns\TextColumn::make('location.name')
                    ->label('Lokasi')
                    ->placeholder('Belum ditentukan')
                    ->icon('heroicon-m-map-pin'),

                Tables\Columns\TextColumn::make('responsibleUser.name')
                    ->label('PIC')
                    ->placeholder('Belum ada PIC')
                    ->icon('heroicon-m-user'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Tidak ada aset bermasalah')
            ->emptyStateDescription(
                'Seluruh aset berada dalam kondisi aktif atau normal.'
            )
            ->emptyStateIcon('heroicon-o-check-circle')
            ->striped();
    }
}
