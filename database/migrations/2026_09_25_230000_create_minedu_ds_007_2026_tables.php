<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Implementación D.S. N.º 007-2026-MINEDU
     */
    public function up(): void
    {
        // 1. Campos de Fraccionamiento y Compensación en tabla pagos
        Schema::table('pagos', function (Blueprint $table) {
            if (!Schema::hasColumn('pagos', 'modalidad')) {
                $table->string('modalidad', 50)->default('CONTADO')->after('monto_pago'); // CONTADO, FRACCIONADO, COMPENSACION_FAENA
            }
            if (!Schema::hasColumn('pagos', 'numero_cuota')) {
                $table->integer('numero_cuota')->default(1)->after('modalidad');
            }
            if (!Schema::hasColumn('pagos', 'total_cuotas')) {
                $table->integer('total_cuotas')->default(1)->after('numero_cuota');
            }
            if (!Schema::hasColumn('pagos', 'saldo_pendiente')) {
                $table->decimal('saldo_pendiente', 10, 2)->default(0.00)->after('total_cuotas');
            }
            if (!Schema::hasColumn('pagos', 'faena_id')) {
                $table->unsignedBigInteger('faena_id')->nullable()->after('saldo_pendiente');
            }
        });

        // 2. Tabla Faenas Escolares y Servicios Comunales
        if (!Schema::hasTable('faenas')) {
            Schema::create('faenas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->default(1)->constrained('empresas')->onDelete('cascade');
                $table->string('titulo');
                $table->text('descripcion')->nullable();
                $table->date('fecha_faena');
                $table->string('lugar')->default('Local Principal I.E.');
                $table->decimal('valor_compensacion', 10, 2)->default(40.00); // Monto deducible de la cuota ordinaria
                $table->integer('cupo_maximo')->default(50);
                $table->enum('estado', ['PROGRAMADA', 'EN_CURSO', 'CONCLUIDA'])->default('PROGRAMADA');
                $table->timestamps();
            });
        }

        // 3. Asistencia a Faenas y Compensaciones de Padres
        if (!Schema::hasTable('faena_asistencias')) {
            Schema::create('faena_asistencias', function (Blueprint $table) {
                $table->id();
                $table->foreignId('faena_id')->constrained('faenas')->onDelete('cascade');
                $table->foreignId('padre_familia_id')->constrained('padres_familia')->onDelete('cascade');
                $table->decimal('horas_trabajadas', 4, 1)->default(4.0);
                $table->decimal('monto_compensado', 10, 2)->default(40.00);
                $table->text('observaciones')->nullable();
                $table->unsignedBigInteger('pago_id')->nullable();
                $table->timestamps();
            });
        }

        // 4. Comicios Electorales (Inicio 1 de Setiembre - D.S. 007-2026-MINEDU)
        if (!Schema::hasTable('comicios_electorales')) {
            Schema::create('comicios_electorales', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->default(1)->constrained('empresas')->onDelete('cascade');
                $table->year('año_electoral')->default(2026);
                $table->string('nombre_proceso');
                $table->date('fecha_corte_padron'); // Habitualmente 31 de Agosto
                $table->date('fecha_elecciones');   // Convocatoria obligatoria setiembre - noviembre
                $table->enum('estado', ['CONVOCADA', 'PADRON_CERRADO', 'EN_VOTACION', 'FINALIZADA'])->default('CONVOCADA');
                $table->timestamps();
            });
        }

        // 5. Padrón y Votos Electorales
        if (!Schema::hasTable('votos_padron')) {
            Schema::create('votos_padron', function (Blueprint $table) {
                $table->id();
                $table->foreignId('comicio_id')->constrained('comicios_electorales')->onDelete('cascade');
                $table->foreignId('padre_familia_id')->constrained('padres_familia')->onDelete('cascade');
                $table->boolean('ha_votado')->default(false);
                $table->timestamp('fecha_hora_voto')->nullable();
                $table->string('mesa_numero')->default('MESA-01');
                $table->text('observaciones')->nullable();
                $table->timestamps();
            });
        }

        // 6. Quioscos Escolares y Cafeterías Saludables (Ley 30021 / D.S. 007-2026-MINEDU)
        if (!Schema::hasTable('quioscos')) {
            Schema::create('quioscos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->default(1)->constrained('empresas')->onDelete('cascade');
                $table->string('nombre');
                $table->string('ubicacion')->nullable();
                $table->enum('estado', ['DISPONIBLE', 'CONCESIONADO', 'EN_MANTENIMIENTO'])->default('DISPONIBLE');
                $table->timestamps();
            });
        }

        // 7. Concesiones y Licitaciones de Quioscos
        if (!Schema::hasTable('concesiones_quioscos')) {
            Schema::create('concesiones_quioscos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quiosco_id')->constrained('quioscos')->onDelete('cascade');
                $table->foreignId('empresa_id')->default(1)->constrained('empresas')->onDelete('cascade');
                $table->string('nombre_concesionario');
                $table->string('dni_ruc', 20);
                $table->string('telefono', 20)->nullable();
                $table->string('correo')->nullable();
                $table->date('fecha_inicio');
                $table->date('fecha_fin');
                $table->decimal('canon_mensual', 10, 2);
                $table->decimal('garantia', 10, 2)->default(0.00);
                $table->boolean('cumple_ley_alimentacion_saludable')->default(true);
                $table->enum('estado', ['VIGENTE', 'FINALIZADO', 'RESCINDIDO'])->default('VIGENTE');
                $table->timestamps();
            });
        }

        // 8. Pagos de Arrendamiento de Quioscos (Ingresos APAFA)
        if (!Schema::hasTable('pagos_quioscos')) {
            Schema::create('pagos_quioscos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('concesion_id')->constrained('concesiones_quioscos')->onDelete('cascade');
                $table->foreignId('empresa_id')->default(1)->constrained('empresas')->onDelete('cascade');
                $table->string('mes_correspondiente');
                $table->decimal('monto', 10, 2);
                $table->date('fecha_pago');
                $table->string('metodo_pago')->default('EFECTIVO');
                $table->string('comprobante_numero')->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();
            });
        }

        // 9. Dictámenes y Auditorías del Consejo de Vigilancia
        if (!Schema::hasTable('dictamenes_vigilancia')) {
            Schema::create('dictamenes_vigilancia', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->default(1)->constrained('empresas')->onDelete('cascade');
                $table->string('periodo'); // Ej: I Trimestre 2026
                $table->string('titulo');
                $table->enum('tipo_dictamen', ['FAVORABLE', 'OBSERVADO', 'DESAPROBADO'])->default('FAVORABLE');
                $table->text('detalles_auditoria')->nullable();
                $table->text('observaciones_recomendaciones')->nullable();
                $table->decimal('monto_ingresos_auditado', 12, 2)->default(0.00);
                $table->decimal('monto_gastos_auditado', 12, 2)->default(0.00);
                $table->decimal('saldo_auditado', 12, 2)->default(0.00);
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->timestamps();
            });
        }

        // 10. Continuidad de Gestión, Vacancias y Reemplazos de Directivos
        if (!Schema::hasTable('movimientos_directiva')) {
            Schema::create('movimientos_directiva', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->default(1)->constrained('empresas')->onDelete('cascade');
                $table->string('cargo_nombre');
                $table->string('directivo_saliente_nombre');
                $table->string('directivo_saliente_dni', 20)->nullable();
                $table->enum('causal_vacancia', [
                    'INASISTENCIA_3_ASAMBLEAS',
                    'RENUNCIA_VOLUNTARIA',
                    'EGRESO_DEL_HIJO',
                    'FALLECIMIENTO_O_INCAPACIDAD',
                    'DESTITUCION_POR_ASAMBLEA'
                ])->default('RENUNCIA_VOLUNTARIA');
                $table->date('fecha_vacancia');
                $table->string('directivo_entrante_nombre');
                $table->string('directivo_entrante_dni', 20)->nullable();
                $table->string('directivo_entrante_parentesco')->nullable();
                $table->string('numero_acta_asamblea')->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_directiva');
        Schema::dropIfExists('dictamenes_vigilancia');
        Schema::dropIfExists('pagos_quioscos');
        Schema::dropIfExists('concesiones_quioscos');
        Schema::dropIfExists('quioscos');
        Schema::dropIfExists('votos_padron');
        Schema::dropIfExists('comicios_electorales');
        Schema::dropIfExists('faena_asistencias');
        Schema::dropIfExists('faenas');
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn(['modalidad', 'numero_cuota', 'total_cuotas', 'saldo_pendiente', 'faena_id']);
        });
    }
};
