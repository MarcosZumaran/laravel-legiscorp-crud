<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;

class ClienteController extends Controller
{

    public function index()
    {
        $clientes = Cliente::paginate(50);

        // Desencriptar correo y teléfono antes de devolverlos
        $clientes->getCollection()->transform(function ($cliente) {
            try {
                if (!empty($cliente->correo)) {
                    $cliente->correo = Crypt::decryptString($cliente->correo);
                }
                if (!empty($cliente->telefono)) {
                    $cliente->telefono = Crypt::decryptString($cliente->telefono);
                }
            } catch (\Exception $e) {
                // Si ya están en texto plano o hay error, se dejan como están
            }
            return $cliente;
        });

        return response()->json([
            'mensaje' => 'Lista de clientes paginada',
            'total'   => $clientes->total(),
            'data'    => $clientes->items(),
            'links'   => [
                'current_page'  => $clientes->currentPage(),
                'next_page_url' => $clientes->nextPageUrl(),
                'prev_page_url' => $clientes->previousPageUrl(),
                'last_page'     => $clientes->lastPage(),
            ],
        ], 200);
    }

    public function todos()
    {
        ini_set('memory_limit', '2G');

        $clientes = Cliente::all();

        $clientes->transform(function ($cliente) {
            try {
                if (!empty($cliente->correo)) {
                    $cliente->correo = Crypt::decryptString($cliente->correo);
                }
                if (!empty($cliente->telefono)) {
                    $cliente->telefono = Crypt::decryptString($cliente->telefono);
                }
            } catch (\Exception $e) {
                // Se deja como está si no puede desencriptarse
            }
            return $cliente;
        });

        return response()->json([
            'mensaje' => 'Lista completa de clientes',
            'total'   => $clientes->count(),
            'data'    => $clientes,
        ], 200);
    }


    public function show($id)
    {
        $cliente = Cliente::findOrFail($id);
        return response()->json($cliente);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo_persona'        => 'required|string|in:Jurídica,Natural',
            'tipo_documento'      => 'required|string|in:Pasaporte,RUC,DNI',
            'numero_documento'    => 'required|string|max:20|unique:clientes,numero_documento',
            'nombres'             => 'nullable|string|max:100',
            'apellidos'           => 'nullable|string|max:100',
            'razon_social'        => 'nullable|string|max:150',
            'representante_legal' => 'nullable|string|max:150',
            'telefono'            => 'nullable|string|max:20',
            'correo'              => 'nullable|email|max:150',
            'direccion'           => 'nullable|string|max:255',
            'estado'              => 'nullable|string|in:Activo,Inactivo',
        ]);

        if ($validator->fails()) {
            return response()->json(['errores' => $validator->errors()], 422);
        }

        $datos = $validator->validated();

        if (!empty($datos['correo'])) {
            $datos['correo'] = Crypt::encryptString($datos['correo']);
        }

        if (!empty($datos['telefono'])) {
            $datos['telefono'] = Crypt::encryptString($datos['telefono']);
        }

        $cliente = Cliente::create($datos);

        return response()->json($cliente, 201);
    }

    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'tipo_persona'        => 'sometimes|required|string|in:Jurídica,Natural',
            'tipo_documento'      => 'sometimes|required|string|in:Pasaporte,RUC,DNI',
            'numero_documento'    => 'sometimes|required|string|max:20|unique:clientes,numero_documento,' . $cliente->id,
            'nombres'             => 'nullable|string|max:100',
            'apellidos'           => 'nullable|string|max:100',
            'razon_social'        => 'nullable|string|max:150',
            'representante_legal' => 'nullable|string|max:150',
            'telefono'            => 'nullable|string|max:20',
            'correo'              => 'nullable|email|max:150',
            'direccion'           => 'nullable|string|max:255',
            'estado'              => 'nullable|string|in:Activo,Inactivo',
        ]);

        if ($validator->fails()) {
            return response()->json(['errores' => $validator->errors()], 422);
        }

        $datos = $validator->validated();

        if (isset($datos['correo'])) {
            $datos['correo'] = Crypt::encryptString($datos['correo']);
        }

        if (isset($datos['telefono'])) {
            $datos['telefono'] = Crypt::encryptString($datos['telefono']);
        }

        $cliente->update($datos);

        return response()->json($cliente);
    }

    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();

        return response()->json(['mensaje' => 'Cliente eliminado correctamente']);
    }
}
