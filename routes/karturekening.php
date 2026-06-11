<?php

use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\KartuRekeningController;
use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->group(function (): void {
        Route::get('/laporan/simpanan-hari-raya/export', [
            LaporanController::class,
            'exportSimpananHariRaya',
        ])->name('laporan.simpanan-hari-raya.export');
        Route::get('/laporan/tagihan-bulanan/export', [
            LaporanController::class,
            'exportTagihanBulanan',
        ])->name('laporan.tagihan-bulanan.export');
        Route::get(
            '/kartu-rekening',
            [
                KartuRekeningController::class,
                'index',
            ]
        )->name(
            'kartu-rekening.index'
        );

        Route::patch(
            '/kartu-rekening',
            [
                KartuRekeningController::class,
                'update',
            ]
        )->name(
            'kartu-rekening.update'
        );

        Route::post(
            '/anggota',
            [
                AnggotaController::class,
                'store',
            ]
        )->name(
            'anggota.store'
        );

        Route::patch(
            '/anggota/{anggota}/keluarkan',
            [
                AnggotaController::class,
                'keluarkan',
            ]
        )->name(
            'anggota.keluarkan'
        );

        Route::delete(
            '/anggota/{anggota}',
            [
                AnggotaController::class,
                'destroy',
            ]
        )->name(
            'anggota.destroy'
        );
        Route::get(
            '/kartu-rekening/export',
            [
                KartuRekeningController::class,
                'download',
            ]
        )->name(
            'kartu-rekening.export'
        );
    });
