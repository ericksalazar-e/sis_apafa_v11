<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aportes', function (Blueprint $table) {
            $table->string('tipo', 50)->default('ORDINARIA')->after('titulo'); // ORDINARIA, EXTRAORDINARIA
            $table->decimal('uit_vigente', 10, 2)->default(5350.00)->after('monto');
            $table->decimal('max_legal', 10, 2)->default(80.25)->after('uit_vigente');
        });
    }

    public function down(): void
    {
        Schema::table('aportes', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'uit_vigente', 'max_legal']);
        });
    }
};
