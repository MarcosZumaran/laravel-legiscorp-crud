<?php

namespace App\Http\Controllers;

use App\Models\MateriaCaso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class MateriaCasoController extends Controller
{
    /**
     * Mostrar materias paginadas (50 por página)
     */
    public function index()
    {
        $materias = MateriaCaso::paginate(50);

        // Desencriptar descripciones antes de devolverlas
        $materias->getCollection()->transform(function ($materia) {
            if ($materia->descripcion) {
                try {
                    $materia->descripcion = Crypt::decryptString($materia->descripcion);
                } catch (\Exception $e) {
                    // Si ya está en texto plano o hay error de descifrado, la dejamos igual
                }
            }
            return $materia;
        });

        return response()->json([
            'mensaje' => 'Lista de materias paginada',
            'total'   => $materias->total(),
            'data'    => $materias->items(),
            'links'   => [
                'current_page'  => $materias->currentPage(),
                'next_page_url' => $materias->nextPageUrl(),
                'prev_page_url' => $materias->previousPageUrl(),
                'last_page'     => $materias->lastPage(),
            ],
        ], 200);
    }

    /**
     * Mostrar todas las materias (sin límite)
     */
    public function todos()
    {
        ini_set('memory_limit', '2G');

        $materias = MateriaCaso::all();

        $materias->transform(function ($materia) {
            if ($materia->descripcion) {
                try {
                    $materia->descripcion = Crypt::decryptString($materia->descripcion);
                } catch (\Exception $e) {
                    // Si falla la desencriptación, la dejamos como está
                }
            }
            return $materia;
        });

        return response()->json([
            'mensaje' => 'Lista completa de materias',
            'total'   => $materias->count(),
            'data'    => $materias,
        ], 200);
    }

    /**
     * Crear una nueva materia.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
        ]);

        $materia = MateriaCaso::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion ? Crypt::encryptString($request->descripcion) : null,
        ]);

        return response()->json([
            'mensaje' => 'Materia creada correctamente',
            'materia' => $materia
        ], 201);
    }

    /**
     * Mostrar una materia específica.
     */
    public function show($id)
    {
        $materia = MateriaCaso::find($id);

        if (!$materia) {
            return response()->json(['mensaje' => 'Materia no encontrada'], 404);
        }

        // Desencriptar descripción antes de devolverla
        if ($materia->descripcion) {
            try {
                $materia->descripcion = Crypt::decryptString($materia->descripcion);
            } catch (\Exception $e) {
                // En caso de que ya esté en texto plano o haya error
            }
        }

        return response()->json($materia, 200);
    }

    /**
     * Actualizar una materia existente.
     */
    public function update(Request $request, $id)
    {
        $materia = MateriaCaso::find($id);

        if (!$materia) {
            return response()->json(['mensaje' => 'Materia no encontrada'], 404);
        }

        $request->validate([
            'nombre' => 'sometimes|required|string|max:100',
            'descripcion' => 'nullable|string',
        ]);

        if ($request->has('nombre')) {
            $materia->nombre = $request->nombre;
        }

        if ($request->has('descripcion')) {
            $materia->descripcion = $request->descripcion
                ? Crypt::encryptString($request->descripcion)
                : null;
        }

        $materia->save();

        return response()->json([
            'mensaje' => 'Materia actualizada correctamente',
            'materia' => $materia
        ], 200);
    }

    /**
     * Eliminar una materia.
     */
    public function destroy($id)
    {
        $materia = MateriaCaso::find($id);

        if (!$materia) {
            return response()->json(['mensaje' => 'Materia no encontrada'], 404);
        }

        $materia->delete();

        return response()->json(['mensaje' => 'Materia eliminada correctamente'], 200);
    }
}
