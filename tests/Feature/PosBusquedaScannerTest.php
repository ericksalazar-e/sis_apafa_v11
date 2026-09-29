<?php

namespace Tests\Feature;

use App\Models\Aporte;
use App\Models\AportePadre;
use App\Models\Estudiante;
use App\Models\PadreFamilia;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PosBusquedaScannerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected int $empresaId;

    protected PadreFamilia $padre;

    protected Estudiante $estudiante;

    protected Aporte $aporte;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::withoutGlobalScopes()->where('email', 'admin@apafa.pe')->first()
            ?? User::withoutGlobalScopes()->first();

        $this->empresaId = $this->admin->empresa_id ?? 1;
        session(['empresa_id' => $this->empresaId]);

        // Crear datos de prueba específicos para POS Scanner
        $this->padre = PadreFamilia::create([
            'empresa_id' => $this->empresaId,
            'padre_dni' => '78451296',
            'padre_nombres' => 'ROBERTO CARLOS',
            'padre_ape_pat' => 'MENDOZA',
            'padre_ape_mat' => 'QUISPE',
            'correlativo' => 'APAFA-2026-POS-99',
            'celular' => '987654321',
            'parentesco' => 'PADRE',
            'activo' => true,
        ]);

        $this->estudiante = Estudiante::create([
            'empresa_id' => $this->empresaId,
            'padre_familia_id' => $this->padre->id,
            'dni' => '87654329',
            'nombres' => 'DYLAN MATEO',
            'ape_pat' => 'MENDOZA',
            'ape_mat' => 'ROJAS',
            'grado' => '4to',
            'seccion' => 'B',
            'nivel' => 'PRIMARIA',
            'correlativo' => 'EST-T-2026-99',
            'activo' => true,
        ]);

        $this->aporte = Aporte::create([
            'empresa_id' => $this->empresaId,
            'titulo' => 'CUOTA EXTRAORDINARIA TECHO PATIO 2026',
            'monto' => 60.00,
            'tipo' => 'EXTRAORDINARIO',
            'descripcion' => 'Aporte para techado escolar',
            'activo' => true,
        ]);

        AportePadre::create([
            'empresa_id' => $this->empresaId,
            'aporte_id' => $this->aporte->id,
            'padre_familia_id' => $this->padre->id,
            'monto_aporte' => 60.00,
            'monto_aportado' => 10.00,
            'debe' => 50.00,
            'estado' => 'PENDIENTE',
        ]);
    }

    /**
     * Test 1: Búsqueda directa por DNI del padre de familia en POS.
     */
    public function test_buscar_pos_por_dni_padre(): void
    {
        $response = $this->actingAs($this->admin)->getJson(route('pagos.buscar_asociado', [
            'query' => '78451296',
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'multiple' => false,
            'padre' => [
                'id' => $this->padre->id,
                'dni' => '78451296',
                'nombre_completo' => $this->padre->nombre_completo,
            ],
        ]);

        // Verificar que devuelve la lista de deudas pendientes
        $this->assertNotEmpty($response->json('deudas'));
        $this->assertEquals(50.00, $response->json('deudas.0.debe'));
    }

    /**
     * Test 2: Búsqueda en POS usando el DNI del hijo (carné escolar o código de barras del estudiante).
     * El sistema debe identificar al padre / apoderado automáticamente.
     */
    public function test_buscar_pos_por_dni_estudiante_carnet_escolar(): void
    {
        $response = $this->actingAs($this->admin)->getJson(route('pagos.buscar_asociado', [
            'query' => '87654329', // DNI del alumno
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'multiple' => false,
            'padre' => [
                'id' => $this->padre->id,
                'dni' => '78451296',
            ],
        ]);

        // Verifica que el motivo_match mencione al hijo
        $this->assertStringContainsString('DYLAN MATEO', $response->json('motivo_match'));
    }

    /**
     * Test 3: Búsqueda con Código QR oficial del carné del padre (APAFA_ASOCIADO).
     */
    public function test_buscar_pos_por_qr_carnet_padre(): void
    {
        $qrString = 'APAFA_ASOCIADO|DNI:78451296|PADRE:ROBERTO CARLOS MENDOZA QUISPE|CORRELATIVO:APAFA-2026-POS-99';

        $response = $this->actingAs($this->admin)->getJson(route('pagos.buscar_asociado', [
            'query' => $qrString,
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'padre' => [
                'id' => $this->padre->id,
                'dni' => '78451296',
            ],
        ]);
    }

    /**
     * Test 4: Búsqueda con Código QR oficial del carné escolar del estudiante (ASISTENCIA_ESCOLAR).
     */
    public function test_buscar_pos_por_qr_carnet_estudiante(): void
    {
        $qrString = 'ASISTENCIA_ESCOLAR|DNI:87654329|ALUMNO:DYLAN MATEO MENDOZA ROJAS|APODERADO:78451296';

        $response = $this->actingAs($this->admin)->getJson(route('pagos.buscar_asociado', [
            'query' => $qrString,
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'padre' => [
                'id' => $this->padre->id,
                'dni' => '78451296',
            ],
        ]);
    }

    /**
     * Test 5: Búsqueda por código correlativo de carné.
     */
    public function test_buscar_pos_por_correlativo_carnet(): void
    {
        $response = $this->actingAs($this->admin)->getJson(route('pagos.buscar_asociado', [
            'query' => 'APAFA-2026-POS-99',
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'padre' => [
                'id' => $this->padre->id,
                'dni' => '78451296',
            ],
        ]);
    }

    /**
     * Test 6: Búsqueda inexistente devuelve 404 con mensaje amigable.
     */
    public function test_buscar_pos_inexistente_retorna_404(): void
    {
        $response = $this->actingAs($this->admin)->getJson(route('pagos.buscar_asociado', [
            'query' => '00000000',
        ]));

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
        ]);
    }

    /**
     * Test 7: Vista de POS ventanilla renderiza el componente escáner y modal de cámara.
     */
    public function test_pos_view_renders_scanner_and_modal(): void
    {
        $response = $this->actingAs($this->admin)->get(route('pagos.index'));

        $response->assertStatus(200);
        $response->assertSee('pos_scanner_input');
        $response->assertSee('modal-camara-pos');
        $response->assertSee('reader-pos');
        $response->assertSee('tarjeta-asociado-seleccionado');
    }
}
