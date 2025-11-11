<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DocumentoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Documento::with(['usuario', 'caso', 'cliente', 'carpetaPadre'])
                         ->orderBy('fecha_subida', 'desc');

        // Filtros
        if ($request->has('categoria') && $request->categoria) {
            $query->porCategoria($request->categoria);
        }

        if ($request->has('caso_id') && $request->caso_id) {
            $query->porCaso($request->caso_id);
        }

        if ($request->has('cliente_id') && $request->cliente_id) {
            $query->porCliente($request->cliente_id);
        }

        if ($request->has('es_carpeta') && $request->es_carpeta !== '') {
            $query->where('es_carpeta', (bool)$request->es_carpeta);
        }

        if ($request->has('q') && $request->q) {
            $query->buscar($request->q);
        }

        $documentos = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'mensaje' => 'Lista de documentos paginada',
            'total'   => $documentos->total(),
            'data'    => $documentos->items(),
            'meta'    => [
                'current_page'  => $documentos->currentPage(),
                'per_page'      => $documentos->perPage(),
                'next_page_url' => $documentos->nextPageUrl(),
                'prev_page_url' => $documentos->previousPageUrl(),
                'last_page'     => $documentos->lastPage(),
            ],
        ], 200);
    }

    public function todos(Request $request): JsonResponse
    {
        $query = Documento::with(['usuario', 'caso', 'cliente'])
                         ->orderBy('fecha_subida', 'desc');

        if ($request->has('categoria') && $request->categoria) {
            $query->porCategoria($request->categoria);
        }

        $documentos = $query->get();

        return response()->json([
            'mensaje' => 'Lista completa de documentos',
            'total'   => $documentos->count(),
            'data'    => $documentos,
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $documento = Documento::with(['usuario', 'caso', 'cliente', 'carpetaPadre'])->find($id);

        if (!$documento) {
            return response()->json([
                'mensaje' => 'Documento no encontrado'
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Documento encontrado',
            'data' => $documento,
            'puede_eliminar' => $documento->puedeEliminar()
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre_archivo' => 'required|string|max:255',
            'tipo_archivo'   => 'nullable|string|max:50',
            'ruta'           => 'required|string|max:255',
            'descripcion'    => 'nullable|string',
            'expediente'     => 'nullable|string|max:30',
            'subido_por'     => 'required|integer|exists:usuarios,id',
            'caso_id'        => 'nullable|integer|exists:casos,id',
            'cliente_id'     => 'nullable|integer|exists:clientes,id',
            'categoria'      => 'required|string|in:General,Contrato,Sentencia,Resolución,Evidencia,Otro',
            'tamano_bytes'   => 'nullable|integer',
            'es_carpeta'     => 'boolean',
            'carpeta_padre_id' => 'nullable|integer|exists:documentos,id',
            'es_publico'     => 'boolean',
            'etiquetas'      => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $validator->errors()
            ], 422);
        }

        // NO se encripta el archivo
        $documento = Documento::create($validator->validated());

        return response()->json([
            'mensaje' => 'Documento creado correctamente',
            'data' => $documento->load(['usuario', 'caso', 'cliente'])
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $documento = Documento::find($id);

        if (!$documento) {
            return response()->json([
                'mensaje' => 'Documento no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre_archivo' => 'sometimes|required|string|max:255',
            'tipo_archivo'   => 'nullable|string|max:50',
            'ruta'           => 'sometimes|required|string|max:255',
            'descripcion'    => 'nullable|string',
            'expediente'     => 'nullable|string|max:30',
            'categoria'      => 'sometimes|required|string|in:General,Contrato,Sentencia,Resolución,Evidencia,Otro',
            'es_publico'     => 'boolean',
            'etiquetas'      => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $validator->errors()
            ], 422);
        }

        // NO encriptar la ruta - se actualiza normalmente
        $documento->update($validator->validated());

        return response()->json([
            'mensaje' => 'Documento actualizado correctamente',
            'data' => $documento->load(['usuario', 'caso', 'cliente'])
        ], 200);
    }

    public function destroy($id): JsonResponse
    {
        $documento = Documento::withCount('archivosHijos')->find($id);

        if (!$documento) {
            return response()->json([
                'mensaje' => 'Documento no encontrado'
            ], 404);
        }

        if (!$documento->puedeEliminar()) {
            return response()->json([
                'mensaje' => 'No se puede eliminar porque contiene archivos hijos',
                'total_archivos_hijos' => $documento->archivos_hijos_count
            ], 422);
        }

        $documento->delete();

        return response()->json([
            'mensaje' => 'Documento eliminado correctamente'
        ], 200);
    }

    // MÉTODOS ADICIONALES
    public function buscar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $validator->errors()
            ], 422);
        }

        $documentos = Documento::with(['usuario', 'caso', 'cliente'])
                              ->buscar($request->q)
                              ->orderBy('fecha_subida', 'desc')
                              ->get();

        return response()->json([
            'mensaje' => 'Resultados de búsqueda',
            'termino' => $request->q,
            'total' => $documentos->count(),
            'data' => $documentos
        ], 200);
    }

    public function carpetas(): JsonResponse
    {
        $carpetas = Documento::carpetas()
                            ->withCount('archivosHijos')
                            ->orderBy('nombre_archivo')
                            ->get();

        return response()->json([
            'mensaje' => 'Lista de carpetas',
            'data' => $carpetas
        ], 200);
    }

    public function porCarpeta($carpetaId): JsonResponse
    {
        $documentos = Documento::where('carpeta_padre_id', $carpetaId)
                              ->with(['usuario', 'caso', 'cliente'])
                              ->orderBy('fecha_subida', 'desc')
                              ->get();

        return response()->json([
            'mensaje' => 'Documentos por carpeta',
            'carpeta_id' => $carpetaId,
            'total' => $documentos->count(),
            'data' => $documentos
        ], 200);
    }

    public function estadisticas(): JsonResponse
    {
        $total = Documento::count();
        $carpetas = Documento::carpetas()->count();
        $archivos = Documento::archivos()->count();
        
        $porCategoria = Documento::selectRaw('categoria, COUNT(*) as total')
            ->groupBy('categoria')
            ->get()
            ->pluck('total', 'categoria');

        return response()->json([
            'mensaje' => 'Estadísticas de documentos',
            'data' => [
                'total_documentos' => $total,
                'total_carpetas' => $carpetas,
                'total_archivos' => $archivos,
                'documentos_por_categoria' => $porCategoria
            ]
        ], 200);
    }
}