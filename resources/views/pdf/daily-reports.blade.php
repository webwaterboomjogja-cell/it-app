<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <title>Laporan Harian Divisi IT</title>

    <style>
        @page {
            margin: 12mm 10mm 15mm 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            line-height: 1.45;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .company-header {
            margin-bottom: 7px;
            border-bottom: 2px solid #1f4e78;
        }

        .company-header td {
            padding-bottom: 7px;
            border: none;
            vertical-align: middle;
        }

        .logo-column {
            width: 75px;
            text-align: center;
        }

        .company-logo {
            max-width: 62px;
            max-height: 55px;
        }

        .company-name {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .company-division {
            margin-top: 2px;
            font-size: 9px;
            font-weight: bold;
        }

        .company-contact {
            margin-top: 2px;
            color: #4b5563;
            font-size: 7px;
        }

        .report-title {
            margin: 8px 0;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .filter-table {
            margin-bottom: 7px;
        }

        .filter-table td {
            padding: 4px 5px;
            border: 1px solid #d1d5db;
            vertical-align: top;
        }

        .filter-label {
            width: 11%;
            background: #f3f4f6;
            font-weight: bold;
        }

        .filter-value {
            width: 22.33%;
        }

        /*
        |--------------------------------------------------------------------------
        | Statistik
        |--------------------------------------------------------------------------
        */

        .statistics {
            margin-bottom: 9px;
            table-layout: fixed;
            page-break-inside: avoid;
        }

        .statistics td {
            height: 42px;
            padding: 5px 3px;
            border: 1px solid #d1d5db;
            background: #f8fafc;
            text-align: center;
            vertical-align: middle;
        }

        .stat-value {
            display: block;
            color: #1f4e78;
            font-size: 11px;
            font-weight: bold;
        }

        .stat-label {
            display: block;
            margin-top: 2px;
            color: #6b7280;
            font-size: 6px;
            text-transform: uppercase;
        }

        /*
        |--------------------------------------------------------------------------
        | Detail laporan
        |--------------------------------------------------------------------------
        */

        .report-item {
            margin-bottom: 10px;
            border: 1px solid #9ca3af;
            page-break-inside: avoid;
        }

        .report-item-header {
            table-layout: fixed;
        }

        .report-item-header td {
            padding: 5px 6px;
            border: none;
            background: #1f4e78;
            color: #ffffff;
            vertical-align: middle;
        }

        .report-number {
            width: 38px;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
        }

        .report-heading {
            font-size: 9px;
            font-weight: bold;
        }

        .report-status {
            width: 105px;
            text-align: center;
        }

        .report-meta {
            table-layout: fixed;
        }

        .report-meta th,
        .report-meta td {
            padding: 4px 5px;
            border: 1px solid #d1d5db;
            vertical-align: top;
        }

        .report-meta th {
            width: 13%;
            background: #f3f4f6;
            text-align: left;
            font-weight: bold;
        }

        .report-meta td {
            width: 20.33%;
        }

        .content-section {
            padding: 6px;
            border-top: 1px solid #d1d5db;
        }

        .section-title {
            margin-bottom: 2px;
            color: #1f4e78;
            font-size: 8px;
            font-weight: bold;
        }

        .section-content {
            white-space: pre-line;
        }

        .problem-table {
            table-layout: fixed;
        }

        .problem-table th,
        .problem-table td {
            padding: 5px 6px;
            border: 1px solid #d1d5db;
            vertical-align: top;
        }

        .problem-table th {
            width: 15%;
            background: #f3f4f6;
            text-align: left;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 5px;
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: 3px;
            font-size: 7px;
        }

        .empty-data {
            padding: 25px;
            border: 1px solid #d1d5db;
            text-align: center;
            font-style: italic;
        }

        /*
        |--------------------------------------------------------------------------
        | Tanda tangan
        |--------------------------------------------------------------------------
        */

        .signature-table {
            margin-top: 16px;
            page-break-inside: avoid;
        }

        .signature-table td {
            width: 50%;
            border: none;
            text-align: center;
            vertical-align: top;
        }

        .signature-space {
            height: 45px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }

        /*
        |--------------------------------------------------------------------------
        | Footer
        |--------------------------------------------------------------------------
        */

        .footer {
            position: fixed;
            right: 0;
            bottom: -10mm;
            left: 0;
            padding-top: 3px;
            border-top: 1px solid #d1d5db;
            color: #6b7280;
            font-size: 6px;
        }

        .footer-left {
            float: left;
        }

        .footer-right {
            float: right;
        }

        .page-number:before {
            content: counter(page);
        }


        .draft-watermark {
            position: fixed;
            top: 42%;
            left: 12%;
            z-index: -1000;

            color: rgba(156, 163, 175, 0.15);

            font-size: 90px;
            font-weight: bold;

            transform: rotate(-30deg);
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    @php
        /*
        |--------------------------------------------------------------------------
        | Helper membersihkan RichEditor
        |--------------------------------------------------------------------------
        */

        $plainText = function ($value): string {
            if (blank($value)) {
                return '-';
            }

            $text = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            $text = strip_tags($text);

            $text = str_replace(["\u{00A0}", '&nbsp;'], ' ', $text);

            return trim(preg_replace('/[ \t]+/', ' ', $text));
        };

        /*
        |--------------------------------------------------------------------------
        | Helper waktu
        |--------------------------------------------------------------------------
        */

        $formatTime = function ($value): string {
            if (blank($value)) {
                return '-';
            }

            $time = trim(explode(',', (string) $value)[0]);

            foreach (['H:i:s', 'H:i'] as $format) {
                try {
                    return \Carbon\Carbon::createFromFormat($format, $time)->format('H:i');
                } catch (\Throwable) {
                    // Coba format berikutnya.
                }
            }

            return $time;
        };

        $formatDuration = function ($minutes): string {
            return app(\App\Services\Exports\DailyReportExportService::class)->formatDuration(
                is_numeric($minutes) ? (int) $minutes : null,
            );
        };

        $headline = function ($value): string {
            return filled($value) ? str((string) $value)->headline()->toString() : '-';
        };
    @endphp

    @if (($documentStatus ?? 'draft') !== 'final')
        <div class="draft-watermark">
            DRAFT
        </div>
    @endif

    <div class="footer">
        <span class="footer-left">
            {{ $company['name'] ?? config('app.name') }}
            - Laporan Harian Divisi IT
        </span>

        <span class="footer-right">
            Halaman <span class="page-number"></span>
        </span>
    </div>

    <table class="company-header">
        <tr>
            @if ($logoBase64)
                <td class="logo-column">
                    <img src="{{ $logoBase64 }}" class="company-logo" alt="Logo Perusahaan">
                </td>
            @endif

            <td>
                <div class="company-name">
                    {{ $company['name'] ?? config('app.name') }}
                </div>

                @if (!empty($company['division']))
                    <div class="company-division">
                        {{ $company['division'] }}
                    </div>
                @endif

                <div class="company-contact">
                    @if (!empty($company['address']))
                        {{ $company['address'] }}
                    @endif

                    @if (!empty($company['phone']))
                        <br>
                        Telepon: {{ $company['phone'] }}
                    @endif

                    @if (!empty($company['email']))
                        |
                        Email: {{ $company['email'] }}
                    @endif
                </div>
            </td>
        </tr>

        <tr>
            <td class="information-label">
                Nomor dokumen
            </td>

            <td>
                {{ $documentNumber ?? '-' }}
            </td>

            <td class="information-label">
                Status dokumen
            </td>

            <td>
                {{ strtoupper($documentStatus ?? 'draft') }}
            </td>
        </tr>
    </table>

    <div class="report-title">
        Laporan Harian Divisi IT
    </div>

    <table class="filter-table">
        <tr>
            <td class="filter-label">Periode</td>
            <td class="filter-value">
                {{ $filters['period'] }}
            </td>

            <td class="filter-label">Staff IT</td>
            <td class="filter-value">
                {{ $filters['staff'] }}
            </td>

            <td class="filter-label">Kategori</td>
            <td class="filter-value">
                {{ $filters['category'] }}
            </td>
        </tr>

        <tr>
            <td class="filter-label">Status</td>
            <td class="filter-value">
                {{ $filters['work_status'] }}
            </td>

            <td class="filter-label">Review</td>
            <td class="filter-value">
                {{ $filters['review_status'] }}
            </td>

            <td class="filter-label">Prioritas</td>
            <td class="filter-value">
                {{ $filters['priority'] }}
            </td>
        </tr>

        <tr>
            <td class="filter-label">Lokasi</td>
            <td class="filter-value">
                {{ $filters['location'] }}
            </td>

            <td class="filter-label">Durasi</td>
            <td class="filter-value">
                {{ $filters['duration'] }}
            </td>

            <td class="filter-label">Aset</td>
            <td class="filter-value">
                {{ $filters['asset'] }}
            </td>
        </tr>

        <tr>
            <td class="filter-label">Dibuat</td>
            <td colspan="2">
                {{ $generatedAt->format('d/m/Y H:i') }}
            </td>

            <td class="filter-label">Dibuat oleh</td>
            <td colspan="2">
                {{ $generatedBy }}
            </td>
        </tr>
    </table>

    <table class="statistics">
        <tr>
            <td>
                <span class="stat-value">
                    {{ $statistics['total'] }}
                </span>
                <span class="stat-label">Total Laporan</span>
            </td>

            <td>
                <span class="stat-value">
                    {{ $statistics['completed'] }}
                </span>
                <span class="stat-label">Selesai</span>
            </td>

            <td>
                <span class="stat-value">
                    {{ $statistics['in_progress'] }}
                </span>
                <span class="stat-label">Proses</span>
            </td>

            <td>
                <span class="stat-value">
                    {{ $statistics['pending'] }}
                </span>
                <span class="stat-label">Tertunda</span>
            </td>

            <td>
                <span class="stat-value">
                    {{ $statistics['urgent'] }}
                </span>
                <span class="stat-label">Urgent</span>
            </td>

            <td>
                <span class="stat-value">
                    {{ $statistics['total_duration_label'] }}
                </span>
                <span class="stat-label">Total Durasi</span>
            </td>
        </tr>
    </table>

    @forelse ($reports as $index => $report)
        @php
            $assetLabel = $report->asset
                ? collect([$report->asset->code, $report->asset->name])
                    ->filter()
                    ->implode(' - ')
                : '-';
        @endphp

        <div class="report-item">
            <table class="report-item-header">
                <tr>
                    <td class="report-number">
                        {{ $index + 1 }}
                    </td>

                    <td>
                        <div class="report-heading">
                            {{ $plainText($report->title) }}
                        </div>
                    </td>

                    <td class="report-status">
                        <span class="status-badge">
                            {{ $headline($report->work_status) }}
                        </span>
                    </td>
                </tr>
            </table>

            <table class="report-meta">
                <tr>
                    <th>Tanggal</th>
                    <td>
                        {{ filled($report->report_date) ? \Carbon\Carbon::parse($report->report_date)->format('d/m/Y') : '-' }}
                    </td>

                    <th>Staff IT</th>
                    <td>
                        {{ $report->user?->name ?: '-' }}
                    </td>

                    <th>Kategori</th>
                    <td>
                        {{ $report->category?->name ?: '-' }}
                    </td>
                </tr>

                <tr>
                    <th>Prioritas</th>
                    <td>
                        {{ $headline($report->priority) }}
                    </td>

                    <th>Lokasi</th>
                    <td>
                        {{ $plainText($report->location) }}
                    </td>

                    <th>Aset</th>
                    <td>
                        {{ $assetLabel }}
                    </td>
                </tr>

                <tr>
                    <th>Jam Mulai</th>
                    <td>
                        {{ $formatTime($report->start_time) }}
                    </td>

                    <th>Jam Selesai</th>
                    <td>
                        {{ $formatTime($report->end_time) }}
                    </td>

                    <th>Durasi</th>
                    <td>
                        {{ $formatDuration($report->duration_minutes) }}
                    </td>
                </tr>

                <tr>
                    <th>Status Review</th>
                    <td>
                        {{ $headline($report->review_status) }}
                    </td>

                    <th>Reviewer</th>
                    <td>
                        {{ $report->reviewer?->name ?: '-' }}
                    </td>

                    <th>Catatan Review</th>
                    <td>
                        {{ $plainText($report->review_note) }}
                    </td>
                </tr>
            </table>

            <div class="content-section">
                <div class="section-title">
                    Deskripsi Pekerjaan
                </div>

                <div class="section-content">
                    {{ $plainText($report->description) }}
                </div>
            </div>

            <table class="problem-table">
                <tr>
                    <th>Kendala</th>
                    <td>
                        {{ $plainText($report->obstacle) }}
                    </td>
                </tr>

                <tr>
                    <th>Solusi</th>
                    <td>
                        {{ $plainText($report->solution) }}
                    </td>
                </tr>
            </table>
        </div>
    @empty
        <div class="empty-data">
            Tidak ada laporan harian yang sesuai dengan filter.
        </div>
    @endforelse

    @php
        $prepared = $signatories['prepared_by'] ?? [];

        $reviewed = $signatories['reviewed_by'] ?? [];

        $approved = $signatories['approved_by'] ?? [];
    @endphp

    <table class="signature-table">
        <tr>
            <td>
                Dibuat oleh,
                <br>
                {{ $prepared['position'] ?? 'Staff IT' }}

                <div class="signature-space">
                    @if (!empty($prepared['signature_base64']))
                        <img src="{{ $prepared['signature_base64'] }}"
                            style="
                            max-height: 45px;
                            max-width: 110px;
                        "
                            alt="Tanda tangan">
                    @endif
                </div>

                <div class="signature-name">
                    {{ $prepared['name'] ?? $generatedBy }}
                </div>
            </td>

            <td>
                Diperiksa oleh,
                <br>
                {{ $reviewed['position'] ?? 'Kepala Divisi IT' }}

                <div class="signature-space">
                    @if (!empty($reviewed['signature_base64']))
                        <img src="{{ $reviewed['signature_base64'] }}"
                            style="
                            max-height: 45px;
                            max-width: 110px;
                        "
                            alt="Tanda tangan">
                    @endif
                </div>

                <div class="signature-name">
                    {{ filled($reviewed['name'] ?? null) ? $reviewed['name'] : '____________________' }}
                </div>
            </td>

            <td>
                Disetujui oleh,
                <br>
                {{ $approved['position'] ?? 'Pimpinan' }}

                <div class="signature-space">
                    @if (!empty($approved['signature_base64']))
                        <img src="{{ $approved['signature_base64'] }}"
                            style="
                            max-height: 45px;
                            max-width: 110px;
                        "
                            alt="Tanda tangan">
                    @endif
                </div>

                <div class="signature-name">
                    {{ filled($approved['name'] ?? null) ? $approved['name'] : '____________________' }}
                </div>
            </td>
        </tr>
    </table>
    
</body>

</html>
