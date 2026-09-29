@extends('layouts.app')

@section('title', 'Control de Asistencia Escolar y Horarios')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100 flex items-center justify-center">
                    <i class="fa-solid fa-barcode text-xl"></i>
                </span>
                Control de Asistencia Escolar
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Lector de Tarjeta de Asistencia Escolar (Código de Barras / QR) con evaluación por turno y gestión de justificaciones.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Selector de Fecha -->
            <form method="GET" action="{{ route('asistencia.estudiantes.index') }}" class="flex items-center gap-2 bg-white px-3 py-2 rounded-xl shadow-sm border border-slate-200">
                <i class="fa-regular fa-calendar text-slate-400"></i>
                <span class="text-xs font-semibold text-slate-600">Fecha:</span>
                <input type="date" name="fecha" value="{{ $fecha }}" onchange="this.form.submit()" 
                       class="text-xs font-bold text-slate-800 bg-transparent border-none focus:ring-0 p-0 cursor-pointer">
                @if(!empty($filtroTurno))<input type="hidden" name="turno" value="{{ $filtroTurno }}">@endif
                @if(!empty($filtroEstado))<input type="hidden" name="estado" value="{{ $filtroEstado }}">@endif
                @if(!empty($filtroNivel))<input type="hidden" name="nivel" value="{{ $filtroNivel }}">@endif
                @if(!empty($filtroGrado))<input type="hidden" name="grado" value="{{ $filtroGrado }}">@endif
                @if(!empty($filtroSeccion))<input type="hidden" name="seccion" value="{{ $filtroSeccion }}">@endif
                @if(!empty($search))<input type="hidden" name="search" value="{{ $search }}">@endif
            </form>

            <!-- Enlace Turnos y Talleres -->
            <a href="{{ route('configuracion.index', ['tab' => 'turnos-talleres']) }}" 
               class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 font-bold text-xs rounded-xl shadow-sm transition-all flex items-center gap-2">
                <i class="fa-solid fa-shapes text-indigo-600"></i>
                <span>Turnos por Grado y Talleres</span>
            </a>

            <!-- Botón Configurar Horarios -->
            <button onclick="document.getElementById('modal-horarios').classList.remove('hidden')" 
                    class="px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold text-xs rounded-xl shadow-sm transition-all flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-indigo-600"></i>
                <span>Configurar Horarios</span>
            </button>

            <!-- Botón Justificar Inasistencia -->
            <button onclick="document.getElementById('modal-falta-justificada').classList.remove('hidden')" 
                    class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all flex items-center gap-2">
                <i class="fa-solid fa-file-circle-plus"></i>
                <span>Justificar Inasistencia</span>
            </button>
        </div>
    </div>

    <!-- Active Schedules Banner (Turnos: Mañana, Tarde, Noche) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        @php
            $turnosInfo = [
                'Mañana' => ['icon' => 'fa-sun', 'color' => 'amber', 'bg' => 'from-amber-500/10 to-orange-500/5', 'border' => 'border-amber-200/80'],
                'Tarde' => ['icon' => 'fa-cloud-sun', 'color' => 'blue', 'bg' => 'from-blue-500/10 to-indigo-500/5', 'border' => 'border-blue-200/80'],
                'Noche' => ['icon' => 'fa-moon', 'color' => 'indigo', 'bg' => 'from-indigo-500/10 to-purple-500/5', 'border' => 'border-indigo-200/80'],
            ];
        @endphp

        @foreach(['Mañana', 'Tarde', 'Noche'] as $tName)
            @php
                $h = $horarios->get($tName);
                $info = $turnosInfo[$tName];
                $ingreso = $h ? substr($h->hora_ingreso, 0, 5) : '--:--';
                $limite = $h ? substr($h->hora_limite_puntual, 0, 5) : '--:--';
                $salida = $h ? substr($h->hora_salida, 0, 5) : '--:--';
                $activo = $h ? $h->activo : true;
            @endphp
            <div class="p-3.5 rounded-2xl bg-gradient-to-r {{ $info['bg'] }} border {{ $info['border'] }} flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white shadow-sm flex items-center justify-center text-{{ $info['color'] }}-600 font-bold text-base">
                        <i class="fa-solid {{ $info['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Turno {{ $tName }}</h4>
                            @if($activo)
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            @else
                                <span class="text-[10px] text-slate-400 italic">Inactivo</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Ingreso: <strong class="text-slate-700 font-mono">{{ $ingreso }}</strong> 
                            | Límite: <strong class="text-rose-600 font-mono">{{ $limite }}</strong>
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Salida</span>
                    <span class="text-xs font-mono font-bold text-slate-700">{{ $salida }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-base flex-shrink-0">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Matriculados</p>
                <p class="text-lg font-black text-slate-800 leading-none mt-1">{{ $totalEstudiantes }}</p>
            </div>
        </div>

        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base flex-shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Presentes Hoy</p>
                <p class="text-lg font-black text-emerald-600 leading-none mt-1">{{ $presentesHoy }}</p>
            </div>
        </div>

        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center font-bold text-base flex-shrink-0">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Puntuales</p>
                <p class="text-lg font-black text-teal-600 leading-none mt-1">{{ $puntuales }}</p>
            </div>
        </div>

        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-base flex-shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tardanzas</p>
                <p class="text-lg font-black text-amber-600 leading-none mt-1">{{ $tardanzasTotal }}</p>
            </div>
        </div>

        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-base flex-shrink-0">
                <i class="fa-solid fa-file-shield"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Justificadas</p>
                <p class="text-lg font-black text-indigo-600 leading-none mt-1">{{ $tardanzasJustificadas + $faltasJustificadas }}</p>
            </div>
        </div>

        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-base flex-shrink-0">
                <i class="fa-solid fa-user-xmark"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Ausentes</p>
                <p class="text-lg font-black text-rose-600 leading-none mt-1">{{ $ausentes }}</p>
            </div>
        </div>
    </div>

    <!-- Scanner Station Box (Main Focus for Portería / Scanning) -->
    <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-4 sm:p-7 text-white shadow-xl relative overflow-hidden border border-indigo-900/50">
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-3xl mx-auto text-center space-y-4">
            <!-- Mode Switcher (Pill Tabs: Camera vs Gun/Keyboard) -->
            <div class="inline-flex p-1 bg-white/10 backdrop-blur-md rounded-2xl border border-white/15 gap-1">
                <button type="button" id="tab-btn-camera" onclick="switchScanMode('camera')"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 bg-indigo-600 text-white shadow-md shadow-indigo-500/30">
                    <i class="fa-solid fa-camera"></i>
                    <span>📱 Cámara del Celular</span>
                </button>
                <button type="button" id="tab-btn-gun" onclick="switchScanMode('gun')"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 text-slate-300 hover:text-white hover:bg-white/5">
                    <i class="fa-solid fa-barcode"></i>
                    <span>🔫 Pistola / Lector USB</span>
                </button>
            </div>

            <!-- Selector Global de Actividad de Escaneo (Clases Regulares vs Talleres Contraturno) -->
            <div class="max-w-md mx-auto bg-white/10 backdrop-blur-md px-3.5 py-2 rounded-2xl border border-white/15 flex items-center justify-between gap-3 text-left">
                <div class="flex items-center gap-2 text-indigo-200 flex-shrink-0">
                    <span class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-bolt-lightning"></i>
                    </span>
                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-200">Actividad:</span>
                </div>
                <div class="flex-1">
                    <select id="scan-actividad-selector" onchange="actualizarActividadEscaneo(this.value)"
                            class="w-full text-xs font-bold text-white bg-slate-900/90 border border-white/20 rounded-xl px-2.5 py-1.5 focus:ring-2 focus:ring-indigo-400 focus:outline-none cursor-pointer">
                        <option value="AUTOMATICO">⚡ Automático Inteligente (Clase / Taller)</option>
                        <option value="CLASE_REGULAR">🎒 Solo Clases Regulares</option>
                        @if(isset($talleresActivos) && $talleresActivos->count() > 0)
                            <optgroup label="Talleres Extracurriculares (Contraturno)">
                                @foreach($talleresActivos as $tac)
                                    <option value="TALLER_{{ $tac->id }}">🏆 Taller: {{ $tac->nombre }} (Turno {{ $tac->turno }})</option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>
            </div>

            <!-- MODE 1: SMARTPHONE CAMERA SCANNER -->
            <div id="mode-camera" class="space-y-3">
                <h2 class="text-xl sm:text-2xl font-black tracking-tight">
                    Terminal Móvil — Escaneo con Cámara del Celular
                </h2>
                <p class="text-indigo-200 text-xs max-w-lg mx-auto">
                    Apunte la cámara al Código QR o Código de Barras de la Tarjeta del Estudiante. Detección automática instantánea.
                </p>

                <!-- Camera Container -->
                <div class="relative w-full max-w-sm mx-auto">
                    <div id="qr-reader" class="w-full overflow-hidden rounded-2xl bg-black border-2 border-indigo-500/50 shadow-2xl aspect-square flex items-center justify-center relative">
                        <div id="camera-placeholder" class="text-center p-6 space-y-3">
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-indigo-500/20 text-indigo-300 flex items-center justify-center text-3xl">
                                <i class="fa-solid fa-camera-rotate"></i>
                            </div>
                            <p class="text-xs text-slate-300 font-medium">Presione para activar la cámara en este dispositivo</p>
                            <button type="button" onclick="startCamera()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/40 transition-all flex items-center gap-2 mx-auto">
                                <i class="fa-solid fa-play"></i>
                                <span>Activar Cámara</span>
                            </button>
                        </div>
                    </div>

                    <!-- Scan laser indicator -->
                    <div id="scan-laser" class="hidden absolute left-4 right-4 h-0.5 bg-gradient-to-r from-transparent via-red-500 to-transparent shadow-[0_0_12px_rgba(239,68,68,1)] animate-pulse pointer-events-none top-1/2"></div>
                </div>

                <!-- Camera Controls & Continuous Mode Switch -->
                <div class="flex flex-wrap items-center justify-center gap-3 pt-1">
                    <button type="button" id="btn-toggle-cam" onclick="toggleCameraState()" class="hidden px-4 py-2 bg-rose-600/80 hover:bg-rose-600 text-white font-bold text-xs rounded-xl transition-all items-center gap-2">
                        <i class="fa-solid fa-stop"></i>
                        <span>Detener Cámara</span>
                    </button>
                    <button type="button" id="btn-switch-cam" onclick="switchCameraFacing()" class="hidden px-3.5 py-2 bg-white/10 hover:bg-white/20 text-white font-bold text-xs rounded-xl border border-white/20 transition-all items-center gap-2">
                        <i class="fa-solid fa-repeat"></i>
                        <span>Cambiar Cámara</span>
                    </button>
                    <label class="flex items-center gap-2 cursor-pointer bg-white/5 border border-white/10 px-3 py-2 rounded-xl text-xs text-slate-300 select-none">
                        <input type="checkbox" id="continuous-scan-check" checked class="rounded border-slate-600 text-indigo-600 focus:ring-0">
                        <span>Modo Continuo (Cola de Puerta)</span>
                    </label>
                </div>
            </div>

            <!-- MODE 2: HARDWARE BARCODE GUN / KEYBOARD INPUT -->
            <div id="mode-gun" class="hidden space-y-3">
                <h2 class="text-xl sm:text-2xl font-black tracking-tight">
                    Pistola Barcode USB / OTG / Bluetooth
                </h2>
                <p class="text-indigo-200 text-xs max-w-lg mx-auto">
                    El lector escribe automáticamente el código. También puede digitar el DNI del alumno manualmente.
                </p>

                <form action="{{ route('asistencia.estudiantes.escanear') }}" method="POST" id="form-scan" class="mt-4">
                    @csrf
                    <input type="hidden" name="actividad" id="form-scan-actividad" value="AUTOMATICO">
                    <div class="relative flex items-center">
                        <div class="absolute left-4 text-slate-400 text-xl pointer-events-none">
                            <i class="fa-solid fa-barcode"></i>
                        </div>
                        <input type="text" 
                               id="codigo-input" 
                               name="codigo" 
                               placeholder="Escanee código o DNI..." 
                               autocomplete="off"
                               required
                               class="w-full pl-12 pr-28 sm:pr-32 py-3.5 sm:py-4 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-white placeholder-slate-400 text-base sm:text-lg font-mono tracking-wider focus:outline-none focus:ring-4 focus:ring-indigo-500 focus:bg-white/20 transition-all shadow-inner">
                        <button type="submit" 
                                class="absolute right-2 sm:right-2.5 px-4 sm:px-5 py-2 sm:py-2.5 bg-indigo-500 hover:bg-indigo-600 active:scale-95 text-white font-bold text-xs sm:text-sm rounded-xl transition-all shadow-md flex items-center gap-2">
                            <i class="fa-solid fa-check"></i>
                            <span>Marcar</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- ================= BANNER EN VIVO DE ASISTENCIA / DATOS DEL ALUMNO (INGRESO O SALIDA) ================= -->
            @if(session('scan_result'))
                @php 
                    $scan = session('scan_result'); 
                    $est = $scan['estudiante'] ?? []; 
                    $tipoScan = $scan['tipo'] ?? 'INGRESO';
                    $estadoScan = $scan['estado'] ?? 'PUNTUAL';
                @endphp
                <div id="session-scan-banner" class="max-w-xl mx-auto my-3 text-left">
                    <div class="relative overflow-hidden rounded-3xl p-5 shadow-2xl border-2 {{ $tipoScan === 'SALIDA' ? 'bg-gradient-to-r from-blue-950 via-slate-900 to-indigo-950 border-cyan-400 shadow-cyan-500/20' : ($tipoScan === 'DUPLICADO_PREVENIDO' ? 'bg-gradient-to-r from-indigo-950 via-slate-900 to-slate-900 border-indigo-400 shadow-indigo-500/20' : ($tipoScan === 'COMPLETO' ? 'bg-gradient-to-r from-purple-950 via-slate-900 to-slate-900 border-purple-400 shadow-purple-500/20' : ($estadoScan === 'TARDANZA' ? 'bg-gradient-to-r from-amber-950 via-slate-900 to-slate-900 border-amber-400 shadow-amber-500/20' : 'bg-gradient-to-r from-emerald-950 via-slate-900 to-slate-900 border-emerald-400 shadow-emerald-500/20'))) }}">
                        <div class="flex items-center justify-between pb-3 border-b border-white/15">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-3.5 w-3.5 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $tipoScan === 'SALIDA' ? 'bg-cyan-400' : ($tipoScan === 'DUPLICADO_PREVENIDO' ? 'bg-indigo-400' : ($tipoScan === 'COMPLETO' ? 'bg-purple-400' : ($estadoScan === 'TARDANZA' ? 'bg-amber-400' : 'bg-emerald-400'))) }}"></span>
                                    <span class="relative inline-flex rounded-full h-3.5 w-3.5 {{ $tipoScan === 'SALIDA' ? 'bg-cyan-500' : ($tipoScan === 'DUPLICADO_PREVENIDO' ? 'bg-indigo-500' : ($tipoScan === 'COMPLETO' ? 'bg-purple-500' : ($estadoScan === 'TARDANZA' ? 'bg-amber-500' : 'bg-emerald-500'))) }}"></span>
                                </span>
                                <span class="text-xs sm:text-sm font-black uppercase tracking-wider {{ $tipoScan === 'SALIDA' ? 'text-cyan-300' : ($tipoScan === 'DUPLICADO_PREVENIDO' ? 'text-indigo-300' : ($tipoScan === 'COMPLETO' ? 'text-purple-300' : ($estadoScan === 'TARDANZA' ? 'text-amber-300' : 'text-emerald-300'))) }}">
                                    @if($tipoScan === 'SALIDA')
                                        🚪 SALIDA REGISTRADA — ESTUDIANTE SALIENDO DEL COLEGIO
                                    @elseif($tipoScan === 'DUPLICADO_PREVENIDO')
                                        ℹ️ INGRESO PREVIO — EL ESTUDIANTE YA INGRESÓ A LAS {{ $scan['hora'] ?? '' }}
                                    @elseif($tipoScan === 'COMPLETO')
                                        ✅ JORNADA COMPLETA — INGRESO Y SALIDA YA REGISTRADOS
                                    @elseif($estadoScan === 'TARDANZA')
                                        ⚠️ INGRESO REGISTRADO — ESTUDIANTE INGRESANDO (TARDANZA)
                                    @else
                                        🟢 INGRESO REGISTRADO — ESTUDIANTE INGRESANDO (PUNTUAL)
                                    @endif
                                </span>
                            </div>
                            <span class="px-3 py-1 rounded-xl text-xs font-black font-mono shadow-sm bg-white/20 text-white border border-white/20">
                                🕒 {{ $scan['hora'] }}
                            </span>
                        </div>
                        <div class="mt-4 flex items-center gap-4">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl flex items-center justify-center text-2xl font-black text-white shadow-lg flex-shrink-0 {{ $tipoScan === 'SALIDA' ? 'bg-blue-600 ring-4 ring-cyan-400/30' : ($tipoScan === 'DUPLICADO_PREVENIDO' ? 'bg-indigo-600 ring-4 ring-indigo-400/30' : ($tipoScan === 'COMPLETO' ? 'bg-purple-600 ring-4 ring-purple-400/30' : ($estadoScan === 'TARDANZA' ? 'bg-amber-600 ring-4 ring-amber-400/30' : 'bg-emerald-600 ring-4 ring-emerald-400/30'))) }}">
                                {{ substr($est['nombre'] ?? 'A', 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-extrabold uppercase tracking-widest text-indigo-300">Estudiante Identificado</p>
                                <h3 class="text-base sm:text-xl font-black text-white truncate leading-tight mt-0.5">
                                    {{ $est['nombre'] ?? 'Estudiante' }}
                                </h3>
                                <div class="flex flex-wrap items-center gap-1.5 mt-2 text-xs">
                                    <span class="bg-black/40 text-slate-200 px-2.5 py-0.5 rounded-lg font-mono font-bold border border-white/10">
                                        DNI: {{ $est['dni'] ?? '-' }}
                                    </span>
                                    <span class="bg-white/15 text-white px-2.5 py-0.5 rounded-lg font-bold">
                                        🎓 {{ $est['grado'] ?? '' }}
                                    </span>
                                    @if(!empty($est['nivel']))
                                        <span class="bg-white/10 text-slate-300 px-2 py-0.5 rounded-lg font-semibold">
                                            {{ $est['nivel'] }}
                                        </span>
                                    @endif
                                    <span class="bg-white/15 text-indigo-200 px-2.5 py-0.5 rounded-lg font-bold">
                                        Turno {{ $est['turno'] ?? 'Mañana' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Pill: Ingresando o Saliendo -->
                        @if($tipoScan === 'SALIDA')
                            <div class="mt-3 px-3 py-2 rounded-xl bg-cyan-500/20 border border-cyan-500/40 text-cyan-300 font-black text-xs sm:text-sm flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid fa-person-walking-dashed-line-arrow-right text-base animate-pulse"></i>
                                    <span>MOVIMIENTO: <strong>SALIENDO DEL COLEGIO</strong></span>
                                </span>
                                <span class="px-2 py-0.5 rounded-lg bg-cyan-400 text-slate-950 font-black text-[11px] uppercase tracking-wide">Salida OK</span>
                            </div>
                        @elseif($tipoScan === 'DUPLICADO_PREVENIDO')
                            <div class="mt-3 px-3 py-2 rounded-xl bg-indigo-500/20 border border-indigo-500/40 text-indigo-300 font-black text-xs sm:text-sm flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle-info text-base"></i>
                                    <span>ESTADO: <strong>YA REGISTRÓ INGRESO A LAS {{ $scan['hora'] ?? '' }}</strong></span>
                                </span>
                                <span class="px-2 py-0.5 rounded-lg bg-indigo-500 text-white font-black text-[11px] uppercase tracking-wide">En Colegio</span>
                            </div>
                        @elseif($tipoScan === 'COMPLETO')
                            <div class="mt-3 px-3 py-2 rounded-xl bg-purple-500/20 border border-purple-500/40 text-purple-300 font-black text-xs sm:text-sm flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid fa-award text-base"></i>
                                    <span>ESTADO: <strong>ASISTENCIA Y RETIRO COMPLETADOS</strong></span>
                                </span>
                                <span class="px-2 py-0.5 rounded-lg bg-purple-500 text-white font-black text-[11px] uppercase tracking-wide">Completado</span>
                            </div>
                        @elseif($estadoScan === 'TARDANZA')
                            <div class="mt-3 px-3 py-2 rounded-xl bg-amber-500/20 border border-amber-500/40 text-amber-300 font-black text-xs sm:text-sm flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid fa-clock-rotate-left text-base animate-pulse"></i>
                                    <span>MOVIMIENTO: <strong>INGRESANDO AL COLEGIO</strong></span>
                                </span>
                                <span class="px-2 py-0.5 rounded-lg bg-amber-500 text-slate-950 font-black text-[11px] uppercase tracking-wide">Tardanza</span>
                            </div>
                        @else
                            <div class="mt-3 px-3 py-2 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 font-black text-xs sm:text-sm flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid fa-person-walking-arrow-right text-base animate-pulse"></i>
                                    <span>MOVIMIENTO: <strong>INGRESANDO AL COLEGIO</strong></span>
                                </span>
                                <span class="px-2 py-0.5 rounded-lg bg-emerald-500 text-slate-950 font-black text-[11px] uppercase tracking-wide">Puntual</span>
                            </div>
                        @endif

                        <div class="mt-3.5 pt-2.5 border-t border-white/10 flex items-center justify-between text-xs text-slate-300">
                            <p class="font-medium text-[11px] sm:text-xs">
                                {{ $scan['mensaje'] }}
                            </p>
                            <button type="button" onclick="document.getElementById('session-scan-banner').remove()" class="text-white/60 hover:text-white text-xs underline font-semibold">
                                Ocultar
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Banner dinámico para escaneo en tiempo real (Cámara y Pistola) -->
            <div id="live-scan-card" class="hidden max-w-xl mx-auto my-3 text-left transition-all duration-300">
            </div>

            <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 pt-1 text-[11px] sm:text-xs text-slate-300">
                <span class="flex items-center gap-1.5"><i class="fa-solid fa-mobile-screen text-indigo-400"></i> Cámara Móvil QR/1D</span>
                <span class="flex items-center gap-1.5"><i class="fa-solid fa-arrows-split-up-and-left text-blue-400"></i> Turno Automático</span>
                <span class="flex items-center gap-1.5"><i class="fa-solid fa-volume-high text-emerald-400"></i> Beep Sonoro</span>
                <span class="flex items-center gap-1.5"><i class="fa-solid fa-file-circle-check text-amber-400"></i> Justificaciones</span>
            </div>
        </div>
    </div>

    <!-- Pestañas Rápidas por Nivel (Inicial, Primaria, Secundaria) -->
    @php
        $nivelActual = strtolower($filtroNivel ?? '');
    @endphp
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('asistencia.estudiantes.index', array_merge(request()->query(), ['nivel' => null])) }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ empty($nivelActual) ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80' }}">
            <i class="fa-solid fa-layer-group text-xs"></i>
            <span>Todos los Niveles</span>
            <span class="px-1.5 py-0.5 rounded-lg text-[10px] {{ empty($nivelActual) ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600' }}">
                {{ $conteosNivel['todos'] ?? 0 }}
            </span>
        </a>

        <a href="{{ route('asistencia.estudiantes.index', array_merge(request()->query(), ['nivel' => 'Inicial'])) }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $nivelActual === 'inicial' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-amber-50 hover:text-amber-700 border border-slate-200/80' }}">
            <i class="fa-solid fa-shapes text-xs"></i>
            <span>🧸 Nivel Inicial</span>
            <span class="px-1.5 py-0.5 rounded-lg text-[10px] {{ $nivelActual === 'inicial' ? 'bg-amber-700 text-white' : 'bg-amber-50 text-amber-700' }}">
                {{ $conteosNivel['inicial'] ?? 0 }}
            </span>
        </a>

        <a href="{{ route('asistencia.estudiantes.index', array_merge(request()->query(), ['nivel' => 'Primaria'])) }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $nivelActual === 'primaria' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 border border-slate-200/80' }}">
            <i class="fa-solid fa-book-open text-xs"></i>
            <span>📚 Nivel Primaria</span>
            <span class="px-1.5 py-0.5 rounded-lg text-[10px] {{ $nivelActual === 'primaria' ? 'bg-indigo-700 text-white' : 'bg-indigo-50 text-indigo-700' }}">
                {{ $conteosNivel['primaria'] ?? 0 }}
            </span>
        </a>

        <a href="{{ route('asistencia.estudiantes.index', array_merge(request()->query(), ['nivel' => 'Secundaria'])) }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $nivelActual === 'secundaria' ? 'bg-purple-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-purple-50 hover:text-purple-700 border border-slate-200/80' }}">
            <i class="fa-solid fa-user-graduate text-xs"></i>
            <span>🎓 Nivel Secundaria</span>
            <span class="px-1.5 py-0.5 rounded-lg text-[10px] {{ $nivelActual === 'secundaria' ? 'bg-purple-700 text-white' : 'bg-purple-50 text-purple-700' }}">
                {{ $conteosNivel['secundaria'] ?? 0 }}
            </span>
        </a>
    </div>

    <!-- Attendance Log Table with Filters -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <!-- Table Header & Filter Tabs -->
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 bg-slate-50/50">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Registros de Asistencia del Día</h3>
                <p class="text-xs text-slate-500">Listado de asistencias, tardanzas e inasistencias para el {{ date('d/m/Y', strtotime($fecha)) }}</p>
            </div>

            <!-- Filter Badges por Turno, Actividad y Estado -->
            <div class="flex flex-wrap items-center gap-1.5 text-xs font-semibold">
                <a href="{{ route('asistencia.estudiantes.index', array_merge(request()->query(), ['turno' => null, 'estado' => null, 'tipo_actividad' => null])) }}" 
                   class="px-3 py-1.5 rounded-lg transition-colors {{ empty($filtroTurno) && empty($filtroEstado) && (empty($filtroActividad) || $filtroActividad === 'TODOS') ? 'bg-indigo-600 text-white' : 'bg-slate-200/70 text-slate-600 hover:bg-slate-300/70' }}">
                    Todos
                </a>
                <a href="{{ route('asistencia.estudiantes.index', array_merge(request()->query(), ['turno' => 'Mañana', 'estado' => null])) }}" 
                   class="px-3 py-1.5 rounded-lg transition-colors {{ $filtroTurno === 'Mañana' ? 'bg-amber-600 text-white' : 'bg-slate-200/70 text-slate-600 hover:bg-slate-300/70' }}">
                    🌅 Mañana
                </a>
                <a href="{{ route('asistencia.estudiantes.index', array_merge(request()->query(), ['turno' => 'Tarde', 'estado' => null])) }}" 
                   class="px-3 py-1.5 rounded-lg transition-colors {{ $filtroTurno === 'Tarde' ? 'bg-blue-600 text-white' : 'bg-slate-200/70 text-slate-600 hover:bg-slate-300/70' }}">
                    🌇 Tarde
                </a>
                <a href="{{ route('asistencia.estudiantes.index', array_merge(request()->query(), ['tipo_actividad' => 'TALLER', 'turno' => null])) }}" 
                   class="px-3 py-1.5 rounded-lg transition-colors {{ $filtroActividad === 'TALLER' ? 'bg-indigo-600 text-white' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200' }}">
                    🏆 Talleres
                </a>
                <a href="{{ route('asistencia.estudiantes.index', array_merge(request()->query(), ['estado' => 'TARDANZA'])) }}" 
                   class="px-3 py-1.5 rounded-lg transition-colors {{ $filtroEstado === 'TARDANZA' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200' }}">
                    ⚠️ Tardanzas
                </a>
                <a href="{{ route('asistencia.estudiantes.index', array_merge(request()->query(), ['estado' => 'JUSTIFICADAS'])) }}" 
                   class="px-3 py-1.5 rounded-lg transition-colors {{ $filtroEstado === 'JUSTIFICADAS' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' }}">
                    🛡️ Justificadas
                </a>
            </div>
        </div>

        <!-- Barra de Filtros Combinados: Nivel, Grado, Sección y Buscador -->
        <div class="p-3.5 sm:p-4 bg-slate-50/70 border-b border-slate-100">
            <form action="{{ route('asistencia.estudiantes.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2.5 items-center">
                <input type="hidden" name="fecha" value="{{ $fecha }}">
                @if(!empty($filtroTurno))<input type="hidden" name="turno" value="{{ $filtroTurno }}">@endif
                @if(!empty($filtroEstado))<input type="hidden" name="estado" value="{{ $filtroEstado }}">@endif
                @if(!empty($filtroActividad) && $filtroActividad !== 'TODOS')<input type="hidden" name="tipo_actividad" value="{{ $filtroActividad }}">@endif
                @if(!empty($filtroTallerId))<input type="hidden" name="taller_id" value="{{ $filtroTallerId }}">@endif

                <!-- Buscador por DNI o Apellidos/Nombres -->
                <div class="sm:col-span-2 lg:col-span-4 relative">
                    <input type="text" 
                           name="search" 
                           value="{{ $search ?? '' }}" 
                           placeholder="Buscar por DNI o apellidos..." 
                           class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all shadow-sm placeholder-slate-400">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs pointer-events-none"></i>
                </div>

                <!-- Filtro por Nivel -->
                <div class="lg:col-span-2">
                    <select name="nivel" onchange="this.form.submit()" 
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all shadow-sm cursor-pointer">
                        <option value="">🎓 Nivel: Todos</option>
                        <option value="Inicial" {{ strtolower($filtroNivel ?? '') === 'inicial' ? 'selected' : '' }}>🧸 Nivel Inicial</option>
                        <option value="Primaria" {{ strtolower($filtroNivel ?? '') === 'primaria' ? 'selected' : '' }}>📚 Nivel Primaria</option>
                        <option value="Secundaria" {{ strtolower($filtroNivel ?? '') === 'secundaria' ? 'selected' : '' }}>🎓 Nivel Secundaria</option>
                    </select>
                </div>

                <!-- Filtro por Grado -->
                <div class="lg:col-span-2">
                    <select name="grado" onchange="this.form.submit()" 
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all shadow-sm cursor-pointer">
                        <option value="">📚 Grado: Todos</option>
                        @if(strtolower($filtroNivel ?? '') === 'inicial')
                            <option value="3 Años" {{ ($filtroGrado ?? '') === '3 Años' ? 'selected' : '' }}>3 Años</option>
                            <option value="4 Años" {{ ($filtroGrado ?? '') === '4 Años' ? 'selected' : '' }}>4 Años</option>
                            <option value="5 Años" {{ ($filtroGrado ?? '') === '5 Años' ? 'selected' : '' }}>5 Años</option>
                        @elseif(strtolower($filtroNivel ?? '') === 'secundaria')
                            <option value="1ro" {{ ($filtroGrado ?? '') === '1ro' ? 'selected' : '' }}>1ro Secundaria</option>
                            <option value="2do" {{ ($filtroGrado ?? '') === '2do' ? 'selected' : '' }}>2do Secundaria</option>
                            <option value="3ro" {{ ($filtroGrado ?? '') === '3ro' ? 'selected' : '' }}>3ro Secundaria</option>
                            <option value="4to" {{ ($filtroGrado ?? '') === '4to' ? 'selected' : '' }}>4to Secundaria</option>
                            <option value="5to" {{ ($filtroGrado ?? '') === '5to' ? 'selected' : '' }}>5to Secundaria</option>
                        @elseif(strtolower($filtroNivel ?? '') === 'primaria')
                            <option value="1ro" {{ ($filtroGrado ?? '') === '1ro' ? 'selected' : '' }}>1ro Primaria</option>
                            <option value="2do" {{ ($filtroGrado ?? '') === '2do' ? 'selected' : '' }}>2do Primaria</option>
                            <option value="3ro" {{ ($filtroGrado ?? '') === '3ro' ? 'selected' : '' }}>3ro Primaria</option>
                            <option value="4to" {{ ($filtroGrado ?? '') === '4to' ? 'selected' : '' }}>4to Primaria</option>
                            <option value="5to" {{ ($filtroGrado ?? '') === '5to' ? 'selected' : '' }}>5to Primaria</option>
                            <option value="6to" {{ ($filtroGrado ?? '') === '6to' ? 'selected' : '' }}>6to Primaria</option>
                        @else
                            <optgroup label="🧸 Nivel Inicial">
                                <option value="3 Años" {{ ($filtroGrado ?? '') === '3 Años' ? 'selected' : '' }}>3 Años</option>
                                <option value="4 Años" {{ ($filtroGrado ?? '') === '4 Años' ? 'selected' : '' }}>4 Años</option>
                                <option value="5 Años" {{ ($filtroGrado ?? '') === '5 Años' ? 'selected' : '' }}>5 Años</option>
                            </optgroup>
                            <optgroup label="📚 Primaria / Secundaria">
                                <option value="1ro" {{ ($filtroGrado ?? '') === '1ro' ? 'selected' : '' }}>1ro</option>
                                <option value="2do" {{ ($filtroGrado ?? '') === '2do' ? 'selected' : '' }}>2do</option>
                                <option value="3ro" {{ ($filtroGrado ?? '') === '3ro' ? 'selected' : '' }}>3ro</option>
                                <option value="4to" {{ ($filtroGrado ?? '') === '4to' ? 'selected' : '' }}>4to</option>
                                <option value="5to" {{ ($filtroGrado ?? '') === '5to' ? 'selected' : '' }}>5to</option>
                                <option value="6to" {{ ($filtroGrado ?? '') === '6to' ? 'selected' : '' }}>6to</option>
                            </optgroup>
                        @endif
                    </select>
                </div>

                <!-- Filtro por Sección -->
                <div class="lg:col-span-2">
                    <select name="seccion" onchange="this.form.submit()" 
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all shadow-sm cursor-pointer">
                        <option value="">🏷️ Sección: Todas</option>
                        @foreach($seccionesDisponibles as $sec)
                            <option value="{{ $sec }}" {{ strtoupper($filtroSeccion ?? '') === strtoupper($sec) ? 'selected' : '' }}>
                                Sección {{ $sec }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Botones de Acción -->
                <div class="sm:col-span-2 lg:col-span-2 flex items-center gap-1.5">
                    <button type="submit" 
                            class="flex-1 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-all shadow-sm">
                        <i class="fa-solid fa-filter text-[11px]"></i>
                        <span>Filtrar</span>
                    </button>
                    @if(!empty($search) || (!empty($filtroNivel) && $filtroNivel !== 'todos') || (!empty($filtroGrado) && $filtroGrado !== 'todos') || (!empty($filtroSeccion) && $filtroSeccion !== 'todos'))
                        <a href="{{ route('asistencia.estudiantes.index', ['fecha' => $fecha, 'turno' => $filtroTurno, 'estado' => $filtroEstado]) }}" 
                           title="Restablecer filtros de nivel, grado y sección" 
                           class="px-2.5 py-2 bg-white hover:bg-rose-50 hover:text-rose-600 text-slate-500 border border-slate-200 rounded-xl text-xs font-bold transition-all flex items-center justify-center shadow-sm">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>

            <!-- Badges de Filtros Activos con eliminación rápida individual -->
            @if(!empty($search) || (!empty($filtroNivel) && $filtroNivel !== 'todos') || (!empty($filtroGrado) && $filtroGrado !== 'todos') || (!empty($filtroSeccion) && $filtroSeccion !== 'todos'))
                <div class="mt-2.5 pt-2 border-t border-slate-200/60 flex flex-wrap items-center gap-1.5 text-xs">
                    <span class="text-slate-400 font-semibold text-[11px]">Filtros activos:</span>
                    @if(!empty($search))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-indigo-50 text-indigo-700 rounded-lg text-[11px] font-bold border border-indigo-200">
                            <i class="fa-solid fa-magnifying-glass text-[10px]"></i> "{{ $search }}"
                            <a href="{{ route('asistencia.estudiantes.index', array_merge(request()->query(), ['search' => null])) }}" class="hover:text-indigo-900 ml-1 font-black">&times;</a>
                        </span>
                    @endif
                    @if(!empty($filtroNivel) && $filtroNivel !== 'todos')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-teal-50 text-teal-700 rounded-lg text-[11px] font-bold border border-teal-200">
                            🎓 {{ ucfirst(strtolower($filtroNivel)) }}
                            <a href="{{ route('asistencia.estudiantes.index', array_merge(request()->query(), ['nivel' => null])) }}" class="hover:text-teal-900 ml-1 font-black">&times;</a>
                        </span>
                    @endif
                    @if(!empty($filtroGrado) && $filtroGrado !== 'todos')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-amber-50 text-amber-700 rounded-lg text-[11px] font-bold border border-amber-200">
                            📚 {{ $filtroGrado }}
                            <a href="{{ route('asistencia.estudiantes.index', array_merge(request()->query(), ['grado' => null])) }}" class="hover:text-amber-900 ml-1 font-black">&times;</a>
                        </span>
                    @endif
                    @if(!empty($filtroSeccion) && $filtroSeccion !== 'todos')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-purple-50 text-purple-700 rounded-lg text-[11px] font-bold border border-purple-200">
                            🏷️ Sección {{ $filtroSeccion }}
                            <a href="{{ route('asistencia.estudiantes.index', array_merge(request()->query(), ['seccion' => null])) }}" class="hover:text-purple-900 ml-1 font-black">&times;</a>
                        </span>
                    @endif
                    <a href="{{ route('asistencia.estudiantes.index', ['fecha' => $fecha, 'turno' => $filtroTurno, 'estado' => $filtroEstado]) }}" 
                       class="text-[11px] text-rose-600 hover:underline font-semibold ml-auto">
                        Limpiar filtros de aula
                    </a>
                </div>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-400 text-[11px] font-bold uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4">Estudiante</th>
                        <th class="py-3.5 px-4">Grado / Sección</th>
                        <th class="py-3.5 px-4">Turno</th>
                        <th class="py-3.5 px-4">Hora Ingreso</th>
                        <th class="py-3.5 px-4">Hora Salida</th>
                        <th class="py-3.5 px-4">Estado</th>
                        <th class="py-3.5 px-4">Justificación / Obs</th>
                        <th class="py-3.5 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-asistencias-body" class="divide-y divide-slate-100 font-medium">
                    @forelse($asistencias as $asistencia)
                        <tr id="asistencia-row-{{ $asistencia->estudiante->dni }}" class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs uppercase flex-shrink-0">
                                        {{ substr($asistencia->estudiante->nombres ?? 'A', 0, 1) }}{{ substr($asistencia->estudiante->apellidos ?? 'E', 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 text-sm leading-tight">
                                            {{ $asistencia->estudiante->apellidos }}, {{ $asistencia->estudiante->nombres }}
                                        </p>
                                        <p class="text-xs text-slate-400 font-mono mt-0.5">DNI: {{ $asistencia->estudiante->dni }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-xs font-semibold">
                                    {{ $asistencia->estudiante->grado ?? '1ro' }} "{{ $asistencia->estudiante->seccion ?? 'A' }}"
                                </span>
                                <span class="text-[11px] text-slate-400 block">{{ $asistencia->estudiante->nivel ?? 'Primaria' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @php
                                    $turnoActual = $asistencia->turno ?? $asistencia->estudiante->turno ?? 'Mañana';
                                    $badgeColors = [
                                        'Mañana' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'Tarde' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'Noche' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'Sábado' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    ];
                                    $cls = $badgeColors[$turnoActual] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                @endphp
                                <div class="space-y-1">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $cls }}">
                                        {{ $turnoActual }}
                                    </span>
                                    @if($asistencia->tipo_actividad === 'TALLER')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-200 block max-w-fit" title="Asistencia a Taller Extracurricular">
                                            <i class="fa-solid fa-shapes text-[9px] text-indigo-500"></i>
                                            {{ $asistencia->taller->nombre ?? 'Taller' }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-600 block max-w-fit">
                                            <i class="fa-solid fa-book-open text-[9px] text-slate-400"></i> Clases
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 cell-hora-ingreso">
                                @if($asistencia->hora_ingreso)
                                    <div class="font-mono text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                        <i class="fa-regular fa-clock text-slate-400"></i>
                                        {{ date('h:i:s A', strtotime($asistencia->hora_ingreso)) }}
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">No registró</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 cell-hora-salida">
                                @if($asistencia->hora_salida)
                                    <div class="flex items-center gap-1.5">
                                        <div class="font-mono text-xs font-bold text-slate-800 flex items-center gap-1">
                                            <i class="fa-solid fa-arrow-right-from-bracket text-emerald-500"></i>
                                            {{ date('h:i:s A', strtotime($asistencia->hora_salida)) }}
                                        </div>
                                        <button type="button" 
                                                onclick="abrirModalHoraSalida({{ $asistencia->id }}, '{{ addslashes($asistencia->estudiante->nombre_completo ?? 'Estudiante') }}', '{{ substr($asistencia->hora_salida, 0, 5) }}', '{{ addslashes($asistencia->observacion ?? '') }}')"
                                                title="Corregir o anular hora de salida"
                                                class="p-1 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition-colors">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </button>
                                    </div>
                                @elseif($asistencia->hora_ingreso)
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                                            En clase
                                        </span>
                                        <button type="button" 
                                                onclick="abrirModalHoraSalida({{ $asistencia->id }}, '{{ addslashes($asistencia->estudiante->nombre_completo ?? 'Estudiante') }}', '', '{{ addslashes($asistencia->observacion ?? '') }}')"
                                                title="Registrar o corregir salida manual"
                                                class="p-1 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition-colors">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">--:--</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 cell-estado">
                                {!! $asistencia->estado_badge_html !!}
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                @if($asistencia->justificado)
                                    <div class="text-xs">
                                        <p class="font-bold text-indigo-700 flex items-center gap-1">
                                            <i class="fa-solid fa-file-circle-check"></i>
                                            {{ $asistencia->motivo_justificacion }}
                                        </p>
                                        @if($asistencia->documento_sustento)
                                            <p class="text-[11px] text-slate-500 truncate" title="{{ $asistencia->documento_sustento }}">
                                                Doc: {{ $asistencia->documento_sustento }}
                                            </p>
                                        @endif
                                    </div>
                                @elseif($asistencia->observacion)
                                    <span class="text-xs text-slate-500 truncate block max-w-[200px]" title="{{ $asistencia->observacion }}">
                                        {{ $asistencia->observacion }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Sin observaciones</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Botón Justificar si es tardanza o falta no justificada -->
                                    @if(!$asistencia->justificado && ($asistencia->estado === 'TARDANZA' || str_contains($asistencia->estado, 'FALTA')))
                                        <button onclick="abrirModalJustificar({{ $asistencia->id }}, '{{ addslashes($asistencia->estudiante->nombre_completo) }}', '{{ $asistencia->estado }}')" 
                                                title="Justificar Tardanza/Inasistencia"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 transition-colors">
                                            <i class="fa-solid fa-file-pen text-xs"></i>
                                            <span>Justificar</span>
                                        </button>
                                    @endif

                                    <!-- Botón Corregir Salida -->
                                    @if($asistencia->hora_ingreso)
                                        <button type="button" 
                                                onclick="abrirModalHoraSalida({{ $asistencia->id }}, '{{ addslashes($asistencia->estudiante->nombre_completo ?? 'Estudiante') }}', '{{ $asistencia->hora_salida ? substr($asistencia->hora_salida, 0, 5) : '' }}', '{{ addslashes($asistencia->observacion ?? '') }}')"
                                                title="Corregir Hora de Salida"
                                                class="p-2 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors">
                                            <i class="fa-solid fa-clock-rotate-left text-sm"></i>
                                        </button>
                                    @endif

                                    <!-- Ver Fotocheck / Tarjeta Asistencia -->
                                    <a href="{{ route('estudiantes.tarjeta', $asistencia->estudiante_id) }}" 
                                       target="_blank"
                                       title="Ver Tarjeta de Asistencia Escolar"
                                       class="p-2 rounded-xl text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition-colors">
                                        <i class="fa-solid fa-id-card text-base"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center">
                                <div class="max-w-md mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-xl">
                                        <i class="fa-solid fa-clipboard-user"></i>
                                    </div>
                                    @if(!empty($search) || (!empty($filtroNivel) && $filtroNivel !== 'todos') || (!empty($filtroGrado) && $filtroGrado !== 'todos') || (!empty($filtroSeccion) && $filtroSeccion !== 'todos') || !empty($filtroTurno) || !empty($filtroEstado))
                                        <h4 class="text-sm font-bold text-slate-700">No se encontraron registros con los filtros seleccionados</h4>
                                        <p class="text-xs text-slate-400">Pruebe seleccionando otro nivel, grado, sección o turno para el {{ date('d/m/Y', strtotime($fecha)) }}.</p>
                                        <div class="pt-2">
                                            <a href="{{ route('asistencia.estudiantes.index', ['fecha' => $fecha]) }}" 
                                               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs transition-colors">
                                                <i class="fa-solid fa-rotate-left text-xs"></i>
                                                <span>Ver todos los alumnos del día</span>
                                            </a>
                                        </div>
                                    @else
                                        <h4 class="text-sm font-bold text-slate-700">Sin registros para esta fecha</h4>
                                        <p class="text-xs text-slate-400">Escanee la tarjeta de asistencia con el lector o registre una inasistencia justificada.</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= MODAL 1: CONFIGURAR HORARIOS POR TURNO ================= -->
<div id="modal-horarios" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full overflow-hidden border border-slate-200">
        <div class="p-6 bg-gradient-to-r from-slate-900 to-indigo-950 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-indigo-300">
                    <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black">Configuración de Horarios y Turnos</h3>
                    <p class="text-xs text-indigo-200">Defina las horas de ingreso, límite de puntualidad y salida por turno.</p>
                </div>
            </div>
            <button onclick="document.getElementById('modal-horarios').classList.add('hidden')" class="text-slate-400 hover:text-white text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('asistencia.estudiantes.horarios') }}" method="POST" class="p-6 space-y-5">
            @csrf
            
            @foreach(['Mañana', 'Tarde', 'Noche'] as $tName)
                @php
                    $h = $horarios->get($tName);
                    $ingresoVal = $h ? substr($h->hora_ingreso, 0, 5) : ($tName === 'Mañana' ? '07:30' : ($tName === 'Tarde' ? '13:00' : '18:30'));
                    $limiteVal = $h ? substr($h->hora_limite_puntual, 0, 5) : ($tName === 'Mañana' ? '08:00' : ($tName === 'Tarde' ? '13:30' : '19:00'));
                    $salidaVal = $h ? substr($h->hora_salida, 0, 5) : ($tName === 'Mañana' ? '13:00' : ($tName === 'Tarde' ? '18:30' : '22:30'));
                    $activoChecked = $h ? $h->activo : ($tName !== 'Noche');
                @endphp
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ $tName === 'Mañana' ? 'bg-amber-500' : ($tName === 'Tarde' ? 'bg-blue-500' : 'bg-purple-500') }}"></span>
                            <h4 class="font-extrabold text-slate-800 text-sm uppercase tracking-wide">Turno {{ $tName }}</h4>
                        </div>
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                            <input type="checkbox" name="horarios[{{ $tName }}][activo]" value="1" {{ $activoChecked ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            Habilitado en I.E.
                        </label>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase">Hora Apertura / Ingreso</label>
                            <input type="time" name="horarios[{{ $tName }}][hora_ingreso]" value="{{ $ingresoVal }}" required
                                   class="mt-1 w-full text-xs font-mono font-bold rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-rose-600 uppercase">Límite Puntual (Tolerancia)</label>
                            <input type="time" name="horarios[{{ $tName }}][hora_limite_puntual]" value="{{ $limiteVal }}" required
                                   class="mt-1 w-full text-xs font-mono font-bold rounded-xl border-rose-300 bg-rose-50/30 text-rose-700 focus:border-rose-500 focus:ring-rose-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase">Hora de Salida</label>
                            <input type="time" name="horarios[{{ $tName }}][hora_salida]" value="{{ $salidaVal }}" required
                                   class="mt-1 w-full text-xs font-mono font-bold rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-horarios').classList.add('hidden')" 
                        class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Guardar Configuración
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL 2: JUSTIFICAR TARDANZA O FALTA EXISTENTE ================= -->
<div id="modal-justificar" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200">
        <div class="p-6 bg-gradient-to-r from-amber-600 to-orange-700 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-amber-200">
                    <i class="fa-solid fa-file-pen text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black">Justificar Registro de Asistencia</h3>
                    <p class="text-xs text-amber-100" id="justificar-subtitulo">Ingrese el sustento o certificado.</p>
                </div>
            </div>
            <button onclick="document.getElementById('modal-justificar').classList.add('hidden')" class="text-amber-200 hover:text-white text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="form-justificar-asistencia" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alumno</label>
                <input type="text" id="justificar-nombre-alumno" readonly 
                       class="w-full bg-slate-100 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 py-2.5 px-3">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Motivo de Justificación *</label>
                <select name="motivo_justificacion" required 
                        class="w-full rounded-xl border-slate-300 text-xs font-semibold focus:border-amber-500 focus:ring-amber-500">
                    <option value="">-- Seleccione el motivo --</option>
                    <option value="Cita médica o atención de salud">Cita médica o atención de salud</option>
                    <option value="Descanso médico / Enfermedad">Descanso médico / Enfermedad</option>
                    <option value="Problemas familiares o fuerza mayor">Problemas familiares o fuerza mayor</option>
                    <option value="Trámite documentario o legal">Trámite documentario o legal</option>
                    <option value="Retraso por transporte / Clima adverso">Retraso por transporte / Clima adverso</option>
                    <option value="Permiso autorizado por Dirección">Permiso autorizado por Dirección</option>
                    <option value="Otro motivo justificado">Otro motivo justificado</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Documento Sustentatorio</label>
                <input type="text" name="documento_sustento" placeholder="Ej. Certificado Médico N° 1245 / Solicitud escrita" 
                       class="w-full rounded-xl border-slate-300 text-xs font-medium focus:border-amber-500 focus:ring-amber-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Observación Adicional</label>
                <textarea name="observacion" rows="2" placeholder="Detalles de la justificación brindada por el apoderado..." 
                          class="w-full rounded-xl border-slate-300 text-xs font-medium focus:border-amber-500 focus:ring-amber-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-justificar').classList.add('hidden')" 
                        class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-md transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-check"></i>
                    Aplicar Justificación
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL 3: REGISTRAR FALTA / INASISTENCIA JUSTIFICADA ================= -->
<div id="modal-falta-justificada" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200">
        <div class="p-6 bg-gradient-to-r from-indigo-700 to-violet-800 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-indigo-200">
                    <i class="fa-solid fa-file-circle-plus text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black">Justificar Inasistencia de Alumno</h3>
                    <p class="text-xs text-indigo-200">Registrar permiso previo o justificación de ausencia escolar.</p>
                </div>
            </div>
            <button onclick="document.getElementById('modal-falta-justificada').classList.add('hidden')" class="text-indigo-200 hover:text-white text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('asistencia.estudiantes.justificar_falta') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Seleccionar Estudiante *</label>
                <select name="estudiante_id" required 
                        class="w-full rounded-xl border-slate-300 text-xs font-semibold focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- Buscar por Apellidos y Nombres --</option>
                    @foreach($estudiantesLista as $est)
                        <option value="{{ $est->id }}">
                            {{ $est->apellidos }}, {{ $est->nombres }} (DNI: {{ $est->dni }} - {{ $est->grado }} "{{ $est->seccion }}") [Turno {{ $est->turno ?? 'Mañana' }}]
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Fecha de la Inasistencia *</label>
                    <input type="date" name="fecha" value="{{ $fecha }}" required 
                           class="w-full rounded-xl border-slate-300 text-xs font-bold focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Motivo *</label>
                    <select name="motivo_justificacion" required 
                            class="w-full rounded-xl border-slate-300 text-xs font-semibold focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="Descanso médico / Enfermedad">Descanso médico / Enfermedad</option>
                        <option value="Cita médica de emergencia">Cita médica de emergencia</option>
                        <option value="Problemas familiares graves">Problemas familiares graves</option>
                        <option value="Duelo familiar">Duelo familiar</option>
                        <option value="Permiso formal de APAFA / I.E.">Permiso formal de APAFA / I.E.</option>
                        <option value="Viaje justificado">Viaje justificado</option>
                        <option value="Otro motivo">Otro motivo</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Documento Sustentatorio</label>
                <input type="text" name="documento_sustento" placeholder="Ej. Certificado Médico N° 98234, Carta firmada por el apoderado" 
                       class="w-full rounded-xl border-slate-300 text-xs font-medium focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Observaciones</label>
                <textarea name="observacion" rows="2" placeholder="Detalles de la justificación..." 
                          class="w-full rounded-xl border-slate-300 text-xs font-medium focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-falta-justificada').classList.add('hidden')" 
                        class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Registrar Inasistencia Justificada
                </button>
            </div>
        </form>
    </div>
<!-- ================= MODAL 4: CORREGIR / ACTUALIZAR HORA DE SALIDA ================= -->
<div id="modal-hora-salida" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200">
        <div class="p-6 bg-gradient-to-r from-slate-900 via-indigo-900 to-slate-900 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-emerald-400">
                    <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-black">Corregir Hora de Salida</h3>
                    <p class="text-xs text-indigo-200" id="salida-nombre-estudiante">Alumno</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modal-hora-salida').classList.add('hidden')" class="text-slate-400 hover:text-white text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="form-hora-salida" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Hora de Salida (Formato 24h)</label>
                <div class="relative">
                    <input type="time" name="hora_salida" id="input-hora-salida" 
                           class="w-full pl-9 pr-3 py-2.5 rounded-xl border-2 border-slate-200 text-sm font-bold font-mono focus:border-indigo-600 focus:outline-none">
                    <i class="fa-regular fa-clock absolute left-3 top-3 text-slate-400 text-sm"></i>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Especifique la hora real de salida escolar.</p>
            </div>

            <div class="p-3 bg-emerald-50 rounded-2xl border border-emerald-200">
                <label class="flex items-start gap-2.5 cursor-pointer">
                    <input type="checkbox" name="dejar_en_clase" id="chk-dejar-en-clase" value="1" 
                           onchange="toggleSalidaManualInput(this.checked)"
                           class="rounded text-emerald-600 focus:ring-emerald-500 mt-0.5">
                    <div class="text-xs">
                        <span class="font-bold text-emerald-900 block">Dejar "En clase" (Anular salida)</span>
                        <span class="text-emerald-700 text-[11px]">Si la salida se marcó por error o por doble lectura del escáner, marque esta opción para borrar la hora de salida.</span>
                    </div>
                </label>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Observación (Opcional)</label>
                <input type="text" name="observacion" id="input-observacion-salida" placeholder="Ej. Corrección por doble lectura o salida anticipada" 
                       class="w-full rounded-xl border-slate-300 text-xs font-medium focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-hora-salida').classList.add('hidden')" 
                        class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-check"></i>
                    Guardar Corrección
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModalHoraSalida(id, nombre, horaSalida, observacion) {
        const modal = document.getElementById('modal-hora-salida');
        const form = document.getElementById('form-hora-salida');
        const nombreLabel = document.getElementById('salida-nombre-estudiante');
        const inputHora = document.getElementById('input-hora-salida');
        const chkDejarEnClase = document.getElementById('chk-dejar-en-clase');
        const inputObs = document.getElementById('input-observacion-salida');

        form.action = `/asistencia-estudiantes/${id}/hora-salida`;
        nombreLabel.textContent = nombre;
        inputHora.value = horaSalida || '';
        inputObs.value = observacion || '';
        chkDejarEnClase.checked = false;
        inputHora.disabled = false;

        modal.classList.remove('hidden');
    }

    function toggleSalidaManualInput(checked) {
        const inputHora = document.getElementById('input-hora-salida');
        if (checked) {
            inputHora.value = '';
            inputHora.disabled = true;
        } else {
            inputHora.disabled = false;
        }
    }

    function abrirModalJustificar(id, nombre, estado) {
        const modal = document.getElementById('modal-justificar');
        const form = document.getElementById('form-justificar-asistencia');
        const nombreInput = document.getElementById('justificar-nombre-alumno');
        const subtitulo = document.getElementById('justificar-subtitulo');

        form.action = `/asistencia-estudiantes/${id}/justificar`;
        nombreInput.value = nombre;
        subtitulo.textContent = `Justificando ${estado} registrada`;
        modal.classList.remove('hidden');
    }

    // ================= SCANNER AUDIO & HAPTIC FEEDBACK =================
    function playAudioBeep(type = 'success') {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            if (type === 'success') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, ctx.currentTime);
                osc.frequency.setValueAtTime(1320, ctx.currentTime + 0.08);
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.22);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.22);
            } else {
                osc.type = 'square';
                osc.frequency.setValueAtTime(440, ctx.currentTime);
                osc.frequency.setValueAtTime(310, ctx.currentTime + 0.1);
                gain.gain.setValueAtTime(0.12, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.35);
            }
        } catch (e) {}
    }

    function triggerHaptic(type = 'success') {
        if ('vibrate' in navigator) {
            try {
                if (type === 'success') {
                    navigator.vibrate([70, 40, 70]);
                } else {
                    navigator.vibrate([160, 60, 160]);
                }
            } catch (e) {}
        }
    }

    // ================= SCANNER MODE SWITCHING =================
    function switchScanMode(mode) {
        const modeCam = document.getElementById('mode-camera');
        const modeGun = document.getElementById('mode-gun');
        const tabCam = document.getElementById('tab-btn-camera');
        const tabGun = document.getElementById('tab-btn-gun');

        if (mode === 'camera') {
            modeCam.classList.remove('hidden');
            modeGun.classList.add('hidden');
            tabCam.className = "px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 bg-indigo-600 text-white shadow-md shadow-indigo-500/30";
            tabGun.className = "px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 text-slate-300 hover:text-white hover:bg-white/5";
        } else {
            modeCam.classList.add('hidden');
            modeGun.classList.remove('hidden');
            tabGun.className = "px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 bg-indigo-600 text-white shadow-md shadow-indigo-500/30";
            tabCam.className = "px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 text-slate-300 hover:text-white hover:bg-white/5";
            
            stopCamera();
            const input = document.getElementById('codigo-input');
            if (input) setTimeout(() => input.focus(), 150);
        }
    }

    // ================= HTML5-QRCODE CAMERA INTEGRATION =================
    let html5QrCode = null;
    let isCameraActive = false;
    let currentCameraFacing = "environment";
    let isProcessingScan = false;

    function startCamera() {
        const placeholder = document.getElementById('camera-placeholder');
        const btnToggle = document.getElementById('btn-toggle-cam');
        const btnSwitch = document.getElementById('btn-switch-cam');
        const laser = document.getElementById('scan-laser');

        if (typeof Html5Qrcode === 'undefined') {
            alert('La librería del escáner aún se está cargando. Verifique su conexión a internet.');
            return;
        }

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("qr-reader");
        }

        const config = {
            fps: 15,
            qrbox: { width: 240, height: 240 },
            aspectRatio: 1.0,
            formatsToSupport: [
                Html5QrcodeSupportedFormats.QR_CODE,
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8,
                Html5QrcodeSupportedFormats.UPC_A,
            ]
        };

        html5QrCode.start(
            { facingMode: currentCameraFacing },
            config,
            onScanSuccess,
            onScanFailure
        ).then(() => {
            isCameraActive = true;
            if (placeholder) placeholder.style.display = 'none';
            if (btnToggle) {
                btnToggle.classList.remove('hidden');
                btnToggle.classList.add('inline-flex');
                btnToggle.innerHTML = '<i class="fa-solid fa-stop"></i><span>Detener Cámara</span>';
            }
            if (btnSwitch) {
                btnSwitch.classList.remove('hidden');
                btnSwitch.classList.add('inline-flex');
            }
            if (laser) laser.classList.remove('hidden');
        }).catch(err => {
            console.error("Error al iniciar cámara:", err);
            alert("No se pudo iniciar la cámara. Conceda permiso de cámara al navegador o use la pestaña 'Pistola / Lector USB'.");
        });
    }

    function stopCamera() {
        if (html5QrCode && isCameraActive) {
            html5QrCode.stop().then(() => {
                isCameraActive = false;
                const placeholder = document.getElementById('camera-placeholder');
                const btnToggle = document.getElementById('btn-toggle-cam');
                const btnSwitch = document.getElementById('btn-switch-cam');
                const laser = document.getElementById('scan-laser');
                if (placeholder) placeholder.style.display = 'block';
                if (btnToggle) {
                    btnToggle.innerHTML = '<i class="fa-solid fa-play"></i><span>Iniciar Cámara</span>';
                    btnToggle.classList.replace('bg-rose-600/80', 'bg-indigo-600');
                }
                if (btnSwitch) {
                    btnSwitch.classList.remove('inline-flex');
                    btnSwitch.classList.add('hidden');
                }
                if (laser) laser.classList.add('hidden');
            }).catch(err => console.error("Error al detener cámara:", err));
        }
    }

    function toggleCameraState() {
        if (isCameraActive) {
            stopCamera();
        } else {
            startCamera();
        }
    }

    function switchCameraFacing() {
        if (!isCameraActive) return;
        currentCameraFacing = currentCameraFacing === "environment" ? "user" : "environment";
        stopCamera();
        setTimeout(() => startCamera(), 300);
    }

    function onScanSuccess(decodedText, decodedResult) {
        if (isProcessingScan) return;
        procesarEscaneoCodigo(decodedText);
    }

    function onScanFailure(error) {
        // Ignored during normal viewfinder seeking
    }

    function actualizarActividadEscaneo(val) {
        const inputHidden = document.getElementById('form-scan-actividad');
        if (inputHidden) {
            inputHidden.value = val;
        }
    }

    // ================= UNIFIED REAL-TIME SCANNING ENGINE (CAMERA & BARCODE GUN) =================
    function procesarEscaneoCodigo(codigoRaw) {
        const codigo = String(codigoRaw || '').trim();
        if (!codigo || isProcessingScan) return;

        isProcessingScan = true;

        // Ocultar banner anterior si existiera
        const sessionBanner = document.getElementById('session-scan-banner');
        if (sessionBanner) sessionBanner.style.display = 'none';

        // Mostrar indicador de carga en live-scan-card
        const card = document.getElementById('live-scan-card');
        if (card) {
            card.classList.remove('hidden');
            card.innerHTML = `
                <div class="relative overflow-hidden rounded-3xl p-5 shadow-2xl border-2 border-indigo-400/80 bg-slate-900 text-white flex items-center gap-4 animate-pulse">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-xl flex-shrink-0 border border-indigo-500/30">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                    </div>
                    <div>
                        <p class="text-xs sm:text-sm font-bold text-white">Consultando estudiante: <span class="font-mono text-indigo-300 font-black">${codigo}</span>...</p>
                        <p class="text-[11px] text-slate-300 mt-0.5">Identificando si corresponde a Ingreso o Salida...</p>
                    </div>
                </div>
            `;
        }

        const csrfToken = '{{ csrf_token() }}';

        fetch("{{ route('asistencia.estudiantes.escanear') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({ 
                codigo: codigo,
                actividad: document.getElementById('scan-actividad-selector') ? document.getElementById('scan-actividad-selector').value : 'AUTOMATICO'
            })
        })
        .then(async response => {
            const data = await response.json();
            if (response.ok && data.success) {
                if (data.tipo === 'SALIDA') {
                    playAudioBeep('success');
                    triggerHaptic('success');
                } else if (data.estado === 'TARDANZA') {
                    playAudioBeep('warning');
                    triggerHaptic('warning');
                } else {
                    playAudioBeep('success');
                    triggerHaptic('success');
                }
                renderScanCardSuccess(data);
                actualizarTablaAsistenciaEnVivo(data);
            } else {
                playAudioBeep('warning');
                triggerHaptic('warning');
                renderScanCardError(data.message || 'No se encontró ningún estudiante con el código o DNI escaneado.');
            }
        })
        .catch(err => {
            console.error("Error al procesar asistencia:", err);
            playAudioBeep('warning');
            triggerHaptic('warning');
            renderScanCardError('Error de comunicación con el servidor al registrar asistencia.');
        })
        .finally(() => {
            const input = document.getElementById('codigo-input');
            if (input && !document.getElementById('mode-gun').classList.contains('hidden')) {
                input.value = '';
                input.focus();
            }

            const isContinuous = document.getElementById('continuous-scan-check')?.checked;
            const cooldown = isContinuous ? 1500 : 2500;
            setTimeout(() => {
                isProcessingScan = false;
            }, cooldown);
        });
    }

    function renderScanCardSuccess(data) {
        const card = document.getElementById('live-scan-card');
        if (!card) return;

        const est = data.estudiante || {};
        const tipo = data.tipo || 'INGRESO';
        const estado = data.estado || 'PUNTUAL';
        const isSalida = tipo === 'SALIDA';
        const isTardanza = estado === 'TARDANZA';
        const isDuplicado = tipo === 'DUPLICADO_PREVENIDO';
        const isCompleto = tipo === 'COMPLETO';

        // Determinar temas visuales y mensajes claros de movimiento
        let themeBorder = 'border-emerald-400 shadow-emerald-500/25';
        let themeBg = 'bg-gradient-to-r from-emerald-950 via-slate-900 to-slate-900';
        let pingColor = 'bg-emerald-400';
        let dotColor = 'bg-emerald-500';
        let titleColor = 'text-emerald-300';
        let avatarBg = 'bg-emerald-600 ring-4 ring-emerald-400/30';
        let movementHeading = '🟢 INGRESO REGISTRADO — ESTUDIANTE INGRESANDO (PUNTUAL)';
        let actionPill = `
            <div class="mt-3 px-3 py-2 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 font-black text-xs sm:text-sm flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <i class="fa-solid fa-person-walking-arrow-right text-base animate-pulse"></i>
                    <span>MOVIMIENTO: <strong>INGRESANDO AL COLEGIO</strong></span>
                </span>
                <span class="px-2.5 py-0.5 rounded-lg bg-emerald-500 text-slate-950 font-black text-[11px] uppercase tracking-wide">Puntual</span>
            </div>
        `;

        if (isSalida) {
            themeBorder = 'border-cyan-400 shadow-cyan-500/25';
            themeBg = 'bg-gradient-to-r from-blue-950 via-slate-900 to-indigo-950';
            pingColor = 'bg-cyan-400';
            dotColor = 'bg-cyan-500';
            titleColor = 'text-cyan-300';
            avatarBg = 'bg-blue-600 ring-4 ring-cyan-400/30';
            movementHeading = '🚪 SALIDA REGISTRADA — ESTUDIANTE SALIENDO DEL COLEGIO';
            actionPill = `
                <div class="mt-3 px-3 py-2 rounded-xl bg-cyan-500/20 border border-cyan-500/40 text-cyan-300 font-black text-xs sm:text-sm flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-person-walking-dashed-line-arrow-right text-base animate-pulse"></i>
                        <span>MOVIMIENTO: <strong>SALIENDO DEL COLEGIO</strong></span>
                    </span>
                    <span class="px-2.5 py-0.5 rounded-lg bg-cyan-400 text-slate-950 font-black text-[11px] uppercase tracking-wide">Salida OK</span>
                </div>
            `;
        } else if (isTardanza) {
            themeBorder = 'border-amber-400 shadow-amber-500/25';
            themeBg = 'bg-gradient-to-r from-amber-950 via-slate-900 to-slate-900';
            pingColor = 'bg-amber-400';
            dotColor = 'bg-amber-500';
            titleColor = 'text-amber-300';
            avatarBg = 'bg-amber-600 ring-4 ring-amber-400/30';
            movementHeading = '⚠️ INGRESO REGISTRADO — ESTUDIANTE INGRESANDO (TARDANZA)';
            actionPill = `
                <div class="mt-3 px-3 py-2 rounded-xl bg-amber-500/20 border border-amber-500/40 text-amber-300 font-black text-xs sm:text-sm flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-base animate-pulse"></i>
                        <span>MOVIMIENTO: <strong>INGRESANDO AL COLEGIO</strong></span>
                    </span>
                    <span class="px-2.5 py-0.5 rounded-lg bg-amber-500 text-slate-950 font-black text-[11px] uppercase tracking-wide">Tardanza</span>
                </div>
            `;
        } else if (isDuplicado) {
            themeBorder = 'border-indigo-400 shadow-indigo-500/25';
            themeBg = 'bg-gradient-to-r from-indigo-950 via-slate-900 to-slate-900';
            pingColor = 'bg-indigo-400';
            dotColor = 'bg-indigo-500';
            titleColor = 'text-indigo-300';
            avatarBg = 'bg-indigo-600 ring-4 ring-indigo-400/30';
            movementHeading = 'ℹ️ INGRESO PREVIO — EL ESTUDIANTE YA INGRESÓ';
            actionPill = `
                <div class="mt-3 px-3 py-2 rounded-xl bg-indigo-500/20 border border-indigo-500/40 text-indigo-300 font-black text-xs sm:text-sm flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-base"></i>
                        <span>ESTADO: <strong>YA REGISTRÓ INGRESO A LAS ${data.hora || ''}</strong></span>
                    </span>
                    <span class="px-2.5 py-0.5 rounded-lg bg-indigo-500 text-white font-black text-[11px] uppercase tracking-wide">En Colegio</span>
                </div>
            `;
        } else if (isCompleto) {
            themeBorder = 'border-purple-400 shadow-purple-500/25';
            themeBg = 'bg-gradient-to-r from-purple-950 via-slate-900 to-slate-900';
            pingColor = 'bg-purple-400';
            dotColor = 'bg-purple-500';
            titleColor = 'text-purple-300';
            avatarBg = 'bg-purple-600 ring-4 ring-purple-400/30';
            movementHeading = '✅ JORNADA COMPLETA — INGRESO Y SALIDA YA COMPLETADOS';
            actionPill = `
                <div class="mt-3 px-3 py-2 rounded-xl bg-purple-500/20 border border-purple-500/40 text-purple-300 font-black text-xs sm:text-sm flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-award text-base"></i>
                        <span>ESTADO: <strong>ASISTENCIA Y RETIRO FINALIZADOS</strong></span>
                    </span>
                    <span class="px-2.5 py-0.5 rounded-lg bg-purple-500 text-white font-black text-[11px] uppercase tracking-wide">Completado</span>
                </div>
            `;
        }

        const inicial = String(est.nombre || 'E').trim().charAt(0).toUpperCase();

        card.classList.remove('hidden');
        card.innerHTML = `
            <div class="relative overflow-hidden rounded-3xl p-5 shadow-2xl border-2 ${themeBorder} ${themeBg} text-left transition-all">
                <!-- Header con indicador de pulso y hora -->
                <div class="flex items-center justify-between pb-3 border-b border-white/15">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-3.5 w-3.5 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 ${pingColor}"></span>
                            <span class="relative inline-flex rounded-full h-3.5 w-3.5 ${dotColor}"></span>
                        </span>
                        <span class="text-xs sm:text-sm font-black uppercase tracking-wider ${titleColor}">
                            ${movementHeading}
                        </span>
                    </div>
                    <span class="px-3 py-1 rounded-xl text-xs font-black font-mono shadow-sm bg-white/20 text-white border border-white/20">
                        🕒 ${data.hora || ''}
                    </span>
                </div>

                <!-- Datos del Estudiante -->
                <div class="mt-4 flex items-center gap-4">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl flex items-center justify-center text-2xl font-black text-white shadow-lg flex-shrink-0 ${avatarBg}">
                        ${inicial}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-indigo-300">Estudiante Identificado</p>
                        <h3 class="text-base sm:text-xl font-black text-white truncate leading-tight mt-0.5">
                            ${est.nombre || 'Estudiante'}
                        </h3>
                        <div class="flex flex-wrap items-center gap-1.5 mt-2 text-xs">
                            <span class="bg-black/40 text-slate-200 px-2.5 py-0.5 rounded-lg font-mono font-bold border border-white/10">
                                DNI: ${est.dni || '-'}
                            </span>
                            <span class="bg-white/15 text-white px-2.5 py-0.5 rounded-lg font-bold">
                                🎓 ${est.grado || ''}
                            </span>
                            ${est.nivel ? `<span class="bg-white/10 text-slate-300 px-2 py-0.5 rounded-lg font-semibold">${est.nivel}</span>` : ''}
                            <span class="bg-white/15 text-indigo-200 px-2.5 py-0.5 rounded-lg font-bold">
                                Turno ${est.turno || 'Mañana'}
                            </span>
                            ${data.actividad_nombre ? `<span class="bg-indigo-500/30 text-indigo-200 px-2.5 py-0.5 rounded-lg font-bold border border-indigo-400/30">🏆 ${data.actividad_nombre}</span>` : ''}
                        </div>
                    </div>
                </div>

                <!-- Banner Claro del Movimiento: Ingresando o Saliendo -->
                ${actionPill}

                <!-- Mensaje explicativo y botón cerrar -->
                <div class="mt-3.5 pt-2.5 border-t border-white/10 flex items-center justify-between text-xs text-slate-300">
                    <p class="font-medium text-[11px] sm:text-xs">
                        ${data.mensaje || ''}
                    </p>
                    <button type="button" onclick="document.getElementById('live-scan-card').classList.add('hidden')" class="text-white/70 hover:text-white text-xs underline font-semibold transition-colors">
                        Ocultar
                    </button>
                </div>
            </div>
        `;
    }

    function renderScanCardError(msg) {
        const card = document.getElementById('live-scan-card');
        if (!card) return;
        card.classList.remove('hidden');
        card.innerHTML = `
            <div class="relative overflow-hidden rounded-3xl p-5 shadow-2xl border-2 border-rose-500 bg-gradient-to-r from-rose-950 via-slate-900 to-slate-900 text-left transition-all">
                <div class="flex items-center justify-between pb-3 border-b border-white/15">
                    <div class="flex items-center gap-2 text-rose-400 font-black text-xs sm:text-sm uppercase tracking-wider">
                        <i class="fa-solid fa-circle-xmark text-base"></i>
                        <span>ERROR AL ESCANEAR — CÓDIGO NO IDENTIFICADO</span>
                    </div>
                    <button type="button" onclick="document.getElementById('live-scan-card').classList.add('hidden')" class="text-white/70 hover:text-white text-xs underline font-semibold">
                        Cerrar
                    </button>
                </div>
                <div class="mt-3 flex items-center gap-3 text-rose-200">
                    <div class="w-12 h-12 rounded-2xl bg-rose-600/30 text-rose-400 flex items-center justify-center text-xl flex-shrink-0 border border-rose-500/40">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h4 class="text-sm sm:text-base font-bold text-white">${msg}</h4>
                        <p class="text-xs text-rose-300/80 mt-0.5">Verifique que el alumno pertenezca a la institución o que el código de barras/QR sea válido.</p>
                    </div>
                </div>
            </div>
        `;
    }

    // ================= ACTUALIZACIÓN EN VIVO DE LA TABLA =================
    function actualizarTablaAsistenciaEnVivo(data) {
        if (!data || !data.estudiante || !data.estudiante.dni) return;
        const dni = String(data.estudiante.dni).trim();
        const row = document.getElementById(`asistencia-row-${dni}`);

        const nombreEst = (data.estudiante.nombre || 'Estudiante').replace(/'/g, "\\'");
        const horaCorta = (data.hora || '').substring(0, 5);

        if (row) {
            if (data.tipo === 'INGRESO') {
                const cellHoraIngreso = row.querySelector('.cell-hora-ingreso');
                if (cellHoraIngreso) {
                    cellHoraIngreso.innerHTML = `
                        <div class="font-mono text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <i class="fa-regular fa-clock text-slate-400"></i>
                            ${data.hora}
                        </div>
                    `;
                }
                const cellHoraSalida = row.querySelector('.cell-hora-salida');
                if (cellHoraSalida && (cellHoraSalida.textContent.includes('--:--') || cellHoraSalida.textContent.includes('No registró'))) {
                    cellHoraSalida.innerHTML = `
                        <div class="flex items-center gap-1.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                                En clase
                            </span>
                            <button type="button" 
                                    onclick="abrirModalHoraSalida(${data.asistencia_id || 0}, '${nombreEst}', '', '')"
                                    title="Registrar o corregir salida manual"
                                    class="p-1 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition-colors">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                            </button>
                        </div>
                    `;
                }
                const cellEstado = row.querySelector('.cell-estado');
                if (cellEstado) {
                    if (data.estado === 'TARDANZA') {
                        cellEstado.innerHTML = `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200"><i class="fa-solid fa-triangle-exclamation text-amber-600"></i> Tardanza</span>`;
                    } else {
                        cellEstado.innerHTML = `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200"><i class="fa-solid fa-circle-check text-emerald-600"></i> Puntual</span>`;
                    }
                }
                row.classList.add('bg-emerald-50');
                setTimeout(() => row.classList.remove('bg-emerald-50'), 3000);
            } else if (data.tipo === 'SALIDA') {
                const cellHoraSalida = row.querySelector('.cell-hora-salida');
                if (cellHoraSalida) {
                    cellHoraSalida.innerHTML = `
                        <div class="flex items-center gap-1.5">
                            <div class="font-mono text-xs font-bold text-slate-800 flex items-center gap-1">
                                <i class="fa-solid fa-arrow-right-from-bracket text-emerald-500"></i>
                                ${data.hora}
                            </div>
                            <button type="button" 
                                    onclick="abrirModalHoraSalida(${data.asistencia_id || 0}, '${nombreEst}', '${horaCorta}', '')"
                                    title="Corregir o anular hora de salida"
                                    class="p-1 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition-colors">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                            </button>
                        </div>
                    `;
                }
                row.classList.add('bg-cyan-50');
                setTimeout(() => row.classList.remove('bg-cyan-50'), 3000);
            }
        } else {
            // Si la fila no está en la tabla (por paginación o nuevo ingreso), se inserta al inicio
            const tbody = document.getElementById('tabla-asistencias-body');
            if (tbody) {
                const emptyRow = tbody.querySelector('tr td[colspan]');
                if (emptyRow) emptyRow.closest('tr').remove();

                const newTr = document.createElement('tr');
                newTr.id = `asistencia-row-${dni}`;
                newTr.className = "hover:bg-slate-50/80 transition-colors bg-emerald-50";

                const estadoBadge = data.tipo === 'SALIDA' 
                    ? '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200"><i class="fa-solid fa-arrow-right-from-bracket text-blue-600"></i> Salida</span>'
                    : (data.estado === 'TARDANZA'
                        ? '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200"><i class="fa-solid fa-triangle-exclamation text-amber-600"></i> Tardanza</span>'
                        : '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200"><i class="fa-solid fa-circle-check text-emerald-600"></i> Puntual</span>'
                    );

                const horaIngresoHtml = data.tipo === 'INGRESO' 
                    ? `<div class="font-mono text-xs font-bold text-slate-800 flex items-center gap-1.5"><i class="fa-regular fa-clock text-slate-400"></i> ${data.hora}</div>` 
                    : `<span class="text-xs text-slate-400 italic">No registró</span>`;

                const horaSalidaHtml = data.tipo === 'SALIDA'
                    ? `<div class="flex items-center gap-1.5"><div class="font-mono text-xs font-bold text-slate-800 flex items-center gap-1"><i class="fa-solid fa-arrow-right-from-bracket text-emerald-500"></i> ${data.hora}</div></div>`
                    : `<div class="flex items-center gap-1.5"><span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700">En clase</span></div>`;

                newTr.innerHTML = `
                    <td class="py-3.5 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs uppercase flex-shrink-0">
                                ${(data.estudiante.nombre || 'E').substring(0, 1)}
                            </div>
                            <div>
                                <p class="font-bold text-slate-800 text-sm leading-tight">${data.estudiante.nombre || 'Estudiante'}</p>
                                <p class="text-xs text-slate-400 font-mono mt-0.5">DNI: ${data.estudiante.dni}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-xs font-semibold">
                            ${data.estudiante.grado || ''}
                        </span>
                        <span class="text-[11px] text-slate-400 block">${data.estudiante.nivel || ''}</span>
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold border bg-amber-50 text-amber-700 border-amber-200">
                            ${data.estudiante.turno || 'Mañana'}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 cell-hora-ingreso">${horaIngresoHtml}</td>
                    <td class="py-3.5 px-4 cell-hora-salida">${horaSalidaHtml}</td>
                    <td class="py-3.5 px-4 cell-estado">${estadoBadge}</td>
                    <td class="py-3.5 px-4 max-w-xs text-xs text-slate-400">Escaneado en portería</td>
                    <td class="py-3.5 px-4 text-right">
                        <span class="text-emerald-600 text-xs font-bold">En vivo</span>
                    </td>
                `;
                tbody.insertBefore(newTr, tbody.firstChild);
                setTimeout(() => newTr.classList.remove('bg-emerald-50'), 3000);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('codigo-input');
        const formScan = document.getElementById('form-scan');

        // Interceptar formulario de escáner USB / Pistola para escaneo fluido sin recargar página
        if (formScan) {
            formScan.addEventListener('submit', function (e) {
                e.preventDefault();
                if (input && input.value.trim()) {
                    const codigo = input.value.trim();
                    procesarEscaneoCodigo(codigo);
                }
            });
        }

        if (input && !document.getElementById('mode-gun').classList.contains('hidden')) {
            input.focus();
            
            document.addEventListener('click', function(e) {
                if (!['INPUT', 'BUTTON', 'A', 'SELECT', 'TEXTAREA'].includes(e.target.tagName)) {
                    input.focus();
                }
            });
        }

        @if(session('success'))
            playAudioBeep('success');
            triggerHaptic('success');
        @endif
    });
</script>
@endsection
