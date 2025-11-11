<?php

namespace App\Http\Controllers;

use App\Models\ComentarioCaso;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ComentarioCasoController extends Controller
{
    /**
     * Lista paginada de comentarios con filtros
     */
    public function index(Request $request): JsonResponse
    {
        $query = ComentarioCaso::with(['usuario', 'caso'])->ordenarPorFecha();

        // Filtros
        if ($request->has('caso_id') && $request->caso_id) {
            $query->porCaso($request->caso_id);
        }

        if ($request->has('usuario_id') && $request->usuario_id) {
            $query->porUsuario($request->usuario_id);
        }

        if ($request->has('recientes') && $request->recientes) {
            $query->recientes($request->get('dias', 7));
        }

        $comentarios = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'mensaje' => 'Lista de comentarios paginada',
            'total'   => $comentarios->total(),
            'data'    => $comentarios->items(),
            'meta'    => [
                'current_page'  => $comentarios->currentPage(),
                'per_page'      => $comentarios->perPage(),
                'next_page_url' => $comentarios->nextPageUrl(),
                'prev_page_url' => $comentarios->previousPageUrl(),
                'last_page'     => $comentarios->lastPage(),
            ],
        ], 200);
    }

    /**
     * Todos los comentarios con filtros opcionales
     */
    public function todos(Request $request): JsonResponse
    {
        $query = ComentarioCaso::with(['usuario', 'caso'])->ordenarPorFecha();

        if ($request->has('caso_id') && $request->caso_id) {
            $query->porCaso($request->caso_id);
        }

        $comentarios = $query->get();

        return response()->json([
            'mensaje' => 'Lista completa de comentarios',
            'total'   => $comentarios->count(),
            'data'    => $comentarios,
        ], 200);
    }

    /**
     * Mostrar comentario específico con relaciones
     */
    public function show($id): JsonResponse
    {
        $comentario = ComentarioCaso::with(['usuario', 'caso'])->find($id);

        if (!$comentario) {
            return response()->json([
                'mensaje' => 'Comentario no encontrado'
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Comentario encontrado',
            'data' => $comentario
        ], 200);
    }

    /**
     * Crear nuevo comentario (encriptación automática en el modelo)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'caso_id'    => 'required|integer|exists:casos,id',
            'usuario_id' => 'required|integer|exists:usuarios,id',
            'comentario' => 'required|string|min:1|max:5000',
        ], [
            'caso_id.required' => 'El caso es obligatorio',
            'usuario_id.required' => 'El usuario es obligatorio',
            'comentario.required' => 'El comentario es obligatorio',
            'comentario.min' => 'El comentario no puede estar vacío',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $validator->errors()
            ], 422);
        }

        // El comentario se encripta automáticamente en el modelo
        $comentario = ComentarioCaso::create($validator->validated());

        return response()->json([
            'mensaje' => 'Comentario creado correctamente',
            'data' => $comentario->load(['usuario', 'caso'])
        ], 201);
    }

    /**
     * Actualizar comentario existente (encriptación automática en el modelo)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $comentario = ComentarioCaso::find($id);

        if (!$comentario) {
            return response()->json([
                'mensaje' => 'Comentario no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'comentario' => 'required|string|min:1|max:5000',
        ], [
            'comentario.required' => 'El comentario es obligatorio',
            'comentario.min' => 'El comentario no puede estar vacío',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $validator->errors()
            ], 422);
        }

        // El comentario se encripta automáticamente en el modelo
        $comentario->update($validator->validated());

        return response()->json([
            'mensaje' => 'Comentario actualizado correctamente',
            'data' => $comentario->load(['usuario', 'caso'])
        ], 200);
    }

    /**
     * Eliminar comentario
     */
    public function destroy($id): JsonResponse
    {
        $comentario = ComentarioCaso::find($id);

        if (!$comentario) {
            return response()->json([
                'mensaje' => 'Comentario no encontrado'
            ], 404);
        }

        $comentario->delete();

        return response()->json([
            'mensaje' => 'Comentario eliminado correctamente'
        ], 200);
    }

    /**
     * Comentarios por caso específico
     */
    public function porCaso($casoId): JsonResponse
    {
        $comentarios = ComentarioCaso::obtenerPorCaso($casoId);

        return response()->json([
            'mensaje' => 'Comentarios del caso',
            'caso_id' => $casoId,
            'total' => $comentarios->count(),
            'data' => $comentarios
        ], 200);
    }

    /**
     * Comentarios por usuario específico
     */
    public function porUsuario($usuarioId): JsonResponse
    {
        $comentarios = ComentarioCaso::with(['usuario', 'caso'])
            ->porUsuario($usuarioId)
            ->ordenarPorFecha()
            ->get();

        return response()->json([
            'mensaje' => 'Comentarios del usuario',
            'usuario_id' => $usuarioId,
            'total' => $comentarios->count(),
            'data' => $comentarios
        ], 200);
    }

    /**
     * Búsqueda de comentarios (limitada por encriptación)
     */
    public function buscar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'caso_id' => 'nullable|integer|exists:casos,id',
            'usuario_id' => 'nullable|integer|exists:usuarios,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $validator->errors()
            ], 422);
        }

        $query = ComentarioCaso::with(['usuario', 'caso']);

        if ($request->has('caso_id') && $request->caso_id) {
            $query->porCaso($request->caso_id);
        }

        if ($request->has('usuario_id') && $request->usuario_id) {
            $query->porUsuario($request->usuario_id);
        }

        $comentarios = $query->ordenarPorFecha()->get();

        return response()->json([
            'mensaje' => 'Resultados de búsqueda',
            'filtros' => $request->all(),
            'total' => $comentarios->count(),
            'data' => $comentarios
        ], 200);
    }

    /**
     * Estadísticas de comentarios
     */
    public function estadisticas(): JsonResponse
    {
        $total = ComentarioCaso::count();
        $hoy = ComentarioCaso::whereDate('fecha', today())->count();
        $ultimaSemana = ComentarioCaso::recientes(7)->count();

        $usuarioMasActivo = ComentarioCaso::selectRaw('usuario_id, COUNT(*) as total')
            ->groupBy('usuario_id')
            ->orderBy('total', 'desc')
            ->with('usuario')
            ->first();

        return response()->json([
            'mensaje' => 'Estadísticas de comentarios',
            'data' => [
                'total_comentarios' => $total,
                'comentarios_hoy' => $hoy,
                'comentarios_ultima_semana' => $ultimaSemana,
                'usuario_mas_activo' => $usuarioMasActivo
            ]
        ], 200);
    }
}