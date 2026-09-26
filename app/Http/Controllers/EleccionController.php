<?php

namespace App\Http\Controllers;

use App\Models\ComicioElectoral;
use App\Models\Empresa;
use App\Models\PadreFamilia;
use App\Models\VotoPadron;
use App\Models\ListaElectoral;
use App\Models\CandidatoElectoral;
use App\Models\Cargo;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EleccionController extends Controller
{
    /**
     * Panel Principal de Comicios Electorales APAFA
     * Adelanto de cronograma al 1 de Setiembre (D.S. N.º 007-2026-MINEDU)
     */
    public function index(Request $request)
    {
        $comicioId = $request->query('comicio_id');
        
        if ($comicioId) {
            $comicio = ComicioElectoral::with([
                'votos.padreFamilia',
                'listas.candidatos.padreFamilia.estudiantes'
            ])->find($comicioId);
        }

        if (empty($comicio)) {
            $comicio = ComicioElectoral::with([
                'votos.padreFamilia',
                'listas.candidatos.padreFamilia.estudiantes'
            ])->latest()->first();
        }

        // Si no existe proceso convocado, creamos el del presente periodo por defecto dentro de la ventana legal
        if (!$comicio) {
            $comicio = ComicioElectoral::create([
                'empresa_id' => session('empresa_id', 1),
                'año_electoral' => 2026,
                'nombre_proceso' => 'Elecciones Generales de Junta Directiva y Consejo de Vigilancia (Ley 28628 / D.S. 007-2026-MINEDU)',
                'fecha_corte_padron' => '2026-08-31',
                'fecha_elecciones' => '2026-10-15',
                'estado' => 'CONVOCADA',
            ]);
        }

        $comiciosHistoricos = ComicioElectoral::orderBy('año_electoral', 'desc')->get();

        $totalElectores = VotoPadron::where('comicio_id', $comicio->id)->count();
        $totalVotaron = VotoPadron::where('comicio_id', $comicio->id)->where('ha_votado', true)->count();
        $totalPendientes = $totalElectores - $totalVotaron;
        $porcentajeParticipacion = $totalElectores > 0 ? round(($totalVotaron / $totalElectores) * 100, 1) : 0;

        $ultimosVotos = VotoPadron::with(['padreFamilia', 'listaElectoral'])
            ->where('comicio_id', $comicio->id)
            ->where('ha_votado', true)
            ->orderBy('fecha_hora_voto', 'desc')
            ->take(10)
            ->get();

        $padres = PadreFamilia::orderBy('padre_ape_pat')->orderBy('padre_nombres')->get();

        // Verificación de la ventana legal (1 de setiembre al 30 de noviembre)
        $esVentanaLegal = $comicio->es_ventana_legal_valida;

        return view('elecciones.index', compact(
            'comicio',
            'comiciosHistoricos',
            'totalElectores',
            'totalVotaron',
            'totalPendientes',
            'porcentajeParticipacion',
            'ultimosVotos',
            'padres',
            'esVentanaLegal'
        ));
    }

    /**
     * Convocar Proceso Electoral Ordinario Bienal (Cada 2 Años)
     * Periodo Legal Obligatorio: 1 de Setiembre al 30 de Noviembre (Ley N.° 28628 Art. 48)
     */
    public function convocarComicio(Request $request)
    {
        $request->validate([
            'año_electoral' => 'required|integer|min:2020|max:2050',
            'nombre_proceso' => 'required|string|max:255',
            'fecha_elecciones' => 'required|date',
            'fecha_corte_padron' => 'nullable|date',
        ]);

        $empresaId = (int) (session('empresa_id') ?? 1);

        // 1. Validar Ventana Legal Obligatoria: 1 de Setiembre al 30 de Noviembre del último año de gestión
        $mes = (int) date('m', strtotime($request->fecha_elecciones));
        if ($mes < 9 || $mes > 11) {
            return back()->withInput()->with('error', 'IMPROCEDENCIA NORMATIVA (Ley N.º 28628 Art. 48 / D.S. N.º 007-2026-MINEDU): Las elecciones generales de APAFA se realizan obligatoriamente durante el periodo comprendido del 1 de setiembre al 30 de noviembre del último año de gestión de la directiva vigente.');
        }

        // 2. Establecer corte de padrón obligatorio al 31 de agosto por normativa
        $fechaCorte = $request->fecha_corte_padron ?: ($request->año_electoral . '-08-31');

        $comicio = ComicioElectoral::create([
            'empresa_id' => $empresaId,
            'año_electoral' => $request->año_electoral,
            'nombre_proceso' => $request->nombre_proceso,
            'fecha_corte_padron' => $fechaCorte,
            'fecha_elecciones' => $request->fecha_elecciones,
            'estado' => 'CONVOCADA',
        ]);

        return redirect()->route('elecciones.index', ['comicio_id' => $comicio->id])
            ->with('success', "¡Proceso Electoral {$request->año_electoral} convocado exitosamente para el {$comicio->bienio_electo}! Conforme a la ventana legal (1 de setiembre al 30 de noviembre).");
    }


    /**
     * Inscribir Lista Electoral de Candidatos a APAFA (Ley N° 28628)
     */
    public function storeLista(Request $request, $comicioId)
    {
        $comicio = ComicioElectoral::findOrFail($comicioId);

        $request->validate([
            'numero_lista' => 'required|integer|min:1',
            'nombre_lista' => 'required|string|max:255',
            'eslogan' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
            'propuestas' => 'nullable|string',
        ]);

        $existe = ListaElectoral::where('comicio_id', $comicio->id)
            ->where('numero_lista', $request->numero_lista)
            ->first();

        if ($existe) {
            return back()->withInput()->with('error', "La Lista N° {$request->numero_lista} ya está inscrita en este proceso electoral.");
        }

        // Validar que no exista otra lista con el mismo nombre en este comicio
        $nombreDuplicado = ListaElectoral::where('comicio_id', $comicio->id)
            ->whereRaw('UPPER(TRIM(nombre_lista)) = ?', [mb_strtoupper(trim($request->nombre_lista))])
            ->first();

        if ($nombreDuplicado) {
            return back()->withInput()->with('error', "Ya existe una lista con el nombre '{$request->nombre_lista}' inscrita como Lista N° {$nombreDuplicado->numero_lista} en este proceso electoral.");
        }

        ListaElectoral::create([
            'comicio_id' => $comicio->id,
            'empresa_id' => $comicio->empresa_id ?? 1,
            'numero_lista' => $request->numero_lista,
            'nombre_lista' => $request->nombre_lista,
            'eslogan' => $request->eslogan,
            'color' => $request->color ?? '#10b981',
            'propuestas' => $request->propuestas,
            'estado' => 'HABILITADA',
        ]);

        return redirect()->route('elecciones.index')->with('success', "¡Lista N° {$request->numero_lista} ('{$request->nombre_lista}') inscrita exitosamente!");
    }

    /**
     * Registrar Candidato en una Lista Electoral para Junta Directiva o Vigilancia
     */
    public function storeCandidato(Request $request, $listaId)
    {
        $lista = ListaElectoral::with('comicio')->findOrFail($listaId);

        $request->validate([
            'padre_familia_id' => 'required|exists:padres_familia,id',
            'cargo_postula' => 'required|string',
            'observaciones' => 'nullable|string',
        ]);

        $padre = PadreFamilia::findOrFail($request->padre_familia_id);

        // Verificar que el padre no postule en otra lista del mismo comicio (Ley 28628)
        $yaEsCandidato = CandidatoElectoral::whereHas('lista', function ($q) use ($lista) {
            $q->where('comicio_id', $lista->comicio_id);
        })->where('padre_familia_id', $padre->id)->first();

        if ($yaEsCandidato) {
            return back()->with('error', "El asociado {$padre->apellidos}, {$padre->nombres} ya está inscrito como candidato en la Lista N° {$yaEsCandidato->lista->numero_lista}.");
        }

        // Verificar si el cargo uninominal ya está ocupado en esta lista
        $cargosUnicos = ['PRESIDENTE', 'VICEPRESIDENTE', 'SECRETARIO', 'TESORERO', 'CONSEJO_VIGILANCIA_PRESIDENTE', 'CONSEJO_VIGILANCIA_SECRETARIO'];
        if (in_array($request->cargo_postula, $cargosUnicos)) {
            $cargoOcupado = CandidatoElectoral::where('lista_electoral_id', $lista->id)
                ->where('cargo_postula', $request->cargo_postula)
                ->first();

            if ($cargoOcupado) {
                return back()->with('error', "La Lista N° {$lista->numero_lista} ya tiene asignado un candidato para el cargo de {$request->cargo_postula}.");
            }
        }

        // 1. Verificación Legal de Requisitos Mínimos (Ley N° 28628 Art. 45 y D.S. N.º 007-2026-MINEDU)
        $verif = $padre->verificarRequisitosApafa();
        if (!$verif['cumple']) {
            return back()->withInput()->with('error', 'REQUISITOS DE ELEGIBILIDAD (Ley N° 28628 Art. 45): ' . implode(' ', $verif['errores']));
        }

        $declaraAntecedentes = $request->boolean('declara_sin_antecedentes', true);
        $declaraNoLabora = $request->boolean('declara_no_labora_ie', true);
        $declaraNoRemovido = $request->boolean('declara_no_removido', true);
        $alDiaPagos = $request->boolean('al_dia_pagos', true);

        if (!$declaraAntecedentes || !$declaraNoLabora || !$declaraNoRemovido) {
            return back()->withInput()->with('error', 'Incompatibilidad legal: El candidato debe acreditar no contar con antecedentes, no laborar en la I.E. y no registrar remoción previa en cargos de APAFA.');
        }

        CandidatoElectoral::create([
            'lista_electoral_id' => $lista->id,
            'padre_familia_id' => $padre->id,
            'cargo_postula' => $request->cargo_postula,
            'cumple_requisitos' => true,
            'al_dia_pagos' => $alDiaPagos,
            'declara_sin_antecedentes' => $declaraAntecedentes,
            'declara_no_labora_ie' => $declaraNoLabora,
            'declara_no_removido' => $declaraNoRemovido,
            'grado_estudiante_valido' => $verif['tiene_grado_valido'],
            'observaciones' => $request->observaciones ?? 'Cumple requisitos de elegibilidad Art. 45 Ley 28628',
        ]);

        return redirect()->route('elecciones.index')->with('success', "Candidato {$padre->apellidos}, {$padre->nombres} verificado e incorporado a la Lista N° {$lista->numero_lista}.");
    }

    /**
     * Ficha Oficial de Idoneidad y Declaración Jurada del Candidato APAFA (Ley N° 28628)
     */
    public function declaracionJurada($candidatoId)
    {
        $candidato = CandidatoElectoral::with(['lista.comicio', 'padreFamilia.estudiantes'])->findOrFail($candidatoId);
        $empresa = Empresa::find(session('empresa_id', 1)) ?? Empresa::first();

        return view('elecciones.declaracion_jurada', compact('candidato', 'empresa'));
    }

    /**
     * Retirar Candidato de la Lista
     */
    public function destroyCandidato($id)
    {
        $candidato = CandidatoElectoral::findOrFail($id);
        $candidato->delete();

        return redirect()->route('elecciones.index')->with('success', 'Candidato retirado de la lista electoral.');
    }

    /**
     * Eliminar Lista Electoral completa
     */
    public function destroyLista($id)
    {
        $lista = ListaElectoral::findOrFail($id);
        $lista->delete();

        return redirect()->route('elecciones.index')->with('success', 'Lista electoral eliminada.');
    }

    /**
     * Cédula Oficial de Sufragio Imprimible (Balota Electoral)
     */
    public function cedulaVotacion($comicioId)
    {
        $comicio = ComicioElectoral::with(['listas.candidatos.padreFamilia'])->findOrFail($comicioId);
        $empresa = Empresa::find(session('empresa_id', 1)) ?? Empresa::first();

        return view('elecciones.cedula_sufragio', compact('comicio', 'empresa'));
    }

    /**
     * Proclamar Lista Ganadora y asumir automáticamente la Directiva APAFA
     */
    public function proclamarGanadora(Request $request, $comicioId, $listaId)
    {
        $comicio = ComicioElectoral::findOrFail($comicioId);
        $lista = ListaElectoral::with('candidatos.padreFamilia')->where('comicio_id', $comicio->id)->findOrFail($listaId);

        // Actualizar estados
        ListaElectoral::where('comicio_id', $comicio->id)->update(['estado' => 'HABILITADA']);
        $lista->update(['estado' => 'GANADORA']);
        $comicio->update(['estado' => 'FINALIZADA']);

        // Asumir automáticamente cargos en la APAFA
        $mapCargos = [
            'PRESIDENTE' => 'Presidente',
            'VICEPRESIDENTE' => 'Vicepresidente',
            'SECRETARIO' => 'Secretario',
            'TESORERO' => 'Tesorero',
            'VOCAL_1' => 'Vocal 1',
            'VOCAL_2' => 'Vocal 2',
            'VOCAL_3' => 'Vocal 3',
            'CONSEJO_VIGILANCIA_PRESIDENTE' => 'Presidente Consejo Vigilancia',
            'CONSEJO_VIGILANCIA_SECRETARIO' => 'Secretario Consejo Vigilancia',
            'CONSEJO_VIGILANCIA_VOCAL' => 'Vocal Consejo Vigilancia',
        ];

        $empresaId = (int) ($comicio->empresa_id ?? 1);

        \Illuminate\Support\Facades\DB::transaction(function () use ($empresaId, $lista, $comicio, $mapCargos) {
            // 1. Conclusión de mandato por Función Cumplida para directivos salientes de la gestión bienal
            $cargoBase = Cargo::firstOrCreate(
                ['empresa_id' => $empresaId, 'nombre' => 'PADRE DE FAMILIA'],
                ['descripcion' => 'Asociado ordinario sin cargo directivo', 'es_oficial' => false]
            );

            $directivosSalientes = PadreFamilia::with('cargo')
                ->where('empresa_id', $empresaId)
                ->whereHas('cargo', function ($q) {
                    $q->where('nombre', '!=', 'PADRE DE FAMILIA');
                })->get();

            $idsNuevos = $lista->candidatos->pluck('padre_familia_id')->toArray();

            foreach ($directivosSalientes as $saliente) {
                if (!in_array($saliente->id, $idsNuevos)) {
                    $fechaHoy = Carbon::now()->toDateString();
                    $yaExiste = \App\Models\MovimientoDirectiva::where('empresa_id', $empresaId)
                        ->whereDate('fecha_vacancia', $fechaHoy)
                        ->where(function($q) use ($saliente) {
                            $q->where('directivo_saliente_dni', $saliente->padre_dni)
                              ->orWhere('directivo_saliente_nombre', $saliente->nombre_completo);
                        })
                        ->exists();

                    if (!$yaExiste) {
                        \App\Models\MovimientoDirectiva::create([
                            'empresa_id' => $empresaId,
                            'cargo_nombre' => $saliente->cargo->nombre ?? 'Directivo',
                            'directivo_saliente_nombre' => $saliente->nombre_completo,
                            'directivo_saliente_dni' => $saliente->padre_dni,
                            'causal_vacancia' => 'FUNCION_CUMPLIDA',
                            'fecha_vacancia' => $fechaHoy,
                            'directivo_entrante_nombre' => 'Directiva Electa ' . $comicio->bienio_electo,
                            'numero_acta_asamblea' => 'ACTA-PROCLAMACION-' . $comicio->año_electoral,
                            'observaciones' => "Conclusión de mandato bienal ({$comicio->bienio_saliente}) según Art. 43 Ley 28628.",
                        ]);
                    }
                    $saliente->update(['cargo_id' => $cargoBase->id]);
                }
            }

            // 2. Asumir automáticamente cargos de la nueva directiva
            foreach ($lista->candidatos as $candidato) {
                $nombreCargo = $mapCargos[$candidato->cargo_postula] ?? $candidato->cargo_postula;
                $cargo = Cargo::firstOrCreate(
                    ['empresa_id' => $empresaId, 'nombre' => $nombreCargo]
                );
                $candidato->padreFamilia->update(['cargo_id' => $cargo->id]);
            }
        });

        return redirect()->route('elecciones.acta_proclamacion', $comicio->id)
            ->with('success', "¡Lista N° {$lista->numero_lista} proclamada ganadora y asumida oficialmente en la Directiva de APAFA! Directiva saliente pasa a condición de Función Cumplida.");
    }

    /**
     * Acta Oficial de Proclamación y Juramentación
     */
    public function actaProclamacion($comicioId)
    {
        $comicio = ComicioElectoral::with(['listas.candidatos.padreFamilia'])->findOrFail($comicioId);
        $listaGanadora = $comicio->listas->where('estado', 'GANADORA')->first() ?? $comicio->listas->first();
        $empresa = Empresa::find(session('empresa_id', 1)) ?? Empresa::first();

        return view('elecciones.acta_proclamacion', compact('comicio', 'listaGanadora', 'empresa'));
    }

    /**
     * Generar o Actualizar el Padrón Electoral Definitivo (Corte al 31 de Agosto)
     */
    public function generarPadron($comicioId)
    {
        $comicio = ComicioElectoral::findOrFail($comicioId);
        $padres = PadreFamilia::all();

        $creados = 0;
        foreach ($padres as $padre) {
            $voto = VotoPadron::firstOrCreate(
                [
                    'comicio_id' => $comicio->id,
                    'padre_familia_id' => $padre->id,
                ],
                [
                    'ha_votado' => false,
                    'mesa_numero' => 'MESA-01',
                ]
            );
            if ($voto->wasRecentlyCreated) {
                $creados++;
            }
        }

        $comicio->update(['estado' => 'PADRON_CERRADO']);

        return redirect()->route('elecciones.index')->with('success', "Padrón electoral oficial cerrado al 31 de agosto. {$creados} asociados incorporados al padrón.");
    }

    /**
     * Padrón Electoral Imprimible para Mesa de Sufragio
     */
    public function imprimirPadron(Request $request, $comicioId)
    {
        $comicio = ComicioElectoral::findOrFail($comicioId);
        $empresa = Empresa::find(session('empresa_id', 1)) ?? Empresa::first();

        $query = VotoPadron::with(['padreFamilia.estudiantes'])
            ->where('comicio_id', $comicio->id)
            ->join('padres_familia', 'votos_padron.padre_familia_id', '=', 'padres_familia.id')
            ->orderBy('padres_familia.padre_ape_pat')
            ->orderBy('padres_familia.padre_nombres')
            ->select('votos_padron.*');

        $votos = $query->get();

        return view('elecciones.padron_imprimible', compact('comicio', 'empresa', 'votos'));
    }

    /**
     * Interfaz de Mesa de Sufragio con Escáner y Registro en Vivo
     */
    public function mesaVotacion($comicioId)
    {
        $comicio = ComicioElectoral::with('listas.candidatos.padreFamilia')->findOrFail($comicioId);
        $empresa = Empresa::find(session('empresa_id', 1)) ?? Empresa::first();

        $totalElectores = VotoPadron::where('comicio_id', $comicio->id)->count();
        $totalVotaron = VotoPadron::where('comicio_id', $comicio->id)->where('ha_votado', true)->count();

        return view('elecciones.mesa', compact('comicio', 'empresa', 'totalElectores', 'totalVotaron'));
    }

    /**
     * Registrar el Voto de un Padre de Familia (Evita doble votación)
     */
    public function registrarVoto(Request $request, $comicioId)
    {
        $comicio = ComicioElectoral::findOrFail($comicioId);

        $queryText = trim($request->input('dni_o_qr', ''));
        $padreId = $request->input('padre_familia_id');
        $listaId = $request->input('lista_electoral_id');
        $tipoVoto = $request->input('tipo_voto', 'VALIDO');

        if (!$padreId && !empty($queryText)) {
            if (preg_match('/APAFA-PADRE-(\d+)/', $queryText, $matches)) {
                $padreId = (int) $matches[1];
            } else {
                $padre = PadreFamilia::where('padre_dni', $queryText)
                    ->orWhere('correlativo', $queryText)
                    ->first();
                if ($padre) {
                    $padreId = $padre->id;
                }
            }
        }

        if (!$padreId) {
            return response()->json(['success' => false, 'message' => 'Asociado no encontrado en el sistema.'], 404);
        }

        $voto = VotoPadron::where('comicio_id', $comicio->id)
            ->where('padre_familia_id', $padreId)
            ->first();

        if (!$voto) {
            $padre = PadreFamilia::findOrFail($padreId);
            $voto = VotoPadron::create([
                'comicio_id' => $comicio->id,
                'padre_familia_id' => $padre->id,
                'ha_votado' => false,
                'mesa_numero' => 'MESA-01',
            ]);
        }

        if ($voto->ha_votado) {
            return response()->json([
                'success' => false,
                'already_voted' => true,
                'message' => "¡ALERTA! El asociado {$voto->padreFamilia->apellidos}, {$voto->padreFamilia->nombres} YA EMITIÓ SU VOTO a las " . Carbon::parse($voto->fecha_hora_voto)->format('H:i:s d/m/Y') . ".",
                'voto' => $voto,
            ], 422);
        }

        $voto->update([
            'ha_votado' => true,
            'fecha_hora_voto' => Carbon::now(),
            'lista_electoral_id' => $listaId ?: null,
            'tipo_voto' => $tipoVoto,
            'observaciones' => $request->input('observaciones', 'Sufragio registrado vía escaneo digital QR/DNI'),
        ]);

        if ($listaId) {
            ListaElectoral::where('id', $listaId)->increment('total_votos');
        }

        $padre = $voto->padreFamilia;

        return response()->json([
            'success' => true,
            'message' => "¡Voto registrado con éxito para {$padre->apellidos}, {$padre->nombres}!",
            'padre' => [
                'id' => $padre->id,
                'nombre_completo' => "{$padre->apellidos}, {$padre->nombres}",
                'dni' => $padre->dni,
                'hora_voto' => Carbon::now()->format('H:i:s'),
            ],
        ]);
    }

    /**
     * Acta de Escrutinio y Cómputo Final para la Dirección y UGEL
     */
    public function actaEscrutinio($comicioId)
    {
        $comicio = ComicioElectoral::with([
            'listas.candidatos.padreFamilia.estudiantes',
        ])->findOrFail($comicioId);

        $empresa = Empresa::find(session('empresa_id', 1)) ?? Empresa::first();

        $totalElectores = VotoPadron::where('comicio_id', $comicio->id)->count();
        $totalVotaron = VotoPadron::where('comicio_id', $comicio->id)->where('ha_votado', true)->count();
        $totalAusentes = max(0, $totalElectores - $totalVotaron);
        $porcentaje = $totalElectores > 0 ? round(($totalVotaron / $totalElectores) * 100, 2) : 0;

        $votosBlancos = VotoPadron::where('comicio_id', $comicio->id)
            ->where('ha_votado', true)
            ->where('tipo_voto', 'BLANCO')
            ->count();

        $votosNulos = VotoPadron::where('comicio_id', $comicio->id)
            ->where('ha_votado', true)
            ->where('tipo_voto', 'NULO')
            ->count();

        $votosValidos = max(0, $totalVotaron - ($votosBlancos + $votosNulos));

        // Determinar lista ganadora: estado GANADORA o por mayor votos registrados
        $listaGanadora = $comicio->listas->where('estado', 'GANADORA')->first()
            ?? $comicio->listas->sortByDesc('total_votos')->first()
            ?? $comicio->listas->first();

        $padres = PadreFamilia::orderBy('padre_ape_pat')->orderBy('padre_nombres')->get();

        return view('elecciones.acta_escrutinio', compact(
            'comicio',
            'empresa',
            'totalElectores',
            'totalVotaron',
            'totalAusentes',
            'porcentaje',
            'votosBlancos',
            'votosNulos',
            'votosValidos',
            'listaGanadora',
            'padres'
        ));
    }

    /**
     * Registrar o Actualizar los integrantes del Comité Electoral del Comicio
     */
    public function updateComiteElectoral(Request $request, $comicioId)
    {
        $comicio = ComicioElectoral::findOrFail($comicioId);

        $request->validate([
            'comite_presidente_nombre' => 'nullable|string|max:255',
            'comite_presidente_dni' => 'nullable|string|max:20',
            'comite_secretario_nombre' => 'nullable|string|max:255',
            'comite_secretario_dni' => 'nullable|string|max:20',
            'comite_vocal_nombre' => 'nullable|string|max:255',
            'comite_vocal_dni' => 'nullable|string|max:20',
        ]);

        $comicio->update([
            'comite_presidente_nombre' => $request->comite_presidente_nombre,
            'comite_presidente_dni' => $request->comite_presidente_dni,
            'comite_secretario_nombre' => $request->comite_secretario_nombre,
            'comite_secretario_dni' => $request->comite_secretario_dni,
            'comite_vocal_nombre' => $request->comite_vocal_nombre,
            'comite_vocal_dni' => $request->comite_vocal_dni,
        ]);

        return back()->with('success', '¡Datos de los miembros del Comité Electoral actualizados correctamente para el acta oficial!');
    }
}

