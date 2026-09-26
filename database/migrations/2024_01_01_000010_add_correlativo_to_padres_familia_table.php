<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('padres_familia', function (Blueprint $table) {
            $table->string('correlativo', 30)->nullable()->after('direccion');
        });
    }

    public function down(): void
    {
        Schema::table('padres_familia', function (Blueprint $table) {
            $table->dropColumn('correlativo');
        });
    }
};
