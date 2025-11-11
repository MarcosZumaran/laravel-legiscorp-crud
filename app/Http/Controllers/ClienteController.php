<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClienteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Cliente::query();
        
        // Filtros
        if ($request->has('tipo_persona')) {
            $query->porTipoPersona($request->tipo_persona);
        }
        
        if ($request->has('estado')) {
            $query->porEstado($request->estado);
        }
        
        if ($request->has('tipo_documento')) {
            $query->porTipoDocumento($request->tipo_documento);
        }
        
        if ($request->has('q')) {
            $query->buscar($request->q);
        }
        
        if ($request->has('activos')) {
            $query->activos();
        }

        $clientes = $query->orderBy('creado_en', 'desc')->paginate(50);

        return response()->json([
            'data' => $clientes->items(),
            'paginacion' => [
                'total' => $clientes->total(),
                'per_page' => $clientes->perPage(),
                'current_page' => $clientes->currentPage(),
                'last_page' => $clientes->lastPage(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tipo_persona' => 'required|string|in:Jurídica,Natural',
            'tipo_documento' => 'required|string|in:Pasaporte,RUC,DNI',
            'numero_documento' => 'required|string|max:20',
            'nombres' => 'nullable|string|max:100',
            'apellidos' => 'nullable|string|max:100',
            'razon_social' => 'nullable|string|max:150',
            'representante_legal' => 'nullable|string|max:150',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:150',
            'direccion' => 'nullable|string|max:255',
            'estado' => 'sometimes|string|in:Activo,Inactivo',
        ]);

        // Validaciones específicas por tipo de persona
        if ($validated['tipo_persona'] === 'Natural') {
            if (empty($validated['nombres']) || empty($validated['apellidos'])) {
                return response()->json([
                    'mensaje' => 'Para persona natural, nombres y apellidos son obligatorios'
                ], 422);
            }
        } else {
            if (empty($validated['razon_social'])) {
                return response()->json([
                    'mensaje' => 'Para persona jurídica, la razón social es obligatoria'
                ], 422);
            }
        }


        $cliente = Cliente::create($validated);

        return response()->json([
            'mensaje' => 'Cliente creado correctamente',
            'data' => $cliente
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['mensaje' => 'Cliente no encontrado'], 404);
        }

        return response()->json(['data' => $cliente]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['mensaje' => 'Cliente no encontrado'], 404);
        }

        $validated = $request->validate([
            'tipo_persona' => 'sometimes|required|string|in:Jurídica,Natural',
            'tipo_documento' => 'sometimes|required|string|in:Pasaporte,RUC,DNI',
            'numero_documento' => 'sometimes|required|string|max:20',
            'nombres' => 'nullable|string|max:100',
            'apellidos' => 'nullable|string|max:100',
            'razon_social' => 'nullable|string|max:150',
            'representante_legal' => 'nullable|string|max:150',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:150',
            'direccion' => 'nullable|string|max:255',
            'estado' => 'sometimes|string|in:Activo,Inactivo',
        ]);

        // Validaciones por tipo de persona si se está cambiando
        if (isset($validated['tipo_persona'])) {
            if ($validated['tipo_persona'] === 'Natural') {
                if (empty($validated['nombres']) || empty($validated['apellidos'])) {
                    return response()->json([
                        'mensaje' => 'Para persona natural, nombres y apellidos son obligatorios'
                    ], 422);
                }
            } else {
                if (empty($validated['razon_social'])) {
                    return response()->json([
                        'mensaje' => 'Para persona jurídica, la razón social es obligatoria'
                    ], 422);
                }
            }
        }

        $cliente->update($validated);

        return response()->json([
            'mensaje' => 'Cliente actualizado correctamente',
            'data' => $cliente
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $cliente = Cliente::withCount('documentos')->find($id);

        if (!$cliente) {
            return response()->json(['mensaje' => 'Cliente no encontrado'], 404);
        }

        // Verificar si tiene documentos asociados
        if ($cliente->documentos_count > 0) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el cliente porque tiene documentos asociados'
            ], 422);
        }

        $cliente->delete();

        return response()->json(['mensaje' => 'Cliente eliminado correctamente']);
    }
}