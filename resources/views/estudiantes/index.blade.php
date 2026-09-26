@extends('layouts.app')

@section('title', 'Padrón de Alumnos y Estudiantes')

@section('content')
<div class="space-y-6">

    <!-- Encabezado y Acciones -->
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-graduation-cap text-indigo-600"></i>
                Padrón de Alumnos Matriculados
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Control escolar, carga masiva en Excel, corrección/edición de datos, asignación de apoderados y emisión de carnés.
            </p>
        </div>

        <!-- Botones de Acción -->
        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
            <!-- Descargar Plantilla Excel -->
            <a href="{{ route('estudiantes.plantilla.descargar', ['formato' => 'xlsx']) }}" 
               class="flex-1 sm:flex-initial px-3.5 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-all shadow-sm"
               title="Descargar plantilla oficial en formato Microsoft Excel (.xlsx)">
                <i class="fa-solid fa-file-excel text-emerald-600 text-sm"></i>
                <span>Descargar Plantilla Excel</span>
            </a>

            <!-- Botón Carga Masiva -->
            <button type="button" onclick="document.getElementById('modal-carga-masiva').classList.remove('hidden')"
                    class="flex-1 sm:flex-initial px-3.5 py-2.5 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-sm transition-all active:scale-95">
                <i class="fa-solid fa-file-arrow-up text-sm"></i>
                <span>Carga Masiva Excel / CSV</span>
            </button>

            <!-- Botón Modal Nuevo Estudiante -->
            <button onclick="document.getElementById('modal-nuevo-estudiante').classList.remove('hidden')" 
                    class="w-full sm:w-auto px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-sm transition-all">
                <i class="fa-solid fa-user-plus text-sm"></i>
                <span>Matricular Estudiante</span>
            </button>
        </div>
    </div>

    <!-- Advertencias o Alertas de Importación (si existieron) -->
    @if(session('import_warnings') && count(session('import_warnings')) > 0)
        <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-amber-900 text-xs space-y-2">
            <div class="flex items-center gap-2 font-bold text-amber-800">
                <i class="fa-solid fa-triangle-exclamation text-amber-600 text-base"></i>
                <span>Observaciones durante la carga masiva ({{ count(session('import_warnings')) }} avisos):</span>
            </div>
            <ul class="list-disc list-inside space-y-1 text-slate-700 max-h-36 overflow-y-auto pl-2 font-mono text-[11px]">
                @foreach(session('import_warnings') as $warn)
                    <li>{{ $warn }}</li>
                @endforeach
            </ul>
            <p class="text-[11px] text-amber-700 italic">
                * Los alumnos fueron registrados con éxito. Puede asignar a sus respectivos apoderados usando el botón <strong>"Asignar Apoderado"</strong> en cada ficha.
            </p>
        </div>
    @endif

    <!-- Barra de Pestañas Rápidas: Por Nivel y Por Estado de Apoderado -->
    <div class="space-y-2.5">
        <!-- Pestañas por Nivel -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1">
            @php
                $nivelActual = strtolower($nivel ?? '');
                $apoderadoActual = $apoderadoFiltro ?? 'todos';
            @endphp
            <a href="{{ route('estudiantes.index', array_merge(request()->except('nivel', 'page'), ['nivel' => 'todos'])) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ empty($nivelActual) || $nivelActual === 'todos' ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
                <span>Todos los Niveles</span>
                <span class="px-1.5 py-0.5 rounded-lg text-[10px] {{ empty($nivelActual) || $nivelActual === 'todos' ? 'bg-slate-800 text-slate-200' : 'bg-slate-100 text-slate-600' }}">
                    {{ $conteosNivel['todos'] ?? 0 }}
                </span>
            </a>

            <a href="{{ route('estudiantes.index', array_merge(request()->except('nivel', 'page'), ['nivel' => 'Inicial'])) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $nivelActual === 'inicial' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-amber-50 hover:text-amber-700 border border-slate-200/80' }}">
                <i class="fa-solid fa-shapes text-xs"></i>
                <span>Nivel Inicial</span>
                <span class="px-1.5 py-0.5 rounded-lg text-[10px] {{ $nivelActual === 'inicial' ? 'bg-amber-700 text-white' : 'bg-amber-50 text-amber-700' }}">
                    {{ $conteosNivel['inicial'] ?? 0 }}
                </span>
            </a>

            <a href="{{ route('estudiantes.index', array_merge(request()->except('nivel', 'page'), ['nivel' => 'Primaria'])) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $nivelActual === 'primaria' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 border border-slate-200/80' }}">
                <i class="fa-solid fa-book-open text-xs"></i>
                <span>Nivel Primaria</span>
                <span class="px-1.5 py-0.5 rounded-lg text-[10px] {{ $nivelActual === 'primaria' ? 'bg-indigo-700 text-white' : 'bg-indigo-50 text-indigo-700' }}">
                    {{ $conteosNivel['primaria'] ?? 0 }}
                </span>
            </a>

            <a href="{{ route('estudiantes.index', array_merge(request()->except('nivel', 'page'), ['nivel' => 'Secundaria'])) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $nivelActual === 'secundaria' ? 'bg-purple-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-purple-50 hover:text-purple-700 border border-slate-200/80' }}">
                <i class="fa-solid fa-user-graduate text-xs"></i>
                <span>Nivel Secundaria</span>
                <span class="px-1.5 py-0.5 rounded-lg text-[10px] {{ $nivelActual === 'secundaria' ? 'bg-purple-700 text-white' : 'bg-purple-50 text-purple-700' }}">
                    {{ $conteosNivel['secundaria'] ?? 0 }}
                </span>
            </a>
        </div>

        <!-- Pestañas por Estado de Asignación de Apoderado -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <span class="text-slate-400 font-semibold mr-1 text-[11px]">Apoderado:</span>
            <a href="{{ route('estudiantes.index', array_merge(request()->except('apoderado', 'page'), ['apoderado' => 'todos'])) }}" 
               class="px-3 py-1.5 rounded-xl font-bold transition-all flex items-center gap-1.5 {{ $apoderadoActual === 'todos' ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <span>Todos</span>
                <span class="text-[10px] opacity-80">({{ $conteosApoderado['todos'] ?? 0 }})</span>
            </a>

            <a href="{{ route('estudiantes.index', array_merge(request()->except('apoderado', 'page'), ['apoderado' => 'con_apoderado'])) }}" 
               class="px-3 py-1.5 rounded-xl font-bold transition-all flex items-center gap-1.5 {{ $apoderadoActual === 'con_apoderado' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' }}">
                <i class="fa-solid fa-user-check text-[11px]"></i>
                <span>Con Apoderado</span>
                <span class="text-[10px] opacity-80">({{ $conteosApoderado['con_apoderado'] ?? 0 }})</span>
            </a>

            <a href="{{ route('estudiantes.index', array_merge(request()->except('apoderado', 'page'), ['apoderado' => 'sin_apoderado'])) }}" 
               class="px-3 py-1.5 rounded-xl font-bold transition-all flex items-center gap-1.5 {{ $apoderadoActual === 'sin_apoderado' ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200' }}">
                <i class="fa-solid fa-triangle-exclamation text-[11px] {{ ($conteosApoderado['sin_apoderado'] ?? 0) > 0 ? 'text-amber-500' : '' }}"></i>
                <span>Sin Apoderado Asignado</span>
                <span class="px-1.5 py-0.2 rounded-md text-[10px] {{ $apoderadoActual === 'sin_apoderado' ? 'bg-amber-700 text-white' : 'bg-amber-200 text-amber-900 font-extrabold' }}">
                    {{ $conteosApoderado['sin_apoderado'] ?? 0 }}
                </span>
            </a>
        </div>
    </div>

    <!-- Barra de Filtros Combinados (Buscador, Nivel, Grado, Sección) -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form action="{{ route('estudiantes.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 items-center">
            
            @if(!empty($apoderadoFiltro) && $apoderadoFiltro !== 'todos')
                <input type="hidden" name="apoderado" value="{{ $apoderadoFiltro }}">
            @endif

            <!-- Buscador por texto (DNI / Apellidos / Nombres) -->
            <div class="md:col-span-4 relative">
                <input type="text" name="search" value="{{ $search ?? '' }}" 
                       placeholder="Buscar por DNI, nombres o apellidos..." 
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none transition-all">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
            </div>

            <!-- Filtro por Nivel -->
            <div class="md:col-span-2">
                <select name="nivel" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none transition-all">
                    <option value="">Nivel: Todos</option>
                    <option value="Inicial" {{ strtolower($nivel ?? '') === 'inicial' ? 'selected' : '' }}>Inicial</option>
                    <option value="Primaria" {{ strtolower($nivel ?? '') === 'primaria' ? 'selected' : '' }}>Primaria</option>
                    <option value="Secundaria" {{ strtolower($nivel ?? '') === 'secundaria' ? 'selected' : '' }}>Secundaria</option>
                </select>
            </div>

            <!-- Filtro por Grado -->
            <div class="md:col-span-2">
                <select name="grado" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none transition-all">
                    <option value="">Grado: Todos</option>
                    <optgroup label="Inicial">
                        <option value="3 Años" {{ ($grado ?? '') === '3 Años' ? 'selected' : '' }}>3 Años</option>
                        <option value="4 Años" {{ ($grado ?? '') === '4 Años' ? 'selected' : '' }}>4 Años</option>
                        <option value="5 Años" {{ ($grado ?? '') === '5 Años' ? 'selected' : '' }}>5 Años</option>
                    </optgroup>
                    <optgroup label="Primaria / Secundaria">
                        <option value="1ro" {{ ($grado ?? '') === '1ro' ? 'selected' : '' }}>1ro</option>
                        <option value="2do" {{ ($grado ?? '') === '2do' ? 'selected' : '' }}>2do</option>
                        <option value="3ro" {{ ($grado ?? '') === '3ro' ? 'selected' : '' }}>3ro</option>
                        <option value="4to" {{ ($grado ?? '') === '4to' ? 'selected' : '' }}>4to</option>
                        <option value="5to" {{ ($grado ?? '') === '5to' ? 'selected' : '' }}>5to</option>
                        <option value="6to" {{ ($grado ?? '') === '6to' ? 'selected' : '' }}>6to</option>
                    </optgroup>
                </select>
            </div>

            <!-- Filtro por Sección -->
            <div class="md:col-span-2">
                <select name="seccion" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none transition-all">
                    <option value="">Sección: Todas</option>
                    <option value="A" {{ ($seccion ?? '') === 'A' ? 'selected' : '' }}>Sección A</option>
                    <option value="B" {{ ($seccion ?? '') === 'B' ? 'selected' : '' }}>Sección B</option>
                    <option value="C" {{ ($seccion ?? '') === 'C' ? 'selected' : '' }}>Sección C</option>
                    <option value="D" {{ ($seccion ?? '') === 'D' ? 'selected' : '' }}>Sección D</option>
                    <option value="E" {{ ($seccion ?? '') === 'E' ? 'selected' : '' }}>Sección E</option>
                    <option value="Única" {{ ($seccion ?? '') === 'Única' ? 'selected' : '' }}>Sección Única</option>
                </select>
            </div>

            <!-- Botones de Acción -->
            <div class="md:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 px-3 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-all shadow-sm">
                    <i class="fa-solid fa-filter text-[11px]"></i>
                    Filtrar
                </button>
                @if(!empty($search) || (!empty($nivel) && $nivel !== 'todos') || !empty($grado) || !empty($seccion) || (!empty($apoderadoFiltro) && $apoderadoFiltro !== 'todos'))
                    <a href="{{ route('estudiantes.index') }}" title="Limpiar todos los filtros" 
                       class="px-2.5 py-2 bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-500 rounded-xl text-xs font-bold transition-all flex items-center justify-center">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>

        <!-- Indicador de Resultados y Filtros Activos -->
        @if(!empty($search) || (!empty($nivel) && $nivel !== 'todos') || !empty($grado) || !empty($seccion) || (!empty($apoderadoFiltro) && $apoderadoFiltro !== 'todos'))
            <div class="mt-3 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="text-slate-400 font-semibold">Filtros activos:</span>
                    @if(!empty($search))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-indigo-50 text-indigo-700 rounded-lg text-[11px] font-bold border border-indigo-200">
                            Búsqueda: "{{ $search }}"
                            <a href="{{ route('estudiantes.index', request()->except('search', 'page')) }}" class="hover:text-rose-600">&times;</a>
                        </span>
                    @endif
                    @if(!empty($nivel) && $nivel !== 'todos')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-blue-50 text-blue-700 rounded-lg text-[11px] font-bold border border-blue-200">
                            Nivel: {{ ucfirst($nivel) }}
                            <a href="{{ route('estudiantes.index', request()->except('nivel', 'page')) }}" class="hover:text-rose-600">&times;</a>
                        </span>
                    @endif
                    @if(!empty($grado))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-purple-50 text-purple-700 rounded-lg text-[11px] font-bold border border-purple-200">
                            Grado: {{ $grado }}
                            <a href="{{ route('estudiantes.index', request()->except('grado', 'page')) }}" class="hover:text-rose-600">&times;</a>
                        </span>
                    @endif
                    @if(!empty($seccion))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-emerald-50 text-emerald-700 rounded-lg text-[11px] font-bold border border-emerald-200">
                            Sección: "{{ $seccion }}"
                            <a href="{{ route('estudiantes.index', request()->except('seccion', 'page')) }}" class="hover:text-rose-600">&times;</a>
                        </span>
                    @endif
                    @if(!empty($apoderadoFiltro) && $apoderadoFiltro !== 'todos')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 {{ $apoderadoFiltro === 'sin_apoderado' ? 'bg-amber-50 text-amber-800 border-amber-300' : 'bg-emerald-50 text-emerald-700 border-emerald-200' }} rounded-lg text-[11px] font-bold border">
                            Apoderado: {{ $apoderadoFiltro === 'sin_apoderado' ? 'Sin Apoderado' : 'Con Apoderado' }}
                            <a href="{{ route('estudiantes.index', request()->except('apoderado', 'page')) }}" class="hover:text-rose-600">&times;</a>
                        </span>
                    @endif
                </div>

                <div class="text-slate-500 font-medium">
                    Mostrando <strong class="text-slate-800">{{ $estudiantes->total() }}</strong> estudiante(s) encontrado(s)
                </div>
            </div>
        @endif
    </div>

    <!-- Students Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($estudiantes as $est)
            @php
                $nivelEst = strtolower($est->nivel ?? '');
                if ($nivelEst === 'inicial') {
                    $badgeNivelClass = 'bg-amber-50 text-amber-700 border-amber-200';
                    $iconNivel = 'fa-shapes';
                } elseif ($nivelEst === 'secundaria') {
                    $badgeNivelClass = 'bg-purple-50 text-purple-700 border-purple-200';
                    $iconNivel = 'fa-graduation-cap';
                } else {
                    $badgeNivelClass = 'bg-indigo-50 text-indigo-700 border-indigo-200';
                    $iconNivel = 'fa-book-open';
                }

                $tieneApoderado = !empty($est->padre_familia_id) && !empty($est->padreFamilia);
                $estudianteJson = [
                    'id' => $est->id,
                    'dni' => $est->dni,
                    'nombres' => $est->nombres,
                    'ape_pat' => $est->ape_pat,
                    'ape_mat' => $est->ape_mat,
                    'nivel' => $est->nivel,
                    'grado' => $est->grado,
                    'seccion' => $est->seccion,
                    'turno' => $est->turno,
                    'padre_familia_id' => $est->padre_familia_id,
                    'parentesco' => $est->parentesco ?? 'HIJO(A)',
                    'nombre_completo' => $est->nombre_completo,
                ];
            @endphp
            <div class="bg-white rounded-2xl p-4 border {{ $tieneApoderado ? 'border-slate-200/80' : 'border-amber-300 bg-amber-50/20' }} shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
                <div>
                    <div class="flex items-start gap-3.5">
                        <div class="h-10 w-10 rounded-xl {{ $tieneApoderado ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-700' }} flex items-center justify-center text-lg font-bold flex-shrink-0">
                            <i class="fa-solid {{ $iconNivel }} {{ $tieneApoderado ? 'text-indigo-600' : 'text-amber-600' }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-[11px] font-bold px-2 py-0.5 rounded {{ $tieneApoderado ? 'bg-slate-100 text-slate-700' : 'bg-amber-100 text-amber-800' }}">
                                    DNI: {{ $est->dni }}
                                </span>
                                <div class="flex items-center gap-1">
                                    @if(in_array(auth()->user()->rol ?? '', ['ADMIN', 'SECRETARIO']))
                                    <button type="button" 
                                            onclick='abrirModalEditarEstudiante(@json($estudianteJson))'
                                            title="Editar datos del estudiante (DNI, nombres o apellidos)" 
                                            class="text-slate-400 hover:text-indigo-600 p-1 rounded hover:bg-indigo-50 transition-colors">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>
                                    <form action="{{ route('estudiantes.destroy', $est) }}" method="POST" 
                                          onsubmit="return confirmDelete(event, this, '¿Retirar al estudiante \'{{ addslashes($est->nombre_completo) }}\'?', 'Esta acción no se puede deshacer. Se removerá su matrícula y asistencias.');" 
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Retirar estudiante" class="text-slate-400 hover:text-rose-600 p-1 rounded hover:bg-rose-50 transition-colors">
                                            <i class="fa-regular fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                            <h4 class="font-bold text-slate-800 text-xs mt-1.5 truncate">{{ $est->nombre_completo }}</h4>
                            
                            <!-- Grado, Sección y Nivel -->
                            <div class="flex items-center gap-1.5 mt-1 text-[11px] flex-wrap">
                                <span class="font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">
                                    {{ $est->grado }} "{{ $est->seccion }}"
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeNivelClass }}">
                                    {{ $est->nivel }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-medium">
                                    Turno {{ $est->turno }}
                                </span>
                            </div>

                            <!-- Estado de Apoderado / Vínculo Familiar -->
                            @if($tieneApoderado)
                                <div class="text-[11px] text-slate-500 mt-2 flex items-center justify-between gap-1.5 flex-wrap pt-2 border-t border-slate-100">
                                    <span class="flex items-center gap-1 truncate max-w-[170px]" title="{{ $est->padreFamilia->nombre_completo }}">
                                        <i class="fa-solid fa-user-tie text-[10px] text-indigo-600"></i>
                                        <span class="truncate">Apoderado: <b class="text-slate-800">{{ $est->padreFamilia->nombre_completo }}</b></span>
                                    </span>
                                    <div class="flex items-center gap-1">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-violet-50 text-violet-700 border border-violet-200">
                                            {{ $est->parentesco ?? 'HIJO(A)' }}
                                        </span>
                                        <button type="button" 
                                                onclick="abrirModalAsignarApoderado({{ $est->id }}, '{{ addslashes($est->nombre_completo) }}', '{{ $est->dni }}', '{{ $est->parentesco ?? 'HIJO(A)' }}', {{ $est->padre_familia_id }})"
                                                title="Cambiar o reasignar apoderado" 
                                                class="w-5 h-5 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 flex items-center justify-center transition-colors">
                                            <i class="fa-solid fa-user-pen text-[10px]"></i>
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="mt-2.5 pt-2 border-t border-amber-200/80 flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1 text-[11px] text-amber-700 font-bold">
                                        <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                                        <span>Sin Apoderado</span>
                                    </span>
                                    <button type="button" 
                                            onclick="abrirModalAsignarApoderado({{ $est->id }}, '{{ addslashes($est->nombre_completo) }}', '{{ $est->dni }}', '{{ $est->parentesco ?? 'HIJO(A)' }}', null)"
                                            class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-[11px] font-bold rounded-lg shadow-sm flex items-center gap-1 transition-all">
                                        <i class="fa-solid fa-user-plus text-[10px]"></i>
                                        <span>Asignar a su Apoderado</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[10px] font-mono font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">
                        {{ $est->correlativo ?? 'N° 00000 - 2026' }}
                    </span>
                    <div class="flex items-center gap-1.5">
                        @if(in_array(auth()->user()->rol ?? '', ['ADMIN', 'SECRETARIO']))
                        <button type="button"
                                onclick='abrirModalEditarEstudiante(@json($estudianteJson))'
                                class="px-2.5 py-1.5 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-600 text-xs font-bold rounded-xl transition-all flex items-center gap-1"
                                title="Editar datos del estudiante">
                            <i class="fa-solid fa-pen-to-square"></i> Editar
                        </button>
                        @endif
                        <a href="{{ route('carta_poder.estudiante', $est) }}" target="_blank"
                           class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-700 text-xs font-bold rounded-xl transition-all flex items-center gap-1"
                           title="Generar Carta Poder Simple (D.S. N.º 007-2026-MINEDU)">
                            <i class="fa-solid fa-file-signature"></i> Carta Poder
                        </a>
                        <a href="{{ route('estudiantes.tarjeta', $est) }}" target="_blank"
                           class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-600 hover:text-white text-indigo-700 text-xs font-bold rounded-xl transition-all flex items-center gap-1.5">
                            <i class="fa-solid fa-address-card"></i> Tarjeta
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-3xl p-12 text-center border border-slate-200/80 shadow-sm">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl mx-auto mb-3">
                    <i class="fa-solid fa-user-slash"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">No se encontraron estudiantes</h3>
                <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
                    No existen alumnos registrados que coincidan con los criterios de filtro seleccionados.
                </p>
                <div class="mt-4 flex items-center justify-center gap-2 flex-wrap">
                    <a href="{{ route('estudiantes.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all">
                        Limpiar Filtros
                    </a>
                    <button onclick="document.getElementById('modal-carga-masiva').classList.remove('hidden')" 
                            class="px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-xs font-bold transition-all">
                        <i class="fa-solid fa-file-arrow-up mr-1"></i> Carga Masiva Excel
                    </button>
                    <button onclick="document.getElementById('modal-nuevo-estudiante').classList.remove('hidden')" 
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all">
                        Matricular Estudiante
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    @if($estudiantes->hasPages())
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80">
            {{ $estudiantes->links() }}
        </div>
    @endif

</div>

<!-- MODAL 1: Carga Masiva desde Excel / CSV -->
<div id="modal-carga-masiva" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 max-h-[92vh] overflow-y-auto space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-file-arrow-up text-violet-600"></i> Carga Masiva de Alumnos (Excel / CSV)
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Importa la nómina de estudiantes y vincula automáticamente sus apoderados</p>
            </div>
            <button onclick="document.getElementById('modal-carga-masiva').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Descarga de Plantillas -->
        <div class="p-3.5 bg-emerald-50 rounded-2xl border border-emerald-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2 text-emerald-800 font-bold text-xs">
                    <i class="fa-solid fa-file-excel text-emerald-600 text-base"></i>
                    <span>¿No tienes el formato oficial de columnas?</span>
                </div>
                <p class="text-[11px] text-emerald-700 mt-0.5">Descarga la plantilla estructurada con ejemplos listos para rellenar.</p>
            </div>
            <div class="flex items-center gap-1.5 w-full sm:w-auto">
                <a href="{{ route('estudiantes.plantilla.descargar', ['formato' => 'xlsx']) }}" 
                   class="flex-1 sm:flex-initial px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm text-center">
                    <i class="fa-solid fa-download mr-1"></i> Excel (.xlsx)
                </a>
                <a href="{{ route('estudiantes.plantilla.descargar', ['formato' => 'csv']) }}" 
                   class="flex-1 sm:flex-initial px-3 py-1.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 rounded-xl text-xs font-bold transition-all text-center">
                    CSV (.csv)
                </a>
            </div>
        </div>

        <form action="{{ route('estudiantes.importar') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf

            <!-- Selector de Archivo -->
            <div>
                <label class="block font-bold text-slate-700 mb-1.5">Seleccionar Archivo Excel o CSV *</label>
                <div class="border-2 border-dashed border-slate-300 hover:border-violet-500 rounded-2xl p-5 text-center transition-colors bg-slate-50/70">
                    <i class="fa-solid fa-cloud-arrow-up text-violet-500 text-2xl mb-2 block"></i>
                    <input type="file" name="archivo_excel" id="input_archivo_excel" accept=".xlsx,.xlsm,.csv,.txt" required
                           class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100 cursor-pointer">
                    <p class="text-[11px] text-slate-400 mt-2">Formatos admitidos: Microsoft Excel (.xlsx), CSV delimitado por comas o punto y coma (.csv). Máx. 10MB.</p>
                </div>
            </div>

            <!-- Valores por defecto si no vienen en el archivo -->
            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 space-y-2.5">
                <label class="font-bold text-slate-700 text-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-sliders text-indigo-600"></i> Valores por defecto (si la fila en Excel no especifica grado o nivel):
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <div>
                        <label class="block text-[10px] text-slate-500 font-semibold mb-0.5">Nivel</label>
                        <select name="nivel_defecto" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs bg-white">
                            <option value="Primaria" selected>Primaria</option>
                            <option value="Inicial">Inicial</option>
                            <option value="Secundaria">Secundaria</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] text-slate-500 font-semibold mb-0.5">Grado</label>
                        <select name="grado_defecto" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs bg-white">
                            <option value="1ro" selected>1ro</option>
                            <option value="2do">2do</option>
                            <option value="3ro">3ro</option>
                            <option value="4to">4to</option>
                            <option value="5to">5to</option>
                            <option value="6to">6to</option>
                            <option value="3 Años">3 Años</option>
                            <option value="4 Años">4 Años</option>
                            <option value="5 Años">5 Años</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] text-slate-500 font-semibold mb-0.5">Sección</label>
                        <input type="text" name="seccion_defecto" value="A" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs bg-white font-bold uppercase">
                    </div>
                    <div>
                        <label class="block text-[10px] text-slate-500 font-semibold mb-0.5">Turno</label>
                        <select name="turno_defecto" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs bg-white">
                            <option value="Mañana" selected>Mañana</option>
                            <option value="Tarde">Tarde</option>
                            <option value="Completo">Completo</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Información de Columnas -->
            <div class="text-[11px] text-slate-500 space-y-1 bg-indigo-50/70 p-3 rounded-xl border border-indigo-100">
                <div class="font-bold text-indigo-900 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-info text-indigo-600"></i>
                    <span>Estructura de columnas reconocidas:</span>
                </div>
                <p><code>DNI</code> (8 dígitos) | <code>NOMBRES</code> | <code>APELLIDO_PATERNO</code> | <code>APELLIDO_MATERNO</code> | <code>NIVEL</code> | <code>GRADO</code> | <code>SECCION</code> | <code>TURNO</code> | <code>DNI_APODERADO</code> (opcional) | <code>PARENTESCO</code> (opcional).</p>
                <p class="text-indigo-800">
                    * Si incluye la columna <strong>DNI_APODERADO</strong> y el padre ya está registrado en el sistema, se vinculará de inmediato. Si no está registrado o se deja en blanco, el estudiante se creará pendiente de asignar su apoderado.
                </p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-carga-masiva').classList.add('hidden')" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl font-semibold">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white rounded-xl font-bold shadow-md shadow-violet-600/30 flex items-center gap-1.5">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    Procesar e Importar Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: Asignar / Reasignar Apoderado al Estudiante -->
<div id="modal-asignar-apoderado" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-user-tie text-indigo-600"></i> Asignar Apoderado al Estudiante
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Vincular al menor con un padre o tutor del padrón de asociados</p>
            </div>
            <button onclick="document.getElementById('modal-asignar-apoderado').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Ficha del Estudiante -->
        <div class="p-3 bg-indigo-50/70 border border-indigo-100 rounded-2xl flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-lg font-bold flex-shrink-0">
                <i class="fa-solid fa-child"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[10px] uppercase tracking-wider font-extrabold text-indigo-700">Estudiante a Vincular</span>
                <h4 id="modal_asignar_nombre_estudiante" class="font-black text-slate-900 text-sm truncate">--</h4>
                <p class="text-xs text-slate-500 font-mono">DNI: <span id="modal_asignar_dni_estudiante">--</span></p>
            </div>
        </div>

        <form id="form-asignar-apoderado" method="POST" action="" class="space-y-4 text-xs">
            @csrf

            <!-- Selector de Apoderado con Búsqueda Rápida -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="font-semibold text-slate-700">Seleccionar Apoderado (Padrón APAFA) *</label>
                    <label class="flex items-center gap-1 cursor-pointer text-[11px] text-slate-500">
                        <input type="checkbox" id="chk_desvincular_apoderado" onchange="toggleDesvincularApoderado(this.checked)" class="rounded text-rose-600">
                        <span>Dejar sin apoderado</span>
                    </label>
                </div>

                <input type="text" id="filtro_padres_input" oninput="filtrarOpcionesPadres(this.value)" 
                       placeholder="Escriba para filtrar por DNI o Apellidos..." 
                       class="w-full px-3 py-1.5 mb-1.5 border border-slate-200 rounded-xl text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500">

                <select name="padre_familia_id" id="select_padre_asignar" size="6" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white text-xs">
                    <option value="">-- Seleccione un apoderado del padrón --</option>
                    @foreach($padres as $p)
                        <option value="{{ $p->id }}" data-search="{{ mb_strtolower($p->padre_dni . ' ' . $p->nombre_completo, 'UTF-8') }}">
                            {{ $p->padre_dni }} - {{ $p->nombre_completo }} {{ $p->cargo && $p->cargo->nombre !== 'PADRE DE FAMILIA' ? '[' . $p->cargo->nombre . ']' : '' }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[10px] text-slate-400 mt-1">Haga clic sobre el asociado en la lista para seleccionarlo.</p>
            </div>

            <!-- Vínculo / Parentesco -->
            <div id="seccion_parentesco">
                <label class="block font-semibold text-slate-700 mb-1">Vínculo con el Menor *</label>
                <select name="parentesco" id="select_parentesco_asignar" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white font-medium">
                    <option value="HIJO(A)">Hijo / Hija</option>
                    <option value="TUTOR / APODERADO LEGAL">Tutor / Apoderado Legal</option>
                    <option value="NIETO(A)">Nieto / Nieta</option>
                    <option value="SOBRINO(A)">Sobrino / Sobrina</option>
                    <option value="HERMANO(A) MAYOR">Hermano(a) Mayor</option>
                    <option value="AHIJADO(A)">Ahijado / Ahijada</option>
                    <option value="OTRO FAMILIAR">Otro Familiar</option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-asignar-apoderado').classList.add('hidden')" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl font-semibold">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-md shadow-indigo-600/30 flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i>
                    Guardar Asignación
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: Matrícula Manual de Estudiante -->
<div id="modal-nuevo-estudiante" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <div>
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-graduation-cap text-indigo-600"></i> Matricular y Emitir Tarjeta de Asistencia
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Asignación de menor a un apoderado asociado a la APAFA</p>
            </div>
            <button onclick="document.getElementById('modal-nuevo-estudiante').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('estudiantes.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            
            <!-- Apoderado y Vínculo -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Apoderado Asociado (Opcional)</label>
                    <select name="padre_familia_id" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="">-- Sin apoderado por ahora (asignar luego) --</option>
                        @foreach($padres as $p)
                            <option value="{{ $p->id }}">{{ $p->padre_dni }} - {{ $p->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Vínculo con Apoderado</label>
                    <select name="parentesco" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white font-medium">
                        <option value="HIJO(A)" selected>Hijo / Hija</option>
                        <option value="TUTOR / APODERADO LEGAL">Tutor / Apoderado Legal</option>
                        <option value="NIETO(A)">Nieto / Nieta</option>
                        <option value="SOBRINO(A)">Sobrino / Sobrina</option>
                        <option value="HERMANO(A) MENOR">Hermano(a) Menor</option>
                        <option value="AHIJADO(A)">Ahijado / Ahijada</option>
                    </select>
                </div>
            </div>

            <!-- Datos del Estudiante -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">DNI del Alumno *</label>
                    <input type="text" name="dni" maxlength="8" pattern="[0-9]{8}" required placeholder="8 dígitos" 
                           class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono">
                </div>
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1">Nombres del Alumno *</label>
                    <input type="text" name="nombres" required placeholder="Ej. Mateo Gabriel" 
                           class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 uppercase">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Apellido Paterno *</label>
                    <input type="text" name="ape_pat" required placeholder="Primer apellido" 
                           class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 uppercase">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Apellido Materno</label>
                    <input type="text" name="ape_mat" placeholder="Segundo apellido" 
                           class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 uppercase">
                </div>
            </div>

            <!-- Nivel Educativo y Turno -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nivel Educativo *</label>
                    <select name="nivel" id="modal-select-nivel" onchange="actualizarGradosModal()" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="Inicial">Inicial</option>
                        <option value="Primaria" selected>Primaria</option>
                        <option value="Secundaria">Secundaria</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Turno *</label>
                    <select name="turno" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="Mañana" selected>Mañana</option>
                        <option value="Tarde">Tarde</option>
                        <option value="Noche">Noche</option>
                    </select>
                </div>
            </div>

            <!-- Grado y Sección -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Grado *</label>
                    <select name="grado" id="modal-select-grado" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="1ro">1ro</option>
                        <option value="2do">2do</option>
                        <option value="3ro" selected>3ro</option>
                        <option value="4to">4to</option>
                        <option value="5to">5to</option>
                        <option value="6to">6to</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Sección *</label>
                    <select name="seccion" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="A" selected>Sección A</option>
                        <option value="B">Sección B</option>
                        <option value="C">Sección C</option>
                        <option value="D">Sección D</option>
                        <option value="E">Sección E</option>
                        <option value="Única">Sección Única</option>
                    </select>
                </div>
            </div>

            <div class="p-3 bg-indigo-50 rounded-xl border border-indigo-100 text-indigo-900 text-[11px] flex items-center gap-2">
                <i class="fa-solid fa-address-card text-indigo-600 text-base"></i>
                <p>Al guardar, el sistema generará automáticamente la <b>Tarjeta de Asistencia Escolar</b> del alumno con su Código QR y Código de Barras para control de ingreso.</p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-nuevo-estudiante').classList.add('hidden')" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl font-semibold">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-md shadow-indigo-600/30">
                    Guardar y Emitir Tarjeta
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 4: Editar Datos Personales y Académicos del Estudiante -->
<div id="modal-editar-estudiante" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 max-h-[92vh] overflow-y-auto space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-user-pen text-indigo-600"></i> Modificar Datos del Estudiante
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Corrige el DNI, nombres, apellidos o grado en caso de error o confusión</p>
            </div>
            <button onclick="document.getElementById('modal-editar-estudiante').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="form-editar-estudiante" method="POST" action="" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <!-- DNI y Nombres -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">DNI del Alumno *</label>
                    <input type="text" name="dni" id="editar_dni" maxlength="8" pattern="[0-9]{8}" required 
                           placeholder="8 dígitos" 
                           class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono font-bold bg-amber-50/50">
                    <span class="text-[10px] text-amber-700 font-semibold mt-0.5 block">Corregir si hubo error</span>
                </div>
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1">Nombres del Alumno *</label>
                    <input type="text" name="nombres" id="editar_nombres" required 
                           placeholder="Nombres completos" 
                           class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 uppercase font-semibold">
                </div>
            </div>

            <!-- Apellidos -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Apellido Paterno *</label>
                    <input type="text" name="ape_pat" id="editar_ape_pat" required 
                           placeholder="Primer apellido" 
                           class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 uppercase font-semibold">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Apellido Materno</label>
                    <input type="text" name="ape_mat" id="editar_ape_mat" 
                           placeholder="Segundo apellido" 
                           class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 uppercase font-semibold">
                </div>
            </div>

            <!-- Nivel Educativo y Turno -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nivel Educativo *</label>
                    <select name="nivel" id="editar_nivel" onchange="actualizarGradosModalEditar()" required 
                            class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white font-medium">
                        <option value="Inicial">Inicial</option>
                        <option value="Primaria">Primaria</option>
                        <option value="Secundaria">Secundaria</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Turno *</label>
                    <select name="turno" id="editar_turno" required 
                            class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="Mañana">Mañana</option>
                        <option value="Tarde">Tarde</option>
                        <option value="Completo">Completo</option>
                        <option value="Noche">Noche</option>
                    </select>
                </div>
            </div>

            <!-- Grado y Sección -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Grado *</label>
                    <select name="grado" id="editar_grado" required 
                            class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white font-medium">
                        <option value="1ro">1ro</option>
                        <option value="2do">2do</option>
                        <option value="3ro">3ro</option>
                        <option value="4to">4to</option>
                        <option value="5to">5to</option>
                        <option value="6to">6to</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Sección *</label>
                    <select name="seccion" id="editar_seccion" required 
                            class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white font-medium">
                        <option value="A">Sección A</option>
                        <option value="B">Sección B</option>
                        <option value="C">Sección C</option>
                        <option value="D">Sección D</option>
                        <option value="E">Sección E</option>
                        <option value="Única">Sección Única</option>
                    </select>
                </div>
            </div>

            <!-- Apoderado Asociado -->
            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 space-y-2.5">
                <label class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-user-tie text-indigo-600"></i> Apoderado Asociado (Opcional):
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[10px] text-slate-500 font-semibold mb-0.5">Apoderado</label>
                        <select name="padre_familia_id" id="editar_padre_familia_id" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs bg-white">
                            <option value="">-- Sin apoderado (desvincular) --</option>
                            @foreach($padres as $p)
                                <option value="{{ $p->id }}">{{ $p->padre_dni }} - {{ $p->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] text-slate-500 font-semibold mb-0.5">Parentesco / Vínculo</label>
                        <select name="parentesco" id="editar_parentesco" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs bg-white font-medium">
                            <option value="HIJO(A)">Hijo / Hija</option>
                            <option value="TUTOR / APODERADO LEGAL">Tutor / Apoderado Legal</option>
                            <option value="NIETO(A)">Nieto / Nieta</option>
                            <option value="SOBRINO(A)">Sobrino / Sobrina</option>
                            <option value="HERMANO(A) MAYOR">Hermano(a) Mayor</option>
                            <option value="AHIJADO(A)">Ahijado / Ahijada</option>
                            <option value="OTRO FAMILIAR">Otro Familiar</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-editar-estudiante').classList.add('hidden')" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl font-semibold">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-md shadow-indigo-600/30 flex items-center gap-1.5">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function actualizarGradosModal() {
        const nivel = document.getElementById('modal-select-nivel').value;
        const gradoSelect = document.getElementById('modal-select-grado');
        gradoSelect.innerHTML = '';

        let opciones = [];
        if (nivel === 'Inicial') {
            opciones = ['3 Años', '4 Años', '5 Años'];
        } else if (nivel === 'Secundaria') {
            opciones = ['1ro', '2do', '3ro', '4to', '5to'];
        } else {
            opciones = ['1ro', '2do', '3ro', '4to', '5to', '6to'];
        }

        opciones.forEach((opc, idx) => {
            const opt = document.createElement('option');
            opt.value = opc;
            opt.textContent = opc;
            if (idx === 0) opt.selected = true;
            gradoSelect.appendChild(opt);
        });
    }

    function abrirModalEditarEstudiante(est) {
        const form = document.getElementById('form-editar-estudiante');
        form.action = `/estudiantes/${est.id}`;

        document.getElementById('editar_dni').value = est.dni || '';
        document.getElementById('editar_nombres').value = est.nombres || '';
        document.getElementById('editar_ape_pat').value = est.ape_pat || '';
        document.getElementById('editar_ape_mat').value = est.ape_mat || '';

        const nivelSelect = document.getElementById('editar_nivel');
        nivelSelect.value = est.nivel || 'Primaria';
        actualizarGradosModalEditar(est.grado);

        document.getElementById('editar_seccion').value = est.seccion || 'A';
        document.getElementById('editar_turno').value = est.turno || 'Mañana';

        const padreSelect = document.getElementById('editar_padre_familia_id');
        if (est.padre_familia_id) {
            padreSelect.value = String(est.padre_familia_id);
        } else {
            padreSelect.value = '';
        }

        const parentescoSelect = document.getElementById('editar_parentesco');
        if (est.parentesco) {
            parentescoSelect.value = est.parentesco;
        } else {
            parentescoSelect.value = 'HIJO(A)';
        }

        document.getElementById('modal-editar-estudiante').classList.remove('hidden');
    }

    function actualizarGradosModalEditar(gradoSeleccionado = null) {
        const nivel = document.getElementById('editar_nivel').value;
        const gradoSelect = document.getElementById('editar_grado');
        gradoSelect.innerHTML = '';

        let opciones = [];
        if (nivel === 'Inicial') {
            opciones = ['3 Años', '4 Años', '5 Años'];
        } else if (nivel === 'Secundaria') {
            opciones = ['1ro', '2do', '3ro', '4to', '5to'];
        } else {
            opciones = ['1ro', '2do', '3ro', '4to', '5to', '6to'];
        }

        opciones.forEach((opc) => {
            const opt = document.createElement('option');
            opt.value = opc;
            opt.textContent = opc;
            if (gradoSeleccionado && gradoSeleccionado.toLowerCase() === opc.toLowerCase()) {
                opt.selected = true;
            }
            gradoSelect.appendChild(opt);
        });

        if (!gradoSelect.value && opciones.length > 0) {
            gradoSelect.value = opciones[0];
        }
    }

    function abrirModalAsignarApoderado(estudianteId, nombreEstudiante, dniEstudiante, parentescoActual, padreIdActual) {
        document.getElementById('modal_asignar_nombre_estudiante').textContent = nombreEstudiante;
        document.getElementById('modal_asignar_dni_estudiante').textContent = dniEstudiante;

        const form = document.getElementById('form-asignar-apoderado');
        form.action = `/estudiantes/${estudianteId}/asignar-apoderado`;

        const selectPadre = document.getElementById('select_padre_asignar');
        const selectParentesco = document.getElementById('select_parentesco_asignar');
        const chkDesvincular = document.getElementById('chk_desvincular_apoderado');
        const inputFiltro = document.getElementById('filtro_padres_input');

        if (inputFiltro) inputFiltro.value = '';
        filtrarOpcionesPadres('');

        if (padreIdActual) {
            selectPadre.value = String(padreIdActual);
            chkDesvincular.checked = false;
            selectPadre.disabled = false;
        } else {
            selectPadre.value = '';
            chkDesvincular.checked = false;
            selectPadre.disabled = false;
        }

        if (parentescoActual && selectParentesco) {
            selectParentesco.value = parentescoActual;
        }

        document.getElementById('modal-asignar-apoderado').classList.remove('hidden');
    }

    function toggleDesvincularApoderado(checked) {
        const selectPadre = document.getElementById('select_padre_asignar');
        const seccionParentesco = document.getElementById('seccion_parentesco');
        if (checked) {
            selectPadre.value = '';
            selectPadre.disabled = true;
            if (seccionParentesco) seccionParentesco.classList.add('opacity-50');
        } else {
            selectPadre.disabled = false;
            if (seccionParentesco) seccionParentesco.classList.remove('opacity-50');
        }
    }

    function filtrarOpcionesPadres(filtro) {
        filtro = filtro.trim().toLowerCase();
        const select = document.getElementById('select_padre_asignar');
        if (!select) return;

        const options = select.querySelectorAll('option');
        options.forEach(opt => {
            if (!opt.value) return; // Mantener opción por defecto
            const searchData = (opt.getAttribute('data-search') || opt.textContent).toLowerCase();
            if (!filtro || searchData.includes(filtro)) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        });
    }
</script>
@endsection
