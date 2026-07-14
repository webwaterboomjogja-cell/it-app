<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Aset {{ $asset->code }}</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            color: #111827;
        }

        .wrapper {
            max-width: 520px;
            margin: 0 auto;
            padding: 24px;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            border: 1px solid #e5e7eb;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: bold;
            background: #dbeafe;
            color: #1d4ed8;
            margin-bottom: 12px;
        }

        .title {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
        }

        .code {
            margin-top: 6px;
            color: #2563eb;
            font-weight: bold;
        }

        .photo {
            width: 100%;
            max-height: 280px;
            object-fit: cover;
            border-radius: 16px;
            margin: 18px 0;
            background: #e5e7eb;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .row:last-child {
            border-bottom: none;
        }

        .label {
            color: #6b7280;
            font-size: 13px;
        }

        .value {
            text-align: right;
            font-weight: 700;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: bold;
            background: #dcfce7;
            color: #166534;
        }

        .footer {
            margin-top: 18px;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="card">
            <div class="badge">Inventaris Aset IT</div>

            <h1 class="title">{{ $asset->name }}</h1>
            <div class="code">{{ $asset->code }}</div>

            @if ($asset->photo)
                <img class="photo" src="{{ asset('storage/' . $asset->photo) }}" alt="{{ $asset->name }}">
            @endif

            <div class="row">
                <div class="label">Kategori</div>
                <div class="value">{{ $asset->category?->name ?? '-' }}</div>
            </div>

            <div class="row">
                <div class="label">Lokasi</div>
                <div class="value">{{ $asset->location?->name ?? '-' }}</div>
            </div>

            <div class="row">
                <div class="label">Penanggung Jawab</div>
                <div class="value">{{ $asset->responsibleUser?->name ?? '-' }}</div>
            </div>

            <div class="row">
                <div class="label">Merek / Model</div>
                <div class="value">
                    {{ collect([$asset->brand, $asset->model])->filter()->join(' / ') ?:'-' }}
                </div>
            </div>

            <div class="row">
                <div class="label">Status</div>
                <div class="value">
                    <span class="status">
                        {{ match ($asset->status) {
                            'aktif' => 'Aktif',
                            'rusak' => 'Rusak',
                            'maintenance' => 'Maintenance',
                            'nonaktif' => 'Nonaktif',
                            'hilang' => 'Hilang',
                            default => $asset->status,
                        } }}
                    </span>
                </div>
            </div>

            <div class="row">
                <div class="label">Kondisi</div>
                <div class="value">
                    {{ match ($asset->condition) {
                        'baik' => 'Baik',
                        'cukup' => 'Cukup',
                        'rusak_ringan' => 'Rusak Ringan',
                        'rusak_berat' => 'Rusak Berat',
                        default => $asset->condition,
                    } }}
                </div>
            </div>
        </div>

        <div class="footer">
            Halaman ini dibuat otomatis dari sistem inventaris IT.
        </div>
    </div>
</body>

</html>
