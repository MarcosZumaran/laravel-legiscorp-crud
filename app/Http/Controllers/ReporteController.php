<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ReporteController extends Controller
{
    /**
     * Mostrar reportes paginados con filtros
     */
    public function index(Request $request): JsonResponse
    {
        $query = Reporte::with('usuario')->orderBy('fecha_generacion', 'desc');

        // Filtros
        if ($request->has('tipo_reporte') && $request->tipo_reporte) {
            $query->porTipo($request->tipo_reporte);
        }

        if ($request->has('usuario_id') && $request->usuario_id) {
            $query->porUsuario($request->usuario_id);
        }

        if ($request->has('recientes') && $request->recientes) {
            $query->recientes($request->get('dias', 7));
        }

        $reportes = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'mensaje' => 'Lista de reportes paginada',
            'total'   => $reportes->total(),
            'data'    => $reportes->items(),
            'meta'    => [
                'current_page'  => $reportes->currentPage(),
                'per_page'      => $reportes->perPage(),
                'next_page_url' => $reportes->nextPageUrl(),
                'prev_page_url' => $reportes->previousPageUrl(),
                'last_page'     => $reportes->lastPage(),
            ],
        ], 200);
    }

    /**
     * Mostrar todos los reportes (sin límite) con filtros opcionales
     */
    public function todos(Request $request): JsonResponse
    {
        // ini_set('memory_limit', '2G'); // Solo si es realmente necesario

        $query = Reporte::with('usuario')->orderBy('fecha_generacion', 'desc');

        // Filtros opcionales
        if ($request->has('tipo_reporte') && $request->tipo_reporte) {
            $query->porTipo($request->tipo_reporte);
        }

        if ($request->has('usuario_id') && $request->usuario_id) {
            $query->porUsuario($request->usuario_id);
        }

        $reportes = $query->get();

        return response()->json([
            'mensaje' => 'Lista completa de reportes',
            'total'   => $reportes->count(),
            'data'    => $reportes,
        ], 200);
    }

    /**
     * Mostrar un reporte por su ID con parámetros decodificados
     */
    public function show($id): JsonResponse
    {
        $reporte = Reporte::with('usuario')->find($id);

        if (!$reporte) {
            return response()->json([
                'mensaje' => 'Reporte no encontrado'
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Reporte encontrado',
            'data' => $reporte,
            'parametros_decodificados' => $reporte->parametros_decodificados
        ], 200);
    }

    /**
     * Crear un nuevo reporte con parámetros encriptados automáticamente
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:150',
            'tipo_reporte' => 'required|string|in:General,Calendario,Documentos,Clientes,Casos',
            'descripcion' => 'nullable|string',
            'parametros' => 'nullable|array', // ✅ Cambiado a array para encriptación automática
            'generado_por' => 'required|integer|exists:usuarios,id',
        ], [
            'tipo_reporte.in' => 'El tipo de reporte debe ser: General, Calendario, Documentos, Clientes o Casos',
            'generado_por.exists' => 'El usuario especificado no existe'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        
        // ✅ Convertir parámetros a JSON para encriptación automática
        if (isset($data['parametros']) && is_array($data['parametros'])) {
            $data['parametros'] = json_encode($data['parametros']);
        }

        // ✅ No necesitas fecha_generacion - la BD usa GETDATE() por defecto
        $reporte = Reporte::create($data);

        return response()->json([
            'mensaje' => 'Reporte creado correctamente',
            'data' => $reporte->load('usuario'),
            'parametros_decodificados' => $reporte->parametros_decodificados
        ], 201);
    }

    /**
     * Actualizar un reporte existente
     */
    public function update(Request $request, $id): JsonResponse
    {
        $reporte = Reporte::find($id);

        if (!$reporte) {
            return response()->json([
                'mensaje' => 'Reporte no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'titulo' => 'sometimes|required|string|max:150',
            'tipo_reporte' => 'sometimes|required|string|in:General,Calendario,Documentos,Clientes,Casos',
            'descripcion' => 'nullable|string',
            'parametros' => 'nullable|array', // ✅ Cambiado a array
            'generado_por' => 'sometimes|required|integer|exists:usuarios,id',
        ], [
            'tipo_reporte.in' => 'El tipo de reporte debe ser: General, Calendario, Documentos, Clientes o Casos',
            'generado_por.exists' => 'El usuario especificado no existe'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        
        // ✅ Convertir parámetros a JSON para encriptación automática
        if (isset($data['parametros']) && is_array($data['parametros'])) {
            $data['parametros'] = json_encode($data['parametros']);
        }

        $reporte->update($data);

        return response()->json([
            'mensaje' => 'Reporte actualizado correctamente',
            'data' => $reporte->load('usuario'),
            'parametros_decodificados' => $reporte->parametros_decodificados
        ], 200);
    }

    /**
     * Eliminar un reporte
     */
    public function destroy($id): JsonResponse
    {
        $reporte = Reporte::find($id);

        if (!$reporte) {
            return response()->json([
                'mensaje' => 'Reporte no encontrado'
            ], 404);
        }

        $reporte->delete();

        return response()->json([
            'mensaje' => 'Reporte eliminado correctamente'
        ], 200);
    }

    /**
     * 🆕 Búsqueda de reportes por término
     */
    public function buscar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2',
            'tipo_reporte' => 'nullable|string|in:General,Calendario,Documentos,Clientes,Casos',
            'usuario_id' => 'nullable|integer|exists:usuarios,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $validator->errors()
            ], 422);
        }

        $query = Reporte::with('usuario')
            ->where('titulo', 'LIKE', "%{$request->q}%")
            ->orWhere('descripcion', 'LIKE', "%{$request->q}%");

        // Filtros adicionales
        if ($request->has('tipo_reporte') && $request->tipo_reporte) {
            $query->porTipo($request->tipo_reporte);
        }

        if ($request->has('usuario_id') && $request->usuario_id) {
            $query->porUsuario($request->usuario_id);
        }

        $reportes = $query->orderBy('fecha_generacion', 'desc')->get();

        return response()->json([
            'mensaje' => 'Resultados de búsqueda',
            'termino' => $request->q,
            'total' => $reportes->count(),
            'data' => $reportes
        ], 200);
    }

    /**
     * 🆕 Reportes por usuario específico
     */
    public function porUsuario($usuarioId): JsonResponse
    {
        $reportes = Reporte::with('usuario')
            ->porUsuario($usuarioId)
            ->orderBy('fecha_generacion', 'desc')
            ->get();

        return response()->json([
            'mensaje' => 'Reportes del usuario',
            'usuario_id' => $usuarioId,
            'total' => $reportes->count(),
            'data' => $reportes
        ], 200);
    }

    /**
     * 🆕 Estadísticas de reportes
     */
    public function estadisticas(): JsonResponse
    {
        $total = Reporte::count();
        
        $porTipo = Reporte::selectRaw('tipo_reporte, COUNT(*) as total')
            ->groupBy('tipo_reporte')
            ->get()
            ->pluck('total', 'tipo_reporte');

        $recientes = Reporte::recientes(7)->count();
        $hoy = Reporte::whereDate('fecha_generacion', today())->count();

        return response()->json([
            'mensaje' => 'Estadísticas de reportes',
            'data' => [
                'total_reportes' => $total,
                'reportes_por_tipo' => $porTipo,
                'reportes_ultimos_7_dias' => $recientes,
                'reportes_hoy' => $hoy
            ]
        ], 200);
    }

    /**
     * 🆕 Tipos de reporte disponibles
     */
    public function tipos(): JsonResponse
    {
        return response()->json([
            'mensaje' => 'Tipos de reporte disponibles',
            'data' => Reporte::TIPOS_REPORTE
        ], 200);
    }
}