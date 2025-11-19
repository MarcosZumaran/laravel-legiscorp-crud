<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\MateriaCaso;
use App\Models\TiposCasos;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class CasoFactory extends Factory
{
    public function definition(): array
    {
        $estados = ['Abierto', 'En Proceso', 'Cerrado'];
        $materias = MateriaCaso::all();
        $tiposCasos = TiposCasos::all();
        $abogados = Usuario::where('rol', 'Abogado')->get();
        $clientes = Cliente::all();

        if ($materias->isEmpty() || $abogados->isEmpty() || $clientes->isEmpty()) {
            throw new \Exception('Se necesitan materias, abogados y clientes para crear casos');
        }

        $fechaInicio = $this->faker->dateTimeBetween('-1 year', 'now');
        $estado = $this->faker->randomElement($estados);
        
        return [
            'codigo_caso' => 'CAS-' . $this->faker->unique()->numberBetween(1000, 9999),
            'numero_expediente' => $this->faker->optional(0.8)->bothify('EXP-####-??'),
            'numero_carpeta_fiscal' => $this->faker->optional(0.6)->bothify('CPF-####-??'),
            'titulo' => $this->faker->sentence(4),
            'descripcion' => $this->faker->paragraph(3),
            'materia_id' => $materias->random()->id,
            'tipo_caso_id' => $tiposCasos->isNotEmpty() ? $tiposCasos->random()->id : null,
            'estado' => $estado,
            'fecha_inicio' => $fechaInicio,
            'fecha_cierre' => $estado === 'Cerrado' ? $this->faker->dateTimeBetween($fechaInicio, 'now') : null,
            'cliente_id' => $clientes->random()->id,
            'abogado_id' => $abogados->random()->id,
            'contraparte' => $this->faker->optional(0.7)->name(),
            'juzgado' => $this->faker->optional(0.6)->company(),
            'fiscal' => $this->faker->optional(0.5)->name(),
            'creado_en' => $fechaInicio,
        ];
    }

    public function abierto(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'Abierto',
            'fecha_cierre' => null,
        ]);
    }

    public function enProceso(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'En Proceso',
            'fecha_cierre' => null,
        ]);
    }

    public function cerrado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'Cerrado',
            'fecha_cierre' => $this->faker->dateTimeBetween($attributes['fecha_inicio'], 'now'),
        ]);
    }

    public function paraCliente(int $clienteId): static
    {
        return $this->state(fn (array $attributes) => [
            'cliente_id' => $clienteId,
        ]);
    }

    public function conAbogado(int $abogadoId): static
    {
        return $this->state(fn (array $attributes) => [
            'abogado_id' => $abogadoId,
        ]);
    }

    public function conMateria(int $materiaId): static
    {
        return $this->state(fn (array $attributes) => [
            'materia_id' => $materiaId,
        ]);
    }
}