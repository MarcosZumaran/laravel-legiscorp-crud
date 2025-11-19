<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentoFactory extends Factory
{
    public function definition(): array
    {
        $tiposArchivo = ['pdf', 'doc', 'docx', 'xls', 'jpg', 'png'];
        $categorias = ['General', 'Contrato', 'Sentencia', 'Resolución', 'Evidencia', 'Otro'];
        
        $esCarpeta = $this->faker->boolean(20); // 20% probabilidad de ser carpeta

        return [
            'nombre_archivo' => $esCarpeta ? 
                $this->faker->word() : 
                $this->faker->word() . '.' . $this->faker->randomElement($tiposArchivo),
            'tipo_archivo' => $esCarpeta ? null : $this->faker->randomElement($tiposArchivo),
            'ruta' => $esCarpeta ? 
                '/carpetas/' . $this->faker->word() : 
                '/documentos/' . $this->faker->word() . '.' . $this->faker->randomElement($tiposArchivo),
            'descripcion' => $this->faker->optional(0.7)->sentence(),
            'expediente' => $this->faker->optional(0.5)->bothify('EXP-####-??'),
            'fecha_subida' => $this->faker->dateTimeBetween('-60 days', 'now'),
            'subido_por' => Usuario::inRandomOrder()->first()->id ?? Usuario::factory()->create()->id,
            'caso_id' => null, // Se asignará cuando existan casos
            'cliente_id' => null, // Se asignará cuando existan clientes
            'categoria' => $this->faker->randomElement($categorias),
            'tamano_bytes' => $esCarpeta ? null : $this->faker->numberBetween(1024, 10485760), // 1KB a 10MB
            'es_carpeta' => $esCarpeta,
            'carpeta_padre_id' => null, // Se manejará en el seeder
            'es_publico' => $this->faker->boolean(30),
            'etiquetas' => $this->faker->optional(0.4)->words(3, true),
        ];
    }

    public function carpeta(): static
    {
        return $this->state(fn (array $attributes) => [
            'es_carpeta' => true,
            'tipo_archivo' => null,
            'tamano_bytes' => null,
        ]);
    }

    public function archivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'es_carpeta' => false,
        ]);
    }

    public function publico(): static
    {
        return $this->state(fn (array $attributes) => [
            'es_publico' => true,
        ]);
    }

    public function privado(): static
    {
        return $this->state(fn (array $attributes) => [
            'es_publico' => false,
        ]);
    }

    public function conCategoria(string $categoria): static
    {
        return $this->state(fn (array $attributes) => [
            'categoria' => $categoria,
        ]);
    }

    public function subidoPor(int $usuarioId): static
    {
        return $this->state(fn (array $attributes) => [
            'subido_por' => $usuarioId,
        ]);
    }
}