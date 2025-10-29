<?php

namespace App\Http\Controllers;

use App\Models\Calendario;
use Illuminate\Http\Request;

class CalendarioController extends Controller
{
    /**
     * Muestra la lista completa de eventos del calendario.
     */
    /**
     * Mostrar lista de eventos del calendario (paginada de 50 en 50)
     */
    public function index()
    {
        $calendarios = Calendario::paginate(50);

        return response()->json([
            'mensaje' => 'Lista de eventos del calendario (paginada)',
            'total'   => $calendarios->total(),
            'data'    => $calendarios->items(),
            'links'   => [
                'current_page'  => $calendarios->currentPage(),
                'next_page_url' => $calendarios->nextPageUrl(),
                'prev_page_url' => $calendarios->previousPageUrl(),
                'last_page'     => $calendarios->lastPage(),
            ],
        ], 200);
    }

    /**
     * Mostrar todos los eventos del calendario (sin paginación)
     */
    public function todos()
    {
        ini_set('memory_limit', '2G');

        $calendarios = Calendario::all();

        return response()->json([
            'mensaje' => 'Lista completa de eventos del calendario',
            'total'   => $calendarios->count(),
            'data'    => $calendarios,
        ], 200);
    }

    /**
     * Guarda un nuevo evento en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date',
            'tipo_evento' => 'nullable|string|max:50',
            'estado' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'caso_id' => 'nullable|integer',
            'etapa_id' => 'nullable|integer',
            'abogado_id' => 'nullable|integer',
            'cliente_id' => 'nullable|integer',
            'creado_por' => 'nullable|integer',
        ]);

        $calendario = Calendario::create($validated);

        return response()->json([
            'message' => 'Evento creado correctamente',
            'data' => $calendario
        ], 201);
    }

    /**
     * Muestra un evento específico.
     */
    public function show($id)
    {
        $calendario = Calendario::findOrFail($id);
        return response()->json($calendario);
    }

    /**
     * Muestra el formulario para editar un evento (si se usa vista).
     */
    public function edit(Calendario $calendario)
    {
        //
    }

    /**
     * Actualiza los datos de un evento.
     */
    public function update(Request $request, $id)
    {
        $calendario = Calendario::findOrFail($id);

        $validated = $request->validate([
            'titulo' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'sometimes|required|date',
            'fecha_fin' => 'nullable|date',
            'tipo_evento' => 'nullable|string|max:50',
            'estado' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'caso_id' => 'nullable|integer',
            'etapa_id' => 'nullable|integer',
            'abogado_id' => 'nullable|integer',
            'cliente_id' => 'nullable|integer',
            'creado_por' => 'nullable|integer',
        ]);

        $calendario->update($validated);

        return response()->json([
            'message' => 'Evento actualizado correctamente',
            'data' => $calendario
        ]);
    }

    /**
     * Elimina un evento del calendario.
     */
    public function destroy($id)
    {
        $calendario = Calendario::findOrFail($id);
        $calendario->delete();

        return response()->json(['message' => 'Evento eliminado correctamente']);
    }
}
