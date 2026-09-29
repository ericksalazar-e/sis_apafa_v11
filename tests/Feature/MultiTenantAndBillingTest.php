<?php

namespace Tests\Feature;

use App\Models\Cargo;
use App\Models\Empresa;
use App\Models\Estudiante;
use App\Models\PadreFamilia;
use App\Models\Pago;
use App\Models\User;
use App\Services\FacturacionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MultiTenantAndBillingTest extends TestCase
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
     * Test que el Dashboard cargue correctamente.
     */
    public function test_dashboard_loads_successfully(): void
    {
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard de Gestión');
        $response->assertSee('recaudacionChart');
    }

    /**
     * Test de Aislamiento Multi-tenant: los datos de una empresa no se mezclan con otra.
     */
    public function test_multi_tenant_data_isolation(): void
    {
        $empresa1 = Empresa::find(1);
        $empresa2 = Empresa::find(2);

        $this->assertNotNull($empresa1, 'Empresa 1 debe existir');
        $this->assertNotNull($empresa2, 'Empresa 2 debe existir');

        // Consultar con Tenant 1 en sesión
        session(['empresa_id' => $empresa1->id]);
        $padresTenant1 = PadreFamilia::all();

        // Consultar con Tenant 2 en sesión
        session(['empresa_id' => $empresa2->id]);
        $padresTenant2 = PadreFamilia::all();

        // Verificar que cada conjunto pertenezca exclusivamente a su tenant
        foreach ($padresTenant1 as $p) {
            $this->assertEquals($empresa1->id, $p->empresa_id);
        }

        foreach ($padresTenant2 as $p) {
            $this->assertEquals($empresa2->id, $p->empresa_id);
        }
    }

    /**
     * Test de Microservicio de Facturación Electrónica.
     */
    public function test_facturacion_service_emission(): void
    {
        session(['empresa_id' => 1]);
        $pago = Pago::first();
        $this->assertNotNull($pago, 'Debe haber al menos un pago registrado');

        $service = new FacturacionService;
        $resultado = $service->procesarComprobante($pago);

        $this->assertTrue($resultado['success']);
        $this->assertEquals('EMITIDO', $resultado['estado']);
        $this->assertNotEmpty($resultado['hash_cpe']);
        $this->assertNotEmpty($resultado['qr_string']);
    }

    /**
     * Test de Generación de Tarjeta de APAFA (Anverso y Reverso).
     */
    public function test_estudiante_tarjeta_generation(): void
    {
        session(['empresa_id' => 1]);
        $estudiante = Estudiante::first();
        $this->assertNotNull($estudiante, 'Debe existir un estudiante');

        $response = $this->get(route('estudiantes.tarjeta', $estudiante));
        $response->assertStatus(200);
        $response->assertSee('Control de Asistencia Escolar');
        $response->assertSee('Control de Asistencia Diaria');
        $response->assertSee($estudiante->nombre_completo);
        $response->assertSee($estudiante->correlativo);
    }

    /**
     * Test de Generación de Tarjeta de APAFA para el Padre con sus Hijos Matriculados.
     */
    public function test_padre_tarjeta_apafa_generation(): void
    {
        session(['empresa_id' => 1]);
        $padre = PadreFamilia::with('estudiantes')->first();
        $this->assertNotNull($padre, 'Debe existir un padre de familia');

        $response = $this->get(route('padres.tarjeta', $padre));
        $response->assertStatus(200);
        $response->assertSee('Asociación de Padres de Familia (APAFA)');
        $response->assertSee('CARNÉ DE ASOCIADO');
        $response->assertSee($padre->nombre_completo);
        $response->assertSee($padre->padre_dni);
        $response->assertSee('Estudiantes a su Cargo');
        $response->assertSee($padre->correlativo);
    }

    /**
     * Test de la Vista de Control de Asistencia Diaria Escolar.
     */
    public function test_asistencia_estudiantes_view_loads(): void
    {
        session(['empresa_id' => 1]);

        $response = $this->get(route('asistencia.estudiantes.index'));
        $response->assertStatus(200);
        $response->assertSee('Control de Asistencia Escolar');
        $response->assertSee('Matriculados');
        $response->assertSee('Presentes Hoy');
        $response->assertSee('codigo-input');
        $response->assertSee('name="nivel"', false);
        $response->assertSee('name="grado"', false);
        $response->assertSee('name="seccion"', false);
    }

    /**
     * Test de Filtrado de Asistencias por Nivel, Grado y Sección.
     */
    public function test_asistencia_estudiantes_filter_by_nivel_grado_and_seccion(): void
    {
        $admin = User::first();
        session(['empresa_id' => $admin->empresa_id]);

        $padre = PadreFamilia::first();

        // 1. Crear dos alumnos en diferentes niveles, grados y secciones
        $dni1 = '8'.rand(1000000, 9999999);
        $alumnoSecundaria = Estudiante::create([
            'empresa_id' => $admin->empresa_id,
            'padre_familia_id' => $padre->id,
            'dni' => $dni1,
            'nombres' => 'SEBASTIAN',
            'ape_pat' => 'VALVERDE',
            'ape_mat' => 'ROJAS',
            'nivel' => 'Secundaria',
            'grado' => '5to',
            'seccion' => 'C',
            'turno' => 'Mañana',
            'correlativo' => 'EST-T001',
            'estado' => 1,
        ]);

        $dni2 = '8'.rand(1000000, 9999999);
        $alumnoPrimaria = Estudiante::create([
            'empresa_id' => $admin->empresa_id,
            'padre_familia_id' => $padre->id,
            'dni' => $dni2,
            'nombres' => 'LUCIA',
            'ape_pat' => 'CABRERA',
            'ape_mat' => 'SOTO',
            'nivel' => 'Primaria',
            'grado' => '2do',
            'seccion' => 'A',
            'turno' => 'Tarde',
            'correlativo' => 'EST-T002',
            'estado' => 1,
        ]);

        $dni3 = '8'.rand(1000000, 9999999);
        $alumnoInicial = Estudiante::create([
            'empresa_id' => $admin->empresa_id,
            'padre_familia_id' => $padre->id,
            'dni' => $dni3,
            'nombres' => 'MATEO',
            'ape_pat' => 'RODRIGUEZ',
            'ape_mat' => 'PAREDES',
            'nivel' => 'Inicial',
            'grado' => '5 Años',
            'seccion' => 'B',
            'turno' => 'Mañana',
            'correlativo' => 'EST-T003',
            'estado' => 1,
        ]);

        $hoy = date('Y-m-d');

        // 2. Registrar asistencia para los alumnos hoy
        $asist1 = \App\Models\AsistenciaEstudiante::create([
            'empresa_id' => $admin->empresa_id,
            'estudiante_id' => $alumnoSecundaria->id,
            'fecha' => $hoy,
            'hora_ingreso' => '07:45:00',
            'turno' => 'Mañana',
            'estado' => 'PUNTUAL',
            'observacion' => 'Ingreso puntual',
        ]);

        $asist2 = \App\Models\AsistenciaEstudiante::create([
            'empresa_id' => $admin->empresa_id,
            'estudiante_id' => $alumnoPrimaria->id,
            'fecha' => $hoy,
            'hora_ingreso' => '13:10:00',
            'turno' => 'Tarde',
            'estado' => 'PUNTUAL',
            'observacion' => 'Ingreso puntual',
        ]);

        $asist3 = \App\Models\AsistenciaEstudiante::create([
            'empresa_id' => $admin->empresa_id,
            'estudiante_id' => $alumnoInicial->id,
            'fecha' => $hoy,
            'hora_ingreso' => '08:00:00',
            'turno' => 'Mañana',
            'estado' => 'PUNTUAL',
            'observacion' => 'Ingreso puntual Inicial',
        ]);

        // 3. Filtrar por Nivel Secundaria, Grado 5to y Sección C
        $responseSec = $this->actingAs($admin)->get(route('asistencia.estudiantes.index', [
            'fecha' => $hoy,
            'nivel' => 'Secundaria',
            'grado' => '5to',
            'seccion' => 'C',
        ]));

        $responseSec->assertOk();
        $responseSec->assertSee("asistencia-row-{$dni1}");
        $responseSec->assertDontSee("asistencia-row-{$dni2}");
        $responseSec->assertDontSee("asistencia-row-{$dni3}");

        // 4. Filtrar por Nivel Primaria, Grado 2do y Sección A
        $responsePri = $this->actingAs($admin)->get(route('asistencia.estudiantes.index', [
            'fecha' => $hoy,
            'nivel' => 'Primaria',
            'grado' => '2do',
            'seccion' => 'A',
        ]));

        $responsePri->assertOk();
        $responsePri->assertSee("asistencia-row-{$dni2}");
        $responsePri->assertDontSee("asistencia-row-{$dni1}");
        $responsePri->assertDontSee("asistencia-row-{$dni3}");

        // 5. Filtrar por Nivel Inicial, Grado 5 Años y Sección B
        $responseIni = $this->actingAs($admin)->get(route('asistencia.estudiantes.index', [
            'fecha' => $hoy,
            'nivel' => 'Inicial',
            'grado' => '5 Años',
            'seccion' => 'B',
        ]));

        $responseIni->assertOk();
        $responseIni->assertSee("asistencia-row-{$dni3}");
        $responseIni->assertDontSee("asistencia-row-{$dni1}");
        $responseIni->assertDontSee("asistencia-row-{$dni2}");

        // 6. Búsqueda por texto (DNI)
        $responseSearch = $this->actingAs($admin)->get(route('asistencia.estudiantes.index', [
            'fecha' => $hoy,
            'search' => $dni1,
        ]));

        $responseSearch->assertOk();
        $responseSearch->assertSee("asistencia-row-{$dni1}");
        $responseSearch->assertDontSee("asistencia-row-{$dni2}");
        $responseSearch->assertDontSee("asistencia-row-{$dni3}");

        // 7. Limpieza de datos de prueba
        $asist1->delete();
        $asist2->delete();
        $asist3->delete();
        $alumnoSecundaria->delete();
        $alumnoPrimaria->delete();
        $alumnoInicial->delete();
    }

    /**
     * Test de Escaneo de Tarjeta de Asistencia Escolar (Ingreso y Salida).
     */
    public function test_asistencia_estudiante_scanning_flow(): void
    {
        session(['empresa_id' => 1]);
        $estudiante = Estudiante::first();
        $this->assertNotNull($estudiante, 'Debe existir un estudiante');

        // Limpiar registro previo del día para esta prueba
        \App\Models\AsistenciaEstudiante::where('estudiante_id', $estudiante->id)
            ->where('fecha', date('Y-m-d'))
            ->delete();

        // 1. Primer escaneo: Registro de Ingreso
        $response1 = $this->post(route('asistencia.estudiantes.escanear'), [
            'codigo' => $estudiante->dni,
        ]);
        $response1->assertRedirect();
        $response1->assertSessionHas('success');

        $asistencia = \App\Models\AsistenciaEstudiante::where('estudiante_id', $estudiante->id)
            ->where('fecha', date('Y-m-d'))
            ->first();
        $this->assertNotNull($asistencia, 'Debe haberse creado el registro de asistencia');
        $this->assertNotEmpty($asistencia->hora_ingreso);
        $this->assertNull($asistencia->hora_salida);

        // 2. Segundo escaneo: Registro de Salida
        $response2 = $this->post(route('asistencia.estudiantes.escanear'), [
            'codigo' => "ASISTENCIA_ESCOLAR|DNI:{$estudiante->dni}|IIEE:1",
        ]);
        $response2->assertRedirect();
        $response2->assertSessionHas('success');

        $asistencia->refresh();
        $this->assertNotNull($asistencia->hora_salida, 'El segundo escaneo debe registrar la hora de salida');
        $asistencia->delete();
    }

    /**
     * Test de Configuración de Horarios por Turno (Mañana, Tarde, Noche).
     */
    public function test_configurar_horarios_turnos(): void
    {
        session(['empresa_id' => 1]);

        $response = $this->post(route('asistencia.estudiantes.horarios'), [
            'horarios' => [
                'Mañana' => [
                    'hora_ingreso' => '07:20',
                    'hora_limite_puntual' => '07:45',
                    'hora_salida' => '13:15',
                    'activo' => '1',
                ],
                'Tarde' => [
                    'hora_ingreso' => '13:00',
                    'hora_limite_puntual' => '13:20',
                    'hora_salida' => '18:15',
                    'activo' => '1',
                ],
                'Noche' => [
                    'hora_ingreso' => '18:30',
                    'hora_limite_puntual' => '18:50',
                    'hora_salida' => '22:15',
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $horarioManana = \App\Models\HorarioTurno::where('empresa_id', 1)->where('turno', 'Mañana')->first();
        $this->assertNotNull($horarioManana);
        $this->assertEquals('07:45:00', strlen($horarioManana->hora_limite_puntual) === 5 ? $horarioManana->hora_limite_puntual.':00' : $horarioManana->hora_limite_puntual);
        $this->assertTrue($horarioManana->activo);
    }

    /**
     * Test de Justificación de una Tardanza.
     */
    public function test_justificar_tardanza(): void
    {
        session(['empresa_id' => 1]);
        $estudiante = Estudiante::first();

        // Crear una tardanza
        $asistencia = \App\Models\AsistenciaEstudiante::create([
            'empresa_id' => 1,
            'estudiante_id' => $estudiante->id,
            'fecha' => date('Y-m-d'),
            'hora_ingreso' => '08:35:00',
            'turno' => 'Mañana',
            'estado' => 'TARDANZA',
            'justificado' => false,
            'observacion' => 'Ingreso tarde',
        ]);

        $response = $this->post(route('asistencia.estudiantes.justificar', $asistencia->id), [
            'motivo_justificacion' => 'Cita médica o atención de salud',
            'documento_sustento' => 'Certificado N° 9988-MINSA',
            'observacion' => 'Atención odontológica de urgencia',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $asistencia->refresh();
        $this->assertTrue($asistencia->justificado);
        $this->assertEquals('TARDANZA JUSTIFICADA', $asistencia->estado);
        $this->assertEquals('Cita médica o atención de salud', $asistencia->motivo_justificacion);
        $this->assertEquals('Certificado N° 9988-MINSA', $asistencia->documento_sustento);
        $this->assertNotNull($asistencia->fecha_justificacion);
        $asistencia->delete();
    }

    /**
     * Test de Registro de Inasistencia Justificada previa.
     */
    public function test_registrar_inasistencia_justificada(): void
    {
        session(['empresa_id' => 1]);
        $estudiante = Estudiante::first();
        $fechaManana = date('Y-m-d', strtotime('+1 day'));

        $response = $this->post(route('asistencia.estudiantes.justificar_falta'), [
            'estudiante_id' => $estudiante->id,
            'fecha' => $fechaManana,
            'motivo_justificacion' => 'Problemas familiares graves',
            'documento_sustento' => 'Solicitud escrita firmada por el apoderado',
            'observacion' => 'Permiso por viaje de salud familiar',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $inasistencia = \App\Models\AsistenciaEstudiante::where('estudiante_id', $estudiante->id)
            ->where('fecha', $fechaManana)
            ->first();

        $this->assertNotNull($inasistencia);
        $this->assertEquals('FALTA JUSTIFICADA', $inasistencia->estado);
        $this->assertTrue($inasistencia->justificado);
        $this->assertNull($inasistencia->hora_ingreso);
        $this->assertEquals('Problemas familiares graves', $inasistencia->motivo_justificacion);
        $inasistencia->delete();
    }

    /**
     * Test de Carga de la Pantalla de Login con Selector de Roles.
     */
    public function test_login_screen_loads(): void
    {
        auth()->logout();
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('SIS-APAFA');
        $response->assertSee('Secretario');
        $response->assertSee('Tesorero');
        $response->assertSee('C. Vigilancia');
        $response->assertSee('Vocal / Puerta');
        $response->assertSee('Director I.E.');
    }

    /**
     * Test de Autenticación con Credenciales Válidas.
     */
    public function test_user_authentication_flow(): void
    {
        auth()->logout();

        $response = $this->post('/login', [
            'login' => 'secretario@apafa.edu.pe',
            'password' => 'secretario123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertEquals('SECRETARIO', auth()->user()->rol);

        // Logout
        $logoutResponse = $this->post('/logout');
        $logoutResponse->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * Test de Redirección Inteligente de Rol: Vocal / Operador va directo a Portería.
     */
    public function test_vocal_operador_redirects_to_porteria(): void
    {
        auth()->logout();

        $response = $this->post('/login', [
            'login' => 'vocal@apafa.edu.pe',
            'password' => 'vocal123',
        ]);

        $response->assertRedirect(route('asistencia.estudiantes.index'));
        $this->assertAuthenticated();
        $this->assertEquals('VOCAL_OPERADOR', auth()->user()->rol);
    }

    /**
     * Test de Fiscalización: Comité de Vigilancia tiene acceso de Solo Lectura y NO puede alterar datos.
     */
    public function test_comite_vigilancia_is_read_only(): void
    {
        $vigilante = User::withoutGlobalScopes()->where('rol', 'COMITE_VIGILANCIA')->first();
        $this->actingAs($vigilante);

        // 1. Puede consultar y auditar pagos
        $responseView = $this->get(route('pagos.index'));
        $responseView->assertStatus(200);

        // 2. NO puede registrar pagos / alterar la caja
        $padre = PadreFamilia::first();
        $aporte = \App\Models\Aporte::first();
        $responseWrite = $this->post(route('pagos.store'), [
            'padre_familia_id' => $padre->id,
            'aporte_id' => $aporte->id,
            'monto' => 50.00,
            'metodo_pago' => 'EFECTIVO',
            'tipo_comprobante' => 'TICKET_BOLETA',
        ]);

        $responseWrite->assertStatus(403);
    }

    /**
     * Test de la Landing Page pública para padres de familia.
     */
    public function test_landing_page_renders_search_interface(): void
    {
        auth()->logout();

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Portal Ciudadano de Consulta Escolar y APAFA');
        $response->assertSee('Consulta de Aportes, Asambleas');
        $response->assertSee('y Asistencia de sus Hijos en Clases');
        $response->assertSee('Ingrese su N° de DNI');
    }

    /**
     * Test de Consulta Pública: Padre filtra sus asistencias a Asambleas y las asistencias a clases de sus hijos.
     */
    public function test_parent_filters_own_attendances_and_children_attendances(): void
    {
        auth()->logout();

        $padre = PadreFamilia::withoutGlobalScopes()->with(['estudiantes', 'reuniones'])->first();
        $this->assertNotNull($padre, 'Debe existir un padre de familia para la consulta');

        $response = $this->get('/?dni='.$padre->padre_dni);
        $response->assertStatus(200);
        $response->assertSee($padre->padre_nombres);
        $response->assertSee($padre->padre_dni);
        $response->assertSee('Asambleas y Convocatorias de APAFA');
        $response->assertSee('Aportes y Cuotas de la APAFA');
        $response->assertSee('Total Cuotas');
        $response->assertSee('Total Pagado');
        $response->assertSee('Deuda Pendiente');
        $response->assertSee('Asistencia de Estudiantes a Cargo (Hijos, Nietos, Sobrinos)');
        $response->assertDontSee('Ver Mi Carné de APAFA');
        $response->assertSee('Consulta Segura');

        // Verificar que aparezcan los datos de sus hijos
        if ($padre->estudiantes->count() > 0) {
            $hijo = $padre->estudiantes->first();
            $response->assertSee($hijo->nombres);
            $response->assertSee($hijo->dni);
            $response->assertDontSee('Fotocheck Escolar');
            $response->assertSee('Estudiante Regular');
        }
    }

    /**
     * Test de Cumplimiento Legal Ley 28628: Cuota ordinaria tope 1.5% de la UIT por padre.
     */
    public function test_cuota_ordinaria_cannot_exceed_one_and_half_percent_of_uit(): void
    {
        $admin = User::withoutGlobalScopes()->where('rol', 'ADMIN')->first();
        session(['empresa_id' => 1]);

        // Intentar registrar una cuota ordinaria de S/ 100.00 con UIT S/ 5,350.00 (tope es S/ 80.25)
        $response = $this->actingAs($admin)->post(route('aportes.store'), [
            'titulo' => 'CUOTA EXCESIVA ILEGAL',
            'tipo' => 'ORDINARIA',
            'monto' => 100.00,
            'uit_vigente' => 5350.00,
            'fecha' => now()->toDateString(),
            'descripcion' => 'Prueba de validación legal',
        ]);

        $response->assertSessionHasErrors('monto');

        // Registrar una cuota ordinaria válida de S/ 60.00 (dentro del tope legal de S/ 80.25)
        $uniqueTitulo = 'CUOTA ORDINARIA LEGAL '.uniqid();
        $responseValida = $this->actingAs($admin)->post(route('aportes.store'), [
            'titulo' => $uniqueTitulo,
            'tipo' => 'ORDINARIA',
            'monto' => 60.00,
            'uit_vigente' => 5350.00,
            'fecha' => now()->toDateString(),
            'descripcion' => 'Aprobada en asamblea general',
        ]);

        $responseValida->assertRedirect(route('aportes.index'));
        $responseValida->assertSessionHas('success');

        // Comprobar que se asignó por Padre de Familia y no duplicado por hijos
        $padreConVariosHijos = PadreFamilia::has('estudiantes', '>=', 2)->first();
        if ($padreConVariosHijos) {
            $cuotaAsignada = \App\Models\AportePadre::where('padre_familia_id', $padreConVariosHijos->id)
                ->whereHas('aporte', function ($q) use ($uniqueTitulo) {
                    $q->where('titulo', $uniqueTitulo);
                })->count();

            // Debe ser exactamente 1 registro de cuota, aunque tenga 2 o más hijos
            $this->assertEquals(1, $cuotaAsignada, 'La cuota ordinaria debe asignarse exactamente una sola vez por padre de familia.');
        }

        // Limpieza de aporte de prueba para evitar contaminación de la base de datos
        \App\Models\Aporte::withoutGlobalScopes()->where('titulo', $uniqueTitulo)->delete();
    }

    /**
     * Test de Eliminación de Aportes sin recaudación y Protección contra duplicados.
     */
    public function test_aporte_eliminar_y_prevencion_duplicados(): void
    {
        $admin = User::withoutGlobalScopes()->where('rol', 'ADMIN')->first();
        session(['empresa_id' => 1]);

        // 1. Crear aporte de prueba
        $tituloTemp = 'APORTE PRUEBA ELIMINAR '.uniqid();
        $this->actingAs($admin)->post(route('aportes.store'), [
            'titulo' => $tituloTemp,
            'tipo' => 'EXTRAORDINARIA',
            'monto' => 25.00,
            'fecha' => now()->toDateString(),
            'descripcion' => 'Aporte temporal para prueba de eliminación',
        ]);

        $aporte = \App\Models\Aporte::where('titulo', $tituloTemp)->first();
        $this->assertNotNull($aporte);

        // 2. Intentar duplicar el aporte en el mismo año debe ser rechazado
        $dupResp = $this->actingAs($admin)->post(route('aportes.store'), [
            'titulo' => $tituloTemp,
            'tipo' => 'EXTRAORDINARIA',
            'monto' => 25.00,
            'fecha' => now()->toDateString(),
        ]);
        $dupResp->assertSessionHasErrors('titulo');

        // 3. Eliminar el aporte sin recaudación debe ser exitoso
        $delResp = $this->actingAs($admin)->delete(route('aportes.destroy', $aporte->id));
        $delResp->assertRedirect(route('aportes.index'));
        $delResp->assertSessionHas('success');
        $this->assertDatabaseMissing('aportes', ['id' => $aporte->id]);

        // 4. Intentar eliminar un aporte que ya tiene pagos (Aporte 1) debe ser bloqueado por auditoría contable
        $aporteConPagos = \App\Models\Aporte::find(1);
        if ($aporteConPagos) {
            $delBlockedResp = $this->actingAs($admin)->delete(route('aportes.destroy', $aporteConPagos->id));
            $delBlockedResp->assertSessionHas('error');
            $this->assertDatabaseHas('aportes', ['id' => 1]);
        }
    }

    /**
     * Test de Gestión y Auditoría de Gastos del POA (Ley 28628).
     */
    public function test_gastos_poa_view_and_budget_transparency(): void
    {
        $admin = User::withoutGlobalScopes()->where('rol', 'ADMIN')->first();
        session(['empresa_id' => 1]);

        // Cargar vista de gastos POA
        $response = $this->actingAs($admin)->get(route('gastos_poa.index'));
        $response->assertStatus(200);
        $response->assertSee('Ejecución Presupuestal del Plan Operativo Anual (POA)');
        $response->assertSee('Total Recaudado');
        $response->assertSee('Total Invertido');
        $response->assertSee('Saldo Disponible en Cuenta');

        // Registrar un nuevo gasto del POA en Deporte y Psicomotricidad
        $postResponse = $this->actingAs($admin)->post(route('gastos_poa.store'), [
            'categoria' => 'DEPORTE_LUDICO',
            'titulo' => 'Tableros de Ajedrez y Balones de Básquet',
            'descripcion' => 'Aprobado en sesión extraordinaria de la APAFA',
            'monto' => 450.00,
            'nro_comprobante' => 'B001-9982',
            'proveedor' => 'Deportes del Centro SAC',
            'fecha' => now()->toDateString(),
        ]);

        $postResponse->assertRedirect(route('gastos_poa.index'));
        $postResponse->assertSessionHas('success');

        $this->assertDatabaseHas('gastos_poa', [
            'titulo' => 'Tableros de Ajedrez y Balones de Básquet',
            'monto' => 450.00,
        ]);

        // Limpieza de gasto POA de prueba para no contaminar la base de datos
        \App\Models\GastoPoa::withoutGlobalScopes()
            ->where('titulo', 'Tableros de Ajedrez y Balones de Básquet')
            ->delete();
    }

    /**
     * Test que la Landing Page pública muestre la Rendición de Cuentas y el destino del dinero de la APAFA.
     */
    public function test_landing_page_renders_poa_transparency_section(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('¿En qué se invierte el dinero recaudado de la APAFA?');
        $response->assertSee('Plan Operativo Anual (POA)');
        $response->assertSee('Cuentas Claras y Auditadas');
        $response->assertSee('Total Recaudado (Aportes)');
        $response->assertSee('Total Invertido en el Colegio');
        $response->assertSee('Saldo Disponible en Banco');
    }

    /**
     * Test de Parentesco Flexible: Padre, Madre, Ambos, Tíos y Cónyuge.
     */
    public function test_parentesco_padre_o_madre_o_ambos_and_conyuge(): void
    {
        $admin = User::withoutGlobalScopes()->where('rol', 'ADMIN')->first();
        session(['empresa_id' => 1]);

        $tipoAmbos = \App\Models\TipoConsanguinidad::where('nombre', 'AMBOS (PADRE Y MADRE)')->first();
        $this->assertNotNull($tipoAmbos);

        $dniTest = '7'.rand(1000000, 9999999);
        $response = $this->actingAs($admin)->post(route('padres.store'), [
            'padre_dni' => $dniTest,
            'padre_nombres' => 'CARLOS ALBERTO',
            'padre_ape_pat' => 'MENDOZA',
            'padre_ape_mat' => 'FLORES',
            'tipo_consanguinidad_id' => $tipoAmbos->id,
            'conyuge_nombres' => 'LUCIA ELENA VILCA RAMOS',
            'conyuge_dni' => '45678912',
            'conyuge_celular' => '987112233',
            'celular' => '999887766',
            'direccion' => 'Av. Los Héroes 450',
        ]);

        $padreCreado = PadreFamilia::where('padre_dni', $dniTest)->first();
        $this->assertNotNull($padreCreado);
        $this->assertEquals('AMBOS (PADRE Y MADRE)', $padreCreado->parentesco_texto);
        $this->assertEquals('CARLOS ALBERTO MENDOZA FLORES & LUCIA ELENA VILCA RAMOS', $padreCreado->representacion_familiar);

        // Verificar que en la vista de padres se muestre el cónyuge y el parentesco
        $viewPadres = $this->actingAs($admin)->get(route('padres.index', ['search' => $dniTest]));
        $viewPadres->assertStatus(200);
        $viewPadres->assertSee('AMBOS (PADRE Y MADRE)');
        $viewPadres->assertSee('LUCIA ELENA VILCA RAMOS');

        // Verificar en el carné de APAFA
        $viewTarjeta = $this->actingAs($admin)->get(route('padres.tarjeta', $padreCreado));
        $viewTarjeta->assertStatus(200);
        $viewTarjeta->assertSee('Asociado (AMBOS (PADRE Y MADRE)):');
        $viewTarjeta->assertSee('LUCIA ELENA VILCA RAMOS');

        // Verificar que ABUELO O ABUELA y HERMANO O HERMANA MAYOR existen y pueden registrarse
        $tipoAbuelo = \App\Models\TipoConsanguinidad::where('nombre', 'ABUELO O ABUELA')->first();
        $this->assertNotNull($tipoAbuelo, 'Debe existir ABUELO O ABUELA en el catálogo');

        $tipoHermanoMayor = \App\Models\TipoConsanguinidad::where('nombre', 'HERMANO O HERMANA MAYOR')->first();
        $this->assertNotNull($tipoHermanoMayor, 'Debe existir HERMANO O HERMANA MAYOR en el catálogo');

        $dniAbuelo = '7'.rand(1000000, 9999999);
        $this->actingAs($admin)->post(route('padres.store'), [
            'padre_dni' => $dniAbuelo,
            'padre_nombres' => 'MANUEL',
            'padre_ape_pat' => 'QUISPE',
            'padre_ape_mat' => 'CONDORI',
            'tipo_consanguinidad_id' => $tipoAbuelo->id,
            'conyuge_nombres' => 'ROSA FLORES',
        ]);

        $abueloCreado = PadreFamilia::where('padre_dni', $dniAbuelo)->first();
        $this->assertNotNull($abueloCreado);
        $this->assertEquals('ABUELO O ABUELA', $abueloCreado->parentesco_texto);

        // Limpieza de datos de prueba
        $padreCreado->delete();
        $abueloCreado->delete();
    }

    /**
     * Test: Un apoderado puede tener a su cargo estudiantes con distintos parentescos (hijos, nietos, sobrinos)
     * manteniendo una única cuota APAFA según Ley N° 28628 y viéndose reflejado en carné y landing.
     */
    public function test_apoderado_with_multiple_students_different_relationships_and_single_apafa_fee(): void
    {
        $admin = User::withoutGlobalScopes()->where('rol', 'ADMIN')->first();
        session(['empresa_id' => 1]);

        $tipoAbuelo = \App\Models\TipoConsanguinidad::where('nombre', 'ABUELO O ABUELA')->first();

        // 1. Registrar apoderado (ej. Abuelo/Tutor)
        $dniApoderado = '8'.rand(1000000, 9999999);
        $this->actingAs($admin)->post(route('padres.store'), [
            'padre_dni' => $dniApoderado,
            'padre_nombres' => 'SEGUNDO TOMAS',
            'padre_ape_pat' => 'QUISPE',
            'padre_ape_mat' => 'CONDORI',
            'tipo_consanguinidad_id' => $tipoAbuelo ? $tipoAbuelo->id : 1,
            'celular' => '955443322',
            'direccion' => 'Jr. Las Palmeras 123',
        ]);

        $apoderado = PadreFamilia::where('padre_dni', $dniApoderado)->first();
        $this->assertNotNull($apoderado);

        // 2. Asociar 3 estudiantes con diferentes vínculos familiares: Hijo, Nieto, Sobrino
        $dniHijo = '7'.rand(1000000, 9999999);
        $dniNieto = '7'.rand(1000000, 9999999);
        $dniSobrino = '7'.rand(1000000, 9999999);

        // Hijo
        $this->actingAs($admin)->post(route('estudiantes.store'), [
            'dni' => $dniHijo,
            'nombres' => 'MATEO SEGUNDO',
            'ape_pat' => 'QUISPE',
            'ape_mat' => 'FLORES',
            'padre_familia_id' => $apoderado->id,
            'parentesco' => 'HIJO(A)',
            'nivel' => 'PRIMARIA',
            'grado' => '6to',
            'seccion' => 'A',
            'turno' => 'Mañana',
        ]);

        // Nieto
        $this->actingAs($admin)->post(route('estudiantes.store'), [
            'dni' => $dniNieto,
            'nombres' => 'LUCAS GABRIEL',
            'ape_pat' => 'QUISPE',
            'ape_mat' => 'RAMOS',
            'padre_familia_id' => $apoderado->id,
            'parentesco' => 'NIETO(A)',
            'nivel' => 'PRIMARIA',
            'grado' => '1ro',
            'seccion' => 'B',
            'turno' => 'Mañana',
        ]);

        // Sobrino
        $this->actingAs($admin)->post(route('estudiantes.store'), [
            'dni' => $dniSobrino,
            'nombres' => 'SEBASTIAN DANIEL',
            'ape_pat' => 'PAREDES',
            'ape_mat' => 'QUISPE',
            'padre_familia_id' => $apoderado->id,
            'parentesco' => 'SOBRINO(A)',
            'nivel' => 'SECUNDARIA',
            'grado' => '3ro',
            'seccion' => 'C',
            'turno' => 'Tarde',
        ]);

        $apoderado->refresh();
        $this->assertEquals(3, $apoderado->estudiantes()->count(), 'El apoderado debe tener 3 estudiantes a su cargo');

        $estudianteNieto = Estudiante::where('dni', $dniNieto)->first();
        $this->assertNotNull($estudianteNieto);
        $this->assertEquals('NIETO(A)', $estudianteNieto->parentesco);

        // 3. Verificar que en la tarjeta de APAFA del apoderado se listen los 3 con sus vínculos
        $viewPadreCard = $this->actingAs($admin)->get(route('padres.tarjeta', $apoderado));
        $viewPadreCard->assertStatus(200);
        $viewPadreCard->assertSee('Estudiantes a su Cargo (3):');
        $viewPadreCard->assertSee('[HIJO(A)]');
        $viewPadreCard->assertSee('[NIETO(A)]');
        $viewPadreCard->assertSee('[SOBRINO(A)]');

        // 4. Verificar que en la tarjeta escolar individual del nieto figure su vínculo y apoderado
        $viewEstudianteCard = $this->actingAs($admin)->get(route('estudiantes.tarjeta', $estudianteNieto));
        $viewEstudianteCard->assertStatus(200);
        $viewEstudianteCard->assertSee('Vínculo con Apoderado:');
        $viewEstudianteCard->assertSee('NIETO(A)');
        $viewEstudianteCard->assertSee('APODERADO (NIETO(A)):');
        $viewEstudianteCard->assertSee($apoderado->nombre_completo);

        // 5. Verificar que en la consulta pública de la Landing Page se muestren los 3 estudiantes y sus chips
        $viewLanding = $this->get(route('consulta.padre', ['dni' => $dniApoderado]));
        $viewLanding->assertStatus(200);
        $viewLanding->assertSee('Asistencia de Estudiantes a Cargo (Hijos, Nietos, Sobrinos)');
        $viewLanding->assertSee('MATEO SEGUNDO');
        $viewLanding->assertSee('LUCAS GABRIEL');
        $viewLanding->assertSee('SEBASTIAN DANIEL');
        $viewLanding->assertSee('HIJO(A)');
        $viewLanding->assertSee('NIETO(A)');
        $viewLanding->assertSee('SOBRINO(A)');

        // Limpieza de datos de prueba
        Estudiante::where('padre_familia_id', $apoderado->id)->delete();
        $apoderado->delete();
    }

    /**
     * Test de Filtrado de Estudiantes por Nivel (Inicial, Primaria, Secundaria), Grado y Sección.
     */
    public function test_estudiantes_filter_by_nivel_grado_and_seccion(): void
    {
        $admin = User::withoutGlobalScopes()->where('rol', 'ADMIN')->first();
        session(['empresa_id' => 1]);

        $padre = PadreFamilia::create([
            'empresa_id' => 1,
            'padre_dni' => '9'.rand(1000000, 9999999),
            'padre_nombres' => 'PADRE_TEST_FILTROS',
            'padre_ape_pat' => 'TEST',
            'padre_ape_mat' => 'TEST',
            'tipo_consanguinidad_id' => 1,
            'estado' => 1,
        ]);

        // Crear alumnos de prueba para cada nivel
        $dniInicial = '7'.rand(1000000, 9999999);
        $alumnoInicial = Estudiante::create([
            'empresa_id' => 1,
            'padre_familia_id' => $padre->id,
            'dni' => $dniInicial,
            'nombres' => 'VALENTINA INES',
            'ape_pat' => 'ROCA',
            'ape_mat' => 'SOTO',
            'nivel' => 'Inicial',
            'grado' => '4 Años',
            'seccion' => 'A',
            'turno' => 'Mañana',
            'parentesco' => 'HIJO(A)',
        ]);

        $dniPrimaria = '7'.rand(1000000, 9999999);
        $alumnoPrimaria = Estudiante::create([
            'empresa_id' => 1,
            'padre_familia_id' => $padre->id,
            'dni' => $dniPrimaria,
            'nombres' => 'RODRIGO ALONSO',
            'ape_pat' => 'ROCA',
            'ape_mat' => 'SOTO',
            'nivel' => 'Primaria',
            'grado' => '4to',
            'seccion' => 'B',
            'turno' => 'Tarde',
            'parentesco' => 'HIJO(A)',
        ]);

        $dniSecundaria = '7'.rand(1000000, 9999999);
        $alumnoSecundaria = Estudiante::create([
            'empresa_id' => 1,
            'padre_familia_id' => $padre->id,
            'dni' => $dniSecundaria,
            'nombres' => 'FABIANA LUCIA',
            'ape_pat' => 'ROCA',
            'ape_mat' => 'SOTO',
            'nivel' => 'Secundaria',
            'grado' => '5to',
            'seccion' => 'C',
            'turno' => 'Mañana',
            'parentesco' => 'SOBRINO(A)',
        ]);

        // 1. Filtrar solo por Nivel Inicial
        $resInicial = $this->actingAs($admin)->get(route('estudiantes.index', ['nivel' => 'Inicial']));
        $resInicial->assertStatus(200);
        $resInicial->assertSee('VALENTINA INES');
        $resInicial->assertSee('4 Años');
        $resInicial->assertDontSee('RODRIGO ALONSO');
        $resInicial->assertDontSee('FABIANA LUCIA');

        // 2. Filtrar por Nivel Primaria, Grado 4to y Sección B
        $resPrimaria = $this->actingAs($admin)->get(route('estudiantes.index', [
            'nivel' => 'Primaria',
            'grado' => '4to',
            'seccion' => 'B',
        ]));
        $resPrimaria->assertStatus(200);
        $resPrimaria->assertSee('RODRIGO ALONSO');
        $resPrimaria->assertSee('4to "B"', false);
        $resPrimaria->assertDontSee('VALENTINA INES');
        $resPrimaria->assertDontSee('FABIANA LUCIA');

        // 3. Filtrar por Nivel Secundaria
        $resSecundaria = $this->actingAs($admin)->get(route('estudiantes.index', ['nivel' => 'Secundaria']));
        $resSecundaria->assertStatus(200);
        $resSecundaria->assertSee('FABIANA LUCIA');
        $resSecundaria->assertSee('5to "C"', false);
        $resSecundaria->assertDontSee('VALENTINA INES');
        $resSecundaria->assertDontSee('RODRIGO ALONSO');

        // 4. Verificar que se rendericen los indicadores de conteo por nivel
        $resTodos = $this->actingAs($admin)->get(route('estudiantes.index'));
        $resTodos->assertStatus(200);
        $resTodos->assertSee('Nivel Inicial');
        $resTodos->assertSee('Nivel Primaria');
        $resTodos->assertSee('Nivel Secundaria');
        $resTodos->assertSee('Padrón de Alumnos Matriculados');

        // Limpieza de alumnos y padre de prueba
        $alumnoInicial->delete();
        $alumnoPrimaria->delete();
        $alumnoSecundaria->delete();
        $padre->delete();
    }

    /**
     * Test del Módulo de Configuración:
     * - Carga de Logo y datos de la IIEE.
     * - Asignación de cargos en el Comité de APAFA.
     * - Creación y gestión de responsabilidades con usuarios y contraseñas.
     */
    public function test_configuracion_institucion_logo_comite_and_usuarios(): void
    {
        Storage::fake('public');

        $admin = User::withoutGlobalScopes()->where('rol', 'ADMIN')->first();
        session(['empresa_id' => 1]);

        // 1. Carga de la vista de Configuración
        $response = $this->actingAs($admin)->get(route('configuracion.index'));
        $response->assertStatus(200);
        $response->assertSee('Configuración del Sistema y Comité de APAFA');
        $response->assertSee('1. Institución y Logo Oficial');
        $response->assertSee('2. Consejo Directivo y Comité de Vigilancia');
        $response->assertSee('3. Responsabilidades, Usuarios y Contraseñas');

        // 2. Cargar Logo de la IIEE y actualizar datos institucionales
        $logoFake = UploadedFile::fake()->image('insignia_colegio.png', 200, 200);

        $resUpdateInst = $this->actingAs($admin)->post(route('configuracion.institucion.update'), [
            'nombre' => 'I.E. EMBLEMÁTICA NERY GARCÍA ZÁRATE',
            'cod_modular' => '0345999',
            'ruc' => '20489998811',
            'periodo_apafa' => '2025 - 2026',
            'lema' => 'Ciencia, Disciplina y Transparencia APAFA',
            'nivel_educativo' => 'Inicial, Primaria y Secundaria',
            'director_nombre' => 'Mg. Juan Carlos Quispe Pérez',
            'foto_institucion' => $logoFake,
        ]);

        $resUpdateInst->assertRedirect(route('configuracion.index', ['tab' => 'institucion']));
        $resUpdateInst->assertSessionHas('success');

        $empresa = Empresa::find(1);
        $this->assertEquals('I.E. EMBLEMÁTICA NERY GARCÍA ZÁRATE', $empresa->nombre);
        $this->assertEquals('0345999', $empresa->cod_modular);
        $this->assertEquals('20489998811', $empresa->ruc);
        $this->assertNotEmpty($empresa->foto_institucion);
        $this->assertStringContainsString('storage/logos/', $empresa->foto_institucion);

        // 3. Registro y Asignación de Cargo en el Comité de APAFA
        $padre = PadreFamilia::first();
        $this->assertNotNull($padre);

        $cargoTesorero = Cargo::where('nombre', 'like', '%TESORERO%')->first();
        if (! $cargoTesorero) {
            $cargoTesorero = Cargo::create(['empresa_id' => 1, 'nombre' => 'TESORERO(A)']);
        }

        $resComite = $this->actingAs($admin)->post(route('configuracion.comite.store'), [
            'padre_familia_id' => $padre->id,
            'cargo_id' => $cargoTesorero->id,
        ]);

        $resComite->assertRedirect(route('configuracion.index', ['tab' => 'comite']));
        $resComite->assertSessionHas('success');

        $padre->refresh();
        $this->assertEquals($cargoTesorero->id, $padre->cargo_id);

        // 4. Crear Responsabilidad con Usuario y Contraseña
        $randomUser = 'secretaria_'.rand(1000, 9999);
        $resNuevoUsuario = $this->actingAs($admin)->post(route('configuracion.usuarios.store'), [
            'name' => 'MARIA ELENA TORRES CASTILLO',
            'username' => $randomUser,
            'email' => $randomUser.'@apafa.edu.pe',
            'rol' => 'SECRETARIO',
            'cargo_titulo' => 'Secretaria General de Actas y Padrón',
            'password' => 'SecreClave2026*',
            'password_confirmation' => 'SecreClave2026*',
        ]);

        $resNuevoUsuario->assertRedirect(route('configuracion.index', ['tab' => 'usuarios']));
        $resNuevoUsuario->assertSessionHas('success');

        $usuarioCreado = User::withoutGlobalScopes()->where('username', $randomUser)->first();
        $this->assertNotNull($usuarioCreado);
        $this->assertEquals('SECRETARIO', $usuarioCreado->rol);
        $this->assertTrue(Hash::check('SecreClave2026*', $usuarioCreado->password), 'La contraseña debe estar hasheada y ser verificable');

        // 5. Actualizar Usuario existente
        $resEditUsuario = $this->actingAs($admin)->put(route('configuracion.usuarios.update', $usuarioCreado), [
            'name' => 'MARIA ELENA TORRES DE QUISPE',
            'username' => $randomUser,
            'email' => $randomUser.'@apafa.edu.pe',
            'rol' => 'SECRETARIO',
            'cargo_titulo' => 'Secretaria General Titular',
            'estado' => 1,
        ]);
        $resEditUsuario->assertRedirect(route('configuracion.index', ['tab' => 'usuarios']));
        $usuarioCreado->refresh();
        $this->assertEquals('MARIA ELENA TORRES DE QUISPE', $usuarioCreado->name);
        $this->assertEquals('Secretaria General Titular', $usuarioCreado->cargo_titulo);

        // 6. Evitar que un usuario elimine su propia cuenta activa
        $resDeleteSelf = $this->actingAs($admin)->delete(route('configuracion.usuarios.destroy', $admin));
        $resDeleteSelf->assertSessionHas('error');

        // Limpieza del usuario de prueba para evitar contaminación de la base de datos
        $usuarioCreado->delete();
    }

    /**
     * Test para verificar el escaneo móvil y responsivo mediante cámara / AJAX:
     * - Retorno de respuestas JSON con estados PUNTUAL/TARDANZA/SALIDA.
     * - Descomposición de códigos QR compuestos (ASISTENCIA_ESCOLAR|DNI:... y APAFA|DNI:...).
     */
    public function test_responsive_mobile_scanning_flows_with_json_and_qr_parsing(): void
    {
        $admin = User::first();
        session(['empresa_id' => $admin->empresa_id]);

        // 1. Crear Padre de Familia asociado
        $dniPadre = '4'.rand(1000000, 9999999);
        $padre = PadreFamilia::create([
            'empresa_id' => $admin->empresa_id,
            'padre_dni' => $dniPadre,
            'padre_nombres' => 'CESAR AUGUSTO',
            'padre_ape_pat' => 'MENDEZ',
            'padre_ape_mat' => 'RAMIREZ',
            'tipo_consanguinidad_id' => 1,
            'correlativo' => 'PAD-0099',
            'estado' => 1,
        ]);

        // 2. Escaneo móvil de estudiante con código QR compuesto vía JSON
        $dniEstudiante = '7'.rand(1000000, 9999999);
        $estudiante = Estudiante::create([
            'empresa_id' => $admin->empresa_id,
            'padre_familia_id' => $padre->id,
            'dni' => $dniEstudiante,
            'nombres' => 'RODRIGO ALONSO',
            'ape_pat' => 'MENDEZ',
            'ape_mat' => 'FLORES',
            'nivel' => 'Primaria',
            'grado' => '4to',
            'seccion' => 'B',
            'turno' => 'Mañana',
            'correlativo' => 'EST-0099',
            'estado' => 1,
        ]);

        $qrEstudianteCompuesto = "ASISTENCIA_ESCOLAR|DNI:{$dniEstudiante}|ALUMNO:MENDEZ FLORES, RODRIGO ALONSO|APODERADO:{$dniPadre}";

        // Petición AJAX (como la que envía el escáner de la cámara del celular)
        $resScanEstudiante = $this->actingAs($admin)->json('POST', route('asistencia.estudiantes.escanear'), [
            'codigo' => $qrEstudianteCompuesto,
        ]);

        $resScanEstudiante->assertOk()
            ->assertJson([
                'success' => true,
                'tipo' => 'INGRESO',
                'estudiante' => [
                    'dni' => $dniEstudiante,
                    'nombre' => 'RODRIGO ALONSO MENDEZ FLORES',
                ],
            ]);
        $this->assertEquals('INGRESO', $resScanEstudiante->json('tipo'));
        $this->assertNotEmpty($resScanEstudiante->json('estudiante.grado'));

        $this->assertDatabaseHas('asistencia_estudiantes', [
            'estudiante_id' => $estudiante->id,
            'fecha' => date('Y-m-d'),
        ]);

        // Escaneo secundario: debe registrar SALIDA
        $resScanSalida = $this->actingAs($admin)->json('POST', route('asistencia.estudiantes.escanear'), [
            'codigo' => $qrEstudianteCompuesto,
        ]);
        $resScanSalida->assertOk()
            ->assertJson([
                'success' => true,
                'tipo' => 'SALIDA',
                'estudiante' => [
                    'dni' => $dniEstudiante,
                ],
            ]);
        $this->assertEquals('SALIDA', $resScanSalida->json('tipo'));

        // 3. Escaneo móvil de asamblea de padres con código QR compuesto vía JSON
        $reunion = \App\Models\Reunion::create([
            'empresa_id' => $admin->empresa_id,
            'titulo' => 'Asamblea Extraordinaria de Presupuesto',
            'fecha' => date('Y-m-d'),
            'hora' => '08:00:00',
            'lugar' => 'Patio de Honor',
            'tipo' => 'EXTRAORDINARIA',
            'descripcion' => 'Reunión de prueba responsive',
            'multa_monto' => 20.00,
        ]);

        // Asignar al padre a la asamblea
        \App\Models\ReunionPadre::create([
            'reunion_id' => $reunion->id,
            'padre_familia_id' => $padre->id,
            'asistencia' => null,
            'detalles' => 'Sin registrar',
        ]);

        $qrPadreCompuesto = "APAFA|DNI:{$dniPadre}|ASOCIADO:MENDEZ RAMIREZ, CESAR AUGUSTO|CORR:PAD-0099";

        // Petición AJAX desde la cámara del celular en asamblea
        $resScanPadre = $this->actingAs($admin)->json('POST', route('reuniones.asistencia', $reunion), [
            'dni' => $qrPadreCompuesto,
        ]);

        $resScanPadre->assertOk()
            ->assertJson([
                'success' => true,
                'padre' => [
                    'dni' => $dniPadre,
                ],
            ]);

        $this->assertDatabaseHas('reunion_padres', [
            'reunion_id' => $reunion->id,
            'padre_familia_id' => $padre->id,
        ]);
        $this->assertNotNull(\App\Models\ReunionPadre::where('reunion_id', $reunion->id)->where('padre_familia_id', $padre->id)->first()->asistencia);

        // Limpieza de datos de prueba
        \App\Models\AsistenciaEstudiante::where('estudiante_id', $estudiante->id)->delete();
        Estudiante::where('padre_familia_id', $padre->id)->delete();
        \App\Models\ReunionPadre::where('reunion_id', $reunion->id)->delete();
        $reunion->delete();
        $padre->delete();
    }

    /**
     * Test de Eliminación de Asambleas sin asistencia y Prevención de reuniones duplicadas el mismo día.
     */
    public function test_reunion_eliminar_y_prevencion_duplicados(): void
    {
        $admin = User::first();
        session(['empresa_id' => $admin->empresa_id]);

        $fechaTest = '2026-11-15';
        $tituloTest = 'ASAMBLEA EXTRAORDINARIA TEST '.uniqid();

        // 1. Crear asamblea
        $resStore = $this->actingAs($admin)->post(route('reuniones.store'), [
            'titulo' => $tituloTest,
            'fecha' => $fechaTest,
            'hora' => '10:00',
            'multa_precio' => 15.00,
            'descripcion' => 'Prueba de validación y eliminación',
        ]);
        $resStore->assertRedirect(route('reuniones.index'));
        $resStore->assertSessionHas('success');

        $reunion = \App\Models\Reunion::where('titulo', $tituloTest)->first();
        $this->assertNotNull($reunion);

        // 2. Intentar convocar otra reunión en la misma fecha (incluso a distinta hora y con distinto título) debe ser estrictamente rechazado
        $dupResp = $this->actingAs($admin)->post(route('reuniones.store'), [
            'titulo' => 'ASAMBLEA DIFERENTE MISMO DIA',
            'fecha' => $fechaTest,
            'hora' => '16:30',
            'multa_precio' => 20.00,
        ]);
        $dupResp->assertSessionHasErrors('fecha');

        // 3. Eliminar asamblea sin asistencia debe ser exitoso
        $delResp = $this->actingAs($admin)->delete(route('reuniones.destroy', $reunion));
        $delResp->assertRedirect(route('reuniones.index'));
        $delResp->assertSessionHas('success');
        $this->assertDatabaseMissing('reuniones', ['id' => $reunion->id]);

        // 4. Intentar eliminar una asamblea con asistencia registrada debe ser bloqueado
        $reunionConAsistencia = \App\Models\Reunion::find(1);
        if ($reunionConAsistencia) {
            $delBlockedResp = $this->actingAs($admin)->delete(route('reuniones.destroy', $reunionConAsistencia));
            $delBlockedResp->assertSessionHas('error');
            $this->assertDatabaseHas('reuniones', ['id' => 1]);
        }
    }

    /**
     * Test para verificar que al iniciar sesión con el rol de SECRETARIO
     * se muestra correctamente la imagen del logo oficial de la I.E. en el layout.
     */
    public function test_secretario_role_shows_institution_logo_in_layout(): void
    {
        // 1. Asegurar que la I.E. principal tiene un logo oficial asignado
        $empresa = Empresa::find(1);
        $empresa->update([
            'foto_institucion' => 'storage/logos/BI1I3PmWqm9tmTliN5uEfgjQP8A3jNITkM8E2rLt.jpg',
            'nombre' => 'I.E. EMBLEMÁTICA NERY GARCÍA ZÁRATE',
        ]);

        // 2. Ingresar mediante quickLogin con el rol SECRETARIO
        $responseQuick = $this->get(route('login.quick', 'SECRETARIO'));
        $responseQuick->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $user = auth()->user();
        $this->assertEquals('SECRETARIO', $user->rol);

        // 3. Cargar el Dashboard y verificar que el HTML contiene la imagen del logo institucional
        $responseDashboard = $this->get(route('dashboard'));
        $responseDashboard->assertOk();
        $responseDashboard->assertSee(asset($empresa->foto_institucion), false);
        $responseDashboard->assertSee('I.E. EMBLEMÁTICA NERY GARCÍA ZÁRATE');

        // 4. Cargar el Padrón de Padres como Secretario y verificar también la presencia del logo
        $responsePadres = $this->get(route('padres.index'));
        $responsePadres->assertOk();
        $responsePadres->assertSee(asset($empresa->foto_institucion), false);
    }
}
