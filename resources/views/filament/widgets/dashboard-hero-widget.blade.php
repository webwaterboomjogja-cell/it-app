<x-filament-widgets::widget>
    <style>
        .mit-hero {
            position: relative;
            overflow: hidden;
            border-radius: 24px;
            padding: 28px;
            background:
                radial-gradient(circle at top right, rgba(245, 158, 11, .18), transparent 32%),
                radial-gradient(circle at bottom left, rgba(59, 130, 246, .16), transparent 35%),
                linear-gradient(135deg, #18181b 0%, #111827 55%, #0f172a 100%);
            border: 1px solid rgba(255, 255, 255, .08);
            box-shadow: 0 20px 55px rgba(0, 0, 0, .28);
        }

        .mit-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, .035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .035) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: linear-gradient(to bottom, black, transparent);
            pointer-events: none;
        }

        .mit-hero-content {
            position: relative;
            z-index: 2;
        }

        .mit-hero-top {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin-bottom: 32px;
        }

        .mit-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 700;
            color: #f8fafc;
            border: 1px solid rgba(255, 255, 255, .14);
            background: rgba(255, 255, 255, .06);
            backdrop-filter: blur(10px);
        }

        .mit-badge-warning {
            color: #fbbf24;
            border-color: rgba(251, 191, 36, .25);
            background: rgba(251, 191, 36, .08);
        }

        .mit-label {
            margin-bottom: 10px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .mit-title {
            max-width: 820px;
            margin: 0;
            font-size: clamp(30px, 4vw, 48px);
            line-height: 1.05;
            font-weight: 800;
            letter-spacing: -0.04em;
            color: #ffffff;
        }

        .mit-title span {
            color: #fbbf24;
        }

        .mit-desc {
            max-width: 760px;
            margin-top: 16px;
            font-size: 15px;
            line-height: 1.8;
            color: #cbd5e1;
        }

        .mit-info-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 28px;
        }

        .mit-info-card {
            display: flex;
            align-items: center;
            gap: 14px;
            min-height: 88px;
            padding: 18px;
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, .09);
            background: rgba(255, 255, 255, .055);
            backdrop-filter: blur(12px);
            transition: .2s ease;
        }

        .mit-info-card:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, .08);
            border-color: rgba(251, 191, 36, .28);
        }

        .mit-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            width: 46px;
            height: 46px;
            border-radius: 15px;
            color: #fbbf24;
            background: rgba(251, 191, 36, .12);
        }

        .mit-icon-blue {
            color: #60a5fa;
            background: rgba(96, 165, 250, .12);
        }

        .mit-icon-green {
            color: #34d399;
            background: rgba(52, 211, 153, .12);
        }

        .mit-info-label {
            margin: 0 0 4px;
            font-size: 12px;
            color: #94a3b8;
        }

        .mit-info-value {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            color: #ffffff;
        }

        @media (max-width: 900px) {
            .mit-info-grid {
                grid-template-columns: 1fr;
            }

            .mit-hero {
                padding: 22px;
            }
        }
    </style>

    <section class="mit-hero">
        <div class="mit-hero-content">
            <div class="mit-hero-top">
                <div class="mit-badge">
                    <x-heroicon-o-sparkles style="width: 18px; height: 18px;" />
                    Sistem Manajemen IT
                </div>

                <div class="mit-badge mit-badge-warning">
                    <span
                        style="width: 8px; height: 8px; border-radius: 999px; background: #22c55e; display: inline-block;"></span>
                    Sistem Aktif
                </div>
            </div>

            <div class="mit-label">
                Dashboard Overview
            </div>

            <h1 class="mit-title">
                Selamat datang,
                <span>{{ auth()->user()->name ?? 'Admin' }}</span>
            </h1>

            <p class="mit-desc">
                Pantau data master, pengguna, hak akses, aset IT, laporan pekerjaan,
                dan jadwal maintenance dalam satu dashboard yang rapi dan terpusat.
            </p>

            <div class="mit-info-grid">
                <div class="mit-info-card">
                    <div class="mit-icon mit-icon-blue">
                        <x-heroicon-o-user-circle style="width: 24px; height: 24px;" />
                    </div>

                    <div>
                        <p class="mit-info-label">Login Sebagai</p>
                        <p class="mit-info-value">{{ auth()->user()->name ?? 'Admin' }}</p>
                    </div>
                </div>

                <div class="mit-info-card">
                    <div class="mit-icon">
                        <x-heroicon-o-calendar-days style="width: 24px; height: 24px;" />
                    </div>

                    <div>
                        <p class="mit-info-label">Hari Ini</p>
                        <p class="mit-info-value">{{ now()->translatedFormat('d F Y') }}</p>
                    </div>
                </div>

                <div class="mit-info-card">
                    <div class="mit-icon mit-icon-green">
                        <x-heroicon-o-shield-check style="width: 24px; height: 24px;" />
                    </div>

                    <div>
                        <p class="mit-info-label">Status</p>
                        <p class="mit-info-value">Aman & Siap Digunakan</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-filament-widgets::widget>
