<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('padres_familia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('cargo_id')->nullable()->constrained('cargos')->nullOnDelete();
            $table->foreignId('tipo_consanguinidad_id')->nullable()->constrained('tipos_consanguinidad')->nullOnDelete();
            $table->string('padre_dni', 20)->index();
            $table->string('padre_nombres', 100);
            $table->string('padre_ape_pat', 100);
            $table->string('padre_ape_mat', 100)->nullable();
            $table->string('celular', 30)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->timestamp('fecha_creacion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('padres_familia');
    }
};
