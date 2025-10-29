<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    /**
     * Muestra los usuarios paginados (50 por página)
     */
    public function index()
    {
        $usuarios = Usuario::paginate(50);

        return response()->json([
            'mensaje' => 'Lista de usuarios paginada',
            'total'   => $usuarios->total(),
            'data'    => $usuarios->items(),
            'links'   => [
                'current_page' => $usuarios->currentPage(),
                'next_page_url' => $usuarios->nextPageUrl(),
                'prev_page_url' => $usuarios->previousPageUrl(),
                'last_page' => $usuarios->lastPage(),
            ]
        ], 200);
    }

    /**
     * Muestra todos los usuarios (sin límite)
     */
    public function todos()
    {
        // Aumentar el límite de memoria por si hay millones de registros
        ini_set('memory_limit', '2G');

        $usuarios = Usuario::all();

        return response()->json([
            'mensaje' => 'Lista completa de usuarios',
            'total' => $usuarios->count(),
            'data' => $usuarios,
        ], 200);
    }

    /**
     * Muestra un usuario específico.
     */
    public function show($id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        return response()->json($usuario, 200);
    }

    /**
     * Crea un nuevo usuario.
     */
    public function store(Request $request)
    {
        // Validación básica
        $validated = $request->validate([
            'nombres'   => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'correo'    => 'required|string|email|max:150|unique:usuarios,correo',
            'password'  => 'required|string|min:8',
            'rol'       => 'nullable|string|in:Asistente,Abogado,Administrador',
        ]);

        // Cifrado de contraseña antes de guardar (bcrypt)
        $validated['password'] = Hash::make($validated['password']);

        // Creación del usuario
        $usuario = Usuario::create($validated);

        return response()->json([
            'mensaje' => 'Usuario creado correctamente',
            'usuario' => $usuario,
        ], 201);
    }

    /**
     * Actualiza un usuario existente.
     */
    public function update(Request $request, $id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        // Validación con reglas dinámicas (sin romper unique)
        $validated = $request->validate([
            'nombres'   => 'sometimes|required|string|max:100',
            'apellidos' => 'sometimes|required|string|max:100',
            'correo'    => "sometimes|required|string|email|max:150|unique:usuarios,correo,{$usuario->id}",
            'password'  => 'sometimes|required|string|min:8',
            'rol'       => 'sometimes|required|string|in:Asistente,Abogado,Administrador',
        ]);

        // Si incluye contraseña, se vuelve a cifrar
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $usuario->update($validated);

        return response()->json([
            'mensaje' => 'Usuario actualizado correctamente',
            'usuario' => $usuario,
        ], 200);
    }

    /**
     * Elimina un usuario.
     */
    public function destroy($id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        $usuario->delete();

        return response()->json(['mensaje' => 'Usuario eliminado correctamente'], 200);
    }
}
