<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->decimal('monto', 10, 2);
            $table->dateTime('fecha')->nullable();
            $table->timestamps();
        });

        Schema::create('aporte_padres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('aporte_id')->constrained('aportes')->cascadeOnDelete();
            $table->foreignId('padre_familia_id')->constrained('padres_familia')->cascadeOnDelete();
            $table->decimal('monto_aporte', 10, 2);
            $table->decimal('monto_aportado', 10, 2)->default(0);
            $table->decimal('debe', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aporte_padres');
        Schema::dropIfExists('aportes');
    }
};
