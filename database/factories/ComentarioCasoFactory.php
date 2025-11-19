<?php

namespace Database\Factories;

use App\Models\Caso;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComentarioCasoFactory extends Factory
{
    public function definition(): array
    {
        $casos = Caso::all();
        $usuarios = Usuario::all();

        if ($casos->isEmpty() || $usuarios->isEmpty()) {
            throw new \Exception('Se necesitan casos y usuarios para crear comentarios');
        }

        $fechaComentario = $this->faker->dateTimeBetween('-60 days', 'now');

        return [
            'caso_id' => $casos->random()->id,
            'usuario_id' => $usuarios->random()->id,
            'comentario' => $this->faker->paragraph(2),
            'fecha' => $fechaComentario,
        ];
    }

    public function paraCaso(int $casoId): static
    {
        return $this->state(fn (array $attributes) => [
            'caso_id' => $casoId,
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
            'fecha' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function conComentario(string $comentario): static
    {
        return $this->state(fn (array $attributes) => [
            'comentario' => $comentario,
        ]);
    }
}