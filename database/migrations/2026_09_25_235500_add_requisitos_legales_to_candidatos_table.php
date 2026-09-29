<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Requisitos mínimos de elegibilidad para Junta Directiva y Vigilancia APAFA (Ley N° 28628 Art. 45)
     */
    public function up(): void
    {
        Schema::table('candidatos_electorales', function (Blueprint $table) {
            if (! Schema::hasColumn('candidatos_electorales', 'al_dia_pagos')) {
                $table->boolean('al_dia_pagos')->default(true)->after('cumple_requisitos');
            }
            if (! Schema::hasColumn('candidatos_electorales', 'declara_sin_antecedentes')) {
                $table->boolean('declara_sin_antecedentes')->default(true)->after('al_dia_pagos');
            }
            if (! Schema::hasColumn('candidatos_electorales', 'declara_no_labora_ie')) {
                $table->boolean('declara_no_labora_ie')->default(true)->after('declara_sin_antecedentes');
            }
            if (! Schema::hasColumn('candidatos_electorales', 'declara_no_removido')) {
                $table->boolean('declara_no_removido')->default(true)->after('declara_no_labora_ie');
            }
            if (! Schema::hasColumn('candidatos_electorales', 'grado_estudiante_valido')) {
                $table->boolean('grado_estudiante_valido')->default(true)->after('declara_no_removido');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidatos_electorales', function (Blueprint $table) {
            $table->dropColumn([
                'al_dia_pagos',
                'declara_sin_antecedentes',
                'declara_no_labora_ie',
                'declara_no_removido',
                'grado_estudiante_valido',
            ]);
        });
    }
};
