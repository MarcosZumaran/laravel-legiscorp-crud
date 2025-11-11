<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class DocumentoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Documento::with(['usuario', 'caso', 'cliente']);
        
        // Filtros
        if ($request->has('categoria')) {
            $query->porCategoria($request->categoria);
        }
        
        if ($request->has('caso_id')) {
            $query->porCaso($request->caso_id);
        }
        
        if ($request->has('cliente_id')) {
            $query->porCliente($request->cliente_id);
        }
        
        if ($request->has('es_carpeta')) {
            $query->where('es_carpeta', $request->boolean('es_carpeta'));
        }
        
        if ($request->has('q')) {
            $query->buscar($request->q);
        }

        $documentos = $query->orderBy('fecha_subida', 'desc')->paginate(50);

        return response()->json([
            'data' => $documentos->items(),
            'paginacion' => [
                'total' => $documentos->total(),
                'per_page' => $documentos->perPage(),
                'current_page' => $documentos->currentPage(),
                'last_page' => $documentos->lastPage(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre_archivo' => 'required|string|max:255',
            'tipo_archivo' => 'nullable|string|max:50',
            'ruta' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'expediente' => 'nullable|string|max:30',
            'subido_por' => 'required|integer|exists:usuarios,id',
            'caso_id' => 'nullable|integer|exists:casos,id',
            'cliente_id' => 'nullable|integer|exists:clientes,id',
            'categoria' => 'required|string|in:General,Contrato,Sentencia,Resolución,Evidencia,Otro',
            'tamano_bytes' => 'nullable|integer',
            'es_carpeta' => 'sometimes|boolean',
            'carpeta_padre_id' => 'nullable|integer|exists:documentos,id',
            'es_publico' => 'sometimes|boolean',
            'etiquetas' => 'nullable|string|max:500',
        ]);

        $documento = Documento::create($validated);

        return response()->json([
            'mensaje' => 'Documento creado correctamente',
            'data' => $documento->load(['usuario', 'caso', 'cliente'])
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $documento = Documento::with(['usuario', 'caso', 'cliente', 'archivosHijos'])->find($id);

        if (!$documento) {
            return response()->json(['mensaje' => 'Documento no encontrado'], 404);
        }

        return response()->json(['data' => $documento]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $documento = Documento::find($id);

        if (!$documento) {
            return response()->json(['mensaje' => 'Documento no encontrado'], 404);
        }

        $validated = $request->validate([
            'nombre_archivo' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'expediente' => 'nullable|string|max:30',
            'categoria' => 'sometimes|required|string|in:General,Contrato,Sentencia,Resolución,Evidencia,Otro',
            'es_publico' => 'sometimes|boolean',
            'etiquetas' => 'nullable|string|max:500',
        ]);

        $documento->update($validated);

        return response()->json([
            'mensaje' => 'Documento actualizado correctamente',
            'data' => $documento->load(['usuario', 'caso', 'cliente'])
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $documento = Documento::withCount('archivosHijos')->find($id);

        if (!$documento) {
            return response()->json(['mensaje' => 'Documento no encontrado'], 404);
        }

        // Verificar si es carpeta y tiene archivos
        if ($documento->es_carpeta && $documento->archivos_hijos_count > 0) {
            return response()->json([
                'mensaje' => 'No se puede eliminar la carpeta porque contiene archivos'
            ], 422);
        }

        $documento->delete();

        return response()->json(['mensaje' => 'Documento eliminado correctamente']);
    }
}