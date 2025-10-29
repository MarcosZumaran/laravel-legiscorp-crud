<?php

namespace App\Http\Controllers;

use App\Models\Caso;
use Illuminate\Http\Request;

class CasoController extends Controller
{
    public function index()
    {
        $casos = Caso::with(['cliente', 'abogado', 'materia', 'tipoCaso'])->paginate(50);

        return response()->json([
            'mensaje' => 'Lista de casos paginada',
            'total'   => $casos->total(),
            'data'    => $casos->items(),
            'links'   => [
                'current_page'  => $casos->currentPage(),
                'next_page_url' => $casos->nextPageUrl(),
                'prev_page_url' => $casos->previousPageUrl(),
                'last_page'     => $casos->lastPage(),
            ],
        ], 200);
    }

    public function todos()
    {
        ini_set('memory_limit', '2G');

        $casos = Caso::with(['cliente', 'abogado', 'materia', 'tipoCaso'])->get();

        return response()->json([
            'mensaje' => 'Lista completa de casos',
            'total'   => $casos->count(),
            'data'    => $casos,
        ], 200);
    }


    public function show($id)
    {
        $caso = Caso::with(['cliente', 'abogado', 'materia', 'tipoCaso'])->findOrFail($id);
        return response()->json($caso);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo_caso' => 'required|string|max:50|unique:casos',
            'expediente_completo' => 'required|string|max:30|unique:casos',
            'secuencia' => 'required|string|max:5',
            'anio' => 'required|string|max:4',
            'indicador_fuero' => 'required|string|max:1',
            'codigo_organo' => 'required|string|max:4',
            'tipo_organo' => 'required|string|max:2',
            'especialidad' => 'required|string|max:2',
            'distrito' => 'required|string|max:2',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'materia_id' => 'required|exists:materias_casos,id',
            'tipo_caso_id' => 'nullable|exists:tipos_casos,id',
            'estado' => 'nullable|in:Abierto,En Proceso,Cerrado',
            'fecha_inicio' => 'nullable|date',
            'fecha_cierre' => 'nullable|date',
            'cliente_id' => 'required|exists:clientes,id',
            'abogado_id' => 'required|exists:usuarios,id',
            'contraparte' => 'nullable|string|max:255',
        ]);

        $caso = Caso::create($validated);
        return response()->json($caso, 201);
    }

    public function update(Request $request, $id)
    {
        $caso = Caso::findOrFail($id);

        $validated = $request->validate([
            'codigo_caso' => 'sometimes|string|max:50|unique:casos,codigo_caso,' . $id,
            'expediente_completo' => 'sometimes|string|max:30|unique:casos,expediente_completo,' . $id,
            'secuencia' => 'sometimes|string|max:5',
            'anio' => 'sometimes|string|max:4',
            'indicador_fuero' => 'sometimes|string|max:1',
            'codigo_organo' => 'sometimes|string|max:4',
            'tipo_organo' => 'sometimes|string|max:2',
            'especialidad' => 'sometimes|string|max:2',
            'distrito' => 'sometimes|string|max:2',
            'titulo' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'materia_id' => 'sometimes|exists:materias_casos,id',
            'tipo_caso_id' => 'nullable|exists:tipos_casos,id',
            'estado' => 'nullable|in:Abierto,En Proceso,Cerrado',
            'fecha_inicio' => 'nullable|date',
            'fecha_cierre' => 'nullable|date',
            'cliente_id' => 'sometimes|exists:clientes,id',
            'abogado_id' => 'sometimes|exists:usuarios,id',
            'contraparte' => 'nullable|string|max:255',
        ]);

        $caso->update($validated);
        return response()->json($caso);
    }

    public function destroy($id)
    {
        $caso = Caso::findOrFail($id);
        $caso->delete();
        return response()->json(['message' => 'Caso eliminado correctamente']);
    }
}
