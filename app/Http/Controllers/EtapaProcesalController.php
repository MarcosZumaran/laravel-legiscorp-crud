<?php

namespace App\Http\Controllers;

use App\Models\EtapaProcesal;
use Illuminate\Http\Request;

class EtapaProcesalController extends Controller
{
    public function index()
    {
        $etapas = EtapaProcesal::paginate(50);

        return response()->json([
            'mensaje' => 'Lista de etapas procesales paginada',
            'total'   => $etapas->total(),
            'data'    => $etapas->items(),
            'links'   => [
                'current_page'  => $etapas->currentPage(),
                'next_page_url' => $etapas->nextPageUrl(),
                'prev_page_url' => $etapas->previousPageUrl(),
                'last_page'     => $etapas->lastPage(),
            ],
        ], 200);
    }

    public function todos()
    {
        ini_set('memory_limit', '2G');

        $etapas = EtapaProcesal::all();

        return response()->json([
            'mensaje' => 'Lista completa de etapas procesales',
            'total'   => $etapas->count(),
            'data'    => $etapas,
        ], 200);
    }


    public function show($id)
    {
        $etapa = EtapaProcesal::find($id);

        if (!$etapa) {
            return response()->json(['message' => 'Etapa procesal no encontrada'], 404);
        }

        return response()->json($etapa, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_caso_id' => 'nullable|exists:tipos_casos,id',
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer',
        ]);

        $etapa = EtapaProcesal::create($validated);

        return response()->json($etapa, 201);
    }

    public function update(Request $request, $id)
    {
        $etapa = EtapaProcesal::find($id);

        if (!$etapa) {
            return response()->json(['message' => 'Etapa procesal no encontrada'], 404);
        }

        $validated = $request->validate([
            'tipo_caso_id' => 'nullable|exists:tipos_casos,id',
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer',
        ]);

        $etapa->update($validated);

        return response()->json($etapa, 200);
    }

    public function destroy($id)
    {
        $etapa = EtapaProcesal::find($id);

        if (!$etapa) {
            return response()->json(['message' => 'Etapa procesal no encontrada'], 404);
        }

        $etapa->delete();

        return response()->json(['message' => 'Etapa procesal eliminada correctamente'], 200);
    }
}
