<?php

namespace Tests\Feature;

use App\Models\ConfiguracionCpe;
use App\Models\PadreFamilia;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FacturacionServiceCpeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::withoutGlobalScopes()->where('rol', 'ADMIN')->first();
        if ($admin) {
            $this->actingAs($admin);
        }
    }

    /**
     * Test que el Centro de Facturación CPE (/facturacion-service) cargue correctamente con UBL 2.1.
     */
    public function test_facturacion_service_dashboard_loads(): void
    {
        session(['empresa_id' => 1]);

        $response = $this->get('/facturacion-service');
        $response->assertStatus(200);
        $response->assertSee('Facturación CPE', false);
        $response->assertSee('Microservicio UBL 2.1 Activo', false);
        $response->assertSee('Parámetros UBL 2.1 & SUNAT', false);
        $response->assertSee('Monitor de Comprobantes & Envíos', false);
        $response->assertSee('Consola & Simulador en Vivo', false);
    }

    /**
     * Test de actualización de configuración CPE y credenciales SUNAT.
     */
    public function test_actualizar_configuracion_cpe(): void
    {
        session(['empresa_id' => 1]);

        $data = [
            'ambiente' => 'BETA',
            'ruc_emisor' => '20601234567',
            'razon_social' => 'ASOCIACION DE PADRES DE FAMILIA TEST UBL',
            'nombre_comercial' => 'APAFA TEST',
            'direccion_fiscal' => 'Av. Educativa 456, Lima',
            'ubigeo' => '150101',
            'departamento' => 'LIMA',
            'provincia' => 'LIMA',
            'distrito' => 'LIMA',
            'usuario_sol' => 'MODDATOS',
            'clave_sol' => 'moddatos',
            'serie_boleta' => 'B001',
            'serie_factura' => 'F001',
            'serie_nota_venta' => 'NV01',
            'correlativo_boleta' => 10,
            'correlativo_factura' => 5,
            'correlativo_nota_venta' => 20,
            'modo_emision' => 'ASINCRONO_COLA',
            'auto_envio_sunat' => '1',
            'formato_impresion' => 'TICKET_80MM',
            'leyenda_pie' => 'Comprobante Electrónico UBL 2.1 de Prueba',
        ];

        $response = $this->post(route('facturacion.config.update'), $data);
        $response->assertRedirect(route('facturacion.index'));
        $response->assertSessionHas('success');

        $config = ConfiguracionCpe::where('empresa_id', 1)->first();
        $this->assertNotNull($config);
        $this->assertEquals('20601234567', $config->ruc_emisor);
        $this->assertEquals('ASOCIACION DE PADRES DE FAMILIA TEST UBL', $config->razon_social);
        $this->assertEquals(10, $config->correlativo_boleta);
    }

    /**
     * Test del simulador de emisión UBL 2.1 en vivo.
     */
    public function test_simulador_emision_ubl21_en_vivo(): void
    {
        session(['empresa_id' => 1]);

        $response = $this->post(route('facturacion.test'), [
            'tipo_comprobante' => 'BOLETA',
            'monto' => 65.50,
        ]);

        $response->assertRedirect(route('facturacion.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('test_result');

        $result = session('test_result');
        $this->assertTrue($result['success']);
        $this->assertEquals('BOLETA', $result['tipo_comprobante']);
        $this->assertNotEmpty($result['hash_cpe']);
        $this->assertNotEmpty($result['qr_string']);
        $this->assertStringContainsString('|03|', $result['qr_string']); // 03 = Código SUNAT Boleta
    }

    /**
     * Test de procesamiento de cola y generación de XML UBL 2.1.
     */
    public function test_procesar_cola_y_generar_xml_ubl21(): void
    {
        session(['empresa_id' => 1]);
        $padre = PadreFamilia::first();
        $this->assertNotNull($padre);

        $testNum = sprintf('%06d', rand(800000, 899999));

        $pago = Pago::create([
            'empresa_id' => 1,
            'padre_familia_id' => $padre->id,
            'pago_concepto' => 'Cuota Extraordinaria Mantenimiento',
            'monto_pago' => 45.00,
            'tipo_comprobante' => 'BOLETA',
            'serie' => 'B001',
            'numero' => $testNum,
            'estado_facturacion' => 'EN_COLA',
        ]);

        // Procesar cola
        $response = $this->post(route('facturacion.procesar_cola'));
        $response->assertRedirect(route('facturacion.index'));

        $pago->refresh();
        $this->assertEquals('EMITIDO', $pago->estado_facturacion);
        $this->assertNotEmpty($pago->hash_cpe);

        // Ver / Descargar XML UBL 2.1
        $xmlResponse = $this->get(route('facturacion.xml', $pago));
        $xmlResponse->assertStatus(200);
        $xmlResponse->assertHeader('Content-Type', 'application/xml');
        $xmlContent = $xmlResponse->getContent();
        $this->assertStringContainsString('urn:oasis:names:specification:ubl:schema:xsd:Invoice-2', $xmlContent);
        $this->assertStringContainsString("B001-{$testNum}", $xmlContent);
    }
}
