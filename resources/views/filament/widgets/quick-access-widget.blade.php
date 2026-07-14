<x-filament-widgets::widget>
    <style>
        .mit-quick-section {
            overflow: hidden;
            border-radius: 24px;
            background:
                radial-gradient(circle at top right, rgba(245, 158, 11, .10), transparent 28%),
                linear-gradient(135deg, #18181b 0%, #111827 55%, #0f172a 100%);
            border: 1px solid rgba(255, 255, 255, .08);
            box-shadow: 0 18px 45px rgba(0, 0, 0, .22);
        }

        .mit-quick-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 24px 26px;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        .mit-quick-title-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .mit-quick-header-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            border-radius: 16px;
            color: #fbbf24;
            background: rgba(251, 191, 36, .12);
            border: 1px solid rgba(251, 191, 36, .18);
        }

        .mit-quick-heading {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.02em;
        }

        .mit-quick-description {
            margin: 5px 0 0;
            font-size: 14px;
            line-height: 1.6;
            color: #94a3b8;
        }

        .mit-quick-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 7px 12px;
            font-size: 12px;
            font-weight: 700;
            color: #fbbf24;
            background: rgba(251, 191, 36, .10);
            border: 1px solid rgba(251, 191, 36, .20);
            white-space: nowrap;
        }

        .mit-quick-body {
            padding: 24px 26px 26px;
        }

        .mit-quick-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .mit-quick-card {
            position: relative;
            display: block;
            min-height: 155px;
            padding: 20px;
            overflow: hidden;
            border-radius: 20px;
            text-decoration: none;
            color: inherit;
            background: rgba(255, 255, 255, .055);
            border: 1px solid rgba(255, 255, 255, .09);
            backdrop-filter: blur(14px);
            transition: transform .22s ease, border-color .22s ease, background .22s ease, box-shadow .22s ease;
        }

        .mit-quick-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(251, 191, 36, .16), transparent 30%),
                radial-gradient(circle at bottom left, rgba(96, 165, 250, .12), transparent 32%);
            opacity: 0;
            transition: opacity .22s ease;
            pointer-events: none;
        }

        .mit-quick-card:hover {
            transform: translateY(-4px);
            border-color: rgba(251, 191, 36, .32);
            background: rgba(255, 255, 255, .075);
            box-shadow: 0 18px 35px rgba(0, 0, 0, .28);
        }

        .mit-quick-card:hover::before {
            opacity: 1;
        }

        .mit-quick-card-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .mit-quick-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 22px;
        }

        .mit-quick-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 16px;
            color: #fbbf24;
            background: rgba(251, 191, 36, .12);
            border: 1px solid rgba(251, 191, 36, .16);
            transition: background .22s ease, color .22s ease, transform .22s ease;
        }

        .mit-quick-card:hover .mit-quick-icon {
            color: #111827;
            background: #fbbf24;
            transform: scale(1.04);
        }

        .mit-quick-icon svg,
        .mit-quick-arrow svg,
        .mit-quick-header-icon svg {
            width: 24px;
            height: 24px;
        }

        .mit-quick-arrow {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 999px;
            color: #94a3b8;
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .08);
            transition: color .22s ease, background .22s ease, transform .22s ease;
        }

        .mit-quick-card:hover .mit-quick-arrow {
            color: #ffffff;
            background: rgba(255, 255, 255, .12);
            transform: translate(2px, -2px);
        }

        .mit-quick-card-title {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.01em;
        }

        .mit-quick-card-desc {
            margin: 8px 0 0;
            font-size: 13px;
            line-height: 1.7;
            color: #94a3b8;
        }

        .mit-quick-card-footer {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: auto;
            padding-top: 20px;
            font-size: 12px;
            font-weight: 700;
            color: #fbbf24;
        }

        .mit-quick-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: #fbbf24;
            box-shadow: 0 0 16px rgba(251, 191, 36, .65);
        }

        @media (max-width: 1200px) {
            .mit-quick-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 720px) {
            .mit-quick-header {
                align-items: flex-start;
                flex-direction: column;
                padding: 22px;
            }

            .mit-quick-body {
                padding: 22px;
            }

            .mit-quick-grid {
                grid-template-columns: 1fr;
            }

            .mit-quick-card {
                min-height: 145px;
            }
        }
    </style>

    <section class="mit-quick-section">
        <div class="mit-quick-header">
            <div class="mit-quick-title-wrap">
                <div class="mit-quick-header-icon">
                    <x-heroicon-o-bolt />
                </div>

                <div>
                    <h2 class="mit-quick-heading">Akses Cepat</h2>
                    <p class="mit-quick-description">
                        Menu utama untuk mengelola data sistem Manajemen IT.
                    </p>
                </div>
            </div>

            <div class="mit-quick-badge">
                {{ count($menus) }} Menu Tersedia
            </div>
        </div>

        <div class="mit-quick-body">
            <div class="mit-quick-grid">
                @foreach ($menus as $menu)
                    <a href="{{ $menu['url'] }}" class="mit-quick-card">
                        <div class="mit-quick-card-content">
                            <div class="mit-quick-card-top">
                                <div class="mit-quick-icon">
                                    <x-dynamic-component :component="$menu['icon']" />
                                </div>

                                <div class="mit-quick-arrow">
                                    <x-heroicon-o-arrow-up-right />
                                </div>
                            </div>

                            <div>
                                <h3 class="mit-quick-card-title">
                                    {{ $menu['title'] }}
                                </h3>

                                <p class="mit-quick-card-desc">
                                    {{ $menu['description'] }}
                                </p>
                            </div>

                            <div class="mit-quick-card-footer">
                                <span class="mit-quick-dot"></span>
                                Buka Menu
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
</x-filament-widgets::widget>
