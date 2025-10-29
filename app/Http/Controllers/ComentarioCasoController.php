<?php

namespace App\Http\Controllers;

use App\Models\ComentarioCaso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ComentarioCasoController extends Controller
{
    public function index()
    {
        $comentarios = ComentarioCaso::paginate(50);

        return response()->json([
            'mensaje' => 'Lista de comentarios paginada',
            'total'   => $comentarios->total(),
            'data'    => $comentarios->items(),
            'links'   => [
                'current_page'  => $comentarios->currentPage(),
                'next_page_url' => $comentarios->nextPageUrl(),
                'prev_page_url' => $comentarios->previousPageUrl(),
                'last_page'     => $comentarios->lastPage(),
            ],
        ], 200);
    }

    public function todos()
    {
        ini_set('memory_limit', '2G');

        $comentarios = ComentarioCaso::all();

        return response()->json([
            'mensaje' => 'Lista completa de comentarios',
            'total'   => $comentarios->count(),
            'data'    => $comentarios,
        ], 200);
    }


    public function show($id)
    {
        $comentario = ComentarioCaso::findOrFail($id);
        return response()->json($comentario);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'caso_id'    => 'required|integer|exists:casos,id',
            'usuario_id' => 'required|integer|exists:usuarios,id',
            'comentario' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errores' => $validator->errors()], 422);
        }

        $comentario = ComentarioCaso::create($validator->validated());

        return response()->json($comentario, 201);
    }

    public function update(Request $request, $id)
    {
        $comentario = ComentarioCaso::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'comentario' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errores' => $validator->errors()], 422);
        }

        $comentario->update($validator->validated());

        return response()->json($comentario);
    }

    public function destroy($id)
    {
        $comentario = ComentarioCaso::findOrFail($id);
        $comentario->delete();

        return response()->json(['mensaje' => 'Comentario eliminado correctamente']);
    }
}
