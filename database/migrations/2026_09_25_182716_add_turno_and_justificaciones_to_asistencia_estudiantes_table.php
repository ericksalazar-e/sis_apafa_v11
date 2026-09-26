<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asistencia_estudiantes', function (Blueprint $table) {
            $table->string('turno', 50)->nullable()->after('hora_salida');
            $table->boolean('justificado')->default(false)->after('estado');
            $table->string('motivo_justificacion', 255)->nullable()->after('justificado');
            $table->string('documento_sustento', 255)->nullable()->after('motivo_justificacion');
            $table->timestamp('fecha_justificacion')->nullable()->after('documento_sustento');
        });
    }

    public function down(): void
    {
        Schema::table('asistencia_estudiantes', function (Blueprint $table) {
            $table->dropColumn([
                'turno',
                'justificado',
                'motivo_justificacion',
                'documento_sustento',
                'fecha_justificacion',
            ]);
        });
    }
};
