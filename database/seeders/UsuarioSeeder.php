<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        // Usuarios de prueba con credenciales conocidas
        $usuariosEspecificos = [
            [
                'nombres' => 'Carlos',
                'apellidos' => 'Rodríguez',
                'correo' => 'carlos@abogados.com',
                'password' => 'abogado123',
                'rol' => 'Abogado',
            ],
            [
                'nombres' => 'Ana',
                'apellidos' => 'Gómez',
                'correo' => 'ana@abogados.com',
                'password' => 'asistente123',
                'rol' => 'Asistente',
            ],
            [
                'nombres' => 'Super',
                'apellidos' => 'Administrador',
                'correo' => 'admin@sistema.com',
                'password' => 'admin123',
                'rol' => 'Administrador',
            ],
        ];

        foreach ($usuariosEspecificos as $usuario) {
            Usuario::create($usuario);
        }

        // Usuarios aleatorios para pruebas
        Usuario::factory()->count(15)->create();

        $this->command->info('Usuarios de prueba creados correctamente');
        $this->command->info('Credenciales para pruebas:');
        $this->command->info('Abogado: carlos@abogados.com / abogado123');
        $this->command->info('Asistente: ana@abogados.com / asistente123');
        $this->command->info('Admin: admin@sistema.com / admin123');
    }
}
