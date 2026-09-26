<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->string('grado', 50)->default('1ro')->after('ape_mat');
            $table->string('seccion', 10)->default('A')->after('grado');
            $table->string('nivel', 50)->default('Primaria')->after('seccion');
            $table->string('turno', 50)->default('Mañana')->after('nivel');
            $table->string('correlativo', 20)->nullable()->after('turno');
        });
    }

    public function down(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropColumn(['grado', 'seccion', 'nivel', 'turno', 'correlativo']);
        });
    }
};
