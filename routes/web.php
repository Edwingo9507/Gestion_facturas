<?php

use App\Http\Controllers\FacturaController;
use App\Http\Controllers\AdminController;

Route::get('/', [FacturaController::class, 'index'])->name('consulta');
Route::post('/', [FacturaController::class, 'consultar'])->name('consultar');
Route::post('/pagar', [FacturaController::class, 'pagarSeleccionadas'])->name('pagar.facturas');

// Rutas de administración
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'login'])->name('admin.login.post');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');

    Route::middleware('auth')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.index');
        Route::post('/import-csv', [AdminController::class, 'importCsv'])->name('admin.import.csv');
        Route::delete('/facturas/{id}', [AdminController::class, 'destroy'])->name('admin.facturas.destroy');
    });
});
