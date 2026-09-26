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
        if (Schema::hasTable('comicios_electorales')) {
            Schema::table('comicios_electorales', function (Blueprint $table) {
                if (!Schema::hasColumn('comicios_electorales', 'comite_presidente_nombre')) {
                    $table->string('comite_presidente_nombre')->nullable();
                    $table->string('comite_presidente_dni', 20)->nullable();
                    $table->string('comite_secretario_nombre')->nullable();
                    $table->string('comite_secretario_dni', 20)->nullable();
                    $table->string('comite_vocal_nombre')->nullable();
                    $table->string('comite_vocal_dni', 20)->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('comicios_electorales')) {
            Schema::table('comicios_electorales', function (Blueprint $table) {
                $table->dropColumn([
                    'comite_presidente_nombre',
                    'comite_presidente_dni',
                    'comite_secretario_nombre',
                    'comite_secretario_dni',
                    'comite_vocal_nombre',
                    'comite_vocal_dni',
                ]);
            });
        }
    }
};
