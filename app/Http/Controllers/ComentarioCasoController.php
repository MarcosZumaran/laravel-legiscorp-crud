<?php

namespace App\Http\Controllers;

use App\Models\ComentarioCaso;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ComentarioCasoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ComentarioCaso::with(['usuario', 'caso']);
        
        // Filtros
        if ($request->has('caso_id')) {
            $query->porCaso($request->caso_id);
        }
        
        if ($request->has('usuario_id')) {
            $query->porUsuario($request->usuario_id);
        }
        
        if ($request->has('recientes')) {
            $query->recientes($request->get('recientes', 7));
        }

        $comentarios = $query->orderBy('fecha', 'desc')->paginate(50);

        return response()->json([
            'data' => $comentarios->items(),
            'paginacion' => [
                'total' => $comentarios->total(),
                'per_page' => $comentarios->perPage(),
                'current_page' => $comentarios->currentPage(),
                'last_page' => $comentarios->lastPage(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'caso_id' => 'required|integer|exists:casos,id',
            'usuario_id' => 'required|integer|exists:usuarios,id',
            'comentario' => 'required|string',
        ]);

        $comentario = ComentarioCaso::create($validated);

        return response()->json([
            'mensaje' => 'Comentario agregado correctamente',
            'data' => $comentario->load(['usuario', 'caso'])
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $comentario = ComentarioCaso::with(['usuario', 'caso'])->find($id);

        if (!$comentario) {
            return response()->json(['mensaje' => 'Comentario no encontrado'], 404);
        }

        return response()->json(['data' => $comentario]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $comentario = ComentarioCaso::find($id);

        if (!$comentario) {
            return response()->json(['mensaje' => 'Comentario no encontrado'], 404);
        }

        $validated = $request->validate([
            'comentario' => 'required|string',
        ]);

        $comentario->update($validated);

        return response()->json([
            'mensaje' => 'Comentario actualizado correctamente',
            'data' => $comentario->load(['usuario', 'caso'])
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $comentario = ComentarioCaso::find($id);

        if (!$comentario) {
            return response()->json(['mensaje' => 'Comentario no encontrado'], 404);
        }

        $comentario->delete();

        return response()->json(['mensaje' => 'Comentario eliminado correctamente']);
    }
}