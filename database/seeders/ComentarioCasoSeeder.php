<?php

namespace Database\Seeders;

use App\Models\Caso;
use App\Models\ComentarioCaso;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class ComentarioCasoSeeder extends Seeder
{
    public function run(): void
    {
        $casos = Caso::all();
        $usuarios = Usuario::all();

        if ($casos->isEmpty() || $usuarios->isEmpty()) {
            $this->command->error('No se pueden crear comentarios sin casos y usuarios existentes');
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

        // Comentarios predefinidos
        $comentariosPredefinidos = [
            // Comentarios para caso de divorcio
            [
                'caso_id' => $casoDivorcio->id,
                'usuario_id' => $usuarioAbogado->id,
                'comentario' => 'Primera reunión con el cliente. Se explicaron los procedimientos y se recopiló documentación inicial.',
                'fecha' => now()->subMonths(3),
            ],
            [
                'caso_id' => $casoDivorcio->id,
                'usuario_id' => $usuarioAsistente->id,
                'comentario' => 'Documentación completa recibida. Se programó audiencia preliminar para el próximo mes.',
                'fecha' => now()->subMonths(2)->addDays(5),
            ],
            [
                'caso_id' => $casoDivorcio->id,
                'usuario_id' => $usuarioAbogado->id,
                'comentario' => 'Audiencia preliminar realizada. La contraparte se mostró receptiva a negociar.',
                'fecha' => now()->subMonths(2),
            ],
            // Comentarios para caso penal
            [
                'caso_id' => $casoPenal->id,
                'usuario_id' => $usuarioMaria->id,
                'comentario' => 'Cliente detenido preventivamente. Se presentó escrito de habeas corpus.',
                'fecha' => now()->subMonths(1),
            ],
            [
                'caso_id' => $casoPenal->id,
                'usuario_id' => $usuarioMaria->id,
                'comentario' => 'Habeas corpus concedido. Cliente en libertad mientras continúa el proceso.',
                'fecha' => now()->subMonths(1)->addDays(3),
            ],
            // Comentarios para caso laboral (cerrado)
            [
                'caso_id' => $casoLaboral->id,
                'usuario_id' => $usuarioAbogado->id,
                'comentario' => 'Demanda presentada exitosamente. Empresa notificada.',
                'fecha' => now()->subMonths(5),
            ],
            [
                'caso_id' => $casoLaboral->id,
                'usuario_id' => $usuarioAbogado->id,
                'comentario' => 'Audiencia de conciliación: no hubo acuerdo. Se procede a etapa probatoria.',
                'fecha' => now()->subMonths(4),
            ],
            [
                'caso_id' => $casoLaboral->id,
                'usuario_id' => $usuarioAbogado->id,
                'comentario' => 'Sentencia favorable. Cliente recibirá indemnización completa. Caso cerrado.',
                'fecha' => now()->subMonths(1),
            ],
            // Comentarios para caso mercantil
            [
                'caso_id' => $casoMercantil->id,
                'usuario_id' => $usuarioMaria->id,
                'comentario' => 'Análisis de contrato identificó cláusulas abusivas. Base sólida para la demanda.',
                'fecha' => now()->subMonths(2),
            ],
            [
                'caso_id' => $casoMercantil->id,
                'usuario_id' => $usuarioAdmin->id,
                'comentario' => 'Revisé la estrategia legal. Aprobado proceder con demanda por incumplimiento.',
                'fecha' => now()->subMonths(2)->addDays(2),
            ],
        ];

        foreach ($comentariosPredefinidos as $comentario) {
            ComentarioCaso::create($comentario);
        }

        // Comentarios aleatorios
        ComentarioCaso::factory()->count(15)->create();

        $this->command->info('Comentarios de casos creados correctamente');
        $this->command->info('Total comentarios: ' . ComentarioCaso::count());
        
        $this->command->info('Comentarios por caso:');
        foreach ($casos as $caso) {
            $count = ComentarioCaso::where('caso_id', $caso->id)->count();
            $this->command->info('   ' . $caso->codigo_caso . ': ' . $count . ' comentarios');
        }

        $this->command->info('Campo encriptado: comentario');
    }
}