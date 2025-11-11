<?php

namespace App\Http\Controllers;

use App\Models\DocumentoCompartido;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DocumentoCompartidoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DocumentoCompartido::with([
            'documento',
            'compartidoConUsuario',
            'compartidoPorUsuario'
        ]);
        
        // Filtros
        if ($request->has('documento_id')) {
            $query->porDocumento($request->documento_id);
        }
        
        if ($request->has('usuario_id')) {
            $query->porUsuario($request->usuario_id);
        }
        
        if ($request->has('rol')) {
            $query->porRol($request->rol);
        }
        
        if ($request->has('permisos')) {
            $query->porPermisos($request->permisos);
        }
        
        if ($request->has('compartido_por')) {
            $query->porCompartidoPor($request->compartido_por);
        }

        $documentosCompartidos = $query->recientes()->paginate(50);

        return response()->json([
            'data' => $documentosCompartidos->items(),
            'paginacion' => [
                'total' => $documentosCompartidos->total(),
                'per_page' => $documentosCompartidos->perPage(),
                'current_page' => $documentosCompartidos->currentPage(),
                'last_page' => $documentosCompartidos->lastPage(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'documento_id' => 'required|integer|exists:documentos,id',
            'compartido_con_usuario_id' => 'nullable|integer|exists:usuarios,id',
            'compartido_con_rol' => 'nullable|string|in:Asistente,Abogado,Administrador',
            'permisos' => 'required|string|in:lectura,escritura',
            'compartido_por' => 'required|integer|exists:usuarios,id',
        ]);

        // Validar que tenga al menos un destinatario
        if (empty($validated['compartido_con_usuario_id']) && empty($validated['compartido_con_rol'])) {
            return response()->json([
                'mensaje' => 'Debe especificar un usuario o un rol para compartir'
            ], 422);
        }

        // Validar que no tenga ambos destinatarios
        if (!empty($validated['compartido_con_usuario_id']) && !empty($validated['compartido_con_rol'])) {
            return response()->json([
                'mensaje' => 'Solo puede especificar un usuario o un rol, no ambos'
            ], 422);
        }

        // Verificar duplicados
        $existe = DocumentoCompartido::where('documento_id', $validated['documento_id'])
            ->where('compartido_con_usuario_id', $validated['compartido_con_usuario_id'])
            ->where('compartido_con_rol', $validated['compartido_con_rol'])
            ->exists();

        if ($existe) {
            return response()->json([
                'mensaje' => 'Este documento ya ha sido compartido con el mismo destinatario'
            ], 422);
        }

        $documentoCompartido = DocumentoCompartido::create($validated);

        return response()->json([
            'mensaje' => 'Documento compartido correctamente',
            'data' => $documentoCompartido->load(['documento', 'compartidoConUsuario', 'compartidoPorUsuario'])
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $documentoCompartido = DocumentoCompartido::with([
            'documento', 
            'compartidoConUsuario', 
            'compartidoPorUsuario'
        ])->find($id);

        if (!$documentoCompartido) {
            return response()->json(['mensaje' => 'Documento compartido no encontrado'], 404);
        }

        return response()->json(['data' => $documentoCompartido]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $documentoCompartido = DocumentoCompartido::find($id);

        if (!$documentoCompartido) {
            return response()->json(['mensaje' => 'Documento compartido no encontrado'], 404);
        }

        $validated = $request->validate([
            'permisos' => 'sometimes|required|string|in:lectura,escritura',
        ]);

        $documentoCompartido->update($validated);

        return response()->json([
            'mensaje' => 'Permisos actualizados correctamente',
            'data' => $documentoCompartido->load(['documento', 'compartidoConUsuario', 'compartidoPorUsuario'])
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $documentoCompartido = DocumentoCompartido::find($id);

        if (!$documentoCompartido) {
            return response()->json(['mensaje' => 'Documento compartido no encontrado'], 404);
        }

        $documentoCompartido->delete();

        return response()->json(['mensaje' => 'Compartición eliminada correctamente']);
    }
}