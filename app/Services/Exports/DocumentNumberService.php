<?php

namespace App\Services\Exports;

use App\Models\Documentsequence;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DocumentNumberService
{
    public function generate(
        string $reportType
    ): string {
        $code = $this->typeCode(
            $reportType
        );

        $year = (int) now()->year;
        $month = (int) now()->month;

        return DB::transaction(
            function () use (
                $reportType,
                $code,
                $year,
                $month
            ): string {
                

                Documentsequence::query()
                    ->firstOrCreate(
                        [
                            'document_type' =>
                                $reportType,

                            'year' => $year,
                            'month' => $month,
                        ],
                        [
                            'last_number' => 0,
                        ]
                    );

                /*
                |--------------------------------------------------------------------------
                | Kunci row agar nomor tidak ganda
                |--------------------------------------------------------------------------
                */

                $sequence = Documentsequence::query()
                    ->where(
                        'document_type',
                        $reportType
                    )
                    ->where('year', $year)
                    ->where('month', $month)
                    ->lockForUpdate()
                    ->firstOrFail();

                $sequence->increment(
                    'last_number'
                );

                $sequence->refresh();

                return sprintf(
                    'IT/%s/%04d/%02d/%04d',
                    $code,
                    $year,
                    $month,
                    $sequence->last_number
                );
            },
            3
        );
    }

    private function typeCode(
        string $reportType
    ): string {
        return match ($reportType) {
            'assets' => 'AST',

            'daily_reports' => 'DLY',

            'monthly_reports' => 'MON',

            default => throw new InvalidArgumentException(
                'Jenis dokumen tidak valid.'
            ),
        };
    }
}