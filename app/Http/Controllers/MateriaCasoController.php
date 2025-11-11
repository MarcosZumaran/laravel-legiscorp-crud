<?php

namespace App\Http\Controllers;

use App\Models\MateriaCaso;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MateriaCasoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MateriaCaso::withCount('tiposCasos');
        
        // Búsqueda si se especifica
        if ($request->has('q')) {
            $query->buscar($request->q);
        }
        
        $materias = $query->orderBy('nombre')->paginate(50);

        return response()->json([
            'data' => $materias->items(),
            'paginacion' => [
                'total' => $materias->total(),
                'per_page' => $materias->perPage(),
                'current_page' => $materias->currentPage(),
                'last_page' => $materias->lastPage(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:materias_casos,nombre',
            'descripcion' => 'nullable|string|max:2000',
        ]);

        $materia = MateriaCaso::create($validated);

        return response()->json([
            'mensaje' => 'Materia creada correctamente',
            'data' => $materia
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $materia = MateriaCaso::withCount('tiposCasos')->find($id);

        if (!$materia) {
            return response()->json(['mensaje' => 'Materia no encontrada'], 404);
        }

        return response()->json(['data' => $materia]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $materia = MateriaCaso::find($id);

        if (!$materia) {
            return response()->json(['mensaje' => 'Materia no encontrada'], 404);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:100|unique:materias_casos,nombre,' . $id,
            'descripcion' => 'nullable|string|max:2000',
        ]);

        $materia->update($validated);

        return response()->json([
            'mensaje' => 'Materia actualizada correctamente',
            'data' => $materia
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $materia = MateriaCaso::withCount('tiposCasos')->find($id);

        if (!$materia) {
            return response()->json(['mensaje' => 'Materia no encontrada'], 404);
        }

        // Verificar si tiene tipos de casos asociados
        if ($materia->tipos_casos_count > 0) {
            return response()->json([
                'mensaje' => 'No se puede eliminar la materia porque tiene tipos de casos asociados'
            ], 422);
        }

        $materia->delete();

        return response()->json(['mensaje' => 'Materia eliminada correctamente']);
    }
}