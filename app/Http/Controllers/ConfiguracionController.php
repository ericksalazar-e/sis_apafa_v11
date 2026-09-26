<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\Empresa;
use App\Models\PadreFamilia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
            ->unique(fn($c) => mb_strtoupper(trim($c->nombre), 'UTF-8'))
            ->values();

        // Asociados miembros de directiva (cuyo cargo no sea ordinario)
        $miembrosDirectiva = PadreFamilia::with(['cargo', 'estudiantes'])
            ->whereHas('cargo', function ($q) {
                $q->where('nombre', '!=', 'PADRE DE FAMILIA');
            })
            ->get();

        // Separar por órgano estatutario (Ley N° 28628)
        $miembrosConsejoDirectivo = $miembrosDirectiva->filter(fn($m) => $m->cargo && $m->cargo->isConsejoDirectivo());
        $miembrosConsejoVigilancia = $miembrosDirectiva->filter(fn($m) => $m->cargo && $m->cargo->isConsejoVigilancia());

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
            'movimientosData'
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
            $validated['foto_institucion'] = 'storage/' . $path;
        }

        // Carga de logo UGEL / Regional (opcional)
        if ($request->hasFile('foto_ugel')) {
            $pathUgel = $request->file('foto_ugel')->store('logos', 'public');
            $validated['foto_ugel'] = 'storage/' . $pathUgel;
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

        if (!empty($validated['password'])) {
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
                if (!empty($dniSaliente)) {
                    $q->where('directivo_saliente_dni', $dniSaliente)
                      ->orWhere('directivo_saliente_nombre', $nombreSaliente);
                } else {
                    $q->where('directivo_saliente_nombre', $nombreSaliente);
                }
            })
            ->first();

        if ($existeSalienteFecha) {
            return back()->withInput()->withErrors([
                'fecha_vacancia' => "Registro Duplicado: Ya existe un registro de vacancia o relevo para el directivo saliente '{$existeSalienteFecha->directivo_saliente_nombre}' en la fecha {$fechaVacancia}. No se pueden repetir registros de un mismo saliente en la misma fecha."
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
            if (!empty($request->directivo_saliente_dni)) {
                $saliente = PadreFamilia::where('padre_dni', $request->directivo_saliente_dni)->first();
                if ($saliente) {
                    $saliente->update(['cargo_id' => $cargoBase->id]);
                }
            }

            // 4. Si se designa directivo entrante con DNI, asignarle el cargo oficial
            if (!empty($request->directivo_entrante_dni)) {
                $entrante = PadreFamilia::where('padre_dni', $request->directivo_entrante_dni)->first();
                if ($entrante) {
                    $cargoDestino = Cargo::where('empresa_id', $empresaId)
                        ->where(function ($q) use ($request) {
                            $q->where('nombre', $request->cargo_nombre)
                              ->orWhere('nombre', 'like', '%' . $request->cargo_nombre . '%');
                        })->first();

                    if (!$cargoDestino) {
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
}
