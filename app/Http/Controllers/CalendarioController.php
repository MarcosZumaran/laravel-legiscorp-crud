<?php

namespace App\Http\Controllers;

use App\Models\Calendario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CalendarioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Calendario::with(['caso', 'abogado', 'cliente', 'creador']);
        
        // Filtros
        if ($request->has('tipo_evento')) {
            $query->porTipo($request->tipo_evento);
        }
        
        if ($request->has('estado')) {
            $query->porEstado($request->estado);
        }
        
        if ($request->has('abogado_id')) {
            $query->porAbogado($request->abogado_id);
        }
        
        if ($request->has('cliente_id')) {
            $query->porCliente($request->cliente_id);
        }
        
        if ($request->has('caso_id')) {
            $query->porCaso($request->caso_id);
        }
        
        if ($request->has('prioridad')) {
            $query->porPrioridad($request->prioridad);
        }
        
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->entreFechas($request->fecha_inicio, $request->fecha_fin);
        }
        
        if ($request->has('proximos')) {
            $query->proximos($request->get('proximos', 7));
        }
        
        if ($request->has('hoy')) {
            $query->hoy();
        }
        
        if ($request->has('pendientes')) {
            $query->pendientes();
        }
        
        if ($request->has('q')) {
            $query->buscar($request->q);
        }

        $eventos = $query->orderBy('fecha_inicio')->paginate(100);

        return response()->json([
            'data' => $eventos->items(),
            'paginacion' => [
                'total' => $eventos->total(),
                'per_page' => $eventos->perPage(),
                'current_page' => $eventos->currentPage(),
                'last_page' => $eventos->lastPage(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'ubicacion' => 'nullable|string|max:255',
            'tipo_evento' => 'required|string|in:Audiencia,Reunión,Plazo,Entrega,Otro',
            'estado' => 'sometimes|string|in:Pendiente,Completado,Cancelado',
            'color' => 'nullable|string|max:20',
            'recurrente' => 'sometimes|string|in:No,Diario,Semanal,Mensual,Anual',
            'caso_id' => 'nullable|integer|exists:casos,id',
            'abogado_id' => 'nullable|integer|exists:usuarios,id',
            'cliente_id' => 'nullable|integer|exists:clientes,id',
            'creado_por' => 'required|integer|exists:usuarios,id',
            'expediente' => 'nullable|string|max:30',
            'prioridad' => 'sometimes|string|in:Baja,Media,Alta,Urgente',
        ]);

        $evento = Calendario::create($validated);

        return response()->json([
            'mensaje' => 'Evento creado correctamente',
            'data' => $evento->load(['caso', 'abogado', 'cliente', 'creador'])
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $evento = Calendario::with(['caso', 'abogado', 'cliente', 'creador'])->find($id);

        if (!$evento) {
            return response()->json(['mensaje' => 'Evento no encontrado'], 404);
        }

        return response()->json(['data' => $evento]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $evento = Calendario::find($id);

        if (!$evento) {
            return response()->json(['mensaje' => 'Evento no encontrado'], 404);
        }

        $validated = $request->validate([
            'titulo' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'sometimes|required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'ubicacion' => 'nullable|string|max:255',
            'tipo_evento' => 'sometimes|required|string|in:Audiencia,Reunión,Plazo,Entrega,Otro',
            'estado' => 'sometimes|required|string|in:Pendiente,Completado,Cancelado',
            'color' => 'nullable|string|max:20',
            'recurrente' => 'sometimes|string|in:No,Diario,Semanal,Mensual,Anual',
            'caso_id' => 'nullable|integer|exists:casos,id',
            'abogado_id' => 'nullable|integer|exists:usuarios,id',
            'cliente_id' => 'nullable|integer|exists:clientes,id',
            'prioridad' => 'sometimes|string|in:Baja,Media,Alta,Urgente',
        ]);

        $evento->update($validated);

        return response()->json([
            'mensaje' => 'Evento actualizado correctamente',
            'data' => $evento->load(['caso', 'abogado', 'cliente', 'creador'])
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $evento = Calendario::find($id);

        if (!$evento) {
            return response()->json(['mensaje' => 'Evento no encontrado'], 404);
        }

        $evento->delete();

        return response()->json(['mensaje' => 'Evento eliminado correctamente']);
    }
}