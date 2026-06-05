<?php

use App\Http\Controllers\PengurusController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'admin',
])->group(function (): void {
    Route::get('/pengurus', [
        PengurusController::class,
        'index',
    ])->name('pengurus.index');

    Route::post('/pengurus', [
        PengurusController::class,
        'store',
    ])->name('pengurus.store');

    Route::put('/pengurus/{pengurus}', [
        PengurusController::class,
        'update',
    ])->name('pengurus.update');

    Route::delete('/pengurus/{pengurus}', [
        PengurusController::class,
        'destroy',
    ])->name('pengurus.destroy');

    Route::patch('/pengurus/{pengurus}/reset-password', [
        PengurusController::class,
        'resetPassword',
    ])->name('pengurus.reset-password');
});