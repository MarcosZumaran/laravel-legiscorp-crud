<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etapas_procesales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_caso_id')->nullable()->constrained('tipos_casos')->onDelete('set null');
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->integer('orden')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etapas_procesales');
    }
};
