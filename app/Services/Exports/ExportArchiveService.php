<?php

namespace App\Services\Exports;

use App\Models\Exporthistory;
use App\Models\Monthlyitreport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ExportArchiveService
{
    public function __construct(
        private readonly DocumentNumberService
            $documentNumberService,

        private readonly ReportSignatoryService
            $signatoryService
    ) {
    }

    /**
     * Membuat riwayat awal sebelum file diproses.
     */
    public function start(
        string $reportType,
        string $format,
        array $filters,
        string $generatedBy,
        ?Monthlyitreport $monthlyReport = null
    ): Exporthistory {
        return Exporthistory::query()->create([
            'user_id' => auth()->id(),

            'monthly_it_report_id' =>
                $monthlyReport?->id,

            'document_number' =>
                $this->documentNumberService
                    ->generate($reportType),

            'report_type' => $reportType,
            'format' => $format,

            'generation_status' =>
                'processing',

            /*
            |--------------------------------------------------------------------------
            | Laporan bulanan final mengikuti status sumber
            |--------------------------------------------------------------------------
            */

            'document_status' =>
                $this->initialDocumentStatus(
                    $reportType,
                    $monthlyReport
                ),

            'filters' => $filters,

            'signatories' =>
                $this->signatoryService
                    ->snapshot($generatedBy),

            'disk' => 'reports',
        ]);
    }

    /**
     * Simpan file ke storage private.
     */
    public function complete(
        Exporthistory $history,
        string $contents,
        string $filename
    ): Exporthistory {
        return DB::transaction(
            function () use (
                $history,
                $contents,
                $filename
            ): Exporthistory {
                $directory = sprintf(
                    '%s/%s/%s',
                    $history->report_type,
                    now()->format('Y'),
                    now()->format('m')
                );

                $safeNumber = str_replace(
                    '/',
                    '-',
                    $history->document_number
                );

                $storedFilename = sprintf(
                    '%s-%s.%s',
                    strtolower($history->report_type),
                    $safeNumber,
                    $history->format
                );

                $path = "{$directory}/{$storedFilename}";

                Storage::disk(
                    $history->disk
                )->put(
                    $path,
                    $contents
                );

                $history->update([
                    'generation_status' =>
                        'completed',

                    'file_path' => $path,

                    'original_filename' =>
                        $filename,

                    'file_size' =>
                        strlen($contents),

                    'checksum' =>
                        hash(
                            'sha256',
                            $contents
                        ),

                    'generated_at' => now(),
                    'error_message' => null,
                ]);

                return $history->refresh();
            }
        );
    }

    public function fail(
        Exporthistory $history,
        Throwable|string $error
    ): void {
        $message = $error instanceof Throwable
            ? $error->getMessage()
            : $error;

        $history->update([
            'generation_status' => 'failed',
            'error_message' => mb_substr(
                $message,
                0,
                5000
            ),
        ]);
    }

    public function finalize(
        Exporthistory $history
    ): Exporthistory {
        if (! $history->isCompleted()) {
            throw new \RuntimeException(
                'Dokumen belum selesai dibuat.'
            );
        }

        if (! $history->fileExists()) {
            throw new \RuntimeException(
                'File arsip tidak ditemukan.'
            );
        }

        if ($history->isFinal()) {
            return $history;
        }

        

        if (
            $history->report_type ===
                'monthly_reports' &&
            $history->monthlyItReport &&
            ! in_array(
                $history->monthlyItReport->status,
                [
                    'final',
                    'finalized',
                    'selesai',
                ],
                true
            )
        ) {
            throw new \RuntimeException(
                'Laporan bulanan harus difinalisasi terlebih dahulu.'
            );
        }

        $history->update([
            'document_status' => 'final',
            'finalized_by' => auth()->id(),
            'finalized_at' => now(),
        ]);

        return $history->refresh();
    }

    private function initialDocumentStatus(
        string $reportType,
        ?Monthlyitreport $report
    ): string {
        if (
            $reportType === 'monthly_reports' &&
            $report &&
            in_array(
                $report->status,
                [
                    'final',
                    'finalized',
                    'selesai',
                ],
                true
            )
        ) {
            return 'final';
        }

        return 'draft';
    }
}