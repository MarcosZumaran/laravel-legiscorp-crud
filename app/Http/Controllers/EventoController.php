<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class EventoController extends Controller
{
    /**
     * Mostrar lista de eventos paginada (50 por página)
     */
    public function index()
    {
        $eventos = Evento::paginate(50);

        // Desencriptar las descripciones en la colección paginada
        $eventos->getCollection()->transform(function ($evento) {
            if ($evento->descripcion) {
                try {
                    $evento->descripcion = Crypt::decryptString($evento->descripcion);
                } catch (\Exception $e) {
                    // En caso de error o texto plano
                }
            }
            return $evento;
        });

        return response()->json([
            'mensaje' => 'Lista de eventos paginada',
            'total'   => $eventos->total(),
            'data'    => $eventos->items(),
            'links'   => [
                'current_page'  => $eventos->currentPage(),
                'next_page_url' => $eventos->nextPageUrl(),
                'prev_page_url' => $eventos->previousPageUrl(),
                'last_page'     => $eventos->lastPage(),
            ],
        ], 200);
    }

    /**
     * Mostrar todos los eventos (sin paginación)
     */
    public function todos()
    {
        ini_set('memory_limit', '2G');

        $eventos = Evento::all();

        // Desencriptar descripciones
        $eventos->transform(function ($evento) {
            if ($evento->descripcion) {
                try {
                    $evento->descripcion = Crypt::decryptString($evento->descripcion);
                } catch (\Exception $e) {
                    // Si ya está en texto plano o hay error de descifrado
                }
            }
            return $evento;
        });

        return response()->json([
            'mensaje' => 'Lista completa de eventos',
            'total'   => $eventos->count(),
            'data'    => $eventos,
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'ubicacion' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
            'tipo_evento' => 'nullable|string|max:50',
            'recurrente' => 'nullable|string|max:50',
            'caso_id' => 'nullable|integer|exists:casos,id',
            'etapa_id' => 'nullable|integer|exists:etapas_procesales,id',
            'expediente' => 'nullable|string|max:30',
            'creado_por' => 'required|integer|exists:usuarios,id',
        ]);

        $evento = Evento::create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion ? Crypt::encryptString($request->descripcion) : null,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'ubicacion' => $request->ubicacion,
            'color' => $request->color ?? '#3486bc',
            'tipo_evento' => $request->tipo_evento ?? 'Otro',
            'recurrente' => $request->recurrente ?? 'No',
            'caso_id' => $request->caso_id,
            'etapa_id' => $request->etapa_id,
            'expediente' => $request->expediente,
            'creado_por' => $request->creado_por,
        ]);

        return response()->json([
            'mensaje' => 'Evento creado correctamente',
            'evento' => $evento
        ], 201);
    }

    public function show($id)
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json(['mensaje' => 'Evento no encontrado'], 404);
        }

        if ($evento->descripcion) {
            try {
                $evento->descripcion = Crypt::decryptString($evento->descripcion);
            } catch (\Exception $e) {}
        }

        return response()->json($evento, 200);
    }

    public function update(Request $request, $id)
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json(['mensaje' => 'Evento no encontrado'], 404);
        }

        $request->validate([
            'titulo' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'sometimes|required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'ubicacion' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
            'tipo_evento' => 'nullable|string|max:50',
            'recurrente' => 'nullable|string|max:50',
            'caso_id' => 'nullable|integer|exists:casos,id',
            'etapa_id' => 'nullable|integer|exists:etapas_procesales,id',
            'expediente' => 'nullable|string|max:30',
        ]);

        $evento->update([
            'titulo' => $request->titulo ?? $evento->titulo,
            'descripcion' => $request->has('descripcion')
                ? ($request->descripcion ? Crypt::encryptString($request->descripcion) : null)
                : $evento->descripcion,
            'fecha_inicio' => $request->fecha_inicio ?? $evento->fecha_inicio,
            'fecha_fin' => $request->fecha_fin ?? $evento->fecha_fin,
            'ubicacion' => $request->ubicacion ?? $evento->ubicacion,
            'color' => $request->color ?? $evento->color,
            'tipo_evento' => $request->tipo_evento ?? $evento->tipo_evento,
            'recurrente' => $request->recurrente ?? $evento->recurrente,
            'caso_id' => $request->caso_id ?? $evento->caso_id,
            'etapa_id' => $request->etapa_id ?? $evento->etapa_id,
            'expediente' => $request->expediente ?? $evento->expediente,
        ]);

        return response()->json([
            'mensaje' => 'Evento actualizado correctamente',
            'evento' => $evento
        ], 200);
    }

    public function destroy($id)
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json(['mensaje' => 'Evento no encontrado'], 404);
        }

        $evento->delete();

        return response()->json(['mensaje' => 'Evento eliminado correctamente'], 200);
    }
}
