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
        Schema::create('tipos_casos', function (Blueprint $table) {
            // id autoincremental (IDENTITY en SQL Server)
            $table->id();

            // Clave foránea
            $table->unsignedBigInteger('materia_id');

            // Campos principales
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();

            // Definición de la relación (foreign key)
            $table->foreign('materia_id')
                  ->references('id')
                  ->on('materias_casos')
                  ->onDelete('cascade'); // opcional, puedes quitarlo si tu tabla no lo usa
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_casos');
    }
};
