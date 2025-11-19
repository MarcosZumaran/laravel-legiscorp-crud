<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UsuarioSeeder::class,
            MateriaCasoSeeder::class,
            TiposCasosSeeder::class,
            ClienteSeeder::class,
            CasoSeeder::class,
            ReporteSeeder::class,
            DocumentoSeeder::class,
            DocumentoCompartidoSeeder::class,
            ComentarioCasoSeeder::class,
            BitacoraSeeder::class,
            CalendarioSeeder::class,
        ]);
    }
}
