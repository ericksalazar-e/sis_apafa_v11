<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios_turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('turno', 50); // Mañana, Tarde, Noche
            $table->time('hora_ingreso')->default('07:30:00');
            $table->time('hora_limite_puntual')->default('08:00:00');
            $table->time('hora_salida')->default('13:00:00');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'turno']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_turnos');
    }
};
