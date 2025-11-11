<?php

namespace App\Http\Controllers;

use App\Models\Caso;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CasoController extends Controller
{
    /**
     * Lista paginada de casos con filtros
     */
    public function index(Request $request): JsonResponse
    {
        $query = Caso::with(['cliente', 'abogado', 'materia', 'tipoCaso'])
                    ->ordenarPorFecha();

        // Filtros
        if ($request->has('estado') && $request->estado) {
            $query->porEstado($request->estado);
        }

        if ($request->has('cliente_id') && $request->cliente_id) {
            $query->porCliente($request->cliente_id);
        }

        if ($request->has('abogado_id') && $request->abogado_id) {
            $query->porAbogado($request->abogado_id);
        }

        if ($request->has('materia_id') && $request->materia_id) {
            $query->porMateria($request->materia_id);
        }

        if ($request->has('activos') && $request->activos) {
            $query->activos();
        }

        if ($request->has('q') && $request->q) {
            $query->buscar($request->q);
        }

        $casos = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'mensaje' => 'Lista de casos paginada',
            'total'   => $casos->total(),
            'data'    => $casos->items(),
            'meta'    => [
                'current_page'  => $casos->currentPage(),
                'per_page'      => $casos->perPage(),
                'next_page_url' => $casos->nextPageUrl(),
                'prev_page_url' => $casos->previousPageUrl(),
                'last_page'     => $casos->lastPage(),
            ],
        ], 200);
    }

    /**
     * Todos los casos con filtros opcionales
     */
    public function todos(Request $request): JsonResponse
    {
        $query = Caso::with(['cliente', 'abogado', 'materia', 'tipoCaso'])
                    ->ordenarPorFecha();

        if ($request->has('estado') && $request->estado) {
            $query->porEstado($request->estado);
        }

        $casos = $query->get();

        return response()->json([
            'mensaje' => 'Lista completa de casos',
            'total'   => $casos->count(),
            'data'    => $casos,
        ], 200);
    }

    /**
     * Mostrar caso específico con todas las relaciones
     */
    public function show($id): JsonResponse
    {
        $caso = Caso::with([
            'cliente', 
            'abogado', 
            'materia', 
            'tipoCaso',
            'comentarios.usuario',
            'documentos'
        ])->withCount(['comentarios', 'documentos'])->find($id);

        if (!$caso) {
            return response()->json([
                'mensaje' => 'Caso no encontrado'
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Caso encontrado',
            'data' => $caso,
            'puede_eliminar' => $caso->puedeEliminar()
        ], 200);
    }

    /**
     * Crear nuevo caso (encriptación automática en el modelo)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'codigo_caso' => 'required|string|max:50|unique:casos',
            'numero_expediente' => 'nullable|string|max:50',
            'numero_carpeta_fiscal' => 'nullable|string|max:50',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'materia_id' => 'required|integer|exists:materias_casos,id',
            'tipo_caso_id' => 'nullable|integer|exists:tipos_casos,id',
            'estado' => 'nullable|string|in:Abierto,En Proceso,Cerrado',
            'fecha_inicio' => 'nullable|date',
            'fecha_cierre' => 'nullable|date',
            'cliente_id' => 'required|integer|exists:clientes,id',
            'abogado_id' => 'required|integer|exists:usuarios,id',
            'contraparte' => 'nullable|string|max:255',
            'juzgado' => 'nullable|string|max:100',
            'fiscal' => 'nullable|string|max:100',
        ], [
            'codigo_caso.required' => 'El código de caso es obligatorio',
            'codigo_caso.unique' => 'El código de caso ya está registrado',
            'titulo.required' => 'El título es obligatorio',
            'materia_id.required' => 'La materia es obligatoria',
            'cliente_id.required' => 'El cliente es obligatorio',
            'abogado_id.required' => 'El abogado es obligatorio',
        ]);

        // La descripción se encripta automáticamente en el modelo
        $caso = Caso::create($validated);

        return response()->json([
            'mensaje' => 'Caso creado correctamente',
            'data' => $caso->load(['cliente', 'abogado', 'materia', 'tipoCaso'])
        ], 201);
    }

    /**
     * Actualizar caso existente (encriptación automática en el modelo)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $caso = Caso::find($id);

        if (!$caso) {
            return response()->json([
                'mensaje' => 'Caso no encontrado'
            ], 404);
        }

        $validated = $request->validate([
            'codigo_caso' => 'sometimes|required|string|max:50|unique:casos,codigo_caso,' . $id,
            'numero_expediente' => 'nullable|string|max:50',
            'numero_carpeta_fiscal' => 'nullable|string|max:50',
            'titulo' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'materia_id' => 'sometimes|required|integer|exists:materias_casos,id',
            'tipo_caso_id' => 'nullable|integer|exists:tipos_casos,id',
            'estado' => 'nullable|string|in:Abierto,En Proceso,Cerrado',
            'fecha_inicio' => 'nullable|date',
            'fecha_cierre' => 'nullable|date',
            'cliente_id' => 'sometimes|required|integer|exists:clientes,id',
            'abogado_id' => 'sometimes|required|integer|exists:usuarios,id',
            'contraparte' => 'nullable|string|max:255',
            'juzgado' => 'nullable|string|max:100',
            'fiscal' => 'nullable|string|max:100',
        ], [
            'codigo_caso.unique' => 'El código de caso ya está registrado',
        ]);

        // La descripción se encripta automáticamente en el modelo
        $caso->update($validated);

        return response()->json([
            'mensaje' => 'Caso actualizado correctamente',
            'data' => $caso->load(['cliente', 'abogado', 'materia', 'tipoCaso'])
        ], 200);
    }

    /**
     * Eliminar caso
     */
    public function destroy($id): JsonResponse
    {
        $caso = Caso::withCount(['comentarios', 'documentos'])->find($id);

        if (!$caso) {
            return response()->json([
                'mensaje' => 'Caso no encontrado'
            ], 404);
        }

        if (!$caso->puedeEliminar()) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el caso porque tiene comentarios o documentos asociados',
                'total_comentarios' => $caso->comentarios_count,
                'total_documentos' => $caso->documentos_count
            ], 422);
        }

        $caso->delete();

        return response()->json([
            'mensaje' => 'Caso eliminado correctamente'
        ], 200);
    }

    /**
     * Búsqueda de casos
     */
    public function buscar(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2'
        ]);

        $casos = Caso::with(['cliente', 'abogado', 'materia'])
                    ->buscar($request->q)
                    ->ordenarPorFecha()
                    ->get();

        return response()->json([
            'mensaje' => 'Resultados de búsqueda',
            'termino' => $request->q,
            'total' => $casos->count(),
            'data' => $casos
        ], 200);
    }

    /**
     * Casos por cliente específico
     */
    public function porCliente($clienteId): JsonResponse
    {
        $casos = Caso::with(['cliente', 'abogado', 'materia', 'tipoCaso'])
                    ->porCliente($clienteId)
                    ->ordenarPorFecha()
                    ->get();

        return response()->json([
            'mensaje' => 'Casos del cliente',
            'cliente_id' => $clienteId,
            'total' => $casos->count(),
            'data' => $casos
        ], 200);
    }

    /**
     * Casos por abogado específico
     */
    public function porAbogado($abogadoId): JsonResponse
    {
        $casos = Caso::with(['cliente', 'abogado', 'materia', 'tipoCaso'])
                    ->porAbogado($abogadoId)
                    ->ordenarPorFecha()
                    ->get();

        return response()->json([
            'mensaje' => 'Casos del abogado',
            'abogado_id' => $abogadoId,
            'total' => $casos->count(),
            'data' => $casos
        ], 200);
    }

    /**
     * Cambiar estado del caso
     */
    public function cambiarEstado(Request $request, $id): JsonResponse
    {
        $caso = Caso::find($id);

        if (!$caso) {
            return response()->json([
                'mensaje' => 'Caso no encontrado'
            ], 404);
        }

        $request->validate([
            'estado' => 'required|string|in:Abierto,En Proceso,Cerrado'
        ]);

        $caso->update(['estado' => $request->estado]);

        return response()->json([
            'mensaje' => 'Estado del caso actualizado',
            'data' => $caso
        ], 200);
    }

    /**
     * Cerrar caso
     */
    public function cerrar($id): JsonResponse
    {
        $caso = Caso::find($id);

        if (!$caso) {
            return response()->json([
                'mensaje' => 'Caso no encontrado'
            ], 404);
        }

        $caso->cerrar();

        return response()->json([
            'mensaje' => 'Caso cerrado correctamente',
            'data' => $caso
        ], 200);
    }

    /**
     * Estadísticas de casos
     */
    public function estadisticas(): JsonResponse
    {
        $total = Caso::count();
        $abiertos = Caso::porEstado('Abierto')->count();
        $enProceso = Caso::porEstado('En Proceso')->count();
        $cerrados = Caso::porEstado('Cerrado')->count();
        
        $porMateria = Caso::selectRaw('materia_id, COUNT(*) as total')
            ->groupBy('materia_id')
            ->with('materia')
            ->get()
            ->pluck('total', 'materia.nombre');

        $recientes = Caso::recientes(7)->count();

        return response()->json([
            'mensaje' => 'Estadísticas de casos',
            'data' => [
                'total_casos' => $total,
                'casos_abiertos' => $abiertos,
                'casos_en_proceso' => $enProceso,
                'casos_cerrados' => $cerrados,
                'casos_por_materia' => $porMateria,
                'casos_recientes_7_dias' => $recientes
            ]
        ], 200);
    }
}