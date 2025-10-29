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
        Schema::create('reportes', function (Blueprint $table) {
            // ID autoincremental (IDENTITY en SQL Server)
            $table->id();

            // Campos principales
            $table->string('titulo', 150);
            $table->string('tipo_reporte', 50);
            $table->text('descripcion')->nullable();
            $table->text('parametros')->nullable(); // NVARCHAR(MAX) equivale a text en Laravel

            // Fecha de generación con valor por defecto GETDATE()
            $table->dateTime('fecha_generacion')->default(DB::raw('GETDATE()'));

            // Clave foránea al usuario que generó el reporte
            $table->unsignedBigInteger('generado_por');

            // Restricción CHECK para validar el tipo de reporte
            $table->check("tipo_reporte IN ('General', 'Calendario', 'Documentos', 'Clientes', 'Casos')");

            // Definición de la relación foránea
            $table->foreign('generado_por')
                  ->references('id')
                  ->on('usuarios')
                  ->onDelete('cascade'); // puedes quitarlo si no quieres borrado en cascada
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};
