<?php

namespace Database\Seeders;

use App\Models\Caso;
use App\Models\Cliente;
use App\Models\MateriaCaso;
use App\Models\TiposCasos;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class CasoSeeder extends Seeder
{
    public function run(): void
    {
        $abogados = Usuario::where('rol', 'Abogado')->get();
        $clientes = Cliente::all();
        $materias = MateriaCaso::all();
        $tiposCasos = TiposCasos::all();

        if ($abogados->isEmpty() || $clientes->isEmpty() || $materias->isEmpty()) {
            $this->command->error('No se pueden crear casos sin abogados, clientes y materias existentes');
            return;
        }

        $usuarioAbogado = Usuario::where('correo', 'carlos@abogados.com')->first();
        $usuarioMaria = Usuario::where('correo', 'maria@abogados.com')->first();
        $clientePrincipal = Cliente::first();

        // Casos predefinidos
        $casosPredefinidos = [
            [
                'codigo_caso' => 'CAS-2024-001',
                'numero_expediente' => 'EXP-2024-001',
                'numero_carpeta_fiscal' => 'CPF-2024-001',
                'titulo' => 'Caso de Divorcio Contencioso',
                'descripcion' => 'Proceso de divorcio por causas específicas, incluye disputa por custodia y bienes',
                'materia_id' => $materias->where('nombre', 'Civil')->first()->id,
                'tipo_caso_id' => $tiposCasos->where('nombre', 'Divorcio Necessary')->first()->id,
                'estado' => 'En Proceso',
                'fecha_inicio' => now()->subMonths(3),
                'cliente_id' => $clientePrincipal->id,
                'abogado_id' => $usuarioAbogado->id,
                'contraparte' => 'María González',
                'juzgado' => 'Juzgado Primero de Familia',
                'fiscal' => null,
                'creado_en' => now()->subMonths(3),
            ],
            [
                'codigo_caso' => 'CAS-2024-002',
                'numero_expediente' => 'EXP-2024-002',
                'numero_carpeta_fiscal' => 'CPF-2024-002',
                'titulo' => 'Defensa por Robo Calificado',
                'descripcion' => 'Caso penal por robo calificado con agravantes, cliente acusado injustamente',
                'materia_id' => $materias->where('nombre', 'Penal')->first()->id,
                'tipo_caso_id' => $tiposCasos->where('nombre', 'Robo Simple')->first()->id,
                'estado' => 'Abierto',
                'fecha_inicio' => now()->subMonths(1),
                'cliente_id' => $clientes->skip(1)->first()->id,
                'abogado_id' => $usuarioMaria->id,
                'contraparte' => 'Ministerio Público',
                'juzgado' => 'Juzgado Segundo Penal',
                'fiscal' => 'Dr. Roberto Mendoza',
                'creado_en' => now()->subMonths(1),
            ],
            [
                'codigo_caso' => 'CAS-2024-003',
                'numero_expediente' => 'EXP-2024-003',
                'numero_carpeta_fiscal' => null,
                'titulo' => 'Reclamación por Despido Injustificado',
                'descripcion' => 'Trabajador despedido sin causa justa, reclamo por indemnización y reinstalación',
                'materia_id' => $materias->where('nombre', 'Laboral')->first()->id,
                'tipo_caso_id' => $tiposCasos->where('nombre', 'Despido Injustificado')->first()->id,
                'estado' => 'Cerrado',
                'fecha_inicio' => now()->subMonths(6),
                'fecha_cierre' => now()->subMonths(1),
                'cliente_id' => $clientes->skip(2)->first()->id,
                'abogado_id' => $usuarioAbogado->id,
                'contraparte' => 'Empresa Industrial S.A.',
                'juzgado' => 'Juzgado Laboral Primero',
                'fiscal' => null,
                'creado_en' => now()->subMonths(6),
            ],
            [
                'codigo_caso' => 'CAS-2024-004',
                'numero_expediente' => 'EXP-2024-004',
                'numero_carpeta_fiscal' => null,
                'titulo' => 'Incumplimiento de Contrato de Servicios',
                'descripcion' => 'Cliente no recibió servicios contratados, demanda por incumplimiento contractual',
                'materia_id' => $materias->where('nombre', 'Mercantil')->first()->id,
                'tipo_caso_id' => $tiposCasos->where('nombre', 'Incumplimiento de Contrato')->first()->id,
                'estado' => 'En Proceso',
                'fecha_inicio' => now()->subMonths(2),
                'cliente_id' => $clientes->skip(3)->first()->id,
                'abogado_id' => $usuarioMaria->id,
                'contraparte' => 'Servicios Profesionales Ltda.',
                'juzgado' => 'Juzgado Mercantil Segundo',
                'fiscal' => null,
                'creado_en' => now()->subMonths(2),
            ],
        ];

        foreach ($casosPredefinidos as $caso) {
            Caso::create($caso);
        }

        // Casos aleatorios
        Caso::factory()->count(8)->create();

        $this->command->info('Casos creados correctamente');
        $this->command->info('Total casos: ' . Caso::count());
        
        $this->command->info('Distribucion por estado:');
        foreach (Caso::select('estado')->groupBy('estado')->get() as $estado) {
            $count = Caso::where('estado', $estado->estado)->count();
            $this->command->info('   ' . $estado->estado . ': ' . $count . ' casos');
        }

        $this->command->info('Campos encriptados: descripcion, contraparte, juzgado, fiscal');
    }
}