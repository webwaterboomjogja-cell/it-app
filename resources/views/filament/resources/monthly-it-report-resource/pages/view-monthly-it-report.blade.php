<x-filament-panels::page>
    @php
        /** @var \App\Models\MonthlyItReport $record */

        $staffSummary = collect(
            $record->staff_summary ?? []
        );

        $categorySummary = collect(
            $record->category_summary ?? []
        );

        $workStatusSummary = collect(
            $record->work_status_summary ?? []
        );

        $prioritySummary = collect(
            $record->priority_summary ?? []
        );

        $dailySummary = $record->daily_report_summary ?? [];

        $dailyByDate = collect(
            data_get($dailySummary, 'by_date', [])
        );

        $assetSummary = $record->asset_summary ?? [];

        $assetStatusSummary = collect(
            data_get($assetSummary, 'status_summary', [])
        );

        $assetConditionSummary = collect(
            data_get($assetSummary, 'condition_summary', [])
        );

        $problemAssets = collect(
            data_get(
                $assetSummary,
                'frequently_problematic_assets',
                []
            )
        );

        $scheduleSummary = $record->schedule_summary ?? [];

        $scheduleByType = collect(
            data_get($scheduleSummary, 'by_type', [])
        );

        $scheduleByStaff = collect(
            data_get($scheduleSummary, 'by_staff', [])
        );

        
        $formatRichText = function (?string $value): string {
            $value = $value ?? '';

            $value = preg_replace(
                '/<\/(p|div|h1|h2|h3|h4|li)>/i',
                "\n",
                $value
            );

            $value = preg_replace(
                '/<br\s*\/?>/i',
                "\n",
                $value
            );

            return trim(
                html_entity_decode(
                    strip_tags($value)
                )
            );
        };

        $stats = [
            [
                'label' => 'Laporan Harian',
                'value' => $record->total_daily_reports,
                'tone' => 'primary',
            ],
            [
                'label' => 'Pekerjaan Selesai',
                'value' => $record->total_completed,
                'tone' => 'success',
            ],
            [
                'label' => 'Pekerjaan Tertunda',
                'value' => $record->total_pending,
                'tone' => 'warning',
            ],
            [
                'label' => 'Pekerjaan Urgent',
                'value' => $record->total_urgent,
                'tone' => 'danger',
            ],
            [
                'label' => 'Total Aset',
                'value' => $record->total_assets,
                'tone' => 'primary',
            ],
            [
                'label' => 'Aset Bermasalah',
                'value' => $record->total_problem_assets,
                'tone' => 'danger',
            ],
            [
                'label' => 'Aset Maintenance',
                'value' => $record->total_maintenance_assets,
                'tone' => 'warning',
            ],
            [
                'label' => 'Total Jadwal',
                'value' => $record->total_schedules,
                'tone' => 'success',
            ],
        ];
    @endphp

    <style>
        .mit-grid {
            display: grid;
            gap: 1rem;
        }

        .mit-stat-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }

        .mit-two-column {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }

        @media (min-width: 640px) {
            .mit-stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px) {
            .mit-stat-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .mit-two-column {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .mit-info-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }

        @media (min-width: 768px) {
            .mit-info-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        .mit-stat-card {
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            background: #ffffff;
            padding: 1rem;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.04);
        }

        .dark .mit-stat-card {
            border-color: #374151;
            background: #111827;
        }

        .mit-stat-label {
            color: #6b7280;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .dark .mit-stat-label {
            color: #9ca3af;
        }

        .mit-stat-value {
            margin-top: 0.35rem;
            color: #111827;
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1;
        }

        .dark .mit-stat-value {
            color: #f9fafb;
        }

        .mit-stat-card--primary {
            border-left: 4px solid #3b82f6;
        }

        .mit-stat-card--success {
            border-left: 4px solid #10b981;
        }

        .mit-stat-card--warning {
            border-left: 4px solid #f59e0b;
        }

        .mit-stat-card--danger {
            border-left: 4px solid #ef4444;
        }

        .mit-meta-label {
            color: #6b7280;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .dark .mit-meta-label {
            color: #9ca3af;
        }

        .mit-meta-value {
            margin-top: 0.3rem;
            color: #111827;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .dark .mit-meta-value {
            color: #f9fafb;
        }

        .mit-table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .mit-table {
            width: 100%;
            min-width: 700px;
            border-collapse: collapse;
        }

        .mit-table th {
            padding: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
            color: #4b5563;
            font-size: 0.75rem;
            font-weight: 700;
            text-align: left;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .dark .mit-table th {
            border-color: #374151;
            background: #1f2937;
            color: #d1d5db;
        }

        .mit-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
            font-size: 0.875rem;
            vertical-align: middle;
        }

        .dark .mit-table td {
            border-color: #374151;
            color: #d1d5db;
        }

        .mit-table tr:last-child td {
            border-bottom: 0;
        }

        .mit-text-center {
            text-align: center !important;
        }

        .mit-text-right {
            text-align: right !important;
        }

        .mit-font-semibold {
            font-weight: 600;
        }

        .mit-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            padding: 0.2rem 0.55rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .mit-badge--success {
            background: #d1fae5;
            color: #065f46;
        }

        .mit-badge--warning {
            background: #fef3c7;
            color: #92400e;
        }

        .mit-badge--danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .mit-badge--info {
            background: #dbeafe;
            color: #1e40af;
        }

        .mit-badge--gray {
            background: #f3f4f6;
            color: #374151;
        }

        .dark .mit-badge--success {
            background: rgb(6 95 70 / 0.35);
            color: #6ee7b7;
        }

        .dark .mit-badge--warning {
            background: rgb(146 64 14 / 0.35);
            color: #fcd34d;
        }

        .dark .mit-badge--danger {
            background: rgb(153 27 27 / 0.35);
            color: #fca5a5;
        }

        .dark .mit-badge--info {
            background: rgb(30 64 175 / 0.35);
            color: #93c5fd;
        }

        .dark .mit-badge--gray {
            background: #374151;
            color: #d1d5db;
        }

        .mit-progress {
            width: 100%;
            min-width: 90px;
            height: 0.45rem;
            overflow: hidden;
            border-radius: 9999px;
            background: #e5e7eb;
        }

        .dark .mit-progress {
            background: #374151;
        }

        .mit-progress-bar {
            height: 100%;
            border-radius: 9999px;
            background: #10b981;
        }

        .mit-empty {
            padding: 2rem 1rem;
            color: #6b7280;
            font-size: 0.875rem;
            text-align: center;
        }

        .dark .mit-empty {
            color: #9ca3af;
        }

        .mit-rich-text {
            color: #374151;
            font-size: 0.9rem;
            line-height: 1.75;
            white-space: pre-line;
        }

        .dark .mit-rich-text {
            color: #d1d5db;
        }
    </style>

    <div class="mit-grid">
        {{-- Informasi laporan --}}
        <x-filament::section
            icon="heroicon-o-document-chart-bar"
            icon-color="primary"
        >
            <x-slot name="heading">
                Informasi Laporan
            </x-slot>

            <x-slot name="description">
                Informasi periode, pembuat, dan status laporan.
            </x-slot>

            <div class="mit-info-grid">
                <div>
                    <div class="mit-meta-label">
                        Periode
                    </div>

                    <div class="mit-meta-value">
                        {{ $record->period_label }}
                    </div>
                </div>

                <div>
                    <div class="mit-meta-label">
                        Rentang Tanggal
                    </div>

                    <div class="mit-meta-value">
                        {{ $record->period_start?->format('d M Y') }}
                        –
                        {{ $record->period_end?->format('d M Y') }}
                    </div>
                </div>

                <div>
                    <div class="mit-meta-label">
                        Status
                    </div>

                    <div class="mit-meta-value">
                        <span
                            class="mit-badge {{ $record->isFinalized()
                                ? 'mit-badge--success'
                                : 'mit-badge--warning' }}"
                        >
                            {{ $record->status_label }}
                        </span>
                    </div>
                </div>

                <div>
                    <div class="mit-meta-label">
                        Dibuat Oleh
                    </div>

                    <div class="mit-meta-value">
                        {{ $record->generatedBy?->name ?? '-' }}
                    </div>
                </div>

                <div>
                    <div class="mit-meta-label">
                        Terakhir Generate
                    </div>

                    <div class="mit-meta-value">
                        {{ $record->generated_at?->format('d M Y H:i') ?? '-' }}
                    </div>
                </div>

                <div>
                    <div class="mit-meta-label">
                        Disetujui Oleh
                    </div>

                    <div class="mit-meta-value">
                        {{ $record->approvedBy?->name ?? '-' }}
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Statistik --}}
        <div class="mit-stat-grid">
            @foreach ($stats as $stat)
                <div
                    class="mit-stat-card mit-stat-card--{{ $stat['tone'] }}"
                >
                    <div class="mit-stat-label">
                        {{ $stat['label'] }}
                    </div>

                    <div class="mit-stat-value">
                        {{ number_format((int) $stat['value'], 0, ',', '.') }}
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Rekap per staff --}}
        <x-filament::section
            icon="heroicon-o-user-group"
            icon-color="primary"
            collapsible
        >
            <x-slot name="heading">
                Rekap Pekerjaan per Staff
            </x-slot>

            <x-slot name="description">
                Jumlah pekerjaan, durasi, dan persentase penyelesaian setiap staff.
            </x-slot>

            <div class="mit-table-wrapper">
                <table class="mit-table">
                    <thead>
                        <tr>
                            <th>Staff</th>
                            <th class="mit-text-center">Total</th>
                            <th class="mit-text-center">Selesai</th>
                            <th class="mit-text-center">Proses</th>
                            <th class="mit-text-center">Tertunda</th>
                            <th class="mit-text-center">Urgent</th>
                            <th class="mit-text-center">Durasi</th>
                            <th>Penyelesaian</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($staffSummary as $staff)
                            @php
                                $completion = min(
                                    100,
                                    max(
                                        0,
                                        (float) (
                                            $staff['completion_percentage']
                                            ?? 0
                                        )
                                    )
                                );
                            @endphp

                            <tr>
                                <td class="mit-font-semibold">
                                    {{ $staff['staff_name'] ?? '-' }}
                                </td>

                                <td class="mit-text-center">
                                    {{ $staff['total_reports'] ?? 0 }}
                                </td>

                                <td class="mit-text-center">
                                    {{ $staff['completed'] ?? 0 }}
                                </td>

                                <td class="mit-text-center">
                                    {{ $staff['in_progress'] ?? 0 }}
                                </td>

                                <td class="mit-text-center">
                                    {{ $staff['pending'] ?? 0 }}
                                </td>

                                <td class="mit-text-center">
                                    {{ $staff['urgent'] ?? 0 }}
                                </td>

                                <td class="mit-text-center">
                                    {{ number_format(
                                        (float) (
                                            $staff['total_duration_hours']
                                            ?? 0
                                        ),
                                        2,
                                        ',',
                                        '.'
                                    ) }}
                                    jam
                                </td>

                                <td>
                                    <div>
                                        {{ number_format(
                                            $completion,
                                            2,
                                            ',',
                                            '.'
                                        ) }}%
                                    </div>

                                    <div class="mit-progress">
                                        <div
                                            class="mit-progress-bar"
                                            style="width: {{ $completion }}%"
                                        ></div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="mit-empty">
                                    Belum ada rekap pekerjaan per staff.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Rekap kategori --}}
        <x-filament::section
            icon="heroicon-o-tag"
            icon-color="info"
            collapsible
        >
            <x-slot name="heading">
                Rekap Pekerjaan per Kategori
            </x-slot>

            <x-slot name="description">
                Distribusi pekerjaan berdasarkan kategori pekerjaan IT.
            </x-slot>

            <div class="mit-table-wrapper">
                <table class="mit-table">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th class="mit-text-center">Total</th>
                            <th class="mit-text-center">Selesai</th>
                            <th class="mit-text-center">Tertunda</th>
                            <th class="mit-text-center">Urgent</th>
                            <th class="mit-text-center">Durasi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($categorySummary as $category)
                            <tr>
                                <td class="mit-font-semibold">
                                    {{ $category['category_name'] ?? '-' }}
                                </td>

                                <td class="mit-text-center">
                                    {{ $category['total_reports'] ?? 0 }}
                                </td>

                                <td class="mit-text-center">
                                    {{ $category['completed'] ?? 0 }}
                                </td>

                                <td class="mit-text-center">
                                    {{ $category['pending'] ?? 0 }}
                                </td>

                                <td class="mit-text-center">
                                    {{ $category['urgent'] ?? 0 }}
                                </td>

                                <td class="mit-text-center">
                                    {{ number_format(
                                        ((float) (
                                            $category['total_duration_minutes']
                                            ?? 0
                                        )) / 60,
                                        2,
                                        ',',
                                        '.'
                                    ) }}
                                    jam
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="mit-empty">
                                    Belum ada rekap kategori.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Status dan prioritas --}}
        <div class="mit-two-column">
            <x-filament::section
                icon="heroicon-o-check-circle"
                icon-color="success"
                collapsible
            >
                <x-slot name="heading">
                    Status Pekerjaan
                </x-slot>

                <div class="mit-table-wrapper">
                    <table class="mit-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th class="mit-text-center">Total</th>
                                <th class="mit-text-center">Persentase</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($workStatusSummary as $status)
                                @php
                                    $statusName =
                                        $status['status'] ?? 'unknown';

                                    $statusBadge = match ($statusName) {
                                        'selesai',
                                        'completed',
                                        'done' =>
                                            'mit-badge--success',

                                        'proses',
                                        'diproses',
                                        'in_progress',
                                        'progress' =>
                                            'mit-badge--info',

                                        'tertunda',
                                        'pending' =>
                                            'mit-badge--warning',

                                        default =>
                                            'mit-badge--gray',
                                    };
                                @endphp

                                <tr>
                                    <td>
                                        <span
                                            class="mit-badge {{ $statusBadge }}"
                                        >
                                            {{ $status['label'] ?? '-' }}
                                        </span>
                                    </td>

                                    <td class="mit-text-center">
                                        {{ $status['total'] ?? 0 }}
                                    </td>

                                    <td class="mit-text-center">
                                        {{ number_format(
                                            (float) (
                                                $status['percentage']
                                                ?? 0
                                            ),
                                            2,
                                            ',',
                                            '.'
                                        ) }}%
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="mit-empty">
                                        Belum ada data status.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            <x-filament::section
                icon="heroicon-o-flag"
                icon-color="warning"
                collapsible
            >
                <x-slot name="heading">
                    Prioritas Pekerjaan
                </x-slot>

                <div class="mit-table-wrapper">
                    <table class="mit-table">
                        <thead>
                            <tr>
                                <th>Prioritas</th>
                                <th class="mit-text-center">Total</th>
                                <th class="mit-text-center">Persentase</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($prioritySummary as $priority)
                                @php
                                    $priorityName =
                                        $priority['priority'] ?? 'normal';

                                    $priorityBadge = match ($priorityName) {
                                        'urgent',
                                        'darurat' =>
                                            'mit-badge--danger',

                                        'tinggi',
                                        'high' =>
                                            'mit-badge--warning',

                                        'normal',
                                        'medium' =>
                                            'mit-badge--info',

                                        default =>
                                            'mit-badge--gray',
                                    };
                                @endphp

                                <tr>
                                    <td>
                                        <span
                                            class="mit-badge {{ $priorityBadge }}"
                                        >
                                            {{ $priority['label'] ?? '-' }}
                                        </span>
                                    </td>

                                    <td class="mit-text-center">
                                        {{ $priority['total'] ?? 0 }}
                                    </td>

                                    <td class="mit-text-center">
                                        {{ number_format(
                                            (float) (
                                                $priority['percentage']
                                                ?? 0
                                            ),
                                            2,
                                            ',',
                                            '.'
                                        ) }}%
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="mit-empty">
                                        Belum ada data prioritas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>

        {{-- Rekap aset --}}
        <x-filament::section
            icon="heroicon-o-computer-desktop"
            icon-color="warning"
            collapsible
        >
            <x-slot name="heading">
                Rekap Aset IT
            </x-slot>

            <x-slot name="description">
                Kondisi aset pada saat laporan bulanan terakhir digenerate.
            </x-slot>

            <div class="mit-two-column">
                <div class="mit-table-wrapper">
                    <table class="mit-table">
                        <thead>
                            <tr>
                                <th>Status Aset</th>
                                <th class="mit-text-center">Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($assetStatusSummary as $status)
                                <tr>
                                    <td class="mit-font-semibold">
                                        {{ $status['label'] ?? '-' }}
                                    </td>

                                    <td class="mit-text-center">
                                        {{ $status['total'] ?? 0 }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="mit-empty">
                                        Belum ada rekap status aset.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mit-table-wrapper">
                    <table class="mit-table">
                        <thead>
                            <tr>
                                <th>Kondisi Aset</th>
                                <th class="mit-text-center">Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($assetConditionSummary as $condition)
                                <tr>
                                    <td class="mit-font-semibold">
                                        {{ $condition['label'] ?? '-' }}
                                    </td>

                                    <td class="mit-text-center">
                                        {{ $condition['total'] ?? 0 }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="mit-empty">
                                        Belum ada rekap kondisi aset.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </x-filament::section>

        {{-- Aset sering bermasalah --}}
        <x-filament::section
            icon="heroicon-o-exclamation-triangle"
            icon-color="danger"
            collapsible
        >
            <x-slot name="heading">
                Aset yang Sering Bermasalah
            </x-slot>

            <x-slot name="description">
                Aset yang tercatat pada laporan harian minimal dua kali dalam periode laporan.
            </x-slot>

            <div class="mit-table-wrapper">
                <table class="mit-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Aset</th>
                            <th>Status</th>
                            <th>Kondisi</th>
                            <th class="mit-text-center">Jumlah Laporan</th>
                            <th class="mit-text-center">Urgent</th>
                            <th>Laporan Terakhir</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($problemAssets as $asset)
                            <tr>
                                <td class="mit-font-semibold">
                                    {{ $asset['asset_code'] ?? '-' }}
                                </td>

                                <td>
                                    {{ $asset['asset_name'] ?? '-' }}
                                </td>

                                <td>
                                    {{ ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $asset['asset_status'] ?? '-'
                                        )
                                    ) }}
                                </td>

                                <td>
                                    {{ ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $asset['asset_condition'] ?? '-'
                                        )
                                    ) }}
                                </td>

                                <td class="mit-text-center">
                                    {{ $asset['report_count'] ?? 0 }}
                                </td>

                                <td class="mit-text-center">
                                    {{ $asset['urgent_count'] ?? 0 }}
                                </td>

                                <td>
                                    {{ ! empty($asset['last_report_date'])
                                        ? \Carbon\Carbon::parse(
                                            $asset['last_report_date']
                                        )->format('d M Y')
                                        : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="mit-empty">
                                    Tidak ada aset yang tercatat bermasalah berulang kali.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Rekap jadwal --}}
        <x-filament::section
            icon="heroicon-o-calendar-days"
            icon-color="info"
            collapsible
        >
            <x-slot name="heading">
                Rekap Jadwal Tim IT
            </x-slot>

            <x-slot name="description">
                Rekap jadwal kerja, maintenance, Cuti/DP, dan izin setiap staff.
            </x-slot>

            <div class="mit-stat-grid">
                @foreach ($scheduleByType as $scheduleType)
                    <div class="mit-stat-card">
                        <div class="mit-stat-label">
                            {{ $scheduleType['label'] ?? '-' }}
                        </div>

                        <div class="mit-stat-value">
                            {{ $scheduleType['total'] ?? 0 }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div
                class="mit-table-wrapper"
                style="margin-top: 1rem;"
            >
                <table class="mit-table">
                    <thead>
                        <tr>
                            <th>Staff</th>
                            <th class="mit-text-center">Total</th>
                            <th class="mit-text-center">Kerja</th>
                            <th class="mit-text-center">Maintenance</th>
                            <th class="mit-text-center">Cuti / DP</th>
                            <th class="mit-text-center">Ijin</th>
                            <th class="mit-text-center">Total Jam</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($scheduleByStaff as $schedule)
                            <tr>
                                <td class="mit-font-semibold">
                                    {{ $schedule['staff_name'] ?? '-' }}
                                </td>

                                <td class="mit-text-center">
                                    {{ $schedule['total_schedules'] ?? 0 }}
                                </td>

                                <td class="mit-text-center">
                                    {{ $schedule['work_days'] ?? 0 }}
                                </td>

                                <td class="mit-text-center">
                                    {{ $schedule['maintenance_days'] ?? 0 }}
                                </td>

                                <td class="mit-text-center">
                                    {{ $schedule['leave_days'] ?? 0 }}
                                </td>

                                <td class="mit-text-center">
                                    {{ $schedule['permission_days'] ?? 0 }}
                                </td>

                                <td class="mit-text-center">
                                    {{ number_format(
                                        (float) (
                                            $schedule[
                                                'total_scheduled_hours'
                                            ] ?? 0
                                        ),
                                        2,
                                        ',',
                                        '.'
                                    ) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="mit-empty">
                                    Belum ada jadwal pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Aktivitas per tanggal --}}
        <x-filament::section
            icon="heroicon-o-list-bullet"
            icon-color="gray"
            collapsible
            collapsed
        >
            <x-slot name="heading">
                Aktivitas Laporan per Tanggal
            </x-slot>

            <div class="mit-table-wrapper">
                <table class="mit-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th class="mit-text-center">Total</th>
                            <th class="mit-text-center">Selesai</th>
                            <th class="mit-text-center">Tertunda</th>
                            <th class="mit-text-center">Urgent</th>
                            <th class="mit-text-center">Durasi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($dailyByDate as $daily)
                            <tr>
                                <td class="mit-font-semibold">
                                    {{ $daily['date_label'] ?? '-' }}
                                </td>

                                <td class="mit-text-center">
                                    {{ $daily['total_reports'] ?? 0 }}
                                </td>

                                <td class="mit-text-center">
                                    {{ $daily['completed'] ?? 0 }}
                                </td>

                                <td class="mit-text-center">
                                    {{ $daily['pending'] ?? 0 }}
                                </td>

                                <td class="mit-text-center">
                                    {{ $daily['urgent'] ?? 0 }}
                                </td>

                                <td class="mit-text-center">
                                    {{ number_format(
                                        (float) (
                                            $daily['duration_hours']
                                            ?? 0
                                        ),
                                        2,
                                        ',',
                                        '.'
                                    ) }}
                                    jam
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="mit-empty">
                                    Belum ada aktivitas harian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Evaluasi dan rekomendasi --}}
        <div class="mit-two-column">
            <x-filament::section
                icon="heroicon-o-clipboard-document-check"
                icon-color="success"
            >
                <x-slot name="heading">
                    Evaluasi Bulanan
                </x-slot>

                @if ($record->evaluation)
                    <div class="mit-rich-text">{{
                        $formatRichText($record->evaluation)
                    }}</div>
                @else
                    <div class="mit-empty">
                        Evaluasi bulanan belum diisi.
                    </div>
                @endif
            </x-filament::section>

            <x-filament::section
                icon="heroicon-o-light-bulb"
                icon-color="warning"
            >
                <x-slot name="heading">
                    Rekomendasi Bulan Berikutnya
                </x-slot>

                @if ($record->recommendation)
                    <div class="mit-rich-text">{{
                        $formatRichText($record->recommendation)
                    }}</div>
                @else
                    <div class="mit-empty">
                        Rekomendasi bulan berikutnya belum diisi.
                    </div>
                @endif
            </x-filament::section>
        </div>

        @if ($record->notes)
            <x-filament::section
                icon="heroicon-o-pencil"
                icon-color="gray"
            >
                <x-slot name="heading">
                    Catatan Tambahan
                </x-slot>

                <div class="mit-rich-text">
                    {{ $record->notes }}
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>