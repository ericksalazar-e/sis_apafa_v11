<?php

namespace App\Http\Controllers;

use App\Models\Aporte;
use App\Models\AportePadre;
use App\Models\Cargo;
use App\Models\Empresa;
use App\Models\Estudiante;
use App\Models\PadreFamilia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ConfiguracionController extends Controller
{
    /**
     * Muestra la vista principal de Configuración:
     * 1. Datos Institucionales y Logo de la I.E.
     * 2. Registro del Consejo Directivo y Comité de Vigilancia de APAFA.
     * 3. Gestión de Responsabilidades, Usuarios y Contraseñas.
     */
    public function index(Request $request)
    {
        $empresaId = (int) (session('empresa_id') ?? 1);
        $empresa = Empresa::find($empresaId) ?? Empresa::first();

        // 1. Directiva y Comité de APAFA
        // Asegurar que existan los cargos base de APAFA según Ley N° 28628
        $this->asegurarCargosBase($empresa->id ?? 1);

        $cargos = Cargo::where('empresa_id', $empresa->id ?? $empresaId)
            ->orderBy('id', 'asc')
            ->get()
            ->unique(fn ($c) => mb_strtoupper(trim($c->nombre), 'UTF-8'))
            ->values();

        // Asociados miembros de directiva (cuyo cargo no sea ordinario)
        $miembrosDirectiva = PadreFamilia::with(['cargo', 'estudiantes'])
            ->whereHas('cargo', function ($q) {
                $q->where('nombre', '!=', 'PADRE DE FAMILIA');
            })
            ->get();

        // Separar por órgano estatutario (Ley N° 28628)
        $miembrosConsejoDirectivo = $miembrosDirectiva->filter(fn ($m) => $m->cargo && $m->cargo->isConsejoDirectivo());
        $miembrosConsejoVigilancia = $miembrosDirectiva->filter(fn ($m) => $m->cargo && $m->cargo->isConsejoVigilancia());

        // Todos los padres para el selector de asignación
        $padres = PadreFamilia::orderBy('padre_ape_pat')->get();

        // 2. Usuarios del sistema
        $usuarios = User::orderBy('id', 'asc')->get();

        // Roles disponibles con metadatos
        $rolesDisponibles = [
            'ADMIN' => [
                'nombre' => 'Presidente / Administrador General',
                'descripcion' => 'Acceso y control total de todos los módulos, configuración y credenciales.',
                'color' => 'indigo',
                'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            ],
            'SECRETARIO' => [
                'nombre' => 'Secretario de la Directiva',
                'descripcion' => 'Gestión del padrón de asociados, matrícula de estudiantes, emisión de carnés y actas.',
                'color' => 'blue',
                'badge' => 'bg-blue-50 text-blue-700 border-blue-200',
            ],
            'TESORERO' => [
                'nombre' => 'Tesorero de la Directiva',
                'descripcion' => 'Control de aportes, cuotas ordinarias/extraordinarias, cobros POS, comprobantes y presupuesto POA.',
                'color' => 'emerald',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],
            'VOCAL_OPERADOR' => [
                'nombre' => 'Vocal / Operador de Portería',
                'descripcion' => 'Control diario de ingreso escolar y asistencia a asambleas por lector QR o pistola de código de barras.',
                'color' => 'amber',
                'badge' => 'bg-amber-50 text-amber-700 border-amber-200',
            ],
            'COMITE_VIGILANCIA' => [
                'nombre' => 'Comité de Vigilancia (Auditor)',
                'descripcion' => 'Auditoría y fiscalización de cuentas, padrón y gastos en modo de solo lectura.',
                'color' => 'rose',
                'badge' => 'bg-rose-50 text-rose-700 border-rose-200',
            ],
            'DIRECTOR' => [
                'nombre' => 'Director de la I.E.',
                'descripcion' => 'Supervisión institucional, quórum de asambleas e inspección general de asistencia.',
                'color' => 'purple',
                'badge' => 'bg-purple-50 text-purple-700 border-purple-200',
            ],
        ];

        $movimientos = \App\Models\MovimientoDirectiva::latest()->get();
        $movimientosData = $movimientos->map(function ($m) {
            return [
                'dni' => trim($m->directivo_saliente_dni ?? ''),
                'nombre' => trim($m->directivo_saliente_nombre ?? ''),
                'fecha' => \Carbon\Carbon::parse($m->fecha_vacancia)->format('Y-m-d'),
            ];
        })->values();

        // 3. Matriz de Turnos por Grado y Sección
        $gradosSeccionesTurnos = \App\Models\GradoSeccionTurno::where('empresa_id', $empresa->id ?? $empresaId)
            ->orderBy('nivel')
            ->orderBy('grado')
            ->orderBy('seccion')
            ->get();

        // Talleres Extracurriculares (Contraturno)
        $talleres = \App\Models\Taller::where('empresa_id', $empresa->id ?? $empresaId)
            ->withCount('asistencias')
            ->orderBy('id', 'desc')
            ->get();

        // Grados y secciones actualmente registrados en la I.E. desde Estudiantes
        $gradosSeccionesExistentes = Estudiante::where('empresa_id', $empresa->id ?? $empresaId)
            ->whereNotNull('nivel')
            ->whereNotNull('grado')
            ->whereNotNull('seccion')
            ->select('nivel', 'grado', 'seccion')
            ->distinct()
            ->orderBy('nivel')
            ->orderBy('grado')
            ->orderBy('seccion')
            ->get();

        $horariosTurnos = \App\Models\HorarioTurno::sincronizarYObtener($empresa->id ?? $empresaId);

        $empresaIdVal = $empresa->id ?? $empresaId;
        $totalMatriculados = Estudiante::where('empresa_id', $empresaIdVal)
            ->where(fn ($q) => $q->whereNull('estado_matricula')->orWhere('estado_matricula', 'MATRICULADO'))
            ->count();
        $totalEgresados = Estudiante::where('empresa_id', $empresaIdVal)
            ->where('estado_matricula', 'EGRESADO')
            ->count();
        $totalPadresAsoc = PadreFamilia::where('empresa_id', $empresaIdVal)
            ->count();

        return view('configuracion.index', compact(
            'empresa',
            'cargos',
            'miembrosDirectiva',
            'miembrosConsejoDirectivo',
            'miembrosConsejoVigilancia',
            'padres',
            'usuarios',
            'rolesDisponibles',
            'movimientos',
            'movimientosData',
            'gradosSeccionesTurnos',
            'talleres',
            'gradosSeccionesExistentes',
            'horariosTurnos',
            'totalMatriculados',
            'totalEgresados',
            'totalPadresAsoc'
        ));
    }

    /**
     * Actualiza los datos institucionales de la I.E. y sube su logo oficial.
     */
    public function updateInstitucion(Request $request)
    {
        $empresaId = (int) (session('empresa_id') ?? 1);
        $empresa = Empresa::findOrFail($empresaId);

        $validated = $request->validate([
            'nombre' => 'required|string|max:200',
            'cod_modular' => 'nullable|string|max:30',
            'ruc' => 'nullable|string|max:11',
            'ubicacion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'correo' => 'nullable|email|max:100',
            'periodo_apafa' => 'nullable|string|max:50',
            'lema' => 'nullable|string|max:255',
            'nivel_educativo' => 'nullable|string|max:100',
            'director_nombre' => 'nullable|string|max:150',
            'foto_institucion' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'foto_ugel' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);

        // Carga de logo de la I.E.
        if ($request->hasFile('foto_institucion')) {
            $path = $request->file('foto_institucion')->store('logos', 'public');
            $validated['foto_institucion'] = 'storage/'.$path;
            $this->generarFaviconDesdeImagen(public_path('storage/'.$path));
        }

        // Carga de logo UGEL / Regional (opcional)
        if ($request->hasFile('foto_ugel')) {
            $pathUgel = $request->file('foto_ugel')->store('logos', 'public');
            $validated['foto_ugel'] = 'storage/'.$pathUgel;
        }

        $empresa->update($validated);

        return redirect()->route('configuracion.index', ['tab' => 'institucion'])
            ->with('success', '¡Datos institucionales y logo de la I.E. actualizados exitosamente!');
    }

    /**
     * Asigna un cargo de la directiva de APAFA o Comité de Vigilancia a un asociado.
     */
    public function asignarCargoComite(Request $request)
    {
        $validated = $request->validate([
            'padre_familia_id' => 'required|exists:padres_familia,id',
            'cargo_id' => 'required|exists:cargos,id',
        ]);

        $padre = PadreFamilia::findOrFail($validated['padre_familia_id']);
        $padre->update(['cargo_id' => $validated['cargo_id']]);

        $cargo = Cargo::find($validated['cargo_id']);

        return redirect()->route('configuracion.index', ['tab' => 'comite'])
            ->with('success', "¡Se asignó el cargo '{$cargo->nombre}' a {$padre->nombre_completo} exitosamente!");
    }

    /**
     * Crea un nuevo usuario con credenciales y responsabilidad asignada.
     */
    public function storeUsuario(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|max:100|unique:users,email',
            'rol' => 'required|in:ADMIN,SECRETARIO,TESORERO,VOCAL_OPERADOR,COMITE_VIGILANCIA,DIRECTOR',
            'cargo_titulo' => 'nullable|string|max:100',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $empresaId = (int) (session('empresa_id') ?? 1);

        $usuario = User::create([
            'empresa_id' => $empresaId,
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => strtolower($validated['email']),
            'rol' => $validated['rol'],
            'cargo_titulo' => $validated['cargo_titulo'] ?? null,
            'password' => Hash::make($validated['password']),
            'estado' => 1,
            'codigo' => sprintf('USR-%04d', rand(100, 9999)),
        ]);

        return redirect()->route('configuracion.index', ['tab' => 'usuarios'])
            ->with('success', "¡Usuario '{$usuario->username}' ({$usuario->rol_nombre}) creado correctamente!");
    }

    /**
     * Actualiza los datos, rol o contraseña de un usuario existente.
     */
    public function updateUsuario(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'username' => "required|string|max:50|unique:users,username,{$usuario->id}",
            'email' => "required|email|max:100|unique:users,email,{$usuario->id}",
            'rol' => 'required|in:ADMIN,SECRETARIO,TESORERO,VOCAL_OPERADOR,COMITE_VIGILANCIA,DIRECTOR',
            'cargo_titulo' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:6',
            'estado' => 'nullable|in:0,1',
        ]);

        $data = [
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => strtolower($validated['email']),
            'rol' => $validated['rol'],
            'cargo_titulo' => $validated['cargo_titulo'] ?? null,
            'estado' => isset($validated['estado']) ? (int) $validated['estado'] : $usuario->estado,
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $usuario->update($data);

        return redirect()->route('configuracion.index', ['tab' => 'usuarios'])
            ->with('success', "¡Usuario '{$usuario->username}' actualizado correctamente!");
    }

    /**
     * Elimina un usuario del sistema (impide autoeliminación).
     */
    public function destroyUsuario(User $usuario)
    {
        if (auth()->id() === $usuario->id) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta en uso.');
        }

        $username = $usuario->username;
        $usuario->delete();

        return redirect()->route('configuracion.index', ['tab' => 'usuarios'])
            ->with('success', "¡Usuario '{$username}' eliminado exitosamente!");
    }

    /**
     * Asegura que existan los cargos reglamentarios de APAFA según Ley 28628.
     */
    private function asegurarCargosBase(int $empresaId): void
    {
        $cargosReglamentarios = [
            'PADRE DE FAMILIA',
            'PRESIDENTE DE APAFA',
            'VICEPRESIDENTE DE APAFA',
            'SECRETARIO(A)',
            'TESORERO(A)',
            '1ER VOCAL (OPERADOR)',
            '2DO VOCAL',
            '3ER VOCAL',
            'PRESIDENTE COMITÉ DE VIGILANCIA',
            'SECRETARIO COMITÉ DE VIGILANCIA',
            'VOCAL COMITÉ DE VIGILANCIA',
        ];

        foreach ($cargosReglamentarios as $nom) {
            Cargo::firstOrCreate(
                ['empresa_id' => $empresaId, 'nombre' => $nom],
                ['nombre' => $nom]
            );
        }
    }

    /**
     * Registrar vacancia, relevo o función cumplida de directivo según Ley N.° 28628 y D.S. N.º 007-2026-MINEDU
     */
    public function storeMovimientoDirectiva(Request $request)
    {
        $request->validate([
            'cargo_nombre' => 'required|string',
            'directivo_saliente_nombre' => 'required|string',
            'causal_vacancia' => 'required|in:FUNCION_CUMPLIDA,NO_ASUME_CARGO,RENUNCIA_VOLUNTARIA,INASISTENCIA_3_ASAMBLEAS,EGRESO_DEL_HIJO,FALLECIMIENTO_O_INCAPACIDAD,DESTITUCION_POR_ASAMBLEA',
            'fecha_vacancia' => 'required|date',
            'directivo_entrante_nombre' => 'nullable|string',
        ]);

        $empresaId = (int) (session('empresa_id') ?? 1);
        $fechaVacancia = date('Y-m-d', strtotime($request->fecha_vacancia));
        $dniSaliente = trim($request->directivo_saliente_dni ?? '');
        $nombreSaliente = trim($request->directivo_saliente_nombre);

        // Prevención de duplicados: no repetir fecha del mismo directivo saliente
        $existeSalienteFecha = \App\Models\MovimientoDirectiva::where('empresa_id', $empresaId)
            ->whereDate('fecha_vacancia', $fechaVacancia)
            ->where(function ($q) use ($dniSaliente, $nombreSaliente) {
                if (! empty($dniSaliente)) {
                    $q->where('directivo_saliente_dni', $dniSaliente)
                        ->orWhere('directivo_saliente_nombre', $nombreSaliente);
                } else {
                    $q->where('directivo_saliente_nombre', $nombreSaliente);
                }
            })
            ->first();

        if ($existeSalienteFecha) {
            return back()->withInput()->withErrors([
                'fecha_vacancia' => "Registro Duplicado: Ya existe un registro de vacancia o relevo para el directivo saliente '{$existeSalienteFecha->directivo_saliente_nombre}' en la fecha {$fechaVacancia}. No se pueden repetir registros de un mismo saliente en la misma fecha.",
            ])->with('error', "No se pueden repetir movimientos para el mismo directivo saliente en la misma fecha ({$fechaVacancia}).");
        }

        $nombreEntrante = trim($request->input('directivo_entrante_nombre', ''));
        if (empty($nombreEntrante)) {
            $nombreEntrante = 'VACANTE / POR DESIGNAR EN ASAMBLEA';
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $empresaId, $nombreEntrante) {
            // 1. Registrar el movimiento oficial de recomposición o cierre de función
            \App\Models\MovimientoDirectiva::create([
                'empresa_id' => $empresaId,
                'cargo_nombre' => $request->cargo_nombre,
                'directivo_saliente_nombre' => $request->directivo_saliente_nombre,
                'directivo_saliente_dni' => $request->directivo_saliente_dni,
                'causal_vacancia' => $request->causal_vacancia,
                'fecha_vacancia' => $request->fecha_vacancia,
                'directivo_entrante_nombre' => $nombreEntrante,
                'directivo_entrante_dni' => $request->directivo_entrante_dni,
                'directivo_entrante_parentesco' => $request->directivo_entrante_parentesco,
                'numero_acta_asamblea' => $request->numero_acta_asamblea,
                'observaciones' => $request->observaciones,
            ]);

            // 2. Obtener o crear el cargo ordinario base 'PADRE DE FAMILIA'
            $cargoBase = Cargo::firstOrCreate(
                ['empresa_id' => $empresaId, 'nombre' => 'PADRE DE FAMILIA'],
                ['descripcion' => 'Asociado ordinario sin cargo directivo', 'es_oficial' => false]
            );

            // 3. Actualizar al Directivo Saliente (Retirarle el cargo directivo por Función Cumplida / Vacancia)
            if (! empty($request->directivo_saliente_dni)) {
                $saliente = PadreFamilia::where('padre_dni', $request->directivo_saliente_dni)->first();
                if ($saliente) {
                    $saliente->update(['cargo_id' => $cargoBase->id]);
                }
            }

            // 4. Si se designa directivo entrante con DNI, asignarle el cargo oficial
            if (! empty($request->directivo_entrante_dni)) {
                $entrante = PadreFamilia::where('padre_dni', $request->directivo_entrante_dni)->first();
                if ($entrante) {
                    $cargoDestino = Cargo::where('empresa_id', $empresaId)
                        ->where(function ($q) use ($request) {
                            $q->where('nombre', $request->cargo_nombre)
                                ->orWhere('nombre', 'like', '%'.$request->cargo_nombre.'%');
                        })->first();

                    if (! $cargoDestino) {
                        $cargoDestino = Cargo::create([
                            'empresa_id' => $empresaId,
                            'nombre' => $request->cargo_nombre,
                            'descripcion' => 'Designado por recomposición directiva (Ley 28628)',
                            'es_oficial' => true,
                        ]);
                    }

                    $entrante->update(['cargo_id' => $cargoDestino->id]);
                }
            }
        });

        $mensajeCausal = match ($request->causal_vacancia) {
            'FUNCION_CUMPLIDA' => "Función Cumplida registrada para {$request->directivo_saliente_nombre}. Cierre de gestión bienal formalizado.",
            'NO_ASUME_CARGO' => "Constancia de no asunción de funciones / desistimiento registrada para {$request->directivo_saliente_nombre}.",
            default => "Movimiento de recomposición directiva registrado ({$request->causal_vacancia}) conforme a Ley N.° 28628 y D.S. N.º 007-2026-MINEDU."
        };

        return redirect()->route('configuracion.index', ['tab' => 'comite'])
            ->with('success', $mensajeCausal);
    }

    /**
     * Eliminar un registro de movimiento directivo del historial
     */
    public function destroyMovimientoDirectiva($id)
    {
        $mov = \App\Models\MovimientoDirectiva::findOrFail($id);
        $saliente = $mov->directivo_saliente_nombre;
        $mov->delete();

        return redirect()->route('configuracion.index', ['tab' => 'comite'])
            ->with('success', "Registro de relevo de '{$saliente}' eliminado correctamente del historial.");
    }

    /**
     * Emisión oficial de Credencial de Junta Directiva y Consejo de Vigilancia
     */
    public function imprimirCredencialDirectiva()
    {
        $empresaId = (int) (session('empresa_id') ?? 1);
        $empresa = Empresa::find($empresaId) ?? Empresa::first();

        $miembrosDirectiva = PadreFamilia::with(['cargo', 'estudiantes'])
            ->whereHas('cargo', function ($q) {
                $q->where('nombre', '!=', 'PADRE DE FAMILIA');
            })
            ->get();

        $movimientos = \App\Models\MovimientoDirectiva::latest()->take(5)->get();

        return view('configuracion.credencial_directiva', compact('empresa', 'miembrosDirectiva', 'movimientos'));
    }

    /**
     * Guarda o actualiza el turno oficial de un Grado y Sección.
     */
    public function storeGradoSeccionTurno(Request $request)
    {
        $empresaId = (int) (session('empresa_id') ?? 1);
        $validated = $request->validate([
            'nivel' => 'required|string|max:50',
            'grado' => 'required|string|max:50',
            'seccion' => 'required|string|max:20',
            'turno' => 'required|string|in:Mañana,Tarde,Noche',
            'tutor' => 'nullable|string|max:150',
            'aula' => 'nullable|string|max:100',
            'sincronizar_alumnos' => 'nullable',
        ]);

        $nivel = trim($validated['nivel']);
        $grado = trim($validated['grado']);
        $seccion = strtoupper(trim($validated['seccion']));
        $turno = trim($validated['turno']);

        $config = \App\Models\GradoSeccionTurno::updateOrCreate(
            [
                'empresa_id' => $empresaId,
                'nivel' => $nivel,
                'grado' => $grado,
                'seccion' => $seccion,
            ],
            [
                'turno' => $turno,
                'tutor' => ! empty($validated['tutor']) ? trim($validated['tutor']) : null,
                'aula' => ! empty($validated['aula']) ? trim($validated['aula']) : null,
                'activo' => true,
            ]
        );

        $msg = "Turno '{$turno}' asignado exitosamente a {$nivel} - {$grado} \"{$seccion}\".";

        if ($request->boolean('sincronizar_alumnos') || $request->has('sincronizar_alumnos')) {
            $actualizados = $config->sincronizarAlumnosConTurno();
            $msg .= " Se sincronizó y actualizó a {$actualizados} estudiante(s) matriculado(s) en este aula.";
        }

        return redirect()->route('configuracion.index', ['tab' => 'turnos-talleres'])
            ->with('success', $msg);
    }

    /**
     * Elimina la configuración de turno de un Grado y Sección.
     */
    public function destroyGradoSeccionTurno($id)
    {
        $config = \App\Models\GradoSeccionTurno::findOrFail($id);
        $detalle = "{$config->nivel} - {$config->grado} \"{$config->seccion}\"";
        $config->delete();

        return redirect()->route('configuracion.index', ['tab' => 'turnos-talleres'])
            ->with('success', "Configuración de turno para {$detalle} eliminada.");
    }

    /**
     * Sincronización masiva de todos los estudiantes existentes con la matriz de turnos configurada.
     */
    public function sincronizarTurnosMasivo()
    {
        $empresaId = (int) (session('empresa_id') ?? 1);
        $configs = \App\Models\GradoSeccionTurno::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->get();

        $totalActualizados = 0;
        foreach ($configs as $cfg) {
            $totalActualizados += $cfg->sincronizarAlumnosConTurno();
        }

        return redirect()->route('configuracion.index', ['tab' => 'turnos-talleres'])
            ->with('success', "¡Sincronización masiva completada! {$totalActualizados} estudiante(s) actualizados al turno que le corresponde a su grado y sección.");
    }

    /**
     * Registra un nuevo Taller extracurricular / actividad en contraturno.
     */
    public function storeTaller(Request $request)
    {
        $empresaId = (int) (session('empresa_id') ?? 1);
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:500',
            'nivel' => 'required|string|max:50',
            'grado' => 'required|string|max:50',
            'seccion' => 'required|string|max:20',
            'turno' => 'required|string|in:Mañana,Tarde,Noche,Sábado',
            'hora_inicio' => 'required|string',
            'hora_limite_puntual' => 'required|string',
            'hora_fin' => 'required|string',
            'dias_semana' => 'required|array|min:1',
            'docente_responsable' => 'nullable|string|max:150',
            'lugar' => 'nullable|string|max:100',
        ]);

        $diasStr = implode(',', $validated['dias_semana']);

        \App\Models\Taller::create([
            'empresa_id' => $empresaId,
            'nombre' => trim($validated['nombre']),
            'descripcion' => ! empty($validated['descripcion']) ? trim($validated['descripcion']) : null,
            'nivel' => trim($validated['nivel']),
            'grado' => trim($validated['grado']),
            'seccion' => strtoupper(trim($validated['seccion'])),
            'turno' => trim($validated['turno']),
            'hora_inicio' => $validated['hora_inicio'],
            'hora_limite_puntual' => $validated['hora_limite_puntual'],
            'hora_fin' => $validated['hora_fin'],
            'dias_semana' => $diasStr,
            'docente_responsable' => ! empty($validated['docente_responsable']) ? trim($validated['docente_responsable']) : null,
            'lugar' => ! empty($validated['lugar']) ? trim($validated['lugar']) : null,
            'activo' => true,
        ]);

        return redirect()->route('configuracion.index', ['tab' => 'turnos-talleres'])
            ->with('success', "Taller extracurricular '{$validated['nombre']}' registrado exitosamente para contraturno.");
    }

    /**
     * Actualiza los datos y horarios de un Taller extracurricular.
     */
    public function updateTaller(Request $request, $id)
    {
        $taller = \App\Models\Taller::findOrFail($id);
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:500',
            'nivel' => 'required|string|max:50',
            'grado' => 'required|string|max:50',
            'seccion' => 'required|string|max:20',
            'turno' => 'required|string|in:Mañana,Tarde,Noche,Sábado',
            'hora_inicio' => 'required|string',
            'hora_limite_puntual' => 'required|string',
            'hora_fin' => 'required|string',
            'dias_semana' => 'required|array|min:1',
            'docente_responsable' => 'nullable|string|max:150',
            'lugar' => 'nullable|string|max:100',
            'activo' => 'nullable',
        ]);

        $diasStr = implode(',', $validated['dias_semana']);

        $taller->update([
            'nombre' => trim($validated['nombre']),
            'descripcion' => ! empty($validated['descripcion']) ? trim($validated['descripcion']) : null,
            'nivel' => trim($validated['nivel']),
            'grado' => trim($validated['grado']),
            'seccion' => strtoupper(trim($validated['seccion'])),
            'turno' => trim($validated['turno']),
            'hora_inicio' => $validated['hora_inicio'],
            'hora_limite_puntual' => $validated['hora_limite_puntual'],
            'hora_fin' => $validated['hora_fin'],
            'dias_semana' => $diasStr,
            'docente_responsable' => ! empty($validated['docente_responsable']) ? trim($validated['docente_responsable']) : null,
            'lugar' => ! empty($validated['lugar']) ? trim($validated['lugar']) : null,
            'activo' => $request->boolean('activo') || $request->has('activo'),
        ]);

        return redirect()->route('configuracion.index', ['tab' => 'turnos-talleres'])
            ->with('success', "Taller extracurricular '{$taller->nombre}' actualizado con éxito.");
    }

    /**
     * Elimina un Taller extracurricular.
     */
    public function destroyTaller($id)
    {
        $taller = \App\Models\Taller::findOrFail($id);
        $nombre = $taller->nombre;
        $taller->delete();

        return redirect()->route('configuracion.index', ['tab' => 'turnos-talleres'])
            ->with('success', "Taller '{$nombre}' eliminado correctamente.");
    }

    /**
     * Registra o actualiza el Fondo de Apertura y Saldo Inicial de Caja.
     * Permite incorporar fondos previos existentes antes del sistema (acta de entrega de cargo o arqueo).
     */
    public function updateSaldoInicial(Request $request)
    {
        $empresaId = (int) (session('empresa_id') ?? 1);
        $empresa = Empresa::findOrFail($empresaId);

        $validated = $request->validate([
            'saldo_inicial_caja' => 'required|numeric|min:0',
            'fecha_saldo_inicial' => 'required|date',
            'sustento_saldo_inicial' => 'required|string|max:255',
            'glosa_saldo_inicial' => 'nullable|string|max:500',
        ], [
            'saldo_inicial_caja.required' => 'El monto del saldo inicial de caja es obligatorio.',
            'fecha_saldo_inicial.required' => 'La fecha de corte o arqueo del saldo inicial es obligatoria.',
            'sustento_saldo_inicial.required' => 'Debe indicar el documento de sustento (ej. Acta de Entrega de Cargo N° 01-2026).',
        ]);

        $empresa->update($validated);

        $formateado = number_format($validated['saldo_inicial_caja'], 2);

        return redirect()->route('configuracion.index', ['tab' => 'saldo-inicial'])
            ->with('success', "Fondo de Apertura y Saldo Inicial consolidado exitosamente en S/ {$formateado}. Los balances contables, el POA y los dictámenes de auditoría han sido actualizados.");
    }

    /**
     * Asistente de Cierre de Año Escolar y Apertura de Nuevo Ciclo Lectivo.
     * Promueve masivamente a los alumnos al grado inmediato superior,
     * actualiza el periodo lectivo de la APAFA y apertura la nueva cuota anual.
     */
    public function aperturaNuevoAnio(Request $request)
    {
        $empresaId = (int) (session('empresa_id') ?? 1);
        $empresa = Empresa::findOrFail($empresaId);

        // Normalizar nombres de parámetros si vienen de modalidades simplificadas o modal de estudiantes
        if (! $request->has('nuevo_anio_lectivo') && $request->has('nuevo_anio')) {
            $request->merge(['nuevo_anio_lectivo' => $request->input('nuevo_anio')]);
        }
        if (! $request->has('nuevo_periodo_apafa') && $request->has('periodo_apafa')) {
            $request->merge(['nuevo_periodo_apafa' => $request->input('periodo_apafa')]);
        }
        if (! $request->has('promover_alumnos') && $request->has('promover_grados')) {
            $request->merge(['promover_alumnos' => $request->input('promover_grados')]);
        }
        if (! $request->has('crear_cuota_apafa') && $request->has('generar_cuotas')) {
            $request->merge(['crear_cuota_apafa' => $request->input('generar_cuotas')]);
        }
        if (! $request->has('monto_cuota_apafa') && $request->has('monto_cuota')) {
            $request->merge(['monto_cuota_apafa' => $request->input('monto_cuota')]);
        }

        $validated = $request->validate([
            'nuevo_anio_lectivo' => 'required|integer|min:2024|max:2050',
            'nuevo_periodo_apafa' => 'nullable|string|max:50',
            'promover_alumnos' => 'nullable',
            'crear_cuota_apafa' => 'nullable',
            'monto_cuota_apafa' => 'nullable|numeric|min:0|max:82.50',
        ], [
            'nuevo_anio_lectivo.required' => 'Debe especificar el nuevo año escolar lectivo (ej: '.((int) date('Y') + 1).').',
            'monto_cuota_apafa.max' => 'La cuota ordinaria no puede exceder el 1.5% de la UIT (máximo S/ 82.50 según Ley 28628).',
        ]);

        $nuevoAnio = (int) $validated['nuevo_anio_lectivo'];
        $promover = $request->boolean('promover_alumnos') || $request->input('promover_alumnos') === '1';
        $crearCuota = $request->boolean('crear_cuota_apafa') || $request->input('crear_cuota_apafa') === '1';
        $montoCuota = (float) $request->input('monto_cuota_apafa', 50.00);

        $resultado = \Illuminate\Support\Facades\DB::transaction(function () use ($empresa, $empresaId, $nuevoAnio, $validated, $promover, $crearCuota, $montoCuota) {
            // 1. Actualizar año lectivo y periodo en la institución
            $nuevoPeriodo = ! empty($validated['nuevo_periodo_apafa'])
                ? trim($validated['nuevo_periodo_apafa'])
                : "{$nuevoAnio} - ".($nuevoAnio + 1);

            $empresa->update([
                'anio_lectivo_actual' => $nuevoAnio,
                'periodo_apafa' => $nuevoPeriodo,
            ]);

            $promovidos = 0;
            $egresados = 0;

            // 2. Promoción de alumnos
            if ($promover) {
                $estudiantes = Estudiante::where('empresa_id', $empresaId)
                    ->where(function ($q) {
                        $q->whereNull('estado_matricula')
                            ->orWhere('estado_matricula', 'MATRICULADO');
                    })
                    ->get();

                foreach ($estudiantes as $est) {
                    $nivel = trim($est->nivel);
                    $grado = trim($est->grado);
                    $nuevoGrado = $grado;
                    $nuevoNivel = $nivel;
                    $nuevoEstado = 'MATRICULADO';

                    $numGrado = (int) filter_var($grado, FILTER_SANITIZE_NUMBER_INT);

                    if (stripos($nivel, 'Inicial') !== false) {
                        if ($numGrado === 3 || str_contains($grado, '3')) {
                            $nuevoGrado = '4 Años';
                        } elseif ($numGrado === 4 || str_contains($grado, '4')) {
                            $nuevoGrado = '5 Años';
                        } elseif ($numGrado === 5 || str_contains($grado, '5')) {
                            $nuevoNivel = 'Primaria';
                            $nuevoGrado = '1ro';
                        }
                    } elseif (stripos($nivel, 'Primaria') !== false) {
                        if ($numGrado === 1 || str_contains($grado, '1')) {
                            $nuevoGrado = '2do';
                        } elseif ($numGrado === 2 || str_contains($grado, '2')) {
                            $nuevoGrado = '3ro';
                        } elseif ($numGrado === 3 || str_contains($grado, '3')) {
                            $nuevoGrado = '4to';
                        } elseif ($numGrado === 4 || str_contains($grado, '4')) {
                            $nuevoGrado = '5to';
                        } elseif ($numGrado === 5 || str_contains($grado, '5')) {
                            $nuevoGrado = '6to';
                        } elseif ($numGrado === 6 || str_contains($grado, '6')) {
                            $nuevoNivel = 'Secundaria';
                            $nuevoGrado = '1ro';
                        }
                    } elseif (stripos($nivel, 'Secundaria') !== false) {
                        if ($numGrado === 1 || str_contains($grado, '1')) {
                            $nuevoGrado = '2do';
                        } elseif ($numGrado === 2 || str_contains($grado, '2')) {
                            $nuevoGrado = '3ro';
                        } elseif ($numGrado === 3 || str_contains($grado, '3')) {
                            $nuevoGrado = '4to';
                        } elseif ($numGrado === 4 || str_contains($grado, '4')) {
                            $nuevoGrado = '5to';
                        } elseif ($numGrado === 5 || str_contains($grado, '5')) {
                            $nuevoEstado = 'EGRESADO';
                            $egresados++;
                        }
                    }

                    if ($nuevoEstado !== 'EGRESADO') {
                        $promovidos++;
                    }

                    $nuevoTurno = \App\Models\GradoSeccionTurno::obtenerTurnoPredeterminado($empresaId, $nuevoNivel, $nuevoGrado, $est->seccion)
                        ?: $est->turno;

                    $est->update([
                        'nivel' => $nuevoNivel,
                        'grado' => $nuevoGrado,
                        'turno' => $nuevoTurno,
                        'estado_matricula' => $nuevoEstado,
                    ]);
                }
            }

            // 3. Crear cuota anual de APAFA del nuevo año
            $cuotaCreada = null;
            if ($crearCuota && $montoCuota > 0) {
                $tituloCuota = "Cuota Ordinaria Anual APAFA {$nuevoAnio}";
                $existeAporte = Aporte::where('empresa_id', $empresaId)
                    ->where('titulo', $tituloCuota)
                    ->exists();

                if (! $existeAporte) {
                    $aporte = Aporte::create([
                        'empresa_id' => $empresaId,
                        'titulo' => $tituloCuota,
                        'tipo' => 'ORDINARIA',
                        'monto' => $montoCuota,
                        'fecha' => "{$nuevoAnio}-03-01",
                        'uit_vigente' => 5350.00,
                        'max_legal' => 80.25,
                        'descripcion' => "Cuota ordinaria anual de asociados para el año lectivo {$nuevoAnio}.",
                    ]);

                    $padres = PadreFamilia::where('empresa_id', $empresaId)->select('id')->get();
                    $batch = [];
                    $now = now();
                    foreach ($padres as $p) {
                        $batch[] = [
                            'empresa_id' => $empresaId,
                            'aporte_id' => $aporte->id,
                            'padre_familia_id' => $p->id,
                            'monto_aporte' => $montoCuota,
                            'monto_aportado' => 0.00,
                            'debe' => $montoCuota,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                    foreach (array_chunk($batch, 250) as $chunk) {
                        AportePadre::insert($chunk);
                    }
                    $cuotaCreada = $tituloCuota;
                }
            }

            return [
                'promovidos' => $promovidos,
                'egresados' => $egresados,
                'cuota' => $cuotaCreada,
                'periodo' => $nuevoPeriodo,
            ];
        });

        $msg = "¡Apertura del Año Escolar {$nuevoAnio} completada con éxito! ";
        if ($promover) {
            $msg .= "{$resultado['promovidos']} alumno(s) promovidos al siguiente grado y {$resultado['egresados']} alumno(s) egresados de 5to de secundaria. ";
        }
        if ($resultado['cuota']) {
            $msg .= "Se aperturó '{$resultado['cuota']}' (S/ ".number_format($montoCuota, 2).') para todos los padres de familia. ';
        }
        $msg .= "Periodo de APAFA actualizado a '{$resultado['periodo']}'.";

        $tab = $request->input('origen') === 'estudiantes' ? 'estudiantes' : 'apertura-anio';
        if ($request->input('origen') === 'estudiantes') {
            return redirect()->route('estudiantes.index')->with('success', $msg);
        }

        return redirect()->route('configuracion.index', ['tab' => $tab])->with('success', $msg);
    }

    /**
     * Regenera automáticamente los íconos de favicon del navegador en alta definición a partir de la imagen institucional cargada.
     */
    private function generarFaviconDesdeImagen(string $sourcePath): void
    {
        if (! file_exists($sourcePath) || ! extension_loaded('gd')) {
            return;
        }

        try {
            $info = @getimagesize($sourcePath);
            if (! $info) {
                return;
            }

            $mime = $info['mime'] ?? '';
            $srcImg = match ($mime) {
                'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($sourcePath),
                'image/png' => @imagecreatefrompng($sourcePath),
                'image/webp' => @imagecreatefromwebp($sourcePath),
                default => null,
            };

            if (! $srcImg) {
                return;
            }

            $srcW = imagesx($srcImg);
            $srcH = imagesy($srcImg);

            $sizes = [
                'favicon.ico' => 64,
                'favicon.png' => 128,
                'favicon-32x32.png' => 32,
                'favicon-16x16.png' => 16,
                'apple-touch-icon.png' => 180,
            ];

            foreach ($sizes as $filename => $targetSize) {
                $targetImg = imagecreatetruecolor($targetSize, $targetSize);
                imagealphablending($targetImg, false);
                imagesavealpha($targetImg, true);
                $transparent = imagecolorallocatealpha($targetImg, 0, 0, 0, 127);
                imagefilledrectangle($targetImg, 0, 0, $targetSize, $targetSize, $transparent);
                imagealphablending($targetImg, true);

                $scale = min($targetSize / $srcW, $targetSize / $srcH);
                $newW = (int) round($srcW * $scale);
                $newH = (int) round($srcH * $scale);
                $dstX = (int) round(($targetSize - $newW) / 2);
                $dstY = (int) round(($targetSize - $newH) / 2);

                imagecopyresampled($targetImg, $srcImg, $dstX, $dstY, 0, 0, $newW, $newH, $srcW, $srcH);
                imagepng($targetImg, public_path($filename), 9);
                imagedestroy($targetImg);
            }

            imagedestroy($srcImg);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error regenerando favicon: '.$e->getMessage());
        }
    }
}
