<?php

namespace App\Filament\Pages;

use App\Exports\AssetsExport;
use App\Exports\DailyReportsExport;
use App\Exports\MonthlyItReportExport;
use App\Models\AssetCategory;
use App\Models\DailyReport;
use App\Models\Itassests;

use App\Models\Locations;
use App\Models\User;
use App\Models\WorkCategory;
use App\Services\Exports\AssetExportService;
use App\Services\Exports\DailyReportExportService;
use App\Services\Exports\ExportArchiveService;
use App\Services\Exports\MonthlyReportExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ExportData extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon =
        'heroicon-o-arrow-down-tray';

    protected static ?string $navigationGroup =
        'Laporan';

    protected static ?string $navigationLabel =
        'Export Data';

    protected static ?string $title =
        'Pusat Export Data';

    protected static ?int $navigationSort = 90;

    protected static string $view =
        'filament.pages.export-data';

    public ?array $data = [];

   
    public static function canAccess(): bool
    {
        return auth()->user()?->can(
            'page_ExportData'
        ) ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'report_type' => 'assets',
            'format' => 'xlsx',

            'start_date' => now()
                ->startOfMonth()
                ->toDateString(),

            'end_date' => now()
                ->toDateString(),

            'month' => now()->month,
            'year' => now()->year,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Jenis Laporan')
                    ->description(
                        'Pilih data dan format dokumen yang akan dibuat.'
                    )
                    ->icon(
                        'heroicon-o-document-arrow-down'
                    )
                    ->schema([
                        Forms\Components\Select::make(
                            'report_type'
                        )
                            ->label('Jenis Data')
                            ->options([
                                'assets' =>
                                    'Data Inventaris Aset',

                                'daily_reports' =>
                                    'Laporan Harian IT',

                                'monthly_reports' =>
                                    'Laporan Bulanan IT',
                            ])
                            ->required()
                            ->live()
                            ->native(false),

                        Forms\Components\Select::make(
                            'format'
                        )
                            ->label('Format File')
                            ->options([
                                'xlsx' =>
                                    'Excel (.xlsx)',

                                'pdf' =>
                                    'PDF (.pdf)',
                            ])
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                /*
                |--------------------------------------------------------------------------
                | Filter aset
                |--------------------------------------------------------------------------
                */

                Section::make(
                    'Filter Inventaris Aset'
                )
                    ->description(
                        'Batasi data berdasarkan kategori, lokasi, dan status aset.'
                    )
                    ->icon(
                        'heroicon-o-computer-desktop'
                    )
                    ->visible(
                        fn (Get $get): bool =>
                            $get('report_type') ===
                            'assets'
                    )
                    ->schema([
                        Forms\Components\Select::make(
                            'asset_category_id'
                        )
                            ->label('Kategori Aset')
                            ->options(
                                fn (): array =>
                                    Assetcategory::query()
                                        ->orderBy('name')
                                        ->pluck(
                                            'name',
                                            'id'
                                        )
                                        ->all()
                            )
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder(
                                'Semua kategori'
                            ),

                        Forms\Components\Select::make(
                            'location_id'
                        )
                            ->label('Lokasi Aset')
                            ->options(
                                fn (): array =>
                                    Locations::query()
                                        ->orderBy('name')
                                        ->pluck(
                                            'name',
                                            'id'
                                        )
                                        ->all()
                            )
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder(
                                'Semua lokasi'
                            ),

                        Forms\Components\Select::make(
                            'asset_status'
                        )
                            ->label('Status Aset')
                            ->options([
                                'aktif' => 'Aktif',
                                'rusak' => 'Rusak',

                                'maintenance' =>
                                    'Maintenance',

                                'nonaktif' =>
                                    'Nonaktif',

                                'hilang' => 'Hilang',
                            ])
                            ->native(false)
                            ->placeholder(
                                'Semua status'
                            ),
                    ])
                    ->columns(3),

                /*
                |--------------------------------------------------------------------------
                | Filter laporan harian
                |--------------------------------------------------------------------------
                */

                Section::make(
                    'Filter Laporan Harian'
                )
                    ->description(
                        'Tentukan periode, staff, kategori, status, prioritas, lokasi, durasi, dan aset.'
                    )
                    ->icon(
                        'heroicon-o-clipboard-document-list'
                    )
                    ->visible(
                        fn (Get $get): bool =>
                            $get('report_type') ===
                            'daily_reports'
                    )
                    ->schema([
                        Forms\Components\DatePicker::make(
                            'start_date'
                        )
                            ->label('Tanggal Mulai')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(
                                fn (Get $get): bool =>
                                    $get(
                                        'report_type'
                                    ) ===
                                    'daily_reports'
                            ),

                        Forms\Components\DatePicker::make(
                            'end_date'
                        )
                            ->label('Tanggal Selesai')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->afterOrEqual(
                                'start_date'
                            )
                            ->required(
                                fn (Get $get): bool =>
                                    $get(
                                        'report_type'
                                    ) ===
                                    'daily_reports'
                            ),

                        Forms\Components\Select::make(
                            'user_id'
                        )
                            ->label('Staff IT')
                            ->options(
                                fn (): array =>
                                    User::query()
                                        ->orderBy('name')
                                        ->pluck(
                                            'name',
                                            'id'
                                        )
                                        ->all()
                            )
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder(
                                'Semua staff'
                            ),

                        Forms\Components\Select::make(
                            'work_category_id'
                        )
                            ->label(
                                'Kategori Pekerjaan'
                            )
                            ->options(
                                fn (): array =>
                                    WorkCategory::query()
                                        ->orderBy('name')
                                        ->pluck(
                                            'name',
                                            'id'
                                        )
                                        ->all()
                            )
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder(
                                'Semua kategori'
                            ),

                        Forms\Components\Select::make(
                            'work_status'
                        )
                            ->label(
                                'Status Pekerjaan'
                            )
                            ->options([
                                'selesai' =>
                                    'Selesai',

                                'proses' =>
                                    'Proses',

                                'tertunda' =>
                                    'Tertunda',
                            ])
                            ->native(false)
                            ->placeholder(
                                'Semua status pekerjaan'
                            ),

                        Forms\Components\Select::make(
                            'review_status'
                        )
                            ->label('Status Review')
                            ->options([
                                'draft' => 'Draft',

                                'dikirim' =>
                                    'Dikirim',

                                'direview' =>
                                    'Direview',
                            ])
                            ->native(false)
                            ->placeholder(
                                'Semua status review'
                            ),

                        Forms\Components\Select::make(
                            'priority'
                        )
                            ->label('Prioritas')
                            ->options([
                                'rendah' =>
                                    'Rendah',

                                'normal' =>
                                    'Normal',

                                'tinggi' =>
                                    'Tinggi',

                                'urgent' =>
                                    'Urgent',
                            ])
                            ->native(false)
                            ->placeholder(
                                'Semua prioritas'
                            ),

                        Forms\Components\Select::make(
                            'location'
                        )
                            ->label(
                                'Lokasi Pekerjaan'
                            )
                            ->options(
                                fn (): array =>
                                    Dailyreport::query()
                                        ->whereNotNull(
                                            'location'
                                        )
                                        ->where(
                                            'location',
                                            '!=',
                                            ''
                                        )
                                        ->distinct()
                                        ->orderBy(
                                            'location'
                                        )
                                        ->pluck(
                                            'location',
                                            'location'
                                        )
                                        ->all()
                            )
                            ->searchable()
                            ->native(false)
                            ->placeholder(
                                'Semua lokasi'
                            ),

                        Forms\Components\TextInput::make(
                            'duration_min'
                        )
                            ->label(
                                'Durasi Minimum'
                            )
                            ->numeric()
                            ->minValue(0)
                            ->suffix('menit'),

                        Forms\Components\TextInput::make(
                            'duration_max'
                        )
                            ->label(
                                'Durasi Maksimum'
                            )
                            ->numeric()
                            ->minValue(0)
                            ->suffix('menit'),

                        Forms\Components\Select::make(
                            'asset_id'
                        )
                            ->label('Aset Terkait')
                            ->options(
                                fn (): array =>
                                    Itassests::query()
                                        ->orderBy('code')
                                        ->get()
                                        ->mapWithKeys(
                                            function (
                                                Itassests $asset
                                            ): array {
                                                return [
                                                    $asset->id =>
                                                        collect([
                                                            $asset->code,
                                                            $asset->name,
                                                        ])
                                                            ->filter()
                                                            ->implode(
                                                                ' — '
                                                            ),
                                                ];
                                            }
                                        )
                                        ->all()
                            )
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder(
                                'Semua aset'
                            ),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Filter laporan bulanan
                |--------------------------------------------------------------------------
                */

                Section::make(
                    'Filter Laporan Bulanan'
                )
                    ->description(
                        'Pilih laporan bulanan yang sudah digenerate.'
                    )
                    ->icon(
                        'heroicon-o-calendar-days'
                    )
                    ->visible(
                        fn (Get $get): bool =>
                            $get('report_type') ===
                            'monthly_reports'
                    )
                    ->schema([
                        Forms\Components\Select::make(
                            'month'
                        )
                            ->label('Bulan')
                            ->options([
                                1 => 'Januari',
                                2 => 'Februari',
                                3 => 'Maret',
                                4 => 'April',
                                5 => 'Mei',
                                6 => 'Juni',
                                7 => 'Juli',
                                8 => 'Agustus',
                                9 => 'September',
                                10 => 'Oktober',
                                11 => 'November',
                                12 => 'Desember',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make(
                            'year'
                        )
                            ->label('Tahun')
                            ->options(
                                $this->yearOptions()
                            )
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    /*
    |--------------------------------------------------------------------------
    | Router proses export
    |--------------------------------------------------------------------------
    */

    public function exportData()
    {
        $state = $this->form->getState();

        $reportType = (string) (
            $state['report_type'] ?? ''
        );

        $this->authorizeReportExport(
            $reportType
        );

        return match ($reportType) {
            'assets' =>
                $this->exportAssets($state),

            'daily_reports' =>
                $this->exportDailyReports(
                    $state
                ),

            'monthly_reports' =>
                $this->exportMonthlyReport(
                    $state
                ),

            default =>
                $this->invalidReportType(),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | BAGIAN 11 — Export aset dan arsip
    |--------------------------------------------------------------------------
    */

    private function exportAssets(
        array $state
    ) {
        $filters = Arr::only($state, [
            'asset_category_id',
            'location_id',
            'asset_status',
        ]);

        $format = (string) (
            $state['format'] ?? ''
        );

        if (! in_array(
            $format,
            ['xlsx', 'pdf'],
            true
        )) {
            Notification::make()
                ->title('Format tidak valid')
                ->danger()
                ->send();

            return null;
        }

        $assetService = app(
            AssetExportService::class
        );

        if (! $assetService
            ->query($filters)
            ->exists()
        ) {
            Notification::make()
                ->title('Data tidak ditemukan')
                ->body(
                    'Tidak ada data aset sesuai filter.'
                )
                ->warning()
                ->send();

            return null;
        }

        $generatedBy =
            auth()->user()?->name
            ?? 'Sistem';

        $generatedAt = now();

        $archiveService = app(
            ExportArchiveService::class
        );

        $history = $archiveService->start(
            reportType: 'assets',
            format: $format,
            filters: $filters,
            generatedBy: $generatedBy
        );

        $filename = sprintf(
            'laporan-inventaris-aset-%s.%s',
            $generatedAt->format(
                'Ymd-His'
            ),
            $format
        );

        try {
            if ($format === 'xlsx') {
                $contents = Excel::raw(
                    new AssetsExport(
                        filters: $filters,

                        generatedBy:
                            $generatedBy,

                        generatedAt:
                            $generatedAt->format(
                                'd/m/Y H:i'
                            ),

                        documentNumber:
                            $history
                                ->document_number,

                        documentStatus:
                            $history
                                ->document_status,
                    ),
                    ExcelWriter::XLSX
                );
            } else {
                $assets = $assetService->get(
                    $filters
                );

                $pdf = Pdf::loadView(
                    'pdf.assets-report',
                    [
                        'assets' => $assets,

                        'filters' =>
                            $assetService
                                ->filterSummary(
                                    $filters
                                ),

                        'generatedAt' =>
                            $generatedAt,

                        'generatedBy' =>
                            $generatedBy,

                        'company' =>
                            config('company'),

                        'logoBase64' =>
                            $this
                                ->companyLogoBase64(),

                        'documentNumber' =>
                            $history
                                ->document_number,

                        'documentStatus' =>
                            $history
                                ->document_status,

                        'signatories' =>
                            $history
                                ->signatories,
                    ]
                )->setPaper(
                    'a4',
                    'landscape'
                );

                $contents = $pdf->output();
            }

            $archiveService->complete(
                history: $history,
                contents: $contents,
                filename: $filename
            );

            Notification::make()
                ->title(
                    'Laporan aset berhasil dibuat'
                )
                ->body(
                    'Nomor dokumen: ' .
                    $history->document_number
                )
                ->success()
                ->send();

            return $this->downloadResponse(
                contents: $contents,
                filename: $filename,
                format: $format
            );
        } catch (Throwable $exception) {
            $archiveService->fail(
                $history,
                $exception
            );

            report($exception);

            Notification::make()
                ->title(
                    'Export aset gagal'
                )
                ->body(
                    $exception->getMessage()
                )
                ->danger()
                ->send();

            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | BAGIAN 13 — Export laporan harian dan arsip
    |--------------------------------------------------------------------------
    */

    private function exportDailyReports(
        array $state
    ) {
        $filters = Arr::only($state, [
            'start_date',
            'end_date',
            'user_id',
            'work_category_id',
            'work_status',
            'review_status',
            'priority',
            'location',
            'duration_min',
            'duration_max',
            'asset_id',
        ]);

        try {
            $startDate = Carbon::parse(
                $filters['start_date']
            );

            $endDate = Carbon::parse(
                $filters['end_date']
            );
        } catch (Throwable) {
            Notification::make()
                ->title(
                    'Periode tidak valid'
                )
                ->body(
                    'Pilih tanggal mulai dan tanggal selesai.'
                )
                ->warning()
                ->send();

            return null;
        }

        if ($endDate->lt($startDate)) {
            Notification::make()
                ->title(
                    'Periode tidak valid'
                )
                ->body(
                    'Tanggal selesai tidak boleh lebih kecil dari tanggal mulai.'
                )
                ->warning()
                ->send();

            return null;
        }

        $minimumDuration =
            $filters['duration_min']
            ?? null;

        $maximumDuration =
            $filters['duration_max']
            ?? null;

        if (
            filled($minimumDuration) &&
            filled($maximumDuration) &&
            (int) $maximumDuration <
            (int) $minimumDuration
        ) {
            Notification::make()
                ->title(
                    'Durasi tidak valid'
                )
                ->body(
                    'Durasi maksimum tidak boleh lebih kecil dari durasi minimum.'
                )
                ->warning()
                ->send();

            return null;
        }

        $format = (string) (
            $state['format'] ?? ''
        );

        if (! in_array(
            $format,
            ['xlsx', 'pdf'],
            true
        )) {
            Notification::make()
                ->title('Format tidak valid')
                ->danger()
                ->send();

            return null;
        }

        $dailyReportService = app(
            DailyReportExportService::class
        );

        if (! $dailyReportService
            ->query($filters)
            ->exists()
        ) {
            Notification::make()
                ->title('Data tidak ditemukan')
                ->body(
                    'Tidak ada laporan harian sesuai filter.'
                )
                ->warning()
                ->send();

            return null;
        }

        $generatedBy =
            auth()->user()?->name
            ?? 'Sistem';

        $generatedAt = now();

        $archiveService = app(
            ExportArchiveService::class
        );

        $history = $archiveService->start(
            reportType: 'daily_reports',
            format: $format,
            filters: $filters,
            generatedBy: $generatedBy
        );

        $filename = sprintf(
            'laporan-harian-it-%s-sampai-%s-%s.%s',
            $startDate->format('Ymd'),
            $endDate->format('Ymd'),
            $generatedAt->format('His'),
            $format
        );

        try {
            if ($format === 'xlsx') {
                $contents = Excel::raw(
                    new DailyReportsExport(
                        filters: $filters,

                        generatedBy:
                            $generatedBy,

                        generatedAt:
                            $generatedAt->format(
                                'd/m/Y H:i'
                            ),

                        documentNumber:
                            $history
                                ->document_number,

                        documentStatus:
                            $history
                                ->document_status,
                    ),
                    ExcelWriter::XLSX
                );
            } else {
                $reports =
                    $dailyReportService->get(
                        $filters
                    );

                $pdf = Pdf::loadView(
                    'pdf.daily-reports',
                    [
                        'reports' => $reports,

                        'filters' =>
                            $dailyReportService
                                ->filterSummary(
                                    $filters
                                ),

                        'statistics' =>
                            $dailyReportService
                                ->statistics(
                                    $reports
                                ),

                        'generatedAt' =>
                            $generatedAt,

                        'generatedBy' =>
                            $generatedBy,

                        'company' =>
                            config('company'),

                        'logoBase64' =>
                            $this
                                ->companyLogoBase64(),

                        'documentNumber' =>
                            $history
                                ->document_number,

                        'documentStatus' =>
                            $history
                                ->document_status,

                        'signatories' =>
                            $history
                                ->signatories,
                    ]
                )->setPaper(
                    'a4',
                    'landscape'
                );

                $contents = $pdf->output();
            }

            $archiveService->complete(
                history: $history,
                contents: $contents,
                filename: $filename
            );

            Notification::make()
                ->title(
                    'Laporan harian berhasil dibuat'
                )
                ->body(
                    'Nomor dokumen: ' .
                    $history->document_number
                )
                ->success()
                ->send();

            return $this->downloadResponse(
                contents: $contents,
                filename: $filename,
                format: $format
            );
        } catch (Throwable $exception) {
            $archiveService->fail(
                $history,
                $exception
            );

            report($exception);

            Notification::make()
                ->title(
                    'Export laporan harian gagal'
                )
                ->body(
                    $exception->getMessage()
                )
                ->danger()
                ->send();

            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | BAGIAN 14 — Export laporan bulanan dan arsip
    |--------------------------------------------------------------------------
    */

    private function exportMonthlyReport(
        array $state
    ) {
        $month = (int) (
            $state['month']
            ?? now()->month
        );

        $year = (int) (
            $state['year']
            ?? now()->year
        );

        $format = (string) (
            $state['format'] ?? ''
        );

        if (
            $month < 1 ||
            $month > 12
        ) {
            Notification::make()
                ->title('Bulan tidak valid')
                ->warning()
                ->send();

            return null;
        }

        if (! in_array(
            $format,
            ['xlsx', 'pdf'],
            true
        )) {
            Notification::make()
                ->title('Format tidak valid')
                ->danger()
                ->send();

            return null;
        }

        $monthlyService = app(
            MonthlyReportExportService::class
        );

        $report = $monthlyService
            ->findReport(
                $month,
                $year
            );

        if (! $report) {
            Notification::make()
                ->title(
                    'Laporan bulanan belum tersedia'
                )
                ->body(
                    'Generate laporan bulanan terlebih dahulu.'
                )
                ->warning()
                ->send();

            return null;
        }

        $generatedBy =
            auth()->user()?->name
            ?? 'Sistem';

        $generatedAt = now();

        $archiveService = app(
            ExportArchiveService::class
        );

        $history = $archiveService->start(
            reportType: 'monthly_reports',
            format: $format,
            filters: [
                'month' => $month,
                'year' => $year,
            ],
            generatedBy: $generatedBy,
            monthlyReport: $report
        );

        $monthSlug = [
            1 => 'januari',
            2 => 'februari',
            3 => 'maret',
            4 => 'april',
            5 => 'mei',
            6 => 'juni',
            7 => 'juli',
            8 => 'agustus',
            9 => 'september',
            10 => 'oktober',
            11 => 'november',
            12 => 'desember',
        ][$month];

        $filename = sprintf(
            'laporan-bulanan-it-%s-%s-%s.%s',
            $monthSlug,
            $year,
            $generatedAt->format('His'),
            $format
        );

        try {
            if ($format === 'xlsx') {
                $contents = Excel::raw(
                    new MonthlyItReportExport(
                        report: $report,

                        generatedBy:
                            $generatedBy,

                        generatedAt:
                            $generatedAt->format(
                                'd/m/Y H:i'
                            ),

                        documentNumber:
                            $history
                                ->document_number,

                        documentStatus:
                            $history
                                ->document_status,
                    ),
                    ExcelWriter::XLSX
                );
            } else {
                $data = $monthlyService
                    ->build($report);

                $pdf = Pdf::loadView(
                    'pdf.monthly-it-report',
                    [
                        'report' => $report,
                        'data' => $data,

                        'generatedAt' =>
                            $generatedAt,

                        'generatedBy' =>
                            $generatedBy,

                        'company' =>
                            config('company'),

                        'logoBase64' =>
                            $this
                                ->companyLogoBase64(),

                        'documentNumber' =>
                            $history
                                ->document_number,

                        'documentStatus' =>
                            $history
                                ->document_status,

                        'signatories' =>
                            $history
                                ->signatories,
                    ]
                )->setPaper(
                    'a4',
                    'landscape'
                );

                $contents = $pdf->output();
            }

            $archiveService->complete(
                history: $history,
                contents: $contents,
                filename: $filename
            );

            Notification::make()
                ->title(
                    'Laporan bulanan berhasil dibuat'
                )
                ->body(
                    'Nomor dokumen: ' .
                    $history->document_number
                )
                ->success()
                ->send();

            return $this->downloadResponse(
                contents: $contents,
                filename: $filename,
                format: $format
            );
        } catch (Throwable $exception) {
            $archiveService->fail(
                $history,
                $exception
            );

            report($exception);

            Notification::make()
                ->title(
                    'Export laporan bulanan gagal'
                )
                ->body(
                    $exception->getMessage()
                )
                ->danger()
                ->send();

            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Permission per jenis laporan
    |--------------------------------------------------------------------------
    */

    private function authorizeReportExport(
        string $reportType
    ): void {
        $permission = match (
            $reportType
        ) {
            'assets' =>
                'export_assets',

            'daily_reports' =>
                'export_daily_reports',

            'monthly_reports' =>
                'export_monthly_reports',

            default => null,
        };

        abort_if(
            $permission === null,
            403,
            'Jenis export tidak valid.'
        );

        abort_unless(
            auth()->user()?->can(
                $permission
            ),
            403,
            'Anda tidak memiliki izin melakukan export ini.'
        );
    }

    private function invalidReportType()
    {
        Notification::make()
            ->title(
                'Jenis laporan tidak valid'
            )
            ->danger()
            ->send();

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Response download
    |--------------------------------------------------------------------------
    */

    private function downloadResponse(
        string $contents,
        string $filename,
        string $format
    ) {
        $contentType = $format === 'pdf'
            ? 'application/pdf'
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        return response()->streamDownload(
            static function () use (
                $contents
            ): void {
                echo $contents;
            },
            $filename,
            [
                'Content-Type' =>
                    $contentType,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Logo perusahaan
    |--------------------------------------------------------------------------
    */

    private function companyLogoBase64():
        ?string
    {
        $relativePath = config(
            'company.logo'
        );

        if (blank($relativePath)) {
            return null;
        }

        $logoPath = public_path(
            $relativePath
        );

        if (! is_file($logoPath)) {
            return null;
        }

        $contents = file_get_contents(
            $logoPath
        );

        if ($contents === false) {
            return null;
        }

        $mimeType = mime_content_type(
            $logoPath
        ) ?: 'image/png';

        return sprintf(
            'data:%s;base64,%s',
            $mimeType,
            base64_encode($contents)
        );
    }

    private function yearOptions(): array
    {
        $years = [];

        for (
            $year = now()->year + 1;
            $year >= now()->year - 4;
            $year--
        ) {
            $years[$year] =
                (string) $year;
        }

        return $years;
    }
}