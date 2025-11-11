<?php

namespace App\Http\Controllers;

use App\Models\DocumentoCompartido;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DocumentoCompartidoController extends Controller
{
    /**
     * Lista paginada de documentos compartidos con filtros
     */
    public function index(Request $request): JsonResponse
    {
        $query = DocumentoCompartido::with([
            'documento',
            'compartidoConUsuario:id,nombre,email',
            'compartidoPorUsuario:id,nombre,email'
        ])->recientes();

        // Filtros
        if ($request->has('documento_id') && $request->documento_id) {
            $query->porDocumento($request->documento_id);
        }

        if ($request->has('usuario_id') && $request->usuario_id) {
            $query->porUsuario($request->usuario_id);
        }

        if ($request->has('compartido_por') && $request->compartido_por) {
            $query->porCompartidoPor($request->compartido_por);
        }

        if ($request->has('rol') && $request->rol) {
            $query->porRol($request->rol);
        }

        if ($request->has('permisos') && $request->permisos) {
            $query->porPermisos($request->permisos);
        }

        if ($request->has('escritura') && $request->escritura) {
            $query->conPermisoEscritura();
        }

        if ($request->has('lectura') && $request->lectura) {
            $query->conPermisoLectura();
        }

        if ($request->has('q') && $request->q) {
            $query->buscar($request->q);
        }

        $documentosCompartidos = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'mensaje' => 'Lista de documentos compartidos',
            'total'   => $documentosCompartidos->total(),
            'data'    => $documentosCompartidos->items(),
            'meta'    => [
                'current_page'  => $documentosCompartidos->currentPage(),
                'per_page'      => $documentosCompartidos->perPage(),
                'next_page_url' => $documentosCompartidos->nextPageUrl(),
                'prev_page_url' => $documentosCompartidos->previousPageUrl(),
                'last_page'     => $documentosCompartidos->lastPage(),
            ],
        ], 200);
    }

    /**
     * Todos los documentos compartidos con filtros opcionales
     */
    public function todos(Request $request): JsonResponse
    {
        $query = DocumentoCompartido::with([
            'documento',
            'compartidoConUsuario:id,nombre,email',
            'compartidoPorUsuario:id,nombre,email'
        ])->recientes();

        if ($request->has('documento_id') && $request->documento_id) {
            $query->porDocumento($request->documento_id);
        }

        $documentosCompartidos = $query->get();

        return response()->json([
            'mensaje' => 'Lista completa de documentos compartidos',
            'total'   => $documentosCompartidos->count(),
            'data'    => $documentosCompartidos,
        ], 200);
    }

    /**
     * Mostrar documento compartido específico
     */
    public function show($id): JsonResponse
    {
        $documentoCompartido = DocumentoCompartido::with([
            'documento',
            'compartidoConUsuario:id,nombre,email',
            'compartidoPorUsuario:id,nombre,email'
        ])->find($id);

        if (!$documentoCompartido) {
            return response()->json([
                'mensaje' => 'Documento compartido no encontrado'
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Documento compartido encontrado',
            'data' => $documentoCompartido,
            'puede_editar' => $documentoCompartido->puedeEditar()
        ], 200);
    }

    /**
     * Crear nuevo documento compartido
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'documento_id' => 'required|integer|exists:documentos,id',
            'compartido_con_usuario_id' => 'nullable|integer|exists:usuarios,id',
            'compartido_con_rol' => 'nullable|string|max:20',
            'permisos' => 'required|string|in:lectura,escritura',
            'compartido_por' => 'required|integer|exists:usuarios,id',
        ], [
            'documento_id.required' => 'El documento es obligatorio',
            'compartido_por.required' => 'El usuario que comparte es obligatorio',
            'permisos.required' => 'Los permisos son obligatorios',
            'permisos.in' => 'Los permisos deben ser lectura o escritura',
        ]);

        // Validación adicional para evitar duplicados
        $existe = DocumentoCompartido::where('documento_id', $validated['documento_id'])
            ->where('compartido_con_usuario_id', $validated['compartido_con_usuario_id'])
            ->where('compartido_con_rol', $validated['compartido_con_rol'])
            ->exists();

        if ($existe) {
            return response()->json([
                'mensaje' => 'Este documento ya ha sido compartido con el mismo destinatario'
            ], 409);
        }

        $documentoCompartido = DocumentoCompartido::create($validated);

        return response()->json([
            'mensaje' => 'Documento compartido correctamente',
            'data' => $documentoCompartido->load(['documento', 'compartidoConUsuario', 'compartidoPorUsuario'])
        ], 201);
    }

    /**
     * Actualizar documento compartido existente
     */
    public function update(Request $request, $id): JsonResponse
    {
        $documentoCompartido = DocumentoCompartido::find($id);

        if (!$documentoCompartido) {
            return response()->json([
                'mensaje' => 'Documento compartido no encontrado'
            ], 404);
        }

        $validated = $request->validate([
            'compartido_con_rol' => 'nullable|string|max:20',
            'permisos' => 'sometimes|required|string|in:lectura,escritura',
        ], [
            'permisos.in' => 'Los permisos deben ser lectura o escritura',
        ]);

        $documentoCompartido->update($validated);

        return response()->json([
            'mensaje' => 'Documento compartido actualizado correctamente',
            'data' => $documentoCompartido->load(['documento', 'compartidoConUsuario', 'compartidoPorUsuario'])
        ], 200);
    }

    /**
     * Eliminar documento compartido
     */
    public function destroy($id): JsonResponse
    {
        $documentoCompartido = DocumentoCompartido::find($id);

        if (!$documentoCompartido) {
            return response()->json([
                'mensaje' => 'Documento compartido no encontrado'
            ], 404);
        }

        $documentoCompartido->delete();

        return response()->json([
            'mensaje' => 'Documento compartido eliminado correctamente'
        ], 200);
    }

    /**
     * Documentos compartidos con un usuario específico
     */
    public function porUsuario($usuarioId): JsonResponse
    {
        $documentos = DocumentoCompartido::with([
            'documento',
            'compartidoPorUsuario:id,nombre,email'
        ])
        ->porUsuario($usuarioId)
        ->recientes()
        ->get();

        return response()->json([
            'mensaje' => 'Documentos compartidos con el usuario',
            'usuario_id' => $usuarioId,
            'total' => $documentos->count(),
            'data' => $documentos
        ], 200);
    }

    /**
     * Documentos compartidos por un usuario específico
     */
    public function porCompartidoPor($usuarioId): JsonResponse
    {
        $documentos = DocumentoCompartido::with([
            'documento',
            'compartidoConUsuario:id,nombre,email'
        ])
        ->porCompartidoPor($usuarioId)
        ->recientes()
        ->get();

        return response()->json([
            'mensaje' => 'Documentos compartidos por el usuario',
            'usuario_id' => $usuarioId,
            'total' => $documentos->count(),
            'data' => $documentos
        ], 200);
    }

    /**
     * Documentos compartidos con un rol específico
     */
    public function porRol($rol): JsonResponse
    {
        $documentos = DocumentoCompartido::with([
            'documento',
            'compartidoPorUsuario:id,nombre,email'
        ])
        ->porRol($rol)
        ->recientes()
        ->get();

        return response()->json([
            'mensaje' => 'Documentos compartidos con el rol',
            'rol' => $rol,
            'total' => $documentos->count(),
            'data' => $documentos
        ], 200);
    }

    /**
     * Cambiar permisos de un documento compartido
     */
    public function cambiarPermisos(Request $request, $id): JsonResponse
    {
        $documentoCompartido = DocumentoCompartido::find($id);

        if (!$documentoCompartido) {
            return response()->json([
                'mensaje' => 'Documento compartido no encontrado'
            ], 404);
        }

        $validated = $request->validate([
            'permisos' => 'required|string|in:lectura,escritura',
        ]);

        $documentoCompartido->cambiarPermisos($validated['permisos']);

        return response()->json([
            'mensaje' => 'Permisos actualizados correctamente',
            'data' => $documentoCompartido
        ], 200);
    }

    /**
     * Verificar si un usuario tiene acceso a un documento
     */
    public function verificarAcceso(Request $request, $documentoId): JsonResponse
    {
        $request->validate([
            'usuario_id' => 'required|integer|exists:usuarios,id',
            'rol_usuario' => 'required|string|max:20',
        ]);

        $acceso = DocumentoCompartido::where('documento_id', $documentoId)
            ->get()
            ->filter(function ($docCompartido) use ($request) {
                return $docCompartido->esAccesiblePorUsuario(
                    $request->usuario_id, 
                    $request->rol_usuario
                );
            })
            ->first();

        if ($acceso) {
            return response()->json([
                'mensaje' => 'Acceso permitido',
                'tiene_acceso' => true,
                'permisos' => $acceso->permisos,
                'data' => $acceso
            ], 200);
        }

        return response()->json([
            'mensaje' => 'Acceso denegado',
            'tiene_acceso' => false
        ], 403);
    }

    /**
     * Estadísticas de documentos compartidos
     */
    public function estadisticas(): JsonResponse
    {
        $total = DocumentoCompartido::count();
        $conPermisoEscritura = DocumentoCompartido::conPermisoEscritura()->count();
        $conPermisoLectura = DocumentoCompartido::conPermisoLectura()->count();
        
        $porRol = DocumentoCompartido::selectRaw('compartido_con_rol, COUNT(*) as total')
            ->whereNotNull('compartido_con_rol')
            ->groupBy('compartido_con_rol')
            ->get()
            ->pluck('total', 'compartido_con_rol');

        $porUsuario = DocumentoCompartido::selectRaw('compartido_con_usuario_id, COUNT(*) as total')
            ->whereNotNull('compartido_con_usuario_id')
            ->groupBy('compartido_con_usuario_id')
            ->get()
            ->pluck('total', 'compartido_con_usuario_id');

        return response()->json([
            'mensaje' => 'Estadísticas de documentos compartidos',
            'data' => [
                'total_compartidos' => $total,
                'con_permiso_escritura' => $conPermisoEscritura,
                'con_permiso_lectura' => $conPermisoLectura,
                'por_rol' => $porRol,
                'por_usuario' => $porUsuario,
            ]
        ], 200);
    }
}