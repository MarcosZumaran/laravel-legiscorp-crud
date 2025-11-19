<?php

namespace Database\Factories;

use App\Models\Documento;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentoCompartidoFactory extends Factory
{
    public function definition(): array
    {
        $documentos = Documento::where('es_carpeta', false)->get();
        $usuarios = Usuario::all();
        
        if ($documentos->isEmpty() || $usuarios->isEmpty()) {
            throw new \Exception('Se necesitan documentos y usuarios para crear documentos compartidos');
        }

        $compartirConUsuario = $this->faker->boolean(70);

        return [
            'documento_id' => $documentos->random()->id,
            'compartido_con_usuario_id' => $compartirConUsuario ? $usuarios->random()->id : null,
            'compartido_con_rol' => !$compartirConUsuario ? $this->faker->randomElement(['Asistente', 'Abogado', 'Administrador']) : null,
            'permisos' => $this->faker->randomElement(['lectura', 'escritura']),
            'fecha_compartido' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'compartido_por' => $usuarios->random()->id,
        ];
    }

    public function conPermisoLectura(): static
    {
        return $this->state(fn (array $attributes) => [
            'permisos' => 'lectura',
        ]);
    }

    public function conPermisoEscritura(): static
    {
        return $this->state(fn (array $attributes) => [
            'permisos' => 'escritura',
        ]);
    }

    public function compartidoConUsuario(int $usuarioId): static
    {
        return $this->state(fn (array $attributes) => [
            'compartido_con_usuario_id' => $usuarioId,
            'compartido_con_rol' => null,
        ]);
    }

    public function compartidoConRol(string $rol): static
    {
        return $this->state(fn (array $attributes) => [
            'compartido_con_usuario_id' => null,
            'compartido_con_rol' => $rol,
        ]);
    }

    public function porUsuario(int $usuarioId): static
    {
        return $this->state(fn (array $attributes) => [
            'compartido_por' => $usuarioId,
        ]);
    }

    public function paraDocumento(int $documentoId): static
    {
        return $this->state(fn (array $attributes) => [
            'documento_id' => $documentoId,
        ]);
    }
}