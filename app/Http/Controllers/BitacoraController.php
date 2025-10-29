<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use Illuminate\Http\Request;

class BitacoraController extends Controller
{
    /**
     * Muestra lista paginada de entradas en la bitácora (50 por página)
     */
    public function index()
    {
        $bitacoras = Bitacora::paginate(50);

        return response()->json([
            'mensaje' => 'Lista de registros de la bitácora (paginada)',
            'total'   => $bitacoras->total(),
            'data'    => $bitacoras->items(),
            'links'   => [
                'current_page'  => $bitacoras->currentPage(),
                'next_page_url' => $bitacoras->nextPageUrl(),
                'prev_page_url' => $bitacoras->previousPageUrl(),
                'last_page'     => $bitacoras->lastPage(),
            ],
        ], 200);
    }

    /**
     * Muestra todas las entradas de la bitácora (sin paginación)
     */
    public function todos()
    {
        ini_set('memory_limit', '2G');

        $bitacoras = Bitacora::all();

        return response()->json([
            'mensaje' => 'Lista completa de registros de la bitácora',
            'total'   => $bitacoras->count(),
            'data'    => $bitacoras,
        ], 200);
    }

    /**
     * Registra una nueva acción en la bitácora.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'usuario_id' => 'nullable|integer',
            'accion' => 'nullable|string|max:255',
            'ip' => 'nullable|string|max:50',
        ]);

        $bitacora = Bitacora::create([
            ...$validated,
            'fecha' => now(),
        ]);

        return response()->json([
            'message' => 'Acción registrada en la bitácora correctamente',
            'data' => $bitacora
        ], 201);
    }

    /**
     * Muestra una entrada específica.
     */
    public function show($id)
    {
        $bitacora = Bitacora::findOrFail($id);
        return response()->json($bitacora);
    }

    /**
     * Actualiza una entrada específica.
     */
    public function update(Request $request, $id)
    {
        $bitacora = Bitacora::findOrFail($id);

        $validated = $request->validate([
            'usuario_id' => 'nullable|integer',
            'accion' => 'nullable|string|max:255',
            'ip' => 'nullable|string|max:50',
        ]);

        $bitacora->update($validated);

        return response()->json([
            'message' => 'Entrada actualizada correctamente',
            'data' => $bitacora
        ]);
    }

    /**
     * Elimina una entrada de la bitácora.
     */
    public function destroy($id)
    {
        $bitacora = Bitacora::findOrFail($id);
        $bitacora->delete();

        return response()->json(['message' => 'Entrada eliminada correctamente']);
    }
}
