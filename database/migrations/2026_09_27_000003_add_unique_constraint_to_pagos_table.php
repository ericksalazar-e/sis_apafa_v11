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
        Schema::table('pagos', function (Blueprint $table) {
            // Asegurar que la combinación de empresa, serie y número sea única e irrepetible
            $table->unique(['empresa_id', 'serie', 'numero'], 'unique_pago_empresa_serie_numero');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropUnique('unique_pago_empresa_serie_numero');
        });
    }
};
