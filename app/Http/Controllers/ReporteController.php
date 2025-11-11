<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReporteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Reporte::with('usuario');
        
        // Filtros
        if ($request->has('tipo')) {
            $query->porTipo($request->tipo);
        }
        
        if ($request->has('usuario_id')) {
            $query->porUsuario($request->usuario_id);
        }
        
        if ($request->has('recientes')) {
            $query->recientes($request->get('recientes', 30));
        }
        
        $reportes = $query->orderBy('fecha_generacion', 'desc')->paginate(50);

        return response()->json([
            'data' => $reportes->items(),
            'paginacion' => [
                'total' => $reportes->total(),
                'per_page' => $reportes->perPage(),
                'current_page' => $reportes->currentPage(),
                'last_page' => $reportes->lastPage(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:150',
            'tipo_reporte' => 'required|string|in:General,Calendario,Documentos,Clientes,Casos',
            'descripcion' => 'nullable|string',
            'parametros' => 'nullable|array',
            'generado_por' => 'required|integer|exists:usuarios,id',
        ]);

        $reporte = Reporte::create($validated);

        return response()->json([
            'mensaje' => 'Reporte generado correctamente',
            'data' => $reporte->load('usuario')
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $reporte = Reporte::with('usuario')->find($id);

        if (!$reporte) {
            return response()->json(['mensaje' => 'Reporte no encontrado'], 404);
        }

        return response()->json(['data' => $reporte]);
    }

    public function destroy($id): JsonResponse
    {
        $reporte = Reporte::find($id);

        if (!$reporte) {
            return response()->json(['mensaje' => 'Reporte no encontrado'], 404);
        }

        $reporte->delete();

        return response()->json(['mensaje' => 'Reporte eliminado correctamente']);
    }
}