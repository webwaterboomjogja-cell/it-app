<?php

use App\Http\Controllers\AssetQrController;
use App\Http\Controllers\ItScheduleCalendarDownloadController;
use Illuminate\Support\Facades\Route;
use App\Exports\ItassetsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

Route::redirect('/', '/admin');

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/it-schedules/calendar/download', ItScheduleCalendarDownloadController::class)
        ->name('it-schedules.calendar.download');
});

Route::get('/asset-it/labels/print-all', [AssetQrController::class, 'printAll'])
    ->middleware(['auth'])
    ->name('asset-it.labels.print-all');

Route::get('/asset-it/scan/{token}', [AssetQrController::class, 'show'])
    ->name('asset-it.scan');

Route::get('/asset-it/label/{token}', [AssetQrController::class, 'label'])
    ->name('asset-it.label');

Route::middleware(['auth'])->group(function () {
    Route::get('/export/it-assets', function (Request $request) {
        $fileName = 'laporan-aset-it-' . now()->format('Y-m-d-H-i-s') . '.xlsx';

        return Excel::download(
            new ItassetsExport(
                status: $request->query('status'),
                condition: $request->query('condition'),
                categoryId: $request->query('category_id') ? (int) $request->query('category_id') : null,
                locationId: $request->query('location_id') ? (int) $request->query('location_id') : null,
                responsibleUserId: $request->query('responsible_user_id') ? (int) $request->query('responsible_user_id') : null,
            ),
            $fileName
        );
    })->name('it-assets.export');
});
