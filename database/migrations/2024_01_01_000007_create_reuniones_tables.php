<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reuniones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('estado_reunion_id')->nullable()->constrained('estados_reunion')->nullOnDelete();
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->decimal('multa_precio', 10, 2)->default(0);
            $table->date('fecha')->nullable();
            $table->time('hora')->nullable();
            $table->timestamps();
        });

        Schema::create('reunion_padres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('reunion_id')->constrained('reuniones')->cascadeOnDelete();
            $table->foreignId('padre_familia_id')->constrained('padres_familia')->cascadeOnDelete();
            $table->dateTime('asistencia')->nullable();
            $table->dateTime('asistencia_salida')->nullable();
            $table->string('detalles', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reunion_padres');
        Schema::dropIfExists('reuniones');
    }
};
