<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class BitacoraController extends Controller
{
    /**
     * Lista paginada de registros de bitácora con filtros
     */
    public function index(Request $request): JsonResponse
    {
        $query = Bitacora::with('usuario')->ordenarPorFecha();

        // Filtros
        if ($request->has('usuario_id') && $request->usuario_id) {
            $query->porUsuario($request->usuario_id);
        }

        if ($request->has('accion') && $request->accion) {
            $query->porAccion($request->accion);
        }

        if ($request->has('ip') && $request->ip) {
            $query->porIp($request->ip);
        }

        if ($request->has('recientes') && $request->recientes) {
            $query->recientes($request->get('horas', 24));
        }

        if ($request->has('hoy') && $request->hoy) {
            $query->hoy();
        }

        if ($request->has('q') && $request->q) {
            $query->buscar($request->q);
        }

        // Filtro por rango de fechas
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->entreFechas($request->fecha_inicio, $request->fecha_fin);
        }

        $bitacoras = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'mensaje' => 'Lista de registros de la bitácora',
            'total'   => $bitacoras->total(),
            'data'    => $bitacoras->items(),
            'meta'    => [
                'current_page'  => $bitacoras->currentPage(),
                'per_page'      => $bitacoras->perPage(),
                'next_page_url' => $bitacoras->nextPageUrl(),
                'prev_page_url' => $bitacoras->previousPageUrl(),
                'last_page'     => $bitacoras->lastPage(),
            ],
        ], 200);
    }

    /**
     * Todos los registros con filtros opcionales
     */
    public function todos(Request $request): JsonResponse
    {
        $query = Bitacora::with('usuario')->ordenarPorFecha();

        if ($request->has('usuario_id') && $request->usuario_id) {
            $query->porUsuario($request->usuario_id);
        }

        $bitacoras = $query->get();

        return response()->json([
            'mensaje' => 'Lista completa de registros de la bitácora',
            'total'   => $bitacoras->count(),
            'data'    => $bitacoras,
        ], 200);
    }

    /**
     * Mostrar registro específico
     */
    public function show($id): JsonResponse
    {
        $bitacora = Bitacora::with('usuario')->find($id);

        if (!$bitacora) {
            return response()->json([
                'mensaje' => 'Registro de bitácora no encontrado'
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Registro de bitácora encontrado',
            'data' => $bitacora
        ], 200);
    }

    /**
     * Registrar nueva acción en bitácora (método seguro)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'usuario_id' => 'required|integer|exists:usuarios,id',
            'accion' => 'required|string|max:255',
            'ip' => 'nullable|string|max:50',
        ], [
            'usuario_id.required' => 'El usuario es obligatorio',
            'accion.required' => 'La acción es obligatoria',
        ]);

        // La acción se encripta automáticamente en el modelo
        $bitacora = Bitacora::registrar(
            $validated['usuario_id'],
            $validated['accion'],
            $validated['ip'] ?? request()->ip()
        );

        return response()->json([
            'mensaje' => 'Acción registrada en la bitácora correctamente',
            'data' => $bitacora->load('usuario')
        ], 201);
    }

    /**
     * ELIMINADO: Actualizar registro de bitácora
     * Los registros de auditoría no deben ser modificables
     */
    public function update(Request $request, $id): JsonResponse
    {
        return response()->json([
            'mensaje' => 'Los registros de bitácora no pueden ser modificados por políticas de auditoría'
        ], 403);
    }

    /**
     * ELIMINADO: Eliminar registro de bitácora
     * Los registros de auditoría no deben ser eliminables
     */
    public function destroy($id): JsonResponse
    {
        return response()->json([
            'mensaje' => 'Los registros de bitácora no pueden ser eliminados por políticas de auditoría'
        ], 403);
    }

    /**
     * Actividades por usuario específico
     */
    public function porUsuario($usuarioId): JsonResponse
    {
        $bitacoras = Bitacora::with('usuario')
                            ->porUsuario($usuarioId)
                            ->ordenarPorFecha()
                            ->get();

        return response()->json([
            'mensaje' => 'Actividades del usuario en la bitácora',
            'usuario_id' => $usuarioId,
            'total' => $bitacoras->count(),
            'data' => $bitacoras
        ], 200);
    }

    /**
     * Actividades recientes de un usuario
     */
    public function actividadesUsuario($usuarioId, Request $request): JsonResponse
    {
        $horas = $request->get('horas', 24);
        $bitacoras = Bitacora::actividadesRecientesUsuario($usuarioId, $horas);

        return response()->json([
            'mensaje' => 'Actividades recientes del usuario',
            'usuario_id' => $usuarioId,
            'horas' => $horas,
            'total' => $bitacoras->count(),
            'data' => $bitacoras
        ], 200);
    }

    /**
     * Búsqueda en bitácora
     */
    public function buscar(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2'
        ]);

        $bitacoras = Bitacora::with('usuario')
                            ->buscar($request->q)
                            ->ordenarPorFecha()
                            ->get();

        return response()->json([
            'mensaje' => 'Resultados de búsqueda en bitácora',
            'termino' => $request->q,
            'total' => $bitacoras->count(),
            'data' => $bitacoras
        ], 200);
    }

    /**
     * Registros por rango de fechas
     */
    public function porRango(Request $request): JsonResponse
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $bitacoras = Bitacora::with('usuario')
                            ->entreFechas($request->fecha_inicio, $request->fecha_fin)
                            ->ordenarPorFecha()
                            ->get();

        return response()->json([
            'mensaje' => 'Registros de bitácora por rango de fechas',
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'total' => $bitacoras->count(),
            'data' => $bitacoras
        ], 200);
    }

    /**
     * Estadísticas de la bitácora
     */
    public function estadisticas(): JsonResponse
    {
        $total = Bitacora::count();
        $hoy = Bitacora::hoy()->count();
        $ultimas24h = Bitacora::recientes(24)->count();
        
        $porUsuario = Bitacora::selectRaw('usuario_id, COUNT(*) as total')
            ->groupBy('usuario_id')
            ->with('usuario')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'usuario' => $item->usuario ? $item->usuario->nombres . ' ' . $item->usuario->apellidos : 'Usuario desconocido',
                    'total' => $item->total
                ];
            });

        $accionesComunes = Bitacora::selectRaw('accion, COUNT(*) as total')
            ->groupBy('accion')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get()
            ->pluck('total', 'accion');

        return response()->json([
            'mensaje' => 'Estadísticas de la bitácora',
            'data' => [
                'total_registros' => $total,
                'registros_hoy' => $hoy,
                'registros_ultimas_24h' => $ultimas24h,
                'usuarios_mas_activos' => $porUsuario,
                'acciones_mas_comunes' => $accionesComunes
            ]
        ], 200);
    }

    /**
     * Actividades sospechosas (múltiples accesos desde misma IP)
     */
    public function actividadesSospechosas(Request $request): JsonResponse
    {
        $horas = $request->get('horas', 1);
        $limite = $request->get('limite', 10);

        $sospechosas = Bitacora::selectRaw('ip, COUNT(DISTINCT usuario_id) as usuarios_diferentes, COUNT(*) as total_intentos')
            ->recientes($horas)
            ->groupBy('ip')
            ->having('usuarios_diferentes', '>', 1)
            ->orHaving('total_intentos', '>', $limite)
            ->orderBy('total_intentos', 'desc')
            ->get();

        return response()->json([
            'mensaje' => 'Actividades sospechosas detectadas',
            'horas' => $horas,
            'limite' => $limite,
            'total' => $sospechosas->count(),
            'data' => $sospechosas
        ], 200);
    }

    /**
     * Método de utilidad para registro automático desde otros controladores
     */
    public static function registrarAutomatico($usuarioId, $accion, $ip = null): void
    {
        try {
            Bitacora::registrar(
                $usuarioId,
                $accion,
                $ip ?: request()->ip()
            );
        } catch (\Exception $e) {
            // Log del error pero no interrumpir el flujo principal
            Log::error($e->getMessage());
        }
    }
}