<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        // Clientes predefinidos
        $clientesPredefinidos = [
            // Personas Naturales
            [
                'tipo_persona' => 'Natural',
                'tipo_documento' => 'DNI',
                'numero_documento' => '71234567',
                'nombres' => 'Juan Carlos',
                'apellidos' => 'Pérez González',
                'razon_social' => null,
                'representante_legal' => null,
                'telefono' => '+51 987 654 321',
                'correo' => 'juan.perez@email.com',
                'direccion' => 'Av. Principal 123, Lima, Perú',
                'estado' => 'Activo',
                'creado_en' => now()->subMonths(6),
            ],
            [
                'tipo_persona' => 'Natural',
                'tipo_documento' => 'DNI',
                'numero_documento' => '87654321',
                'nombres' => 'María Elena',
                'apellidos' => 'Rodríguez Silva',
                'razon_social' => null,
                'representante_legal' => null,
                'telefono' => '+51 987 123 456',
                'correo' => 'maria.rodriguez@email.com',
                'direccion' => 'Calle Los Olivos 456, Miraflores, Lima',
                'estado' => 'Activo',
                'creado_en' => now()->subMonths(4),
            ],
            [
                'tipo_persona' => 'Natural',
                'tipo_documento' => 'Pasaporte',
                'numero_documento' => 'AB123456',
                'nombres' => 'Robert',
                'apellidos' => 'Johnson Smith',
                'razon_social' => null,
                'representante_legal' => null,
                'telefono' => '+51 987 555 777',
                'correo' => 'robert.johnson@email.com',
                'direccion' => 'Urb. Las Gardenias 789, Surco, Lima',
                'estado' => 'Activo',
                'creado_en' => now()->subMonths(2),
            ],
            // Personas Jurídicas
            [
                'tipo_persona' => 'Jurídica',
                'tipo_documento' => 'RUC',
                'numero_documento' => '20123456789',
                'nombres' => null,
                'apellidos' => null,
                'razon_social' => 'Importadora Comercial S.A.',
                'representante_legal' => 'Carlos Eduardo Mendoza López',
                'telefono' => '+51 1 234 5678',
                'correo' => 'contacto@importadoracomercial.com',
                'direccion' => 'Av. Industrial 1234, Lima, Perú',
                'estado' => 'Activo',
                'creado_en' => now()->subMonths(8),
            ],
            [
                'tipo_persona' => 'Jurídica',
                'tipo_documento' => 'RUC',
                'numero_documento' => '20345678901',
                'nombres' => null,
                'apellidos' => null,
                'razon_social' => 'Constructora Desarrollo Inmobiliario E.I.R.L.',
                'representante_legal' => 'Ana Patricia Gutiérrez Ríos',
                'telefono' => '+51 1 345 6789',
                'correo' => 'administracion@constructora-di.com',
                'direccion' => 'Calle Las Magnolias 567, San Isidro, Lima',
                'estado' => 'Activo',
                'creado_en' => now()->subMonths(5),
            ],
            [
                'tipo_persona' => 'Jurídica',
                'tipo_documento' => 'RUC',
                'numero_documento' => '20678901234',
                'nombres' => null,
                'apellidos' => null,
                'razon_social' => 'Tecnología Avanzada S.A.C.',
                'representante_legal' => 'Luis Fernando Vargas Castillo',
                'telefono' => '+51 1 456 7890',
                'correo' => 'info@tecnologia-avanzada.com',
                'direccion' => 'Jr. Innovación 890, La Molina, Lima',
                'estado' => 'Inactivo',
                'creado_en' => now()->subMonths(12),
            ],
        ];

        foreach ($clientesPredefinidos as $cliente) {
            Cliente::create($cliente);
        }

        // Clientes aleatorios
        Cliente::factory()->count(10)->create();

        $this->command->info('Clientes creados correctamente');
        $this->command->info('Total clientes: ' . Cliente::count());
        
        $this->command->info('Distribucion por tipo:');
        $this->command->info('   Natural: ' . Cliente::where('tipo_persona', 'Natural')->count());
        $this->command->info('   Jurídica: ' . Cliente::where('tipo_persona', 'Jurídica')->count());
        
        $this->command->info('Distribucion por estado:');
        $this->command->info('   Activo: ' . Cliente::where('estado', 'Activo')->count());
        $this->command->info('   Inactivo: ' . Cliente::where('estado', 'Inactivo')->count());

        $this->command->info('Campos encriptados: numero_documento, telefono, correo, direccion, representante_legal');
    }
}