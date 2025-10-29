<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calendario', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 255);
            $table->text('descripcion')->nullable();
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin')->nullable();
            $table->string('tipo_evento', 50)->default('Otro');
            $table->string('estado', 50)->default('Pendiente');
            $table->string('color', 20)->default('#2b7bba');
            $table->unsignedBigInteger('caso_id')->nullable();
            $table->unsignedBigInteger('etapa_id')->nullable();
            $table->unsignedBigInteger('abogado_id')->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->dateTime('creado_en')->useCurrent();

            // Relaciones foráneas
            $table->foreign('caso_id')->references('id')->on('casos');
            $table->foreign('etapa_id')->references('id')->on('etapas_procesales');
            $table->foreign('abogado_id')->references('id')->on('usuarios');
            $table->foreign('cliente_id')->references('id')->on('clientes');
            $table->foreign('creado_por')->references('id')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendario');
    }
};
