<?php

use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\TiposCasosController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\MateriaCasoController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\ComentarioCasoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CasoController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\DocumentoCompartidoController;

use Illuminate\Support\Facades\Route;

// rutas de api

// usuarios
Route::prefix('usuarios')->group(function () {
    Route::post('login', [UsuarioController::class, 'login']);
    Route::get('/', [UsuarioController::class, 'index']);
    Route::get('buscar', [UsuarioController::class, 'buscar']);
    Route::get('{id}', [UsuarioController::class, 'show']);
    Route::post('/', [UsuarioController::class, 'store']);
    Route::put('{id}', [UsuarioController::class, 'update']);
    Route::delete('{id}', [UsuarioController::class, 'destroy']);
});

// --------------------------------------------------------------------------
// tipos_casos
Route::prefix('tipos-casos')->group(function () {
    Route::get('/', [TiposCasosController::class, 'index']);
    Route::get('buscar', [TiposCasosController::class, 'buscar']);
    Route::get('{id}', [TiposCasosController::class, 'show']);
    Route::post('/', [TiposCasosController::class, 'store']);
    Route::put('{id}', [TiposCasosController::class, 'update']);
    Route::delete('{id}', [TiposCasosController::class, 'destroy']);
});

// --------------------------------------------------------------------------
// reportes

Route::prefix('reportes')->group(function () {
    Route::get('/', [ReporteController::class, 'index']);
    Route::post('/', [ReporteController::class, 'store']);
    Route::get('/{id}', [ReporteController::class, 'show']);
    Route::delete('/{id}', [ReporteController::class, 'destroy']);
});

// --------------------------------------------------------------------------
// materias_casos
Route::prefix('materias-casos')->group(function () {
    Route::get('/', [MateriaCasoController::class, 'index']);
    Route::post('/', [MateriaCasoController::class, 'store']);
    Route::get('/{id}', [MateriaCasoController::class, 'show']);
    Route::put('/{id}', [MateriaCasoController::class, 'update']);
    Route::delete('/{id}', [MateriaCasoController::class, 'destroy']);
});

// --------------------------------------------------------------------------
// documentos
Route::prefix('documentos')->group(function () {
    Route::get('/', [DocumentoController::class, 'index']);
    Route::post('/', [DocumentoController::class, 'store']);
    Route::get('/{id}', [DocumentoController::class, 'show']);
    Route::put('/{id}', [DocumentoController::class, 'update']);
    Route::delete('/{id}', [DocumentoController::class, 'destroy']);
});

// --------------------------------------------------------------------------
// documentos_compartidos
Route::prefix('documentos-compartidos')->group(function () {
    Route::get('/', [DocumentoCompartidoController::class, 'index']);
    Route::post('/', [DocumentoCompartidoController::class, 'store']);
    Route::get('/{id}', [DocumentoCompartidoController::class, 'show']);
    Route::put('/{id}', [DocumentoCompartidoController::class, 'update']);
    Route::delete('/{id}', [DocumentoCompartidoController::class, 'destroy']);
});


// --------------------------------------------------------------------------
// comentarios_casos
Route::prefix('comentarios-casos')->group(function () {
    Route::get('/', [ComentarioCasoController::class, 'index']);
    Route::post('/', [ComentarioCasoController::class, 'store']);
    Route::get('/{id}', [ComentarioCasoController::class, 'show']);
    Route::put('/{id}', [ComentarioCasoController::class, 'update']);
    Route::delete('/{id}', [ComentarioCasoController::class, 'destroy']);
});

// --------------------------------------------------------------------------
// clientes
Route::prefix('clientes')->group(function () {
    Route::get('/', [ClienteController::class, 'index']);
    Route::post('/', [ClienteController::class, 'store']);
    Route::get('/{id}', [ClienteController::class, 'show']);
    Route::put('/{id}', [ClienteController::class, 'update']);
    Route::delete('/{id}', [ClienteController::class, 'destroy']);
});

// --------------------------------------------------------------------------
// casos
Route::prefix('casos')->group(function () {
    Route::get('/', [CasoController::class, 'index']);
    Route::post('/', [CasoController::class, 'store']);
    Route::get('/{id}', [CasoController::class, 'show']);
    Route::put('/{id}', [CasoController::class, 'update']);
    Route::delete('/{id}', [CasoController::class, 'destroy']);
});

// --------------------------------------------------------------------------
// calendario
Route::prefix('calendario')->group(function () {
    Route::get('/', [CalendarioController::class, 'index']);
    Route::post('/', [CalendarioController::class, 'store']);
    Route::get('/{id}', [CalendarioController::class, 'show']);
    Route::put('/{id}', [CalendarioController::class, 'update']);
    Route::delete('/{id}', [CalendarioController::class, 'destroy']);
});

// --------------------------------------------------------------------------
// bitacora
Route::prefix('bitacora')->group(function () {
    Route::get('/', [BitacoraController::class, 'index']);
    Route::get('/{id}', [BitacoraController::class, 'show']);
});