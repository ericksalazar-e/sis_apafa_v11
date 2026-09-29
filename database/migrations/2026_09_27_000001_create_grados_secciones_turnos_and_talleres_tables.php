<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Matriz de Turnos por Grado y Sección
        Schema::create('grados_secciones_turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nivel', 50); // Inicial, Primaria, Secundaria, CEBA
            $table->string('grado', 50); // 1ro, 2do, 3ro, etc.
            $table->string('seccion', 20); // A, B, C, Única
            $table->string('turno', 50)->default('Mañana'); // Mañana, Tarde, Noche
            $table->string('tutor', 150)->nullable();
            $table->string('aula', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'nivel', 'grado', 'seccion'], 'gst_empresa_nivel_grado_sec_unique');
        });

        // 2. Talleres Extracurriculares y Actividades Fuera de Turno (Contraturno)
        Schema::create('talleres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nombre', 150); // ej. "Taller de Robótica", "Banda de Música"
            $table->text('descripcion')->nullable();
            $table->string('nivel', 50)->default('Todos'); // Inicial, Primaria, Secundaria, Todos
            $table->string('grado', 50)->default('Todos'); // 1ro, 2do, etc., o Todos
            $table->string('seccion', 20)->default('Todas'); // A, B, etc., o Todas
            $table->string('turno', 50)->default('Tarde'); // Turno en que se dicta (Mañana, Tarde, Noche, Sábado)
            $table->time('hora_inicio')->default('14:30:00');
            $table->time('hora_limite_puntual')->default('14:45:00'); // Tolerancia
            $table->time('hora_fin')->default('16:30:00');
            $table->string('dias_semana', 255)->default('Lunes,Miércoles,Viernes'); // Días programados
            $table->string('docente_responsable', 150)->nullable();
            $table->string('lugar', 100)->nullable(); // Laboratorio, Cancha, etc.
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['empresa_id', 'nivel', 'grado', 'seccion'], 'talleres_empresa_academico_idx');
        });

        // 3. Campos en asistencia_estudiantes para soporte de Talleres / Contraturno
        Schema::table('asistencia_estudiantes', function (Blueprint $table) {
            $table->string('tipo_actividad', 50)->default('CLASE_REGULAR')->after('turno'); // CLASE_REGULAR, TALLER
            $table->foreignId('taller_id')->nullable()->after('tipo_actividad')->constrained('talleres')->nullOnDelete();
            $table->index(['empresa_id', 'tipo_actividad', 'fecha'], 'asist_tipo_act_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::table('asistencia_estudiantes', function (Blueprint $table) {
            $table->dropForeign(['taller_id']);
            $table->dropIndex('asist_tipo_act_fecha_idx');
            $table->dropColumn(['tipo_actividad', 'taller_id']);
        });

        Schema::dropIfExists('talleres');
        Schema::dropIfExists('grados_secciones_turnos');
    }
};
