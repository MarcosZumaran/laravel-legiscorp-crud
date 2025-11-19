<?php

namespace Database\Factories;

use App\Models\MateriaCaso;
use Illuminate\Database\Eloquent\Factories\Factory;

class TiposCasosFactory extends Factory
{
    public function definition(): array
    {
        // Asegurarnos de que existe al menos una materia
        $materia = MateriaCaso::inRandomOrder()->first() ?? MateriaCaso::factory()->create();

        return [
            'materia_id' => $materia->id,
            'nombre' => $this->faker->unique()->sentence(3),
            'descripcion' => $this->faker->paragraph(2),
        ];
    }

    /**
     * Estado para tipo de caso específico
     */
    public function conNombre(string $nombre): static
    {
        return $this->state(fn (array $attributes) => [
            'nombre' => $nombre,
        ]);
    }

    /**
     * Estado para materia específica
     */
    public function paraMateria(int $materiaId): static
    {
        return $this->state(fn (array $attributes) => [
            'materia_id' => $materiaId,
        ]);
    }
}