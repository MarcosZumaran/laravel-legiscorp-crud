<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('casos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_caso', 50)->unique();
            $table->string('expediente_completo', 30)->unique();
            $table->char('secuencia', 5);
            $table->char('anio', 4);
            $table->char('indicador_fuero', 1);
            $table->char('codigo_organo', 4);
            $table->char('tipo_organo', 2);
            $table->char('especialidad', 2);
            $table->char('distrito', 2);
            $table->string('titulo', 255);
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('materia_id');
            $table->unsignedBigInteger('tipo_caso_id')->nullable();
            $table->string('estado', 50)->default('Abierto');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_cierre')->nullable();
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('abogado_id');
            $table->string('contraparte', 255)->nullable();

            $table->foreign('abogado_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('materia_id')->references('id')->on('materias_casos')->onDelete('cascade');
            $table->foreign('tipo_caso_id')->references('id')->on('tipos_casos')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('casos');
    }
};
