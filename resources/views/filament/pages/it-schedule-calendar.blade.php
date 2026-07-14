<x-filament-panels::page>
    @php
        $calendar = $this->calendar;
        $selectedUser = $this->selectedUser;
        $selectedMonth = \Carbon\Carbon::createFromFormat('Y-m', $month);

        $allSchedules = collect($calendar)->flatten(1)->flatMap(fn($day) => $day['schedules']);

        $totalSchedules = $allSchedules->count();
        $totalWork = $allSchedules->where('type', 'kerja')->count();
        $totalStandby = $allSchedules->where('type', 'piket')->count();
        $totalMaintenance = $allSchedules->where('type', 'maintenance')->count();
        $totalCutiDp = $allSchedules->where('type', 'cuti_dp')->count();
        $totalIjin = $allSchedules->where('type', 'ijin')->count();

        $typeMeta = [
            'kerja' => [
                'label' => 'Kerja',
                'icon' => 'heroicon-o-briefcase',
            ],
            'maintenance' => [
                'label' => 'Maintenance',
                'icon' => 'heroicon-o-wrench-screwdriver',
            ],
            'cuti_dp' => [
                'label' => 'Cuti / DP',
                'icon' => 'heroicon-o-document-text',
            ],
            'ijin' => [
                'label' => 'Ijin',
                'icon' => 'heroicon-o-no-symbol',
            ],
        ];
    @endphp

    <style>
        .itcal {
            --itcal-primary: rgb(var(--primary-600));
            --itcal-primary-soft: rgba(var(--primary-500), 0.12);
            --itcal-bg: #ffffff;
            --itcal-card: #ffffff;
            --itcal-muted: #6b7280;
            --itcal-text: #111827;
            --itcal-border: rgba(17, 24, 39, 0.10);
            --itcal-soft: #f8fafc;
            --itcal-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
            --itcal-shadow-soft: 0 8px 22px rgba(15, 23, 42, 0.06);
            --itcal-radius: 24px;
        }

        .dark .itcal {
            --itcal-bg: #020617;
            --itcal-card: #0f172a;
            --itcal-muted: #94a3b8;
            --itcal-text: #f8fafc;
            --itcal-border: rgba(255, 255, 255, 0.10);
            --itcal-soft: rgba(15, 23, 42, 0.72);
            --itcal-shadow: 0 18px 45px rgba(0, 0, 0, 0.30);
            --itcal-shadow-soft: 0 8px 22px rgba(0, 0, 0, 0.24);
        }

        .itcal * {
            box-sizing: border-box;
        }

        .itcal-wrap {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .itcal-hero {
            position: relative;
            overflow: hidden;
            border-radius: 30px;
            padding: 28px;
            color: white;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.24), transparent 32%),
                radial-gradient(circle at bottom left, rgba(125, 211, 252, 0.22), transparent 30%),
                linear-gradient(135deg, rgb(var(--primary-600)), rgb(var(--primary-800)), #020617);
            box-shadow: var(--itcal-shadow);
        }

        .itcal-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, .08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .08) 1px, transparent 1px);
            background-size: 36px 36px;
            mask-image: linear-gradient(to bottom, rgba(0, 0, 0, .8), transparent);
            opacity: .45;
        }

        .itcal-hero-content {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(360px, .8fr);
            gap: 24px;
            align-items: end;
        }

        .itcal-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            border-radius: 999px;
            padding: 7px 12px;
            background: rgba(255, 255, 255, .13);
            border: 1px solid rgba(255, 255, 255, .20);
            font-size: 12px;
            font-weight: 700;
            backdrop-filter: blur(16px);
        }

        .itcal-title {
            margin-top: 14px;
            font-size: clamp(26px, 4vw, 42px);
            line-height: 1.1;
            font-weight: 800;
            letter-spacing: -0.04em;
        }

        .itcal-subtitle {
            margin-top: 10px;
            max-width: 680px;
            color: rgba(255, 255, 255, .80);
            font-size: 14px;
            line-height: 1.7;
        }

        .itcal-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .itcal-stat {
            border-radius: 20px;
            padding: 16px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .18);
            backdrop-filter: blur(16px);
        }

        .itcal-stat-label {
            font-size: 12px;
            color: rgba(255, 255, 255, .72);
            font-weight: 600;
        }

        .itcal-stat-value {
            margin-top: 4px;
            font-size: 28px;
            font-weight: 800;
            line-height: 1;
        }

        .itcal-panel {
            border-radius: var(--itcal-radius);
            background: var(--itcal-card);
            border: 1px solid var(--itcal-border);
            box-shadow: var(--itcal-shadow-soft);
        }

        .itcal-filter {
            padding: 20px;
        }

        .itcal-filter-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 240px 220px;
            gap: 16px;
            align-items: end;
        }

        .itcal-field label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            color: var(--itcal-text);
            font-size: 13px;
            font-weight: 700;
        }

        .itcal-input {
            width: 100%;
            min-height: 44px;
            border-radius: 16px;
            border: 1px solid var(--itcal-border);
            background: var(--itcal-card);
            color: var(--itcal-text);
            padding: 0 14px;
            font-size: 14px;
            outline: none;
            transition: .18s ease;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }

        .itcal-input:focus {
            border-color: var(--itcal-primary);
            box-shadow: 0 0 0 4px var(--itcal-primary-soft);
        }

        .itcal-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            width: 100%;
            min-height: 44px;
            border-radius: 16px;
            padding: 0 16px;
            font-size: 14px;
            font-weight: 800;
            text-decoration: none;
            transition: .18s ease;
        }

        .itcal-button-primary {
            color: white;
            background: linear-gradient(135deg, rgb(var(--primary-600)), rgb(var(--primary-500)));
            box-shadow: 0 12px 24px rgba(var(--primary-600), .26);
        }

        .itcal-button-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 30px rgba(var(--primary-600), .32);
        }

        .itcal-button-disabled {
            cursor: not-allowed;
            color: #9ca3af;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
        }

        .dark .itcal-button-disabled {
            background: #1f2937;
            border-color: rgba(255, 255, 255, .08);
            color: #64748b;
        }

        .itcal-calendar {
            overflow: hidden;
        }

        .itcal-calendar-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 20px;
            border-bottom: 1px solid var(--itcal-border);
            background:
                linear-gradient(180deg, rgba(248, 250, 252, .90), rgba(255, 255, 255, .72));
        }

        .dark .itcal-calendar-head {
            background:
                linear-gradient(180deg, rgba(30, 41, 59, .72), rgba(15, 23, 42, .72));
        }

        .itcal-calendar-title {
            color: var(--itcal-text);
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .itcal-calendar-desc {
            margin-top: 4px;
            color: var(--itcal-muted);
            font-size: 13px;
        }

        .itcal-legends {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
        }

        .itcal-legend {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border-radius: 999px;
            padding: 7px 10px;
            font-size: 12px;
            font-weight: 800;
            border: 1px solid transparent;
        }

        .itcal-legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
        }

        .itcal-legend.kerja {
            color: #0369a1;
            background: #e0f2fe;
            border-color: #bae6fd;
        }

        .itcal-legend.piket {
            color: #92400e;
            background: #fef3c7;
            border-color: #fde68a;
        }

        .itcal-legend.maintenance {
            color: #be123c;
            background: #ffe4e6;
            border-color: #fecdd3;
        }

        .itcal-legend.event_support {
            color: #0e7490;
            background: #cffafe;
            border-color: #a5f3fc;
        }

        .itcal-legend.cuti_izin {
            color: #374151;
            background: #f3f4f6;
            border-color: #e5e7eb;
        }

        .dark .itcal-legend.kerja {
            color: #7dd3fc;
            background: rgba(14, 165, 233, .14);
            border-color: rgba(14, 165, 233, .22);
        }

        .dark .itcal-legend.piket {
            color: #fcd34d;
            background: rgba(245, 158, 11, .14);
            border-color: rgba(245, 158, 11, .22);
        }

        .dark .itcal-legend.maintenance {
            color: #fda4af;
            background: rgba(244, 63, 94, .14);
            border-color: rgba(244, 63, 94, .22);
        }

        .dark .itcal-legend.event_support {
            color: #67e8f9;
            background: rgba(6, 182, 212, .14);
            border-color: rgba(6, 182, 212, .22);
        }

        .dark .itcal-legend.cuti_izin {
            color: #cbd5e1;
            background: rgba(148, 163, 184, .10);
            border-color: rgba(148, 163, 184, .18);
        }

        .itcal-desktop {
            display: block;
        }

        .itcal-days {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            border-bottom: 1px solid var(--itcal-border);
            background: var(--itcal-soft);
        }

        .itcal-day-name {
            padding: 13px 10px;
            text-align: center;
            color: var(--itcal-muted);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .itcal-week {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            border-bottom: 1px solid var(--itcal-border);
        }

        .itcal-week:last-child {
            border-bottom: none;
        }

        .itcal-cell {
            min-height: 190px;
            padding: 12px;
            border-right: 1px solid var(--itcal-border);
            background: var(--itcal-card);
            transition: background .18s ease;
        }

        .itcal-cell:last-child {
            border-right: none;
        }

        .itcal-cell:hover {
            background: rgba(var(--primary-500), .04);
        }

        .itcal-cell.is-muted {
            background: var(--itcal-soft);
        }

        .itcal-cell.is-today {
            position: relative;
            box-shadow: inset 0 0 0 2px rgba(var(--primary-500), .75);
        }

        .itcal-cell-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 10px;
        }

        .itcal-date {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 999px;
            color: var(--itcal-text);
            font-size: 13px;
            font-weight: 900;
        }

        .itcal-cell.is-muted .itcal-date {
            color: #9ca3af;
        }

        .itcal-cell.is-today .itcal-date {
            color: white;
            background: rgb(var(--primary-600));
            box-shadow: 0 10px 22px rgba(var(--primary-600), .28);
        }

        .itcal-today-badge {
            border-radius: 999px;
            padding: 4px 8px;
            color: rgb(var(--primary-700));
            background: rgba(var(--primary-500), .11);
            border: 1px solid rgba(var(--primary-500), .18);
            font-size: 10px;
            font-weight: 900;
        }

        .dark .itcal-today-badge {
            color: rgb(var(--primary-300));
        }

        .itcal-count {
            min-width: 24px;
            height: 24px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--itcal-muted);
            background: var(--itcal-soft);
            border: 1px solid var(--itcal-border);
            font-size: 11px;
            font-weight: 900;
        }

        .itcal-events {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .itcal-event {
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            padding: 10px 10px 10px 12px;
            border: 1px solid transparent;
            transition: .18s ease;
        }

        .itcal-event:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(15, 23, 42, .10);
        }

        .itcal-event::before {
            content: "";
            position: absolute;
            left: 0;
            top: 10px;
            bottom: 10px;
            width: 4px;
            border-radius: 999px;
        }

        .itcal-event-title {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 12px;
            font-weight: 900;
            line-height: 1.25;
        }

        .itcal-event-meta {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 6px;
            font-size: 11px;
            line-height: 1.25;
            opacity: .86;
        }

        .itcal-event-meta span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .itcal-event.kerja {
            color: #075985;
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border-color: #bae6fd;
        }

        .itcal-event.kerja::before {
            background: #0ea5e9;
        }

        .itcal-event.piket {
            color: #92400e;
            background: linear-gradient(135deg, #fffbeb, #fef3c7);
            border-color: #fde68a;
        }

        .itcal-event.piket::before {
            background: #f59e0b;
        }

        .itcal-event.maintenance {
            color: #be123c;
            background: linear-gradient(135deg, #fff1f2, #ffe4e6);
            border-color: #fecdd3;
        }

        .itcal-event.maintenance::before {
            background: #f43f5e;
        }

        .itcal-event.event_support {
            color: #0e7490;
            background: linear-gradient(135deg, #ecfeff, #cffafe);
            border-color: #a5f3fc;
        }

        .itcal-event.event_support::before {
            background: #06b6d4;
        }

        .itcal-event.cuti_izin {
            color: #374151;
            background: linear-gradient(135deg, #f9fafb, #f3f4f6);
            border-color: #e5e7eb;
        }

        .itcal-event.cuti_izin::before {
            background: #9ca3af;
        }

        .dark .itcal-event.kerja {
            color: #bae6fd;
            background: rgba(14, 165, 233, .12);
            border-color: rgba(14, 165, 233, .22);
        }

        .dark .itcal-event.piket {
            color: #fde68a;
            background: rgba(245, 158, 11, .12);
            border-color: rgba(245, 158, 11, .22);
        }

        .dark .itcal-event.maintenance {
            color: #fecdd3;
            background: rgba(244, 63, 94, .12);
            border-color: rgba(244, 63, 94, .22);
        }

        .dark .itcal-event.event_support {
            color: #a5f3fc;
            background: rgba(6, 182, 212, .12);
            border-color: rgba(6, 182, 212, .22);
        }

        .dark .itcal-event.cuti_izin {
            color: #cbd5e1;
            background: rgba(148, 163, 184, .10);
            border-color: rgba(148, 163, 184, .18);
        }

        .itcal-empty {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 86px;
            border-radius: 16px;
            border: 1px dashed var(--itcal-border);
            color: var(--itcal-muted);
            background: rgba(148, 163, 184, .04);
            font-size: 12px;
            font-weight: 600;
        }

        .itcal-mobile {
            display: none;
        }

        .itcal-mobile-day {
            padding: 16px;
            border-bottom: 1px solid var(--itcal-border);
        }

        .itcal-mobile-day:last-child {
            border-bottom: none;
        }

        .itcal-mobile-day.is-today {
            background: rgba(var(--primary-500), .07);
        }

        .itcal-mobile-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .itcal-mobile-date {
            color: var(--itcal-text);
            font-size: 14px;
            font-weight: 900;
        }

        .itcal-mobile-count {
            color: var(--itcal-muted);
            font-size: 12px;
            font-weight: 700;
        }

        .itcal-summary {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 14px;
        }

        .itcal-summary-card {
            display: flex;
            align-items: center;
            gap: 12px;
            border-radius: 20px;
            padding: 16px;
            background: var(--itcal-card);
            border: 1px solid var(--itcal-border);
            box-shadow: var(--itcal-shadow-soft);
        }

        .itcal-summary-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 16px;
        }

        .itcal-summary-card.kerja .itcal-summary-icon {
            color: #0284c7;
            background: #e0f2fe;
        }

        .itcal-summary-card.piket .itcal-summary-icon {
            color: #d97706;
            background: #fef3c7;
        }

        .itcal-summary-card.maintenance .itcal-summary-icon {
            color: #e11d48;
            background: #ffe4e6;
        }

        .itcal-summary-card.event_support .itcal-summary-icon {
            color: #0891b2;
            background: #cffafe;
        }

        .itcal-summary-card.cuti_izin .itcal-summary-icon {
            color: #4b5563;
            background: #f3f4f6;
        }

        .dark .itcal-summary-card.kerja .itcal-summary-icon {
            color: #7dd3fc;
            background: rgba(14, 165, 233, .14);
        }

        .dark .itcal-summary-card.piket .itcal-summary-icon {
            color: #fcd34d;
            background: rgba(245, 158, 11, .14);
        }

        .dark .itcal-summary-card.maintenance .itcal-summary-icon {
            color: #fda4af;
            background: rgba(244, 63, 94, .14);
        }

        .dark .itcal-summary-card.event_support .itcal-summary-icon {
            color: #67e8f9;
            background: rgba(6, 182, 212, .14);
        }

        .dark .itcal-summary-card.cuti_izin .itcal-summary-icon {
            color: #cbd5e1;
            background: rgba(148, 163, 184, .12);
        }

        .itcal-summary-label {
            color: var(--itcal-muted);
            font-size: 12px;
            font-weight: 700;
        }

        .itcal-summary-value {
            margin-top: 2px;
            color: var(--itcal-text);
            font-size: 22px;
            line-height: 1;
            font-weight: 900;
        }

        @media (max-width: 1280px) {
            .itcal-desktop {
                display: none;
            }

            .itcal-mobile {
                display: block;
            }
        }

        @media (max-width: 1024px) {
            .itcal-hero-content {
                grid-template-columns: 1fr;
            }

            .itcal-filter-grid {
                grid-template-columns: 1fr;
            }

            .itcal-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .itcal-calendar-head {
                align-items: flex-start;
                flex-direction: column;
            }

            .itcal-legends {
                justify-content: flex-start;
            }
        }

        @media (max-width: 640px) {
            .itcal-hero {
                padding: 22px;
                border-radius: 24px;
            }

            .itcal-stats {
                grid-template-columns: 1fr;
            }

            .itcal-summary {
                grid-template-columns: 1fr;
            }

            .itcal-filter {
                padding: 16px;
            }
        }
    </style>

    <div class="itcal">
        <div class="itcal-wrap">

            {{-- Hero --}}
            <section class="itcal-hero">
                <div class="itcal-hero-content">
                    <div>
                        <div class="itcal-eyebrow">
                            <x-heroicon-o-calendar-days class="h-4 w-4" />
                            Kalender Jadwal Tim IT
                        </div>

                        <div class="itcal-title">
                            {{ $selectedMonth->translatedFormat('F Y') }}
                        </div>

                        <div class="itcal-subtitle">
                            @if ($selectedUser)
                                Menampilkan jadwal kerja, piket, maintenance, event support, dan cuti/izin untuk
                                <strong>{{ $selectedUser->name }}</strong>.
                            @else
                                Menampilkan seluruh jadwal staff IT. Pilih staff untuk mengunduh kalender dalam bentuk
                                PDF per user.
                            @endif
                        </div>
                    </div>

                    <div class="itcal-stats">
                        <div class="itcal-stat">
                            <div class="itcal-stat-label">Total Jadwal</div>
                            <div class="itcal-stat-value">{{ $totalSchedules }}</div>
                        </div>

                        <div class="itcal-stat">
                            <div class="itcal-stat-label">Kerja</div>
                            <div class="itcal-stat-value">{{ $totalWork }}</div>
                        </div>

                        <div class="itcal-stat">
                            <div class="itcal-stat-label">Piket</div>
                            <div class="itcal-stat-value">{{ $totalStandby }}</div>
                        </div>

                        <div class="itcal-stat">
                            <div class="itcal-stat-label">Maintenance</div>
                            <div class="itcal-stat-value">{{ $totalMaintenance }}</div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Filter --}}
            <section class="itcal-panel itcal-filter">
                <div class="itcal-filter-grid">
                    <div class="itcal-field">
                        <label>
                            <x-heroicon-o-user-group class="h-4 w-4" />
                            Staff IT
                        </label>

                        <select wire:model.live="user_id" class="itcal-input">
                            <option value="">Semua Staff</option>

                            @foreach ($this->users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="itcal-field">
                        <label>
                            <x-heroicon-o-calendar class="h-4 w-4" />
                            Bulan
                        </label>

                        <input type="month" wire:model.live="month" class="itcal-input">
                    </div>

                    <div>
                        @if ($this->downloadUrl)
                            <a href="{{ $this->downloadUrl }}" target="_blank"
                                class="itcal-button itcal-button-primary">
                                <x-heroicon-o-arrow-down-tray class="h-5 w-5" />
                                Download PDF
                            </a>
                        @else
                            <button type="button" disabled class="itcal-button itcal-button-disabled">
                                <x-heroicon-o-arrow-down-tray class="h-5 w-5" />
                                Pilih Staff
                            </button>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Calendar --}}
            <section class="itcal-panel itcal-calendar">
                <div class="itcal-calendar-head">
                    <div>
                        <div class="itcal-calendar-title">
                            Kalender Bulanan
                        </div>

                        <div class="itcal-calendar-desc">
                            {{ $selectedMonth->copy()->startOfMonth()->translatedFormat('d F Y') }}
                            -
                            {{ $selectedMonth->copy()->endOfMonth()->translatedFormat('d F Y') }}
                        </div>
                    </div>

                    <div class="itcal-legends">
                        @foreach ($typeMeta as $type => $meta)
                            <div class="itcal-legend {{ $type }}">
                                <span class="itcal-legend-dot"></span>
                                {{ $meta['label'] }}
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Desktop Calendar --}}
                <div class="itcal-desktop">
                    <div class="itcal-days">
                        @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $dayName)
                            <div class="itcal-day-name">
                                {{ $dayName }}
                            </div>
                        @endforeach
                    </div>

                    @foreach ($calendar as $week)
                        <div class="itcal-week">
                            @foreach ($week as $day)
                                <div @class([
                                    'itcal-cell',
                                    'is-muted' => !$day['is_current_month'],
                                    'is-today' => $day['is_today'],
                                ])>
                                    <div class="itcal-cell-top">
                                        <div class="flex items-center gap-2">
                                            <div class="itcal-date">
                                                {{ $day['date']->format('d') }}
                                            </div>

                                            @if ($day['is_today'])
                                                <div class="itcal-today-badge">
                                                    Hari ini
                                                </div>
                                            @endif
                                        </div>

                                        @if ($day['schedules']->count())
                                            <div class="itcal-count">
                                                {{ $day['schedules']->count() }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="itcal-events">
                                        @forelse ($day['schedules'] as $schedule)
                                            @php
                                                $meta = $typeMeta[$schedule->type] ?? [
                                                    'label' => $schedule->getTypeLabel(),
                                                    'icon' => 'heroicon-o-calendar-days',
                                                ];
                                            @endphp

                                            <div class="itcal-event {{ $schedule->type }}">
                                                <div class="itcal-event-title">
                                                    <x-dynamic-component :component="$meta['icon']" class="h-4 w-4 shrink-0" />
                                                    <span class="truncate">{{ $schedule->getTypeLabel() }}</span>
                                                </div>

                                                <div class="itcal-event-meta">
                                                    <x-heroicon-o-clock class="h-3.5 w-3.5 shrink-0" />
                                                    <span>
                                                        @if ($schedule->start_time && $schedule->end_time)
                                                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}
                                                            -
                                                            {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                                        @else
                                                            Full day
                                                        @endif
                                                    </span>
                                                </div>

                                                <div class="itcal-event-meta">
                                                    <x-heroicon-o-user class="h-3.5 w-3.5 shrink-0" />
                                                    <span>{{ $schedule->user?->name ?? '-' }}</span>
                                                </div>

                                                @if ($schedule->location)
                                                    <div class="itcal-event-meta">
                                                        <x-heroicon-o-map-pin class="h-3.5 w-3.5 shrink-0" />
                                                        <span>{{ $schedule->location }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="itcal-empty">
                                                Tidak ada jadwal
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>

                {{-- Mobile / Tablet List --}}
                <div class="itcal-mobile">
                    @foreach ($calendar as $week)
                        @foreach ($week as $day)
                            @if ($day['is_current_month'])
                                <div @class(['itcal-mobile-day', 'is-today' => $day['is_today']])>
                                    <div class="itcal-mobile-head">
                                        <div>
                                            <div class="itcal-mobile-date">
                                                {{ $day['date']->translatedFormat('l, d F Y') }}
                                            </div>

                                            <div class="itcal-mobile-count">
                                                {{ $day['schedules']->count() }} jadwal
                                            </div>
                                        </div>

                                        @if ($day['is_today'])
                                            <div class="itcal-today-badge">
                                                Hari ini
                                            </div>
                                        @endif
                                    </div>

                                    <div class="itcal-events">
                                        @forelse ($day['schedules'] as $schedule)
                                            @php
                                                $meta = $typeMeta[$schedule->type] ?? [
                                                    'label' => $schedule->getTypeLabel(),
                                                    'icon' => 'heroicon-o-calendar-days',
                                                ];
                                            @endphp

                                            <div class="itcal-event {{ $schedule->type }}">
                                                <div class="itcal-event-title">
                                                    <x-dynamic-component :component="$meta['icon']" class="h-4 w-4 shrink-0" />
                                                    <span>{{ $schedule->getTypeLabel() }}</span>
                                                </div>

                                                <div class="itcal-event-meta">
                                                    <x-heroicon-o-clock class="h-3.5 w-3.5 shrink-0" />
                                                    <span>
                                                        @if ($schedule->start_time && $schedule->end_time)
                                                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}
                                                            -
                                                            {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                                        @else
                                                            Full day
                                                        @endif
                                                    </span>
                                                </div>

                                                <div class="itcal-event-meta">
                                                    <x-heroicon-o-user class="h-3.5 w-3.5 shrink-0" />
                                                    <span>{{ $schedule->user?->name ?? '-' }}</span>
                                                </div>

                                                @if ($schedule->location)
                                                    <div class="itcal-event-meta">
                                                        <x-heroicon-o-map-pin class="h-3.5 w-3.5 shrink-0" />
                                                        <span>{{ $schedule->location }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="itcal-empty">
                                                Tidak ada jadwal
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endforeach
                </div>
            </section>

            {{-- Summary --}}
            <section class="itcal-summary">
                <div class="itcal-summary-card kerja">
                    <div class="itcal-summary-icon">
                        <x-heroicon-o-briefcase class="h-5 w-5" />
                    </div>
                    <div>
                        <div class="itcal-summary-label">Kerja</div>
                        <div class="itcal-summary-value">{{ $totalWork }}</div>
                    </div>
                </div>

                <div class="itcal-summary-card maintenance">
                    <div class="itcal-summary-icon">
                        <x-heroicon-o-wrench-screwdriver class="h-5 w-5" />
                    </div>
                    <div>
                        <div class="itcal-summary-label">Maintenance</div>
                        <div class="itcal-summary-value">{{ $totalMaintenance }}</div>
                    </div>
                </div>

                <div class="itcal-summary-card cuti_dp">
                    <div class="itcal-summary-icon">
                        <x-heroicon-o-document-text class="h-5 w-5" />
                    </div>
                    <div>
                        <div class="itcal-summary-label">Cuti / DP</div>
                        <div class="itcal-summary-value">{{ $totalCutiDp }}</div>
                    </div>
                </div>

                <div class="itcal-summary-card ijin">
                    <div class="itcal-summary-icon">
                        <x-heroicon-o-no-symbol class="h-5 w-5" />
                    </div>
                    <div>
                        <div class="itcal-summary-label">Ijin</div>
                        <div class="itcal-summary-value">{{ $totalIjin }}</div>
                    </div>
                </div>
            </section>

        </div>
    </div>
</x-filament-panels::page>
