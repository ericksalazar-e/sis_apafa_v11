<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('padre_familia_id')->constrained('padres_familia')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('pago_concepto', 255);
            $table->decimal('monto_pago', 10, 2);
            $table->decimal('debe', 10, 2)->default(0);

            // Campos vinculados a la Facturación Electrónica (SUNAT / Microservicio)
            $table->string('tipo_comprobante', 50)->default('NOTA_VENTA'); // BOLETA, FACTURA, NOTA_VENTA
            $table->string('serie', 10)->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('estado_facturacion', 50)->default('PENDIENTE'); // PENDIENTE, EN_COLA, EMITIDO, ERROR
            $table->string('hash_cpe', 255)->nullable();
            $table->text('mensaje_sunat')->nullable();
            $table->string('pdf_url', 255)->nullable();
            $table->string('xml_url', 255)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
