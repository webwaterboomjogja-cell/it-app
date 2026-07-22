<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Aset {{ $asset->code }}</title>

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
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, .16), transparent 32%),
                radial-gradient(circle at bottom right, rgba(14, 165, 233, .12), transparent 30%),
                linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
            color: var(--slate-900);
        }

        .wrapper {
            width: 100%;
            max-width: 760px;
            margin: 0 auto;
            padding: 28px 18px;
        }

        .hero {
            position: relative;
            overflow: hidden;
            border-radius: 28px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 22px;
            color: #ffffff;
            box-shadow: 0 24px 50px rgba(37, 99, 235, .25);
            margin-bottom: 18px;
        }

        .hero::after {
            content: "";
            position: absolute;
            width: 180px;
            height: 180px;
            right: -60px;
            top: -70px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .15);
        }

        .hero-top {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
        }

        .kicker {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 11px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .16);
            border: 1px solid rgba(255, 255, 255, .20);
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .asset-code {
            display: inline-flex;
            align-items: center;
            padding: 8px 11px;
            border-radius: 12px;
            background: rgba(15, 23, 42, .22);
            border: 1px solid rgba(255, 255, 255, .18);
            font-size: 13px;
            font-weight: 900;
            letter-spacing: .03em;
            white-space: nowrap;
        }

        .title {
            position: relative;
            z-index: 1;
            margin: 18px 0 0;
            font-size: 29px;
            line-height: 1.15;
            letter-spacing: -0.04em;
            font-weight: 900;
        }

        .subtitle {
            position: relative;
            z-index: 1;
            margin-top: 8px;
            font-size: 14px;
            line-height: 1.5;
            color: rgba(255, 255, 255, .82);
        }

        .card {
            overflow: hidden;
            background: rgba(255, 255, 255, .94);
            border: 1px solid rgba(226, 232, 240, .95);
            border-radius: 28px;
            box-shadow: 0 18px 44px rgba(15, 23, 42, .10);
            backdrop-filter: blur(12px);
        }

        .photo-wrap {
            position: relative;
            background: var(--slate-100);
        }

        .photo {
            display: block;
            width: 100%;
            height: 310px;
            object-fit: cover;
            background: var(--slate-200);
        }

        .photo-placeholder {
            height: 250px;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, .10), transparent 34%),
                linear-gradient(135deg, #f8fafc, #e2e8f0);
        }

        .placeholder-box {
            width: 94px;
            height: 94px;
            display: grid;
            place-items: center;
            border-radius: 28px;
            background: #ffffff;
            border: 1px solid var(--slate-200);
            box-shadow: 0 14px 32px rgba(15, 23, 42, .08);
            font-size: 42px;
        }

        .content {
            padding: 22px;
        }

        .status-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 18px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 10px;
            border-radius: 999px;
            font-size: 12px;
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

        .section-title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 12px;
            font-size: 15px;
            font-weight: 900;
            color: var(--slate-900);
        }

        .details {
            display: grid;
            gap: 10px;
        }

        .detail-row {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 14px;
            align-items: start;
            padding: 13px 14px;
            border: 1px solid var(--slate-200);
            border-radius: 16px;
            background: var(--slate-50);
        }

        .detail-label {
            color: var(--slate-500);
            font-size: 13px;
            font-weight: 800;
        }

        .detail-value {
            color: var(--slate-900);
            font-size: 14px;
            font-weight: 900;
            text-align: right;
            word-break: break-word;
        }

        .notes-card {
            margin-top: 14px;
            padding: 15px;
            border-radius: 18px;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #7c2d12;
            font-size: 13px;
            line-height: 1.6;
        }

        .footer {
            margin-top: 18px;
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(255, 255, 255, .72);
            border: 1px solid rgba(226, 232, 240, .95);
            color: var(--slate-500);
            text-align: center;
            font-size: 12px;
            line-height: 1.5;
        }

        @media screen and (max-width: 560px) {
            .wrapper {
                padding: 16px;
            }

            .hero {
                border-radius: 22px;
                padding: 18px;
            }

            .hero-top {
                flex-direction: column;
            }

            .title {
                font-size: 24px;
            }

            .card {
                border-radius: 22px;
            }

            .photo {
                height: 240px;
            }

            .content {
                padding: 17px;
            }

            .detail-row {
                grid-template-columns: 1fr;
                gap: 5px;
            }

            .detail-value {
                text-align: left;
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

    <div class="wrapper">
        <div class="hero">
            <div class="hero-top">
                <div class="kicker">
                    📦 Inventaris Aset IT
                </div>

                <div class="asset-code">
                    {{ $asset->code }}
                </div>
            </div>

            <h1 class="title">
                {{ $asset->name }}
            </h1>

            <div class="subtitle">
                Detail aset ini dibuat otomatis dari sistem inventaris IT.
            </div>
        </div>

        <div class="card">
            <div class="photo-wrap">
                @if ($asset->photo)
                    <img class="photo" src="{{ asset('storage/' . $asset->photo) }}" alt="{{ $asset->name }}">
                @else
                    <div class="photo-placeholder">
                        <div class="placeholder-box">
                            💻
                        </div>
                    </div>
                @endif
            </div>

            <div class="content">
                <div class="status-row">
                    <span class="badge {{ $statusClass($asset->status) }}">
                        Status: {{ $statusLabel($asset->status) }}
                    </span>

                    <span class="badge {{ $conditionClass($asset->condition) }}">
                        Kondisi: {{ $conditionLabel($asset->condition) }}
                    </span>
                </div>

                <h2 class="section-title">
                    🧾 Detail Aset
                </h2>

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
                        <div class="detail-label">Penanggung Jawab</div>
                        <div class="detail-value">{{ $asset->responsibleUser?->name ?? '-' }}</div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Merek / Model</div>
                        <div class="detail-value">
                            {{ collect([$asset->brand, $asset->model])->filter()->join(' / ') ?:'-' }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Serial Number</div>
                        <div class="detail-value">{{ $asset->serial_number ?? '-' }}</div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Tanggal Pembelian</div>
                        <div class="detail-value">
                            {{ $asset->purchase_date ? $asset->purchase_date->format('d F Y') : '-' }}
                        </div>
                    </div>
                </div>

                @if ($asset->notes)
                    <div class="notes-card">
                        <strong>Catatan:</strong><br>
                        {{ $asset->notes }}
                    </div>
                @endif
            </div>
        </div>

        <div class="footer">
            Scan QR Code aset untuk membuka halaman detail ini.
            <br>
            Sistem Inventaris IT
        </div>
    </div>
</body>

</html>
