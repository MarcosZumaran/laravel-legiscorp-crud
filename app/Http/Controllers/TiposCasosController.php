<?php

namespace App\Http\Controllers;

use App\Models\TiposCasos;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TiposCasosController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TiposCasos::with('materia');
        
        // Filtro por materia si se especifica
        if ($request->has('materia_id')) {
            $query->porMateria($request->materia_id);
        }
        
        // Búsqueda si se especifica
        if ($request->has('q')) {
            $query->buscar($request->q);
        }
        
        // Ordenamiento por nombre por defecto
        $tiposCasos = $query->orderBy('nombre')->paginate(50);

        return response()->json([
            'data' => $tiposCasos->items(),
            'paginacion' => [
                'total' => $tiposCasos->total(),
                'per_page' => $tiposCasos->perPage(),
                'current_page' => $tiposCasos->currentPage(),
                'last_page' => $tiposCasos->lastPage(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'materia_id' => 'required|integer|exists:materias_casos,id',
            'nombre' => 'required|string|max:100|unique:tipos_casos,nombre,NULL,id,materia_id,' . $request->materia_id,
            'descripcion' => 'nullable|string|max:2000',
        ]);

        $tipoCaso = TiposCasos::create($validated);

        return response()->json([
            'mensaje' => 'Tipo de caso creado correctamente',
            'data' => $tipoCaso->load('materia')
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $tipoCaso = TiposCasos::with('materia')->find($id);

        if (!$tipoCaso) {
            return response()->json(['mensaje' => 'Tipo de caso no encontrado'], 404);
        }

        return response()->json(['data' => $tipoCaso]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $tipoCaso = TiposCasos::find($id);

        if (!$tipoCaso) {
            return response()->json(['mensaje' => 'Tipo de caso no encontrado'], 404);
        }

        $validated = $request->validate([
            'materia_id' => 'sometimes|required|integer|exists:materias_casos,id',
            'nombre' => 'sometimes|required|string|max:100|unique:tipos_casos,nombre,' . $id . ',id,materia_id,' . ($request->materia_id ?? $tipoCaso->materia_id),
            'descripcion' => 'nullable|string|max:2000',
        ]);

        $tipoCaso->update($validated);

        return response()->json([
            'mensaje' => 'Tipo de caso actualizado correctamente',
            'data' => $tipoCaso->load('materia')
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $tipoCaso = TiposCasos::find($id);

        if (!$tipoCaso) {
            return response()->json(['mensaje' => 'Tipo de caso no encontrado'], 404);
        }

        // Aquí podrías agregar validación de dependencias si es necesario
        // if ($tipoCaso->casos()->exists()) { ... }

        $tipoCaso->delete();

        return response()->json(['mensaje' => 'Tipo de caso eliminado correctamente']);
    }
}