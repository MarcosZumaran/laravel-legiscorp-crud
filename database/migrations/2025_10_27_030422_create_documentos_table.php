<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_archivo', 255);
            $table->string('tipo_archivo', 50)->nullable();
            $table->string('ruta', 255);
            $table->text('descripcion')->nullable();
            $table->string('expediente', 30)->nullable();
            $table->dateTime('fecha_subida')->default(DB::raw('getdate()'));
            $table->foreignId('subido_por')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->foreignId('caso_id')->nullable()->constrained('casos')->onDelete('set null');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->string('categoria', 50)->default('General');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
