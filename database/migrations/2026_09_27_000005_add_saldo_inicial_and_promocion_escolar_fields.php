<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Campos de Saldo Inicial / Fondo de Apertura y Año Lectivo en Empresas
        Schema::table('empresas', function (Blueprint $table) {
            if (! Schema::hasColumn('empresas', 'saldo_inicial_caja')) {
                $table->decimal('saldo_inicial_caja', 12, 2)->default(0.00)->after('periodo_apafa');
            }
            if (! Schema::hasColumn('empresas', 'fecha_saldo_inicial')) {
                $table->date('fecha_saldo_inicial')->nullable()->after('saldo_inicial_caja');
            }
            if (! Schema::hasColumn('empresas', 'glosa_saldo_inicial')) {
                $table->string('glosa_saldo_inicial', 255)->nullable()->after('fecha_saldo_inicial');
            }
            if (! Schema::hasColumn('empresas', 'sustento_saldo_inicial')) {
                $table->string('sustento_saldo_inicial', 255)->nullable()->after('glosa_saldo_inicial');
            }
            if (! Schema::hasColumn('empresas', 'anio_lectivo_actual')) {
                $table->integer('anio_lectivo_actual')->default((int) date('Y'))->after('sustento_saldo_inicial');
            }
        });

        // 2. Regularización de pagos manuales previos en Pagos
        Schema::table('pagos', function (Blueprint $table) {
            if (! Schema::hasColumn('pagos', 'es_regularizacion')) {
                $table->boolean('es_regularizacion')->default(false)->after('tipo_comprobante');
            }
            if (! Schema::hasColumn('pagos', 'recibo_manual_referencia')) {
                $table->string('recibo_manual_referencia', 100)->nullable()->after('es_regularizacion');
            }
        });

        // 3. Estado de matrícula y seguimiento del año escolar en Estudiantes
        Schema::table('estudiantes', function (Blueprint $table) {
            if (! Schema::hasColumn('estudiantes', 'estado_matricula')) {
                $table->string('estado_matricula', 30)->default('MATRICULADO')->after('turno');
            }
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'saldo_inicial_caja',
                'fecha_saldo_inicial',
                'glosa_saldo_inicial',
                'sustento_saldo_inicial',
                'anio_lectivo_actual',
            ]);
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn([
                'es_regularizacion',
                'recibo_manual_referencia',
            ]);
        });

        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropColumn(['estado_matricula']);
        });
    }
};
