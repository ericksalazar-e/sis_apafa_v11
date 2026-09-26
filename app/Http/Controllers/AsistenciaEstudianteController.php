<?php

namespace App\Http\Controllers;

use App\Models\AsistenciaEstudiante;
use App\Models\Estudiante;
use App\Models\HorarioTurno;
use Illuminate\Http\Request;

class AsistenciaEstudianteController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = (int) (session('empresa_id') ?? 1);
        $fecha = $request->get('fecha', date('Y-m-d'));
        $filtroTurno = $request->get('turno');
        $filtroEstado = $request->get('estado');
        $filtroNivel = trim($request->get('nivel', ''));
        $filtroGrado = trim($request->get('grado', ''));
        $filtroSeccion = trim($request->get('seccion', ''));
        $search = trim($request->get('search', ''));

        // Sincronizar y obtener horarios de turnos (Mañana, Tarde, Noche)
        $horarios = HorarioTurno::sincronizarYObtener($empresaId);

        // Estudiantes para el selector de Justificación de Inasistencia
        $estudiantesLista = Estudiante::orderBy('ape_pat')->orderBy('ape_mat')->get();

        // Consulta de Asistencias del día filtrado
        $query = AsistenciaEstudiante::with(['estudiante.padreFamilia'])
            ->where('fecha', $fecha);

        if (!empty($filtroTurno)) {
            $query->where('turno', $filtroTurno);
        }

        if (!empty($filtroEstado)) {
            if ($filtroEstado === 'JUSTIFICADAS') {
                $query->where('justificado', true);
            } else {
                $query->where('estado', $filtroEstado);
            }
        }

        // Filtro por Nivel del Estudiante
        if (!empty($filtroNivel) && strtolower($filtroNivel) !== 'todos') {
            $query->whereHas('estudiante', function ($q) use ($filtroNivel) {
                $q->whereRaw('LOWER(nivel) = ?', [strtolower($filtroNivel)]);
            });
        }

        // Filtro por Grado del Estudiante
        if (!empty($filtroGrado) && strtolower($filtroGrado) !== 'todos') {
            $query->whereHas('estudiante', function ($q) use ($filtroGrado) {
                $q->whereRaw('LOWER(grado) = ?', [strtolower($filtroGrado)]);
            });
        }

        // Filtro por Sección del Estudiante
        if (!empty($filtroSeccion) && strtolower($filtroSeccion) !== 'todos') {
            $query->whereHas('estudiante', function ($q) use ($filtroSeccion) {
                $q->whereRaw('LOWER(seccion) = ?', [strtolower($filtroSeccion)]);
            });
        }

        // Filtro de Búsqueda por DNI o Nombre/Apellidos
        if (!empty($search)) {
            $query->whereHas('estudiante', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('dni', 'like', "%{$search}%")
                        ->orWhere('nombres', 'like', "%{$search}%")
                        ->orWhere('ape_pat', 'like', "%{$search}%")
                        ->orWhere('ape_mat', 'like', "%{$search}%");
                });
            });
        }

        $asistencias = $query->latest('hora_ingreso')->get();

        // Conteo de Estudiantes Matriculados según los filtros de nivel, grado y sección seleccionados
        $estudiantesFiltroQuery = Estudiante::query();
        if (!empty($filtroNivel) && strtolower($filtroNivel) !== 'todos') {
            $estudiantesFiltroQuery->whereRaw('LOWER(nivel) = ?', [strtolower($filtroNivel)]);
        }
        if (!empty($filtroGrado) && strtolower($filtroGrado) !== 'todos') {
            $estudiantesFiltroQuery->whereRaw('LOWER(grado) = ?', [strtolower($filtroGrado)]);
        }
        if (!empty($filtroSeccion) && strtolower($filtroSeccion) !== 'todos') {
            $estudiantesFiltroQuery->whereRaw('LOWER(seccion) = ?', [strtolower($filtroSeccion)]);
        }
        if (!empty($search)) {
            $estudiantesFiltroQuery->where(function ($sub) use ($search) {
                $sub->where('dni', 'like', "%{$search}%")
                    ->orWhere('nombres', 'like', "%{$search}%")
                    ->orWhere('ape_pat', 'like', "%{$search}%")
                    ->orWhere('ape_mat', 'like', "%{$search}%");
            });
        }

        $totalEstudiantes = $estudiantesFiltroQuery->count();
        $presentesHoy = $asistencias->whereNotNull('hora_ingreso')->count();
        $puntuales = $asistencias->where('estado', 'PUNTUAL')->count();
        $tardanzasTotal = $asistencias->whereIn('estado', ['TARDANZA', 'TARDANZA JUSTIFICADA'])->count();
        $tardanzasJustificadas = $asistencias->where('estado', 'TARDANZA JUSTIFICADA')->count();
        $faltasJustificadas = $asistencias->where('estado', 'FALTA JUSTIFICADA')->count();
        $ausentes = max(0, $totalEstudiantes - $presentesHoy - $faltasJustificadas);

        // Garantizar siempre la presencia de los 3 niveles educativos oficiales: Inicial, Primaria y Secundaria
        $nivelesBase = ['Inicial', 'Primaria', 'Secundaria'];
        $nivelesBD = Estudiante::whereNotNull('nivel')
            ->where('nivel', '!=', '')
            ->distinct()
            ->pluck('nivel')
            ->map(fn($n) => ucfirst(strtolower(trim($n))))
            ->toArray();
        $nivelesDisponibles = array_values(array_unique(array_merge($nivelesBase, $nivelesBD)));

        // Grados oficiales agrupados con Inicial (3, 4 y 5 Años) y Primaria/Secundaria (1ro a 6to)
        $gradosBase = ['3 Años', '4 Años', '5 Años', '1ro', '2do', '3ro', '4to', '5to', '6to'];
        $gradosBD = Estudiante::whereNotNull('grado')
            ->where('grado', '!=', '')
            ->distinct()
            ->pluck('grado')
            ->map(fn($g) => trim($g))
            ->toArray();
        $gradosDisponibles = array_values(array_unique(array_merge($gradosBase, $gradosBD)));

        $seccionesBase = ['A', 'B', 'C', 'D', 'Única'];
        $seccionesBD = Estudiante::whereNotNull('seccion')
            ->where('seccion', '!=', '')
            ->distinct()
            ->pluck('seccion')
            ->map(fn($s) => strtoupper(trim($s)))
            ->toArray();
        $seccionesDisponibles = array_values(array_unique(array_merge($seccionesBase, $seccionesBD)));

        // Conteo de alumnos por nivel
        $conteosNivel = [
            'todos' => Estudiante::count(),
            'inicial' => Estudiante::whereRaw('LOWER(nivel) = ?', ['inicial'])->count(),
            'primaria' => Estudiante::whereRaw('LOWER(nivel) = ?', ['primaria'])->count(),
            'secundaria' => Estudiante::whereRaw('LOWER(nivel) = ?', ['secundaria'])->count(),
        ];

        return view('asistencia_estudiantes.index', compact(
            'asistencias',
            'fecha',
            'horarios',
            'estudiantesLista',
            'filtroTurno',
            'filtroEstado',
            'filtroNivel',
            'filtroGrado',
            'filtroSeccion',
            'search',
            'nivelesDisponibles',
            'gradosDisponibles',
            'seccionesDisponibles',
            'conteosNivel',
            'totalEstudiantes',
            'presentesHoy',
            'puntuales',
            'tardanzasTotal',
            'tardanzasJustificadas',
            'faltasJustificadas',
            'ausentes'
        ));
    }

    public function escanear(Request $request)
    {
        $rawCodigo = trim($request->get('codigo') ?? $request->get('dni') ?? '');
        if (empty($rawCodigo)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Debe escanear o ingresar el DNI del alumno.'], 422);
            }
            return back()->withErrors(['codigo' => 'Debe escanear o ingresar el DNI del alumno.']);
        }

        // Si se escaneó el Código QR ("ASISTENCIA_ESCOLAR|DNI:60976643|...")
        $dni = $rawCodigo;
        if (str_contains($rawCodigo, '|')) {
            $parts = explode('|', $rawCodigo);
            foreach ($parts as $p) {
                if (str_starts_with($p, 'DNI:')) {
                    $dni = substr($p, 4);
                    break;
                }
            }
        }

        $estudiante = Estudiante::with('padreFamilia')->where('dni', $dni)->first();
        if (!$estudiante) {
            $msg = "No se encontró ningún estudiante con el DNI {$dni} en esta institución.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }
            return back()->withErrors(['codigo' => $msg]);
        }

        $empresaId = (int) (session('empresa_id') ?? 1);
        $hoy = date('Y-m-d');
        $horaActual = date('H:i:s');
        $turno = !empty($estudiante->turno) ? $estudiante->turno : 'Mañana';

        // Obtener configuración de horario para el turno del estudiante
        $configTurno = HorarioTurno::where('empresa_id', $empresaId)
            ->where('turno', $turno)
            ->first();

        // Horas límites de tolerancia por turno
        $limitePuntual = '08:00:00';
        if ($configTurno && !empty($configTurno->hora_limite_puntual)) {
            $limitePuntual = strlen($configTurno->hora_limite_puntual) === 5 ? $configTurno->hora_limite_puntual . ':00' : $configTurno->hora_limite_puntual;
        } elseif ($turno === 'Tarde') {
            $limitePuntual = '13:30:00';
        } elseif ($turno === 'Noche') {
            $limitePuntual = '19:00:00';
        }

        $registro = AsistenciaEstudiante::where('estudiante_id', $estudiante->id)
            ->where('fecha', $hoy)
            ->first();

        // 1. Si no ha marcado ingreso hoy (o tenía una inasistencia previa que ahora se convierte en ingreso)
        if (!$registro || (empty($registro->hora_ingreso) && str_contains($registro->estado, 'FALTA'))) {
            $esTarde = strcmp($horaActual, $limitePuntual) > 0;
            $estado = $esTarde ? 'TARDANZA' : 'PUNTUAL';
            $limiteFormato = substr($limitePuntual, 0, 5);
            $obs = $esTarde 
                ? "Tardanza: Ingresó a las {$horaActual} (Límite Turno {$turno}: {$limiteFormato})" 
                : "Ingreso puntual Turno {$turno}";

            if ($registro) {
                $registro->update([
                    'hora_ingreso' => $horaActual,
                    'turno' => $turno,
                    'estado' => $estado,
                    'observacion' => $obs,
                ]);
            } else {
                $registro = AsistenciaEstudiante::create([
                    'estudiante_id' => $estudiante->id,
                    'fecha' => $hoy,
                    'hora_ingreso' => $horaActual,
                    'turno' => $turno,
                    'estado' => $estado,
                    'observacion' => $obs,
                ]);
            }

            $badge = $estado === 'PUNTUAL' ? '✅ PUNTUAL' : '⚠️ TARDANZA';
            $mensaje = "{$badge}: Ingreso registrado para {$estudiante->nombre_completo} [Turno {$turno}] a las {$horaActual}.";

            $payload = [
                'success' => true,
                'asistencia_id' => $registro->id,
                'tipo' => 'INGRESO',
                'estado' => $estado,
                'mensaje' => $mensaje,
                'estudiante' => [
                    'id' => $estudiante->id,
                    'nombre' => $estudiante->nombre_completo,
                    'dni' => $estudiante->dni,
                    'grado' => $estudiante->grado . ' "' . $estudiante->seccion . '"',
                    'nivel' => $estudiante->nivel,
                    'turno' => $turno,
                ],
                'hora' => $horaActual,
                'limite' => $limiteFormato,
            ];

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json($payload);
            }

            return back()->with('success', $mensaje)->with('scan_result', $payload);
        }

        // 2. Si ya marcó ingreso y no ha marcado salida
        if (empty($registro->hora_salida)) {
            // Protección contra doble lectura accidental de escáner dentro de 60 segundos
            if (!app()->environment('testing') && !empty($registro->hora_ingreso)) {
                $segundos = abs(strtotime($horaActual) - strtotime($registro->hora_ingreso));
                if ($segundos < 60) {
                    $mensaje = "ℹ️ El ingreso de {$estudiante->nombre_completo} ya fue registrado a las {$registro->hora_ingreso}. Espere al horario de salida o use el botón de corrección.";
                    $duplicadoPayload = [
                        'success' => true,
                        'asistencia_id' => $registro->id,
                        'tipo' => 'DUPLICADO_PREVENIDO',
                        'estado' => $registro->estado,
                        'mensaje' => $mensaje,
                        'estudiante' => [
                            'id' => $estudiante->id,
                            'nombre' => $estudiante->nombre_completo,
                            'dni' => $estudiante->dni,
                            'grado' => $estudiante->grado . ' "' . $estudiante->seccion . '"',
                            'nivel' => $estudiante->nivel,
                            'turno' => $turno,
                        ],
                        'hora' => $registro->hora_ingreso,
                    ];
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json($duplicadoPayload);
                    }
                    return back()->with('info', $mensaje)->with('scan_result', $duplicadoPayload);
                }
            }

            $registro->update([
                'hora_salida' => $horaActual,
            ]);

            $mensaje = "🚪 SALIDA REGISTRADA: {$estudiante->nombre_completo} ha salido a las {$horaActual}.";

            $salidaPayload = [
                'success' => true,
                'asistencia_id' => $registro->id,
                'tipo' => 'SALIDA',
                'estado' => 'SALIDA',
                'mensaje' => $mensaje,
                'estudiante' => [
                    'id' => $estudiante->id,
                    'nombre' => $estudiante->nombre_completo,
                    'dni' => $estudiante->dni,
                    'grado' => $estudiante->grado . ' "' . $estudiante->seccion . '"',
                    'nivel' => $estudiante->nivel,
                    'turno' => $turno,
                ],
                'hora' => $horaActual,
            ];

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json($salidaPayload);
            }

            return back()->with('success', $mensaje)->with('scan_result', $salidaPayload);
        }

        $mensaje = "ℹ️ El alumno {$estudiante->nombre_completo} ya tiene registrado su ingreso ({$registro->hora_ingreso}) y su salida ({$registro->hora_salida}) para el día de hoy.";

        $completoPayload = [
            'success' => true,
            'asistencia_id' => $registro->id,
            'tipo' => 'COMPLETO',
            'estado' => $registro->estado,
            'mensaje' => $mensaje,
            'estudiante' => [
                'id' => $estudiante->id,
                'nombre' => $estudiante->nombre_completo,
                'dni' => $estudiante->dni,
                'grado' => $estudiante->grado . ' "' . $estudiante->seccion . '"',
                'nivel' => $estudiante->nivel,
                'turno' => $turno,
            ],
            'hora' => $horaActual,
        ];

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($completoPayload);
        }

        return back()->with('success', $mensaje)->with('scan_result', $completoPayload);
    }

    /**
     * Guarda la configuración de horarios de los 3 turnos (Mañana, Tarde, Noche).
     */
    public function guardarHorarios(Request $request)
    {
        $empresaId = (int) (session('empresa_id') ?? 1);
        $datos = $request->validate([
            'horarios' => 'required|array',
            'horarios.*.hora_ingreso' => 'required|string',
            'horarios.*.hora_limite_puntual' => 'required|string',
            'horarios.*.hora_salida' => 'required|string',
            'horarios.*.activo' => 'nullable',
        ]);

        foreach ($datos['horarios'] as $turno => $config) {
            HorarioTurno::updateOrCreate(
                [
                    'empresa_id' => $empresaId,
                    'turno' => $turno,
                ],
                [
                    'hora_ingreso' => $config['hora_ingreso'],
                    'hora_limite_puntual' => $config['hora_limite_puntual'],
                    'hora_salida' => $config['hora_salida'],
                    'activo' => isset($config['activo']),
                ]
            );
        }

        return back()->with('success', 'Configuración de horarios y turnos guardada exitosamente.');
    }

    /**
     * Justifica una tardanza o falta existente en el registro.
     */
    public function justificar(Request $request, $id)
    {
        $asistencia = AsistenciaEstudiante::findOrFail($id);

        $request->validate([
            'motivo_justificacion' => 'required|string|max:255',
            'documento_sustento' => 'nullable|string|max:255',
            'observacion' => 'nullable|string|max:255',
        ]);

        $nuevoEstado = $asistencia->estado;
        if ($asistencia->estado === 'TARDANZA') {
            $nuevoEstado = 'TARDANZA JUSTIFICADA';
        } elseif (str_contains($asistencia->estado, 'FALTA') || str_contains($asistencia->estado, 'INASISTENCIA')) {
            $nuevoEstado = 'FALTA JUSTIFICADA';
        }

        $asistencia->update([
            'justificado' => true,
            'estado' => $nuevoEstado,
            'motivo_justificacion' => $request->input('motivo_justificacion'),
            'documento_sustento' => $request->input('documento_sustento'),
            'fecha_justificacion' => now(),
            'observacion' => $request->input('observacion') ?? $asistencia->observacion,
        ]);

        return back()->with('success', "Asistencia de {$asistencia->estudiante->nombre_completo} justificada correctamente.");
    }

    /**
     * Registra una inasistencia justificada para un alumno que no asistió.
     */
    public function justificarInasistencia(Request $request)
    {
        $request->validate([
            'estudiante_id' => 'required|exists:estudiantes,id',
            'fecha' => 'required|date',
            'motivo_justificacion' => 'required|string|max:255',
            'documento_sustento' => 'nullable|string|max:255',
            'observacion' => 'nullable|string|max:255',
        ]);

        $estudiante = Estudiante::findOrFail($request->estudiante_id);
        $empresaId = (int) (session('empresa_id') ?? 1);
        $turno = !empty($estudiante->turno) ? $estudiante->turno : 'Mañana';

        $registro = AsistenciaEstudiante::where('estudiante_id', $estudiante->id)
            ->where('fecha', $request->fecha)
            ->first();

        if ($registro) {
            $registro->update([
                'justificado' => true,
                'estado' => 'FALTA JUSTIFICADA',
                'motivo_justificacion' => $request->input('motivo_justificacion'),
                'documento_sustento' => $request->input('documento_sustento'),
                'fecha_justificacion' => now(),
                'observacion' => $request->input('observacion') ?? 'Inasistencia justificada por el apoderado/dirección',
            ]);
        } else {
            AsistenciaEstudiante::create([
                'empresa_id' => $empresaId,
                'estudiante_id' => $estudiante->id,
                'fecha' => $request->fecha,
                'turno' => $turno,
                'hora_ingreso' => null,
                'hora_salida' => null,
                'estado' => 'FALTA JUSTIFICADA',
                'justificado' => true,
                'motivo_justificacion' => $request->input('motivo_justificacion'),
                'documento_sustento' => $request->input('documento_sustento'),
                'fecha_justificacion' => now(),
                'observacion' => $request->input('observacion') ?? 'Inasistencia justificada por el apoderado/dirección',
            ]);
        }

        return back()->with('success', "Inasistencia justificada registrada con éxito para {$estudiante->nombre_completo}.");
    }

    /**
     * Corrige o actualiza manualmente la hora de salida de un estudiante, o lo regresa a 'En clase'.
     */
    public function actualizarHoraSalida(Request $request, $id)
    {
        $asistencia = AsistenciaEstudiante::findOrFail($id);

        $request->validate([
            'hora_salida' => 'nullable|string',
            'dejar_en_clase' => 'nullable|boolean',
            'observacion' => 'nullable|string|max:255',
        ]);

        if ($request->boolean('dejar_en_clase') || empty($request->input('hora_salida'))) {
            $asistencia->update([
                'hora_salida' => null,
                'observacion' => $request->input('observacion') ?? $asistencia->observacion,
            ]);
            $msg = "Se corrigió la asistencia de {$asistencia->estudiante->nombre_completo}: marcado como 'En clase' (sin hora de salida).";
        } else {
            $hora = $request->input('hora_salida');
            if (strlen($hora) === 5) {
                $hora .= ':00';
            }
            $asistencia->update([
                'hora_salida' => $hora,
                'observacion' => $request->input('observacion') ?? $asistencia->observacion,
            ]);
            $horaFmt = date('h:i:s A', strtotime($hora));
            $msg = "Hora de salida actualizada para {$asistencia->estudiante->nombre_completo} a las {$horaFmt}.";
        }

        return back()->with('success', $msg);
    }
}
