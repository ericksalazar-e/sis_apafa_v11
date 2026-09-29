<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('padres_familia', function (Blueprint $table) {
            $table->unique(['empresa_id', 'padre_dni'], 'unique_padre_empresa_dni');
        });

        Schema::table('estudiantes', function (Blueprint $table) {
            $table->unique(['empresa_id', 'dni'], 'unique_estudiante_empresa_dni');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('padres_familia', function (Blueprint $table) {
            $table->dropUnique('unique_padre_empresa_dni');
        });

        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropUnique('unique_estudiante_empresa_dni');
        });
    }
};
