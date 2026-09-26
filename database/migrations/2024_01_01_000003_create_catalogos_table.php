<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->cascadeOnDelete();
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('tipos_consanguinidad', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('estados_reunion', function (Blueprint $table) {
            $table->id();
            $table->string('estado', 50);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estados_reunion');
        Schema::dropIfExists('tipos_consanguinidad');
        Schema::dropIfExists('cargos');
    }
};
