<?php

namespace App\Http\Controllers;

use App\Models\Calendario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class CalendarioController extends Controller
{
    /**
     * Lista paginada de eventos con filtros
     */
    public function index(Request $request): JsonResponse
    {
        $query = Calendario::with(['caso', 'abogado', 'cliente', 'creador'])
                          ->orderBy('fecha_inicio', 'asc');

        // Filtros
        if ($request->has('tipo_evento') && $request->tipo_evento) {
            $query->porTipo($request->tipo_evento);
        }

        if ($request->has('estado') && $request->estado) {
            $query->porEstado($request->estado);
        }

        if ($request->has('abogado_id') && $request->abogado_id) {
            $query->porAbogado($request->abogado_id);
        }

        if ($request->has('cliente_id') && $request->cliente_id) {
            $query->porCliente($request->cliente_id);
        }

        if ($request->has('caso_id') && $request->caso_id) {
            $query->porCaso($request->caso_id);
        }

        if ($request->has('pendientes') && $request->pendientes) {
            $query->pendientes();
        }

        if ($request->has('proximos') && $request->proximos) {
            $query->proximos($request->get('dias', 7));
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

        $calendarios = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'mensaje' => 'Lista de eventos del calendario',
            'total'   => $calendarios->total(),
            'data'    => $calendarios->items(),
            'meta'    => [
                'current_page'  => $calendarios->currentPage(),
                'per_page'      => $calendarios->perPage(),
                'next_page_url' => $calendarios->nextPageUrl(),
                'prev_page_url' => $calendarios->previousPageUrl(),
                'last_page'     => $calendarios->lastPage(),
            ],
        ], 200);
    }

    /**
     * Todos los eventos con filtros opcionales
     */
    public function todos(Request $request): JsonResponse
    {
        $query = Calendario::with(['caso', 'abogado', 'cliente', 'creador'])
                          ->orderBy('fecha_inicio', 'asc');

        if ($request->has('estado') && $request->estado) {
            $query->porEstado($request->estado);
        }

        $calendarios = $query->get();

        return response()->json([
            'mensaje' => 'Lista completa de eventos del calendario',
            'total'   => $calendarios->count(),
            'data'    => $calendarios,
        ], 200);
    }

    /**
     * Mostrar evento específico
     */
    public function show($id): JsonResponse
    {
        $calendario = Calendario::with(['caso', 'abogado', 'cliente', 'creador'])->find($id);

        if (!$calendario) {
            return response()->json([
                'mensaje' => 'Evento no encontrado'
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Evento encontrado',
            'data' => $calendario,
            'puede_editar' => $calendario->puedeEditar()
        ], 200);
    }

    /**
     * Crear nuevo evento (encriptación automática en el modelo)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'ubicacion' => 'nullable|string|max:255',
            'tipo_evento' => 'required|string|in:Audiencia,Reunión,Plazo,Entrega,Otro',
            'estado' => 'nullable|string|in:Pendiente,Completado,Cancelado',
            'color' => 'nullable|string|max:20',
            'recurrente' => 'nullable|string|in:No,Diario,Semanal,Mensual,Anual',
            'caso_id' => 'nullable|integer|exists:casos,id',
            // 'etapa_id' => 'nullable|integer|exists:etapas_procesales,id', // ❌ ELIMINADO
            'abogado_id' => 'nullable|integer|exists:usuarios,id',
            'cliente_id' => 'nullable|integer|exists:clientes,id',
            'creado_por' => 'required|integer|exists:usuarios,id',
            'expediente' => 'nullable|string|max:30',
        ], [
            'titulo.required' => 'El título es obligatorio',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio',
            'tipo_evento.required' => 'El tipo de evento es obligatorio',
            'creado_por.required' => 'El creador es obligatorio',
        ]);

        // ✅ La descripción se encripta automáticamente en el modelo
        $calendario = Calendario::create($validated);

        return response()->json([
            'mensaje' => 'Evento creado correctamente',
            'data' => $calendario->load(['caso', 'abogado', 'cliente', 'creador'])
        ], 201);
    }

    /**
     * Actualizar evento existente (encriptación automática en el modelo)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $calendario = Calendario::find($id);

        if (!$calendario) {
            return response()->json([
                'mensaje' => 'Evento no encontrado'
            ], 404);
        }

        $validated = $request->validate([
            'titulo' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'sometimes|required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'ubicacion' => 'nullable|string|max:255',
            'tipo_evento' => 'sometimes|required|string|in:Audiencia,Reunión,Plazo,Entrega,Otro',
            'estado' => 'nullable|string|in:Pendiente,Completado,Cancelado',
            'color' => 'nullable|string|max:20',
            'recurrente' => 'nullable|string|in:No,Diario,Semanal,Mensual,Anual',
            'caso_id' => 'nullable|integer|exists:casos,id',
            // 'etapa_id' => 'nullable|integer|exists:etapas_procesales,id', // ❌ ELIMINADO
            'abogado_id' => 'nullable|integer|exists:usuarios,id',
            'cliente_id' => 'nullable|integer|exists:clientes,id',
            'expediente' => 'nullable|string|max:30',
        ], [
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio',
        ]);

        // ✅ La descripción se encripta automáticamente en el modelo
        $calendario->update($validated);

        return response()->json([
            'mensaje' => 'Evento actualizado correctamente',
            'data' => $calendario->load(['caso', 'abogado', 'cliente', 'creador'])
        ], 200);
    }

    /**
     * Eliminar evento
     */
    public function destroy($id): JsonResponse
    {
        $calendario = Calendario::find($id);

        if (!$calendario) {
            return response()->json([
                'mensaje' => 'Evento no encontrado'
            ], 404);
        }

        $calendario->delete();

        return response()->json([
            'mensaje' => 'Evento eliminado correctamente'
        ], 200);
    }

    /**
     * 🆕 Eventos próximos (para dashboard)
     */
    public function proximos(Request $request): JsonResponse
    {
        $dias = $request->get('dias', 7);
        $eventos = Calendario::with(['caso', 'abogado', 'cliente'])
                            ->proximos($dias)
                            ->get();

        return response()->json([
            'mensaje' => 'Eventos próximos',
            'dias' => $dias,
            'total' => $eventos->count(),
            'data' => $eventos
        ], 200);
    }

    /**
     * 🆕 Eventos de hoy
     */
    public function hoy(): JsonResponse
    {
        $eventos = Calendario::with(['caso', 'abogado', 'cliente'])
                            ->hoy()
                            ->get();

        return response()->json([
            'mensaje' => 'Eventos de hoy',
            'fecha' => now()->format('d/m/Y'),
            'total' => $eventos->count(),
            'data' => $eventos
        ], 200);
    }

    /**
     * 🆕 Completar evento
     */
    public function completar($id): JsonResponse
    {
        $calendario = Calendario::find($id);

        if (!$calendario) {
            return response()->json([
                'mensaje' => 'Evento no encontrado'
            ], 404);
        }

        $calendario->completar();

        return response()->json([
            'mensaje' => 'Evento marcado como completado',
            'data' => $calendario
        ], 200);
    }

    /**
     * 🆕 Cancelar evento
     */
    public function cancelar($id): JsonResponse
    {
        $calendario = Calendario::find($id);

        if (!$calendario) {
            return response()->json([
                'mensaje' => 'Evento no encontrado'
            ], 404);
        }

        $calendario->cancelar();

        return response()->json([
            'mensaje' => 'Evento cancelado',
            'data' => $calendario
        ], 200);
    }

    /**
     * 🆕 Reagendar evento
     */
    public function reagendar(Request $request, $id): JsonResponse
    {
        $calendario = Calendario::find($id);

        if (!$calendario) {
            return response()->json([
                'mensaje' => 'Evento no encontrado'
            ], 404);
        }

        $validated = $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        $calendario->reagendar($validated['fecha_inicio'], $validated['fecha_fin'] ?? null);

        return response()->json([
            'mensaje' => 'Evento reagendado correctamente',
            'data' => $calendario
        ], 200);
    }

    /**
     * 🆕 Eventos por rango de fechas
     */
    public function porRango(Request $request): JsonResponse
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $eventos = Calendario::with(['caso', 'abogado', 'cliente', 'creador'])
                            ->entreFechas($request->fecha_inicio, $request->fecha_fin)
                            ->orderBy('fecha_inicio', 'asc')
                            ->get();

        return response()->json([
            'mensaje' => 'Eventos por rango de fechas',
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'total' => $eventos->count(),
            'data' => $eventos
        ], 200);
    }

    /**
     * 🆕 Estadísticas de calendario
     */
    public function estadisticas(): JsonResponse
    {
        $total = Calendario::count();
        $pendientes = Calendario::pendientes()->count();
        $completados = Calendario::porEstado('Completado')->count();
        $cancelados = Calendario::porEstado('Cancelado')->count();
        
        $porTipo = Calendario::selectRaw('tipo_evento, COUNT(*) as total')
            ->groupBy('tipo_evento')
            ->get()
            ->pluck('total', 'tipo_evento');

        $proximaSemana = Calendario::proximos(7)->count();
        $hoy = Calendario::hoy()->count();

        return response()->json([
            'mensaje' => 'Estadísticas del calendario',
            'data' => [
                'total_eventos' => $total,
                'eventos_pendientes' => $pendientes,
                'eventos_completados' => $completados,
                'eventos_cancelados' => $cancelados,
                'eventos_por_tipo' => $porTipo,
                'eventos_proxima_semana' => $proximaSemana,
                'eventos_hoy' => $hoy
            ]
        ], 200);
    }
}