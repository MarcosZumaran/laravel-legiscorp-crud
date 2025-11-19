<?php

namespace Database\Seeders;

use App\Models\Bitacora;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class BitacoraSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = Usuario::all();

        if ($usuarios->isEmpty()) {
            $this->command->error('No se pueden crear registros de bitácora sin usuarios existentes');
            return;
        }

        $usuarioAdmin = Usuario::where('correo', 'admin@sistema.com')->first();
        $usuarioAbogado = Usuario::where('correo', 'carlos@abogados.com')->first();
        $usuarioAsistente = Usuario::where('correo', 'ana@abogados.com')->first();
        $usuarioMaria = Usuario::where('correo', 'maria@abogados.com')->first();

        // Registros de bitácora predefinidos
        $bitacoraPredefinidos = [
            // Actividades del administrador
            [
                'usuario_id' => $usuarioAdmin->id,
                'accion' => 'LOGIN: Inicio de sesión exitoso',
                'fecha' => now()->subDays(10),
                'ip' => '192.168.1.100',
            ],
            [
                'usuario_id' => $usuarioAdmin->id,
                'accion' => 'CREAR: Configuró permisos del sistema',
                'fecha' => now()->subDays(10)->addHours(2),
                'ip' => '192.168.1.100',
            ],
            [
                'usuario_id' => $usuarioAdmin->id,
                'accion' => 'CREAR: Generó reporte general del sistema',
                'fecha' => now()->subDays(8),
                'ip' => '192.168.1.100',
            ],
            // Actividades del abogado principal
            [
                'usuario_id' => $usuarioAbogado->id,
                'accion' => 'LOGIN: Inicio de sesión exitoso',
                'fecha' => now()->subDays(9),
                'ip' => '192.168.1.101',
            ],
            [
                'usuario_id' => $usuarioAbogado->id,
                'accion' => 'CREAR: Creó nuevo caso CAS-2024-001',
                'fecha' => now()->subDays(9)->addHours(1),
                'ip' => '192.168.1.101',
            ],
            [
                'usuario_id' => $usuarioAbogado->id,
                'accion' => 'ACTUALIZAR: Actualizó estado del caso CAS-2024-001',
                'fecha' => now()->subDays(7),
                'ip' => '192.168.1.101',
            ],
            [
                'usuario_id' => $usuarioAbogado->id,
                'accion' => 'CREAR: Agregó evento al calendario',
                'fecha' => now()->subDays(6),
                'ip' => '192.168.1.101',
            ],
            // Actividades de la asistente
            [
                'usuario_id' => $usuarioAsistente->id,
                'accion' => 'LOGIN: Inicio de sesión exitoso',
                'fecha' => now()->subDays(8),
                'ip' => '192.168.1.102',
            ],
            [
                'usuario_id' => $usuarioAsistente->id,
                'accion' => 'CREAR: Subió documento de evidencia',
                'fecha' => now()->subDays(8)->addHours(3),
                'ip' => '192.168.1.102',
            ],
            [
                'usuario_id' => $usuarioAsistente->id,
                'accion' => 'CONSULTAR: Revisó calendario de actividades',
                'fecha' => now()->subDays(5),
                'ip' => '192.168.1.102',
            ],
            // Actividades de la otra abogada
            [
                'usuario_id' => $usuarioMaria->id,
                'accion' => 'LOGIN: Inicio de sesión exitoso',
                'fecha' => now()->subDays(7),
                'ip' => '192.168.1.103',
            ],
            [
                'usuario_id' => $usuarioMaria->id,
                'accion' => 'CREAR: Creó nuevo caso CAS-2024-004',
                'fecha' => now()->subDays(7)->addHours(2),
                'ip' => '192.168.1.103',
            ],
            [
                'usuario_id' => $usuarioMaria->id,
                'accion' => 'DESCARGAR: Descargó documentos del caso',
                'fecha' => now()->subDays(6),
                'ip' => '192.168.1.103',
            ],
            // Eventos recientes
            [
                'usuario_id' => $usuarioAdmin->id,
                'accion' => 'LOGOUT: Cierre de sesión',
                'fecha' => now()->subHours(3),
                'ip' => '192.168.1.100',
            ],
            [
                'usuario_id' => $usuarioAbogado->id,
                'accion' => 'LOGIN: Inicio de sesión exitoso',
                'fecha' => now()->subHours(2),
                'ip' => '192.168.1.101',
            ],
            [
                'usuario_id' => $usuarioAbogado->id,
                'accion' => 'CONSULTAR: Revisó lista de clientes',
                'fecha' => now()->subHours(1),
                'ip' => '192.168.1.101',
            ],
        ];

        foreach ($bitacoraPredefinidos as $registro) {
            Bitacora::create($registro);
        }

        // Registros de bitácora aleatorios
        Bitacora::factory()->count(25)->create();

        $this->command->info('Registros de bitácora creados correctamente');
        $this->command->info('Total registros: ' . Bitacora::count());
        
        $this->command->info('Distribución por tipo de acción:');
        $acciones = Bitacora::all()->groupBy(function($registro) {
            return explode(':', $registro->accion)[0];
        });
        
        foreach ($acciones as $accion => $registros) {
            $this->command->info('   ' . $accion . ': ' . $registros->count() . ' registros');
        }

        $this->command->info('Campos encriptados: accion, ip');
    }
}