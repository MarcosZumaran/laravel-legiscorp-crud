<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BitacoraController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Bitacora::with('usuario');
        
        // Filtros
        if ($request->has('usuario_id')) {
            $query->porUsuario($request->usuario_id);
        }
        
        if ($request->has('accion')) {
            $query->porAccion($request->accion);
        }
        
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->entreFechas($request->fecha_inicio, $request->fecha_fin);
        }
        
        if ($request->has('hoy')) {
            $query->hoy();
        }
        
        if ($request->has('recientes')) {
            $query->recientes($request->get('recientes', 24));
        }

        $bitacora = $query->orderBy('fecha', 'desc')->paginate(100);

        return response()->json([
            'data' => $bitacora->items(),
            'paginacion' => [
                'total' => $bitacora->total(),
                'per_page' => $bitacora->perPage(),
                'current_page' => $bitacora->currentPage(),
                'last_page' => $bitacora->lastPage(),
            ]
        ]);
    }

    public function show($id): JsonResponse
    {
        $registro = Bitacora::with('usuario')->find($id);

        if (!$registro) {
            return response()->json(['mensaje' => 'Registro de bitácora no encontrado'], 404);
        }

        return response()->json(['data' => $registro]);
    }

    // Nota: No hay store, update o destroy porque la bitácora es de solo lectura
    // y solo se crea mediante el método Bitacora::registrar()
}