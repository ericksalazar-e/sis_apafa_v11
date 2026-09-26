<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencia_estudiantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->date('fecha')->index();
            $table->time('hora_ingreso')->nullable();
            $table->time('hora_salida')->nullable();
            $table->string('estado', 50)->default('PUNTUAL'); // PUNTUAL, TARDANZA, FALTA
            $table->string('observacion', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencia_estudiantes');
    }
};
