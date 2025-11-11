<?php

use Illuminate\Http\Request;
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

// NI SE LES OCURRA ACCEDER A LA URI /todos
// LA MAQUINA NO LO SOPORTARÁ SI HAY MILLONES DE REGISTROS

// usuarios
Route::get('usuarios', [UsuarioController::class, 'index']);
Route::get('usuarios/todos', [UsuarioController::class, 'todos']); // NI SE LES OCURRA
Route::get('usuarios/{id}', [UsuarioController::class, 'show']);
Route::post('usuarios', [UsuarioController::class, 'store']);
Route::put('usuarios/{id}', [UsuarioController::class, 'update']);
Route::delete('usuarios/{id}', [UsuarioController::class, 'destroy']);

// Rutas de búsqueda de Usuarios
Route::get('usuarios/buscar/texto', [UsuarioController::class, 'buscar']);
Route::get('usuarios/buscar/correo', [UsuarioController::class, 'buscarPorCorreo']);

// --------------------------------------------------------------------------
// tipos_casos
Route::get('tipos-casos', [TiposCasosController::class, 'index']);
Route::get('tipos-casos/todos', [TiposCasosController::class, 'todos']); // NI SE LES OCURRA
Route::get('tipos-casos/{id}', [TiposCasosController::class, 'show']);
Route::post('tipos-casos', [TiposCasosController::class, 'store']);
Route::put('tipos-casos/{id}', [TiposCasosController::class, 'update']);
Route::delete('tipos-casos/{id}', [TiposCasosController::class, 'destroy']);

// Rutas de búsqueda y filtros de Tipos de Casos
Route::get('tipos-casos/buscar/texto', [TiposCasosController::class, 'buscar']);
Route::get('tipos-casos/materia/{materiaId}', [TiposCasosController::class, 'porMateria']);

// --------------------------------------------------------------------------
// reportes

Route::get('reportes', [ReporteController::class, 'index']);
Route::get('reportes/todos', [ReporteController::class, 'todos']); // NI SE LES OCURRA
Route::get('reportes/{id}', [ReporteController::class, 'show']);
Route::post('reportes', [ReporteController::class, 'store']);
Route::put('reportes/{id}', [ReporteController::class, 'update']);
Route::delete('reportes/{id}', [ReporteController::class, 'destroy']);

// Rutas de filtros y búsquedas de Reportes
Route::get('reportes/tipo/{tipo}', [ReporteController::class, 'porTipo']);
Route::get('reportes/usuario/{usuarioId}', [ReporteController::class, 'porUsuario']);
Route::get('reportes/recientes/{dias?}', [ReporteController::class, 'recientes']);

// --------------------------------------------------------------------------
// materias_casos

Route::get('materias-casos', [MateriaCasoController::class, 'index']);
Route::get('materias-casos/todos', [MateriaCasoController::class, 'todos']); // NI SE LES OCURRA
Route::get('materias-casos/{id}', [MateriaCasoController::class, 'show']);
Route::post('materias-casos', [MateriaCasoController::class, 'store']);
Route::put('materias-casos/{id}', [MateriaCasoController::class, 'update']);
Route::delete('materias-casos/{id}', [MateriaCasoController::class, 'destroy']);

// Rutas de búsqueda y funciones especiales de Materias
Route::get('materias-casos/buscar/texto', [MateriaCasoController::class, 'buscar']);
Route::get('materias-casos/con-tipos/todos', [MateriaCasoController::class, 'conTiposCasos']);
Route::get('materias-casos/{id}/tipos-casos', [MateriaCasoController::class, 'tiposCasosPorMateria']);
Route::get('materias-casos/{id}/puede-eliminar', [MateriaCasoController::class, 'puedeEliminar']);

// --------------------------------------------------------------------------
// documentos

Route::get('documentos', [DocumentoController::class, 'index']);
Route::get('documentos/todos', [DocumentoController::class, 'todos']); // NI SE LES OCURRA
Route::get('documentos/{id}', [DocumentoController::class, 'show']);
Route::post('documentos', [DocumentoController::class, 'store']);
Route::put('documentos/{id}', [DocumentoController::class, 'update']);
Route::delete('documentos/{id}', [DocumentoController::class, 'destroy']);

// Rutas de búsqueda y funciones especiales de Documentos
Route::get('documentos/buscar/texto', [DocumentoController::class, 'buscar']);
Route::get('documentos/carpetas/todas', [DocumentoController::class, 'carpetas']);
Route::get('documentos/carpeta/{carpetaId}', [DocumentoController::class, 'porCarpeta']);
Route::get('documentos/estadisticas/estadisticas', [DocumentoController::class, 'estadisticas']);

// --------------------------------------------------------------------------
// documentos_compartidos
Route::get('documentos-compartidos', [DocumentoCompartidoController::class, 'index']);
Route::get('documentos-compartidos/todos', [DocumentoCompartidoController::class, 'todos']);
Route::get('documentos-compartidos/{id}', [DocumentoCompartidoController::class, 'show']);
Route::post('documentos-compartidos', [DocumentoCompartidoController::class, 'store']);
Route::put('documentos-compartidos/{id}', [DocumentoCompartidoController::class, 'update']);
Route::delete('documentos-compartidos/{id}', [DocumentoCompartidoController::class, 'destroy']);

// Rutas de consultas y funciones especiales de Documentos Compartidos
Route::get('documentos-compartidos/usuario/{usuarioId}', [DocumentoCompartidoController::class, 'porUsuario']);
Route::get('documentos-compartidos/compartido-por/{usuarioId}', [DocumentoCompartidoController::class, 'porCompartidoPor']);
Route::get('documentos-compartidos/rol/{rol}', [DocumentoCompartidoController::class, 'porRol']);
Route::put('documentos-compartidos/{id}/cambiar-permisos', [DocumentoCompartidoController::class, 'cambiarPermisos']);
Route::post('documentos-compartidos/{documentoId}/verificar-acceso', [DocumentoCompartidoController::class, 'verificarAcceso']);
Route::get('documentos-compartidos/estadisticas/estadisticas', [DocumentoCompartidoController::class, 'estadisticas']);


// --------------------------------------------------------------------------
// comentarios_casos

Route::get('comentarios-casos', [ComentarioCasoController::class, 'index']);
Route::get('comentarios-casos/todos', [ComentarioCasoController::class, 'todos']); // NI SE LES OCURRA
Route::get('comentarios-casos/{id}', [ComentarioCasoController::class, 'show']);
Route::post('comentarios-casos', [ComentarioCasoController::class, 'store']);
Route::put('comentarios-casos/{id}', [ComentarioCasoController::class, 'update']);
Route::delete('comentarios-casos/{id}', [ComentarioCasoController::class, 'destroy']);

// Rutas de filtros y búsquedas de Comentarios
Route::get('comentarios-casos/caso/{casoId}', [ComentarioCasoController::class, 'porCaso']);
Route::get('comentarios-casos/usuario/{usuarioId}', [ComentarioCasoController::class, 'porUsuario']);
Route::get('comentarios-casos/buscar/filtros', [ComentarioCasoController::class, 'buscar']);
Route::get('comentarios-casos/estadisticas/estadisticas', [ComentarioCasoController::class, 'estadisticas']);

// --------------------------------------------------------------------------
// clientes

Route::get('clientes', [ClienteController::class, 'index']);
Route::get('clientes/todos', [ClienteController::class, 'todos']); // NI SE LES OCURRA
Route::get('clientes/{id}', [ClienteController::class, 'show']);
Route::post('clientes', [ClienteController::class, 'store']);
Route::put('clientes/{id}', [ClienteController::class, 'update']);
Route::delete('clientes/{id}', [ClienteController::class, 'destroy']);

// Rutas de búsqueda y funciones especiales de Clientes
Route::get('clientes/buscar/texto', [ClienteController::class, 'buscar']);
Route::get('clientes/documento/{numeroDocumento}', [ClienteController::class, 'porDocumento']);
Route::put('clientes/{id}/activar', [ClienteController::class, 'activar']);
Route::put('clientes/{id}/desactivar', [ClienteController::class, 'desactivar']);
Route::get('clientes/estadisticas/estadisticas', [ClienteController::class, 'estadisticas']);

// --------------------------------------------------------------------------
// casos


Route::get('casos', [CasoController::class, 'index']);
Route::get('casos/todos', [CasoController::class, 'todos']); // NI SE LES OCURRA
Route::get('casos/{id}', [CasoController::class, 'show']);
Route::post('casos', [CasoController::class, 'store']);
Route::put('casos/{id}', [CasoController::class, 'update']);
Route::delete('casos/{id}', [CasoController::class, 'destroy']);

// Rutas de búsqueda y funciones especiales de Casos
Route::get('casos/buscar/texto', [CasoController::class, 'buscar']);
Route::get('casos/cliente/{clienteId}', [CasoController::class, 'porCliente']);
Route::get('casos/abogado/{abogadoId}', [CasoController::class, 'porAbogado']);
Route::put('casos/{id}/cambiar-estado', [CasoController::class, 'cambiarEstado']);
Route::put('casos/{id}/cerrar', [CasoController::class, 'cerrar']);
Route::get('casos/estadisticas/estadisticas', [CasoController::class, 'estadisticas']);

// --------------------------------------------------------------------------
// calendario

Route::get('calendario', [CalendarioController::class, 'index']);
Route::get('calendario/todos', [CalendarioController::class, 'todos']); // NI SE LES OCURRA
Route::get('calendario/{id}', [CalendarioController::class, 'show']);
Route::post('calendario', [CalendarioController::class, 'store']);
Route::put('calendario/{id}', [CalendarioController::class, 'update']);
Route::delete('calendario/{id}', [CalendarioController::class, 'destroy']);

// Rutas de funciones especiales del Calendario
Route::get('calendario/proximos/{dias?}', [CalendarioController::class, 'proximos']);
Route::get('calendario/hoy', [CalendarioController::class, 'hoy']);
Route::put('calendario/{id}/completar', [CalendarioController::class, 'completar']);
Route::put('calendario/{id}/cancelar', [CalendarioController::class, 'cancelar']);
Route::put('calendario/{id}/reagendar', [CalendarioController::class, 'reagendar']);
Route::get('calendario/rango/fechas', [CalendarioController::class, 'porRango']);
Route::get('calendario/estadisticas/estadisticas', [CalendarioController::class, 'estadisticas']);

// --------------------------------------------------------------------------
// bitacora

Route::get('bitacora', [BitacoraController::class, 'index']);
Route::get('bitacora/todos', [BitacoraController::class, 'todos']);
Route::get('bitacora/{id}', [BitacoraController::class, 'show']);
Route::post('bitacora', [BitacoraController::class, 'store']);

// Rutas de consultas y reportes de Bitácora
Route::get('bitacora/usuario/{usuarioId}', [BitacoraController::class, 'porUsuario']);
Route::get('bitacora/usuario/{usuarioId}/actividades', [BitacoraController::class, 'actividadesUsuario']);
Route::get('bitacora/buscar/texto', [BitacoraController::class, 'buscar']);
Route::get('bitacora/rango/fechas', [BitacoraController::class, 'porRango']);
Route::get('bitacora/estadisticas/estadisticas', [BitacoraController::class, 'estadisticas']);
Route::get('bitacora/alertas/sospechosas', [BitacoraController::class, 'actividadesSospechosas']);