<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    /**
     * Mostrar reportes paginados (50 por página)
     */
    public function index()
    {
        $reportes = Reporte::with('usuario')->paginate(50);

        return response()->json([
            'mensaje' => 'Lista de reportes paginada',
            'total'   => $reportes->total(),
            'data'    => $reportes->items(),
            'links'   => [
                'current_page'  => $reportes->currentPage(),
                'next_page_url' => $reportes->nextPageUrl(),
                'prev_page_url' => $reportes->previousPageUrl(),
                'last_page'     => $reportes->lastPage(),
            ],
        ], 200);
    }

    /**
     * Mostrar todos los reportes (sin límite)
     */
    public function todos()
    {
        ini_set('memory_limit', '2G');

        $reportes = Reporte::with('usuario')->get();

        return response()->json([
            'mensaje' => 'Lista completa de reportes',
            'total'   => $reportes->count(),
            'data'    => $reportes,
        ], 200);
    }

    /**
     * Mostrar un reporte por su ID.
     */
    public function show($id)
    {
        $reporte = Reporte::with('usuario')->find($id);

        if (!$reporte) {
            return response()->json(['message' => 'Reporte no encontrado'], 404);
        }

        return response()->json($reporte);
    }

    /**
     * Crear un nuevo reporte.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:150',
            'tipo_reporte' => 'required|string|in:General,Calendario,Documentos,Clientes,Casos',
            'descripcion' => 'nullable|string',
            'parametros' => 'nullable|string',
            'generado_por' => 'required|integer|exists:usuarios,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $reporte = Reporte::create([
            'titulo' => $request->titulo,
            'tipo_reporte' => $request->tipo_reporte,
            'descripcion' => $request->descripcion,
            'parametros' => $request->parametros,
            'fecha_generacion' => DB::raw('GETDATE()'),
            'generado_por' => $request->generado_por,
        ]);

        return response()->json([
            'message' => 'Reporte creado correctamente',
            'data' => $reporte
        ], 201);
    }

    /**
     * Actualizar un reporte existente.
     */
    public function update(Request $request, $id)
    {
        $reporte = Reporte::find($id);

        if (!$reporte) {
            return response()->json(['message' => 'Reporte no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'titulo' => 'sometimes|required|string|max:150',
            'tipo_reporte' => 'sometimes|required|string|in:General,Calendario,Documentos,Clientes,Casos',
            'descripcion' => 'nullable|string',
            'parametros' => 'nullable|string',
            'generado_por' => 'sometimes|required|integer|exists:usuarios,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $reporte->update($request->all());

        return response()->json([
            'message' => 'Reporte actualizado correctamente',
            'data' => $reporte
        ]);
    }

    /**
     * Eliminar un reporte.
     */
    public function destroy($id)
    {
        $reporte = Reporte::find($id);

        if (!$reporte) {
            return response()->json(['message' => 'Reporte no encontrado'], 404);
        }

        $reporte->delete();

        return response()->json(['message' => 'Reporte eliminado correctamente']);
    }
}
