<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <title>
        Laporan Bulanan IT
        {{ $data['month_name'] }}
        {{ $data['year'] }}
    </title>

    <style>
        @page {
            margin: 13mm 9mm 15mm 9mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            line-height: 1.4;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .company-header {
            margin-bottom: 8px;
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
            font-size: 16px;
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
            margin: 8px 0 2px;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .report-period {
            margin-bottom: 8px;
            text-align: center;
            font-size: 9px;
        }

        .information-table {
            margin-bottom: 8px;
        }

        .information-table td {
            padding: 4px 6px;
            border: 1px solid #d1d5db;
        }

        .information-label {
            width: 15%;
            background: #f3f4f6;
            font-weight: bold;
        }

        .summary-table {
            margin-bottom: 9px;
            table-layout: fixed;
        }

        .summary-table td {
            padding: 6px 3px;
            border: 1px solid #d1d5db;
            background: #f8fafc;
            text-align: center;
            vertical-align: middle;
        }

        .summary-value {
            color: #1f4e78;
            font-size: 12px;
            font-weight: bold;
        }

        .summary-label {
            margin-top: 2px;
            color: #6b7280;
            font-size: 6px;
            text-transform: uppercase;
        }

        .section {
            margin-top: 11px;
        }

        .section-title {
            margin-bottom: 4px;
            padding: 5px 7px;
            background: #1f4e78;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
        }

        .data-table {
            table-layout: fixed;
        }

        .data-table thead {
            display: table-header-group;
        }

        .data-table tr {
            page-break-inside: avoid;
        }

        .data-table th {
            padding: 4px 3px;
            border: 1px solid #111827;
            background: #e5e7eb;
            font-size: 7px;
            text-align: center;
        }

        .data-table td {
            padding: 4px 3px;
            border: 1px solid #9ca3af;
            font-size: 7px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .text-center {
            text-align: center;
        }

        .empty-data {
            padding: 12px !important;
            color: #6b7280;
            text-align: center;
            font-style: italic;
        }

        .evaluation-box {
            min-height: 45px;
            padding: 7px;
            border: 1px solid #9ca3af;
            white-space: pre-line;
        }

        .signature-table {
            margin-top: 18px;
            page-break-inside: avoid;
        }

        .signature-table td {
            width: 33.33%;
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

        .page-break {
            page-break-before: always;
        }

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
        $overview = $data['overview'];
    @endphp

    @if (($documentStatus ?? 'draft') !== 'final')
        <div class="draft-watermark">
            DRAFT
        </div>
    @endif

    <div class="footer">
        <span class="footer-left">
            {{ $company['name'] ?? config('app.name') }}
            — Laporan Bulanan Divisi IT
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
                    {{ $company['address'] ?? '' }}

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
        Laporan Bulanan Divisi IT
    </div>

    <div class="report-period">
        {{ $data['month_name'] }}
        {{ $data['year'] }}
        — {{ $data['period'] }}
    </div>

    <table class="information-table">
        <tr>
            <td class="information-label">
                Status laporan
            </td>
            <td>{{ $data['status'] }}</td>

            <td class="information-label">
                Dibuat oleh
            </td>
            <td>{{ $data['generated_by'] }}</td>
        </tr>

        <tr>
            <td class="information-label">
                Disetujui oleh
            </td>
            <td>{{ $data['approved_by'] }}</td>

            <td class="information-label">
                Tanggal persetujuan
            </td>
            <td>{{ $data['approved_at'] }}</td>
        </tr>

        <tr>
            <td class="information-label">
                Diekspor oleh
            </td>
            <td>{{ $generatedBy }}</td>

            <td class="information-label">
                Tanggal export
            </td>
            <td>
                {{ $generatedAt->format('d/m/Y H:i') }}
            </td>
        </tr>
    </table>

    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-value">
                    {{ $overview['total_reports'] }}
                </div>
                <div class="summary-label">
                    Total Laporan
                </div>
            </td>

            <td>
                <div class="summary-value">
                    {{ $overview['completed'] }}
                </div>
                <div class="summary-label">
                    Selesai
                </div>
            </td>

            <td>
                <div class="summary-value">
                    {{ $overview['in_progress'] }}
                </div>
                <div class="summary-label">
                    Proses
                </div>
            </td>

            <td>
                <div class="summary-value">
                    {{ $overview['pending'] }}
                </div>
                <div class="summary-label">
                    Tertunda
                </div>
            </td>

            <td>
                <div class="summary-value">
                    {{ $overview['urgent'] }}
                </div>
                <div class="summary-label">
                    Urgent
                </div>
            </td>

            <td>
                <div class="summary-value">
                    {{ $overview['completion_percentage'] }}%
                </div>
                <div class="summary-label">
                    Penyelesaian
                </div>
            </td>
        </tr>
    </table>

    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-value">
                    {{ $overview['duration_label'] }}
                </div>
                <div class="summary-label">
                    Total Durasi
                </div>
            </td>

            <td>
                <div class="summary-value">
                    {{ $overview['total_assets'] }}
                </div>
                <div class="summary-label">
                    Total Aset
                </div>
            </td>

            <td>
                <div class="summary-value">
                    {{ $overview['damaged_assets'] }}
                </div>
                <div class="summary-label">
                    Aset Rusak
                </div>
            </td>

            <td>
                <div class="summary-value">
                    {{ $overview['maintenance_assets'] }}
                </div>
                <div class="summary-label">
                    Maintenance
                </div>
            </td>

            <td>
                <div class="summary-value">
                    {{ $overview['total_schedules'] }}
                </div>
                <div class="summary-label">
                    Total Jadwal
                </div>
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">
            Rekap Laporan Per Staff
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Staff IT</th>
                    <th>Total</th>
                    <th>Selesai</th>
                    <th>Proses</th>
                    <th>Tertunda</th>
                    <th>Urgent</th>
                    <th>Durasi</th>
                    <th>Penyelesaian</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($data['staff'] as $row)
                    <tr>
                        <td class="text-center">
                            {{ $row['number'] }}
                        </td>
                        <td>{{ $row['name'] }}</td>
                        <td class="text-center">
                            {{ $row['total'] }}
                        </td>
                        <td class="text-center">
                            {{ $row['completed'] }}
                        </td>
                        <td class="text-center">
                            {{ $row['in_progress'] }}
                        </td>
                        <td class="text-center">
                            {{ $row['pending'] }}
                        </td>
                        <td class="text-center">
                            {{ $row['urgent'] }}
                        </td>
                        <td class="text-center">
                            {{ $row['duration_label'] }}
                        </td>
                        <td class="text-center">
                            {{ $row['completion_percentage'] }}%
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="empty-data">
                            Data rekap staff belum tersedia.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">
            Rekap Laporan Per Kategori
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Kategori</th>
                    <th>Total</th>
                    <th>Selesai</th>
                    <th>Proses</th>
                    <th>Tertunda</th>
                    <th>Total Durasi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($data['categories'] as $row)
                    <tr>
                        <td class="text-center">
                            {{ $row['number'] }}
                        </td>
                        <td>{{ $row['name'] }}</td>
                        <td class="text-center">
                            {{ $row['total'] }}
                        </td>
                        <td class="text-center">
                            {{ $row['completed'] }}
                        </td>
                        <td class="text-center">
                            {{ $row['in_progress'] }}
                        </td>
                        <td class="text-center">
                            {{ $row['pending'] }}
                        </td>
                        <td class="text-center">
                            {{ $row['duration_label'] }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-data">
                            Data kategori belum tersedia.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">
            Rekap Status dan Prioritas
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Jenis Rekap</th>
                    <th>Nama</th>
                    <th>Jumlah</th>
                    <th>Persentase</th>
                </tr>
            </thead>

            <tbody>
                @foreach ([...$data['status_rows'], ...$data['priority_rows']] as $row)
                    <tr>
                        <td>{{ $row['type'] }}</td>
                        <td>{{ $row['name'] }}</td>
                        <td class="text-center">
                            {{ $row['total'] }}
                        </td>
                        <td class="text-center">
                            {{ $row['percentage'] }}%
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section page-break">
        <div class="section-title">
            Rekap Aset Bermasalah
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Kode Aset</th>
                    <th>Nama Aset</th>
                    <th>Status / Kondisi</th>
                    <th>Jumlah Masalah</th>
                    <th>Kendala Terakhir</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($data['problematic_assets']
                    as $row)
                    <tr>
                        <td class="text-center">
                            {{ $row['number'] }}
                        </td>
                        <td>{{ $row['code'] }}</td>
                        <td>{{ $row['name'] }}</td>
                        <td class="text-center">
                            {{ $row['status'] }}
                        </td>
                        <td class="text-center">
                            {{ $row['problem_count'] }}
                        </td>
                        <td>{{ $row['last_problem'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-data">
                            Tidak ada aset bermasalah
                            pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">
            Rekap Jadwal Per Staff
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Staff IT</th>
                    <th>Kerja</th>
                    <th>Maintenance</th>
                    <th>Cuti / DP</th>
                    <th>Ijin</th>
                    <th>Total</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($data['schedule_staff']
                    as $row)
                    <tr>
                        <td class="text-center">
                            {{ $row['number'] }}
                        </td>
                        <td>{{ $row['name'] }}</td>
                        <td class="text-center">
                            {{ $row['work'] }}
                        </td>
                        <td class="text-center">
                            {{ $row['maintenance'] }}
                        </td>
                        <td class="text-center">
                            {{ $row['leave'] }}
                        </td>
                        <td class="text-center">
                            {{ $row['permission'] }}
                        </td>
                        <td class="text-center">
                            {{ $row['total'] }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-data">
                            Data jadwal belum tersedia.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">
            Evaluasi Bulanan
        </div>

        <div class="evaluation-box">
            {{ $data['evaluation'] }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            Rekomendasi Bulan Berikutnya
        </div>

        <div class="evaluation-box">
            {{ $data['recommendations'] }}
        </div>
    </div>

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
