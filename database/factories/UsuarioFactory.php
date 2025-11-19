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
            [
                'nombres' => 'María',
                'apellidos' => 'Fernández',
                'correo' => 'maria@abogados.com',
                'password' => 'password123',
                'rol' => 'Abogado',
            ],
        ];

        foreach ($usuariosEspecificos as $usuario) {
            Usuario::create($usuario);
        }

        // Usuarios aleatorios para pruebas masivas
        Usuario::factory()->count(10)->create();

        $this->command->info('Usuarios de prueba creados correctamente');
        $this->command->info('Credenciales para pruebas:');
        $this->command->info('Abogado: carlos@abogados.com / abogado123');
        $this->command->info('Asistente: ana@abogados.com / asistente123');
        $this->command->info('Admin: admin@sistema.com / admin123');
        $this->command->info('Abogado 2: maria@abogados.com / password123');

        $this->command->info('');
        $this->command->info('Los siguientes campos se encriptarán automáticamente:');
        $this->command->info('   - nombres');
        $this->command->info('   - apellidos');
        $this->command->info('   - correo');
        $this->command->info('   - password (hashing)');
        $this->command->info('');
        $this->command->info(' Campo correo_hash: se usa para búsquedas y unicidad');
    }
}
