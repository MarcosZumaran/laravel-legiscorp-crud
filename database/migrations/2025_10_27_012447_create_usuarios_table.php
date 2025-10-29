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
        Schema::create('usuarios', function (Blueprint $table) {
            // id autoincremental (IDENTITY en SQL Server)
            $table->id();

            // columnas principales
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('correo', 150)->unique();
            $table->string('password', 255);

            // columna 'rol' con valor por defecto y check constraint
            $table->string('rol', 50)->default('Asistente');

            // columna de fecha de creación con valor por defecto
            $table->dateTime('creado_en')->default(DB::raw('GETDATE()'));

            // constraint tipo CHECK (rol válido)
            $table->check("rol IN ('Asistente', 'Abogado', 'Administrador')");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
