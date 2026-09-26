@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header con Marco Legal Ley 28628 y D.S. 007-2026-MINEDU -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-300">
                    <i class="bi bi-clock-history mr-1"></i> Renovación Bienal (Cada 2 Años - Ley N.º 28628 Art. 43)
                </span>
                @if($esVentanaLegal)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        <i class="bi bi-shield-check mr-1"></i> Ventana Legal Habilitada (01 Set - 30 Nov)
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                        <i class="bi bi-exclamation-triangle mr-1"></i> Fuera de la Ventana Legal Ordinaria
                    </span>
                @endif
                <span class="text-xs text-slate-500 font-semibold">{{ $comicio->bienio_electo }}</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <i class="bi bi-box-seam text-purple-600"></i> Comicios Electorales de la APAFA
            </h1>
            <p class="text-slate-500 text-xs mt-1">
                Las elecciones se realizan <strong>cada dos (2) años</strong>, específicamente durante el periodo comprendido del <strong>1 de setiembre al 30 de noviembre</strong> del último año de gestión de la directiva vigente (Reglamento de la Ley N.º 28628 y D.S. N.º 007-2026-MINEDU).
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button onclick="document.getElementById('modalConvocarBienal').classList.remove('hidden')" class="px-3.5 py-2.5 bg-purple-700 hover:bg-purple-800 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm">
                <i class="bi bi-calendar2-plus"></i> Convocar Comicio Bienal
            </button>
            <button onclick="document.getElementById('modalNuevaLista').classList.remove('hidden')" class="px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm">
                <i class="bi bi-plus-circle"></i> Inscribir Lista
            </button>
            <a href="{{ route('elecciones.cedula', $comicio->id) }}" target="_blank" class="px-3.5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm">
                <i class="bi bi-file-earmark-check"></i> Cédula
            </a>
            <form action="{{ route('elecciones.generar_padron', $comicio->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                    <i class="bi bi-arrow-repeat"></i> Cerrar Padrón 31/Ago
                </button>
            </form>
            <a href="{{ route('elecciones.imprimir_padron', $comicio->id) }}" target="_blank" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                <i class="bi bi-printer"></i> Padrón
            </a>
            <a href="{{ route('elecciones.acta_escrutinio', $comicio->id) }}" target="_blank" class="px-3.5 py-2.5 bg-purple-100 hover:bg-purple-200 text-purple-900 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 border border-purple-200 shadow-xs">
                <i class="bi bi-file-earmark-bar-graph"></i> Acta de Cómputo
            </a>
            <a href="{{ route('elecciones.mesa', $comicio->id) }}" class="px-3.5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-md">
                <i class="bi bi-qr-code-scan"></i> Mesa QR
            </a>
        </div>
    </div>



    <!-- Métricas Electorales en Tiempo Real -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Padrón Electoral Habilitado</p>
                <h3 class="text-2xl font-extrabold text-slate-800">{{ $totalElectores }}</h3>
                <p class="text-xs text-slate-400 mt-0.5">1 voto por familia asociada</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-check2-circle"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Votos Emitidos</p>
                <h3 class="text-2xl font-extrabold text-emerald-600">{{ $totalVotaron }}</h3>
                <p class="text-xs text-emerald-600 font-medium mt-0.5">Electores que sufragaron</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Pendientes de Votar</p>
                <h3 class="text-2xl font-extrabold text-amber-600">{{ $totalPendientes }}</h3>
                <p class="text-xs text-amber-600 font-medium mt-0.5">Ausentismo actual</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-pie-chart-fill"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Participación</p>
                <h3 class="text-2xl font-extrabold text-blue-600">{{ $porcentajeParticipacion }}%</h3>
                <p class="text-xs text-blue-600 font-medium mt-0.5">Del total del padrón</p>
            </div>
        </div>
    </div>

    <!-- SECCIÓN: LISTAS DE CANDIDATOS A LA DIRECTIVA Y VIGILANCIA DE APAFA -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800">
                        Ley N° 28628 Arts. 45-52
                    </span>
                    <span class="text-xs text-slate-500 font-medium">Requisitos de Idoneidad e Incompatibilidades</span>
                </div>
                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="bi bi-person-lines-fill text-purple-600"></i> Fórmulas y Listas de Candidatos a la Directiva de APAFA
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Postulantes para la Junta Directiva (Presidente, Vicepresidente, Secretario, Tesorero, Vocales) y Consejo de Vigilancia.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <button onclick="document.getElementById('modalNuevaLista').classList.remove('hidden')" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm">
                    <i class="bi bi-plus-lg"></i> Inscribir Lista
                </button>
            </div>
        </div>

        <div class="p-4 space-y-2">
            @forelse($comicio->listas as $listaIndex => $lista)
            <div class="rounded-xl border {{ $lista->estado === 'GANADORA' ? 'border-emerald-300 bg-emerald-50/30' : 'border-slate-200 bg-white' }} overflow-hidden transition-all" id="accordion-lista-{{ $lista->id }}">
                {{-- Fila compacta colapsable --}}
                <div class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-slate-50/80 transition-colors select-none"
                     onclick="toggleAccordionLista({{ $lista->id }})">
                    {{-- Indicador de expansión --}}
                    <i class="bi bi-chevron-right text-slate-400 text-xs transition-transform duration-200" id="chevron-lista-{{ $lista->id }}"></i>

                    {{-- Número de lista --}}
                    <div class="w-9 h-9 rounded-lg bg-purple-900 text-white flex flex-col items-center justify-center font-black flex-shrink-0">
                        <span class="text-[7px] uppercase tracking-tighter leading-none text-purple-300">N°</span>
                        <span class="text-sm leading-none">{{ $lista->numero_lista }}</span>
                    </div>

                    {{-- Nombre y eslogan --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-xs font-extrabold text-slate-800 uppercase truncate">{{ $lista->nombre_lista }}</h3>
                            @if($lista->estado === 'GANADORA')
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full text-[10px] font-extrabold border border-emerald-300 flex-shrink-0">
                                    <i class="bi bi-trophy-fill mr-0.5"></i> GANADORA
                                </span>
                            @else
                                <span class="px-2 py-0.5 bg-purple-50 text-purple-700 rounded-full text-[10px] font-bold border border-purple-200 flex-shrink-0">
                                    {{ $lista->estado }}
                                </span>
                            @endif
                        </div>
                        @if($lista->eslogan)
                            <p class="text-[10px] text-slate-400 italic truncate">"{{ $lista->eslogan }}"</p>
                        @endif
                    </div>

                    {{-- Métricas rápidas --}}
                    <div class="hidden sm:flex items-center gap-3 flex-shrink-0">
                        <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-lg" title="Candidatos inscritos">
                            <i class="bi bi-people-fill text-purple-600 mr-0.5"></i> {{ $lista->candidatos->count() }} candidatos
                        </span>
                        @php
                            $votosLista = $comicio->votos->where('lista_electoral_id', $lista->id)->count();
                        @endphp
                        <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-lg" title="Votos obtenidos">
                            <i class="bi bi-check2-square text-emerald-600 mr-0.5"></i> {{ $votosLista }} votos
                        </span>
                    </div>

                    {{-- Acciones rápidas (sin detener propagación con onclick) --}}
                    <div class="flex items-center gap-1 flex-shrink-0" onclick="event.stopPropagation()">
                        <button onclick="abrirModalCandidato({{ $lista->id }}, {{ $lista->numero_lista }}, '{{ addslashes($lista->nombre_lista) }}')" class="px-2 py-1 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-lg text-[10px] font-bold transition-all flex items-center gap-1 border border-purple-200" title="Agregar Candidato">
                            <i class="bi bi-person-plus-fill"></i> <span class="hidden lg:inline">Candidato</span>
                        </button>

                        @if($lista->estado !== 'GANADORA')
                        <form action="{{ route('elecciones.proclamar', [$comicio->id, $lista->id]) }}" method="POST" 
                              onsubmit="return confirmAction(event, this, '¿Proclamar Lista Ganadora?', '¿Confirma proclamar a la LISTA N° {{ $lista->numero_lista }} como GANADORA y asumir la Directiva de la APAFA?', 'warning', 'Sí, proclamar lista', '#16a34a')">
                            @csrf
                            <button type="submit" class="px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[10px] font-bold transition-all flex items-center gap-1 shadow-xs" title="Proclamar Ganadora">
                                <i class="bi bi-award-fill"></i> <span class="hidden lg:inline">Proclamar</span>
                            </button>
                        </form>
                        @else
                        <a href="{{ route('elecciones.acta_proclamacion', $comicio->id) }}" target="_blank" class="px-2 py-1 bg-emerald-700 text-white rounded-lg text-[10px] font-bold transition-all flex items-center gap-1 shadow-xs" title="Acta de Proclamación">
                            <i class="bi bi-printer"></i> <span class="hidden lg:inline">Acta</span>
                        </a>
                        @endif

                        <form action="{{ route('elecciones.listas.destroy', $lista->id) }}" method="POST" 
                              onsubmit="return confirmDelete(event, this, '¿Eliminar Lista Electoral N° {{ $lista->numero_lista }}?', 'Esta acción no se puede deshacer. Se removerán la lista y sus candidatos postulantes.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1 text-slate-400 hover:text-red-600 rounded transition-all" title="Eliminar lista">
                                <i class="bi bi-trash text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Panel colapsable con candidatos --}}
                <div class="hidden border-t border-slate-100 bg-slate-50/50" id="panel-lista-{{ $lista->id }}">
                    @if($lista->propuestas)
                        <div class="px-4 py-2 border-b border-slate-100 text-[11px] text-slate-500">
                            <i class="bi bi-journal-text text-purple-500 mr-1"></i> <strong>Propuestas:</strong> {{ $lista->propuestas }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="text-slate-400 uppercase text-[10px] font-bold border-b border-slate-100">
                                <tr>
                                    <th class="py-2 px-4">Cargo al que Postula</th>
                                    <th class="py-2 px-3">Candidato(a) / Padre de Familia</th>
                                    <th class="py-2 px-3">DNI</th>
                                    <th class="py-2 px-3">Hijos Matriculados en la I.E.</th>
                                    <th class="py-2 px-3 text-center">Idoneidad</th>
                                    <th class="py-2 px-3 text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($lista->candidatos as $c)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-2.5 px-4">
                                        <span class="font-extrabold text-purple-900 block text-xs">{{ $c->cargo_texto }}</span>
                                        <span class="text-[10px] text-slate-400">{{ str_contains($c->cargo_postula, 'VIGILANCIA') ? 'C. Vigilancia' : 'C. Directivo' }}</span>
                                    </td>
                                    <td class="py-2.5 px-3 font-bold text-slate-800">
                                        {{ $c->padreFamilia->apellidos ?? 'N/A' }}, {{ $c->padreFamilia->nombres ?? '' }}
                                    </td>
                                    <td class="py-2.5 px-3 font-mono text-slate-600 font-bold">
                                        {{ $c->padreFamilia->dni ?? 'N/A' }}
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-600">
                                        @if($c->padreFamilia && $c->padreFamilia->estudiantes && $c->padreFamilia->estudiantes->count() > 0)
                                            @foreach($c->padreFamilia->estudiantes as $est)
                                                <span class="inline-block bg-slate-100 px-1.5 py-0.5 rounded text-[10px] mr-1">
                                                    {{ $est->nombres }} ({{ $est->grado }}° {{ $est->seccion }})
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-slate-400 italic">No registrado</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="bi bi-shield-check"></i> OK
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('elecciones.candidatos.declaracion', $c->id) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 bg-slate-100 hover:bg-purple-600 hover:text-white rounded-lg text-[10px] font-bold text-slate-700 transition-all" title="Declaración Jurada">
                                                <i class="bi bi-file-earmark-text"></i> DJ
                                            </a>
                                            <form action="{{ route('elecciones.candidatos.destroy', $c->id) }}" method="POST" 
                                                  onsubmit="return confirmDelete(event, this, '¿Retirar candidato \'{{ addslashes($c->candidato_nombre) }}\'?', 'El postulante será removido de la lista electoral.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 text-slate-400 hover:text-red-600 text-xs" title="Retirar">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="py-4 text-center text-slate-400 italic text-[11px]">
                                        Sin candidatos inscritos. Presione <strong>"Candidato"</strong> para agregar.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-12 border-2 border-dashed border-slate-200 rounded-2xl">
                <i class="bi bi-person-vcard text-4xl text-slate-300 block mb-2"></i>
                <h3 class="font-bold text-slate-700 text-base">No hay listas electorales inscritas</h3>
                <p class="text-xs text-slate-400 max-w-md mx-auto mt-1 mb-4">
                    Inscriba las listas de postulantes para que los padres de familia puedan emitir su voto y generar la Cédula Oficial de Sufragio.
                </p>
                <button onclick="document.getElementById('modalNuevaLista').classList.remove('hidden')" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 shadow-sm">
                    <i class="bi bi-plus-circle"></i> Inscribir Primera Lista
                </button>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Panel de Cronograma Legal y Últimos Sufragios -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Tarjeta del Cronograma Legal -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <i class="bi bi-clock-history text-purple-600"></i> Cronograma D.S. N.º 007-2026-MINEDU
            </h3>

            <div class="relative pl-6 space-y-5 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200 text-xs">
                <div class="relative">
                    <span class="absolute -left-6 top-1 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-4 ring-emerald-100"></span>
                    <p class="font-bold text-slate-800">1 de Setiembre - 15 de Octubre</p>
                    <p class="text-slate-500">Convocatoria obligatoria a comicios e instalación del Comité Electoral.</p>
                </div>

                <div class="relative">
                    <span class="absolute -left-6 top-1 w-2.5 h-2.5 rounded-full bg-blue-500 ring-4 ring-blue-100"></span>
                    <p class="font-bold text-slate-800">31 de Agosto</p>
                    <p class="text-slate-500">Corte oficial del padrón de asociados con derecho a sufragio.</p>
                </div>

                <div class="relative">
                    <span class="absolute -left-6 top-1 w-2.5 h-2.5 rounded-full bg-purple-500 ring-4 ring-purple-100"></span>
                    <p class="font-bold text-slate-800">Hasta el 30 de Noviembre</p>
                    <p class="text-slate-500">Culminación total del proceso electoral y proclamación de la lista ganadora.</p>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <a href="{{ route('elecciones.acta_escrutinio', $comicio->id) }}" target="_blank" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition-all">
                    <i class="bi bi-file-earmark-check"></i> Generar Acta de Escrutinio Final (UGEL)
                </a>
            </div>
        </div>

        <!-- Tabla de Últimos Sufragios Registrados -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="bi bi-activity text-purple-600"></i> Últimos Votos Registrados en Mesa
                </h3>
                <span class="text-xs font-semibold text-slate-500">Auditoría en Tiempo Real</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3">Asociado / Padre</th>
                            <th class="px-5 py-3">DNI</th>
                            <th class="px-5 py-3">Mesa</th>
                            <th class="px-5 py-3 text-right">Hora Sufragio</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($ultimosVotos as $v)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3 font-semibold text-slate-800">
                                {{ $v->padreFamilia->apellidos ?? 'N/A' }}, {{ $v->padreFamilia->nombres ?? '' }}
                            </td>
                            <td class="px-5 py-3 font-mono text-xs text-slate-600">
                                {{ $v->padreFamilia->dni ?? 'N/A' }}
                            </td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 rounded text-xs bg-slate-100 font-bold text-slate-700">
                                    {{ $v->mesa_numero }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-xs text-emerald-600 font-bold">
                                {{ \Carbon\Carbon::parse($v->fecha_hora_voto)->format('H:i:s d/m') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-slate-400">
                                <i class="bi bi-inbox text-3xl block mb-2 text-slate-300"></i>
                                Aún no se han emitido votos en esta jornada. Inicie el proceso desde la <strong>Mesa de Sufragio QR</strong>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Inscribir Nueva Lista Electoral -->
<div id="modalNuevaLista" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div>
                <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                    <i class="bi bi-flag-fill text-purple-600"></i> Inscribir Lista Electoral
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Comité Electoral APAFA {{ $comicio->año_electoral }}</p>
            </div>
            <button onclick="document.getElementById('modalNuevaLista').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form action="{{ route('elecciones.listas.store', $comicio->id) }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-3 gap-3">
                <div class="col-span-1">
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">N° de Lista *</label>
                    <input type="number" name="numero_lista" required min="1" value="{{ ($comicio->listas->max('numero_lista') ?? 0) + 1 }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-bold text-center">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nombre / Lema de la Lista *</label>
                    <input type="text" name="nombre_lista" required placeholder="Ej: UNIDOS POR EL CAMBIO" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Eslogan de Campaña</label>
                <input type="text" name="eslogan" placeholder="Ej: Por una educación de calidad y cuentas claras" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Propuestas y Plan de Trabajo</label>
                <textarea name="propuestas" rows="3" placeholder="Principales compromisos para la APAFA y los estudiantes..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm"></textarea>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modalNuevaLista').classList.add('hidden')" class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-all">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-purple-600 hover:bg-purple-700 rounded-xl shadow-md transition-all">
                    Inscribir Lista Oficial
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Agregar Candidato a la Lista -->
<div id="modalAgregarCandidato" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div>
                <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                    <i class="bi bi-person-plus-fill text-purple-600"></i> Registrar Candidato
                </h3>
                <p class="text-xs text-slate-500 mt-0.5" id="lblSubtituloCandidato">Inscribir en la Lista</p>
            </div>
            <button onclick="document.getElementById('modalAgregarCandidato').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form id="formAgregarCandidato" action="" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Padre / Madre de Familia *</label>
                <select name="padre_familia_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    <option value="">-- Seleccionar Asociado del Padrón --</option>
                    @foreach($padres as $p)
                    <option value="{{ $p->id }}">{{ $p->apellidos }}, {{ $p->nombres }} (DNI: {{ $p->dni }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Cargo al que Postula *</label>
                <select name="cargo_postula" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    <optgroup label="Consejo Directivo de APAFA (Órgano de Gestión)">
                        <option value="PRESIDENTE">Presidente(a) de la APAFA</option>
                        <option value="VICEPRESIDENTE">Vicepresidente(a)</option>
                        <option value="SECRETARIO">Secretario(a)</option>
                        <option value="TESORERO">Tesorero(a)</option>
                        <option value="VOCAL_1">Primer(a) Vocal</option>
                        <option value="VOCAL_2">Segundo(a) Vocal</option>
                        <option value="VOCAL_3">Tercer(a) Vocal</option>
                    </optgroup>
                    <optgroup label="Consejo de Vigilancia (Órgano de Control)">
                        <option value="CONSEJO_VIGILANCIA_PRESIDENTE">Presidente(a) Consejo de Vigilancia</option>
                        <option value="CONSEJO_VIGILANCIA_SECRETARIO">Secretario(a) Consejo de Vigilancia</option>
                        <option value="CONSEJO_VIGILANCIA_VOCAL">Vocal Consejo de Vigilancia</option>
                    </optgroup>
                </select>
            </div>

            <!-- Cuadro de Requisitos Legales Obligatorios (Ley N° 28628 Art. 45) -->
            <div class="bg-purple-50 p-3.5 rounded-xl border border-purple-200 text-xs space-y-2">
                <span class="font-bold text-purple-950 flex items-center gap-1.5 uppercase text-[11px]">
                    <i class="bi bi-shield-check text-purple-600"></i> Requisitos Mínimos de Idoneidad (Ley 28628 / DRELM / UGEL):
                </span>
                <ul class="text-[11px] text-purple-900 space-y-1 list-disc pl-4">
                    <li><strong>Asociado Activo:</strong> Estudiante matriculado en la I.E. e inscrito en padrón.</li>
                    <li><strong>Aportes al Día:</strong> Cuotas ordinarias de APAFA o faenas comunales compensadas.</li>
                    <li><strong>Grado del Estudiante:</strong> El menor <u>NO debe cursar el último grado</u> de la I.E. (salvo Inicial).</li>
                    <li><strong>Incompatibilidad Laboral:</strong> No laborar ni ser docente o administrativo en la I.E.</li>
                    <li><strong>Antecedentes Limpios:</strong> Sin antecedentes penales, judiciales ni policiales.</li>
                    <li><strong>Sin Sanción:</strong> No haber sido objeto de remoción en cargos anteriores de APAFA.</li>
                </ul>

                <div class="pt-2 border-t border-purple-200 space-y-1.5 text-[11px]">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="checkbox" name="declara_sin_antecedentes" required value="1" checked class="mt-0.5 rounded text-purple-600 focus:ring-purple-500">
                        <span class="text-slate-800">Declara no tener antecedentes penales, judiciales ni policiales.</span>
                    </label>
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="checkbox" name="declara_no_labora_ie" required value="1" checked class="mt-0.5 rounded text-purple-600 focus:ring-purple-500">
                        <span class="text-slate-800">Declara no laborar ni ejercer docencia en este colegio.</span>
                    </label>
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="checkbox" name="declara_no_removido" required value="1" checked class="mt-0.5 rounded text-purple-600 focus:ring-purple-500">
                        <span class="text-slate-800">Declara no haber sido removido ni censurado de cargos previos de APAFA.</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Observaciones / Verificación</label>
                <input type="text" name="observaciones" value="Acreditado conforme al Art. 45 de la Ley N° 28628" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modalAgregarCandidato').classList.add('hidden')" class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-all">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-purple-600 hover:bg-purple-700 rounded-xl shadow-md transition-all">
                    Verificar y Registrar Candidato
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Convocar Comicio Bienal (Cada 2 Años) -->
<div id="modalConvocarBienal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-200 p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="bi bi-calendar2-plus text-purple-600"></i> Convocatoria de Comicios Bienales APAFA
                </h3>
                <p class="text-[11px] text-slate-400">Reglamento Ley N.º 28628 y D.S. N.º 007-2026-MINEDU</p>
            </div>
            <button type="button" onclick="document.getElementById('modalConvocarBienal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg text-lg"></i>
            </button>
        </div>

        <form action="{{ route('elecciones.convocar') }}" method="POST" class="space-y-3.5 text-xs">
            @csrf
            
            <div class="p-3 bg-purple-50 rounded-2xl border border-purple-200 text-purple-900 space-y-1">
                <div class="font-bold flex items-center gap-1.5 text-[11px]">
                    <i class="bi bi-shield-check text-purple-700"></i> MARCO LEGAL OBLIGATORIO:
                </div>
                <p class="text-[11px] leading-relaxed">
                    • <strong>Periodicidad:</strong> Cada <strong>dos (2) años</strong> (mandato de Consejo Directivo y Consejo de Vigilancia).<br>
                    • <strong>Periodo Electoral:</strong> Exclusivamente entre el <strong>1 de setiembre y el 30 de noviembre</strong> del último año de gestión.<br>
                    • <strong>Padrón Electoral:</strong> Cierra el <strong>31 de agosto</strong> del año de la elección.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Año de Elección (Último de gestión) *</label>
                    <input type="number" name="año_electoral" id="inputAñoElectoral" value="{{ date('Y') }}" min="2020" max="2050" required
                           onchange="actualizarFechasRecomendadas(this.value)"
                           class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 font-bold">
                    <span id="lblBienioProyectado" class="text-[10px] text-purple-700 font-semibold block mt-1">
                        Mandato electo: Bienio {{ (int)date('Y') + 1 }} - {{ (int)date('Y') + 2 }}
                    </span>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Fecha de Sufragio (01/Set - 30/Nov) *</label>
                    <input type="date" name="fecha_elecciones" id="inputFechaElecciones"
                           min="{{ date('Y') }}-09-01" max="{{ date('Y') }}-11-30"
                           value="{{ date('Y') }}-10-15" required
                           class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 font-bold">
                    <span class="text-[10px] text-slate-400 block mt-1">Obligatorio entre 01/09 y 30/11</span>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nombre o Denominación Oficial del Proceso *</label>
                <input type="text" name="nombre_proceso" id="inputNombreProceso"
                       value="Elecciones Generales de Consejo Directivo y Consejo de Vigilancia" required
                       class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Fecha de Corte del Padrón Electoral Definitivo</label>
                <input type="date" name="fecha_corte_padron" id="inputFechaCorte"
                       value="{{ date('Y') }}-08-31"
                       class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500">
                <span class="text-[10px] text-slate-400 block mt-1">Normativa: Se fija al 31 de agosto previo.</span>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modalConvocarBienal').classList.add('hidden')" class="px-4 py-2 font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Cancelar</button>
                <button type="submit" class="px-5 py-2 font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-xl shadow-md">
                    <i class="bi bi-check2-circle mr-1"></i> Convocar Elecciones Bienales
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function actualizarFechasRecomendadas(año) {
    const añoNum = parseInt(año) || new Date().getFullYear();
    const inputFecha = document.getElementById('inputFechaElecciones');
    const inputCorte = document.getElementById('inputFechaCorte');
    const lblBienio = document.getElementById('lblBienioProyectado');

    if (inputFecha) {
        inputFecha.min = `${añoNum}-09-01`;
        inputFecha.max = `${añoNum}-11-30`;
        inputFecha.value = `${añoNum}-10-15`;
    }
    if (inputCorte) {
        inputCorte.value = `${añoNum}-08-31`;
    }
    if (lblBienio) {
        lblBienio.textContent = `Mandato electo: Bienio ${añoNum + 1} - ${añoNum + 2}`;
    }
}

function abrirModalCandidato(listaId, numeroLista, nombreLista) {
    const modal = document.getElementById('modalAgregarCandidato');
    const form = document.getElementById('formAgregarCandidato');
    const subtitulo = document.getElementById('lblSubtituloCandidato');

    form.action = `/elecciones/listas/${listaId}/candidatos`;
    subtitulo.textContent = `Inscribir en Lista N° ${numeroLista} - ${nombreLista}`;
    modal.classList.remove('hidden');
}

function toggleAccordionLista(listaId) {
    const panel = document.getElementById('panel-lista-' + listaId);
    const chevron = document.getElementById('chevron-lista-' + listaId);

    if (panel.classList.contains('hidden')) {
        panel.classList.remove('hidden');
        chevron.style.transform = 'rotate(90deg)';
    } else {
        panel.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
    }
}
</script>
@endsection
