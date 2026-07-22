<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Semua QR Code Aset IT</title>

    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
            --success-bg: #dcfce7;
            --success-text: #166534;
            --warning-bg: #fef3c7;
            --warning-text: #92400e;
            --danger-bg: #fee2e2;
            --danger-text: #991b1b;
            --gray-bg: #e5e7eb;
            --gray-text: #374151;
        }

        body {
            margin: 0;
            padding: 28px;
            font-family: Arial, Helvetica, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, .12), transparent 32%),
                linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
            color: var(--slate-900);
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
            gap: 18px;
            margin-bottom: 24px;
            padding: 18px 20px;
            background: rgba(255, 255, 255, .92);
            border: 1px solid rgba(226, 232, 240, .9);
            border-radius: 22px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, .10);
            backdrop-filter: blur(14px);
        }

        .toolbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .toolbar-icon {
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #ffffff;
            font-size: 22px;
            font-weight: 900;
            box-shadow: 0 10px 22px rgba(37, 99, 235, .28);
        }

        .toolbar-title {
            margin: 0;
            font-size: 19px;
            font-weight: 900;
            letter-spacing: -0.02em;
            color: var(--slate-900);
        }

        .toolbar-subtitle {
            margin-top: 4px;
            font-size: 13px;
            color: var(--slate-500);
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .summary-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 13px;
            border-radius: 999px;
            background: var(--slate-100);
            border: 1px solid var(--slate-200);
            color: var(--slate-700);
            font-size: 12px;
            font-weight: 800;
        }

        .btn {
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 11px 16px;
            border-radius: 13px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 12px 24px rgba(37, 99, 235, .28);
        }

        .btn:hover {
            filter: brightness(.98);
            transform: translateY(-1px);
        }

        .labels-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .label-card {
            position: relative;
            overflow: hidden;
            min-height: 300px;
            padding: 14px;
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: 20px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, .08);
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .label-card::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 6px;
            background: linear-gradient(90deg, var(--primary), #38bdf8);
        }

        .label-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            padding-top: 6px;
            margin-bottom: 10px;
        }

        .label-brand {
            text-align: left;
        }

        .label-kicker {
            font-size: 9px;
            font-weight: 900;
            letter-spacing: .12em;
            color: var(--primary);
            text-transform: uppercase;
        }

        .label-title {
            margin-top: 3px;
            font-size: 13px;
            font-weight: 900;
            color: var(--slate-900);
            line-height: 1.25;
        }

        .label-type {
            flex: 0 0 auto;
            padding: 5px 8px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .qr-panel {
            display: grid;
            grid-template-columns: 124px 1fr;
            gap: 12px;
            align-items: center;
            padding: 11px;
            border-radius: 16px;
            background:
                linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid var(--slate-200);
        }

        .qr {
            width: 118px;
            height: 118px;
            display: grid;
            place-items: center;
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: 14px;
            padding: 6px;
        }

        .qr svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .asset-main {
            min-width: 0;
            text-align: left;
        }

        .code {
            display: inline-flex;
            align-items: center;
            padding: 6px 9px;
            border-radius: 10px;
            background: var(--slate-900);
            color: #ffffff;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .02em;
        }

        .name {
            margin-top: 8px;
            font-size: 13px;
            font-weight: 900;
            color: var(--slate-900);
            line-height: 1.35;
            word-break: break-word;
        }

        .status-row {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 7px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 900;
            line-height: 1;
        }

        .badge-success {
            background: var(--success-bg);
            color: var(--success-text);
        }

        .badge-warning {
            background: var(--warning-bg);
            color: var(--warning-text);
        }

        .badge-danger {
            background: var(--danger-bg);
            color: var(--danger-text);
        }

        .badge-gray {
            background: var(--gray-bg);
            color: var(--gray-text);
        }

        .details {
            display: grid;
            gap: 6px;
            margin-top: 12px;
            padding: 10px 11px;
            border-radius: 14px;
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
        }

        .detail-row {
            display: grid;
            grid-template-columns: 68px 1fr;
            gap: 8px;
            align-items: start;
            font-size: 10.5px;
            line-height: 1.35;
        }

        .detail-label {
            color: var(--slate-500);
            font-weight: 800;
        }

        .detail-value {
            color: var(--slate-800);
            font-weight: 800;
            word-break: break-word;
        }

        .label-footer {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-top: 10px;
            padding-top: 9px;
            border-top: 1px dashed var(--slate-300);
            font-size: 9px;
            color: var(--slate-500);
            line-height: 1.35;
        }

        .scan-text {
            font-weight: 800;
            color: var(--slate-700);
        }

        .empty {
            padding: 48px 28px;
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: 22px;
            text-align: center;
            color: var(--slate-500);
            box-shadow: 0 12px 30px rgba(15, 23, 42, .08);
        }

        .empty-title {
            font-size: 18px;
            font-weight: 900;
            color: var(--slate-900);
            margin-bottom: 6px;
        }

        @page {
            size: A4;
            margin: 10mm;
        }

        @media print {
            body {
                padding: 0;
                background: #ffffff;
                color: #000000;
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
                grid-template-columns: repeat(3, 1fr);
                gap: 7mm;
            }

            .label-card {
                min-height: 74mm;
                padding: 4mm;
                border: 1.4px solid #111827;
                border-radius: 4mm;
                box-shadow: none;
            }

            .label-card::before {
                height: 1.5mm;
            }

            .qr-panel {
                grid-template-columns: 30mm 1fr;
                gap: 3mm;
                padding: 2.5mm;
                border-radius: 3mm;
            }

            .qr {
                width: 28mm;
                height: 28mm;
                padding: 1.5mm;
                border-radius: 3mm;
            }

            .label-title {
                font-size: 10pt;
            }

            .code {
                font-size: 8.5pt;
                padding: 1.5mm 2mm;
            }

            .name {
                font-size: 8.5pt;
            }

            .detail-row {
                font-size: 7.5pt;
                grid-template-columns: 17mm 1fr;
            }

            .label-footer {
                font-size: 6.8pt;
            }
        }

        @media screen and (max-width: 960px) {
            .labels-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media screen and (max-width: 640px) {
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
                justify-content: center;
                width: 100%;
            }

            .labels-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    @php
        $statusLabel = function (?string $status): string {
            return match ($status) {
                'aktif' => 'Aktif',
                'rusak' => 'Rusak',
                'maintenance' => 'Maintenance',
                'nonaktif' => 'Nonaktif',
                'hilang' => 'Hilang',
                default => '-',
            };
        };

        $statusClass = function (?string $status): string {
            return match ($status) {
                'aktif' => 'badge-success',
                'maintenance' => 'badge-warning',
                'rusak', 'hilang' => 'badge-danger',
                'nonaktif' => 'badge-gray',
                default => 'badge-gray',
            };
        };

        $conditionLabel = function (?string $condition): string {
            return match ($condition) {
                'baik' => 'Baik',
                'cukup' => 'Cukup',
                'rusak_ringan' => 'Rusak Ringan',
                'rusak_berat' => 'Rusak Berat',
                default => '-',
            };
        };

        $conditionClass = function (?string $condition): string {
            return match ($condition) {
                'baik' => 'badge-success',
                'cukup' => 'badge-gray',
                'rusak_ringan' => 'badge-warning',
                'rusak_berat' => 'badge-danger',
                default => 'badge-gray',
            };
        };
    @endphp

    <div class="page-shell">
        <div class="toolbar">
            <div class="toolbar-left">
                <div class="toolbar-icon">
                    QR
                </div>

                <div>
                    <h1 class="toolbar-title">
                        Print Semua QR Code Aset IT
                    </h1>
                    <div class="toolbar-subtitle">
                        Label inventaris siap cetak untuk ditempel pada perangkat IT.
                    </div>
                </div>
            </div>

            <div class="toolbar-actions">
                <div class="summary-pill">
                    Total: {{ $assets->count() }} aset
                </div>

                <button class="btn" onclick="window.print()">
                    🖨 Print Semua QR
                </button>
            </div>
        </div>

        @if ($assets->isEmpty())
            <div class="empty">
                <div class="empty-title">
                    Belum ada QR Code aset
                </div>
                <div>
                    Belum ada aset yang memiliki QR Token.
                </div>
            </div>
        @else
            <div class="labels-grid">
                @foreach ($assets as $asset)
                    <div class="label-card">
                        <div class="label-top">
                            <div class="label-brand">
                                <div class="label-kicker">
                                    Inventaris IT
                                </div>
                                <div class="label-title">
                                    PT Taman Wisata Jogja
                                </div>
                            </div>

                            <div class="label-type">
                                Asset Tag
                            </div>
                        </div>

                        <div class="qr-panel">
                            <div class="qr">
                                {!! QrCode::size(115)->margin(1)->generate(route('asset-it.scan', $asset->qr_token)) !!}
                            </div>

                            <div class="asset-main">
                                <div class="code">
                                    {{ $asset->code }}
                                </div>

                                <div class="name">
                                    {{ $asset->name }}
                                </div>

                                <div class="status-row">
                                    <span class="badge {{ $statusClass($asset->status) }}">
                                        {{ $statusLabel($asset->status) }}
                                    </span>

                                    <span class="badge {{ $conditionClass($asset->condition) }}">
                                        {{ $conditionLabel($asset->condition) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="details">
                            <div class="detail-row">
                                <div class="detail-label">Kategori</div>
                                <div class="detail-value">{{ $asset->category?->name ?? '-' }}</div>
                            </div>

                            <div class="detail-row">
                                <div class="detail-label">Lokasi</div>
                                <div class="detail-value">{{ $asset->location?->name ?? '-' }}</div>
                            </div>

                            <div class="detail-row">
                                <div class="detail-label">PIC</div>
                                <div class="detail-value">{{ $asset->responsibleUser?->name ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="label-footer">
                            <div class="scan-text">
                                Scan untuk detail aset
                            </div>
                            <div>
                                {{ now()->format('d/m/Y') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>
