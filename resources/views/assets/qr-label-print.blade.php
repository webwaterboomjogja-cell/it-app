<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Label QR Aset {{ $asset->code }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 24px;
        }

        .toolbar {
            margin-bottom: 16px;
            text-align: center;
        }

        .btn {
            display: inline-block;
            border: none;
            background: #2563eb;
            color: white;
            padding: 10px 18px;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
        }

        .label-card {
            width: 320px;
            min-height: 430px;
            margin: 0 auto;
            padding: 18px;
            background: white;
            border: 2px solid #111827;
            border-radius: 16px;
            text-align: center;
        }

        .header {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #2563eb;
            margin-bottom: 10px;
        }

        .qr {
            margin: 12px auto;
            display: flex;
            justify-content: center;
        }

        .code {
            font-size: 18px;
            font-weight: 800;
            margin-top: 10px;
        }

        .name {
            font-size: 15px;
            font-weight: bold;
            margin-top: 6px;
        }

        .info {
            margin-top: 14px;
            font-size: 12px;
            color: #374151;
            line-height: 1.5;
        }

        .footer {
            margin-top: 16px;
            font-size: 11px;
            color: #6b7280;
            border-top: 1px dashed #d1d5db;
            padding-top: 10px;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .toolbar {
                display: none;
            }

            .label-card {
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <button class="btn" onclick="window.print()">Cetak Label QR</button>
    </div>

    <div class="label-card">
        <div class="header">INVENTARIS ASET IT</div>

        <div class="qr">
    {!! QrCode::size(190)->margin(1)->generate(route('asset-it.scan', $asset->qr_token)) !!}
</div>

        <div class="code">{{ $asset->code }}</div>

        <div class="name">{{ $asset->name }}</div>

        <div class="info">
            Kategori: {{ $asset->category?->name ?? '-' }} <br>
            Lokasi: {{ $asset->location?->name ?? '-' }} <br>
            PIC: {{ $asset->responsibleUser?->name ?? '-' }}
        </div>

        <div class="footer">
            Scan QR untuk melihat detail aset.
        </div>
    </div>
</body>

</html>
