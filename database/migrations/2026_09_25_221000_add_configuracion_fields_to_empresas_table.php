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
        Schema::table('empresas', function (Blueprint $table) {
            if (!Schema::hasColumn('empresas', 'ruc')) {
                $table->string('ruc', 11)->nullable()->after('cod_modular');
            }
            if (!Schema::hasColumn('empresas', 'periodo_apafa')) {
                $table->string('periodo_apafa', 50)->nullable()->default('2025 - 2026')->after('correo');
            }
            if (!Schema::hasColumn('empresas', 'lema')) {
                $table->string('lema', 255)->nullable()->after('periodo_apafa');
            }
            if (!Schema::hasColumn('empresas', 'nivel_educativo')) {
                $table->string('nivel_educativo', 100)->nullable()->default('Inicial, Primaria y Secundaria')->after('lema');
            }
            if (!Schema::hasColumn('empresas', 'director_nombre')) {
                $table->string('director_nombre', 150)->nullable()->after('nivel_educativo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'ruc',
                'periodo_apafa',
                'lema',
                'nivel_educativo',
                'director_nombre',
            ]);
        });
    }
};
