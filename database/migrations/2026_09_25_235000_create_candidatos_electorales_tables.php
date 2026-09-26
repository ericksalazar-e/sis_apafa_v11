<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Registro de Listas y Candidatos a la Directiva de APAFA (Ley N° 28628 y D.S. N.º 007-2026-MINEDU)
     */
    public function up(): void
    {
        if (!Schema::hasTable('listas_electorales')) {
            Schema::create('listas_electorales', function (Blueprint $table) {
                $table->id();
                $table->foreignId('comicio_id')->constrained('comicios_electorales')->onDelete('cascade');
                $table->foreignId('empresa_id')->default(1)->constrained('empresas')->onDelete('cascade');
                $table->integer('numero_lista');
                $table->string('nombre_lista');
                $table->string('eslogan')->nullable();
                $table->string('color', 20)->default('#10b981');
                $table->string('logo_simbolo')->nullable();
                $table->text('propuestas')->nullable();
                $table->integer('total_votos')->default(0);
                $table->enum('estado', ['INSCRITA', 'HABILITADA', 'OBSERVADA', 'TACHADA', 'GANADORA'])->default('HABILITADA');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('candidatos_electorales')) {
            Schema::create('candidatos_electorales', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lista_electoral_id')->constrained('listas_electorales')->onDelete('cascade');
                $table->foreignId('padre_familia_id')->constrained('padres_familia')->onDelete('cascade');
                $table->string('cargo_postula'); // PRESIDENTE, VICEPRESIDENTE, SECRETARIO, TESORERO, VOCAL_1, VOCAL_2, VOCAL_3, CONSEJO_VIGILANCIA_PRESIDENTE, CONSEJO_VIGILANCIA_SECRETARIO, CONSEJO_VIGILANCIA_VOCAL
                $table->boolean('cumple_requisitos')->default(true);
                $table->text('observaciones')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('votos_padron', function (Blueprint $table) {
            if (!Schema::hasColumn('votos_padron', 'lista_electoral_id')) {
                $table->unsignedBigInteger('lista_electoral_id')->nullable()->after('ha_votado');
            }
            if (!Schema::hasColumn('votos_padron', 'tipo_voto')) {
                $table->string('tipo_voto', 20)->default('VALIDO')->after('lista_electoral_id'); // VALIDO, BLANCO, NULO
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('votos_padron', function (Blueprint $table) {
            if (Schema::hasColumn('votos_padron', 'lista_electoral_id')) {
                $table->dropColumn('lista_electoral_id');
            }
            if (Schema::hasColumn('votos_padron', 'tipo_voto')) {
                $table->dropColumn('tipo_voto');
            }
        });

        Schema::dropIfExists('candidatos_electorales');
        Schema::dropIfExists('listas_electorales');
    }
};
