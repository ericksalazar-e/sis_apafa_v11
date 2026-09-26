<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gastos_poa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('categoria', 50)->default('TECNOLOGIA');
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->decimal('monto', 10, 2);
            $table->string('nro_comprobante', 100)->nullable();
            $table->string('proveedor', 200)->nullable();
            $table->date('fecha');
            $table->string('foto_comprobante', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gastos_poa');
    }
};
