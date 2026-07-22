<x-filament-widgets::widget class="mit-widget-wrapper"
    style="
        grid-column: 1 / -1 !important;
        width: 100% !important;
        min-width: 0 !important;
    ">
    @once
        <style>
            .mit-widget-wrapper,
            .mit-widget-wrapper *,
            .mit-widget-wrapper *::before,
            .mit-widget-wrapper *::after {
                box-sizing: border-box;
            }

            /*
                 * Container query digunakan agar tampilan mengikuti
                 * lebar widget, bukan hanya lebar browser.
                 */
            .mit-widget-wrapper {
                display: block;
                width: 100%;
                max-width: none;
                min-width: 0;
                container-type: inline-size;
            }

            .mit-hero {
                position: relative;
                width: 100%;
                min-width: 0;
                overflow: hidden;
                padding: clamp(22px, 3vw, 34px);
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 24px;
                isolation: isolate;

                background:
                    radial-gradient(circle at top right,
                        rgba(245, 158, 11, 0.18),
                        transparent 34%),
                    radial-gradient(circle at bottom left,
                        rgba(59, 130, 246, 0.16),
                        transparent 38%),
                    linear-gradient(135deg,
                        #18181b 0%,
                        #111827 55%,
                        #0f172a 100%);

                box-shadow:
                    0 20px 55px rgba(0, 0, 0, 0.28);
            }

            .mit-hero::before {
                content: "";
                position: absolute;
                inset: 0;
                z-index: -1;
                pointer-events: none;

                background-image:
                    linear-gradient(rgba(255, 255, 255, 0.035) 1px,
                        transparent 1px),
                    linear-gradient(90deg,
                        rgba(255, 255, 255, 0.035) 1px,
                        transparent 1px);

                background-size: 42px 42px;

                mask-image: linear-gradient(to bottom,
                        black,
                        transparent);

                -webkit-mask-image: linear-gradient(to bottom,
                        black,
                        transparent);
            }

            .mit-hero-content {
                position: relative;
                z-index: 2;
                width: 100%;
                min-width: 0;
            }

            .mit-hero-top {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 10px;
                margin-bottom: clamp(24px, 3vw, 34px);
            }

            .mit-badge {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                width: fit-content;
                max-width: 100%;
                min-height: 36px;
                padding: 8px 14px;

                border: 1px solid rgba(255, 255, 255, 0.14);
                border-radius: 999px;

                color: #f8fafc;
                background: rgba(255, 255, 255, 0.06);

                font-size: 13px;
                font-weight: 700;
                line-height: 1.2;
                white-space: nowrap;

                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
            }

            .mit-badge-warning {
                color: #fbbf24;
                border-color: rgba(251, 191, 36, 0.28);
                background: rgba(251, 191, 36, 0.08);
            }

            .mit-status-dot {
                display: inline-block;
                flex: 0 0 auto;
                width: 8px;
                height: 8px;

                border-radius: 999px;
                background: #22c55e;

                box-shadow:
                    0 0 0 4px rgba(34, 197, 94, 0.12);
            }

            .mit-label {
                margin-bottom: 10px;

                color: #93c5fd;

                font-size: 12px;
                font-weight: 800;
                line-height: 1.4;
                letter-spacing: 0.14em;
                text-transform: uppercase;
            }

            .mit-title {
                width: 100%;
                max-width: 900px;
                margin: 0;

                color: #ffffff;

                font-size: clamp(30px, 4vw, 50px);
                font-weight: 800;
                line-height: 1.08;
                letter-spacing: -0.04em;

                /*
                     * Jangan gunakan anywhere karena dapat
                     * memecah kata menjadi huruf per huruf.
                     */
                overflow-wrap: break-word;
                word-break: normal;
            }

            .mit-title span {
                color: #fbbf24;
            }

            .mit-desc {
                width: 100%;
                max-width: 790px;
                margin: 16px 0 0;

                color: #cbd5e1;

                font-size: clamp(14px, 1.4vw, 16px);
                line-height: 1.75;

                overflow-wrap: break-word;
                word-break: normal;
            }

            .mit-info-grid {
                display: grid;
                grid-template-columns:
                    repeat(3, minmax(0, 1fr));

                width: 100%;
                min-width: 0;
                gap: 14px;
                margin-top: clamp(24px, 3vw, 30px);
            }

            .mit-info-card {
                display: flex;
                align-items: center;
                gap: 14px;

                width: 100%;
                min-width: 0;
                min-height: 92px;
                padding: 18px;

                border: 1px solid rgba(255, 255, 255, 0.09);
                border-radius: 18px;

                background: rgba(255, 255, 255, 0.055);

                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);

                transition:
                    transform 0.2s ease,
                    background-color 0.2s ease,
                    border-color 0.2s ease,
                    box-shadow 0.2s ease;
            }

            .mit-info-content {
                flex: 1 1 auto;
                width: 100%;
                min-width: 0;
            }

            .mit-icon {
                display: flex;
                align-items: center;
                justify-content: center;
                flex: 0 0 46px;

                width: 46px;
                height: 46px;

                border-radius: 15px;

                color: #fbbf24;
                background: rgba(251, 191, 36, 0.12);
            }

            .mit-icon svg {
                width: 24px;
                height: 24px;
            }

            .mit-icon-blue {
                color: #60a5fa;
                background: rgba(96, 165, 250, 0.12);
            }

            .mit-icon-green {
                color: #34d399;
                background: rgba(52, 211, 153, 0.12);
            }

            .mit-info-label {
                margin: 0 0 4px;

                color: #94a3b8;

                font-size: 12px;
                line-height: 1.4;
            }

            .mit-info-value {
                display: block;
                width: 100%;
                min-width: 0;
                margin: 0;

                color: #ffffff;

                font-size: 15px;
                font-weight: 800;
                line-height: 1.45;

                /*
                     * Mencegah teks "Aman & Siap Digunakan"
                     * menjadi vertikal huruf per huruf.
                     */
                word-break: normal;
                overflow-wrap: break-word;
                white-space: normal;
            }

            @media (hover: hover) and (pointer: fine) {
                .mit-info-card:hover {
                    transform: translateY(-3px);

                    border-color:
                        rgba(251, 191, 36, 0.28);

                    background:
                        rgba(255, 255, 255, 0.08);

                    box-shadow:
                        0 12px 28px rgba(0, 0, 0, 0.18);
                }
            }

            /*
                 * Container sedang / tablet.
                 * Aturan mengikuti lebar widget.
                 */
            @container (max-width: 900px)

                {
                .mit-info-grid {
                    grid-template-columns:
                        repeat(2, minmax(0, 1fr));
                }

                .mit-info-card:last-child {
                    grid-column: 1 / -1;
                }

                .mit-title {
                    font-size: clamp(30px, 6cqi, 42px);
                }
            }

            /*
                 * Container kecil.
                 */
            @container (max-width: 650px)

                {
                .mit-hero {
                    padding: 22px 18px;
                    border-radius: 20px;
                }

                .mit-hero-top {
                    gap: 8px;
                    margin-bottom: 24px;
                }

                .mit-badge {
                    min-height: 34px;
                    padding: 7px 12px;
                    font-size: 12px;
                }

                .mit-title {
                    font-size: clamp(28px, 9cqi, 36px);
                    line-height: 1.12;
                    letter-spacing: -0.03em;
                }

                .mit-desc {
                    margin-top: 14px;
                    font-size: 14px;
                    line-height: 1.7;
                }

                .mit-info-grid {
                    grid-template-columns: minmax(0, 1fr);
                    gap: 10px;
                    margin-top: 22px;
                }

                .mit-info-card,
                .mit-info-card:last-child {
                    grid-column: auto;
                    min-height: 78px;
                    padding: 14px;
                    border-radius: 15px;
                }

                .mit-icon {
                    flex-basis: 42px;
                    width: 42px;
                    height: 42px;
                    border-radius: 13px;
                }

                .mit-icon svg {
                    width: 22px;
                    height: 22px;
                }

                .mit-info-value {
                    font-size: 14px;
                }
            }

            /*
                 * Mobile sangat kecil.
                 */
            @container (max-width: 390px)

                {
                .mit-hero {
                    padding: 18px 14px;
                    border-radius: 17px;
                }

                .mit-hero-top {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .mit-badge {
                    white-space: normal;
                }

                .mit-label {
                    font-size: 10px;
                    letter-spacing: 0.11em;
                }

                .mit-title {
                    font-size: 27px;
                }

                .mit-info-card {
                    gap: 12px;
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .mit-info-card {
                    transition: none;
                }

                .mit-info-card:hover {
                    transform: none;
                }
            }
        </style>
    @endonce

    <section class="mit-hero">
        <div class="mit-hero-content">
            <div class="mit-hero-top">
                <div class="mit-badge">
                    <x-heroicon-o-sparkles aria-hidden="true" style="width: 18px; height: 18px;" />

                    <span>Sistem Manajemen IT</span>
                </div>

                <div class="mit-badge mit-badge-warning">
                    <span class="mit-status-dot" aria-hidden="true"></span>

                    <span>Sistem Aktif</span>
                </div>
            </div>

            <div class="mit-label">
                Dashboard Overview
            </div>

            <h1 class="mit-title">
                Selamat datang,
                <span>
                    {{ auth()->user()?->name ?? 'Admin' }}
                </span>
            </h1>

            <p class="mit-desc">
                Pantau data master, pengguna, hak akses,
                aset IT, laporan pekerjaan, dan jadwal
                maintenance dalam satu dashboard yang
                rapi dan terpusat.
            </p>

            <div class="mit-info-grid">
                <article class="mit-info-card">
                    <div class="mit-icon mit-icon-blue">
                        <x-heroicon-o-user-circle aria-hidden="true" />
                    </div>

                    <div class="mit-info-content">
                        <p class="mit-info-label">
                            Login Sebagai
                        </p>

                        <p class="mit-info-value">
                            {{ auth()->user()?->name ?? 'Admin' }}
                        </p>
                    </div>
                </article>

                <article class="mit-info-card">
                    <div class="mit-icon">
                        <x-heroicon-o-calendar-days aria-hidden="true" />
                    </div>

                    <div class="mit-info-content">
                        <p class="mit-info-label">
                            Hari Ini
                        </p>

                        <p class="mit-info-value">
                            {{ now()->translatedFormat('d F Y') }}
                        </p>
                    </div>
                </article>

                <article class="mit-info-card">
                    <div class="mit-icon mit-icon-green">
                        <x-heroicon-o-shield-check aria-hidden="true" />
                    </div>

                    <div class="mit-info-content">
                        <p class="mit-info-label">
                            Status
                        </p>

                        <p class="mit-info-value">
                            Aman &amp; Siap Digunakan
                        </p>
                    </div>
                </article>
            </div>
        </div>
    </section>
</x-filament-widgets::widget>
