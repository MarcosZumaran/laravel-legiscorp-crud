<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    /**
     * Lista paginada de clientes con filtros
     */
    public function index(Request $request): JsonResponse
    {
        $query = Cliente::orderBy('creado_en', 'desc');

        // Filtros
        if ($request->has('tipo_persona') && $request->tipo_persona) {
            $query->porTipoPersona($request->tipo_persona);
        }

        if ($request->has('estado') && $request->estado) {
            $query->porEstado($request->estado);
        }

        if ($request->has('tipo_documento') && $request->tipo_documento) {
            $query->porTipoDocumento($request->tipo_documento);
        }

        if ($request->has('q') && $request->q) {
            $query->buscar($request->q);
        }

        if ($request->has('activos') && $request->activos) {
            $query->activos();
        }

        $clientes = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'mensaje' => 'Lista de clientes paginada',
            'total'   => $clientes->total(),
            'data'    => $clientes->items(),
            'meta'    => [
                'current_page'  => $clientes->currentPage(),
                'per_page'      => $clientes->perPage(),
                'next_page_url' => $clientes->nextPageUrl(),
                'prev_page_url' => $clientes->previousPageUrl(),
                'last_page'     => $clientes->lastPage(),
            ],
        ], 200);
    }

    /**
     * Todos los clientes con filtros opcionales
     */
    public function todos(Request $request): JsonResponse
    {
        $query = Cliente::orderBy('creado_en', 'desc');

        if ($request->has('tipo_persona') && $request->tipo_persona) {
            $query->porTipoPersona($request->tipo_persona);
        }

        if ($request->has('estado') && $request->estado) {
            $query->porEstado($request->estado);
        }

        $clientes = $query->get();

        return response()->json([
            'mensaje' => 'Lista completa de clientes',
            'total'   => $clientes->count(),
            'data'    => $clientes,
        ], 200);
    }

    /**
     * Mostrar cliente específico
     */
    public function show($id): JsonResponse
    {
        $cliente = Cliente::withCount('documentos')->find($id);

        if (!$cliente) {
            return response()->json([
                'mensaje' => 'Cliente no encontrado'
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Cliente encontrado',
            'data' => $cliente,
            'puede_eliminar' => $cliente->puedeEliminar()
        ], 200);
    }

    /**
     * Crear nuevo cliente (encriptación automática en el modelo)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tipo_persona'        => 'required|string|in:Jurídica,Natural',
            'tipo_documento'      => 'required|string|in:Pasaporte,RUC,DNI',
            'numero_documento'    => 'required|string|max:20|unique:clientes,numero_documento',
            'nombres'             => 'required_if:tipo_persona,Natural|nullable|string|max:100',
            'apellidos'           => 'required_if:tipo_persona,Natural|nullable|string|max:100',
            'razon_social'        => 'required_if:tipo_persona,Jurídica|nullable|string|max:150',
            'representante_legal' => 'nullable|string|max:150',
            'telefono'            => 'nullable|string|max:20',
            'correo'              => 'nullable|email|max:150',
            'direccion'           => 'nullable|string|max:255',
            'estado'              => 'nullable|string|in:Activo,Inactivo',
        ], [
            'tipo_persona.required' => 'El tipo de persona es obligatorio',
            'tipo_documento.required' => 'El tipo de documento es obligatorio',
            'numero_documento.required' => 'El número de documento es obligatorio',
            'numero_documento.unique' => 'El número de documento ya está registrado',
            'nombres.required_if' => 'Los nombres son obligatorios para persona natural',
            'apellidos.required_if' => 'Los apellidos son obligatorios para persona natural',
            'razon_social.required_if' => 'La razón social es obligatoria para persona jurídica',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $validator->errors()
            ], 422);
        }

        // La encriptación de correo y teléfono es automática en el modelo
        $cliente = Cliente::create($validator->validated());

        return response()->json([
            'mensaje' => 'Cliente creado correctamente',
            'data' => $cliente
        ], 201);
    }

    /**
     * Actualizar cliente existente (encriptación automática en el modelo)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json([
                'mensaje' => 'Cliente no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'tipo_persona'        => 'sometimes|required|string|in:Jurídica,Natural',
            'tipo_documento'      => 'sometimes|required|string|in:Pasaporte,RUC,DNI',
            'numero_documento'    => 'sometimes|required|string|max:20|unique:clientes,numero_documento,' . $cliente->id,
            'nombres'             => 'required_if:tipo_persona,Natural|nullable|string|max:100',
            'apellidos'           => 'required_if:tipo_persona,Natural|nullable|string|max:100',
            'razon_social'        => 'required_if:tipo_persona,Jurídica|nullable|string|max:150',
            'representante_legal' => 'nullable|string|max:150',
            'telefono'            => 'nullable|string|max:20',
            'correo'              => 'nullable|email|max:150',
            'direccion'           => 'nullable|string|max:255',
            'estado'              => 'nullable|string|in:Activo,Inactivo',
        ], [
            'numero_documento.unique' => 'El número de documento ya está registrado',
            'nombres.required_if' => 'Los nombres son obligatorios para persona natural',
            'apellidos.required_if' => 'Los apellidos son obligatorios para persona natural',
            'razon_social.required_if' => 'La razón social es obligatoria para persona jurídica',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $validator->errors()
            ], 422);
        }

        // La encriptación de correo y teléfono es automática en el modelo
        $cliente->update($validator->validated());

        return response()->json([
            'mensaje' => 'Cliente actualizado correctamente',
            'data' => $cliente
        ], 200);
    }

    /**
     * Eliminar cliente
     */
    public function destroy($id): JsonResponse
    {
        $cliente = Cliente::withCount('documentos')->find($id);

        if (!$cliente) {
            return response()->json([
                'mensaje' => 'Cliente no encontrado'
            ], 404);
        }

        if (!$cliente->puedeEliminar()) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el cliente porque tiene documentos asociados',
                'total_documentos' => $cliente->documentos_count
            ], 422);
        }

        $cliente->delete();

        return response()->json([
            'mensaje' => 'Cliente eliminado correctamente'
        ], 200);
    }

    /**
     * Búsqueda de clientes
     */
    public function buscar(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2'
        ]);

        $clientes = Cliente::buscar($request->q)
                          ->orderBy('creado_en', 'desc')
                          ->get();

        return response()->json([
            'mensaje' => 'Resultados de búsqueda',
            'termino' => $request->q,
            'total' => $clientes->count(),
            'data' => $clientes
        ], 200);
    }

    /**
     * Buscar cliente por documento
     */
    public function porDocumento($numeroDocumento): JsonResponse
    {
        $cliente = Cliente::buscarPorDocumento($numeroDocumento);

        if (!$cliente) {
            return response()->json([
                'mensaje' => 'Cliente no encontrado'
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Cliente encontrado',
            'data' => $cliente
        ], 200);
    }

    /**
     * Activar cliente
     */
    public function activar($id): JsonResponse
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json([
                'mensaje' => 'Cliente no encontrado'
            ], 404);
        }

        $cliente->activar();

        return response()->json([
            'mensaje' => 'Cliente activado correctamente',
            'data' => $cliente
        ], 200);
    }

    /**
     * Desactivar cliente
     */
    public function desactivar($id): JsonResponse
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json([
                'mensaje' => 'Cliente no encontrado'
            ], 404);
        }

        $cliente->desactivar();

        return response()->json([
            'mensaje' => 'Cliente desactivado correctamente',
            'data' => $cliente
        ], 200);
    }

    /**
     * Estadísticas de clientes
     */
    public function estadisticas(): JsonResponse
    {
        $total = Cliente::count();
        $activos = Cliente::activos()->count();
        $inactivos = $total - $activos;
        
        $porTipoPersona = Cliente::selectRaw('tipo_persona, COUNT(*) as total')
            ->groupBy('tipo_persona')
            ->get()
            ->pluck('total', 'tipo_persona');

        $porTipoDocumento = Cliente::selectRaw('tipo_documento, COUNT(*) as total')
            ->groupBy('tipo_documento')
            ->get()
            ->pluck('total', 'tipo_documento');

        $recientes = Cliente::recientes(7)->count();

        return response()->json([
            'mensaje' => 'Estadísticas de clientes',
            'data' => [
                'total_clientes' => $total,
                'clientes_activos' => $activos,
                'clientes_inactivos' => $inactivos,
                'clientes_por_tipo_persona' => $porTipoPersona,
                'clientes_por_tipo_documento' => $porTipoDocumento,
                'clientes_recientes_7_dias' => $recientes
            ]
        ], 200);
    }
}