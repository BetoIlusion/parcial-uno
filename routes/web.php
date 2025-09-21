<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiagramaController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/dashboard', [DiagramaController::class, 'index'])->name('dashboard');

    Route::prefix('/diagramas')->group(function () {
        Route::get('/uml', [DiagramaController::class, 'uml'])->name('uml.show');
        Route::post('/reporte-diagrama', [DiagramaController::class, 'diagramaReporte'])
            ->name('diagrama-reporte.create');
        Route::post('/', [DiagramaController::class, 'store'])->name('diagramas.store');
        Route::get('/{diagrama}', [DiagramaController::class, 'show'])->name('diagramas.show');
        Route::post('/{diagrama}/contenido', [DiagramaController::class, 'updateContenido'])->name('diagramas.updateContenido');
        Route::get('/descarga', [DiagramaController::class, 'download'])->name('diagrama.download');
        Route::delete('/{diagrama}', [DiagramaController::class, 'destroy'])->name('diagrama.destroy');

        Route::post('/analizar-imagen', [DiagramaController::class, 'procesarImagen'])
            ->name('analizar.imagen');
    });
});
