<?php

namespace App\Http\Controllers;

use App\Models\MateriaCaso;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MateriaCasoController extends Controller
{
    /**
     * Mostrar materias paginadas con filtros
     */
    public function index(Request $request): JsonResponse
    {
        $query = MateriaCaso::withCount('tiposCasos')->ordenarPorNombre();

        // Búsqueda si se especifica
        if ($request->has('q') && $request->q) {
            $query->buscar($request->q);
        }

        $materias = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'mensaje' => 'Lista de materias paginada',
            'total'   => $materias->total(),
            'data'    => $materias->items(),
            'meta'    => [
                'current_page'  => $materias->currentPage(),
                'per_page'      => $materias->perPage(),
                'next_page_url' => $materias->nextPageUrl(),
                'prev_page_url' => $materias->previousPageUrl(),
                'last_page'     => $materias->lastPage(),
            ],
        ], 200);
    }

    /**
     * Mostrar todas las materias (sin límite)
     */
    public function todos(Request $request): JsonResponse
    {
        $query = MateriaCaso::withCount('tiposCasos')->ordenarPorNombre();

        if ($request->has('q') && $request->q) {
            $query->buscar($request->q);
        }

        $materias = $query->get();

        return response()->json([
            'mensaje' => 'Lista completa de materias',
            'total'   => $materias->count(),
            'data'    => $materias,
        ], 200);
    }

    /**
     * Mostrar una materia específica
     */
    public function show($id): JsonResponse
    {
        $materia = MateriaCaso::with('tiposCasos')->withCount('tiposCasos')->find($id);

        if (!$materia) {
            return response()->json(['mensaje' => 'Materia no encontrada'], 404);
        }

        return response()->json([
            'mensaje' => 'Materia encontrada',
            'data' => $materia,
            'puede_eliminar' => $materia->puedeEliminar()
        ], 200);
    }

    /**
     * Crear una nueva materia
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:materias_casos,nombre',
            'descripcion' => 'nullable|string',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.unique' => 'Ya existe una materia con este nombre',
        ]);

        $materia = MateriaCaso::create($validated);

        return response()->json([
            'mensaje' => 'Materia creada correctamente',
            'data' => $materia
        ], 201);
    }

    /**
     * Actualizar una materia existente
     */
    public function update(Request $request, $id): JsonResponse
    {
        $materia = MateriaCaso::find($id);

        if (!$materia) {
            return response()->json(['mensaje' => 'Materia no encontrada'], 404);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:100|unique:materias_casos,nombre,' . $id,
            'descripcion' => 'nullable|string',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.unique' => 'Ya existe una materia con este nombre',
        ]);

        $materia->update($validated);

        return response()->json([
            'mensaje' => 'Materia actualizada correctamente',
            'data' => $materia
        ], 200);
    }

    /**
     * Eliminar una materia (con validación)
     */
    public function destroy($id): JsonResponse
    {
        $materia = MateriaCaso::withCount('tiposCasos')->find($id);

        if (!$materia) {
            return response()->json(['mensaje' => 'Materia no encontrada'], 404);
        }

        if (!$materia->puedeEliminar()) {
            return response()->json([
                'mensaje' => 'No se puede eliminar la materia porque tiene tipos de casos asociados',
                'total_tipos_casos' => $materia->tipos_casos_count
            ], 422);
        }

        $materia->delete();

        return response()->json(['mensaje' => 'Materia eliminada correctamente'], 200);
    }

    /**
     * Búsqueda específica de materias
     */
    public function buscar(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2'
        ]);

        $materias = MateriaCaso::withCount('tiposCasos')
            ->buscar($request->q)
            ->ordenarPorNombre()
            ->get();

        return response()->json([
            'mensaje' => 'Resultados de búsqueda',
            'termino' => $request->q,
            'total' => $materias->count(),
            'data' => $materias
        ], 200);
    }
}