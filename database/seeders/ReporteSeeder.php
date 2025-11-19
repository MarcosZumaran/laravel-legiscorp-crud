<?php

namespace Database\Seeders;

use App\Models\Reporte;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class ReporteSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener algunos usuarios para asignar
        $usuarioAdmin = Usuario::where('correo', 'admin@sistema.com')->first();
        $usuarioAbogado = Usuario::where('correo', 'carlos@abogados.com')->first();
        $usuarioAsistente = Usuario::where('correo', 'ana@abogados.com')->first();
        $usuarioMaria = Usuario::where('correo', 'maria@abogados.com')->first();

        // Reportes predefinidos
        $reportesPredefinidos = [
            [
                'titulo' => 'Reporte General de Casos Activos',
                'tipo_reporte' => 'General',
                'descripcion' => 'Reporte completo de todos los casos activos en el sistema con metricas importantes',
                'parametros' => json_encode([
                    'estado' => 'activo',
                    'incluir_metricas' => true,
                    'rango_fechas' => 'ultimos_30_dias'
                ]),
                'fecha_generacion' => now()->subDays(2),
                'generado_por' => $usuarioAdmin->id,
            ],
            [
                'titulo' => 'Calendario de Audiencias del Mes',
                'tipo_reporte' => 'Calendario', 
                'descripcion' => 'Reporte de todas las audiencias programadas para el mes actual organizadas por fecha',
                'parametros' => json_encode([
                    'mes' => now()->month,
                    'ano' => now()->year,
                    'tipo_evento' => 'audiencia'
                ]),
                'fecha_generacion' => now()->subDays(5),
                'generado_por' => $usuarioAbogado->id,
            ],
            [
                'titulo' => 'Documentos Pendientes de Revision',
                'tipo_reporte' => 'Documentos',
                'descripcion' => 'Listado de documentos que requieren revision y aprobacion por parte del area legal',
                'parametros' => json_encode([
                    'estado_documento' => 'pendiente',
                    'prioridad' => 'alta',
                    'dias_vencimiento' => 7
                ]),
                'fecha_generacion' => now()->subDays(1),
                'generado_por' => $usuarioAsistente->id,
            ],
            [
                'titulo' => 'Reporte de Clientes por Estado',
                'tipo_reporte' => 'Clientes',
                'descripcion' => 'Analisis de clientes agrupados por estado geografico y tipo de caso',
                'parametros' => json_encode([
                    'agrupar_por' => 'estado',
                    'incluir_contactos' => false,
                    'filtro_activos' => true
                ]),
                'fecha_generacion' => now()->subDays(7),
                'generado_por' => $usuarioAdmin->id,
            ],
            [
                'titulo' => 'Casos por Materia Legal',
                'tipo_reporte' => 'Casos',
                'descripcion' => 'Distribucion de casos segun la materia legal asignada y estado procesal',
                'parametros' => json_encode([
                    'agrupar_por' => 'materia',
                    'incluir_estadisticas' => true,
                    'mostrar_vencidos' => true
                ]),
                'fecha_generacion' => now()->subDays(3),
                'generado_por' => $usuarioAbogado->id,
            ],
            [
                'titulo' => 'Reporte de Productividad Mensual',
                'tipo_reporte' => 'General',
                'descripcion' => null, // Probando descripcion NULL
                'parametros' => json_encode([
                    'periodo' => 'mensual',
                    'metricas' => ['casos_cerrados', 'documentos_generados', 'audiencias_asistidas']
                ]),
                'fecha_generacion' => now()->subDays(10),
                'generado_por' => $usuarioMaria->id,
            ],
        ];

        foreach ($reportesPredefinidos as $reporte) {
            Reporte::create($reporte);
        }

        // Reportes aleatorios para pruebas
        Reporte::factory()->count(10)->create();

        $this->command->info('Reportes creados correctamente');
        $this->command->info('Total reportes: ' . Reporte::count());
        
        // Mostrar distribucion por tipo
        $this->command->info('Distribucion por tipo:');
        foreach (Reporte::select('tipo_reporte')->groupBy('tipo_reporte')->get() as $tipo) {
            $count = Reporte::where('tipo_reporte', $tipo->tipo_reporte)->count();
            $this->command->info("   " . $tipo->tipo_reporte . ": " . $count . " reportes");
        }

        // Mostrar encriptacion
        $this->command->info('Campos encriptados: descripcion, parametros');
    }
}