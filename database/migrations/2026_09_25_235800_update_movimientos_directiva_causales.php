<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('movimientos_directiva')) {
            // Modificar la columna causal_vacancia para permitir strings más amplios (MySQL enum limitation workaround)
            // y directivo_entrante_nombre como nullable
            Schema::table('movimientos_directiva', function (Blueprint $table) {
                $table->string('causal_vacancia', 80)->default('RENUNCIA_VOLUNTARIA')->change();
                $table->string('directivo_entrante_nombre')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('movimientos_directiva')) {
            Schema::table('movimientos_directiva', function (Blueprint $table) {
                $table->string('causal_vacancia', 50)->change();
            });
        }
    }
};
