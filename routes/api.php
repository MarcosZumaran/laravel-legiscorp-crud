<?php

use Illuminate\Http\Request;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\TiposCasosController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\MateriaCasoController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\EtapaProcesalController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\ComentarioCasoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CasoController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\BitacoraController;

use Illuminate\Support\Facades\Route;

// rutas de api

// NI SE LES OCURRA ACCEDER A LA URI /todos
// LA MAQUINA NO LO SOPORTARÁ SI HAY MILLONES DE REGISTROS

// usuarios

Route::get('/usuarios', [UsuarioController::class, 'index']);
Route::get('/usuarios/todos', [UsuarioController::class, 'todos']); // NI SE LES OCURRA
Route::get('/usuarios/{id}', [UsuarioController::class, 'show']);
Route::post('/usuarios', [UsuarioController::class, 'store']);
Route::put('/usuarios/{id}', [UsuarioController::class, 'update']);
Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy']);

// --------------------------------------------------------------------------
// tipos_casos

Route::get('/tipos_casos', [TiposCasosController::class, 'index']);
Route::get('/tipos_casos/todos', [TiposCasosController::class, 'todos']);// NI SE LES OCURRA
Route::get('/tipos_casos/{id}', [TiposCasosController::class, 'show']);
Route::post('/tipos_casos', [TiposCasosController::class, 'store']);
Route::put('/tipos_casos/{id}', [TiposCasosController::class, 'update']);
Route::delete('/tipos_casos/{id}', [TiposCasosController::class, 'destroy']);

// --------------------------------------------------------------------------
// reportes

Route::get('/reportes', [ReporteController::class, 'index']);
Route::get('/reportes/todos', [ReporteController::class, 'todos']);// NI SE LES OCURRA
Route::get('/reportes/{id}', [ReporteController::class, 'show']);
Route::post('/reportes', [ReporteController::class, 'store']);
Route::put('/reportes/{id}', [ReporteController::class, 'update']);
Route::delete('/reportes/{id}', [ReporteController::class, 'destroy']);

// --------------------------------------------------------------------------
// materias_casos

Route::get('/materias_casos', [MateriaCasoController::class, 'index']);
Route::get('/materias_casos/todos', [MateriaCasoController::class, 'todos']);// NI SE LES OCURRA
Route::get('/materias_casos/{id}', [MateriaCasoController::class, 'show']);
Route::post('/materias_casos', [MateriaCasoController::class, 'store']);
Route::put('/materias_casos/{id}', [MateriaCasoController::class, 'update']);
Route::delete('/materias_casos/{id}', [MateriaCasoController::class, 'destroy']);

// --------------------------------------------------------------------------
// eventos

Route::get('/eventos', [EventoController::class, 'index']);
Route::get('/eventos/todos', [EventoController::class, 'todos']);// NI SE LES OCURRA
Route::get('/eventos/{id}', [EventoController::class, 'show']);
Route::post('/eventos', [EventoController::class, 'store']);
Route::put('/eventos/{id}', [EventoController::class, 'update']);
Route::delete('/eventos/{id}', [EventoController::class, 'destroy']);

// --------------------------------------------------------------------------
// etapas_procesales

Route::get('/etapas-procesales', [EtapaProcesalController::class, 'index']);
Route::get('/etapas-procesales/todos', [EtapaProcesalController::class, 'todos']);// NI SE LES OCURRA
Route::get('/etapas-procesales/{id}', [EtapaProcesalController::class, 'show']);
Route::post('/etapas-procesales', [EtapaProcesalController::class, 'store']);
Route::put('/etapas-procesales/{id}', [EtapaProcesalController::class, 'update']);
Route::delete('/etapas-procesales/{id}', [EtapaProcesalController::class, 'destroy']);

// --------------------------------------------------------------------------
// documentos

Route::get('/documentos', [DocumentoController::class, 'index']);
Route::get('/documentos/todos', [DocumentoController::class, 'todos']);// NI SE LES OCURRA
Route::get('/documentos/{id}', [DocumentoController::class, 'show']);
Route::post('/documentos', [DocumentoController::class, 'store']);
Route::put('/documentos/{id}', [DocumentoController::class, 'update']);
Route::delete('/documentos/{id}', [DocumentoController::class, 'destroy']);

// --------------------------------------------------------------------------
// comentarios_casos

Route::get('/comentarios-casos', [ComentarioCasoController::class, 'index']);
Route::get('/comentarios-casos/todos', [ComentarioCasoController::class, 'todos']);// NI SE LES OCURRA
Route::get('/comentarios-casos/{id}', [ComentarioCasoController::class, 'show']);
Route::post('/comentarios-casos', [ComentarioCasoController::class, 'store']);
Route::put('/comentarios-casos/{id}', [ComentarioCasoController::class, 'update']);
Route::delete('/comentarios-casos/{id}', [ComentarioCasoController::class, 'destroy']);

// --------------------------------------------------------------------------
// clientes

Route::get('/clientes', [ClienteController::class, 'index']);
Route::get('/clientes/todos', [ClienteController::class, 'todos']);// NI SE LES OCURRA
Route::get('/clientes/{id}', [ClienteController::class, 'show']);
Route::post('/clientes', [ClienteController::class, 'store']);
Route::put('/clientes/{id}', [ClienteController::class, 'update']);
Route::delete('/clientes/{id}', [ClienteController::class, 'destroy']);

// --------------------------------------------------------------------------
// casos


Route::get('/casos', [CasoController::class, 'index']);
Route::get('/casos/todos', [CasoController::class, 'todos']);// NI SE LES OCURRA
Route::get('/casos/{id}', [CasoController::class, 'show']);
Route::post('/casos', [CasoController::class, 'store']);
Route::put('/casos/{id}', [CasoController::class, 'update']);
Route::delete('/casos/{id}', [CasoController::class, 'destroy']);

// --------------------------------------------------------------------------
// calendario

Route::get('/calendario', [CalendarioController::class, 'index']);
Route::get('/calendario/todos', [CalendarioController::class, 'todos']);// NI SE LES OCURRA
Route::get('/calendario/{id}', [CalendarioController::class, 'show']);
Route::post('/calendario', [CalendarioController::class, 'store']);
Route::put('/calendario/{id}', [CalendarioController::class, 'update']);
Route::delete('/calendario/{id}', [CalendarioController::class, 'destroy']);

// --------------------------------------------------------------------------
// bitacora

Route::get('/bitacora', [BitacoraController::class, 'index']);
Route::get('/bitacora/todos', [BitacoraController::class, 'todos']);// NI SE LES OCURRA
Route::get('/bitacora/{id}', [BitacoraController::class, 'show']);
Route::post('/bitacora', [BitacoraController::class, 'store']);
Route::put('/bitacora/{id}', [BitacoraController::class, 'update']);
Route::delete('/bitacora/{id}', [BitacoraController::class, 'destroy']);