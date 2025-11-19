<?php

namespace Database\Seeders;

use App\Models\Documento;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class DocumentoSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = Usuario::all();
        
        if ($usuarios->isEmpty()) {
            $this->command->error('No se pueden crear documentos sin usuarios existentes');
            return;
        }

        $usuarioAdmin = Usuario::where('correo', 'admin@sistema.com')->first();
        $usuarioAbogado = Usuario::where('correo', 'carlos@abogados.com')->first();
        $usuarioAsistente = Usuario::where('correo', 'ana@abogados.com')->first();

        // Crear carpetas principales
        $carpetasPrincipales = [
            [
                'nombre_archivo' => 'Documentos Generales',
                'ruta' => '/documentos-generales',
                'descripcion' => 'Carpeta para documentos generales de la firma',
                'es_carpeta' => true,
                'es_publico' => true,
                'categoria' => 'General',
                'subido_por' => $usuarioAdmin->id,
            ],
            [
                'nombre_archivo' => 'Contratos',
                'ruta' => '/contratos',
                'descripcion' => 'Carpeta para almacenar todos los contratos',
                'es_carpeta' => true,
                'es_publico' => false,
                'categoria' => 'Contrato',
                'subido_por' => $usuarioAdmin->id,
            ],
            [
                'nombre_archivo' => 'Evidencias',
                'ruta' => '/evidencias',
                'descripcion' => 'Carpeta para evidencias de casos',
                'es_carpeta' => true,
                'es_publico' => false,
                'categoria' => 'Evidencia',
                'subido_por' => $usuarioAbogado->id,
            ],
        ];

        $carpetasCreadas = [];
        foreach ($carpetasPrincipales as $carpeta) {
            $carpetasCreadas[] = Documento::create($carpeta);
        }

        // Documentos predefinidos
        $documentosPredefinidos = [
            // Documentos en carpeta general
            [
                'nombre_archivo' => 'politicas_empresa.pdf',
                'tipo_archivo' => 'pdf',
                'ruta' => '/documentos-generales/politicas_empresa.pdf',
                'descripcion' => 'Políticas internas de la firma legal',
                'expediente' => 'POL-2024-01',
                'es_carpeta' => false,
                'es_publico' => true,
                'categoria' => 'General',
                'tamano_bytes' => 2048576,
                'subido_por' => $usuarioAdmin->id,
                'etiquetas' => 'politicas,interno,administracion',
            ],
            [
                'nombre_archivo' => 'manual_procedimientos.docx',
                'tipo_archivo' => 'docx',
                'ruta' => '/documentos-generales/manual_procedimientos.docx',
                'descripcion' => 'Manual de procedimientos legales',
                'es_carpeta' => false,
                'es_publico' => false,
                'categoria' => 'General',
                'tamano_bytes' => 1048576,
                'subido_por' => $usuarioAdmin->id,
                'etiquetas' => 'manual,procedimientos,legal',
            ],
            // Contratos
            [
                'nombre_archivo' => 'contrato_servicios_abc.pdf',
                'tipo_archivo' => 'pdf',
                'ruta' => '/contratos/contrato_servicios_abc.pdf',
                'descripcion' => 'Contrato de prestación de servicios con Empresa ABC',
                'expediente' => 'CTR-2024-015',
                'es_carpeta' => false,
                'es_publico' => false,
                'categoria' => 'Contrato',
                'tamano_bytes' => 3097152,
                'subido_por' => $usuarioAbogado->id,
                'etiquetas' => 'contrato,servicios,empresa-abc',
            ],
            // Evidencias
            [
                'nombre_archivo' => 'fotografia_evidencia_1.jpg',
                'tipo_archivo' => 'jpg',
                'ruta' => '/evidencias/fotografia_evidencia_1.jpg',
                'descripcion' => 'Fotografía como evidencia para caso de responsabilidad civil',
                'es_carpeta' => false,
                'es_publico' => false,
                'categoria' => 'Evidencia',
                'tamano_bytes' => 5242880,
                'subido_por' => $usuarioAsistente->id,
                'etiquetas' => 'evidencia,fotografia,caso-civil',
            ],
            [
                'nombre_archivo' => 'dictamen_pericial.pdf',
                'tipo_archivo' => 'pdf',
                'ruta' => '/evidencias/dictamen_pericial.pdf',
                'descripcion' => 'Dictamen pericial para caso penal',
                'expediente' => 'PER-2024-008',
                'es_carpeta' => false,
                'es_publico' => false,
                'categoria' => 'Evidencia',
                'tamano_bytes' => 4194304,
                'subido_por' => $usuarioAbogado->id,
                'etiquetas' => 'dictamen,pericial,penal',
            ],
            // Sentencias
            [
                'nombre_archivo' => 'sentencia_caso_123.pdf',
                'tipo_archivo' => 'pdf',
                'ruta' => '/sentencias/sentencia_caso_123.pdf',
                'descripcion' => 'Sentencia definitiva caso laboral 123/2024',
                'expediente' => 'SEN-2024-045',
                'es_carpeta' => false,
                'es_publico' => false,
                'categoria' => 'Sentencia',
                'tamano_bytes' => 1572864,
                'subido_por' => $usuarioAbogado->id,
                'etiquetas' => 'sentencia,laboral,definitiva',
            ],
        ];

        foreach ($documentosPredefinidos as $documento) {
            Documento::create($documento);
        }

        // Documentos aleatorios
        Documento::factory()->count(20)->create();

        $this->command->info('Documentos creados correctamente');
        $this->command->info('Total documentos: ' . Documento::count());
        $this->command->info('Carpetas: ' . Documento::where('es_carpeta', true)->count());
        $this->command->info('Archivos: ' . Documento::where('es_carpeta', false)->count());
        
        // Mostrar distribución por categoría
        $this->command->info('Distribucion por categoria:');
        foreach (Documento::select('categoria')->groupBy('categoria')->get() as $categoria) {
            $count = Documento::where('categoria', $categoria->categoria)->count();
            $this->command->info('   ' . $categoria->categoria . ': ' . $count . ' documentos');
        }

        $this->command->info('Campos encriptados: nombre_archivo, descripcion, ruta');
    }
}