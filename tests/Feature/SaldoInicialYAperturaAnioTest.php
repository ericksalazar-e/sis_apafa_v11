<?php

namespace Tests\Feature;

use App\Models\Aporte;
use App\Models\AportePadre;
use App\Models\Empresa;
use App\Models\Estudiante;
use App\Models\PadreFamilia;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SaldoInicialYAperturaAnioTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected int $empresaId;

    protected Empresa $empresa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::withoutGlobalScopes()->where('email', 'admin@apafa.pe')->first()
            ?? User::withoutGlobalScopes()->first();

        $this->empresaId = $this->admin->empresa_id ?? 1;
        session(['empresa_id' => $this->empresaId]);

        $this->empresa = Empresa::find($this->empresaId);
    }

    /**
     * Test 1: Configuración de saldo inicial de caja con acta legal y verificación en balances.
     */
    public function test_actualizar_saldo_inicial_exitoso_y_su_incorporacion_en_balances(): void
    {
        $montoInicial = 3850.50;
        $fecha = '2026-01-15';
        $sustento = 'ACTA N° 001-2026-ENTREGA-CARGO-TESORERIA';
        $glosa = 'Saldo en efectivo recibido de la directiva saliente periodo 2025';

        $response = $this->actingAs($this->admin)->post(route('configuracion.saldo_inicial.update'), [
            'saldo_inicial_caja' => $montoInicial,
            'fecha_saldo_inicial' => $fecha,
            'sustento_saldo_inicial' => $sustento,
            'glosa_saldo_inicial' => $glosa,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->empresa->refresh();
        $this->assertEquals($montoInicial, (float) $this->empresa->saldo_inicial_caja);
        $this->assertEquals($sustento, $this->empresa->sustento_saldo_inicial);
        $this->assertEquals($glosa, $this->empresa->glosa_saldo_inicial);

        // Verificar que en Gastos POA y Vigilancia se refleje el saldo inicial
        $resPoa = $this->actingAs($this->admin)->get(route('gastos_poa.index'));
        $resPoa->assertStatus(200);
        $resPoa->assertSee(number_format($montoInicial, 2));

        $resVigilancia = $this->actingAs($this->admin)->get(route('vigilancia.index'));
        $resVigilancia->assertStatus(200);
        $resVigilancia->assertSee(number_format($montoInicial, 2));
    }

    /**
     * Test 2: Validación de saldo inicial (no permite números negativos).
     */
    public function test_validacion_saldo_inicial_valores_negativos(): void
    {
        $response = $this->actingAs($this->admin)->post(route('configuracion.saldo_inicial.update'), [
            'saldo_inicial_caja' => -500.00,
            'fecha_saldo_inicial' => '2026-01-15',
        ]);

        $response->assertSessionHasErrors(['saldo_inicial_caja']);
    }

    /**
     * Test 3: Apertura de nuevo año lectivo, promoción automática de grados y egreso de 5to de secundaria.
     */
    public function test_apertura_nuevo_anio_promocion_estudiantes_y_egresados(): void
    {
        $anioActual = (int) ($this->empresa->anio_lectivo_actual ?? date('Y'));
        $nuevoAnio = $anioActual + 1;

        // Crear estudiante de 2do de Primaria
        $estPrimaria = Estudiante::create([
            'empresa_id' => $this->empresaId,
            'dni' => '71829301',
            'nombres' => 'LUCAS',
            'ape_pat' => 'VARGAS',
            'ape_mat' => 'SOTO',
            'nivel' => 'Primaria',
            'grado' => '2° de Primaria',
            'seccion' => 'A',
            'turno' => 'Mañana',
            'estado_matricula' => 'MATRICULADO',
        ]);

        // Crear estudiante de 5to de Secundaria
        $estSecundaria = Estudiante::create([
            'empresa_id' => $this->empresaId,
            'dni' => '71829302',
            'nombres' => 'VALERIA',
            'ape_pat' => 'ROJAS',
            'ape_mat' => 'CASTRO',
            'nivel' => 'Secundaria',
            'grado' => '5° de Secundaria',
            'seccion' => 'B',
            'turno' => 'Mañana',
            'estado_matricula' => 'MATRICULADO',
        ]);

        // Ejecutar apertura de nuevo año
        $response = $this->actingAs($this->admin)->post(route('configuracion.apertura_nuevo_anio'), [
            'nuevo_anio' => $nuevoAnio,
            'monto_cuota' => 50.00,
            'promover_grados' => 1,
            'generar_cuotas' => 0, // Probamos solo promoción aquí
            'periodo_apafa' => "$nuevoAnio - ".($nuevoAnio + 1),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verificar actualización en la empresa
        $this->empresa->refresh();
        $this->assertEquals($nuevoAnio, $this->empresa->anio_lectivo_actual);

        // Verificar que el estudiante de 2do Primaria pasó a 3ro de Primaria
        $estPrimaria->refresh();
        $this->assertStringContainsString('3', $estPrimaria->grado);
        $this->assertEquals('MATRICULADO', $estPrimaria->estado_matricula);

        // Verificar que la estudiante de 5to Secundaria pasó a EGRESADO
        $estSecundaria->refresh();
        $this->assertEquals('EGRESADO', $estSecundaria->estado_matricula);
    }

    /**
     * Test 4: Apertura de año lectivo con generación masiva de cuota anual APAFA para padres asociados.
     */
    public function test_apertura_nuevo_anio_generacion_automatica_cuotas_apafa(): void
    {
        $anioActual = (int) ($this->empresa->anio_lectivo_actual ?? date('Y'));
        $nuevoAnio = $anioActual + 1;

        $padre = PadreFamilia::create([
            'empresa_id' => $this->empresaId,
            'padre_dni' => '44883311',
            'padre_nombres' => 'MANUEL',
            'padre_ape_pat' => 'QUISPE',
            'padre_ape_mat' => 'MAMANI',
        ]);

        $montoCuota = 65.00;

        $response = $this->actingAs($this->admin)->post(route('configuracion.apertura_nuevo_anio'), [
            'nuevo_anio' => $nuevoAnio,
            'monto_cuota' => $montoCuota,
            'promover_grados' => 0,
            'generar_cuotas' => 1,
            'periodo_apafa' => "$nuevoAnio - ".($nuevoAnio + 1),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verificar que se creó el Aporte general y el AportePadre para el nuevo año
        $aporteCreado = Aporte::where('empresa_id', $this->empresaId)
            ->where('titulo', 'like', "%Cuota Ordinaria Anual APAFA {$nuevoAnio}%")
            ->first();

        $this->assertNotNull($aporteCreado, "Debe haberse generado el aporte anual para el año $nuevoAnio.");
        $this->assertEquals($montoCuota, (float) $aporteCreado->monto);

        $aportePadre = AportePadre::where('empresa_id', $this->empresaId)
            ->where('aporte_id', $aporteCreado->id)
            ->where('padre_familia_id', $padre->id)
            ->first();

        $this->assertNotNull($aportePadre, 'El padre de familia debe tener la cuota asignada.');
        $this->assertEquals($montoCuota, (float) $aportePadre->monto_aporte);
        $this->assertEquals($montoCuota, (float) $aportePadre->debe);
    }

    /**
     * Test 5: Cobro POS con regularización de recibo manual anterior y amortización de aportes.
     */
    public function test_cobro_pos_regularizacion_recibo_manual_y_amortizacion(): void
    {
        $padre = PadreFamilia::create([
            'empresa_id' => $this->empresaId,
            'padre_dni' => '55227711',
            'padre_nombres' => 'CARMEN',
            'padre_ape_pat' => 'FLORES',
            'padre_ape_mat' => 'RIVERA',
        ]);

        $aporte = Aporte::create([
            'empresa_id' => $this->empresaId,
            'titulo' => 'CUOTA ANUAL ORDINARIA 2026',
            'tipo' => 'ORDINARIA',
            'monto' => 50.00,
            'fecha' => '2026-03-01',
        ]);

        $aportePadre = AportePadre::create([
            'empresa_id' => $this->empresaId,
            'aporte_id' => $aporte->id,
            'padre_familia_id' => $padre->id,
            'monto_aporte' => 50.00,
            'monto_aportado' => 0.00,
            'debe' => 50.00,
        ]);

        $numeroReciboFisico = 'TALONARIO-A-NRO-0042';

        $response = $this->actingAs($this->admin)->post(route('pagos.store'), [
            'padre_familia_id' => $padre->id,
            'pago_concepto' => 'APORTE: CUOTA ANUAL ORDINARIA 2026',
            'monto_pago' => 50.00,
            'tipo_comprobante' => 'BOLETA',
            'modalidad' => 'CONTADO',
            'es_regularizacion' => 1,
            'recibo_manual_referencia' => $numeroReciboFisico,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verificar el pago registrado
        $pago = Pago::where('empresa_id', $this->empresaId)
            ->where('padre_familia_id', $padre->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($pago);
        $this->assertTrue((bool) $pago->es_regularizacion);
        $this->assertEquals($numeroReciboFisico, $pago->recibo_manual_referencia);

        // Verificar que el aporte de la asociada quedó amortizado
        $aportePadre->refresh();
        $this->assertEquals(50.00, (float) $aportePadre->monto_aportado);
        $this->assertEquals(0.00, (float) $aportePadre->debe);
    }
}
