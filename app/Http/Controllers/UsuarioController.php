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
            'data'    => $usuarios->map->toSafeArray(), // Usar datos seguros
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
            'data' => $usuarios->map->toSafeArray(), // Usar datos seguros
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

        return response()->json([
            'mensaje' => 'Usuario encontrado',
            'usuario' => $usuario->toSafeArray() // Usar datos seguros
        ], 200);
    }

    /**
     * Crea un nuevo usuario.
     */
    public function store(Request $request)
    {
        // Validación básica - ELIMINAR unique:usuarios,correo porque está encriptado
        $validated = $request->validate([
            'nombres'   => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'correo'    => 'required|string|email|max:150', // Quitar unique
            'password'  => 'required|string|min:8',
            'rol'       => 'nullable|string|in:Asistente,Abogado,Administrador',
        ]);

        // Validar manualmente que el correo no exista (porque está encriptado)
        $usuarioExistente = Usuario::buscarPorCorreo($validated['correo']);
        if ($usuarioExistente) {
            return response()->json([
                'mensaje' => 'El correo ya está registrado'
            ], 422);
        }

        // ELIMINAR esta línea - El modelo ya hashea el password automáticamente
        // $validated['password'] = Hash::make($validated['password']);

        // Creación del usuario - La encriptación es automática en el modelo
        $usuario = Usuario::create($validated);

        return response()->json([
            'mensaje' => 'Usuario creado correctamente',
            'usuario' => $usuario->toSafeArray(), // Usar datos seguros
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

        // Validación con reglas dinámicas - ELIMINAR unique
        $validated = $request->validate([
            'nombres'   => 'sometimes|required|string|max:100',
            'apellidos' => 'sometimes|required|string|max:100',
            'correo'    => 'sometimes|required|string|email|max:150', // Quitar unique
            'password'  => 'sometimes|required|string|min:8',
            'rol'       => 'sometimes|required|string|in:Asistente,Abogado,Administrador',
        ]);

        // Validar manualmente que el correo no exista (si se está actualizando el correo)
        if (isset($validated['correo'])) {
            $usuarioExistente = Usuario::buscarPorCorreo($validated['correo']);
            if ($usuarioExistente && $usuarioExistente->id != $id) {
                return response()->json([
                    'mensaje' => 'El correo ya está registrado por otro usuario'
                ], 422);
            }
        }

        // ELIMINAR este bloque - El modelo ya maneja el password
        // if (isset($validated['password'])) {
        //     $validated['password'] = Hash::make($validated['password']);
        // }

        // La actualización y encriptación son automáticas en el modelo
        $usuario->update($validated);

        return response()->json([
            'mensaje' => 'Usuario actualizado correctamente',
            'usuario' => $usuario->toSafeArray(), // Usar datos seguros
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

    // MÉTODOS ADICIONALES PARA BÚSQUEDAS

    /**
     * Buscar usuarios por término (correo, nombre, apellido, rol)
     */
    public function buscar(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2'
        ]);

        $termino = $request->get('q');
        $usuarios = Usuario::buscarPorTexto($termino);

        return response()->json([
            'mensaje' => 'Resultados de búsqueda',
            'termino' => $termino,
            'total' => $usuarios->count(),
            'data' => $usuarios->map->toSafeArray()
        ], 200);
    }

    /**
     * Buscar usuario específico por correo (para login)
     */
    public function buscarPorCorreo(Request $request)
    {
        $request->validate([
            'correo' => 'required|email'
        ]);

        $usuario = Usuario::buscarPorCorreo($request->correo);

        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        return response()->json([
            'mensaje' => 'Usuario encontrado',
            'usuario' => $usuario->toSafeArray()
        ], 200);
    }
}