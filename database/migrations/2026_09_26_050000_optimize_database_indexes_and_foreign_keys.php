<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    public function up(): void
    {
        // 1. Claves foráneas sin índice
        Schema::table('faena_asistencias', function (Blueprint $table) {
            if (!$this->hasIndex('faena_asistencias', 'faena_asistencias_pago_id_index')) {
                $table->index('pago_id', 'faena_asistencias_pago_id_index');
            }
        });

        Schema::table('pagos', function (Blueprint $table) {
            if (!$this->hasIndex('pagos', 'pagos_faena_id_index')) {
                $table->index('faena_id', 'pagos_faena_id_index');
            }
            if (!$this->hasIndex('pagos', 'pagos_empresa_padre_index')) {
                $table->index(['empresa_id', 'padre_familia_id'], 'pagos_empresa_padre_index');
            }
        });

        Schema::table('votos_padron', function (Blueprint $table) {
            if (!$this->hasIndex('votos_padron', 'votos_padron_lista_electoral_id_index')) {
                $table->index('lista_electoral_id', 'votos_padron_lista_electoral_id_index');
            }
        });

        // 2. Índices compuestos de alto rendimiento para búsquedas multi-inquilino
        Schema::table('padres_familia', function (Blueprint $table) {
            if (!$this->hasIndex('padres_familia', 'padres_familia_empresa_dni_index')) {
                $table->index(['empresa_id', 'padre_dni'], 'padres_familia_empresa_dni_index');
            }
        });

        Schema::table('estudiantes', function (Blueprint $table) {
            if (!$this->hasIndex('estudiantes', 'estudiantes_empresa_dni_index')) {
                $table->index(['empresa_id', 'dni'], 'estudiantes_empresa_dni_index');
            }
            if (!$this->hasIndex('estudiantes', 'estudiantes_empresa_academic_index')) {
                $table->index(['empresa_id', 'nivel', 'grado', 'seccion'], 'estudiantes_empresa_academic_index');
            }
        });

        Schema::table('asistencia_estudiantes', function (Blueprint $table) {
            if (!$this->hasIndex('asistencia_estudiantes', 'asistencia_estudiantes_empresa_fecha_index')) {
                $table->index(['empresa_id', 'fecha'], 'asistencia_estudiantes_empresa_fecha_index');
            }
        });

        Schema::table('reuniones', function (Blueprint $table) {
            if (!$this->hasIndex('reuniones', 'reuniones_empresa_fecha_index')) {
                $table->index(['empresa_id', 'fecha'], 'reuniones_empresa_fecha_index');
            }
        });

        Schema::table('aportes', function (Blueprint $table) {
            if (!$this->hasIndex('aportes', 'aportes_empresa_fecha_index')) {
                $table->index(['empresa_id', 'fecha'], 'aportes_empresa_fecha_index');
            }
        });

        Schema::table('reunion_padres', function (Blueprint $table) {
            if (!$this->hasIndex('reunion_padres', 'reunion_padres_reunion_padre_index')) {
                $table->index(['reunion_id', 'padre_familia_id'], 'reunion_padres_reunion_padre_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('faena_asistencias', function (Blueprint $table) {
            if ($this->hasIndex('faena_asistencias', 'faena_asistencias_pago_id_index')) {
                $table->dropIndex('faena_asistencias_pago_id_index');
            }
        });
        Schema::table('pagos', function (Blueprint $table) {
            if ($this->hasIndex('pagos', 'pagos_faena_id_index')) {
                $table->dropIndex('pagos_faena_id_index');
            }
            if ($this->hasIndex('pagos', 'pagos_empresa_padre_index')) {
                $table->dropIndex('pagos_empresa_padre_index');
            }
        });
        Schema::table('votos_padron', function (Blueprint $table) {
            if ($this->hasIndex('votos_padron', 'votos_padron_lista_electoral_id_index')) {
                $table->dropIndex('votos_padron_lista_electoral_id_index');
            }
        });
        Schema::table('padres_familia', function (Blueprint $table) {
            if ($this->hasIndex('padres_familia', 'padres_familia_empresa_dni_index')) {
                $table->dropIndex('padres_familia_empresa_dni_index');
            }
        });
        Schema::table('estudiantes', function (Blueprint $table) {
            if ($this->hasIndex('estudiantes', 'estudiantes_empresa_dni_index')) {
                $table->dropIndex('estudiantes_empresa_dni_index');
            }
            if ($this->hasIndex('estudiantes', 'estudiantes_empresa_academic_index')) {
                $table->dropIndex('estudiantes_empresa_academic_index');
            }
        });
        Schema::table('asistencia_estudiantes', function (Blueprint $table) {
            if ($this->hasIndex('asistencia_estudiantes', 'asistencia_estudiantes_empresa_fecha_index')) {
                $table->dropIndex('asistencia_estudiantes_empresa_fecha_index');
            }
        });
        Schema::table('reuniones', function (Blueprint $table) {
            if ($this->hasIndex('reuniones', 'reuniones_empresa_fecha_index')) {
                $table->dropIndex('reuniones_empresa_fecha_index');
            }
        });
        Schema::table('aportes', function (Blueprint $table) {
            if ($this->hasIndex('aportes', 'aportes_empresa_fecha_index')) {
                $table->dropIndex('aportes_empresa_fecha_index');
            }
        });
        Schema::table('reunion_padres', function (Blueprint $table) {
            if ($this->hasIndex('reunion_padres', 'reunion_padres_reunion_padre_index')) {
                $table->dropIndex('reunion_padres_reunion_padre_index');
            }
        });
    }
};
