<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MateriaCasoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->word(),
            'descripcion' => $this->faker->sentence(),
        ];
    }

    /**
     * Estado para una materia específica
     */
    public function conNombre(string $nombre): static
    {
        return $this->state(fn (array $attributes) => [
            'nombre' => $nombre,
            'descripcion' => "Descripción para $nombre",
        ]);
    }
}