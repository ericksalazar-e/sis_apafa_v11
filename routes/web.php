<?php

use App\Http\Controllers\AporteController;
use App\Http\Controllers\AsistenciaEstudianteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartaPoderController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EleccionController;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\FaenaController;
use App\Http\Controllers\GastoPoaController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PadreFamiliaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\QuioscoController;
use App\Http\Controllers\ReunionController;
use App\Http\Controllers\VigilanciaController;
use App\Models\Empresa;
use Illuminate\Support\Facades\Route;

// Rutas de Invitados / Autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/login/quick/{rol}', [AuthController::class, 'quickLogin'])->name('login.quick');

// Portal Público y Landing Page para Padres de Familia
Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/consulta', [LandingController::class, 'index'])->name('consulta.padre');
Route::get('/pagos/{pago}/ticket-publico', [LandingController::class, 'ticketPago'])->name('pagos.ticket_publico');
Route::get('/validar-poder/{codigo}', [CartaPoderController::class, 'validarPublico'])->name('carta_poder.validar');

// Rutas Protegidas por Autenticación
Route::middleware(['auth'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard General (Accesible para todos los roles de la APAFA e I.E.)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 1. Padrón de Padres de Familia (Directiva: Secretario y Presidente; Vigilancia: Auditoría)
    Route::get('/padres', [PadreFamiliaController::class, 'index'])
        ->middleware('role:ADMIN,SECRETARIO,COMITE_VIGILANCIA')
        ->name('padres.index');
    Route::post('/padres', [PadreFamiliaController::class, 'store'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('padres.store');
    Route::put('/padres/{padre}', [PadreFamiliaController::class, 'update'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('padres.update');
    Route::get('/padres/{padre}/tarjeta', [PadreFamiliaController::class, 'tarjeta'])
        ->middleware('role:ADMIN,SECRETARIO,COMITE_VIGILANCIA')
        ->name('padres.tarjeta');
    Route::delete('/padres/{padre}', [PadreFamiliaController::class, 'destroy'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('padres.destroy');

    // 2. Alumnos y Estudiantes (Secretaría, Dirección e Inspección)
    Route::get('/estudiantes', [EstudianteController::class, 'index'])
        ->middleware('role:ADMIN,SECRETARIO,DIRECTOR,COMITE_VIGILANCIA,TESORERO')
        ->name('estudiantes.index');
    Route::get('/estudiantes/plantilla/descargar', [EstudianteController::class, 'descargarPlantilla'])
        ->middleware('role:ADMIN,SECRETARIO,DIRECTOR,COMITE_VIGILANCIA,TESORERO')
        ->name('estudiantes.plantilla.descargar');
    Route::post('/estudiantes/importar', [EstudianteController::class, 'importar'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('estudiantes.importar');
    Route::post('/estudiantes/asignar-apoderado-masivo', [EstudianteController::class, 'asignarApoderadoMasivo'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('estudiantes.asignar_apoderado_masivo');
    Route::post('/estudiantes/{estudiante}/asignar-apoderado', [EstudianteController::class, 'asignarApoderado'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('estudiantes.asignar_apoderado');
    Route::post('/estudiantes', [EstudianteController::class, 'store'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('estudiantes.store');
    Route::put('/estudiantes/{estudiante}', [EstudianteController::class, 'update'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('estudiantes.update');
    Route::get('/estudiantes/{estudiante}/tarjeta', [EstudianteController::class, 'tarjeta'])
        ->middleware('role:ADMIN,SECRETARIO,DIRECTOR,COMITE_VIGILANCIA,VOCAL_OPERADOR')
        ->name('estudiantes.tarjeta');
    Route::delete('/estudiantes/{estudiante}', [EstudianteController::class, 'destroy'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('estudiantes.destroy');

    // 2.1 Control de Asistencia Diaria de Alumnos (Portería / Auxiliares / Vocales)
    Route::get('/asistencia-estudiantes', [AsistenciaEstudianteController::class, 'index'])
        ->middleware('role:ADMIN,SECRETARIO,VOCAL_OPERADOR,DIRECTOR,COMITE_VIGILANCIA')
        ->name('asistencia.estudiantes.index');
    Route::post('/asistencia-estudiantes/escanear', [AsistenciaEstudianteController::class, 'escanear'])
        ->middleware('role:ADMIN,SECRETARIO,VOCAL_OPERADOR')
        ->name('asistencia.estudiantes.escanear');
    Route::post('/asistencia-estudiantes/horarios', [AsistenciaEstudianteController::class, 'guardarHorarios'])
        ->middleware('role:ADMIN,SECRETARIO,DIRECTOR')
        ->name('asistencia.estudiantes.horarios');
    Route::post('/asistencia-estudiantes/{id}/justificar', [AsistenciaEstudianteController::class, 'justificar'])
        ->middleware('role:ADMIN,SECRETARIO,DIRECTOR')
        ->name('asistencia.estudiantes.justificar');
    Route::post('/asistencia-estudiantes/justificar-falta', [AsistenciaEstudianteController::class, 'justificarInasistencia'])
        ->middleware('role:ADMIN,SECRETARIO,DIRECTOR')
        ->name('asistencia.estudiantes.justificar_falta');
    Route::post('/asistencia-estudiantes/{id}/hora-salida', [AsistenciaEstudianteController::class, 'actualizarHoraSalida'])
        ->middleware('role:ADMIN,SECRETARIO,VOCAL_OPERADOR,DIRECTOR')
        ->name('asistencia.estudiantes.hora_salida');

    // 3. Aportes y Cuotas (Tesorería y Auditoría)
    Route::get('/aportes', [AporteController::class, 'index'])
        ->middleware('role:ADMIN,TESORERO,COMITE_VIGILANCIA')
        ->name('aportes.index');
    Route::post('/aportes', [AporteController::class, 'store'])
        ->middleware('role:ADMIN,TESORERO')
        ->name('aportes.store');
    Route::delete('/aportes/{aporte}', [AporteController::class, 'destroy'])
        ->middleware('role:ADMIN,TESORERO')
        ->name('aportes.destroy');

    // 4. Asambleas y Asistencia por QR (Directiva y Vocales de Puerta)
    Route::get('/reuniones', [ReunionController::class, 'index'])
        ->middleware('role:ADMIN,SECRETARIO,VOCAL_OPERADOR,DIRECTOR,COMITE_VIGILANCIA')
        ->name('reuniones.index');
    Route::post('/reuniones', [ReunionController::class, 'store'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('reuniones.store');
    Route::delete('/reuniones/{reunion}', [ReunionController::class, 'destroy'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('reuniones.destroy');
    Route::get('/reuniones/{reunion}', [ReunionController::class, 'show'])
        ->middleware('role:ADMIN,SECRETARIO,VOCAL_OPERADOR,DIRECTOR,COMITE_VIGILANCIA')
        ->name('reuniones.show');
    Route::post('/reuniones/{reunion}/asistencia', [ReunionController::class, 'marcarAsistencia'])
        ->middleware('role:ADMIN,SECRETARIO,VOCAL_OPERADOR')
        ->name('reuniones.asistencia');

    // 5. Punto de Venta (POS) y Pagos con Facturación Asíncrona (Exclusivo Tesorería y Auditoría)
    Route::get('/pagos', [PagoController::class, 'index'])
        ->middleware('role:ADMIN,TESORERO,COMITE_VIGILANCIA')
        ->name('pagos.index');
    Route::post('/pagos', [PagoController::class, 'store'])
        ->middleware('role:ADMIN,TESORERO')
        ->name('pagos.store');
    Route::get('/pagos/{pago}/ticket', [PagoController::class, 'ticket'])
        ->middleware('role:ADMIN,TESORERO,COMITE_VIGILANCIA')
        ->name('pagos.ticket');

    // 6. Rendición de Cuentas y Ejecución Presupuestal del POA (Ley 28628)
    Route::get('/gastos-poa', [\App\Http\Controllers\GastoPoaController::class, 'index'])
        ->middleware('role:ADMIN,TESORERO,COMITE_VIGILANCIA')
        ->name('gastos_poa.index');
    Route::post('/gastos-poa', [\App\Http\Controllers\GastoPoaController::class, 'store'])
        ->middleware('role:ADMIN,TESORERO')
        ->name('gastos_poa.store');
    Route::delete('/gastos-poa/{gasto}', [\App\Http\Controllers\GastoPoaController::class, 'destroy'])
        ->middleware('role:ADMIN,TESORERO')
        ->name('gastos_poa.destroy');

    // 7. Configuración Institucional, Logo, Comité de APAFA y Gestión de Usuarios/Credenciales
    Route::get('/configuracion', [\App\Http\Controllers\ConfiguracionController::class, 'index'])
        ->middleware('role:ADMIN,SECRETARIO,COMITE_VIGILANCIA')
        ->name('configuracion.index');
    Route::post('/configuracion/institucion', [\App\Http\Controllers\ConfiguracionController::class, 'updateInstitucion'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('configuracion.institucion.update');
    Route::post('/configuracion/comite', [\App\Http\Controllers\ConfiguracionController::class, 'asignarCargoComite'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('configuracion.comite.store');
    Route::post('/configuracion/usuarios', [\App\Http\Controllers\ConfiguracionController::class, 'storeUsuario'])
        ->middleware('role:ADMIN')
        ->name('configuracion.usuarios.store');
    Route::put('/configuracion/usuarios/{usuario}', [\App\Http\Controllers\ConfiguracionController::class, 'updateUsuario'])
        ->middleware('role:ADMIN')
        ->name('configuracion.usuarios.update');
    Route::delete('/configuracion/usuarios/{usuario}', [\App\Http\Controllers\ConfiguracionController::class, 'destroyUsuario'])
        ->middleware('role:ADMIN')
        ->name('configuracion.usuarios.destroy');

    // 9. Faenas Escolares y Compensaciones (D.S. N.º 007-2026-MINEDU)
    Route::get('/faenas', [FaenaController::class, 'index'])
        ->middleware('role:ADMIN,SECRETARIO,TESORERO,COMITE_VIGILANCIA')
        ->name('faenas.index');
    Route::post('/faenas', [FaenaController::class, 'store'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('faenas.store');
    Route::get('/faenas/{faena}', [FaenaController::class, 'show'])
        ->middleware('role:ADMIN,SECRETARIO,TESORERO,VOCAL_OPERADOR,COMITE_VIGILANCIA')
        ->name('faenas.show');
    Route::post('/faenas/{faena}/asistencia', [FaenaController::class, 'marcarAsistencia'])
        ->middleware('role:ADMIN,SECRETARIO,VOCAL_OPERADOR')
        ->name('faenas.asistencia');
    Route::get('/faenas/{faena}/padre/{padre}/constancia', [FaenaController::class, 'constancia'])
        ->name('faenas.constancia');
    Route::get('/faenas/{faena}/imprimir-asistencias', [FaenaController::class, 'imprimirPadron'])
        ->middleware('role:ADMIN,SECRETARIO,TESORERO,COMITE_VIGILANCIA')
        ->name('faenas.imprimir_padron');

    // 10. Representación Familiar y Cartas Poder Digitales (Arts. 13 y 14 D.S. 007-2026-MINEDU)
    Route::get('/carta-poder/estudiante/{estudiante}', [CartaPoderController::class, 'generarPorEstudiante'])
        ->name('carta_poder.estudiante');
    Route::get('/carta-poder/padre/{padre}', [CartaPoderController::class, 'generarPorPadre'])
        ->name('carta_poder.padre');

    // 11. Comicios Electorales y Padrón al 31 de Agosto (D.S. N.º 007-2026-MINEDU)
    Route::get('/elecciones', [EleccionController::class, 'index'])
        ->middleware('role:ADMIN,SECRETARIO,DIRECTOR,COMITE_VIGILANCIA')
        ->name('elecciones.index');
    Route::post('/elecciones/convocar', [EleccionController::class, 'convocarComicio'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('elecciones.convocar');
    Route::post('/elecciones/{comicio}/generar-padron', [EleccionController::class, 'generarPadron'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('elecciones.generar_padron');
    Route::get('/elecciones/{comicio}/imprimir-padron', [EleccionController::class, 'imprimirPadron'])
        ->name('elecciones.imprimir_padron');
    Route::get('/elecciones/{comicio}/mesa', [EleccionController::class, 'mesaVotacion'])
        ->middleware('role:ADMIN,SECRETARIO,VOCAL_OPERADOR,DIRECTOR')
        ->name('elecciones.mesa');
    Route::post('/elecciones/{comicio}/votar', [EleccionController::class, 'registrarVoto'])
        ->middleware('role:ADMIN,SECRETARIO,VOCAL_OPERADOR')
        ->name('elecciones.votar');
    Route::get('/elecciones/{comicio}/acta-escrutinio', [EleccionController::class, 'actaEscrutinio'])
        ->name('elecciones.acta_escrutinio');
    Route::post('/elecciones/{comicio}/comite-electoral', [EleccionController::class, 'updateComiteElectoral'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('elecciones.comite_electoral');


    // Gestión de Listas de Candidatos y Cédula de Sufragio (Ley N° 28628)
    Route::post('/elecciones/{comicio}/listas', [EleccionController::class, 'storeLista'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('elecciones.listas.store');
    Route::delete('/elecciones/listas/{lista}', [EleccionController::class, 'destroyLista'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('elecciones.listas.destroy');
    Route::post('/elecciones/listas/{lista}/candidatos', [EleccionController::class, 'storeCandidato'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('elecciones.candidatos.store');
    Route::delete('/elecciones/candidatos/{candidato}', [EleccionController::class, 'destroyCandidato'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('elecciones.candidatos.destroy');
    Route::get('/elecciones/candidatos/{candidato}/declaracion-jurada', [EleccionController::class, 'declaracionJurada'])
        ->name('elecciones.candidatos.declaracion');
    Route::get('/elecciones/{comicio}/cedula-sufragio', [EleccionController::class, 'cedulaVotacion'])
        ->name('elecciones.cedula');
    Route::post('/elecciones/{comicio}/proclamar/{lista}', [EleccionController::class, 'proclamarGanadora'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('elecciones.proclamar');
    Route::get('/elecciones/{comicio}/acta-proclamacion', [EleccionController::class, 'actaProclamacion'])
        ->name('elecciones.acta_proclamacion');

    // 12. Concesiones de Quioscos y Cafeterías Saludables (Ley N° 30021 / D.S. 007-2026-MINEDU)
    Route::get('/quioscos', [QuioscoController::class, 'index'])
        ->middleware('role:ADMIN,TESORERO,DIRECTOR,COMITE_VIGILANCIA')
        ->name('quioscos.index');
    Route::post('/quioscos', [QuioscoController::class, 'storeQuiosco'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('quioscos.store');
    Route::post('/quioscos/concesiones', [QuioscoController::class, 'storeConcesion'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('quioscos.concesion.store');
    Route::post('/quioscos/concesion/{concesion}/pago', [QuioscoController::class, 'registrarPago'])
        ->middleware('role:ADMIN,TESORERO')
        ->name('quioscos.pago.store');
    Route::get('/quioscos/pagos/{pago}/recibo', [QuioscoController::class, 'reciboPago'])
        ->name('quioscos.recibo');

    // 13. Auditoría y Dictámenes del Consejo de Vigilancia (D.S. N.º 007-2026-MINEDU)
    Route::get('/vigilancia', [VigilanciaController::class, 'index'])
        ->middleware('role:ADMIN,COMITE_VIGILANCIA,DIRECTOR')
        ->name('vigilancia.index');
    Route::post('/vigilancia/dictamenes', [VigilanciaController::class, 'store'])
        ->middleware('role:ADMIN,COMITE_VIGILANCIA')
        ->name('vigilancia.store');
    Route::get('/vigilancia/dictamenes/{dictamen}/imprimir', [VigilanciaController::class, 'imprimirDictamen'])
        ->name('vigilancia.imprimir');
    Route::delete('/vigilancia/dictamenes/{dictamen}', [VigilanciaController::class, 'destroy'])
        ->middleware('role:ADMIN,COMITE_VIGILANCIA')
        ->name('vigilancia.destroy');

    // 14. Continuidad y Credencial de Directiva (D.S. N.º 007-2026-MINEDU)
    Route::post('/configuracion/movimientos-directiva', [ConfiguracionController::class, 'storeMovimientoDirectiva'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('configuracion.movimientos.store');
    Route::delete('/configuracion/movimientos-directiva/{id}', [ConfiguracionController::class, 'destroyMovimientoDirectiva'])
        ->middleware('role:ADMIN,SECRETARIO')
        ->name('configuracion.movimientos.destroy');
    Route::get('/configuracion/credencial-directiva', [ConfiguracionController::class, 'imprimirCredencialDirectiva'])
        ->name('configuracion.credencial_directiva');

    // 8. Cambio de Empresa (Soporte Multi-tenant interactivo)
    Route::get('/switch-tenant/{id}', function ($id) {
        if (Empresa::where('id', $id)->exists()) {
            session(['empresa_id' => (int) $id]);
        }
        return back()->with('success', 'Empresa / Institución cambiada correctamente.');
    })->name('tenant.switch');

});
