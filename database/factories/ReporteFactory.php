<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReporteFactory extends Factory
{
    public function definition(): array
    {
        $tiposReporte = [
            'General',
            'Calendario', 
            'Documentos',
            'Clientes',
            'Casos',
        ];

        return [
            'titulo' => $this->faker->sentence(4),
            'tipo_reporte' => $this->faker->randomElement($tiposReporte),
            'descripcion' => $this->faker->optional(0.8)->paragraph(3), // 80% de tener descripción
            'parametros' => json_encode([
                'fecha_inicio' => $this->faker->date(),
                'fecha_fin' => $this->faker->date(),
                'filtros' => $this->faker->words(3),
            ]),
            'fecha_generacion' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'generado_por' => Usuario::inRandomOrder()->first()->id ?? Usuario::factory()->create()->id,
        ];
    }

    public function general(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_reporte' => 'General',
            'titulo' => 'Reporte General del Sistema',
        ]);
    }

    public function calendario(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_reporte' => 'Calendario',
            'titulo' => 'Reporte de Actividades del Calendario',
            'parametros' => json_encode([
                'rango_fechas' => $this->faker->date(),
                'incluir_vencidos' => true,
            ]),
        ]);
    }

    public function documentos(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_reporte' => 'Documentos', 
            'titulo' => 'Reporte de Estado de Documentos',
        ]);
    }

    public function clientes(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_reporte' => 'Clientes',
            'titulo' => 'Reporte de Clientes Activos',
        ]);
    }

    public function casos(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_reporte' => 'Casos',
            'titulo' => 'Reporte de Casos por Estado',
        ]);
    }

    public function paraUsuario(int $usuarioId): static
    {
        return $this->state(fn (array $attributes) => [
            'generado_por' => $usuarioId,
        ]);
    }

    public function conParametros(array $parametros): static
    {
        return $this->state(fn (array $attributes) => [
            'parametros' => json_encode($parametros),
        ]);
    }
}