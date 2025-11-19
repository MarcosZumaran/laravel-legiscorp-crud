<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class BitacoraFactory extends Factory
{
    public function definition(): array
    {
        $acciones = [
            'LOGIN: Inicio de sesión exitoso',
            'LOGOUT: Cierre de sesión',
            'CREAR: Creó un nuevo caso',
            'ACTUALIZAR: Actualizó información del caso',
            'ELIMINAR: Eliminó registro temporal',
            'CONSULTAR: Consultó lista de clientes',
            'DESCARGAR: Descargó reporte PDF',
            'ERROR: Error de validación en formulario',
            'ACCESO_DENEGADO: Intento de acceso no autorizado',
            'CREAR: Subió nuevo documento',
            'ACTUALIZAR: Modificó datos de cliente',
            'CONSULTAR: Revisó calendario de actividades',
        ];

        $usuarios = Usuario::all();

        return [
            'usuario_id' => $usuarios->isNotEmpty() ? $usuarios->random()->id : Usuario::factory()->create()->id,
            'accion' => $this->faker->randomElement($acciones),
            'fecha' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'ip' => $this->faker->ipv4(),
        ];
    }

    public function login(): static
    {
        return $this->state(fn (array $attributes) => [
            'accion' => 'LOGIN: Inicio de sesión exitoso',
        ]);
    }

    public function logout(): static
    {
        return $this->state(fn (array $attributes) => [
            'accion' => 'LOGOUT: Cierre de sesión',
        ]);
    }

    public function crear(): static
    {
        return $this->state(fn (array $attributes) => [
            'accion' => 'CREAR: Creó nuevo registro',
        ]);
    }

    public function actualizar(): static
    {
        return $this->state(fn (array $attributes) => [
            'accion' => 'ACTUALIZAR: Modificó información',
        ]);
    }

    public function porUsuario(int $usuarioId): static
    {
        return $this->state(fn (array $attributes) => [
            'usuario_id' => $usuarioId,
        ]);
    }

    public function reciente(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha' => $this->faker->dateTimeBetween('-1 day', 'now'),
        ]);
    }
}