<?php

namespace Database\Seeders;

use App\Models\Documento;
use App\Models\DocumentoCompartido;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class DocumentoCompartidoSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = Usuario::all();
        $documentos = Documento::where('es_carpeta', false)->get();

        if ($usuarios->isEmpty() || $documentos->isEmpty()) {
            $this->command->error('No se pueden crear documentos compartidos sin usuarios y documentos existentes');
            return;
        }

        $usuarioAdmin = Usuario::where('correo', 'admin@sistema.com')->first();
        $usuarioAbogado = Usuario::where('correo', 'carlos@abogados.com')->first();
        $usuarioAsistente = Usuario::where('correo', 'ana@abogados.com')->first();
        $usuarioMaria = Usuario::where('correo', 'maria@abogados.com')->first();

        $documentoPoliticas = Documento::where('nombre_archivo', 'politicas_empresa.pdf')->first();
        $documentoContrato = Documento::where('nombre_archivo', 'contrato_servicios_abc.pdf')->first();
        $documentoEvidencia = Documento::where('nombre_archivo', 'fotografia_evidencia_1.jpg')->first();
        $documentoSentencia = Documento::where('nombre_archivo', 'sentencia_caso_123.pdf')->first();

        $compartidosPredefinidos = [
            // Admin comparte políticas con todos los abogados
            [
                'documento_id' => $documentoPoliticas->id,
                'compartido_con_usuario_id' => null,
                'compartido_con_rol' => 'Abogado',
                'permisos' => 'lectura',
                'fecha_compartido' => now()->subDays(10),
                'compartido_por' => $usuarioAdmin->id,
            ],
            // Admin comparte políticas con asistentes
            [
                'documento_id' => $documentoPoliticas->id,
                'compartido_con_usuario_id' => null,
                'compartido_con_rol' => 'Asistente',
                'permisos' => 'lectura',
                'fecha_compartido' => now()->subDays(10),
                'compartido_por' => $usuarioAdmin->id,
            ],
            // Abogado comparte contrato con asistente específico
            [
                'documento_id' => $documentoContrato->id,
                'compartido_con_usuario_id' => $usuarioAsistente->id,
                'compartido_con_rol' => null,
                'permisos' => 'lectura',
                'fecha_compartido' => now()->subDays(5),
                'compartido_por' => $usuarioAbogado->id,
            ],
            // Abogado comparte evidencia con otro abogado
            [
                'documento_id' => $documentoEvidencia->id,
                'compartido_con_usuario_id' => $usuarioMaria->id,
                'compartido_con_rol' => null,
                'permisos' => 'escritura',
                'fecha_compartido' => now()->subDays(3),
                'compartido_por' => $usuarioAbogado->id,
            ],
            // Asistente comparte sentencia con admin
            [
                'documento_id' => $documentoSentencia->id,
                'compartido_con_usuario_id' => $usuarioAdmin->id,
                'compartido_con_rol' => null,
                'permisos' => 'lectura',
                'fecha_compartido' => now()->subDays(2),
                'compartido_por' => $usuarioAsistente->id,
            ],
            // Admin comparte manual con rol específico (solo lectura)
            [
                'documento_id' => Documento::where('nombre_archivo', 'manual_procedimientos.docx')->first()->id,
                'compartido_con_usuario_id' => null,
                'compartido_con_rol' => 'Asistente',
                'permisos' => 'lectura',
                'fecha_compartido' => now()->subDays(7),
                'compartido_por' => $usuarioAdmin->id,
            ],
        ];

        foreach ($compartidosPredefinidos as $compartido) {
            DocumentoCompartido::create($compartido);
        }

        // Documentos compartidos aleatorios
        DocumentoCompartido::factory()->count(12)->create();

        $this->command->info('Documentos compartidos creados correctamente');
        $this->command->info('Total documentos compartidos: ' . DocumentoCompartido::count());
        
        $this->command->info('Estadisticas de comparticion:');
        $this->command->info('   Por usuario: ' . DocumentoCompartido::whereNotNull('compartido_con_usuario_id')->count());
        $this->command->info('   Por rol: ' . DocumentoCompartido::whereNotNull('compartido_con_rol')->count());
        $this->command->info('   Permisos lectura: ' . DocumentoCompartido::where('permisos', 'lectura')->count());
        $this->command->info('   Permisos escritura: ' . DocumentoCompartido::where('permisos', 'escritura')->count());
    }
}