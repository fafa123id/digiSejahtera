<?php

use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/laporan', [
        LaporanController::class,
        'index',
    ])->name('laporan.index');

    Route::get('/laporan/jasa-pinjaman/export', [
        LaporanController::class,
        'exportJasaPinjaman',
    ])->name('laporan.jasa-pinjaman.export');

    Route::get('/laporan/shu/export', [
        LaporanController::class,
        'exportShu',
    ])->name('laporan.shu.export');
});
