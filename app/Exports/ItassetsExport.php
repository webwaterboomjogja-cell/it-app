<?php

namespace App\Exports;

use App\Models\Itassests;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItassetsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        protected ?string $status = null,
        protected ?string $condition = null,
        protected ?int $categoryId = null,
        protected ?int $locationId = null,
        protected ?int $responsibleUserId = null,
    ) {}

    public function query(): Builder
    {
        return Itassests::query()
            ->with([
                'category',
                'location',
                'responsibleUser',
            ])
            ->when($this->status, function (Builder $query) {
                $query->where('status', $this->status);
            })
            ->when($this->condition, function (Builder $query) {
                $query->where('condition', $this->condition);
            })
            ->when($this->categoryId, function (Builder $query) {
                $query->where('asset_category_id', $this->categoryId);
            })
            ->when($this->locationId, function (Builder $query) {
                $query->where('location_id', $this->locationId);
            })
            ->when($this->responsibleUserId, function (Builder $query) {
                $query->where('responsible_user_id', $this->responsibleUserId);
            })
            ->latest('created_at');
    }

    public function headings(): array
    {
        return [
            'Kode Aset',
            'Nama Aset',
            'Kategori',
            'Lokasi',
            'Penanggung Jawab',
            'Merek',
            'Model / Tipe',
            'Serial Number',
            'Tanggal Pembelian',
            'Status',
            'Kondisi',
            'Catatan',
            'Tanggal Input',
        ];
    }

    public function map($asset): array
    {
        return [
            $asset->code,
            $asset->name,
            $asset->category?->name ?? '-',
            $asset->location?->name ?? '-',
            $asset->responsibleUser?->name ?? '-',
            $asset->brand ?? '-',
            $asset->model ?? '-',
            $asset->serial_number ?? '-',
            $asset->purchase_date ? $asset->purchase_date->format('d/m/Y') : '-',
            $this->statusLabel($asset->status),
            $this->conditionLabel($asset->condition),
            $asset->notes ?? '-',
            $asset->created_at ? $asset->created_at->format('d/m/Y H:i') : '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }

    protected function statusLabel(?string $status): string
    {
        return match ($status) {
            'aktif' => 'Aktif',
            'rusak' => 'Rusak',
            'maintenance' => 'Maintenance',
            'nonaktif' => 'Nonaktif',
            'hilang' => 'Hilang',
            default => '-',
        };
    }

    protected function conditionLabel(?string $condition): string
    {
        return match ($condition) {
            'baik' => 'Baik',
            'cukup' => 'Cukup',
            'rusak_ringan' => 'Rusak Ringan',
            'rusak_berat' => 'Rusak Berat',
            default => '-',
        };
    }
}
