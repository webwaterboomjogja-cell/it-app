<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Jadwal IT {{ $user->name }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 0;
            padding: 18px;
        }

        .header {
            margin-bottom: 16px;
            border-bottom: 2px solid #111827;
            padding-bottom: 10px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
        }

        .subtitle {
            margin-top: 4px;
            color: #4b5563;
        }

        table.calendar {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.calendar th {
            background: #111827;
            color: #ffffff;
            padding: 8px;
            border: 1px solid #111827;
            font-size: 11px;
        }

        table.calendar td {
            height: 95px;
            vertical-align: top;
            border: 1px solid #d1d5db;
            padding: 6px;
        }

        .date {
            font-weight: bold;
            margin-bottom: 4px;
        }

        .muted {
            color: #9ca3af;
        }

        .schedule {
            border-radius: 6px;
            padding: 4px;
            margin-bottom: 4px;
            border: 1px solid #d1d5db;
            background: #f9fafb;
        }

        .schedule-title {
            font-weight: bold;
        }

        .kerja {
            border-color: #93c5fd;
            background: #eff6ff;
        }

        .piket {
            border-color: #fcd34d;
            background: #fffbeb;
        }

        .maintenance {
            border-color: #fca5a5;
            background: #fef2f2;
        }

        .event_support {
            border-color: #67e8f9;
            background: #ecfeff;
        }

        .cuti_izin {
            border-color: #d1d5db;
            background: #f3f4f6;
        }

        .footer {
            margin-top: 10px;
            font-size: 10px;
            color: #6b7280;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1 class="title">Kalender Jadwal Tim IT</h1>
        <div class="subtitle">
            Staff: <strong>{{ $user->name }}</strong> |
            Bulan: <strong>{{ $month->translatedFormat('F Y') }}</strong>
        </div>
    </div>

    <table class="calendar">
        <thead>
            <tr>
                <th>Senin</th>
                <th>Selasa</th>
                <th>Rabu</th>
                <th>Kamis</th>
                <th>Jumat</th>
                <th>Sabtu</th>
                <th>Minggu</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($weeks as $week)
                <tr>
                    @foreach ($week as $day)
                        <td>
                            <div class="date {{ $day['is_current_month'] ? '' : 'muted' }}">
                                {{ $day['date']->format('d') }}
                            </div>

                            @forelse ($day['schedules'] as $schedule)
                                <div class="schedule {{ $schedule->type }}">
                                    <div class="schedule-title">
                                        {{ $schedule->getTypeLabel() }}
                                    </div>

                                    <div>
                                        @if ($schedule->start_time && $schedule->end_time)
                                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}
                                            -
                                            {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                        @else
                                            -
                                        @endif
                                    </div>

                                    @if ($schedule->location)
                                        <div>
                                            {{ $schedule->location }}
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <span class="muted">-</span>
                            @endforelse
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada {{ now()->translatedFormat('d F Y H:i') }}
    </div>

</body>

</html>
