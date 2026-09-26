<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acta de Cierre de Sufragio y Cómputo General - APAFA {{ $comicio->año_electoral }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @media print {
            body { background: white !important; font-size: 11px !important; margin: 0; padding: 8mm; }
            .no-print { display: none !important; }
            .print-border { border: 2px solid #0f172a !important; }
            @page { size: A4 portrait; margin: 8mm; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans p-4 sm:p-8 flex justify-center">

    <div class="max-w-4xl w-full bg-white p-8 sm:p-12 rounded-2xl shadow-lg border border-slate-200 relative print-border">
        
        <!-- Barra de Acciones (No Imprimible) -->
        <div class="no-print mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-4">
            <a href="{{ route('elecciones.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs flex items-center gap-1.5 transition-all">
                <i class="bi bi-arrow-left"></i> Volver a Comicios
            </a>
            
            <div class="flex items-center gap-2">
                <button type="button" onclick="document.getElementById('modalComiteElectoral').classList.remove('hidden')" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-xs shadow-sm flex items-center gap-1.5 transition-all">
                    <i class="bi bi-person-gear"></i> Datos del Comité Electoral
                </button>
                <button onclick="window.print()" class="px-5 py-2.5 bg-purple-700 hover:bg-purple-800 text-white rounded-xl font-bold text-xs shadow-md flex items-center gap-2 transition-all">
                    <i class="bi bi-printer-fill"></i> Imprimir Acta Oficial
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="no-print mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center gap-2">
                <i class="bi bi-check-circle-fill text-emerald-600"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Encabezado Oficial -->
        <div class="border-b-2 border-slate-900 pb-5 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-slate-50 rounded-xl flex items-center justify-center p-2 border border-slate-200">
                    <img src="{{ !empty($empresa->foto_institucion) ? asset($empresa->foto_institucion) : (!empty($empresa->logo_url) ? asset('storage/' . $empresa->logo_url) : asset('img/logo-default.png')) }}" 
                         onerror="this.src='https://ui-avatars.com/api/?name=APAFA&background=6b21a8&color=fff&size=128'" 
                         class="max-h-full max-w-full object-contain" alt="Logo">
                </div>
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-slate-500 font-extrabold">Ministerio de Educación • República del Perú</h2>
                    <h1 class="text-lg font-black text-slate-900 uppercase">{{ $empresa->nombre ?? 'I.E. EMBLEMÁTICA NERY GARCÍA ZÁRATE' }}</h1>
                    <p class="text-xs text-purple-900 font-bold">COMITÉ ELECTORAL DE LA ASOCIACIÓN DE PADRES DE FAMILIA</p>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-1 bg-purple-50 text-purple-900 border border-purple-300 rounded-lg text-xs font-black">
                    ACTA OFICIAL N.º {{ $comicio->año_electoral }}-01
                </span>
                <p class="text-[11px] font-mono text-slate-500 mt-1">Ley N.º 28628 • D.S. N.º 007-2026-MINEDU</p>
            </div>
        </div>

        <!-- Título del Acta -->
        <div class="text-center my-6">
            <h2 class="text-xl sm:text-2xl font-black uppercase text-slate-900 tracking-wide underline underline-offset-8 decoration-purple-600">
                Acta de Cierre de Sufragio y Cómputo General
            </h2>
            <p class="text-xs font-bold text-slate-600 mt-3">
                PROCESO ELECTORAL {{ $comicio->año_electoral }} • PERIODO DE MANDATO: {{ $comicio->bienio_electo }} (MANDATO DE 2 AÑOS)
            </p>
        </div>

        <!-- Cuerpo del Acta -->
        <div class="text-slate-800 text-xs leading-relaxed space-y-4 text-justify">
            <p>
                En las instalaciones de la <strong>{{ $empresa->nombre ?? 'I.E. Emblemática Nery García Zárate' }}</strong>, siendo las <strong>{{ \Carbon\Carbon::now()->format('H:i') }}</strong> horas del día <strong>{{ \Carbon\Carbon::parse($comicio->fecha_elecciones)->isoFormat('LL') }}</strong>, los suscritos miembros del <strong>Comité Electoral</strong>, debidamente acreditados en Asamblea General conforme al Artículo 48 del Reglamento de la Ley N.º 28628 y el Decreto Supremo N.º 007-2026-MINEDU, proceden a realizar el <strong>Cierre de Sufragio, Escrutinio y Cómputo Final</strong> de las elecciones generales de Junta Directiva y Consejo de Vigilancia.
            </p>

            <!-- 1. Cuadro Resumen del Padrón y Sufragio -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 text-center my-4">
                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                    <p class="text-[10px] font-bold text-slate-500 uppercase">Padrón Electoral</p>
                    <p class="text-lg font-black text-slate-800 mt-0.5">{{ $totalElectores }}</p>
                    <p class="text-[9px] text-slate-400">Asociados Hábiles</p>
                </div>

                <div class="p-2.5 bg-emerald-50 rounded-xl border border-emerald-200">
                    <p class="text-[10px] font-bold text-emerald-700 uppercase">Total Votantes</p>
                    <p class="text-lg font-black text-emerald-800 mt-0.5">{{ $totalVotaron }}</p>
                    <p class="text-[9px] text-emerald-600">{{ $porcentaje }}% Asistencia</p>
                </div>

                <div class="p-2.5 bg-blue-50 rounded-xl border border-blue-200">
                    <p class="text-[10px] font-bold text-blue-700 uppercase">Votos Válidos</p>
                    <p class="text-lg font-black text-blue-800 mt-0.5">{{ $votosValidos }}</p>
                    <p class="text-[9px] text-blue-600">Por Listas</p>
                </div>

                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                    <p class="text-[10px] font-bold text-slate-600 uppercase">Blancos / Nulos</p>
                    <p class="text-lg font-black text-slate-700 mt-0.5">{{ $votosBlancos + $votosNulos }}</p>
                    <p class="text-[9px] text-slate-400">Bl: {{ $votosBlancos }} | Nul: {{ $votosNulos }}</p>
                </div>

                <div class="p-2.5 bg-amber-50 rounded-xl border border-amber-200 col-span-2 sm:col-span-1">
                    <p class="text-[10px] font-bold text-amber-700 uppercase">Ausentismo</p>
                    <p class="text-lg font-black text-amber-800 mt-0.5">{{ $totalAusentes }}</p>
                    <p class="text-[9px] text-amber-600">No sufragaron</p>
                </div>
            </div>

            <!-- 2. Cuadro de Cómputo por Listas Electorales -->
            <div class="space-y-2 mt-4">
                <h3 class="font-bold text-slate-900 uppercase text-[11px] flex items-center gap-1.5 border-b border-slate-200 pb-1">
                    <i class="bi bi-bar-chart-fill text-purple-700"></i> I. Resultados Oficiales del Cómputo General de Votos
                </h3>

                <table class="w-full text-left text-xs border border-slate-300 rounded-lg overflow-hidden">
                    <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] border-b border-slate-300">
                        <tr>
                            <th class="p-2 border-r border-slate-300 text-center w-14">N° Lista</th>
                            <th class="p-2 border-r border-slate-300">Nombre de la Lista / Lema de Campaña</th>
                            <th class="p-2 border-r border-slate-300 text-center w-24">Votos Obtenidos</th>
                            <th class="p-2 border-r border-slate-300 text-center w-20">% Válidos</th>
                            <th class="p-2 text-center w-28">Resultado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-700">
                        @forelse($comicio->listas as $lista)
                            @php
                                $porcLista = $votosValidos > 0 ? round(($lista->total_votos / $votosValidos) * 100, 1) : 0;
                                $esGanadora = ($listaGanadora && $listaGanadora->id === $lista->id);
                            @endphp
                            <tr class="{{ $esGanadora ? 'bg-purple-50/70 font-semibold' : '' }}">
                                <td class="p-2 border-r border-slate-200 text-center font-bold">
                                    <span class="inline-block w-6 h-6 rounded-full bg-slate-900 text-white leading-6 text-[11px]">
                                        {{ $lista->numero_lista }}
                                    </span>
                                </td>
                                <td class="p-2 border-r border-slate-200">
                                    <div class="font-bold text-slate-900 uppercase">{{ $lista->nombre_lista }}</div>
                                    @if($lista->eslogan)
                                        <div class="text-[10px] text-slate-500 italic">"{{ $lista->eslogan }}"</div>
                                    @endif
                                </td>
                                <td class="p-2 border-r border-slate-200 text-center font-mono font-black text-sm {{ $esGanadora ? 'text-purple-900' : 'text-slate-800' }}">
                                    {{ $lista->total_votos }}
                                </td>
                                <td class="p-2 border-r border-slate-200 text-center font-mono text-xs">
                                    {{ $porcLista }}%
                                </td>
                                <td class="p-2 text-center">
                                    @if($esGanadora)
                                        <span class="inline-block px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-300 text-[10px] font-black uppercase">
                                            GANADORA
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-[10px]">No electa</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-4 text-center text-slate-400">No hay listas inscritas en el comicio.</td>
                            </tr>
                        @endforelse
                        <tr class="bg-slate-50 font-bold border-t-2 border-slate-300 text-[11px]">
                            <td colspan="2" class="p-2 text-right uppercase border-r border-slate-300">Total Votos Emitidos (Válidos + Blancos + Nulos):</td>
                            <td class="p-2 text-center font-mono font-black border-r border-slate-300">{{ $totalVotaron }}</td>
                            <td class="p-2 text-center font-mono border-r border-slate-300">100%</td>
                            <td class="p-2 text-center text-[10px] text-emerald-700">ACTA CONFORME</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 3. Proclamación de la Lista Ganadora y Nómina de Ganadores -->
            @if($listaGanadora)
            <div class="space-y-3 mt-6">
                <div class="p-3.5 bg-purple-50 rounded-xl border-2 border-purple-300 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-700">FÓRMULA ELECTORAL PROCLAMADA GANADORA:</span>
                        <h4 class="text-base font-black text-purple-950 uppercase mt-0.5">
                            LISTA N° {{ $listaGanadora->numero_lista }} — "{{ $listaGanadora->nombre_lista }}"
                        </h4>
                        <p class="text-[11px] text-purple-800 mt-0.5">
                            Habiendo alcanzado la mayoría de sufragios válidos emitidos en votación universal, secreta y directa.
                        </p>
                    </div>
                    <div class="text-right sm:border-l sm:border-purple-200 sm:pl-4">
                        <span class="text-[10px] font-bold text-purple-600 uppercase block">Votos Obtenidos:</span>
                        <span class="text-2xl font-black text-purple-900 font-mono">{{ $listaGanadora->total_votos }}</span>
                    </div>
                </div>

                <h3 class="font-bold text-slate-900 uppercase text-[11px] flex items-center gap-1.5 border-b border-slate-200 pb-1 mt-4">
                    <i class="bi bi-people-fill text-purple-700"></i> II. Nómina de Ganadores Electos para el Consejo Directivo y Consejo de Vigilancia
                </h3>
                <p class="text-[11px] text-slate-500">
                    Asociados que asumen funciones formalmente para el mandato de dos (2) años conforme a los Arts. 43 y 48 de la Ley N.º 28628:
                </p>

                <table class="w-full text-left text-xs border border-slate-300 rounded-lg overflow-hidden">
                    <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] border-b border-slate-300">
                        <tr>
                            <th class="p-2 border-r border-slate-300">Cargo Oficial Asignado</th>
                            <th class="p-2 border-r border-slate-300">Apellidos y Nombres del Ganador</th>
                            <th class="p-2 border-r border-slate-300 text-center w-24">DNI</th>
                            <th class="p-2 border-r border-slate-300">Estudiante Vinculado</th>
                            <th class="p-2 text-center w-28">Periodo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-700">
                        @forelse($listaGanadora->candidatos as $c)
                            @php
                                $mapCargos = [
                                    'PRESIDENTE' => 'Presidente del Consejo Directivo',
                                    'VICEPRESIDENTE' => 'Vicepresidente',
                                    'SECRETARIO' => 'Secretario(a)',
                                    'TESORERO' => 'Tesorero(a)',
                                    'VOCAL_1' => '1er Vocal',
                                    'VOCAL_2' => '2do Vocal',
                                    'VOCAL_3' => '3er Vocal',
                                    'CONSEJO_VIGILANCIA_PRESIDENTE' => 'Presidente Consejo de Vigilancia',
                                    'CONSEJO_VIGILANCIA_SECRETARIO' => 'Secretario Consejo de Vigilancia',
                                    'CONSEJO_VIGILANCIA_VOCAL' => 'Vocal Consejo de Vigilancia',
                                ];
                                $cargoTexto = $mapCargos[$c->cargo_postula] ?? str_replace('_', ' ', $c->cargo_postula);
                                $esVigilancia = str_contains($c->cargo_postula, 'VIGILANCIA');
                            @endphp
                            <tr class="{{ $loop->even ? 'bg-slate-50/50' : 'bg-white' }}">
                                <td class="p-2 border-r border-slate-200 font-bold text-slate-900">
                                    <span class="inline-flex items-center gap-1.5">
                                        <i class="bi {{ $esVigilancia ? 'bi-shield-check text-rose-600' : 'bi-award text-purple-700' }}"></i>
                                        {{ $cargoTexto }}
                                    </span>
                                </td>
                                <td class="p-2 border-r border-slate-200 font-bold text-slate-900 uppercase">
                                    {{ $c->padreFamilia->nombre_completo ?? ($c->padreFamilia->padre_ape_pat . ' ' . $c->padreFamilia->padre_nombres) }}
                                </td>
                                <td class="p-2 border-r border-slate-200 text-center font-mono font-semibold">
                                    {{ $c->padreFamilia->padre_dni ?? $c->padreFamilia->dni }}
                                </td>
                                <td class="p-2 border-r border-slate-200 text-[11px] text-slate-600">
                                    @if($c->padreFamilia && $c->padreFamilia->estudiantes->count() > 0)
                                        @foreach($c->padreFamilia->estudiantes as $est)
                                            <span class="inline-block">{{ $est->nombres }} ({{ $est->grado }}° "{{ $est->seccion }}" {{ $est->nivel }})</span>{{ !$loop->last ? '; ' : '' }}
                                        @endforeach
                                    @else
                                        <span class="text-slate-400">Asociado Padrón</span>
                                    @endif
                                </td>
                                <td class="p-2 text-center font-semibold text-[10px] text-purple-900">
                                    {{ $comicio->bienio_electo }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-3 text-center text-slate-400 italic">No se registraron postulantes en la fórmula ganadora.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endif

            <p class="text-justify pt-2">
                Concluido el escrutinio general y no habiéndose presentado impugnaciones que alteren el resultado final, el Comité Electoral declara válida y legítima la elección, disponiendo la juramentación de estilo e informando a la Dirección de la I.E. y a la UGEL correspondiente para la expedición de la resolución de reconocimiento institucional.
            </p>
        </div>

        <!-- 4. Datos Oficiales y Firmas del Comité Electoral -->
        <div class="mt-12 pt-6 border-t-2 border-slate-800">
            <h4 class="text-center font-bold text-slate-700 uppercase text-[10px] tracking-widest mb-8">
                MIEMBROS DEL COMITÉ ELECTORAL DE LA ASOCIACIÓN DE PADRES DE FAMILIA (LEY N.° 28628)
            </h4>

            @php
                $presNombre = !empty($comicio->comite_presidente_nombre) ? $comicio->comite_presidente_nombre : 'PRESIDENTE(A) DEL COMITÉ ELECTORAL';
                $presDni = !empty($comicio->comite_presidente_dni) ? $comicio->comite_presidente_dni : 'DNI: ______________';

                $secNombre = !empty($comicio->comite_secretario_nombre) ? $comicio->comite_secretario_nombre : 'SECRETARIO(A) DEL COMITÉ ELECTORAL';
                $secDni = !empty($comicio->comite_secretario_dni) ? $comicio->comite_secretario_dni : 'DNI: ______________';

                $vocNombre = !empty($comicio->comite_vocal_nombre) ? $comicio->comite_vocal_nombre : 'VOCAL DEL COMITÉ ELECTORAL';
                $vocDni = !empty($comicio->comite_vocal_dni) ? $comicio->comite_vocal_dni : 'DNI: ______________';
            @endphp

            <div class="grid grid-cols-3 gap-6 text-center text-xs">
                <div class="flex flex-col items-center">
                    <div class="w-48 border-t border-slate-700 pt-2">
                        <p class="font-bold text-slate-900 uppercase text-[11px]">{{ $presNombre }}</p>
                        <p class="text-[10px] text-slate-500 font-mono">{{ $presDni }}</p>
                        <span class="inline-block mt-1 px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[9px] font-bold uppercase">
                            PRESIDENTE • COMITÉ ELECTORAL
                        </span>
                    </div>
                </div>

                <div class="flex flex-col items-center">
                    <div class="w-48 border-t border-slate-700 pt-2">
                        <p class="font-bold text-slate-900 uppercase text-[11px]">{{ $secNombre }}</p>
                        <p class="text-[10px] text-slate-500 font-mono">{{ $secDni }}</p>
                        <span class="inline-block mt-1 px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[9px] font-bold uppercase">
                            SECRETARIO • COMITÉ ELECTORAL
                        </span>
                    </div>
                </div>

                <div class="flex flex-col items-center">
                    <div class="w-48 border-t border-slate-700 pt-2">
                        <p class="font-bold text-slate-900 uppercase text-[11px]">{{ $vocNombre }}</p>
                        <p class="text-[10px] text-slate-500 font-mono">{{ $vocDni }}</p>
                        <span class="inline-block mt-1 px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[9px] font-bold uppercase">
                            VOCAL • COMITÉ ELECTORAL
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pie de Página Institucional -->
        <div class="mt-8 pt-4 border-t border-slate-200 flex items-center justify-between text-[10px] text-slate-400">
            <span>SIS-APAFA • Conforme a Ley N.º 28628 y D.S. N.º 007-2026-MINEDU</span>
            <span class="font-mono">ACTA-ESCRUTINIO-{{ $comicio->año_electoral }}-VOTO-UNIVERSAL</span>
        </div>

    </div>

    <!-- Modal para Actualizar Datos del Comité Electoral (No Imprimible) -->
    <div id="modalComiteElectoral" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm hidden p-4 no-print">
        <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-200 p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                        <i class="bi bi-person-gear text-amber-600"></i> Integrantes del Comité Electoral
                    </h3>
                    <p class="text-[11px] text-slate-400">Estos nombres y DNIs se imprimirán en las firmas oficiales del Acta</p>
                </div>
                <button type="button" onclick="document.getElementById('modalComiteElectoral').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>

            <form action="{{ route('elecciones.comite_electoral', $comicio->id) }}" method="POST" class="space-y-4 text-xs">
                @csrf
                
                <!-- Presidente del Comité -->
                <div class="p-3 bg-purple-50/60 rounded-xl border border-purple-100 space-y-2">
                    <span class="font-bold text-purple-900 uppercase text-[10px]">1. Presidente(a) del Comité Electoral</span>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-2">
                            <input type="text" name="comite_presidente_nombre" value="{{ $comicio->comite_presidente_nombre }}" placeholder="Apellidos y Nombres" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs bg-white">
                        </div>
                        <div>
                            <input type="text" name="comite_presidente_dni" value="{{ $comicio->comite_presidente_dni }}" placeholder="DNI (8 dígitos)" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-mono bg-white">
                        </div>
                    </div>
                </div>

                <!-- Secretario del Comité -->
                <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 space-y-2">
                    <span class="font-bold text-blue-900 uppercase text-[10px]">2. Secretario(a) del Comité Electoral</span>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-2">
                            <input type="text" name="comite_secretario_nombre" value="{{ $comicio->comite_secretario_nombre }}" placeholder="Apellidos y Nombres" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs bg-white">
                        </div>
                        <div>
                            <input type="text" name="comite_secretario_dni" value="{{ $comicio->comite_secretario_dni }}" placeholder="DNI (8 dígitos)" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-mono bg-white">
                        </div>
                    </div>
                </div>

                <!-- Vocal del Comité -->
                <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-100 space-y-2">
                    <span class="font-bold text-emerald-900 uppercase text-[10px]">3. Vocal del Comité Electoral</span>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-2">
                            <input type="text" name="comite_vocal_nombre" value="{{ $comicio->comite_vocal_nombre }}" placeholder="Apellidos y Nombres" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs bg-white">
                        </div>
                        <div>
                            <input type="text" name="comite_vocal_dni" value="{{ $comicio->comite_vocal_dni }}" placeholder="DNI (8 dígitos)" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-mono bg-white">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modalComiteElectoral').classList.add('hidden')" class="px-4 py-2 font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Cancelar</button>
                    <button type="submit" class="px-5 py-2 font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-xl shadow-md">
                        <i class="bi bi-check-lg mr-1"></i> Guardar Integrantes
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
