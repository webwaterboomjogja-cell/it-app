<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <title>Laporan Inventaris Aset IT</title>

    <style>
        @page {
            margin: 15mm 9mm 15mm 9mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
        }

        .company-header {
            width: 100%;
            margin-bottom: 8px;
            border-bottom: 2px solid #1f4e78;
            padding-bottom: 8px;
        }

        .company-header td {
            border: none;
            vertical-align: middle;
        }

        .logo-column {
            width: 85px;
            text-align: center;
        }

        .company-logo {
            max-width: 70px;
            max-height: 60px;
        }

        .company-name {
            margin: 0;
            color: #111827;
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .company-division {
            margin-top: 3px;
            font-size: 10px;
            font-weight: bold;
        }

        .company-contact {
            margin-top: 3px;
            color: #4b5563;
            font-size: 8px;
            line-height: 1.4;
        }

        .report-title {
            margin: 10px 0 5px;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .report-information {
            width: 100%;
            margin-bottom: 9px;
            border-collapse: collapse;
        }

        .report-information td {
            border: 1px solid #d1d5db;
            padding: 4px 6px;
            vertical-align: top;
        }

        .information-label {
            width: 115px;
            background: #f3f4f6;
            font-weight: bold;
        }

        .summary {
            margin-bottom: 7px;
            font-weight: bold;
        }

        .asset-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .asset-table thead {
            display: table-header-group;
        }

        .asset-table tr {
            page-break-inside: avoid;
        }

        .asset-table th {
            border: 1px solid #111827;
            background: #1f4e78;
            color: #ffffff;
            padding: 5px 3px;
            font-size: 7px;
            text-align: center;
            vertical-align: middle;
        }

        .asset-table td {
            border: 1px solid #9ca3af;
            padding: 4px 3px;
            font-size: 7px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .text-center {
            text-align: center;
        }

        .column-number {
            width: 3%;
        }

        .column-code {
            width: 9%;
        }

        .column-name {
            width: 15%;
        }

        .column-category {
            width: 10%;
        }

        .column-brand {
            width: 12%;
        }

        .column-serial {
            width: 11%;
        }

        .column-location {
            width: 10%;
        }

        .column-user {
            width: 11%;
        }

        .column-status {
            width: 7%;
        }

        .column-condition {
            width: 7%;
        }

        .column-date {
            width: 8%;
        }

        .empty-data {
            padding: 20px !important;
            text-align: center;
            font-style: italic;
        }

        .signature-section {
            width: 100%;
            margin-top: 18px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .signature-section td {
            width: 50%;
            border: none;
            text-align: center;
            vertical-align: top;
        }

        .signature-space {
            height: 55px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }

        .footer {
            position: fixed;
            right: 0;
            bottom: -9mm;
            left: 0;
            border-top: 1px solid #d1d5db;
            padding-top: 4px;
            color: #6b7280;
            font-size: 7px;
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
    @if (($documentStatus ?? 'draft') !== 'final')
        <div class="draft-watermark">
            DRAFT
        </div>
    @endif

    <div class="footer">
        <span class="footer-left">
            {{ $company['name'] ?? config('app.name') }}
            — Laporan Inventaris Aset IT
        </span>

        <span class="footer-right">
            Halaman <span class="page-number"></span>
        </span>
    </div>

    <table class="company-header">
        <tr>
            @if ($logoBase64)
                <td class="logo-column">
                    <img src="{{ $logoBase64 }}" alt="Logo Perusahaan" class="company-logo">
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
                        <br>Telepon: {{ $company['phone'] }}
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
        Laporan Data Inventaris Aset IT
    </div>

    <table class="report-information">
        <tr>
            <td class="information-label">
                Kategori aset
            </td>
            <td>
                {{ $filters['category'] }}
            </td>

            <td class="information-label">
                Lokasi
            </td>
            <td>
                {{ $filters['location'] }}
            </td>
        </tr>

        <tr>
            <td class="information-label">
                Status aset
            </td>
            <td>
                {{ $filters['status'] }}
            </td>

            <td class="information-label">
                Tanggal dibuat
            </td>
            <td>
                {{ $generatedAt->format('d/m/Y H:i') }}
            </td>
        </tr>

        <tr>
            <td class="information-label">
                Dibuat oleh
            </td>
            <td colspan="3">
                {{ $generatedBy }}
            </td>
        </tr>
    </table>

    <div class="summary">
        Total data: {{ number_format($assets->count(), 0, ',', '.') }}
        aset
    </div>

    <table class="asset-table">
        <thead>
            <tr>
                <th class="column-number">No.</th>
                <th class="column-code">Kode Aset</th>
                <th class="column-name">Nama Aset</th>
                <th class="column-category">Kategori</th>
                <th class="column-brand">Merek / Model</th>
                <th class="column-serial">Nomor Seri</th>
                <th class="column-location">Lokasi</th>
                <th class="column-user">Penanggung Jawab</th>
                <th class="column-status">Status</th>
                <th class="column-condition">Kondisi</th>
                <th class="column-date">Tgl. Pembelian</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($assets as $index => $asset)
                @php
                    $brandModel = collect([$asset->brand, $asset->model])
                        ->filter()
                        ->implode(' / ');
                @endphp

                <tr>
                    <td class="text-center">
                        {{ $index + 1 }}
                    </td>

                    <td>
                        {{ $asset->code ?: '-' }}
                    </td>

                    <td>
                        {{ $asset->name ?: '-' }}
                    </td>

                    <td>
                        {{ $asset->assetCategory?->name ?: '-' }}
                    </td>

                    <td>
                        {{ $brandModel ?: '-' }}
                    </td>

                    <td>
                        {{ $asset->serial_number ?: '-' }}
                    </td>

                    <td>
                        {{ $asset->location?->name ?: '-' }}
                    </td>

                    <td>
                        {{ $asset->responsibleUser?->name ?: '-' }}
                    </td>

                    <td class="text-center">
                        {{ filled($asset->status) ? str($asset->status)->headline() : '-' }}
                    </td>

                    <td class="text-center">
                        {{ filled($asset->condition) ? str($asset->condition)->headline() : '-' }}
                    </td>

                    <td class="text-center">
                        {{ filled($asset->purchase_date) ? \Carbon\Carbon::parse($asset->purchase_date)->format('d/m/Y') : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="empty-data">
                        Tidak ada data aset sesuai filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

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
