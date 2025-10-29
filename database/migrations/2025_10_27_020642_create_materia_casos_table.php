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
        Schema::create('materias_casos', function (Blueprint $table) {
            // ID autoincremental (IDENTITY en SQL Server)
            $table->id();

            // Campos de la tabla
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable(); // VARCHAR(MAX) → text en Laravel
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materias_casos');
    }
};
