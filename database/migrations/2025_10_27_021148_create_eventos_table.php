<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            // ID principal
            $table->id();

            // Campos principales
            $table->string('titulo', 255);
            $table->text('descripcion')->nullable();

            // Fechas
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin')->nullable();

            // Otros datos
            $table->string('ubicacion', 255)->nullable();
            $table->string('color', 20)->default('#3486bc');
            $table->string('tipo_evento', 50)->default('Otro');
            $table->string('recurrente', 50)->default('No');
            $table->string('expediente', 30)->nullable();

            // Relaciones
            $table->unsignedBigInteger('caso_id')->nullable();
            $table->unsignedBigInteger('etapa_id')->nullable();
            $table->unsignedBigInteger('creado_por');

            // Fecha de creación
            $table->dateTime('creado_en')->default(DB::raw('GETDATE()'));

            // Restricciones CHECK
            $table->check("recurrente IN ('Anual', 'Mensual', 'Semanal', 'Diario', 'No')");
            $table->check("tipo_evento IN ('Otro', 'Entrega', 'Plazo', 'Reunión', 'Audiencia')");

            // Claves foráneas
            $table->foreign('caso_id')->references('id')->on('casos');
            $table->foreign('etapa_id')->references('id')->on('etapas_procesales');
            $table->foreign('creado_por')->references('id')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
