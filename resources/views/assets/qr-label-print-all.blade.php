<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Semua Label QR Aset IT</title>

    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --label-width: 80mm;
            --label-height: 28mm;
            --logo-size: 16mm;
            --qr-size: 19mm;

            --primary: #0f172a;
            --accent: #2563eb;
            --accent-soft: #dbeafe;
            --text: #111827;
            --muted: #6b7280;
            --border: #d1d5db;
            --surface: #ffffff;
            --page-bg: #f3f4f6;
            --strip: #4b5563;
            --strip-dark: #374151;
        }

        body {
            margin: 0;
            padding: 24px;
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Arial,
                sans-serif;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, .08), transparent 32%),
                linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
            color: var(--text);
        }

        .page-shell {
            max-width: 1180px;
            margin: 0 auto;
        }

        .toolbar {
            position: sticky;
            top: 16px;
            z-index: 20;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 22px;
            padding: 16px 18px;
            background: rgba(255, 255, 255, .95);
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            box-shadow: 0 16px 38px rgba(15, 23, 42, 0.10);
            backdrop-filter: blur(12px);
        }

        .toolbar-title {
            font-size: 18px;
            font-weight: 900;
            margin: 0;
            letter-spacing: -0.03em;
            color: var(--primary);
        }

        .toolbar-subtitle {
            margin-top: 4px;
            font-size: 13px;
            color: var(--muted);
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .summary-pill {
            padding: 9px 13px;
            border-radius: 999px;
            background: var(--accent-soft);
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 800;
        }

        .btn {
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 10px 15px;
            border-radius: 12px;
            font-weight: 850;
            cursor: pointer;
            box-shadow: 0 12px 24px rgba(37, 99, 235, .25);
        }

        .labels-grid {
            display: grid;
            grid-template-columns: repeat(2, var(--label-width));
            gap: 7mm;
            justify-content: center;
        }

        .asset-label {
            position: relative;
            width: var(--label-width);
            min-height: var(--label-height);
            display: grid;
            grid-template-columns: 18mm 1fr 22mm;

            gap: 2.5mm;
            align-items: center;
            padding: 2mm;
            background: var(--surface);
            border: 1.2px solid #111827;
            border-radius: 2mm;
            overflow: hidden;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .asset-label::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 1.4mm;
            background: linear-gradient(180deg, #2563eb, #0ea5e9, #22c55e);
        }

        .logo-box {
            height: 100%;
            display: grid;
            place-items: center;
            padding-left: .8mm;
            border-radius: 1.5mm;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .logo-box img {
            width: var(--logo-size);
            height: var(--logo-size);
            object-fit: contain;
            display: block;
        }

        .asset-info {
            display: grid;
            grid-template-rows: 1fr 1fr;
            gap: 2mm;
            min-width: 0;
        }

        .info-row {
            min-height: 9mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1mm 2mm;
            border-radius: 1.4mm;
            background: linear-gradient(135deg, var(--strip), var(--strip-dark));
            color: #ffffff;
            text-align: center;
            line-height: 1.05;
            overflow: hidden;
        }

        .row-label {
            margin-bottom: .6mm;
            font-size: 5.5pt;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .68);
        }

        .asset-name {
            max-width: 100%;
            font-size: 7.4pt;
            font-weight: 900;
            letter-spacing: -0.01em;
            line-height: 1.15;
            white-space: normal;
            overflow: visible;
            text-overflow: unset;
            word-break: break-word;
        }

        .asset-serial {
            max-width: 100%;
            font-size: 7pt;
            font-weight: 850;
            letter-spacing: .01em;
            line-height: 1.15;
            white-space: normal;
            overflow: visible;
            text-overflow: unset;
            word-break: break-word;
        }

        .qr-box {
            height: 100%;
            display: grid;
            grid-template-rows: 1fr auto;
            place-items: center;
            padding: 1.2mm;
            border-radius: 1.5mm;
            background: #ffffff;
            border: 1.1px solid #111827;
            overflow: hidden;
        }

        .qr {
            width: var(--qr-size);
            height: var(--qr-size);
            display: grid;
            place-items: center;
        }

        .qr svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .qr-caption {
            margin-top: .5mm;
            font-size: 4.8pt;
            font-weight: 800;
            letter-spacing: .08em;
            color: #374151;
            text-transform: uppercase;
            line-height: 1;
        }

        .empty {
            padding: 40px;
            background: white;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            text-align: center;
            color: var(--muted);
        }

        @page {
            size: A4;
            margin: 10mm;
        }

        @media print {
            body {
                padding: 0;
                background: #ffffff;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .page-shell {
                max-width: none;
                margin: 0;
            }

            .toolbar {
                display: none;
            }

            .labels-grid {
                grid-template-columns: repeat(2, var(--label-width));
                gap: 6mm;
                justify-content: start;
            }

            .asset-label {
                box-shadow: none;
            }
        }

        @media screen and (max-width: 760px) {
            body {
                padding: 16px;
            }

            .toolbar {
                position: static;
                flex-direction: column;
                align-items: stretch;
            }

            .toolbar-actions {
                justify-content: stretch;
            }

            .summary-pill,
            .btn {
                width: 100%;
                text-align: center;
            }

            .labels-grid {
                grid-template-columns: 1fr;
            }

            .asset-label {
                width: 100%;
                max-width: 370px;
                margin: 0 auto;
            }
        }
    </style>
</head>

<body>
    @php
        $logoUrl = asset('img/new-logo.webp');
    @endphp

    <div class="page-shell">
        <div class="toolbar">
            <div>
                <h1 class="toolbar-title">
                    Print Semua Label QR Aset IT
                </h1>
                <div class="toolbar-subtitle">
                    Format label inventaris horizontal: logo, nama aset, nomor seri, dan QR Code.
                </div>
            </div>

            <div class="toolbar-actions">
                <div class="summary-pill">
                    Total: {{ $assets->count() }} aset
                </div>

                <button class="btn" onclick="window.print()">
                    🖨 Print Semua Label
                </button>
            </div>
        </div>

        @if ($assets->isEmpty())
            <div class="empty">
                Belum ada aset yang memiliki QR Token.
            </div>
        @else
            <div class="labels-grid">
                @foreach ($assets as $asset)
                    <div class="asset-label">
                        <div class="logo-box">
                            <img src="{{ $logoUrl }}" alt="Waterboom Jogja">
                        </div>

                        <div class="asset-info">
                            <div class="info-row">
                                <div class="row-label">
                                    Nama Aset
                                </div>
                                <div class="asset-name">
                                    {{ $asset->name }}
                                </div>
                            </div>

                            <div class="info-row">
                                <div class="row-label">
                                    Nomor Seri
                                </div>
                                <div class="asset-serial">
                                    {{ $asset->serial_number ?: $asset->code }}
                                </div>
                            </div>
                        </div>

                        <div class="qr-box">
                            <div class="qr">
                                {!! QrCode::size(90)->margin(1)->generate(route('asset-it.scan', $asset->qr_token)) !!}
                            </div>

                            <div class="qr-caption">
                                Scan
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</body>

</html>
