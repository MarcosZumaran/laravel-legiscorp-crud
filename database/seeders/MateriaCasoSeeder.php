<?php

namespace Database\Seeders;

use App\Models\MateriaCaso;
use Illuminate\Database\Seeder;

class MateriaCasoSeeder extends Seeder
{
    public function run(): void
    {
        $materias = [
            ['nombre' => 'Civil', 'descripcion' => 'Derecho Civil'],
            ['nombre' => 'Penal', 'descripcion' => 'Derecho Penal'],
            ['nombre' => 'Laboral', 'descripcion' => 'Derecho Laboral'],
            ['nombre' => 'Mercantil', 'descripcion' => 'Derecho Mercantil'],
            ['nombre' => 'Administrativo', 'descripcion' => 'Derecho Administrativo'],
            ['nombre' => 'Constitucional', 'descripcion' => 'Derecho Constitucional'],
            ['nombre' => 'Familia', 'descripcion' => 'Derecho de Familia'],
            ['nombre' => 'Propiedad Intelectual', 'descripcion' => 'Derecho de Propiedad Intelectual'],
        ];

        foreach ($materias as $materia) {
            MateriaCaso::create($materia);
        }

        $this->command->info('Materias creadas correctamente');
        $this->command->info('Materias disponibles: ' . MateriaCaso::count());
    }
}