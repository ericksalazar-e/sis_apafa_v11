<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ValidacionesYEstatusTest extends TestCase
{
    /**
     * Prueba las validaciones y errores al iniciar sesión en el sistema.
     */
    public function test_login_validation_and_error_messages(): void
    {
        // 1. Enviar login con campos vacíos
        $res = $this->post('/login', [
            'login' => '',
            'password' => '',
        ]);
        $res->assertSessionHasErrors(['login', 'password']);

        // 2. Enviar usuario inexistente
        $resNotFound = $this->post('/login', [
            'login' => 'usuario_que_no_existe_9999@test.com',
            'password' => 'secret1234',
        ]);
        $resNotFound->assertSessionHasErrors(['login']);
        $resNotFound->assertSessionHas('error');

        // 3. Enviar usuario existente pero contraseña errónea
        $user = User::withoutGlobalScopes()->where('username', 'secretario')->first();
        if ($user) {
            $resBadPass = $this->post('/login', [
                'login' => $user->username,
                'password' => 'clave_completamente_incorrecta',
            ]);
            $resBadPass->assertSessionHasErrors(['password']);
            $resBadPass->assertSessionHas('error');
        }

        // 4. Iniciar sesión correctamente
        $admin = User::withoutGlobalScopes()->where('username', 'admin')->first();
        if ($admin) {
            $resSuccess = $this->post('/login', [
                'login' => $admin->username,
                'password' => 'admin123',
            ]);
            $resSuccess->assertRedirect(route('dashboard'));
            $resSuccess->assertSessionHas('success');
        }
    }

    /**
     * Prueba que el usuario sin permiso reciba el error 403 con la vista personalizada.
     */
    public function test_unauthorized_user_receives_403_page(): void
    {
        $vocal = User::withoutGlobalScopes()->where('rol', 'VOCAL_OPERADOR')->first();
        if (!$vocal) {
            $vocal = User::create([
                'name' => 'Vocal de Prueba',
                'username' => 'vocal_test_403',
                'email' => 'vocal_test_403@apafa.edu.pe',
                'password' => Hash::make('password'),
                'rol' => 'VOCAL_OPERADOR',
                'empresa_id' => 1,
                'estado' => 1,
            ]);
        }

        // El vocal intenta entrar a la configuración del sistema (restringido a ADMIN, SECRETARIO, COMITE_VIGILANCIA)
        session(['empresa_id' => 1]);
        $res = $this->actingAs($vocal)->get('/configuracion');
        
        $res->assertStatus(403);
        $res->assertSee('403 &bull; Acceso Restringido', false);
        $res->assertSee('No cuenta con permisos suficientes');
        $res->assertSee('Ir a Mi Panel de Control (Dashboard)');
        $res->assertSee($vocal->name);
    }

    /**
     * Prueba que usuarios no autenticados sean redirigidos con aviso de sesión requerida.
     */
    public function test_guest_user_redirected_with_error(): void
    {
        $res = $this->get('/dashboard');
        $res->assertRedirect(route('login'));
        $res->assertSessionHas('error');
    }

    /**
     * Prueba que las validaciones de registros se muestren en español.
     */
    public function test_form_validation_in_spanish(): void
    {
        $admin = User::withoutGlobalScopes()->where('rol', 'ADMIN')->first();
        session(['empresa_id' => 1]);

        $res = $this->actingAs($admin)->post(route('padres.store'), [
            'padre_dni' => '', // requerido y 8 dígitos
            'padre_nombres' => '',
        ]);

        $res->assertSessionHasErrors(['padre_dni', 'padre_nombres']);
        $errors = session('errors')->getBag('default');
        
        // Verificar que los mensajes estén traducidos al español
        $this->assertStringContainsString('obligatorio', $errors->first('padre_dni') . $errors->first('padre_nombres'));
    }

    /**
     * Prueba que los botones de eliminación invoquen la confirmación de borrado.
     */
    public function test_delete_confirmation_markup_in_views(): void
    {
        $admin = User::withoutGlobalScopes()->where('rol', 'ADMIN')->first();
        session(['empresa_id' => 1]);

        // 1. Vista de reuniones
        $resReuniones = $this->actingAs($admin)->get(route('reuniones.index'));
        $resReuniones->assertStatus(200);
        $resReuniones->assertSee('confirmDelete', false);

        // 2. Vista de aportes
        $resAportes = $this->actingAs($admin)->get(route('aportes.index'));
        $resAportes->assertStatus(200);
        $resAportes->assertSee('confirmDelete', false);

        // 3. Vista de estudiantes
        $resEstudiantes = $this->actingAs($admin)->get(route('estudiantes.index'));
        $resEstudiantes->assertStatus(200);
        $resEstudiantes->assertSee('confirmDelete', false);

        // 4. SweetAlert2 cargado en el layout
        $resEstudiantes->assertSee('sweetalert2@11', false);
    }

    /**
     * Prueba que no se puedan registrar múltiples aportes o cuotas el mismo día.
     */
    public function test_no_repetir_aportes_el_mismo_dia(): void
    {
        $admin = User::withoutGlobalScopes()->where('rol', 'ADMIN')->first();
        session(['empresa_id' => 1]);

        $fechaPrueba = '2026-11-15';
        \App\Models\Aporte::withoutGlobalScopes()->whereDate('fecha', $fechaPrueba)->delete();

        // 1. Crear primer aporte en esa fecha
        $res1 = $this->actingAs($admin)->post(route('aportes.store'), [
            'titulo' => 'CUOTA DE PRUEBA DIA NOVIEMBRE',
            'tipo' => 'EXTRAORDINARIA',
            'monto' => 15.00,
            'fecha' => $fechaPrueba,
            'descripcion' => 'Primera cuota programada',
        ]);
        $res1->assertSessionHas('success');

        // 2. Intentar crear otro aporte diferente en la MISMA fecha debe ser rechazado
        $res2 = $this->actingAs($admin)->post(route('aportes.store'), [
            'titulo' => 'OTRO APORTE DISTINTO MISMO DIA',
            'tipo' => 'EXTRAORDINARIA',
            'monto' => 20.00,
            'fecha' => $fechaPrueba,
            'descripcion' => 'Segunda cuota que debe ser rechazada por fecha duplicada',
        ]);
        $res2->assertSessionHasErrors(['fecha']);

        // Limpieza
        \App\Models\Aporte::withoutGlobalScopes()->whereDate('fecha', $fechaPrueba)->delete();
    }

    /**
     * Prueba que no se pueda registrar un cese/relevo duplicado para el mismo directivo saliente en la misma fecha.
     */
    public function test_no_repetir_movimiento_directivo_misma_fecha(): void
    {
        $admin = User::withoutGlobalScopes()->where('rol', 'ADMIN')->first();
        session(['empresa_id' => 1]);

        $dniPrueba = '99887766';
        $fechaPrueba = '2026-10-20';

        \App\Models\MovimientoDirectiva::where('directivo_saliente_dni', $dniPrueba)->delete();

        // 1. Crear primer movimiento de relevo
        $res1 = $this->actingAs($admin)->post(route('configuracion.movimientos.store'), [
            'cargo_nombre' => 'VOCAL DE PORTERÍA',
            'directivo_saliente_nombre' => 'DIRECTIVO SALIENTE DE PRUEBA',
            'directivo_saliente_dni' => $dniPrueba,
            'causal_vacancia' => 'RENUNCIA_VOLUNTARIA',
            'fecha_vacancia' => $fechaPrueba,
            'directivo_entrante_nombre' => 'DIRECTIVO ENTRANTE DE PRUEBA',
            'numero_acta_asamblea' => 'ACTA-TEST-001',
        ]);
        $res1->assertSessionHas('success');

        $mov = \App\Models\MovimientoDirectiva::where('directivo_saliente_dni', $dniPrueba)->first();
        $this->assertNotNull($mov);

        // 2. Intentar registrar OTRO movimiento para el MISMO saliente en la MISMA fecha debe ser rechazado
        $res2 = $this->actingAs($admin)->post(route('configuracion.movimientos.store'), [
            'cargo_nombre' => 'VOCAL DE PORTERÍA',
            'directivo_saliente_nombre' => 'DIRECTIVO SALIENTE DE PRUEBA',
            'directivo_saliente_dni' => $dniPrueba,
            'causal_vacancia' => 'FUNCION_CUMPLIDA',
            'fecha_vacancia' => $fechaPrueba,
            'directivo_entrante_nombre' => 'OTRO SUCESOR',
        ]);
        $res2->assertSessionHasErrors(['fecha_vacancia']);

        // 3. Eliminar el movimiento con la ruta destroy
        $delRes = $this->actingAs($admin)->delete(route('configuracion.movimientos.destroy', $mov->id));
        $delRes->assertSessionHas('success');
        $this->assertDatabaseMissing('movimientos_directiva', ['id' => $mov->id]);
    }

    /**
     * Prueba la descarga de la plantilla oficial de estudiantes en XLSX y CSV.
     */
    public function test_descargar_plantilla_excel_y_csv_estudiantes(): void
    {
        $admin = User::withoutGlobalScopes()->where('rol', 'ADMIN')->first();
        session(['empresa_id' => 1]);

        // 1. Descargar plantilla XLSX
        $responseXlsx = $this->actingAs($admin)->get(route('estudiantes.plantilla.descargar', ['formato' => 'xlsx']));
        $responseXlsx->assertStatus(200);
        $responseXlsx->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // 2. Descargar plantilla CSV
        $responseCsv = $this->actingAs($admin)->get(route('estudiantes.plantilla.descargar', ['formato' => 'csv']));
        $responseCsv->assertStatus(200);
        $responseCsv->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('DNI', $responseCsv->getContent());
        $this->assertStringContainsString('APELLIDO_PATERNO', $responseCsv->getContent());
    }

    /**
     * Prueba la carga masiva de estudiantes y asignación posterior de su apoderado.
     */
    public function test_carga_masiva_estudiantes_y_asignacion_apoderado(): void
    {
        $admin = User::withoutGlobalScopes()->where('rol', 'ADMIN')->first();
        $padre = \App\Models\PadreFamilia::withoutGlobalScopes()->first();
        session(['empresa_id' => 1]);

        $dniEst1 = '88112233';
        $dniEst2 = '88112244';

        // Limpiar si existían
        \App\Models\Estudiante::withoutGlobalScopes()->whereIn('dni', [$dniEst1, $dniEst2])->delete();

        // 1. Crear archivo CSV de prueba simulando carga masiva
        $csvContent = "\xEF\xBB\xBF" . "DNI;NOMBRES;APELLIDO_PATERNO;APELLIDO_MATERNO;NIVEL;GRADO;SECCION;TURNO;DNI_APODERADO;PARENTESCO\n";
        $csvContent .= "{$dniEst1};ALUMNO CON APODERADO;GONZALES;RODRIGUEZ;Primaria;2do;A;Mañana;{$padre->padre_dni};HIJO(A)\n";
        $csvContent .= "{$dniEst2};ALUMNO SIN APODERADO;QUISPE;MAMANI;Secundaria;3ro;B;Tarde;;HIJO(A)\n";

        $tempCsv = tempnam(sys_get_temp_dir(), 'test_csv_') . '.csv';
        file_put_contents($tempCsv, $csvContent);

        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempCsv,
            'importacion_test.csv',
            'text/csv',
            null,
            true
        );

        $importResponse = $this->actingAs($admin)->post(route('estudiantes.importar'), [
            'archivo_excel' => $uploadedFile,
        ]);
        $importResponse->assertSessionHas('success');
        @unlink($tempCsv);

        // 2. Verificar que ambos estudiantes fueron creados
        $est1 = \App\Models\Estudiante::withoutGlobalScopes()->where('dni', $dniEst1)->first();
        $est2 = \App\Models\Estudiante::withoutGlobalScopes()->where('dni', $dniEst2)->first();

        $this->assertNotNull($est1, 'El estudiante 1 debe haber sido creado');
        $this->assertNotNull($est2, 'El estudiante 2 debe haber sido creado');

        // El estudiante 1 debe haber quedado vinculado automáticamente a su apoderado
        $this->assertEquals($padre->id, $est1->padre_familia_id);
        $this->assertEquals('Primaria', $est1->nivel);

        // El estudiante 2 debe estar pendiente de asignar apoderado
        $this->assertNull($est2->padre_familia_id);

        // 3. Probar la asignación manual posterior de apoderado desde el padrón
        $asignarResponse = $this->actingAs($admin)->post(route('estudiantes.asignar_apoderado', $est2->id), [
            'padre_familia_id' => $padre->id,
            'parentesco' => 'TUTOR / APODERADO LEGAL',
        ]);
        $asignarResponse->assertSessionHas('success');

        $est2->refresh();
        $this->assertEquals($padre->id, $est2->padre_familia_id);
        $this->assertEquals('TUTOR / APODERADO LEGAL', $est2->parentesco);

        // 4. Probar desvinculación (dejar sin apoderado)
        $desvincularResponse = $this->actingAs($admin)->post(route('estudiantes.asignar_apoderado', $est2->id), [
            'padre_familia_id' => null,
            'parentesco' => 'HIJO(A)',
        ]);
        $desvincularResponse->assertSessionHas('success');
        $est2->refresh();
        $this->assertNull($est2->padre_familia_id);

        // 5. Verificar filtros en el padrón
        $vistaSinApoderado = $this->actingAs($admin)->get(route('estudiantes.index', ['apoderado' => 'sin_apoderado']));
        $vistaSinApoderado->assertStatus(200);
        $vistaSinApoderado->assertSee($dniEst2);

        $vistaConApoderado = $this->actingAs($admin)->get(route('estudiantes.index', ['apoderado' => 'con_apoderado']));
        $vistaConApoderado->assertStatus(200);
        $vistaConApoderado->assertSee($dniEst1);

        // Limpieza
        \App\Models\Estudiante::withoutGlobalScopes()->whereIn('dni', [$dniEst1, $dniEst2])->delete();
    }

    /**
     * Prueba la edición y corrección de datos del estudiante (DNI, nombres, apellidos y grado).
     */
    public function test_editar_datos_estudiante_dni_y_nombres(): void
    {
        $admin = User::withoutGlobalScopes()->where('rol', 'ADMIN')->first();
        session(['empresa_id' => 1]);

        $dniOriginal = '88223344';
        $dniCorregido = '88223355';

        \App\Models\Estudiante::withoutGlobalScopes()->whereIn('dni', [$dniOriginal, $dniCorregido])->delete();

        // 1. Crear estudiante con un DNI o nombre que tenga confusión
        $estudiante = \App\Models\Estudiante::create([
            'empresa_id' => 1,
            'dni' => $dniOriginal,
            'nombres' => 'JUAN CARLOS ERRONEO',
            'ape_pat' => 'LOPEZ',
            'ape_mat' => 'PEREZ',
            'nivel' => 'Primaria',
            'grado' => '1ro',
            'seccion' => 'A',
            'turno' => 'Mañana',
            'parentesco' => 'HIJO(A)',
        ]);

        // 2. Corregir datos con PUT
        $updateResponse = $this->actingAs($admin)->put(route('estudiantes.update', $estudiante->id), [
            'dni' => $dniCorregido,
            'nombres' => 'JUAN CARLOS CORREGIDO',
            'ape_pat' => 'LOPEZ CORREGIDO',
            'ape_mat' => 'PEREZ',
            'nivel' => 'Secundaria',
            'grado' => '2do',
            'seccion' => 'B',
            'turno' => 'Tarde',
            'parentesco' => 'HIJO(A)',
        ]);

        $updateResponse->assertSessionHas('success');

        // 3. Verificar cambios en la BD
        $estudiante->refresh();
        $this->assertEquals($dniCorregido, $estudiante->dni);
        $this->assertEquals('JUAN CARLOS CORREGIDO', $estudiante->nombres);
        $this->assertEquals('LOPEZ CORREGIDO', $estudiante->ape_pat);
        $this->assertEquals('Secundaria', $estudiante->nivel);
        $this->assertEquals('2do', $estudiante->grado);
        $this->assertEquals('B', $estudiante->seccion);
        $this->assertEquals('Tarde', $estudiante->turno);

        // 4. Verificar validación de DNI (debe tener 8 dígitos)
        $invalidDniResponse = $this->actingAs($admin)->put(route('estudiantes.update', $estudiante->id), [
            'dni' => '123', // Invalido
            'nombres' => 'NOMBRE',
            'ape_pat' => 'APELLIDO',
            'nivel' => 'Primaria',
            'grado' => '1ro',
            'seccion' => 'A',
            'turno' => 'Mañana',
        ]);
        $invalidDniResponse->assertSessionHasErrors(['dni']);

        // Limpieza
        $estudiante->delete();
    }

    /**
     * Prueba la edición y corrección de datos del padre de familia (DNI, nombres, apellidos y cónyuge).
     */
    public function test_editar_datos_padre_familia_dni_y_nombres(): void
    {
        $admin = User::withoutGlobalScopes()->where('rol', 'ADMIN')->first();
        session(['empresa_id' => 1]);

        $dniOriginal = '88334455';
        $dniCorregido = '88334466';

        \App\Models\PadreFamilia::withoutGlobalScopes()->whereIn('padre_dni', [$dniOriginal, $dniCorregido])->delete();

        // 1. Crear padre de familia con datos para corregir
        $padre = \App\Models\PadreFamilia::create([
            'empresa_id' => 1,
            'padre_dni' => $dniOriginal,
            'padre_nombres' => 'ROBERTO ERRADO',
            'padre_ape_pat' => 'VARGAS',
            'padre_ape_mat' => 'FLORES',
            'celular' => '999888777',
            'direccion' => 'Av. Los Pinos 123',
        ]);

        // 2. Corregir datos mediante PUT
        $updateResponse = $this->actingAs($admin)->put(route('padres.update', $padre->id), [
            'padre_dni' => $dniCorregido,
            'padre_nombres' => 'ROBERTO CORREGIDO',
            'padre_ape_pat' => 'VARGAS CORREGIDO',
            'padre_ape_mat' => 'FLORES',
            'celular' => '999111222',
            'direccion' => 'Av. Los Pinos 456',
            'conyuge_nombres' => 'ELENA TORRES ROJAS',
            'conyuge_dni' => '88990011',
            'conyuge_celular' => '987654321',
        ]);

        $updateResponse->assertSessionHas('success');

        // 3. Verificar cambios en la BD
        $padre->refresh();
        $this->assertEquals($dniCorregido, $padre->padre_dni);
        $this->assertEquals('ROBERTO CORREGIDO', $padre->padre_nombres);
        $this->assertEquals('VARGAS CORREGIDO', $padre->padre_ape_pat);
        $this->assertEquals('999111222', $padre->celular);
        $this->assertEquals('Av. Los Pinos 456', $padre->direccion);
        $this->assertEquals('ELENA TORRES ROJAS', $padre->conyuge_nombres);
        $this->assertEquals('88990011', $padre->conyuge_dni);

        // 4. Verificar validación de DNI (debe tener 8 dígitos)
        $invalidDniResponse = $this->actingAs($admin)->put(route('padres.update', $padre->id), [
            'padre_dni' => '12', // Inválido
            'padre_nombres' => 'NOMBRE',
            'padre_ape_pat' => 'APELLIDO',
        ]);
        $invalidDniResponse->assertSessionHasErrors(['padre_dni']);

        // Limpieza
        $padre->delete();
    }

    public function test_cargos_directiva_sin_duplicados_en_padres_index_y_modal_editar(): void
    {
        $admin = \App\Models\User::withoutGlobalScopes()->where('email', 'admin@apafa.pe')->first();
        if (!$admin) {
            $admin = \App\Models\User::withoutGlobalScopes()->first();
        }

        $empresaId = $admin->empresa_id ?? 1;
        session(['empresa_id' => $empresaId]);

        $response = $this->actingAs($admin)->get(route('padres.index'));
        $response->assertStatus(200);

        // 1. Obtener la colección de cargos pasada a la vista
        $cargos = $response->viewData('cargos');
        $this->assertNotEmpty($cargos);

        // 2. Verificar que todos pertenezcan a la institución del usuario
        foreach ($cargos as $c) {
            $this->assertEquals($empresaId, $c->empresa_id, "El cargo {$c->nombre} (ID: {$c->id}) no pertenece al tenant {$empresaId}");
        }

        // 3. Verificar que no existan nombres duplicados
        $nombres = $cargos->pluck('nombre')->map(fn($n) => mb_strtoupper(trim($n)))->toArray();
        $this->assertCount(count(array_unique($nombres)), $nombres, 'Se encontraron cargos duplicados en la colección de la vista');

        // 4. Verificar el HTML renderizado de los dropdowns en ambos modales
        $html = $response->getContent();

        // Modal Editar
        $this->assertMatchesRegularExpression('/id="editar_padre_cargo_id"/', $html);
        if (preg_match('/<select[^>]*id="editar_padre_cargo_id"[^>]*>(.*?)<\/select>/s', $html, $m)) {
            preg_match_all('/<option\s+value="([^"]*)"[^>]*>(.*?)<\/option>/s', $m[1], $opts, PREG_SET_ORDER);
            $values = array_map(fn($o) => trim($o[1]), $opts);
            $this->assertCount(count(array_unique($values)), $values, 'El modal editar contiene IDs de cargo repetidos');
        }

        // Modal Nuevo
        if (preg_match('/<div[^>]*id="modal-nuevo-padre".*?<select[^>]*name="cargo_id"[^>]*>(.*?)<\/select>/s', $html, $mNew)) {
            preg_match_all('/<option\s+value="([^"]*)"[^>]*>(.*?)<\/option>/s', $mNew[1], $optsNew, PREG_SET_ORDER);
            $valuesNew = array_map(fn($o) => trim($o[1]), $optsNew);
            $this->assertCount(count(array_unique($valuesNew)), $valuesNew, 'El modal nuevo contiene IDs de cargo repetidos');
        }
    }
}




