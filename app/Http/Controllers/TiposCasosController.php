<?php

namespace App\Http\Controllers;

use App\Models\TiposCasos;
use Illuminate\Http\Request;

class TiposCasosController extends Controller
{
    public function index()
    {
        $tiposCasos = TiposCasos::paginate(50);

        return response()->json([
            'mensaje' => 'Lista de tipos de casos paginada',
            'total'   => $tiposCasos->total(),
            'data'    => $tiposCasos->items(),
            'links'   => [
                'current_page'  => $tiposCasos->currentPage(),
                'next_page_url' => $tiposCasos->nextPageUrl(),
                'prev_page_url' => $tiposCasos->previousPageUrl(),
                'last_page'     => $tiposCasos->lastPage(),
            ],
        ], 200);
    }

    /**
     * Muestra todos los tipos de casos (sin límite)
     */
    public function todos()
    {
        ini_set('memory_limit', '2G');

        $tiposCasos = TiposCasos::all();

        return response()->json([
            'mensaje' => 'Lista completa de tipos de casos',
            'total'   => $tiposCasos->count(),
            'data'    => $tiposCasos,
        ], 200);
    }

    /**
     * Muestra un tipo de caso específico.
     */
    public function show($id)
    {
        $tipoCaso = TiposCasos::find($id);

        if (!$tipoCaso) {
            return response()->json(['mensaje' => 'Tipo de caso no encontrado'], 404);
        }

        return response()->json($tipoCaso, 200);
    }

    /**
     * Crea un nuevo tipo de caso.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'materia_id'  => 'required|integer|exists:materias_casos,id',
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string',
        ]);

        $tipoCaso = TiposCasos::create($validated);

        return response()->json([
            'mensaje'   => 'Tipo de caso creado correctamente',
            'tipo_caso' => $tipoCaso,
        ], 201);
    }

    /**
     * Actualiza un tipo de caso existente.
     */
    public function update(Request $request, $id)
    {
        $tipoCaso = TiposCasos::find($id);

        if (!$tipoCaso) {
            return response()->json(['mensaje' => 'Tipo de caso no encontrado'], 404);
        }

        $validated = $request->validate([
            'materia_id'  => 'sometimes|required|integer|exists:materias_casos,id',
            'nombre'      => 'sometimes|required|string|max:100',
            'descripcion' => 'nullable|string',
        ]);

        $tipoCaso->update($validated);

        return response()->json([
            'mensaje'   => 'Tipo de caso actualizado correctamente',
            'tipo_caso' => $tipoCaso,
        ], 200);
    }

    /**
     * Elimina un tipo de caso.
     */
    public function destroy($id)
    {
        $tipoCaso = TiposCasos::find($id);

        if (!$tipoCaso) {
            return response()->json(['mensaje' => 'Tipo de caso no encontrado'], 404);
        }

        $tipoCaso->delete();

        return response()->json(['mensaje' => 'Tipo de caso eliminado correctamente'], 200);
    }
}
