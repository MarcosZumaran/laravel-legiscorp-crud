<?php

namespace App\Http\Controllers;

use App\Models\TiposCasos;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TiposCasosController extends Controller
{
    /**
     * Lista paginada con filtros
     */
    public function index(Request $request): JsonResponse
    {
        $query = TiposCasos::with('materia')->ordenarPorNombre();

        // Filtros opcionales
        if ($request->has('materia_id') && $request->materia_id) {
            $query->porMateria($request->materia_id);
        }

        if ($request->has('q') && $request->q) {
            $query->buscar($request->q);
        }

        $tiposCasos = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'mensaje' => 'Lista de tipos de casos paginada',
            'total'   => $tiposCasos->total(),
            'data'    => $tiposCasos->items(),
            'meta'    => [
                'current_page'  => $tiposCasos->currentPage(),
                'per_page'      => $tiposCasos->perPage(),
                'next_page_url' => $tiposCasos->nextPageUrl(),
                'prev_page_url' => $tiposCasos->previousPageUrl(),
                'last_page'     => $tiposCasos->lastPage(),
                'total'         => $tiposCasos->total(),
            ],
        ], 200);
    }

    /**
     * Todos los tipos de casos (para selects, combos, etc.)
     */
    public function todos(Request $request): JsonResponse
    {
        $query = TiposCasos::with('materia')->ordenarPorNombre();

        if ($request->has('materia_id') && $request->materia_id) {
            $query->porMateria($request->materia_id);
        }

        $tiposCasos = $query->get();

        return response()->json([
            'mensaje' => 'Lista completa de tipos de casos',
            'total'   => $tiposCasos->count(),
            'data'    => $tiposCasos,
        ], 200);
    }

    /**
     * Muestra un tipo de caso específico
     */
    public function show($id): JsonResponse
    {
        $tipoCaso = TiposCasos::with('materia')->find($id);

        if (!$tipoCaso) {
            return response()->json([
                'mensaje' => 'Tipo de caso no encontrado'
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Tipo de caso encontrado',
            'data' => $tipoCaso
        ], 200);
    }

    /**
     * Crea un nuevo tipo de caso
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'materia_id'  => 'required|integer|exists:materias_casos,id',
            'nombre'      => 'required|string|max:100|unique:tipos_casos,nombre,NULL,id,materia_id,' . $request->materia_id,
            'descripcion' => 'nullable|string',
        ], [
            'materia_id.required' => 'La materia es obligatoria',
            'materia_id.exists' => 'La materia seleccionada no existe',
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.unique' => 'Ya existe un tipo de caso con este nombre en la materia seleccionada',
        ]);

        $tipoCaso = TiposCasos::create($validated);

        return response()->json([
            'mensaje'   => 'Tipo de caso creado correctamente',
            'data' => $tipoCaso->load('materia'),
        ], 201);
    }

    /**
     * Actualiza un tipo de caso existente
     */
    public function update(Request $request, $id): JsonResponse
    {
        $tipoCaso = TiposCasos::find($id);

        if (!$tipoCaso) {
            return response()->json([
                'mensaje' => 'Tipo de caso no encontrado'
            ], 404);
        }

        $validated = $request->validate([
            'materia_id'  => 'sometimes|required|integer|exists:materias_casos,id',
            'nombre'      => 'sometimes|required|string|max:100|unique:tipos_casos,nombre,' . $id . ',id,materia_id,' . ($request->materia_id ?? $tipoCaso->materia_id),
            'descripcion' => 'nullable|string',
        ], [
            'materia_id.required' => 'La materia es obligatoria',
            'materia_id.exists' => 'La materia seleccionada no existe',
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.unique' => 'Ya existe un tipo de caso con este nombre en la materia seleccionada',
        ]);

        $tipoCaso->update($validated);

        return response()->json([
            'mensaje'   => 'Tipo de caso actualizado correctamente',
            'data' => $tipoCaso->load('materia'),
        ], 200);
    }

    /**
     * Elimina un tipo de caso (los triggers manejan el respaldo)
     */
    public function destroy($id): JsonResponse
    {
        $tipoCaso = TiposCasos::find($id);

        if (!$tipoCaso) {
            return response()->json([
                'mensaje' => 'Tipo de caso no encontrado'
            ], 404);
        }

        // ✅ Eliminación directa - Tus triggers se encargan del respaldo automáticamente
        $tipoCaso->delete();

        return response()->json([
            'mensaje' => 'Tipo de caso eliminado correctamente'
        ], 200);
    }

    /**
     * Búsqueda específica
     */
    public function buscar(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'materia_id' => 'nullable|integer|exists:materias_casos,id'
        ]);

        $query = TiposCasos::with('materia')->buscar($request->q);

        if ($request->has('materia_id') && $request->materia_id) {
            $query->porMateria($request->materia_id);
        }

        $resultados = $query->ordenarPorNombre()->get();

        return response()->json([
            'mensaje' => 'Resultados de búsqueda',
            'termino' => $request->q,
            'total'   => $resultados->count(),
            'data'    => $resultados,
        ], 200);
    }

    /**
     * Tipos de casos por materia específica
     */
    public function porMateria($materiaId): JsonResponse
    {
        $tiposCasos = TiposCasos::obtenerPorMateria($materiaId);

        return response()->json([
            'mensaje' => 'Tipos de casos por materia',
            'materia_id' => $materiaId,
            'total' => $tiposCasos->count(),
            'data' => $tiposCasos,
        ], 200);
    }
}