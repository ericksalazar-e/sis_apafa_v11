@extends('layouts.app')

@section('title', 'Configuración Institucional y Comité de APAFA')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-sliders text-indigo-600"></i>
                Configuración del Sistema y Comité de APAFA
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Identidad institucional, logo oficial, conformación de la Directiva y asignación de responsabilidades con usuarios y contraseñas.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold flex items-center gap-2 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>{{ $empresa->nombre ?? 'I.E. Activa' }}</span>
            </span>
        </div>
    </div>

    @php
        $activeTab = request()->get('tab', 'institucion');
    @endphp

    <!-- Pestañas de Navegación -->
    <div class="border-b border-slate-200">
        <nav class="flex space-x-4 sm:space-x-8 overflow-x-auto" aria-label="Tabs">
            <button onclick="cambiarTab('institucion')" id="tab-btn-institucion"
                    class="py-3 px-1 border-b-2 text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap {{ $activeTab === 'institucion' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                <i class="fa-solid fa-school text-sm"></i>
                <span>1. Institución y Logo Oficial</span>
            </button>

            <button onclick="cambiarTab('comite')" id="tab-btn-comite"
                    class="py-3 px-1 border-b-2 text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap {{ $activeTab === 'comite' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                <i class="fa-solid fa-users-gear text-sm"></i>
                <span>2. Consejo Directivo y Comité de Vigilancia</span>
            </button>

            <button onclick="cambiarTab('usuarios')" id="tab-btn-usuarios"
                    class="py-3 px-1 border-b-2 text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap {{ $activeTab === 'usuarios' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                <i class="fa-solid fa-user-shield text-sm"></i>
                <span>3. Responsabilidades, Usuarios y Contraseñas</span>
            </button>
        </nav>
    </div>

    <!-- ==================== SECCIÓN 1: INSTITUCIÓN Y LOGO ==================== -->
    <div id="tab-content-institucion" class="{{ $activeTab === 'institucion' ? '' : 'hidden' }} space-y-6">
        <form action="{{ route('configuracion.institucion.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Columna Izquierda: Carga de Logo Institucional -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm flex flex-col items-center text-center space-y-4">
                    <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-image text-indigo-600"></i> Logo Oficial de la I.E.
                    </h3>
                    <p class="text-xs text-slate-400">
                        Este logotipo aparecerá automáticamente en las tarjetas de APAFA (CR-80), carnés de estudiantes y la Landing Page.
                    </p>

                    <!-- Previsualizador de Logo -->
                    <div class="w-36 h-36 rounded-3xl bg-slate-50 border-2 border-dashed border-slate-300 flex items-center justify-center p-3 relative overflow-hidden shadow-inner group">
                        @if(!empty($empresa->foto_institucion))
                            <img id="logo-preview" src="{{ asset($empresa->foto_institucion) }}" alt="Logo Institución" class="max-h-full max-w-full object-contain">
                        @else
                            <div id="logo-placeholder" class="flex flex-col items-center justify-center text-slate-400">
                                <i class="fa-solid fa-graduation-cap text-4xl text-indigo-400 mb-2"></i>
                                <span class="text-[10px] font-bold uppercase tracking-wider">Sin Logo</span>
                            </div>
                            <img id="logo-preview" src="#" alt="Vista previa" class="max-h-full max-w-full object-contain hidden">
                        @endif
                    </div>

                    <!-- Input de Archivo -->
                    <div class="w-full">
                        <label class="block cursor-pointer">
                            <span class="sr-only">Seleccionar Logo</span>
                            <input type="file" name="foto_institucion" id="input-logo" accept="image/*" onchange="previewLogo(event)"
                                   class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                        </label>
                        <p class="text-[10px] text-slate-400 mt-1">Formatos: PNG, JPG, WEBP o SVG (Máx. 2MB). Fondo transparente recomendado.</p>
                    </div>

                    <!-- Logo UGEL Secundario (Opcional) -->
                    <div class="w-full pt-4 border-t border-slate-100">
                        <label class="block font-semibold text-slate-700 text-xs mb-1 text-left">Insignia UGEL / Regional (Opcional)</label>
                        <input type="file" name="foto_ugel" accept="image/*"
                               class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                    </div>
                </div>

                <!-- Columna Central y Derecha: Formulario de Datos -->
                <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm space-y-4">
                    <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2 border-b border-slate-100 pb-3">
                        <i class="fa-solid fa-building-columns text-indigo-600"></i> Datos Generales de la Institución
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-slate-700 text-xs mb-1">Nombre Oficial de la Institución Educativa *</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $empresa->nombre) }}" required 
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none uppercase">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 text-xs mb-1">Código Modular de la I.E.</label>
                            <input type="text" name="cod_modular" value="{{ old('cod_modular', $empresa->cod_modular) }}" placeholder="Ej. 0345678"
                                   class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 text-xs mb-1">RUC de la Institución / APAFA</label>
                            <input type="text" name="ruc" maxlength="11" value="{{ old('ruc', $empresa->ruc) }}" placeholder="11 dígitos"
                                   class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 text-xs mb-1">Periodo de Gestión de APAFA</label>
                            <input type="text" name="periodo_apafa" value="{{ old('periodo_apafa', $empresa->periodo_apafa ?? '2025 - 2026') }}" placeholder="Ej. 2025 - 2026"
                                   class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 text-xs mb-1">Niveles que Atiende</label>
                            <input type="text" name="nivel_educativo" value="{{ old('nivel_educativo', $empresa->nivel_educativo ?? 'Inicial, Primaria y Secundaria') }}" placeholder="Ej. Primaria y Secundaria"
                                   class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 text-xs mb-1">Teléfono o Celular de Contacto</label>
                            <input type="text" name="telefono" value="{{ old('telefono', $empresa->telefono) }}" placeholder="Ej. 987654321 / (01) 456-7890"
                                   class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 text-xs mb-1">Correo Electrónico Institucional</label>
                            <input type="email" name="correo" value="{{ old('correo', $empresa->correo) }}" placeholder="Ej. contacto@ienerygarcia.edu.pe"
                                   class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-slate-700 text-xs mb-1">Dirección / Ubicación Geográfica</label>
                            <input type="text" name="ubicacion" value="{{ old('ubicacion', $empresa->ubicacion) }}" placeholder="Ej. Av. Los Próceres N° 450, Distrito, Región"
                                   class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-slate-700 text-xs mb-1">Lema Institucional o Encabezado de Carnés</label>
                            <input type="text" name="lema" value="{{ old('lema', $empresa->lema) }}" placeholder="Ej. 'Disciplina, Estudio y Superación' - Gestión APAFA Transparente"
                                   class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-slate-700 text-xs mb-1">Director(a) de la Institución Educativa</label>
                            <input type="text" name="director_nombre" value="{{ old('director_nombre', $empresa->director_nombre) }}" placeholder="Ej. Mg. Carlos Alberto Sánchez Flores"
                                   class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs uppercase focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-md shadow-indigo-600/30 transition-all">
                            <i class="fa-solid fa-floppy-disk"></i> Guardar Configuración Institucional
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- ==================== SECCIÓN 2: DIRECTIVA Y COMITÉ DE APAFA ==================== -->
    <div id="tab-content-comite" class="{{ $activeTab === 'comite' ? '' : 'hidden' }} space-y-6">
        
        <!-- Alerta Informativa Ley 28628 -->
        <div class="p-4 bg-indigo-50/70 border border-indigo-200 rounded-2xl flex items-start gap-3.5 text-xs text-indigo-900">
            <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-bold flex-shrink-0 mt-0.5">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
            <div>
                <h4 class="font-extrabold text-indigo-950 mb-0.5">Estructura Orgánica de APAFA (Ley N° 28628)</h4>
                <p class="text-indigo-800 leading-relaxed">
                    El <strong>Consejo Directivo</strong> (Presidente, Vicepresidente, Secretario, Tesorero y Vocales) es el órgano de gestión y administración, y el <strong>Consejo de Vigilancia</strong> es el órgano de control y fiscalización, ambos elegidos democráticamente por la Asamblea General mediante sufragio universal para un mandato de dos (2) años.
                </p>
            </div>
        </div>

        <!-- Resumen Estatutario del Consejo Directivo y Consejo de Vigilancia (Ley 28628) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            <!-- Consejo Directivo: Órgano de Gestión -->
            <div class="lg:col-span-8 bg-white rounded-3xl p-5 border border-indigo-100 shadow-sm space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">
                            <i class="fa-solid fa-users-gear"></i>
                        </span>
                        <div>
                            <h4 class="font-extrabold text-slate-900 text-xs">Consejo Directivo (Órgano de Gestión y Ejecución)</h4>
                            <span class="text-[10px] text-slate-400">Elegidos democráticamente &bull; Mandato 2 años (Ley 28628 Art. 43)</span>
                        </div>
                    </div>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-bold border border-indigo-200">
                        {{ $miembrosConsejoDirectivo->count() }} directivo(s) activos
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-center text-xs">
                    @php
                        $estatutariosGestion = [
                            ['cargo' => 'PRESIDENTE', 'label' => 'Presidente', 'icono' => 'fa-user-tie', 'color' => 'indigo'],
                            ['cargo' => 'VICEPRESIDENTE', 'label' => 'Vicepresidente', 'icono' => 'fa-user-shield', 'color' => 'blue'],
                            ['cargo' => 'SECRETARIO', 'label' => 'Secretario(a)', 'icono' => 'fa-file-signature', 'color' => 'sky'],
                            ['cargo' => 'TESORERO', 'label' => 'Tesorero(a)', 'icono' => 'fa-coins', 'color' => 'emerald'],
                            ['cargo' => 'VOCAL', 'label' => 'Vocales (1-3)', 'icono' => 'fa-users', 'color' => 'amber'],
                        ];
                    @endphp

                    @foreach($estatutariosGestion as $eg)
                        @php
                            $ocupante = $miembrosConsejoDirectivo->first(function($m) use ($eg) {
                                $nom = mb_strtoupper($m->cargo->nombre ?? '');
                                if (str_contains($nom, 'VIGILANCIA')) return false;
                                return str_contains($nom, $eg['cargo']);
                            });
                        @endphp
                        <div class="p-2.5 rounded-2xl border {{ $ocupante ? 'bg-slate-50 border-slate-200' : 'bg-amber-50/50 border-amber-200 border-dashed' }} flex flex-col justify-between">
                            <div>
                                <i class="fa-solid {{ $eg['icono'] }} text-{{ $eg['color'] }}-600 text-sm mb-1"></i>
                                <span class="block font-bold text-slate-800 text-[11px] truncate">{{ $eg['label'] }}</span>
                            </div>
                            <div class="mt-1.5">
                                @if($ocupante)
                                    <span class="text-[10px] font-extrabold text-slate-900 block truncate" title="{{ $ocupante->nombre_completo }}">
                                        {{ Str::limit($ocupante->padre_ape_pat . ' ' . $ocupante->padre_nombres, 14) }}
                                    </span>
                                    <span class="text-[9px] text-emerald-700 font-bold block">
                                        <i class="fa-solid fa-circle-check"></i> Activo
                                    </span>
                                @else
                                    <span class="text-[10px] text-amber-700 font-semibold block">Por asignar</span>
                                    <span class="text-[9px] text-slate-400 block font-mono">Vacante</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Consejo de Vigilancia: Órgano de Control -->
            <div class="lg:col-span-4 bg-white rounded-3xl p-5 border border-rose-100 shadow-sm space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center text-xs font-bold">
                            <i class="fa-solid fa-shield-halved"></i>
                        </span>
                        <div>
                            <h4 class="font-extrabold text-slate-900 text-xs">Consejo de Vigilancia</h4>
                            <span class="text-[10px] text-slate-400">Fiscalización &bull; Control interno</span>
                        </div>
                    </div>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 font-bold border border-rose-200">
                        {{ $miembrosConsejoVigilancia->count() }} fiscal(es)
                    </span>
                </div>

                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                    @php
                        $estatutariosVig = [
                            ['label' => 'Presidente', 'filtro' => 'PRESIDENTE'],
                            ['label' => 'Secretario', 'filtro' => 'SECRETARIO'],
                            ['label' => 'Vocal', 'filtro' => 'VOCAL'],
                        ];
                    @endphp
                    @foreach($estatutariosVig as $ev)
                        @php
                            $ocupanteVig = $miembrosConsejoVigilancia->first(function($m) use ($ev) {
                                return str_contains(mb_strtoupper($m->cargo->nombre ?? ''), $ev['filtro']);
                            });
                        @endphp
                        <div class="p-2 rounded-2xl border {{ $ocupanteVig ? 'bg-slate-50 border-slate-200' : 'bg-rose-50/40 border-rose-200 border-dashed' }} flex flex-col justify-between">
                            <span class="font-bold text-slate-800 text-[10px] truncate">{{ $ev['label'] }}</span>
                            <div class="mt-1">
                                @if($ocupanteVig)
                                    <span class="text-[9px] font-extrabold text-slate-900 block truncate" title="{{ $ocupanteVig->nombre_completo }}">
                                        {{ Str::limit($ocupanteVig->padre_ape_pat, 10) }}
                                    </span>
                                    <span class="text-[9px] text-rose-600 font-bold block">Auditor</span>
                                @else
                                    <span class="text-[9px] text-rose-500 block">Vacante</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Asignación Rápida de Cargo de Directiva -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm space-y-4">
            <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2 border-b border-slate-100 pb-3">
                <i class="fa-solid fa-user-plus text-indigo-600"></i> Asignar Cargo de Directiva a un Asociado
            </h3>

            <form action="{{ route('configuracion.comite.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end text-xs">
                @csrf
                <div class="sm:col-span-6">
                    <label class="block font-semibold text-slate-700 mb-1">Padre / Madre / Apoderado Asociado *</label>
                    <select name="padre_familia_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                        <option value="">Seleccione asociado del padrón...</option>
                        @foreach($padres as $p)
                            <option value="{{ $p->id }}">
                                {{ $p->padre_dni }} - {{ $p->nombre_completo }} (Actual: {{ $p->cargo->nombre ?? 'PADRE DE FAMILIA' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-4">
                    <label class="block font-semibold text-slate-700 mb-1">Cargo a Asignar en la APAFA *</label>
                    <select name="cargo_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:bg-white font-medium">
                        @foreach($cargos as $c)
                            <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <button type="submit" class="w-full px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold flex items-center justify-center gap-2 transition-all shadow-sm">
                        <i class="fa-solid fa-check"></i> Asignar
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabla de Miembros del Consejo Directivo y Comité de Vigilancia -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-users text-indigo-600"></i> Miembros Actuales de la Directiva y Vigilancia
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Asociados que ostentan cargos directivos vigentes en el periodo {{ $empresa->periodo_apafa ?? '2025-2026' }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="document.getElementById('modalVacanciaDirectiva').classList.remove('hidden')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                        <i class="fa-solid fa-user-pen"></i> Vacancia / Reemplazo D.S. 007
                    </button>
                    <a href="{{ route('configuracion.credencial_directiva') }}" target="_blank" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-award"></i> Credencial UGEL
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-400 text-[11px] font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-6">Directivo / Asociado</th>
                            <th class="py-3 px-4">Cargo Oficial APAFA</th>
                            <th class="py-3 px-4">DNI / Contacto</th>
                            <th class="py-3 px-4">Estudiantes a Cargo</th>
                            <th class="py-3 px-6 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($miembrosDirectiva as $m)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="font-bold text-slate-900 text-xs">{{ $m->nombre_completo }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $m->correlativo }}</div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $m->cargo && $m->cargo->isConsejoVigilancia() ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-indigo-50 text-indigo-700 border-indigo-200' }} border">
                                        <i class="{{ $m->cargo ? $m->cargo->icono : 'fa-solid fa-id-badge' }} text-[10px]"></i>
                                        {{ $m->cargo->nombre ?? 'DIRECTIVO' }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5 font-medium">
                                        {{ $m->cargo ? $m->cargo->organo : '' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="font-mono font-bold text-slate-700">DNI: {{ $m->padre_dni }}</div>
                                    <div class="text-[11px] text-slate-400"><i class="fa-solid fa-phone text-[10px]"></i> {{ $m->celular ?? 'Sin cel.' }}</div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="text-xs text-slate-700 font-semibold">{{ $m->estudiantes->count() }} estudiante(s)</span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button"
                                                onclick="prepararRelevo('{{ addslashes($m->cargo->nombre ?? 'Directivo') }}', '{{ addslashes($m->nombre_completo) }}', '{{ $m->padre_dni }}')"
                                                class="px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-1 shadow-xs"
                                                title="Registrar Función Cumplida, Relevo o Vacancia">
                                            <i class="fa-solid fa-user-clock text-amber-600"></i> Relevo / Cese
                                        </button>
                                        <a href="{{ route('padres.tarjeta', $m) }}" target="_blank"
                                           class="px-2.5 py-1.5 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-600 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-1">
                                            <i class="fa-solid fa-address-card"></i> Carné
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">
                                    No hay asociados asignados a cargos directivos aún. Use el selector superior para designar al Presidente, Secretario, Tesorero, etc.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Historial de Movimientos y Asunción de Suplentes (Ley 28628 / D.S. N.º 007-2026-MINEDU) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-arrows-rotate text-indigo-600"></i> Historial de Recomposición, Relevos y Función Cumplida
                    </h3>
                    <p class="text-xs text-slate-400">Trazabilidad oficial de ceses y asunciones de mandato (Ley 28628 Art. 36 y 43)</p>
                </div>
                <button type="button" onclick="prepararRelevo('', '', '')" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 border border-indigo-200">
                    <i class="fa-solid fa-plus-circle"></i> Nuevo Relevo / Cese
                </button>
            </div>

            <div class="space-y-2.5 text-xs">
                @forelse($movimientos as $mov)
                <div class="p-3.5 bg-slate-50 hover:bg-slate-100/70 rounded-2xl border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition-colors">
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-bold text-slate-900 bg-white px-2.5 py-0.5 rounded-lg border border-slate-200 text-xs">
                                {{ $mov->cargo_nombre }}
                            </span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $mov->causal_badge }}">
                                <i class="fa-solid {{ $mov->causal_icono }} text-[10px]"></i>
                                {{ $mov->causal_label }}
                            </span>
                            @if($mov->numero_acta_asamblea)
                                <span class="text-[10px] text-slate-500 font-mono bg-slate-200/70 px-2 py-0.5 rounded">
                                    Acta: {{ $mov->numero_acta_asamblea }}
                                </span>
                            @endif
                        </div>
                        <div class="text-slate-600 text-xs">
                            Directivo saliente: <strong>{{ $mov->directivo_saliente_nombre }}</strong> (DNI: {{ $mov->directivo_saliente_dni ?? 'S/D' }}).
                            @if($mov->directivo_entrante_nombre && !str_contains($mov->directivo_entrante_nombre, 'VACANTE'))
                                <span class="text-emerald-700 font-semibold block sm:inline"> &bull; Asume: <strong>{{ $mov->directivo_entrante_nombre }}</strong> (DNI: {{ $mov->directivo_entrante_dni ?? 'S/D' }})</span>
                            @else
                                <span class="text-amber-700 font-semibold block sm:inline"> &bull; Condición: <em>Vacante por designar en asamblea</em></span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2 sm:self-center">
                        <div class="text-[11px] text-slate-400 font-mono whitespace-nowrap text-right">
                            <i class="fa-regular fa-calendar mr-1"></i>{{ \Carbon\Carbon::parse($mov->fecha_vacancia)->format('d/m/Y') }}
                        </div>
                        @if(in_array(auth()->user()->rol ?? '', ['ADMIN', 'SECRETARIO']))
                        <form action="{{ route('configuracion.movimientos.destroy', $mov->id) }}" method="POST" 
                              onsubmit="return confirmDelete(event, this, '¿Eliminar registro de relevo / cese?', 'Se removerá del historial oficial de recomposición el movimiento del directivo {{ addslashes($mov->directivo_saliente_nombre) }}.');" 
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Eliminar registro de relevo" class="w-6 h-6 rounded-lg text-slate-300 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition-all ml-1">
                                <i class="fa-solid fa-trash-can text-[11px]"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-slate-400 text-xs italic py-4 text-center">
                    No se han registrado vacancias ni reemplazos en la directiva. La gestión se mantiene sin alteraciones.
                </p>
                @endforelse
            </div>
        </div>

        <!-- Modal Registrar Vacancia, Relevo o Función Cumplida -->
        <div id="modalVacanciaDirectiva" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm hidden p-4">
            <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-200 p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-user-pen text-indigo-600"></i> Relevo, Vacancia o Función Cumplida
                        </h3>
                        <p class="text-[11px] text-slate-400">Reglamento Ley N.º 28628 y D.S. N.º 007-2026-MINEDU</p>
                    </div>
                    <button type="button" onclick="document.getElementById('modalVacanciaDirectiva').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('configuracion.movimientos.store') }}" method="POST" class="space-y-3.5 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Cargo a Modificar / Relevar *</label>
                        <select name="cargo_nombre" id="relevo_cargo" required class="w-full px-3 py-2 border border-slate-200 rounded-xl font-bold bg-slate-50">
                            @foreach($cargos as $c)
                            <option value="{{ $c->nombre }}">{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Directivo Saliente *</label>
                            <input type="text" name="directivo_saliente_nombre" id="relevo_saliente_nombre" required 
                                   oninput="verificarFechaSaliente()" 
                                   placeholder="Apellidos y Nombres" class="w-full px-3 py-2 border border-slate-200 rounded-xl">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">DNI Saliente *</label>
                            <input type="text" name="directivo_saliente_dni" id="relevo_saliente_dni" required 
                                   oninput="verificarFechaSaliente()" 
                                   placeholder="8 dígitos" class="w-full px-3 py-2 border border-slate-200 rounded-xl font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Causal Legal de Cese / Vacancia *</label>
                        <select name="causal_vacancia" id="relevo_causal" required class="w-full px-3 py-2 border border-slate-200 rounded-xl font-bold bg-white focus:ring-2 focus:ring-indigo-500">
                            <option value="FUNCION_CUMPLIDA">Función Cumplida (Conclusión de Mandato Bienal - 2 Años)</option>
                            <option value="NO_ASUME_CARGO">No Desea Asumir Funciones / Desistimiento Voluntario</option>
                            <option value="RENUNCIA_VOLUNTARIA">Renuncia formal voluntaria aceptada</option>
                            <option value="INASISTENCIA_3_ASAMBLEAS">Inasistencia injustificada a 3 Asambleas Consecutivas</option>
                            <option value="EGRESO_DEL_HIJO">Egreso / Retiro del último hijo de la I.E.</option>
                            <option value="FALLECIMIENTO_O_INCAPACIDAD">Fallecimiento o incapacidad sobrevenida</option>
                            <option value="DESTITUCION_POR_ASAMBLEA">Destitución acordada por Asamblea General</option>
                        </select>
                    </div>

                    <!-- Directivo Entrante (Opcional si es Función Cumplida o Vacante) -->
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <label class="font-bold text-slate-800 text-[11px] uppercase tracking-wider">Directivo Entrante / Sucesor</label>
                            <label class="flex items-center gap-1.5 cursor-pointer text-[11px] text-slate-600">
                                <input type="checkbox" id="chkSinEntrante" onchange="toggleSinEntrante(this.checked)" class="rounded text-indigo-600">
                                <span>Dejar en vacancia temporal</span>
                            </label>
                        </div>

                        <div>
                            <label class="block text-[11px] text-slate-500 mb-1">Seleccionar desde el padrón de asociados (Opcional):</label>
                            <select id="select_padre_entrante" onchange="seleccionarPadreEntrante(this)" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs bg-white">
                                <option value="">Seleccione si asumirá un padre asociado...</option>
                                @foreach($padres as $p)
                                    <option value="{{ $p->padre_dni }}" data-nombre="{{ $p->nombre_completo }}">
                                        {{ $p->padre_dni }} - {{ $p->nombre_completo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3" id="seccion_inputs_entrante">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Nombre Entrante</label>
                                <input type="text" name="directivo_entrante_nombre" id="relevo_entrante_nombre" placeholder="Nombre completo" class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-white">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">DNI Entrante</label>
                                <input type="text" name="directivo_entrante_dni" id="relevo_entrante_dni" placeholder="8 dígitos" class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-white font-mono">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Fecha de Efectividad *</label>
                            <input type="date" name="fecha_vacancia" id="relevo_fecha" value="{{ date('Y-m-d') }}" required 
                                   onchange="verificarFechaSaliente()" oninput="verificarFechaSaliente()"
                                   class="w-full px-3 py-2 border border-slate-200 rounded-xl font-bold">
                            <p id="alerta-saliente-fecha-duplicada" class="text-[11px] text-rose-600 font-bold mt-1.5 hidden flex items-center gap-1">
                                <i class="fa-solid fa-triangle-exclamation"></i> Ya existe un relevo registrado para este directivo en esta fecha.
                            </p>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">N° Acta de Asamblea</label>
                            <input type="text" name="numero_acta_asamblea" placeholder="Ej: ACTA-004-2026" class="w-full px-3 py-2 border border-slate-200 rounded-xl">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Observaciones / Sustento Legal</label>
                        <input type="text" name="observaciones" placeholder="Ej: Cumplimiento de funciones de mandato bienal conforme a Ley 28628." class="w-full px-3 py-2 border border-slate-200 rounded-xl">
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" onclick="document.getElementById('modalVacanciaDirectiva').classList.add('hidden')" class="px-4 py-2 font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Cancelar</button>
                        <button type="submit" id="btn-registrar-relevo" class="px-5 py-2 font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md">
                            <i class="fa-solid fa-check mr-1"></i> Registrar Relevo / Cese
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- ==================== SECCIÓN 3: USUARIOS, CREDENCIALES Y RESPONSABILIDADES ==================== -->
    <div id="tab-content-usuarios" class="{{ $activeTab === 'usuarios' ? '' : 'hidden' }} space-y-6">
        
        <!-- Header de Usuarios y Botón Crear -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                    <i class="fa-solid fa-user-lock text-indigo-600"></i> Usuarios del Sistema y Roles Operativos
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">
                    Definición de cuentas de acceso con contraseñas seguras y permisos delimitados según funciones de APAFA.
                </p>
            </div>

            <button onclick="document.getElementById('modal-nuevo-usuario').classList.remove('hidden')" 
                    class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm transition-all">
                <i class="fa-solid fa-user-plus"></i> Crear Nuevo Usuario
            </button>
        </div>

        <!-- Guía de Responsabilidades por Rol -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($rolesDisponibles as $rKey => $rInfo)
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border {{ $rInfo['badge'] }}">
                                {{ $rKey }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-mono">
                                {{ $usuarios->where('rol', $rKey)->count() }} usuario(s)
                            </span>
                        </div>
                        <h4 class="font-bold text-slate-800 text-xs mb-1">{{ $rInfo['nombre'] }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $rInfo['descripcion'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Tabla de Usuarios Registrados -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-users text-indigo-600"></i> Listado de Cuentas de Acceso ({{ $usuarios->count() }})
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-400 text-[11px] font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-6">Usuario / Nombre</th>
                            <th class="py-3 px-4">Correo Electrónico</th>
                            <th class="py-3 px-4">Rol & Responsabilidad</th>
                            <th class="py-3 px-4">Cargo Asignado</th>
                            <th class="py-3 px-4 text-center">Estado</th>
                            <th class="py-3 px-6 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($usuarios as $u)
                            @php
                                $rData = $rolesDisponibles[$u->rol] ?? [
                                    'nombre' => $u->rol,
                                    'badge' => 'bg-slate-100 text-slate-700 border-slate-200',
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold text-xs flex-shrink-0 border border-indigo-100">
                                            {{ substr($u->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-xs">{{ $u->name }}</div>
                                            <div class="text-[11px] font-mono text-indigo-600 font-semibold">{{ $u->username }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 font-mono text-slate-700 text-xs">
                                    {{ $u->email }}
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $rData['badge'] }}">
                                        {{ $rData['nombre'] }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-slate-600 text-xs">
                                    {{ $u->cargo_titulo ?? '—' }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if($u->estado)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                            Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button onclick="abrirModalEditarUsuario({{ json_encode($u) }})" title="Editar usuario y clave"
                                                class="px-2.5 py-1.5 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-600 rounded-lg text-xs font-bold transition-all flex items-center gap-1">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                            <span>Editar</span>
                                        </button>

                                        @if(auth()->id() !== $u->id)
                                            <form action="{{ route('configuracion.usuarios.destroy', $u) }}" method="POST" 
                                                  onsubmit="return confirmDelete(event, this, '¿Eliminar usuario \'{{ addslashes($u->name) }}\'?', 'Esta acción no se puede deshacer y el usuario perderá acceso al sistema de forma inmediata.');" 
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Eliminar usuario" class="p-1.5 text-slate-400 hover:text-rose-600 transition-colors">
                                                    <i class="fa-regular fa-trash-can text-xs"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- ==================== MODAL: CREAR NUEVO USUARIO ==================== -->
<div id="modal-nuevo-usuario" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <div>
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-indigo-600"></i> Crear Cuenta de Usuario
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Asignación de credenciales y permisos por rol</p>
            </div>
            <button onclick="document.getElementById('modal-nuevo-usuario').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('configuracion.usuarios.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nombre Completo del Responsable *</label>
                <input type="text" name="name" required placeholder="Ej. Prof. Juan Manuel Pérez Flores"
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Usuario de Acceso (Username) *</label>
                    <input type="text" name="username" required placeholder="Ej. tesorero2026"
                           class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none lowercase">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Correo Electrónico *</label>
                    <input type="email" name="email" required placeholder="Ej. tesorero@apafa.pe"
                           class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Responsabilidad / Rol *</label>
                    <select name="rol" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:bg-white font-medium">
                        <option value="ADMIN">Presidente / Administrador</option>
                        <option value="SECRETARIO">Secretario de Directiva</option>
                        <option value="TESORERO" selected>Tesorero de Directiva</option>
                        <option value="VOCAL_OPERADOR">Vocal / Operador Portería</option>
                        <option value="COMITE_VIGILANCIA">Comité de Vigilancia</option>
                        <option value="DIRECTOR">Director de la I.E.</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Cargo Personalizado / Título</label>
                    <input type="text" name="cargo_titulo" placeholder="Ej. Tesorero General APAFA 2026"
                           class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Contraseña *</label>
                    <div class="relative">
                        <input type="password" name="password" id="pass-nuevo" required minlength="6" placeholder="Mínimo 6 caracteres"
                               class="w-full px-3.5 py-2 pr-10 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none">
                        <button type="button" onclick="togglePass('pass-nuevo')" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600">
                            <i class="fa-regular fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Confirmar Contraseña *</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="pass-nuevo-conf" required minlength="6" placeholder="Repita contraseña"
                               class="w-full px-3.5 py-2 pr-10 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none">
                        <button type="button" onclick="togglePass('pass-nuevo-conf')" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600">
                            <i class="fa-regular fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-nuevo-usuario').classList.add('hidden')" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl font-semibold">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-md shadow-indigo-600/30">
                    Crear Usuario
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL: EDITAR USUARIO ==================== -->
<div id="modal-editar-usuario" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <div>
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-user-pen text-indigo-600"></i> Editar Usuario y Credenciales
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Modificación de rol, estado y reseteo de contraseña</p>
            </div>
            <button onclick="document.getElementById('modal-editar-usuario').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="form-editar-usuario" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nombre Completo *</label>
                <input type="text" name="name" id="edit-name" required
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Username *</label>
                    <input type="text" name="username" id="edit-username" required
                           class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none lowercase">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Correo Electrónico *</label>
                    <input type="email" name="email" id="edit-email" required
                           class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Responsabilidad / Rol *</label>
                    <select name="rol" id="edit-rol" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:bg-white font-medium">
                        <option value="ADMIN">Presidente / Administrador</option>
                        <option value="SECRETARIO">Secretario de Directiva</option>
                        <option value="TESORERO">Tesorero de Directiva</option>
                        <option value="VOCAL_OPERADOR">Vocal / Operador Portería</option>
                        <option value="COMITE_VIGILANCIA">Comité de Vigilancia</option>
                        <option value="DIRECTOR">Director de la I.E.</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Estado de la Cuenta *</label>
                    <select name="estado" id="edit-estado" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:bg-white font-medium">
                        <option value="1">Activo (Permite login)</option>
                        <option value="0">Inactivo (Acceso bloqueado)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Cargo Personalizado / Título</label>
                <input type="text" name="cargo_titulo" id="edit-cargo-titulo" placeholder="Ej. Tesorero General 2026"
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none">
            </div>

            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 text-[11px]">
                <p><strong>Cambiar Contraseña (Opcional):</strong> Deje este campo en blanco si desea conservar la contraseña actual del usuario.</p>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nueva Contraseña (Opcional)</label>
                <input type="password" name="password" minlength="6" placeholder="Escriba nueva clave solo si desea cambiarla..."
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-editar-usuario').classList.add('hidden')" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl font-semibold">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-md shadow-indigo-600/30">
                    Actualizar Usuario
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function cambiarTab(tabId) {
        // Ocultar todos los contenidos
        document.getElementById('tab-content-institucion').classList.add('hidden');
        document.getElementById('tab-content-comite').classList.add('hidden');
        document.getElementById('tab-content-usuarios').classList.add('hidden');

        // Resetear estilos de tabs
        ['institucion', 'comite', 'usuarios'].forEach(t => {
            const btn = document.getElementById('tab-btn-' + t);
            btn.classList.remove('border-indigo-600', 'text-indigo-600');
            btn.classList.add('border-transparent', 'text-slate-500');
        });

        // Mostrar tab seleccionado
        document.getElementById('tab-content-' + tabId).classList.remove('hidden');
        const activeBtn = document.getElementById('tab-btn-' + tabId);
        activeBtn.classList.remove('border-transparent', 'text-slate-500');
        activeBtn.classList.add('border-indigo-600', 'text-indigo-600');

        // Actualizar URL sin recargar
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.replaceState({}, '', url);
    }

    function previewLogo(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('logo-preview');
                const placeholder = document.getElementById('logo-placeholder');
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if (placeholder) {
                    placeholder.classList.add('hidden');
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function togglePass(inputId) {
        const input = document.getElementById(inputId);
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    function abrirModalEditarUsuario(user) {
        const form = document.getElementById('form-editar-usuario');
        form.action = '/configuracion/usuarios/' + user.id;

        document.getElementById('edit-name').value = user.name;
        document.getElementById('edit-username').value = user.username;
        document.getElementById('edit-email').value = user.email;
        document.getElementById('edit-rol').value = user.rol;
        document.getElementById('edit-cargo-titulo').value = user.cargo_titulo || '';
        document.getElementById('edit-estado').value = user.estado ? '1' : '0';

        document.getElementById('modal-editar-usuario').classList.remove('hidden');
    }

    function prepararRelevo(cargo, nombre, dni) {
        if (cargo) {
            const selectCargo = document.getElementById('relevo_cargo');
            for (let i = 0; i < selectCargo.options.length; i++) {
                if (selectCargo.options[i].value === cargo || selectCargo.options[i].text.includes(cargo)) {
                    selectCargo.selectedIndex = i;
                    break;
                }
            }
        }
        if (nombre) {
            document.getElementById('relevo_saliente_nombre').value = nombre;
        }
        if (dni) {
            document.getElementById('relevo_saliente_dni').value = dni;
        }

        // Reset entrante
        document.getElementById('chkSinEntrante').checked = false;
        document.getElementById('seccion_inputs_entrante').classList.remove('opacity-40', 'pointer-events-none');
        document.getElementById('relevo_entrante_nombre').value = '';
        document.getElementById('relevo_entrante_dni').value = '';
        const selectPadre = document.getElementById('select_padre_entrante');
        if (selectPadre) selectPadre.selectedIndex = 0;

        document.getElementById('modalVacanciaDirectiva').classList.remove('hidden');
        verificarFechaSaliente();
    }

    const movimientosDirectivaExistentes = @json($movimientosData ?? []);

    function verificarFechaSaliente() {
        const inputDni = document.getElementById('relevo_saliente_dni');
        const inputNombre = document.getElementById('relevo_saliente_nombre');
        const inputFecha = document.getElementById('relevo_fecha');
        const alerta = document.getElementById('alerta-saliente-fecha-duplicada');
        const btn = document.getElementById('btn-registrar-relevo');
        if (!inputFecha || !alerta || !btn) return;

        const dni = (inputDni ? inputDni.value : '').trim();
        const nombre = (inputNombre ? inputNombre.value : '').trim().toLowerCase();
        const fecha = inputFecha.value;

        const duplicado = movimientosDirectivaExistentes.some(m => {
            if (m.fecha !== fecha) return false;
            if (dni && m.dni && m.dni === dni) return true;
            if (nombre && m.nombre && m.nombre.toLowerCase() === nombre) return true;
            return false;
        });

        if (duplicado) {
            alerta.classList.remove('hidden');
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            inputFecha.classList.add('border-rose-500', 'ring-1', 'ring-rose-500');
        } else {
            alerta.classList.add('hidden');
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
            inputFecha.classList.remove('border-rose-500', 'ring-1', 'ring-rose-500');
        }
    }

    function seleccionarPadreEntrante(select) {
        const option = select.options[select.selectedIndex];
        if (option && option.value) {
            document.getElementById('relevo_entrante_dni').value = option.value;
            document.getElementById('relevo_entrante_nombre').value = option.getAttribute('data-nombre') || '';
            document.getElementById('chkSinEntrante').checked = false;
            document.getElementById('seccion_inputs_entrante').classList.remove('opacity-40', 'pointer-events-none');
        }
    }

    function toggleSinEntrante(checked) {
        const seccion = document.getElementById('seccion_inputs_entrante');
        const inputNombre = document.getElementById('relevo_entrante_nombre');
        const inputDni = document.getElementById('relevo_entrante_dni');
        const selectPadre = document.getElementById('select_padre_entrante');

        if (checked) {
            seccion.classList.add('opacity-40', 'pointer-events-none');
            inputNombre.value = 'VACANTE / POR DESIGNAR EN ASAMBLEA';
            inputDni.value = '';
            if (selectPadre) selectPadre.selectedIndex = 0;
        } else {
            seccion.classList.remove('opacity-40', 'pointer-events-none');
            inputNombre.value = '';
            inputDni.value = '';
        }
    }
</script>

@endsection
