<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $porPagina = $request->query('por_pagina', 50);
        $usuarios = Usuario::paginate($porPagina);

        return response()->json([
            'mensaje' => 'Lista de usuarios paginada',
            'total'   => $usuarios->total(),
            'data'    => $usuarios->items(),
            'paginacion'   => [
                'current_page' => $usuarios->currentPage(),
                'per_page' => $usuarios->perPage(),
                'next_page_url' => $usuarios->nextPageUrl(),
                'prev_page_url' => $usuarios->previousPageUrl(),
                'last_page' => $usuarios->lastPage(),
                'from' => $usuarios->firstItem(),
                'to' => $usuarios->lastItem(),
            ]
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombres'   => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'correo'    => 'required|string|email|max:150',
            'password'  => 'required|string|min:8',
            'rol'       => 'nullable|string|in:Asistente,Abogado,Administrador',
        ]);

        // ✅ Validar unicidad usando correo_hash
        if (Usuario::correoExiste($validated['correo'])) {
            return response()->json([
                'mensaje' => 'El correo ya está registrado'
            ], 422);
        }

        $usuario = Usuario::create($validated);

        return response()->json([
            'mensaje' => 'Usuario creado correctamente',
            'data' => $usuario
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        return response()->json([
            'mensaje' => 'Usuario encontrado',
            'data' => $usuario
        ], 200);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        $validated = $request->validate([
            'nombres'   => 'sometimes|required|string|max:100',
            'apellidos' => 'sometimes|required|string|max:100',
            'correo'    => 'sometimes|required|string|email|max:150',
            'password'  => 'sometimes|required|string|min:8',
            'rol'       => 'sometimes|required|string|in:Asistente,Abogado,Administrador',
        ]);

        // ✅ Validar correo único solo si se está actualizando
        if (isset($validated['correo']) && $validated['correo'] !== $usuario->correo) {
            if (Usuario::correoExiste($validated['correo'])) {
                return response()->json([
                    'mensaje' => 'El correo ya está registrado por otro usuario'
                ], 422);
            }
        }

        $usuario->update($validated);

        return response()->json([
            'mensaje' => 'Usuario actualizado correctamente',
            'data' => $usuario
        ], 200);
    }

    public function destroy($id): JsonResponse
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        $usuario->delete();

        return response()->json([
            'mensaje' => 'Usuario eliminado correctamente'
        ], 200);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'correo' => 'required|email',
            'password' => 'required|string|min:8'
        ]);

        $usuario = Usuario::buscarLogin($validated['correo']);

        if (!$usuario || !Hash::check($validated['password'], $usuario->password)) {
            return response()->json([
                'mensaje' => 'Credenciales incorrectas'
            ], 401);
        }

        return response()->json([
            'mensaje' => 'Login exitoso',
            'data' => $usuario
        ], 200);
    }

    public function buscar(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100'
        ]);

        $termino = $request->get('q');
        $usuarios = Usuario::buscarPorTexto($termino);

        return response()->json([
            'mensaje' => 'Resultados de búsqueda',
            'termino' => $termino,
            'total' => $usuarios->count(),
            'data' => $usuarios
        ], 200);
    }
}