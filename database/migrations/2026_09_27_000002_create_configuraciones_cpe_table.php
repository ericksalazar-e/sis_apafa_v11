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
        Schema::create('configuraciones_cpe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();

            // Entorno y Credenciales SUNAT
            $table->enum('ambiente', ['BETA', 'PRODUCCION'])->default('BETA');
            $table->string('ruc_emisor', 11)->nullable();
            $table->string('razon_social', 255)->nullable();
            $table->string('nombre_comercial', 255)->nullable();
            $table->string('direccion_fiscal', 255)->nullable();
            $table->string('ubigeo', 10)->default('150101');
            $table->string('departamento', 100)->default('LIMA');
            $table->string('provincia', 100)->default('LIMA');
            $table->string('distrito', 100)->default('LIMA');

            // Credenciales Clave SOL SUNAT
            $table->string('usuario_sol', 50)->default('MODDATOS');
            $table->string('clave_sol', 100)->default('moddatos');

            // Certificado Digital
            $table->string('certificado_path', 255)->nullable();
            $table->string('certificado_password', 100)->nullable();
            $table->date('certificado_vencimiento')->nullable();

            // Series y Correlativos UBL 2.1
            $table->string('serie_boleta', 10)->default('B001');
            $table->string('serie_factura', 10)->default('F001');
            $table->string('serie_nota_venta', 10)->default('NV01');
            $table->unsignedInteger('correlativo_boleta')->default(1);
            $table->unsignedInteger('correlativo_factura')->default(1);
            $table->unsignedInteger('correlativo_nota_venta')->default(1);

            // Configuración del Microservicio Asíncrono
            $table->enum('modo_emision', ['ASINCRONO_COLA', 'SINCRONO_DIRECTO'])->default('ASINCRONO_COLA');
            $table->boolean('auto_envio_sunat')->default(true);
            $table->enum('formato_impresion', ['TICKET_80MM', 'A4'])->default('TICKET_80MM');
            $table->string('leyenda_pie', 255)->default('Representación impresa del Comprobante de Pago Electrónico UBL 2.1');
            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->unique('empresa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuraciones_cpe');
    }
};
