<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Asistencias y Cuotas para Padres de Familia - SIS-APAFA</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-full flex flex-col text-slate-800 antialiased selection:bg-indigo-600 selection:text-white">

    <!-- Header / Navbar -->
    <nav class="bg-white/80 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                <!-- Brand -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    @if(!empty($empresa->foto_institucion))
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-white p-1 border border-slate-200 flex items-center justify-center font-bold text-lg shadow-md group-hover:scale-105 transition-transform overflow-hidden">
                            <img src="{{ asset($empresa->foto_institucion) }}" alt="Logo" class="max-h-full max-w-full object-contain">
                        </div>
                    @else
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white flex items-center justify-center font-bold text-lg shadow-md shadow-indigo-600/20 group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                    @endif
                    <div>
                        <span class="text-base sm:text-lg font-black text-slate-900 tracking-tight block leading-tight">
                            SIS-APAFA
                        </span>
                        <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block truncate max-w-[200px] sm:max-w-none">
                            {{ $empresa->nombre ?? 'Institución Educativa' }}
                        </span>
                    </div>
                </a>

                <!-- Actions -->
                <div class="flex items-center gap-3">
                    <a href="#buscador" class="hidden sm:inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-indigo-600 transition-colors">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span>Consultar por DNI</span>
                    </a>

                    @auth
                        <a href="{{ route('dashboard') }}" 
                           class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs rounded-xl border border-indigo-200 transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-chart-pie"></i>
                            <span>Ir al Panel ({{ auth()->user()->name }})</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="px-4 py-2 bg-slate-900 hover:bg-indigo-600 text-white font-bold text-xs rounded-xl shadow-sm transition-all flex items-center gap-2">
                            <i class="fa-solid fa-lock"></i>
                            <span>Acceso Directiva / Personal</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Hero & Search Section -->
    <main class="flex-1 pb-16">
        
        <!-- Hero Header -->
        <section class="bg-gradient-to-b from-slate-900 via-indigo-950 to-slate-900 text-white pt-12 pb-20 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
            <!-- Glow background decor -->
            <div class="absolute -top-32 -left-32 w-80 h-80 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 -right-32 w-80 h-80 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>

            <div class="max-w-4xl mx-auto text-center space-y-4 relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Portal Ciudadano de Consulta Escolar y APAFA 2026
                </div>

                <h1 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight">
                    Consulta de Aportes, Asambleas <br class="hidden sm:inline">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-300 via-purple-300 to-pink-300">
                        y Asistencia de sus Hijos en Clases
                    </span>
                </h1>

                <p class="text-slate-300 text-xs sm:text-base max-w-2xl mx-auto font-normal">
                    Ingrese el número de <strong>DNI del Padre, Madre o Apoderado</strong> para verificar el estado de sus <strong>aportes y cuotas</strong>, sus asistencias convocadas a la APAFA y el registro diario de ingreso escolar de sus hijos.
                </p>

                <!-- Buscador de DNI Form -->
                <div id="buscador" class="pt-4 max-w-xl mx-auto">
                    <form action="{{ route('home') }}" method="GET" class="relative flex items-center">
                        <div class="absolute left-4 text-slate-400 text-lg pointer-events-none">
                            <i class="fa-solid fa-id-card"></i>
                        </div>
                        <input type="text" 
                               name="dni" 
                               value="{{ $dni }}" 
                               maxlength="8" 
                               pattern="[0-9]{8}"
                               placeholder="Ingrese su N° de DNI (8 dígitos)..." 
                               required 
                               autofocus
                               class="w-full pl-12 pr-36 py-4 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-white placeholder-slate-400 text-base sm:text-lg font-mono tracking-wider focus:outline-none focus:ring-4 focus:ring-indigo-500 focus:bg-white/20 transition-all shadow-xl">
                        <button type="submit" 
                                class="absolute right-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white font-bold text-xs sm:text-sm rounded-xl transition-all shadow-md flex items-center gap-2">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <span>Consultar</span>
                        </button>
                    </form>

                    <!-- Chips de prueba rápida -->
                    <div class="mt-4 flex flex-wrap items-center justify-center gap-1.5 text-xs text-slate-400">
                        <span class="text-[11px] font-semibold text-slate-400">Pruebas rápidas:</span>
                        @foreach($demoPadres as $dp)
                            <a href="{{ route('home', ['dni' => $dp->padre_dni]) }}" 
                               class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-indigo-200 border border-white/10 transition-colors font-mono text-[11px]">
                                {{ $dp->padre_dni }} ({{ $dp->padre_nombres }})
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- Container for Results or Info Cards -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-20">
            
            <!-- Error Alert -->
            @if($errorBusqueda)
                <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 mb-8 text-rose-800 text-sm shadow-md flex items-start gap-3">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 text-xl flex-shrink-0 mt-0.5"></i>
                    <div>
                        <h4 class="font-bold text-rose-900">No se encontraron resultados</h4>
                        <p class="text-xs text-rose-700 mt-1">{{ $errorBusqueda }}</p>
                    </div>
                </div>
            @endif

            <!-- RESULTADOS DEL PADRE ENCONTRADO -->
            @if($padre)
                <div class="space-y-8">
                    
                    <!-- Tarjeta Perfil del Padre de Familia -->
                    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-xl flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-2xl flex-shrink-0">
                                {{ substr($padre->padre_nombres ?? 'P', 0, 1) }}{{ substr($padre->padre_ape_pat ?? 'F', 0, 1) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2.5 flex-wrap">
                                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                                        {{ $padre->padre_nombres }} {{ $padre->padre_ape_pat }} {{ $padre->padre_ape_mat }}
                                    </h2>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        {{ $padre->cargo->nombre ?? 'PADRE DE FAMILIA ASOCIADO' }}
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        <i class="fa-solid fa-people-roof text-[10px]"></i> {{ $padre->parentesco_texto }}
                                    </span>

                                    <!-- Estado Habil / Deudor -->
                                    @if($statsAportes['esta_al_dia'])
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fa-solid fa-circle-check text-emerald-500 text-[10px]"></i>
                                            Asociado Hábil (Al Día)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <i class="fa-solid fa-triangle-exclamation text-amber-500 text-[10px]"></i>
                                            Cuotas Pendientes
                                        </span>
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-4 mt-2 text-xs text-slate-500">
                                    <span class="font-mono"><strong class="text-slate-700">DNI:</strong> {{ $padre->padre_dni }}</span>
                                    @if($padre->conyuge_nombres)
                                        <span class="text-indigo-700 font-semibold"><i class="fa-solid fa-user-group text-indigo-400"></i> Cónyuge/2do: {{ $padre->conyuge_nombres }} @if($padre->conyuge_dni)(DNI: {{ $padre->conyuge_dni }})@endif</span>
                                    @endif
                                    <span><i class="fa-solid fa-phone text-slate-400"></i> {{ $padre->celular ?? 'Sin celular' }}</span>
                                    <span><i class="fa-solid fa-location-dot text-slate-400"></i> {{ $padre->direccion ?? 'I.E. Local' }}</span>
                                    <span class="font-bold text-indigo-600">{{ $padre->correlativo }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Indicador de Consulta Segura -->
                        <div class="flex-shrink-0 text-right">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                <i class="fa-solid fa-shield-halved text-indigo-500"></i>
                                <span>Consulta Segura</span>
                            </span>
                        </div>
                    </div>

                    <!-- ================= NUEVA SECCIÓN: ESTADO ECONÓMICO, APORTES Y CUOTAS ================= -->
                    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-lg overflow-hidden">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                                    <span class="p-2 bg-emerald-100 text-emerald-700 rounded-xl text-xs">
                                        <i class="fa-solid fa-hand-holding-dollar"></i>
                                    </span>
                                    Aportes y Cuotas de la APAFA
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Estado de cuenta del asociado: cuotas ordinarias, aportes extraordinarios y pagos registrados.
                                </p>
                            </div>

                            <!-- KPIs de Aportes y Cuotas -->
                            <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                                <div class="px-3 py-1.5 bg-white rounded-xl border border-slate-200 text-center shadow-sm">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Cuotas</span>
                                    <span class="text-sm font-black text-slate-800">S/ {{ number_format($statsAportes['total_cuotas'], 2) }}</span>
                                </div>
                                <div class="px-3 py-1.5 bg-emerald-50 rounded-xl border border-emerald-200 text-center shadow-sm">
                                    <span class="text-[10px] uppercase font-bold text-emerald-600 block">Total Pagado</span>
                                    <span class="text-sm font-black text-emerald-700">S/ {{ number_format($statsAportes['total_aportado'], 2) }}</span>
                                </div>
                                <div class="px-3 py-1.5 {{ $statsAportes['total_deuda'] > 0 ? 'bg-amber-50 border-amber-200' : 'bg-slate-50 border-slate-200' }} rounded-xl border text-center shadow-sm">
                                    <span class="text-[10px] uppercase font-bold {{ $statsAportes['total_deuda'] > 0 ? 'text-amber-700' : 'text-slate-500' }} block">Deuda Pendiente</span>
                                    <span class="text-sm font-black {{ $statsAportes['total_deuda'] > 0 ? 'text-amber-800' : 'text-slate-700' }}">S/ {{ number_format($statsAportes['total_deuda'], 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de Cuotas del Padre -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-slate-600">
                                <thead class="bg-slate-50 text-slate-400 text-[11px] font-bold uppercase tracking-wider border-b border-slate-100">
                                    <tr>
                                        <th class="py-3.5 px-6">Concepto / Cuota</th>
                                        <th class="py-3.5 px-4 text-right">Monto Asignado</th>
                                        <th class="py-3.5 px-4 text-right">Monto Pagado</th>
                                        <th class="py-3.5 px-4 text-right">Saldo Deudor</th>
                                        <th class="py-3.5 px-6 text-center">Estado del Aporte</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    @forelse($padre->aportes as $ap)
                                        @php
                                            $cuota = $ap->aporte;
                                            $deuda = (float) $ap->debe;
                                            $pagado = (float) $ap->monto_aportado;
                                            $asignado = (float) $ap->monto_aporte;
                                            $estaPagado = $deuda <= 0 && $pagado > 0;
                                            $esParcial = $pagado > 0 && $deuda > 0;
                                        @endphp
                                        <tr class="hover:bg-slate-50/80 transition-colors">
                                            <td class="py-4 px-6">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="font-bold text-slate-800">{{ $cuota->titulo ?? 'Cuota de APAFA' }}</span>
                                                    @if(($cuota->tipo ?? 'ORDINARIA') === 'ORDINARIA')
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                            <i class="fa-solid fa-shield-halved text-[9px]"></i> Ordinaria (Por Padre)
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                            Extraordinaria
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-slate-400 mt-0.5">{{ $cuota->descripcion ?? 'Aporte anual ordinario por asociado' }}</div>
                                            </td>
                                            <td class="py-4 px-4 text-right font-mono font-bold text-slate-800 text-xs">
                                                S/ {{ number_format($asignado, 2) }}
                                            </td>
                                            <td class="py-4 px-4 text-right font-mono font-bold text-emerald-600 text-xs">
                                                S/ {{ number_format($pagado, 2) }}
                                            </td>
                                            <td class="py-4 px-4 text-right font-mono font-bold {{ $deuda > 0 ? 'text-amber-600' : 'text-slate-400' }} text-xs">
                                                S/ {{ number_format($deuda, 2) }}
                                            </td>
                                            <td class="py-4 px-6 text-center">
                                                @if($estaPagado)
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        <i class="fa-solid fa-check text-emerald-500"></i>
                                                        Pagado Total
                                                    </span>
                                                @elseif($esParcial)
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                        <i class="fa-solid fa-hourglass-half text-blue-500"></i>
                                                        Pago Parcial
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                        <i class="fa-solid fa-clock text-rose-500"></i>
                                                        Pendiente de Pago
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-8 text-center text-xs text-slate-400 italic">
                                                No hay cuotas o aportes asignados a este padre de familia.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Nota de Transparencia y Base Legal: Ley 28628 -->
                        <div class="px-6 py-3.5 bg-slate-50/90 border-t border-slate-100 flex items-center justify-between flex-wrap gap-2 text-xs text-slate-500">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-scale-balanced text-indigo-600"></i>
                                <span><strong>Marco Legal (Ley N° 28628 / D.S. N° 004-2006-ED, Art. 74):</strong> La cuota ordinaria se paga <strong>por Padre de Familia (Asociado)</strong>, no por cada hijo matriculado, con un tope máximo legal del 1.5% de la UIT vigente.</span>
                            </div>
                            <span class="font-bold text-indigo-700 text-[11px] bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">
                                Pago único por familia
                            </span>
                        </div>

                        <!-- Subsección: Historial de Comprobantes de Pago -->
                        @if($padre->pagos->count() > 0)
                            <div class="p-5 border-t border-slate-100 bg-slate-50/40">
                                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                                    <i class="fa-solid fa-receipt text-indigo-500"></i>
                                    Comprobantes de Pago y Boletas Electrónicas Emitidas
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($padre->pagos as $pago)
                                        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                                            <div>
                                                <p class="font-bold text-slate-800 text-xs truncate max-w-[180px]">{{ $pago->pago_concepto }}</p>
                                                <p class="text-[11px] text-slate-400 font-mono mt-0.5">
                                                    {{ $pago->tipo_comprobante }} {{ $pago->serie }}-{{ $pago->numero }}
                                                </p>
                                                <p class="text-xs font-black text-emerald-600 font-mono mt-1">
                                                    S/ {{ number_format($pago->monto_pago, 2) }}
                                                </p>
                                            </div>
                                            <a href="{{ route('pagos.ticket_publico', $pago->id) }}" 
                                               target="_blank"
                                               title="Ver Recibo Térmico"
                                               class="p-2.5 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl text-slate-600 text-xs font-bold transition-colors flex items-center gap-1.5">
                                                <i class="fa-solid fa-receipt"></i>
                                                <span>Ticket</span>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- ================= SECCIÓN 2: ASISTENCIAS CONVOCADAS DE LA APAFA ================= -->
                    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-lg overflow-hidden">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                                    <span class="p-2 bg-indigo-100 text-indigo-700 rounded-xl text-xs">
                                        <i class="fa-solid fa-users-rectangle"></i>
                                    </span>
                                    Asambleas y Convocatorias de APAFA
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Registro de asistencia del padre/madre a las asambleas generales y faenas convocadas por la directiva.
                                </p>
                            </div>

                            <!-- KPIs de Asambleas -->
                            <div class="flex items-center gap-2">
                                <div class="px-3 py-1.5 bg-white rounded-xl border border-slate-200 text-center shadow-sm">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Convocadas</span>
                                    <span class="text-sm font-black text-slate-800">{{ $statsReuniones['convocadas'] }}</span>
                                </div>
                                <div class="px-3 py-1.5 bg-emerald-50 rounded-xl border border-emerald-200 text-center shadow-sm">
                                    <span class="text-[10px] uppercase font-bold text-emerald-600 block">Asistidas</span>
                                    <span class="text-sm font-black text-emerald-700">{{ $statsReuniones['asistidas'] }}</span>
                                </div>
                                <div class="px-3 py-1.5 bg-rose-50 rounded-xl border border-rose-200 text-center shadow-sm">
                                    <span class="text-[10px] uppercase font-bold text-rose-600 block">Faltas</span>
                                    <span class="text-sm font-black text-rose-700">{{ $statsReuniones['faltas'] }}</span>
                                </div>
                                @if($statsReuniones['multas'] > 0)
                                    <div class="px-3 py-1.5 bg-amber-50 rounded-xl border border-amber-200 text-center shadow-sm">
                                        <span class="text-[10px] uppercase font-bold text-amber-700 block">Multas</span>
                                        <span class="text-sm font-black text-amber-800">S/ {{ number_format($statsReuniones['multas'], 2) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Tabla de Asambleas -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-slate-600">
                                <thead class="bg-slate-50 text-slate-400 text-[11px] font-bold uppercase tracking-wider border-b border-slate-100">
                                    <tr>
                                        <th class="py-3.5 px-6">Asamblea / Convocatoria</th>
                                        <th class="py-3.5 px-4">Fecha y Hora</th>
                                        <th class="py-3.5 px-4">Hora Marcado</th>
                                        <th class="py-3.5 px-4">Estado de Asistencia</th>
                                        <th class="py-3.5 px-6">Multa por Inasistencia</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    @forelse($padre->reuniones as $rp)
                                        @php
                                            $reunion = $rp->reunion;
                                            $haAsistido = !empty($rp->asistencia);
                                            $esPasada = $reunion && $reunion->fecha && $reunion->fecha < date('Y-m-d');
                                        @endphp
                                        <tr class="hover:bg-slate-50/80 transition-colors">
                                            <td class="py-4 px-6">
                                                <div class="font-bold text-slate-800">{{ $reunion->titulo ?? 'Asamblea General' }}</div>
                                                <div class="text-xs text-slate-400 mt-0.5">{{ $reunion->descripcion ?? 'Convocatoria oficial de directiva' }}</div>
                                            </td>
                                            <td class="py-4 px-4 text-xs font-semibold text-slate-700">
                                                <div>{{ $reunion && $reunion->fecha ? date('d/m/Y', strtotime($reunion->fecha)) : 'Fecha por confirmar' }}</div>
                                                <div class="text-[11px] text-slate-400 font-mono">{{ $reunion && $reunion->hora ? date('h:i A', strtotime($reunion->hora)) : '--:--' }}</div>
                                            </td>
                                            <td class="py-4 px-4 text-xs font-mono font-bold text-slate-800">
                                                @if($rp->asistencia)
                                                    <span class="text-emerald-600 flex items-center gap-1">
                                                        <i class="fa-regular fa-clock"></i>
                                                        {{ date('h:i:s A', strtotime($rp->asistencia)) }}
                                                    </span>
                                                @else
                                                    <span class="text-slate-400 italic">No registró</span>
                                                @endif
                                            </td>
                                            <td class="py-4 px-4">
                                                @if($haAsistido)
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                                        Asistió (Presente)
                                                    </span>
                                                @elseif($esPasada)
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                        <i class="fa-solid fa-circle-xmark text-rose-500"></i>
                                                        Inasistencia (Falta)
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                                                        <i class="fa-regular fa-calendar-check text-slate-400"></i>
                                                        Próxima Convocatoria
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-4 px-6">
                                                @if(!$haAsistido && $esPasada && $reunion->multa_precio > 0)
                                                    <span class="inline-flex items-center gap-1 text-xs font-black text-rose-600 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-200">
                                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                                        S/ {{ number_format($reunion->multa_precio, 2) }}
                                                    </span>
                                                @else
                                                    <span class="text-xs text-slate-400">Sin multa</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-8 text-center text-xs text-slate-400 italic">
                                                No hay asambleas o faenas registradas para esta institución.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ================= SECCIÓN 3: ASISTENCIAS DE SUS HIJOS EN CLASES ================= -->
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                                <span class="p-2 bg-indigo-100 text-indigo-700 rounded-xl text-xs">
                                    <i class="fa-solid fa-child-reaching"></i>
                                </span>
                                Asistencia de Estudiantes a Cargo (Hijos, Nietos, Sobrinos)
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Registro diario de control de portería para cada uno de los menores bajo su potestad o tutela familiar.
                            </p>
                        </div>

                        @forelse($padre->estudiantes as $hijo)
                            @php
                                $sHijo = $statsHijos[$hijo->id] ?? ['total' => 0, 'puntuales' => 0, 'tardanzas' => 0, 'justificadas' => 0, 'porcentaje' => 100];
                            @endphp
                            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-lg overflow-hidden">
                                <!-- Encabezado de la Ficha del Alumno -->
                                <div class="p-6 border-b border-slate-100 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                    <div class="flex items-center gap-3.5">
                                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-base flex-shrink-0">
                                            {{ substr($hijo->nombres ?? 'A', 0, 1) }}{{ substr($hijo->ape_pat ?? 'E', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h4 class="font-extrabold text-slate-900 text-base">
                                                    {{ $hijo->nombres }} {{ $hijo->ape_pat }} {{ $hijo->ape_mat }}
                                                </h4>
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                    <i class="fa-solid fa-people-roof text-[10px]"></i> {{ $hijo->parentesco ?? 'HIJO(A)' }}
                                                </span>
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-200/70 text-slate-700">
                                                    {{ $hijo->grado }} "{{ $hijo->seccion }}" - {{ $hijo->nivel }}
                                                </span>
                                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                    Turno {{ $hijo->turno ?? 'Mañana' }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-slate-400 font-mono mt-0.5">
                                                DNI: {{ $hijo->dni }} | Matrícula: {{ $hijo->correlativo }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 self-start sm:self-auto">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-medium bg-slate-100 text-slate-500">
                                            <i class="fa-solid fa-graduation-cap text-indigo-500"></i>
                                            <span>Estudiante Regular</span>
                                        </span>
                                    </div>
                                </div>

                                <!-- Resumen de Asistencia del Hijo -->
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 bg-slate-50/30 border-b border-slate-100">
                                    <div class="p-3 bg-white rounded-xl border border-slate-100 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                                            <i class="fa-solid fa-calendar-days"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] uppercase font-bold text-slate-400">Total Días</p>
                                            <p class="text-sm font-black text-slate-800">{{ $sHijo['total'] }}</p>
                                        </div>
                                    </div>
                                    <div class="p-3 bg-white rounded-xl border border-slate-100 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">
                                            <i class="fa-solid fa-clock"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] uppercase font-bold text-emerald-600">Puntuales</p>
                                            <p class="text-sm font-black text-emerald-700">{{ $sHijo['puntuales'] }}</p>
                                        </div>
                                    </div>
                                    <div class="p-3 bg-white rounded-xl border border-slate-100 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">
                                            <i class="fa-solid fa-triangle-exclamation"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] uppercase font-bold text-amber-600">Tardanzas</p>
                                            <p class="text-sm font-black text-amber-700">{{ $sHijo['tardanzas'] }}</p>
                                        </div>
                                    </div>
                                    <div class="p-3 bg-white rounded-xl border border-slate-100 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                                            <i class="fa-solid fa-file-shield"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] uppercase font-bold text-indigo-600">Justificadas</p>
                                            <p class="text-sm font-black text-indigo-700">{{ $sHijo['justificadas'] }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Historial Diario de Asistencias del Alumno -->
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-sm text-slate-600">
                                        <thead class="bg-slate-50/70 text-slate-400 text-[11px] font-bold uppercase tracking-wider border-b border-slate-100">
                                            <tr>
                                                <th class="py-3 px-6">Fecha</th>
                                                <th class="py-3 px-4">Hora de Ingreso</th>
                                                <th class="py-3 px-4">Hora de Salida</th>
                                                <th class="py-3 px-4">Estado</th>
                                                <th class="py-3 px-6">Observación / Sustento</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 font-medium">
                                            @forelse($hijo->asistencias as $asist)
                                                <tr class="hover:bg-slate-50/50 transition-colors">
                                                    <td class="py-3.5 px-6 font-bold text-slate-800 text-xs">
                                                        <i class="fa-regular fa-calendar text-slate-400 mr-1.5"></i>
                                                        {{ date('d/m/Y', strtotime($asist->fecha)) }}
                                                    </td>
                                                    <td class="py-3.5 px-4 font-mono text-xs font-bold text-slate-700">
                                                        {{ $asist->hora_ingreso ? date('h:i:s A', strtotime($asist->hora_ingreso)) : '--:--' }}
                                                    </td>
                                                    <td class="py-3.5 px-4 font-mono text-xs font-bold text-slate-700">
                                                        {{ $asist->hora_salida ? date('h:i:s A', strtotime($asist->hora_salida)) : 'En clase' }}
                                                    </td>
                                                    <td class="py-3.5 px-4">
                                                        {!! $asist->estado_badge_html !!}
                                                    </td>
                                                    <td class="py-3.5 px-6 text-xs text-slate-500">
                                                        @if($asist->justificado)
                                                            <span class="text-indigo-700 font-bold flex items-center gap-1">
                                                                <i class="fa-solid fa-file-circle-check"></i>
                                                                {{ $asist->motivo_justificacion }}
                                                            </span>
                                                            @if($asist->documento_sustento)
                                                                <span class="text-[11px] text-slate-400 block">Doc: {{ $asist->documento_sustento }}</span>
                                                            @endif
                                                        @elseif($asist->observacion)
                                                            {{ $asist->observacion }}
                                                        @else
                                                            <span class="text-slate-400 italic">Sin observaciones</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="py-6 text-center text-xs text-slate-400 italic">
                                                        Aún no se registran asistencias en portería para este estudiante.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @empty
                            <div class="bg-white rounded-3xl p-8 text-center border border-slate-200 text-slate-400 text-sm">
                                No figuran estudiantes matriculados bajo este apoderado.
                            </div>
                        @endforelse
                    </div>

                </div>

            @else
                <!-- PANTALLA INFORMATIVA INICIAL CUANDO AÚN NO SE BUSCA DNI -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                    
                    <div class="bg-white p-7 rounded-3xl border border-slate-200/90 shadow-md hover:shadow-xl transition-shadow space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xl">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Aportes y Cuotas APAFA</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Consulte el estado de sus cuotas ordinarias y extraordinarias, verifique sus saldos al día y descargue sus comprobantes de pago.
                        </p>
                    </div>

                    <div class="bg-white p-7 rounded-3xl border border-slate-200/90 shadow-md hover:shadow-xl transition-shadow space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xl">
                            <i class="fa-solid fa-users-viewfinder"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Asambleas y Faenas</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Verifique en tiempo real sus asistencias a reuniones de padres, multas registradas o justificaciones aprobadas por la directiva.
                        </p>
                    </div>

                    <div class="bg-white p-7 rounded-3xl border border-slate-200/90 shadow-md hover:shadow-xl transition-shadow space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xl">
                            <i class="fa-solid fa-qrcode"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Control Escolar de Hijos</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Vea a qué hora exacta ingresó y salió su hijo de la escuela mediante el escaneo de código de barras y fotocheck en la portería del plantel.
                        </p>
                    </div>

                </div>
            @endif

            <!-- ================= PORTAL PÚBLICO DE TRANSPARENCIA Y RENDICIÓN DE CUENTAS (POA - LEY 28628) ================= -->
            <div class="mt-12 mb-8 bg-white rounded-3xl border border-slate-200/90 shadow-xl overflow-hidden">
                <div class="p-6 sm:p-8 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div class="space-y-2 max-w-3xl">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/10 text-indigo-200 border border-white/15">
                            <i class="fa-solid fa-scale-balanced text-amber-400"></i> Transparencia Activa: Ley N° 28628 &bull; D.S. N° 004-2006-ED
                        </span>
                        <h3 class="text-xl sm:text-2xl font-black tracking-tight text-white">
                            ¿En qué se invierte el dinero recaudado de la APAFA?
                        </h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Los fondos de la APAFA no pueden duplicar lo que el Estado financia a través de PRONIED (mantenimiento y pintura de aulas o baños). 
                            Se ejecutan conforme al <strong class="text-amber-300">Plan Operativo Anual (POA)</strong> en rubros complementarios: tecnología educativa, material deportivo, talleres pedagógicos y emergencias estudiantiles.
                        </p>
                    </div>

                    <div class="flex-shrink-0 text-center sm:text-right">
                        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-400/30">
                            <i class="fa-solid fa-shield-check"></i> Cuentas Claras y Auditadas
                        </span>
                    </div>
                </div>

                <!-- KPIs de Balance del POA y Quioscos (D.S. 007-2026-MINEDU) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-slate-100 p-6 bg-slate-50/50">
                    <div class="p-4 text-center sm:text-left">
                        <span class="text-[11px] uppercase font-bold text-slate-400 tracking-wider block">Total Recaudado (Aportes) & Quioscos</span>
                        <span class="text-2xl font-black text-slate-900 font-mono">S/ {{ number_format($transparenciaPoa['recaudado'], 2) }}</span>
                        <span class="text-xs text-slate-500 block mt-0.5">
                            Cuotas: S/ {{ number_format($transparenciaPoa['recaudado_cuotas'] ?? $transparenciaPoa['recaudado'], 2) }} &bull; Quioscos: S/ {{ number_format($transparenciaPoa['recaudado_quioscos'] ?? 0, 2) }}
                        </span>
                    </div>
                    <div class="p-4 text-center sm:text-left">
                        <span class="text-[11px] uppercase font-bold text-slate-400 tracking-wider block">Total Invertido en el Colegio</span>
                        <span class="text-2xl font-black text-rose-600 font-mono">S/ {{ number_format($transparenciaPoa['invertido'], 2) }}</span>
                        <span class="text-xs text-slate-500 block mt-0.5">Egresos sustentados con boleta/factura</span>
                    </div>
                    <div class="p-4 text-center sm:text-left">
                        <span class="text-[11px] uppercase font-bold text-slate-400 tracking-wider block">Saldo Disponible en Banco</span>
                        <span class="text-2xl font-black {{ $transparenciaPoa['saldo'] >= 0 ? 'text-indigo-600' : 'text-rose-600' }} font-mono">S/ {{ number_format($transparenciaPoa['saldo'], 2) }}</span>
                        <span class="text-xs text-slate-500 block mt-0.5">Cuenta Mancomunada de la APAFA</span>
                    </div>
                </div>

                @if(!empty($transparenciaPoa['dictamenes']) && $transparenciaPoa['dictamenes']->count() > 0)
                <!-- Dictámenes de Fiscalización del Consejo de Vigilancia (D.S. 007-2026-MINEDU) -->
                <div class="p-6 bg-rose-50/40 border-t border-rose-100">
                    <h4 class="text-xs font-bold text-rose-800 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-rose-600"></i> Fiscalización del Consejo de Vigilancia (Auditoría Pública)
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($transparenciaPoa['dictamenes'] as $dic)
                        <div class="p-3.5 bg-white rounded-xl border border-rose-200 shadow-sm text-xs">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-slate-800">{{ $dic->periodo }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $dic->tipo_dictamen === 'FAVORABLE' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $dic->tipo_dictamen }}
                                </span>
                            </div>
                            <p class="font-semibold text-slate-700">{{ $dic->titulo }}</p>
                            <p class="text-[11px] text-slate-500 mt-1 line-clamp-2">{{ $dic->detalles_auditoria }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Últimos Proyectos y Gastos Ejecutados del POA -->
                <div class="p-6 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-indigo-600"></i> Últimas Inversiones y Gastos Reportados
                    </h4>

                    @if($transparenciaPoa['gastos']->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($transparenciaPoa['gastos'] as $g)
                                @php
                                    $cat = $g->categoria_info;
                                @endphp
                                <div class="p-4 rounded-2xl border border-slate-200/80 bg-white hover:border-indigo-200 transition-all flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-[10px] font-bold {{ $cat['bg'] }} {{ $cat['text'] }} border {{ $cat['border'] }}">
                                                <i class="{{ $cat['icono'] }}"></i> {{ $cat['nombre'] }}
                                            </span>
                                            <span class="text-[11px] font-mono text-slate-400">{{ $g->fecha ? $g->fecha->format('d/m/Y') : '' }}</span>
                                        </div>
                                        <h5 class="font-bold text-slate-800 text-xs mb-1">{{ $g->titulo }}</h5>
                                        <p class="text-[11px] text-slate-400 line-clamp-2">{{ $g->descripcion ?? 'Ejecutado conforme al Plan Operativo Anual de APAFA.' }}</p>
                                    </div>
                                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                                        <span class="text-[11px] text-slate-500 font-mono">{{ $g->nro_comprobante ? 'Doc: ' . $g->nro_comprobante : 'Sustentado' }}</span>
                                        <span class="font-mono font-black text-rose-600">S/ {{ number_format($g->monto, 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 text-center text-slate-400 text-xs bg-slate-50 rounded-2xl border border-slate-100 italic">
                            Los gastos y proyectos ejecutados del POA aparecerán registrados aquí de manera transparente a medida que la directiva sustente cada comprobante.
                        </div>
                    @endif
                </div>
            </div>

            <!-- ================= CONSEJO DIRECTIVO DE LA APAFA: ÓRGANO DE GESTIÓN (LEY N° 28628) ================= -->
            <div class="mt-12 mb-8 bg-white rounded-3xl border border-slate-200/90 shadow-xl overflow-hidden">
                <!-- Encabezado Institucional -->
                <div class="p-6 sm:p-8 bg-gradient-to-r from-indigo-950 via-slate-900 to-indigo-950 text-white flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div class="space-y-2 max-w-3xl">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/10 text-indigo-200 border border-white/15">
                            <i class="fa-solid fa-users-line text-indigo-400"></i> Estructura Oficial de Representación Escolar &bull; Ley N° 28628
                        </span>
                        <h3 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                            <span>Consejo Directivo de la APAFA</span>
                            <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 font-bold uppercase tracking-wider hidden sm:inline">
                                Periodo {{ $empresa->periodo_apafa ?? '2025 - 2026' }}
                            </span>
                        </h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Consejo Directivo de la APAFA: Órgano de gestión conformado por un presidente, vicepresidente, secretario, tesorero y vocales, elegidos democráticamente mediante sufragio universal, directo y secreto para un mandato de dos (2) años.
                        </p>
                    </div>

                    <div class="flex-shrink-0 text-center sm:text-right space-y-1.5">
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-indigo-500/20 text-indigo-300 text-xs font-bold border border-indigo-400/30">
                            <i class="fa-solid fa-check-to-slot"></i> Elección Democrática Universal
                        </span>
                        <span class="block text-[11px] text-slate-400 font-mono">D.S. N.º 007-2026-MINEDU</span>
                    </div>
                </div>

                <!-- Grid de Directivos Oficiales -->
                <div class="p-6 sm:p-8">
                    @php
                        // Estructura reglamentaria de los 5 cargos del Consejo Directivo
                        $cargosDirectiva = [
                            [
                                'cargo' => 'PRESIDENTE(A)',
                                'rol' => 'Presidente(a) de APAFA',
                                'subtitulo' => 'Representante Legal de la Asociación',
                                'descripcion' => 'Convoca y preside las asambleas, suscribe contratos y convenios, firma mancomunadamente con el tesorero las cuentas bancarias y rinde cuentas documentadas del POA.',
                                'icono' => 'fa-solid fa-user-tie',
                                'badge_bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                'header_gradient' => 'from-indigo-600 to-blue-600',
                                'filtro' => 'PRESIDENTE',
                            ],
                            [
                                'cargo' => 'VICEPRESIDENTE(A)',
                                'rol' => 'Vicepresidente(a)',
                                'subtitulo' => 'Coordinación y Apoyo Ejecutivo',
                                'descripcion' => 'Asume de pleno derecho las funciones del Presidente en caso de ausencia o vacancia. Asesora y articula el plan de trabajo con los Comités de Aula y Talleres.',
                                'icono' => 'fa-solid fa-user-shield',
                                'badge_bg' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'header_gradient' => 'from-blue-600 to-cyan-600',
                                'filtro' => 'VICEPRESIDENTE',
                            ],
                            [
                                'cargo' => 'SECRETARIO(A)',
                                'rol' => 'Secretario(a)',
                                'subtitulo' => 'Fe Pública y Actas Institucionales',
                                'descripcion' => 'Redacta y custodia los libros de actas de asambleas y directiva; mantiene y actualiza el Padrón Oficial de Asociados y expide credenciales y carnés.',
                                'icono' => 'fa-solid fa-file-signature',
                                'badge_bg' => 'bg-sky-50 text-sky-700 border-sky-200',
                                'header_gradient' => 'from-sky-600 to-indigo-600',
                                'filtro' => 'SECRETARIO',
                            ],
                            [
                                'cargo' => 'TESORERO(A)',
                                'rol' => 'Tesorero(a)',
                                'subtitulo' => 'Custodia de Fondos y Cuentas',
                                'descripcion' => 'Custodia los fondos de la APAFA; cobra aportes y concesiones de quioscos; firma la cuenta mancomunada y rinde cuentas con comprobantes de pago autorizados.',
                                'icono' => 'fa-solid fa-coins',
                                'badge_bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'header_gradient' => 'from-emerald-600 to-teal-600',
                                'filtro' => 'TESORERO',
                            ],
                            [
                                'cargo' => 'VOCALES',
                                'rol' => 'Vocales de Directiva (1°, 2° y 3°)',
                                'subtitulo' => 'Comisiones de Trabajo y Seguridad',
                                'descripcion' => 'Presiden comisiones de infraestructura, deporte y cultura; vigilan la seguridad en faenas escolares (prohibiendo tareas peligrosas) y apoyan el control diario.',
                                'icono' => 'fa-solid fa-users-gear',
                                'badge_bg' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'header_gradient' => 'from-amber-600 to-orange-600',
                                'filtro' => 'VOCAL',
                            ],
                        ];
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($cargosDirectiva as $cd)
                            @php
                                $titulares = $miembrosConsejoDirectivo->filter(function($m) use ($cd) {
                                    $nom = mb_strtoupper($m->cargo->nombre ?? '');
                                    if (str_contains($nom, 'VIGILANCIA')) return false;
                                    return str_contains($nom, $cd['filtro']);
                                });
                            @endphp

                            <div class="rounded-3xl border border-slate-200/90 bg-white hover:border-indigo-300 hover:shadow-lg transition-all flex flex-col justify-between overflow-hidden">
                                <div>
                                    <!-- Cabecera de la tarjeta con gradiente distintivo -->
                                    <div class="bg-gradient-to-r {{ $cd['header_gradient'] }} p-4 text-white flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-9 h-9 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-sm font-bold shadow-sm">
                                                <i class="{{ $cd['icono'] }}"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-extrabold text-sm leading-tight text-white">{{ $cd['rol'] }}</h4>
                                                <span class="text-[10px] text-white/80 block">{{ $cd['subtitulo'] }}</span>
                                            </div>
                                        </div>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-white/20 text-white font-bold">
                                            Gestión
                                        </span>
                                    </div>

                                    <!-- Datos de los Titulares Electos -->
                                    <div class="p-5 space-y-3">
                                        @if($titulares->count() > 0)
                                            @foreach($titulares as $t)
                                                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between gap-2">
                                                    <div class="min-w-0">
                                                        <span class="font-black text-slate-900 text-xs block truncate">{{ $t->nombre_completo }}</span>
                                                        <span class="text-[10px] text-slate-400 font-mono">DNI: {{ $t->padre_dni }} &bull; Cargo: {{ $t->cargo->nombre ?? $cd['rol'] }}</span>
                                                    </div>
                                                    <span class="flex-shrink-0 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                        <i class="fa-solid fa-check text-[9px]"></i> Electo
                                                    </span>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="p-3 bg-amber-50/60 rounded-2xl border border-amber-200/80 text-amber-900 text-xs flex items-center gap-2">
                                                <i class="fa-solid fa-circle-info text-amber-600"></i>
                                                <span class="text-[11px] leading-tight">Cargo directivo sujeto a proclamación del Comité Electoral o asunción reglamentaria.</span>
                                            </div>
                                        @endif

                                        <p class="text-[11px] text-slate-500 leading-relaxed pt-1">
                                            {{ $cd['descripcion'] }}
                                        </p>
                                    </div>
                                </div>

                                <div class="px-5 pb-4 pt-2 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between text-[11px] text-slate-400">
                                    <span class="flex items-center gap-1 font-semibold text-slate-600">
                                        <i class="fa-solid fa-scale-balanced text-indigo-600"></i> Ley N° 28628 Art. 43
                                    </span>
                                    <span class="font-mono text-[10px]">Mandato 2 Años</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Fila Complementaria: Órgano de Control (Consejo de Vigilancia) y Comité Electoral -->
                    <div class="mt-6 pt-6 border-t border-slate-200/80 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 bg-rose-50/50 border border-rose-200/80 rounded-2xl flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-sm font-bold flex-shrink-0 mt-0.5">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <div class="text-xs">
                                <h4 class="font-extrabold text-rose-950 mb-0.5">Consejo de Vigilancia (Órgano de Control y Fiscalización)</h4>
                                <p class="text-rose-800 leading-relaxed text-[11px]">
                                    Conformado por un <strong>Presidente, Secretario y Vocal</strong> elegidos también por la Asamblea General. Fiscalizan la gestión económica, los gastos del POA y emiten dictámenes de auditoría interna de manera independiente.
                                </p>
                            </div>
                        </div>

                        <div class="p-4 bg-purple-50/50 border border-purple-200/80 rounded-2xl flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-sm font-bold flex-shrink-0 mt-0.5">
                                <i class="fa-solid fa-check-double"></i>
                            </div>
                            <div class="text-xs">
                                <h4 class="font-extrabold text-purple-950 mb-0.5">Comité Electoral Autónomo (Comicios y Proclamación)</h4>
                                <p class="text-purple-800 leading-relaxed text-[11px]">
                                    Órgano autónomo elegido en asamblea en la primera quincena de octubre. Encargado de convocar, recibir listas completas, revisar requisitos (Art. 45 Ley 28628), conducir la votación en urnas y proclamar la lista ganadora.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 text-xs py-8 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="font-black text-white">SIS-APAFA</span>
                <span>&bull;</span>
                <span>Plataforma Oficial de Transparencia Educativa</span>
            </div>
            <p class="text-slate-500 text-[11px] text-center sm:text-right">
                Conforme a la Ley General de APAFA N° 28628 &bull; Todos los derechos reservados.
            </p>
        </div>
    </footer>

</body>
</html>
