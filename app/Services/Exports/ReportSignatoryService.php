<?php

namespace App\Services\Exports;

use App\Models\Reportsignatory;
use Illuminate\Support\Facades\Storage;

class ReportSignatoryService
{
    /**
     * Mengambil snapshot tanda tangan aktif.
     *
     * Snapshot disimpan pada export history agar perubahan
     * master pejabat tidak mengubah dokumen lama.
     */
    public function snapshot(
        string $generatedBy
    ): array {
        $records = Reportsignatory::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->keyBy('role');

        return [
            'prepared_by' => $this->buildItem(
                role: 'prepared_by',
                record: $records->get(
                    'prepared_by'
                ),
                fallbackName: $generatedBy,
                fallbackPosition: 'Staff Information Technology'
            ),

            'reviewed_by' => $this->buildItem(
                role: 'reviewed_by',
                record: $records->get(
                    'reviewed_by'
                ),
                fallbackName: '',
                fallbackPosition: 'Kepala Divisi IT'
            ),

            'approved_by' => $this->buildItem(
                role: 'approved_by',
                record: $records->get(
                    'approved_by'
                ),
                fallbackName: '',
                fallbackPosition: 'Pimpinan'
            ),
        ];
    }

    private function buildItem(
        string $role,
        ?Reportsignatory $record,
        string $fallbackName,
        string $fallbackPosition
    ): array {
        return [
            'role' => $role,

            'name' => $record?->name
                ?: $fallbackName,

            'position' => $record?->position
                ?: $fallbackPosition,

            'signature_path' =>
            $record?->signature_path,

            'signature_base64' =>
            $this->signatureBase64(
                $record?->signature_path
            ),
        ];
    }

    private function signatureBase64(
        ?string $path
    ): ?string {
        if (blank($path)) {
            return null;
        }

        $disk = Storage::disk('local');

        if (! $disk->exists($path)) {
            return null;
        }

        $contents = $disk->get($path);

        $mimeType = match (strtolower(
            pathinfo(
                $path,
                PATHINFO_EXTENSION
            )
        )) {
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'image/png',
        };

        return sprintf(
            'data:%s;base64,%s',
            $mimeType,
            base64_encode($contents)
        );
    }
}
