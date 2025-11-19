<?php

namespace Database\Seeders;

use App\Models\Calendario;
use App\Models\Caso;
use App\Models\Cliente;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class CalendarioSeeder extends Seeder
{
    public function run(): void
    {
        $casos = Caso::all();
        $abogados = Usuario::where('rol', 'Abogado')->get();
        $clientes = Cliente::all();
        $usuarios = Usuario::all();

        if ($abogados->isEmpty() || $usuarios->isEmpty()) {
            $this->command->error('No se pueden crear eventos de calendario sin abogados y usuarios existentes');
            return;
        }

        $usuarioAdmin = Usuario::where('correo', 'admin@sistema.com')->first();
        $usuarioAbogado = Usuario::where('correo', 'carlos@abogados.com')->first();
        $usuarioAsistente = Usuario::where('correo', 'ana@abogados.com')->first();
        $usuarioMaria = Usuario::where('correo', 'maria@abogados.com')->first();

        $casoDivorcio = Caso::where('codigo_caso', 'CAS-2024-001')->first();
        $casoPenal = Caso::where('codigo_caso', 'CAS-2024-002')->first();
        $casoLaboral = Caso::where('codigo_caso', 'CAS-2024-003')->first();
        $casoMercantil = Caso::where('codigo_caso', 'CAS-2024-004')->first();

        $clientePrincipal = Cliente::first();

        // Eventos de calendario predefinidos
        $eventosPredefinidos = [
            // Audiencias
            [
                'titulo' => 'Audiencia Preliminar - Divorcio Contencioso',
                'descripcion' => 'Primera audiencia para tratar los términos del divorcio y custodia de menores',
                'fecha_inicio' => now()->addDays(5)->setHour(9)->setMinute(0),
                'fecha_fin' => now()->addDays(5)->setHour(11)->setMinute(0),
                'ubicacion' => 'Juzgado Primero de Familia, Sala 3, Piso 2',
                'tipo_evento' => 'Audiencia',
                'estado' => 'Pendiente',
                'color' => '#ff6b6b',
                'recurrente' => 'No',
                'caso_id' => $casoDivorcio->id,
                'abogado_id' => $usuarioAbogado->id,
                'cliente_id' => $clientePrincipal->id,
                'creado_por' => $usuarioAbogado->id,
                'expediente' => 'EXP-2024-001',
                'prioridad' => 'Alta',
                'creado_en' => now()->subDays(10),
            ],
            [
                'titulo' => 'Audiencia de Control - Caso Penal',
                'descripcion' => 'Control de detención y presentación de pruebas preliminares',
                'fecha_inicio' => now()->addDays(3)->setHour(14)->setMinute(30),
                'fecha_fin' => now()->addDays(3)->setHour(16)->setMinute(0),
                'ubicacion' => 'Juzgado Segundo Penal, Sala 1',
                'tipo_evento' => 'Audiencia',
                'estado' => 'Pendiente',
                'color' => '#ff6b6b',
                'recurrente' => 'No',
                'caso_id' => $casoPenal->id,
                'abogado_id' => $usuarioAbogado->id,
                'cliente_id' => $clientes->skip(1)->first()->id,
                'creado_por' => $usuarioAbogado->id,
                'expediente' => 'EXP-2024-002',
                'prioridad' => 'Urgente',
                'creado_en' => now()->subDays(8),
            ],
            // Reuniones
            [
                'titulo' => 'Reunión con Cliente - Actualización de Caso',
                'descripcion' => 'Reunión para informar al cliente sobre los avances en el caso mercantil y próximos pasos',
                'fecha_inicio' => now()->addDays(2)->setHour(10)->setMinute(0),
                'fecha_fin' => now()->addDays(2)->setHour(11)->setMinute(30),
                'ubicacion' => 'Oficinas Principal, Sala de Conferencias A',
                'tipo_evento' => 'Reunión',
                'estado' => 'Pendiente',
                'color' => '#3486bc',
                'recurrente' => 'No',
                'caso_id' => $casoMercantil->id,
                'abogado_id' => $usuarioMaria->id,
                'cliente_id' => $clientes->skip(3)->first()->id,
                'creado_por' => $usuarioAsistente->id,
                'expediente' => 'EXP-2024-004',
                'prioridad' => 'Media',
                'creado_en' => now()->subDays(5),
            ],
            // Plazos
            [
                'titulo' => 'Vencimiento de Plazo para Apelación',
                'descripcion' => 'Último día para presentar recurso de apelación en caso laboral cerrado',
                'fecha_inicio' => now()->addDays(7)->setHour(23)->setMinute(59),
                'fecha_fin' => now()->addDays(7)->setHour(23)->setMinute(59),
                'ubicacion' => null,
                'tipo_evento' => 'Plazo',
                'estado' => 'Pendiente',
                'color' => '#ffd43b',
                'recurrente' => 'No',
                'caso_id' => $casoLaboral->id,
                'abogado_id' => $usuarioAbogado->id,
                'cliente_id' => $clientes->skip(2)->first()->id,
                'creado_por' => $usuarioAdmin->id,
                'expediente' => 'EXP-2024-003',
                'prioridad' => 'Alta',
                'creado_en' => now()->subDays(15),
            ],
            // Entregas
            [
                'titulo' => 'Entrega de Documentos a Fiscalía',
                'descripcion' => 'Entrega de pruebas documentales y dictámenes periciales al ministerio público',
                'fecha_inicio' => now()->addDays(4)->setHour(8)->setMinute(30),
                'fecha_fin' => now()->addDays(4)->setHour(9)->setMinute(30),
                'ubicacion' => 'Fiscalía Provincial, Despacho 5, Edificio Judicial',
                'tipo_evento' => 'Entrega',
                'estado' => 'Pendiente',
                'color' => '#51cf66',
                'recurrente' => 'No',
                'caso_id' => $casoPenal->id,
                'abogado_id' => $usuarioAbogado->id,
                'cliente_id' => $clientes->skip(1)->first()->id,
                'creado_por' => $usuarioAsistente->id,
                'expediente' => 'EXP-2024-002',
                'prioridad' => 'Media',
                'creado_en' => now()->subDays(3),
            ],
            // Eventos recurrentes
            [
                'titulo' => 'Reunión Semanal de Equipo Legal',
                'descripcion' => 'Reunión semanal para revisar avances de casos y coordinar actividades',
                'fecha_inicio' => now()->next('Monday')->setHour(9)->setMinute(0),
                'fecha_fin' => now()->next('Monday')->setHour(10)->setMinute(30),
                'ubicacion' => 'Sala de Juntas Principal',
                'tipo_evento' => 'Reunión',
                'estado' => 'Pendiente',
                'color' => '#cc5de8',
                'recurrente' => 'Semanal',
                'caso_id' => null,
                'abogado_id' => null,
                'cliente_id' => null,
                'creado_por' => $usuarioAdmin->id,
                'expediente' => null,
                'prioridad' => 'Media',
                'creado_en' => now()->subDays(20),
            ],
            // Eventos pasados (completados)
            [
                'titulo' => 'Audiencia Inicial - Caso Laboral',
                'descripcion' => 'Primera audiencia para presentación de demanda laboral',
                'fecha_inicio' => now()->subDays(15)->setHour(10)->setMinute(0),
                'fecha_fin' => now()->subDays(15)->setHour(12)->setMinute(0),
                'ubicacion' => 'Juzgado Laboral Primero',
                'tipo_evento' => 'Audiencia',
                'estado' => 'Completado',
                'color' => '#51cf66',
                'recurrente' => 'No',
                'caso_id' => $casoLaboral->id,
                'abogado_id' => $usuarioAbogado->id,
                'cliente_id' => $clientes->skip(2)->first()->id,
                'creado_por' => $usuarioAbogado->id,
                'expediente' => 'EXP-2024-003',
                'prioridad' => 'Alta',
                'creado_en' => now()->subDays(20),
            ],
        ];

        foreach ($eventosPredefinidos as $evento) {
            Calendario::create($evento);
        }

        // Eventos aleatorios
        Calendario::factory()->count(12)->create();

        $this->command->info('Eventos de calendario creados correctamente');
        $this->command->info('Total eventos: ' . Calendario::count());
        
        $this->command->info('Distribución por tipo:');
        foreach (Calendario::select('tipo_evento')->groupBy('tipo_evento')->get() as $tipo) {
            $count = Calendario::where('tipo_evento', $tipo->tipo_evento)->count();
            $this->command->info('   ' . $tipo->tipo_evento . ': ' . $count . ' eventos');
        }

        $this->command->info('Distribución por estado:');
        foreach (Calendario::select('estado')->groupBy('estado')->get() as $estado) {
            $count = Calendario::where('estado', $estado->estado)->count();
            $this->command->info('   ' . $estado->estado . ': ' . $count . ' eventos');
        }

        $this->command->info('Distribución por prioridad:');
        foreach (Calendario::select('prioridad')->groupBy('prioridad')->get() as $prioridad) {
            $count = Calendario::where('prioridad', $prioridad->prioridad)->count();
            $this->command->info('   ' . $prioridad->prioridad . ': ' . $count . ' eventos');
        }

        $this->command->info('Campos encriptados: descripcion, ubicacion');
    }
}