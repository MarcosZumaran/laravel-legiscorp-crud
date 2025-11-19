<?php

namespace Database\Factories;

use App\Models\Caso;
use App\Models\Cliente;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class CalendarioFactory extends Factory
{
    public function definition(): array
    {
        $tiposEvento = ['Audiencia', 'Reunión', 'Plazo', 'Entrega', 'Otro'];
        $estados = ['Pendiente', 'Completado', 'Cancelado'];
        $recurrencias = ['No', 'Diario', 'Semanal', 'Mensual', 'Anual'];
        $prioridades = ['Baja', 'Media', 'Alta', 'Urgente'];
        $colores = ['#3486bc', '#ff6b6b', '#51cf66', '#ffd43b', '#cc5de8'];

        $fechaInicio = $this->faker->dateTimeBetween('now', '+60 days');
        $fechaFin = $this->faker->dateTimeBetween($fechaInicio, '+60 days');

        $casos = Caso::all();
        $abogados = Usuario::where('rol', 'Abogado')->get();
        $clientes = Cliente::all();
        $usuarios = Usuario::all();

        return [
            'titulo' => $this->faker->sentence(4),
            'descripcion' => $this->faker->paragraph(2),
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $this->faker->boolean(70) ? $fechaFin : null,
            'ubicacion' => $this->faker->optional(0.6)->address(),
            'tipo_evento' => $this->faker->randomElement($tiposEvento),
            'estado' => $this->faker->randomElement($estados),
            'color' => $this->faker->randomElement($colores),
            'recurrente' => $this->faker->randomElement($recurrencias),
            'caso_id' => $casos->isNotEmpty() && $this->faker->boolean(60) ? $casos->random()->id : null,
            'abogado_id' => $abogados->isNotEmpty() ? $abogados->random()->id : null,
            'cliente_id' => $clientes->isNotEmpty() && $this->faker->boolean(50) ? $clientes->random()->id : null,
            'creado_por' => $usuarios->isNotEmpty() ? $usuarios->random()->id : Usuario::factory()->create()->id,
            'expediente' => $this->faker->optional(0.4)->bothify('EXP-####-??'),
            'creado_en' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'prioridad' => $this->faker->randomElement($prioridades),
        ];
    }

    public function audiencia(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_evento' => 'Audiencia',
            'color' => '#ff6b6b',
            'prioridad' => 'Alta',
        ]);
    }

    public function reunion(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_evento' => 'Reunión',
            'color' => '#3486bc',
            'prioridad' => 'Media',
        ]);
    }

    public function plazo(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_evento' => 'Plazo',
            'color' => '#ffd43b',
            'prioridad' => 'Alta',
        ]);
    }

    public function pendiente(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'Pendiente',
        ]);
    }

    public function completado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'Completado',
        ]);
    }

    public function paraCaso(int $casoId): static
    {
        return $this->state(fn (array $attributes) => [
            'caso_id' => $casoId,
        ]);
    }

    public function conAbogado(int $abogadoId): static
    {
        return $this->state(fn (array $attributes) => [
            'abogado_id' => $abogadoId,
        ]);
    }

    public function urgente(): static
    {
        return $this->state(fn (array $attributes) => [
            'prioridad' => 'Urgente',
            'color' => '#ff6b6b',
        ]);
    }

    public function proximo(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_inicio' => $this->faker->dateTimeBetween('now', '+7 days'),
            'estado' => 'Pendiente',
        ]);
    }
}