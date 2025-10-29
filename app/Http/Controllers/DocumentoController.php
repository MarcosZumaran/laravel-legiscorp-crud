<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class DocumentoController extends Controller
{
    public function index()
    {
        $documentos = Documento::paginate(50);

        // Desencriptar la ruta antes de devolver los datos
        $documentos->getCollection()->transform(function ($documento) {
            if ($documento->ruta) {
                try {
                    $documento->ruta = Crypt::decryptString($documento->ruta);
                } catch (\Exception $e) {
                    // Si ya está en texto plano o hay error de descifrado, la dejamos igual
                }
            }
            return $documento;
        });

        return response()->json([
            'mensaje' => 'Lista de documentos paginada',
            'total'   => $documentos->total(),
            'data'    => $documentos->items(),
            'links'   => [
                'current_page'  => $documentos->currentPage(),
                'next_page_url' => $documentos->nextPageUrl(),
                'prev_page_url' => $documentos->previousPageUrl(),
                'last_page'     => $documentos->lastPage(),
            ],
        ], 200);
    }

    public function todos()
    {
        ini_set('memory_limit', '2G');

        $documentos = Documento::all();

        $documentos->transform(function ($documento) {
            if ($documento->ruta) {
                try {
                    $documento->ruta = Crypt::decryptString($documento->ruta);
                } catch (\Exception $e) {
                    // Si falla la desencriptación, la dejamos como está
                }
            }
            return $documento;
        });

        return response()->json([
            'mensaje' => 'Lista completa de documentos',
            'total'   => $documentos->count(),
            'data'    => $documentos,
        ], 200);
    }

    public function show($id)
    {
        $documento = Documento::findOrFail($id);
        return response()->json($documento);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_archivo' => 'required|string|max:255',
            'tipo_archivo'   => 'nullable|string|max:50',
            'ruta'           => 'required|string|max:255',
            'descripcion'    => 'nullable|string',
            'expediente'     => 'nullable|string|max:30',
            'subido_por'     => 'nullable|integer|exists:usuarios,id',
            'caso_id'        => 'nullable|integer|exists:casos,id',
            'cliente_id'     => 'nullable|integer|exists:clientes,id',
            'categoria'      => 'nullable|string|max:50|in:Otro,Contrato,Sentencia,Resolución,Evidencia,General',
        ]);

        if ($validator->fails()) {
            return response()->json(['errores' => $validator->errors()], 422);
        }

        $datos = $validator->validated();
        $datos['ruta'] = Crypt::encryptString($datos['ruta']);

        $documento = Documento::create($datos);

        return response()->json($documento, 201);
    }

    public function update(Request $request, $id)
    {
        $documento = Documento::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre_archivo' => 'sometimes|required|string|max:255',
            'tipo_archivo'   => 'nullable|string|max:50',
            'ruta'           => 'sometimes|required|string|max:255',
            'descripcion'    => 'nullable|string',
            'expediente'     => 'nullable|string|max:30',
            'subido_por'     => 'nullable|integer|exists:usuarios,id',
            'caso_id'        => 'nullable|integer|exists:casos,id',
            'cliente_id'     => 'nullable|integer|exists:clientes,id',
            'categoria'      => 'nullable|string|max:50|in:Otro,Contrato,Sentencia,Resolución,Evidencia,General',
        ]);

        if ($validator->fails()) {
            return response()->json(['errores' => $validator->errors()], 422);
        }

        $datos = $validator->validated();

        if (isset($datos['ruta'])) {
            $datos['ruta'] = Crypt::encryptString($datos['ruta']);
        }

        $documento->update($datos);

        return response()->json($documento);
    }

    public function destroy($id)
    {
        $documento = Documento::findOrFail($id);
        $documento->delete();

        return response()->json(['mensaje' => 'Documento eliminado correctamente']);
    }
}
