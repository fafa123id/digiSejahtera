<?php

use App\Http\Controllers\KitirController;
use Illuminate\Support\Facades\Route;

Route::get(
    '/kitir',
    [
        KitirController::class,
        'index',
    ]
)->name(
    'kitir.index'
);
Route::get(
    '/kitir/export',
    [
        KitirController::class,
        'download',
    ]
)->name(
    'kitir.export'
);