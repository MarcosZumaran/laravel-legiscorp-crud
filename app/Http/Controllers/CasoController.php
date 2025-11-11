<?php

namespace App\Http\Controllers;

use App\Models\Caso;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CasoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Caso::with(['cliente', 'abogado', 'materia', 'tipoCaso']);
        
        // Filtros
        if ($request->has('estado')) {
            $query->porEstado($request->estado);
        }
        
        if ($request->has('cliente_id')) {
            $query->porCliente($request->cliente_id);
        }
        
        if ($request->has('abogado_id')) {
            $query->porAbogado($request->abogado_id);
        }
        
        if ($request->has('materia_id')) {
            $query->porMateria($request->materia_id);
        }
        
        if ($request->has('activos')) {
            $query->activos();
        }
        
        if ($request->has('q')) {
            $query->buscar($request->q);
        }

        $casos = $query->orderBy('creado_en', 'desc')->paginate(50);

        return response()->json([
            'data' => $casos->items(),
            'paginacion' => [
                'total' => $casos->total(),
                'per_page' => $casos->perPage(),
                'current_page' => $casos->currentPage(),
                'last_page' => $casos->lastPage(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'codigo_caso' => 'required|string|max:50|unique:casos,codigo_caso',
            'numero_expediente' => 'nullable|string|max:50',
            'numero_carpeta_fiscal' => 'nullable|string|max:50',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'materia_id' => 'required|integer|exists:materias_casos,id',
            'tipo_caso_id' => 'nullable|integer|exists:tipos_casos,id',
            'estado' => 'sometimes|string|in:Abierto,En Proceso,Cerrado',
            'fecha_inicio' => 'nullable|date',
            'fecha_cierre' => 'nullable|date|after_or_equal:fecha_inicio',
            'cliente_id' => 'required|integer|exists:clientes,id',
            'abogado_id' => 'required|integer|exists:usuarios,id',
            'contraparte' => 'nullable|string|max:255',
            'juzgado' => 'nullable|string|max:100',
            'fiscal' => 'nullable|string|max:100',
        ]);

        $caso = Caso::create($validated);

        return response()->json([
            'mensaje' => 'Caso creado correctamente',
            'data' => $caso->load(['cliente', 'abogado', 'materia', 'tipoCaso'])
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $caso = Caso::with(['cliente', 'abogado', 'materia', 'tipoCaso'])->find($id);

        if (!$caso) {
            return response()->json(['mensaje' => 'Caso no encontrado'], 404);
        }

        return response()->json(['data' => $caso]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $caso = Caso::find($id);

        if (!$caso) {
            return response()->json(['mensaje' => 'Caso no encontrado'], 404);
        }

        $validated = $request->validate([
            'codigo_caso' => 'sometimes|required|string|max:50|unique:casos,codigo_caso,' . $id,
            'numero_expediente' => 'nullable|string|max:50',
            'numero_carpeta_fiscal' => 'nullable|string|max:50',
            'titulo' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'materia_id' => 'sometimes|required|integer|exists:materias_casos,id',
            'tipo_caso_id' => 'nullable|integer|exists:tipos_casos,id',
            'estado' => 'sometimes|required|string|in:Abierto,En Proceso,Cerrado',
            'fecha_inicio' => 'nullable|date',
            'fecha_cierre' => 'nullable|date|after_or_equal:fecha_inicio',
            'cliente_id' => 'sometimes|required|integer|exists:clientes,id',
            'abogado_id' => 'sometimes|required|integer|exists:usuarios,id',
            'contraparte' => 'nullable|string|max:255',
            'juzgado' => 'nullable|string|max:100',
            'fiscal' => 'nullable|string|max:100',
        ]);

        $caso->update($validated);

        return response()->json([
            'mensaje' => 'Caso actualizado correctamente',
            'data' => $caso->load(['cliente', 'abogado', 'materia', 'tipoCaso'])
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $caso = Caso::withCount(['comentarios', 'documentos'])->find($id);

        if (!$caso) {
            return response()->json(['mensaje' => 'Caso no encontrado'], 404);
        }

        // Verificar si tiene comentarios o documentos asociados
        if ($caso->comentarios_count > 0 || $caso->documentos_count > 0) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el caso porque tiene comentarios o documentos asociados'
            ], 422);
        }

        $caso->delete();

        return response()->json(['mensaje' => 'Caso eliminado correctamente']);
    }
}