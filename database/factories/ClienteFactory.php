<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    public function definition(): array
    {
        $tipoPersona = $this->faker->randomElement(['Jurídica', 'Natural']);
        
        // Configurar datos según tipo de persona
        if ($tipoPersona === 'Jurídica') {
            $tipoDocumento = 'RUC';
            $numeroDocumento = $this->faker->unique()->numerify('10###########');
            $razonSocial = $this->faker->company();
            $representanteLegal = $this->faker->name();
            $nombres = null;
            $apellidos = null;
        } else {
            $tipoDocumento = $this->faker->randomElement(['DNI', 'Pasaporte']);
            $numeroDocumento = $tipoDocumento === 'DNI' 
                ? $this->faker->unique()->numerify('########')
                : $this->faker->unique()->bothify('??########');
            $razonSocial = null;
            $representanteLegal = null;
            $nombres = $this->faker->firstName();
            $apellidos = $this->faker->lastName();
        }

        return [
            'tipo_persona' => $tipoPersona,
            'tipo_documento' => $tipoDocumento,
            'numero_documento' => $numeroDocumento,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'razon_social' => $razonSocial,
            'representante_legal' => $representanteLegal,
            'telefono' => $this->faker->phoneNumber(),
            'correo' => $this->faker->unique()->safeEmail(),
            'direccion' => $this->faker->address(),
            'estado' => $this->faker->randomElement(['Activo', 'Inactivo']),
            'creado_en' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function juridica(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_persona' => 'Jurídica',
            'tipo_documento' => 'RUC',
            'numero_documento' => $this->faker->unique()->numerify('10###########'),
            'razon_social' => $this->faker->company(),
            'representante_legal' => $this->faker->name(),
            'nombres' => null,
            'apellidos' => null,
        ]);
    }

    public function natural(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_persona' => 'Natural',
            'tipo_documento' => $this->faker->randomElement(['DNI', 'Pasaporte']),
            'numero_documento' => $this->faker->unique()->numerify('########'),
            'nombres' => $this->faker->firstName(),
            'apellidos' => $this->faker->lastName(),
            'razon_social' => null,
            'representante_legal' => null,
        ]);
    }

    public function activo(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'Activo',
        ]);
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'Inactivo',
        ]);
    }

    public function conDocumento(string $tipo, string $numero): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_documento' => $tipo,
            'numero_documento' => $numero,
        ]);
    }
}