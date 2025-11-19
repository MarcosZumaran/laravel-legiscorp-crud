<?php

namespace Database\Seeders;

use App\Models\MateriaCaso;
use App\Models\TiposCasos;
use Illuminate\Database\Seeder;

class TiposCasosSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener las materias por nombre para asignar correctamente
        $materiaCivil = MateriaCaso::where('nombre', 'Civil')->first();
        $materiaPenal = MateriaCaso::where('nombre', 'Penal')->first();
        $materiaLaboral = MateriaCaso::where('nombre', 'Laboral')->first();
        $materiaMercantil = MateriaCaso::where('nombre', 'Mercantil')->first();
        $materiaFamilia = MateriaCaso::where('nombre', 'Familia')->first();

        // Tipos de casos predefinidos
        $tiposPredefinidos = [
            // Civil
            [
                'materia_id' => $materiaCivil->id,
                'nombre' => 'Divorcio Voluntario',
                'descripcion' => 'Proceso de divorcio por mutuo acuerdo entre las partes',
            ],
            [
                'materia_id' => $materiaCivil->id,
                'nombre' => 'Divorcio Necessary',
                'descripcion' => 'Proceso de divorcio por causas específicas establecidas en la ley',
            ],
            [
                'materia_id' => $materiaCivil->id,
                'nombre' => 'Responsabilidad Civil',
                'descripcion' => 'Reparación de daños y perjuicios',
            ],
            
            // Penal
            [
                'materia_id' => $materiaPenal->id,
                'nombre' => 'Robo Simple',
                'descripcion' => 'Sustracción de bienes sin violencia ni intimidación',
            ],
            [
                'materia_id' => $materiaPenal->id,
                'nombre' => 'Homicidio Culposo',
                'descripcion' => 'Privación de la vida sin intención',
            ],
            [
                'materia_id' => $materiaPenal->id,
                'nombre' => 'Estafa',
                'descripcion' => 'Obtención ilegítima de bienes mediante engaño',
            ],
            
            // Laboral
            [
                'materia_id' => $materiaLaboral->id,
                'nombre' => 'Despido Injustificado',
                'descripcion' => 'Terminación de contrato laboral sin causa justa',
            ],
            [
                'materia_id' => $materiaLaboral->id,
                'nombre' => 'Acoso Laboral',
                'descripcion' => 'Hostigamiento y maltrato en el entorno laboral',
            ],
            [
                'materia_id' => $materiaLaboral->id,
                'nombre' => 'Horas Extraordinarias',
                'descripcion' => 'Reclamo por pago de horas extras no remuneradas',
            ],
            
            // Mercantil
            [
                'materia_id' => $materiaMercantil->id,
                'nombre' => 'Incumplimiento de Contrato',
                'descripcion' => 'Falta de cumplimiento de obligaciones contractuales',
            ],
            [
                'materia_id' => $materiaMercantil->id,
                'nombre' => 'Quiebra',
                'descripcion' => 'Proceso de insolvencia empresarial',
            ],
            
            // Familia
            [
                'materia_id' => $materiaFamilia->id,
                'nombre' => 'Patria Potestad',
                'descripcion' => 'Derechos y obligaciones sobre los hijos menores',
            ],
            [
                'materia_id' => $materiaFamilia->id,
                'nombre' => 'Alimentos',
                'descripcion' => 'Pensión alimenticia para hijos o cónyuge',
            ],
        ];

        foreach ($tiposPredefinidos as $tipo) {
            TiposCasos::create($tipo);
        }

        // Tipos de casos aleatorios
        TiposCasos::factory()->count(10)->create();

        $this->command->info('Tipos de casos creados correctamente');
        $this->command->info('Total: ' . TiposCasos::count() . ' tipos de casos');
        
        // Mostrar relación de materias con sus tipos
        $this->command->info('');
        $this->command->info('Relación Materias - Tipos de Casos:');
        foreach (MateriaCaso::withCount('tiposCasos')->get() as $materia) {
            $this->command->info("   {$materia->nombre}: {$materia->tipos_casos_count} tipos");
        }
    }
}