<?php

use App\Http\Controllers\RiwayatController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/riwayat-transaksi', [
        RiwayatController::class,
        'index',
    ])->name('riwayat.index');
});