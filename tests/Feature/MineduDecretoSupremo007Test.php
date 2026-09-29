<?php

namespace Tests\Feature;

use App\Models\CandidatoElectoral;
use App\Models\Cargo;
use App\Models\ComicioElectoral;
use App\Models\ConcesionQuiosco;
use App\Models\DictamenVigilancia;
use App\Models\Empresa;
use App\Models\Estudiante;
use App\Models\Faena;
use App\Models\FaenaAsistencia;
use App\Models\ListaElectoral;
use App\Models\MovimientoDirectiva;
use App\Models\PadreFamilia;
use App\Models\Pago;
use App\Models\PagoQuiosco;
use App\Models\Quiosco;
use App\Models\User;
use App\Models\VotoPadron;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MineduDecretoSupremo007Test extends TestCase
{
    use DatabaseTransactions;

    private Empresa $empresa;

    private User $admin;

    private PadreFamilia $padre;

    private Estudiante $estudiante;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::firstOrCreate(
            ['id' => 1],
            ['nombre' => 'I.E. EMBLEMÁTICA NERY GARCÍA ZÁRATE', 'cod_modular' => '054321', 'periodo_apafa' => '2026']
        );

        $this->admin = User::where('rol', 'ADMIN')->first() ?? User::firstOrCreate(
            ['username' => 'admin_test'],
            ['name' => 'Admin Test', 'email' => 'admin_test@test.com', 'password' => bcrypt('password'), 'empresa_id' => 1, 'rol' => 'ADMIN']
        );

        $this->padre = PadreFamilia::firstOrCreate(
            ['padre_dni' => '88887777'],
            [
                'empresa_id' => 1,
                'padre_ape_pat' => 'MAMANI',
                'padre_ape_mat' => 'QUISPE',
                'padre_nombres' => 'JUAN CARLOS',
                'tipo_consanguinidad' => 'ABUELO O ABUELA',
                'conyuge_dni' => '44443333',
                'conyuge_nombres' => 'MARIA MAMANI',
                'carnet_codigo' => 'APAFA-PADRE-00999',
            ]
        );

        $this->estudiante = Estudiante::firstOrCreate(
            ['dni' => '99998888'],
            [
                'empresa_id' => 1,
                'padre_familia_id' => $this->padre->id,
                'ape_pat' => 'MAMANI',
                'ape_mat' => 'FLORES',
                'nombres' => 'DIEGO',
                'nivel' => 'Secundaria',
                'grado' => '3',
                'seccion' => 'A',
                'turno' => 'Mañana',
                'parentesco' => 'NIETO(A)',
            ]
        );
    }

    /**
     * Pilar 1: Convocatoria a Faenas, Registro de Asistencia, Deducción y Fraccionamiento de Cuota
     */
    public function test_faena_convocatoria_asistencia_y_compensacion_economica(): void
    {
        $this->actingAs($this->admin);

        // 1. Crear faena
        $tituloFaena = 'Faena Comunal '.uniqid();
        $response = $this->post(route('faenas.store'), [
            'titulo' => $tituloFaena,
            'fecha_faena' => '2026-10-10',
            'lugar' => 'Pabellón Central',
            'valor_compensacion' => 45.00,
            'cupo_maximo' => 40,
            'descripcion' => 'Compensación de cuota según D.S. N.º 007-2026-MINEDU',
        ]);
        $response->assertRedirect(route('faenas.index'));

        // Prohibición estricta de labores peligrosas (prevenir accidentes fatales como derribo de torres)
        $dangerousResponse = $this->from(route('faenas.index'))->post(route('faenas.store'), [
            'titulo' => 'Demolición de torre antigua de agua',
            'fecha_faena' => '2026-10-15',
            'lugar' => 'Patio Trasero',
            'valor_compensacion' => 50.00,
            'cupo_maximo' => 20,
            'descripcion' => 'Tumbar la torre que está en desuso',
        ]);
        $dangerousResponse->assertRedirect(route('faenas.index'));
        $dangerousResponse->assertSessionHas('error');
        $this->assertDatabaseMissing('faenas', ['titulo' => 'Demolición de torre antigua de agua']);

        $faena = Faena::where('titulo', $tituloFaena)->first();
        $this->assertNotNull($faena);

        // Limpiar cualquier asistencia previa para aislamiento de prueba
        FaenaAsistencia::where('padre_familia_id', $this->padre->id)->delete();

        // 2. Registrar asistencia y compensación económica
        $asistResponse = $this->post(route('faenas.asistencia', $faena->id), [
            'padre_familia_id' => $this->padre->id,
            'horas_trabajadas' => 4.5,
            'monto_compensado' => 45.00,
            'observaciones' => 'Trabajo de lijado y pintado cumplido a cabalidad',
        ]);
        $asistResponse->assertSessionHas('success');

        $this->assertDatabaseHas('faena_asistencias', [
            'faena_id' => $faena->id,
            'padre_familia_id' => $this->padre->id,
            'monto_compensado' => 45.00,
        ]);

        // Verificar comprobante de pago por compensación
        $this->assertDatabaseHas('pagos', [
            'padre_familia_id' => $this->padre->id,
            'modalidad' => 'COMPENSACION_FAENA',
            'faena_id' => $faena->id,
        ]);

        // 3. Ver Constancia Oficial de Compensación
        $constanciaResponse = $this->get(route('faenas.constancia', [$faena->id, $this->padre->id]));
        $constanciaResponse->assertStatus(200);
        $constanciaResponse->assertSee('Constancia de Compensación de Aportes por Servicios Comunales');
        $constanciaResponse->assertSee('D.S. N.º 007-2026-MINEDU');

        // 4. Ver Lista de Asistentes en Pantalla y Padrón Oficial Imprimible
        $showResponse = $this->get(route('faenas.show', $faena->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee($this->padre->dni);
        $showResponse->assertSee('Imprimir Padrón de Asistentes');

        $padronResponse = $this->get(route('faenas.imprimir_padron', $faena->id));
        $padronResponse->assertStatus(200);
        $padronResponse->assertSee('PADRÓN DE ASISTENTES');
        $padronResponse->assertSee($this->padre->dni);

        // Limpieza de datos de prueba
        Pago::withoutGlobalScopes()->where('faena_id', $faena->id)->delete();
        FaenaAsistencia::where('faena_id', $faena->id)->delete();
        $faena->delete();
    }

    /**
     * Pilar 1.2: Fraccionamiento de Cuota Ordinaria en Punto de Venta (POS)
     */
    public function test_fraccionamiento_de_cuota_en_pos(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('pagos.store'), [
            'padre_familia_id' => $this->padre->id,
            'pago_concepto' => 'CUOTA ORDINARIA FRACCIONADA 2026',
            'monto_pago' => 40.00,
            'modalidad' => 'FRACCIONADO',
            'numero_cuota' => 1,
            'total_cuotas' => 2,
            'saldo_pendiente' => 40.00,
            'tipo_comprobante' => 'BOLETA',
        ]);
        $response->assertRedirect(route('pagos.index'));

        $this->assertDatabaseHas('pagos', [
            'padre_familia_id' => $this->padre->id,
            'modalidad' => 'FRACCIONADO',
            'numero_cuota' => 1,
            'total_cuotas' => 2,
            'saldo_pendiente' => 40.00,
        ]);

        // Limpieza de pago fraccionado de prueba
        Pago::withoutGlobalScopes()
            ->where('padre_familia_id', $this->padre->id)
            ->where('modalidad', 'FRACCIONADO')
            ->where('pago_concepto', 'CUOTA ORDINARIA FRACCIONADA 2026')
            ->delete();
    }

    /**
     * Pilar 2: Generación de Carta Poder Simple para Estudiante y Padre
     */
    public function test_carta_poder_simple_generation_and_validation(): void
    {
        $this->actingAs($this->admin);

        // Por estudiante
        $respEst = $this->get(route('carta_poder.estudiante', $this->estudiante->id));
        $respEst->assertStatus(200);
        $respEst->assertSee('Carta Poder Simple de Representación Familiar');
        $respEst->assertSee('D.S. N.º 007-2026-MINEDU');
        $respEst->assertSee($this->estudiante->nombres);

        // Por padre
        $respPadre = $this->get(route('carta_poder.padre', $this->padre->id));
        $respPadre->assertStatus(200);
        $respPadre->assertSee($this->padre->padre_nombres);

        // Validación pública
        $respVal = $this->get(route('carta_poder.validar', $this->padre->padre_dni));
        $respVal->assertStatus(200);
        $respVal->assertSee('Poder Válido y Registrado');
    }

    /**
     * Pilar 3: Comicios Electorales con Corte al 31 de Agosto y Prevención de Doble Voto
     */
    public function test_comicios_electorales_padron_y_mesa_sufragio(): void
    {
        $this->actingAs($this->admin);

        // 1. Ver dashboard electoral
        $indexResp = $this->get(route('elecciones.index'));
        $indexResp->assertStatus(200);
        $indexResp->assertSee('Comicios Electorales de la APAFA');

        $comicio = ComicioElectoral::first();
        $this->assertNotNull($comicio);

        // Limpiar votos y candidaturas anteriores de este padre para aislamiento
        VotoPadron::where('padre_familia_id', $this->padre->id)->delete();
        CandidatoElectoral::where('padre_familia_id', $this->padre->id)->delete();

        // 2. Generar Padrón oficial cerrado al 31 de agosto
        $padronResp = $this->post(route('elecciones.generar_padron', $comicio->id));
        $padronResp->assertRedirect(route('elecciones.index'));

        $this->assertDatabaseHas('votos_padron', [
            'comicio_id' => $comicio->id,
            'padre_familia_id' => $this->padre->id,
        ]);

        // 3. Emitir Voto Válido
        $votoResp = $this->postJson(route('elecciones.votar', $comicio->id), [
            'padre_familia_id' => $this->padre->id,
        ]);
        $votoResp->assertStatus(200);
        $votoResp->assertJson(['success' => true]);

        // 4. Intentar DOBLE VOTO (Debe ser rechazado con alerta)
        $dobleVotoResp = $this->postJson(route('elecciones.votar', $comicio->id), [
            'padre_familia_id' => $this->padre->id,
        ]);
        $dobleVotoResp->assertStatus(422);
        $dobleVotoResp->assertJson(['already_voted' => true]);

        // 5. Ver Acta Oficial de Escrutinio Final
        $actaResp = $this->get(route('elecciones.acta_escrutinio', $comicio->id));
        $actaResp->assertStatus(200);
        $actaResp->assertSee('Acta de Cierre de Sufragio y Cómputo General');

        // 6. Registro de Lista de Candidatos a la Directiva de APAFA (Ley 28628)
        // Limpiar listas de prueba previas para evitar acumulación de duplicados
        $numLista = rand(100, 999);
        $nombreListaTest = 'LISTA TEST '.$numLista;

        // Eliminar listas de test anteriores en este comicio
        ListaElectoral::where('comicio_id', $comicio->id)
            ->where('nombre_lista', 'LIKE', 'LISTA TEST%')
            ->delete();

        $listaResp = $this->post(route('elecciones.listas.store', $comicio->id), [
            'numero_lista' => $numLista,
            'nombre_lista' => $nombreListaTest,
            'eslogan' => 'Cuentas claras y mejora continua',
            'propuestas' => 'Mantenimiento de computadoras y áreas verdes',
        ]);
        $listaResp->assertRedirect(route('elecciones.index'));
        $lista = ListaElectoral::where('comicio_id', $comicio->id)->where('numero_lista', $numLista)->first();
        $this->assertNotNull($lista);

        // 7. Registro de Candidato en la Lista
        $candidatoResp = $this->post(route('elecciones.candidatos.store', $lista->id), [
            'padre_familia_id' => $this->padre->id,
            'cargo_postula' => 'PRESIDENTE',
            'observaciones' => 'Acreditado y habilitado',
        ]);
        $candidatoResp->assertRedirect(route('elecciones.index'));
        $this->assertDatabaseHas('candidatos_electorales', [
            'lista_electoral_id' => $lista->id,
            'padre_familia_id' => $this->padre->id,
            'cargo_postula' => 'PRESIDENTE',
        ]);

        $candidato = CandidatoElectoral::where('padre_familia_id', $this->padre->id)->first();
        $this->assertNotNull($candidato);

        // 8. Ficha Oficial de Idoneidad y Declaración Jurada (Ley N° 28628)
        $djResp = $this->get(route('elecciones.candidatos.declaracion', $candidato->id));
        $djResp->assertStatus(200);
        $djResp->assertSee('DECLARACIÓN JURADA DE IDONEIDAD');
        $djResp->assertSee('Antecedentes Limpios');
        $djResp->assertSee('No Laborar en la Institución Educativa');
        $djResp->assertSee('Grado del Estudiante');

        // 9. Bloqueo Legal: Padre sin hijos matriculados
        $dniSinHijo = 'SN'.rand(10000, 99999);
        $padreSinHijo = PadreFamilia::create([
            'padre_dni' => $dniSinHijo,
            'padre_ape_pat' => 'QUISPE',
            'padre_ape_mat' => 'ROJAS',
            'padre_nombres' => 'MARIO',
            'empresa_id' => 1,
        ]);
        $rejectSinHijo = $this->from(route('elecciones.index'))->post(route('elecciones.candidatos.store', $lista->id), [
            'padre_familia_id' => $padreSinHijo->id,
            'cargo_postula' => 'SECRETARIO',
        ]);
        $rejectSinHijo->assertRedirect(route('elecciones.index'));
        $rejectSinHijo->assertSessionHas('error');
        $padreSinHijo->delete();

        // 10. Bloqueo Legal: Hijo en último grado (5to de Secundaria)
        $dniUltimo = 'UL'.rand(10000, 99999);
        $padreUltimoGrado = PadreFamilia::create([
            'padre_dni' => $dniUltimo,
            'padre_ape_pat' => 'LOPEZ',
            'padre_ape_mat' => 'SOTO',
            'padre_nombres' => 'CARLOS',
            'empresa_id' => 1,
        ]);
        $estudianteUltimo = Estudiante::create([
            'empresa_id' => 1,
            'padre_familia_id' => $padreUltimoGrado->id,
            'dni' => 'E'.rand(10000, 99999),
            'nombres' => 'SEBASTIAN',
            'ape_pat' => 'LOPEZ',
            'ape_mat' => 'SOTO',
            'nivel' => 'Secundaria',
            'grado' => '5',
            'seccion' => 'B',
        ]);
        $rejectUltimo = $this->from(route('elecciones.index'))->post(route('elecciones.candidatos.store', $lista->id), [
            'padre_familia_id' => $padreUltimoGrado->id,
            'cargo_postula' => 'TESORERO',
        ]);
        $rejectUltimo->assertRedirect(route('elecciones.index'));
        $rejectUltimo->assertSessionHas('error');
        $estudianteUltimo->delete();
        $padreUltimoGrado->delete();

        // 11. Cédula Oficial de Sufragio
        $cedulaResp = $this->get(route('elecciones.cedula', $comicio->id));
        $cedulaResp->assertStatus(200);
        $cedulaResp->assertSee('CÉDULA OFICIAL DE SUFRAGIO');
        $cedulaResp->assertSee($nombreListaTest);

        // 9. Proclamar Lista Ganadora y asumir Directiva APAFA
        $proclamarResp = $this->post(route('elecciones.proclamar', [$comicio->id, $lista->id]));
        $proclamarResp->assertRedirect(route('elecciones.acta_proclamacion', $comicio->id));
        $this->assertDatabaseHas('listas_electorales', [
            'id' => $lista->id,
            'estado' => 'GANADORA',
        ]);

        // 10. Ver Acta Oficial de Proclamación
        $actaProcResp = $this->get(route('elecciones.acta_proclamacion', $comicio->id));
        $actaProcResp->assertStatus(200);
        $actaProcResp->assertSee('ACTA OFICIAL DE PROCLAMACIÓN Y JURAMENTACIÓN');
    }

    /**
     * Pilar 4: Concesión de Quioscos Escolares bajo Ley 30021 y Pago de Cánones
     */
    public function test_quioscos_concesion_y_pago_de_arrendamiento(): void
    {
        $this->actingAs($this->admin);

        // 1. Ver lista de quioscos
        $respQ = $this->get(route('quioscos.index'));
        $respQ->assertStatus(200);
        $respQ->assertSee('Quioscos, Cafeterías y Comedores Escolares');

        // Aislamiento de prueba: Limpiar cualquier registro previo del test
        $quiosco = Quiosco::updateOrCreate(
            ['nombre' => 'Quiosco Test Adjudicación'],
            ['ubicacion' => 'Pabellón de Prueba', 'estado' => 'DISPONIBLE', 'empresa_id' => 1]
        );
        PagoQuiosco::whereHas('concesion', fn ($q) => $q->where('quiosco_id', $quiosco->id))->delete();
        ConcesionQuiosco::where('quiosco_id', $quiosco->id)->delete();

        // 2. Registrar Concesión
        $respConcesion = $this->post(route('quioscos.concesion.store'), [
            'quiosco_id' => $quiosco->id,
            'nombre_concesionario' => 'LUCIA TEST PERALTA',
            'dni_ruc' => '10445566779',
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-12-31',
            'canon_mensual' => 380.00,
            'garantia' => 380.00,
            'cumple_ley_alimentacion_saludable' => 'on',
        ]);
        $respConcesion->assertRedirect(route('quioscos.index'));

        $concesion = ConcesionQuiosco::where('nombre_concesionario', 'LUCIA TEST PERALTA')->first();
        $this->assertNotNull($concesion);
        $this->assertTrue($concesion->cumple_ley_alimentacion_saludable);

        // 3. Cobrar Canon Mensual de Arrendamiento
        $respPago = $this->post(route('quioscos.pago.store', $concesion->id), [
            'mes_correspondiente' => 'Abril 2026',
            'monto' => 380.00,
            'fecha_pago' => '2026-04-05',
            'metodo_pago' => 'EFECTIVO',
            'observaciones' => 'Cancelación puntual de quiosco',
        ]);
        $respPago->assertRedirect(route('quioscos.index'));

        // Validar que se bloquean pagos duplicados para el mismo mes
        $respPagoDuplicado = $this->post(route('quioscos.pago.store', $concesion->id), [
            'mes_correspondiente' => 'Abril 2026',
            'monto' => 380.00,
            'fecha_pago' => '2026-04-06',
            'metodo_pago' => 'EFECTIVO',
        ]);
        $respPagoDuplicado->assertSessionHas('error');

        $pagoQuiosco = PagoQuiosco::where('concesion_id', $concesion->id)->first();
        $this->assertNotNull($pagoQuiosco);
        $this->assertEquals('Abril 2026', $pagoQuiosco->mes_correspondiente);

        // 4. Verificar Cronograma Mensual del Contrato (el pago es al mes)
        $concesion->refresh();
        $this->assertEquals(1, $concesion->meses_pagados_count);
        $this->assertNotEmpty($concesion->cronograma_mensual);
        $mesAbril = collect($concesion->cronograma_mensual)->firstWhere('mes_nombre', 'Abril 2026');
        $this->assertNotNull($mesAbril);
        $this->assertTrue($mesAbril['pagado']);
        $this->assertEquals('PAGADO', $mesAbril['estado']);

        // 5. Ver Index con métricas de canon mensual y control mensual
        $respQUpdated = $this->get(route('quioscos.index'));
        $respQUpdated->assertStatus(200);
        $respQUpdated->assertSee('Canon Mensual Pactado');
        $respQUpdated->assertSee('Control Mensual');
        $respQUpdated->assertSee('Abr \'26');

        // 6. Ver Recibo Imprimible
        $reciboResp = $this->get(route('quioscos.recibo', $pagoQuiosco->id));
        $reciboResp->assertStatus(200);
        $reciboResp->assertSee('RECIBO DE ARRENDAMIENTO');
        $reciboResp->assertSee('ABRIL 2026');
        $reciboResp->assertSee('Cuota Mensual');

        // Limpieza de datos de prueba para mantener la BD limpia
        PagoQuiosco::where('concesion_id', $concesion->id)->delete();
        $concesion->delete();
        $quiosco->delete();
    }

    /**
     * Pilar 5: Fiscalización del Consejo de Vigilancia y Dictamen de Auditoría
     */
    public function test_consejo_de_vigilancia_dictamen_de_auditoria(): void
    {
        $this->actingAs($this->admin);

        $respVig = $this->get(route('vigilancia.index'));
        $respVig->assertStatus(200);
        $respVig->assertSee('Consejo de Vigilancia de la APAFA');

        // Limpiar cualquier dictamen previo del periodo de prueba
        DictamenVigilancia::where('periodo', 'I Trimestre 2026')->delete();

        // Registrar temporalmente un gasto para probar la regla de saldo deficitario
        $gastoDeficit = \App\Models\GastoPoa::create([
            'empresa_id' => 1,
            'user_id' => $this->admin->id,
            'categoria' => 'EMERGENCIAS_BIENESTAR',
            'titulo' => 'Gasto Temporal Auditoria Deficit',
            'descripcion' => 'Gasto de prueba para verificar regla contable de deficit',
            'monto' => 50000.00,
            'nro_comprobante' => 'F999-001',
            'proveedor' => 'Proveedor Temporal',
            'fecha' => now()->toDateString(),
        ]);

        // Validar que NO permita FAVORABLE cuando existe saldo deficitario / deuda
        $favorableErrorResp = $this->post(route('vigilancia.store'), [
            'periodo' => 'I Trimestre 2026',
            'titulo' => 'Auditoría a los Fondos de Cuotas y Arrendamiento de Quiosco',
            'tipo_dictamen' => 'FAVORABLE',
            'detalles_auditoria' => 'Intento favorable con saldo en contra',
        ]);
        $favorableErrorResp->assertSessionHasErrors('tipo_dictamen');

        // Emitir Dictamen como OBSERVADO (consistente con el saldo deficitario)
        $dictamenResp = $this->post(route('vigilancia.store'), [
            'periodo' => 'I Trimestre 2026',
            'titulo' => 'Auditoría a los Fondos de Cuotas y Arrendamiento de Quiosco',
            'tipo_dictamen' => 'OBSERVADO',
            'detalles_auditoria' => 'Se observa déficit temporal en cuenta corriente donde los egresos ejecutados superan los ingresos recaudados.',
            'observaciones_recomendaciones' => 'Mantener la digitalización de boletas y acelerar recaudación de cuotas ordinarias.',
        ]);
        $dictamenResp->assertRedirect(route('vigilancia.index'));

        // Limpiar gasto temporal de déficit
        $gastoDeficit->delete();

        $dictamen = DictamenVigilancia::where('periodo', 'I Trimestre 2026')->first();
        $this->assertNotNull($dictamen);
        $this->assertEquals('OBSERVADO', $dictamen->tipo_dictamen);

        // Validar que NO permita repetir el periodo del dictamen
        $dupResp = $this->post(route('vigilancia.store'), [
            'periodo' => 'I Trimestre 2026',
            'titulo' => 'Intento Duplicado',
            'tipo_dictamen' => 'OBSERVADO',
            'detalles_auditoria' => 'No debe permitirse duplicar el periodo.',
        ]);
        $dupResp->assertSessionHasErrors('periodo');

        // Ver Dictamen Imprimible
        $printResp = $this->get(route('vigilancia.imprimir', $dictamen->id));
        $printResp->assertStatus(200);
        $printResp->assertSee('Dictamen de Fiscalización y Auditoría Contable');
        $printResp->assertSee('OBSERVADO');

        // Limpieza de dictamen de prueba
        $dictamen->delete();
    }

    /**
     * Pilar 6: Registro de Vacancia y Emisión de Credencial Oficial de Directiva
     */
    public function test_movimiento_vacancia_directiva_y_credencial_oficial(): void
    {
        $this->actingAs($this->admin);

        // Limpiar registro previo si existe para evitar duplicado
        MovimientoDirectiva::where('directivo_saliente_dni', '12345678')->delete();

        // Registrar Vacancia y Asunción
        $movResp = $this->post(route('configuracion.movimientos.store'), [
            'cargo_nombre' => 'SECRETARIO(A)',
            'directivo_saliente_nombre' => 'PEDRO ALONSO CASTILLO',
            'directivo_saliente_dni' => '12345678',
            'causal_vacancia' => 'RENUNCIA_VOLUNTARIA',
            'fecha_vacancia' => '2026-09-15',
            'directivo_entrante_nombre' => 'GLORIA PAREDES RÍOS',
            'directivo_entrante_dni' => '87654321',
            'numero_acta_asamblea' => 'ACTA-003-2026',
        ]);
        $movResp->assertRedirect(route('configuracion.index', ['tab' => 'comite']));

        $this->assertDatabaseHas('movimientos_directiva', [
            'cargo_nombre' => 'SECRETARIO(A)',
            'causal_vacancia' => 'RENUNCIA_VOLUNTARIA',
            'directivo_entrante_nombre' => 'GLORIA PAREDES RÍOS',
        ]);

        // Ver Credencial Institucional para UGEL
        $credResp = $this->get(route('configuracion.credencial_directiva'));
        $credResp->assertStatus(200);
        $credResp->assertSee('Credencial de Reconocimiento y Recomposición Directiva');
        $credResp->assertSee('GLORIA PAREDES RÍOS');

        // Limpiar para mantener la BD limpia
        MovimientoDirectiva::where('directivo_saliente_dni', '12345678')->delete();
    }

    /**
     * Pilar 7: Consejo Directivo como Órgano de Gestión Democrático (Ley N° 28628)
     */
    public function test_consejo_directivo_organo_gestion_structure_and_transparency(): void
    {
        // 1. Verificar modelos de Cargo y métodos de clasificación estatutaria
        $cargoPres = Cargo::firstOrCreate(['empresa_id' => 1, 'nombre' => 'PRESIDENTE DE APAFA']);
        $cargoVice = Cargo::firstOrCreate(['empresa_id' => 1, 'nombre' => 'VICEPRESIDENTE DE APAFA']);
        $cargoSec = Cargo::firstOrCreate(['empresa_id' => 1, 'nombre' => 'SECRETARIO(A)']);
        $cargoTes = Cargo::firstOrCreate(['empresa_id' => 1, 'nombre' => 'TESORERO(A)']);
        $cargoVoc = Cargo::firstOrCreate(['empresa_id' => 1, 'nombre' => '1ER VOCAL']);
        $cargoVig = Cargo::firstOrCreate(['empresa_id' => 1, 'nombre' => 'PRESIDENTE CONSEJO DE VIGILANCIA']);
        $cargoOrdinario = Cargo::firstOrCreate(['empresa_id' => 1, 'nombre' => 'PADRE DE FAMILIA']);

        $this->assertTrue($cargoPres->isConsejoDirectivo());
        $this->assertTrue($cargoVice->isConsejoDirectivo());
        $this->assertTrue($cargoSec->isConsejoDirectivo());
        $this->assertTrue($cargoTes->isConsejoDirectivo());
        $this->assertTrue($cargoVoc->isConsejoDirectivo());
        $this->assertFalse($cargoVig->isConsejoDirectivo());
        $this->assertTrue($cargoVig->isConsejoVigilancia());
        $this->assertFalse($cargoOrdinario->isConsejoDirectivo());

        // 2. Asignar Presidente a padre de prueba
        $this->padre->update(['cargo_id' => $cargoPres->id]);

        // 3. Verificar renderizado en la Landing Page Pública
        $respLanding = $this->get(route('home'));
        $respLanding->assertStatus(200);
        $respLanding->assertSee('Consejo Directivo de la APAFA');
        $respLanding->assertSee('Órgano de gestión conformado por un presidente, vicepresidente, secretario, tesorero y vocales');
        $respLanding->assertSee('Presidente(a) de APAFA');
        $respLanding->assertSee('JUAN CARLOS');
        $respLanding->assertSee('Consejo de Vigilancia');

        // 4. Verificar vista de Configuración (Tab Comité)
        $this->actingAs($this->admin);
        $respConfig = $this->get(route('configuracion.index', ['tab' => 'comite']));
        $respConfig->assertStatus(200);
        $respConfig->assertSee('Consejo Directivo (Órgano de Gestión y Ejecución)');
        $respConfig->assertSee('Consejo de Vigilancia');
        $respConfig->assertSee('Elegidos democráticamente');
    }

    /**
     * Pilar 8: Regla de Periodicidad Bienal (Cada 2 Años) y Ventana Legal Obligatoria (1 Set - 30 Nov)
     */
    public function test_elecciones_bienales_cada_dos_anos_y_ventana_legal_septiembre_noviembre(): void
    {
        $this->actingAs($this->admin);

        // 1. Prohibir convocatoria fuera del periodo legal (ej. Junio)
        $respInvalido = $this->from(route('elecciones.index'))->post(route('elecciones.convocar'), [
            'año_electoral' => 2028,
            'nombre_proceso' => 'Elecciones Anticipadas Fuera de Plazo',
            'fecha_elecciones' => '2028-06-15',
        ]);
        $respInvalido->assertRedirect(route('elecciones.index'));
        $respInvalido->assertSessionHas('error');
        $this->assertDatabaseMissing('comicios_electorales', ['año_electoral' => 2028, 'fecha_elecciones' => '2028-06-15']);

        // 2. Convocatoria válida dentro de la ventana legal (Octubre del año electoral)
        $respValido = $this->post(route('elecciones.convocar'), [
            'año_electoral' => 2028,
            'nombre_proceso' => 'Elecciones Generales Bienio 2029 - 2030 (Ley 28628)',
            'fecha_elecciones' => '2028-10-20',
            'fecha_corte_padron' => '2028-08-31',
        ]);
        $respValido->assertSessionHas('success');

        $comicio2028 = ComicioElectoral::where('año_electoral', 2028)->first();
        $this->assertNotNull($comicio2028);
        $this->assertTrue($comicio2028->es_ventana_legal_valida);
        $this->assertEquals('Bienio 2029 - 2030', $comicio2028->bienio_electo);
        $this->assertEquals('Bienio 2027 - 2028', $comicio2028->bienio_saliente);

        // 3. Verificar renderizado en la vista con distintivo de ventana legal
        $respIndex = $this->get(route('elecciones.index', ['comicio_id' => $comicio2028->id]));
        $respIndex->assertStatus(200);
        $respIndex->assertSee('Ventana Legal Habilitada (01 Set - 30 Nov)');
        $respIndex->assertSee('Renovación Bienal (Cada 2 Años - Ley N.º 28628 Art. 43)');

        // Limpieza de datos de prueba
        $comicio2028->delete();
    }

    /**
     * Pilar 9: Relevo por Función Cumplida, No Asunción y Actualización Automática de Cargos
     */
    public function test_movimiento_funcion_cumplida_y_no_asuncion_de_cargo_con_relevo_automatico(): void
    {
        $this->actingAs($this->admin);

        // Crear cargos
        $cargoTesorero = Cargo::firstOrCreate(['empresa_id' => 1, 'nombre' => 'TESORERO(A) DE APAFA']);
        $cargoBase = Cargo::firstOrCreate(['empresa_id' => 1, 'nombre' => 'PADRE DE FAMILIA']);

        // Crear dos padres de prueba para el relevo
        $padreSaliente = PadreFamilia::create([
            'padre_dni' => '87650001',
            'padre_ape_pat' => 'SALIENTE',
            'padre_ape_mat' => 'TEST',
            'padre_nombres' => 'ROBERTO',
            'cargo_id' => $cargoTesorero->id,
            'empresa_id' => 1,
        ]);

        $padreEntrante = PadreFamilia::create([
            'padre_dni' => '87650002',
            'padre_ape_pat' => 'ENTRANTE',
            'padre_ape_mat' => 'TEST',
            'padre_nombres' => 'MARIA',
            'cargo_id' => $cargoBase->id,
            'empresa_id' => 1,
        ]);

        // 1. Caso: Función Cumplida (Conclusión de mandato) con relevo por nuevo titular
        $respMov = $this->post(route('configuracion.movimientos.store'), [
            'cargo_nombre' => 'TESORERO(A) DE APAFA',
            'directivo_saliente_nombre' => 'ROBERTO SALIENTE',
            'directivo_saliente_dni' => '87650001',
            'causal_vacancia' => 'FUNCION_CUMPLIDA',
            'fecha_vacancia' => '2026-10-30',
            'directivo_entrante_nombre' => 'MARIA ENTRANTE',
            'directivo_entrante_dni' => '87650002',
            'numero_acta_asamblea' => 'ACTA-010-2026',
        ]);
        $respMov->assertRedirect(route('configuracion.index', ['tab' => 'comite']));
        $respMov->assertSessionHas('success');

        // Verificar que el saliente perdió el cargo directivo y volvió a PADRE DE FAMILIA
        $padreSaliente->refresh();
        $this->assertEquals($cargoBase->id, $padreSaliente->cargo_id);

        // Verificar que el entrante asumió el cargo directivo de TESORERO
        $padreEntrante->refresh();
        $this->assertEquals($cargoTesorero->id, $padreEntrante->cargo_id);

        // 2. Caso: No Desea Asumir Cargo / Desistimiento Voluntario dejando vacante temporal
        $respDesistimiento = $this->post(route('configuracion.movimientos.store'), [
            'cargo_nombre' => 'TESORERO(A) DE APAFA',
            'directivo_saliente_nombre' => 'MARIA ENTRANTE',
            'directivo_saliente_dni' => '87650002',
            'causal_vacancia' => 'NO_ASUME_CARGO',
            'fecha_vacancia' => '2026-11-05',
            'directivo_entrante_nombre' => '',
            'numero_acta_asamblea' => 'ACTA-011-2026',
        ]);
        $respDesistimiento->assertRedirect(route('configuracion.index', ['tab' => 'comite']));

        $padreEntrante->refresh();
        $this->assertEquals($cargoBase->id, $padreEntrante->cargo_id);

        $this->assertDatabaseHas('movimientos_directiva', [
            'cargo_nombre' => 'TESORERO(A) DE APAFA',
            'causal_vacancia' => 'NO_ASUME_CARGO',
            'directivo_saliente_dni' => '87650002',
            'directivo_entrante_nombre' => 'VACANTE / POR DESIGNAR EN ASAMBLEA',
        ]);

        // Limpieza
        MovimientoDirectiva::whereIn('directivo_saliente_dni', ['87650001', '87650002'])->delete();
        $padreSaliente->delete();
        $padreEntrante->delete();
    }
}
