<?php

namespace App\Services\Exports;

use App\Models\AssetCategory;
use App\Models\Itassests;
use App\Models\Itasset;
use App\Models\Location as LocationModel;
use App\Models\Locations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AssetExportService
{
    /**
     * Query utama export aset.
     *
     * Query ini digunakan oleh Excel dan PDF supaya
     * hasil filter kedua format selalu sama.
     */
    public function query(array $filters = []): Builder
    {
        return Itassests::query()
            ->with([
                'category:id,name',
                'location:id,name',
                'responsibleUser:id,name',
            ])
            ->when(
                filled($filters['asset_category_id'] ?? null),
                fn(Builder $query): Builder => $query->where(
                    'asset_category_id',
                    $filters['asset_category_id']
                )
            )
            ->when(
                filled($filters['location_id'] ?? null),
                fn(Builder $query): Builder => $query->where(
                    'location_id',
                    $filters['location_id']
                )
            )
            ->when(
                filled($filters['asset_status'] ?? null),
                fn(Builder $query): Builder => $query->where(
                    'status',
                    $filters['asset_status']
                )
            )
            ->orderBy('code')
            ->orderBy('name');
    }

    /**
     * Mengambil seluruh data sesuai filter.
     */
    public function get(array $filters = []): Collection
    {
        return $this->query($filters)->get();
    }

    /**
     * Mendapatkan teks filter untuk header laporan.
     */
    public function filterSummary(array $filters = []): array
    {
        $category = 'Semua kategori';
        $location = 'Semua lokasi';
        $status = 'Semua status';

        if (filled($filters['asset_category_id'] ?? null)) {
            $category = Assetcategory::query()
                ->find($filters['asset_category_id'])
                ?->name ?? 'Kategori tidak ditemukan';
        }

        if (filled($filters['location_id'] ?? null)) {
            $location = Locations::query()
                ->find($filters['location_id'])
                ?->name ?? 'Lokasi tidak ditemukan';
        }

        if (filled($filters['asset_status'] ?? null)) {
            $status = Str::headline(
                (string) $filters['asset_status']
            );
        }

        return [
            'category' => $category,
            'location' => $location,
            'status' => $status,
        ];
    }
}
