@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header con Leyenda D.S. 007-2026-MINEDU / Ley 30021 -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300">
                    <i class="bi bi-shop mr-1"></i> Ley N° 30021 & D.S. N.º 007-2026-MINEDU
                </span>
                <span class="text-xs text-slate-500 font-medium">Concesiones y Cafeterías Escolares Saludables</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <i class="bi bi-cup-hot-fill text-amber-600"></i> Quioscos, Cafeterías y Comedores Escolares
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Licitación transparente y administración de los ingresos por arrendamiento destinados al fondo de mejoras de la APAFA.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button onclick="document.getElementById('modalNuevoQuiosco').classList.remove('hidden')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <i class="bi bi-plus-square"></i> Registrar Quiosco
            </button>
            <button onclick="document.getElementById('modalNuevaConcesion').classList.remove('hidden')" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-2 shadow-sm">
                <i class="bi bi-file-earmark-text"></i> Adjudicar Concesión
            </button>
        </div>
    </div>

    <!-- Métricas Financieras de Quioscos (El pago es mensual) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Recaudación Total</p>
                <h3 class="text-2xl font-extrabold text-slate-800">S/ {{ number_format($totalRecaudadoQuioscos, 2) }}</h3>
                <p class="text-xs text-amber-600 font-medium mt-0.5">Ingresos acumulados APAFA</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-emerald-200/80 shadow-sm flex items-center gap-4 bg-gradient-to-br from-white to-emerald-50/30">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-calendar-month"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Canon Mensual Pactado</p>
                <h3 class="text-2xl font-extrabold text-emerald-700 font-mono">S/ {{ number_format($canonMensualTotal, 2) }}</h3>
                <p class="text-xs text-emerald-700 font-bold mt-0.5">Obligación fija por mes</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Cobrado en {{ explode(' ', $mesActualNombre)[0] }}</p>
                <h3 class="text-2xl font-extrabold text-indigo-700 font-mono">S/ {{ number_format($recaudadoMesActual, 2) }}</h3>
                <p class="text-xs text-slate-400 font-medium mt-0.5">Mes en curso {{ date('Y') }}</p>
            </div>
        </div>

        <!-- Indicador General de Deuda / Morosidad -->
        <div class="bg-white p-5 rounded-2xl border {{ $totalDeudaQuioscos > 0 ? 'border-rose-300 bg-rose-50/30' : 'border-emerald-200 bg-emerald-50/30' }} shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl {{ $totalDeudaQuioscos > 0 ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }} flex items-center justify-center text-xl font-bold">
                <i class="bi {{ $totalDeudaQuioscos > 0 ? 'bi-exclamation-triangle-fill' : 'bi-shield-check' }}"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Estado de Deuda</p>
                @if($totalDeudaQuioscos > 0)
                <h3 class="text-2xl font-extrabold text-rose-700 font-mono">S/ {{ number_format($totalDeudaQuioscos, 2) }}</h3>
                <p class="text-xs text-rose-600 font-black mt-0.5">⚠️ Cánones en mora</p>
                @else
                <h3 class="text-2xl font-extrabold text-emerald-700 font-mono">S/ 0.00</h3>
                <p class="text-xs text-emerald-700 font-black mt-0.5">✓ 100% Al día (No debe)</p>
                @endif
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-shop"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Quioscos / Concesiones</p>
                <h3 class="text-2xl font-extrabold text-blue-600">{{ $totalConcesionesVigentes }} / {{ $quioscos->count() }}</h3>
                <p class="text-xs text-blue-600 font-medium mt-0.5">Vigentes con Ley 30021</p>
            </div>
        </div>
    </div>

    <!-- Módulos de Quioscos Disponibles -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($quioscos as $q)
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $q->estado === 'CONCESIONADO' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $q->estado }}
                    </span>
                    <i class="bi bi-shop text-xl text-slate-400"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-base mt-2">{{ $q->nombre }}</h3>
                <p class="text-xs text-slate-500 flex items-center gap-1 mt-0.5">
                    <i class="bi bi-geo-alt"></i> {{ $q->ubicacion ?? 'Patio de la I.E.' }}
                </p>

                @if($q->concesionVigente)
                <div class="mt-4 p-3.5 bg-slate-50 rounded-xl border border-slate-100 text-xs space-y-3">
                    <div class="flex items-center justify-between pb-1.5 border-b border-slate-200">
                        <span class="text-slate-600 font-semibold">Canon Mensual:</span>
                        <span class="font-extrabold text-emerald-700 font-mono text-sm">
                            S/ {{ number_format($q->concesionVigente->canon_mensual, 2) }} <span class="text-[11px] font-normal text-slate-500">/ mes</span>
                        </span>
                    </div>

                    <!-- Indicador visual claro: ¿Debe o No Debe? -->
                    @if($q->concesionVigente->deuda_acumulada > 0)
                    <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-rose-500 animate-pulse"></span>
                            <div>
                                <span class="font-black text-xs block text-rose-900">⚠️ DEBE S/ {{ number_format($q->concesionVigente->deuda_acumulada, 2) }}</span>
                                <span class="text-[10px] text-rose-700">Tiene meses en mora pendientes</span>
                            </div>
                        </div>
                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md bg-rose-200 text-rose-900 border border-rose-300">
                            En Mora
                        </span>
                    </div>
                    @else
                    <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-check-circle-fill text-emerald-600 text-base"></i>
                            <div>
                                <span class="font-black text-xs block text-emerald-950">✓ NO DEBE (ESTÁ AL DÍA)</span>
                                <span class="text-[10px] text-emerald-700">Deuda vencida: S/ 0.00 &bull; Pagado al 100% hasta la fecha</span>
                            </div>
                        </div>
                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md bg-emerald-200 text-emerald-900 border border-emerald-300">
                            Al Día
                        </span>
                    </div>
                    @endif

                    <div class="space-y-1 text-[11px] text-slate-600">
                        <p><strong>Concesionario:</strong> {{ $q->concesionVigente->nombre_concesionario }}</p>
                        <p><strong>DNI/RUC:</strong> <span class="font-mono">{{ $q->concesionVigente->dni_ruc }}</span> &bull; Cel: {{ $q->concesionVigente->telefono ?? 'S/N' }}</p>
                        <p><strong>Vigencia:</strong> {{ \Carbon\Carbon::parse($q->concesionVigente->fecha_inicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($q->concesionVigente->fecha_fin)->format('d/m/Y') }}</p>
                    </div>

                    <!-- Cronograma Mensual de Pagos -->
                    <div class="pt-2 border-t border-slate-200">
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-700 mb-1.5">
                            <span>Control Mensual:</span>
                            <span class="text-emerald-700">{{ $q->concesionVigente->meses_pagados_count }} pagados</span>
                        </div>
                        <div class="flex flex-wrap gap-1">
                            @foreach($q->concesionVigente->cronograma_mensual as $mesItem)
                                @if($mesItem['estado'] === 'PAGADO')
                                    <a href="{{ route('quioscos.recibo', $mesItem['pago']->id) }}" target="_blank" 
                                       title="{{ $mesItem['mes_nombre'] }}: Pagado con Recibo {{ $mesItem['pago']->comprobante_numero }}"
                                       class="px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-800 text-[10px] font-bold border border-emerald-300 inline-flex items-center gap-1 hover:bg-emerald-200">
                                        <i class="bi bi-check-circle-fill text-[9px]"></i> {{ $mesItem['mes_corto'] }}
                                    </a>
                                @elseif($mesItem['estado'] === 'VENCIDO')
                                    <button type="button" 
                                            onclick="abrirModalCobro('{{ $q->concesionVigente->id }}', '{{ addslashes($q->nombre) }}', '{{ $q->concesionVigente->canon_mensual }}', '{{ $mesItem['mes_nombre'] }}')"
                                            title="{{ $mesItem['mes_nombre'] }}: Vencido en mora (Click para cobrar)"
                                            class="px-2 py-0.5 rounded-lg bg-rose-100 text-rose-800 text-[10px] font-bold border border-rose-300 inline-flex items-center gap-1 hover:bg-rose-200">
                                        <i class="bi bi-exclamation-triangle-fill text-[9px]"></i> {{ $mesItem['mes_corto'] }}
                                    </button>
                                @elseif($mesItem['estado'] === 'PENDIENTE')
                                    <button type="button" 
                                            onclick="abrirModalCobro('{{ $q->concesionVigente->id }}', '{{ addslashes($q->nombre) }}', '{{ $q->concesionVigente->canon_mensual }}', '{{ $mesItem['mes_nombre'] }}')"
                                            title="{{ $mesItem['mes_nombre'] }}: Mes actual por pagar"
                                            class="px-2 py-0.5 rounded-lg bg-amber-100 text-amber-900 text-[10px] font-bold border border-amber-300 inline-flex items-center gap-1 hover:bg-amber-200 animate-pulse">
                                        <i class="bi bi-clock-fill text-[9px]"></i> {{ $mesItem['mes_corto'] }}
                                    </button>
                                @else
                                    <button type="button" 
                                            onclick="abrirModalCobro('{{ $q->concesionVigente->id }}', '{{ addslashes($q->nombre) }}', '{{ $q->concesionVigente->canon_mensual }}', '{{ $mesItem['mes_nombre'] }}')"
                                            title="{{ $mesItem['mes_nombre'] }}: Mes futuro (Click para pago adelantado)"
                                            class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-500 text-[10px] font-medium border border-slate-200 hover:bg-slate-200">
                                        {{ $mesItem['mes_corto'] }}
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @else
                <div class="mt-4 p-4 border border-dashed border-slate-200 rounded-xl text-center text-xs text-slate-400">
                    Sin concesionario activo. Listo para licitación.
                </div>
                @endif
            </div>

            @if($q->concesionVigente)
            <div class="pt-2">
                @if($q->concesionVigente->siguiente_mes_pendiente)
                <button onclick="abrirModalCobro('{{ $q->concesionVigente->id }}', '{{ addslashes($q->nombre) }}', '{{ $q->concesionVigente->canon_mensual }}', '{{ $q->concesionVigente->siguiente_mes_pendiente }}')" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-sm transition-all">
                    <i class="bi bi-cash-coin"></i> Cobrar Siguiente Mes ({{ $q->concesionVigente->siguiente_mes_pendiente }} - S/ {{ number_format($q->concesionVigente->canon_mensual, 2) }})
                </button>
                @else
                <button onclick="abrirModalCobro('{{ $q->concesionVigente->id }}', '{{ addslashes($q->nombre) }}', '{{ $q->concesionVigente->canon_mensual }}', null)" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-sm transition-all">
                    <i class="bi bi-check-all"></i> Al Día en Todos los Meses (Registrar Mes Extra)
                </button>
                @endif
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Historial de Pagos de Arrendamiento -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <i class="bi bi-receipt text-amber-600"></i> Historial de Cánones de Arrendamiento Cobrados
            </h3>
            <span class="text-xs text-slate-400">Ingresos integrados al Libro de Caja</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3">Comprobante</th>
                        <th class="px-5 py-3">Quiosco / Cafetín</th>
                        <th class="px-5 py-3">Concesionario</th>
                        <th class="px-5 py-3">Mes Pagado</th>
                        <th class="px-5 py-3">Monto</th>
                        <th class="px-5 py-3">Fecha & Método</th>
                        <th class="px-5 py-3 text-right">Recibo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pagosRecientes as $p)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3 font-mono font-bold text-xs text-slate-800">
                            {{ $p->comprobante_numero }}
                        </td>
                        <td class="px-5 py-3 font-semibold text-slate-700">
                            {{ $p->concesion->quiosco->nombre ?? 'Quiosco' }}
                        </td>
                        <td class="px-5 py-3 text-xs">
                            {{ $p->concesion->nombre_concesionario ?? 'N/A' }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                <i class="bi bi-calendar3 text-[11px]"></i> {{ $p->mes_correspondiente }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2.5 py-0.5 rounded-md text-xs font-black bg-emerald-100 text-emerald-800">
                                S/ {{ number_format($p->monto, 2) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-slate-500">
                            {{ \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y') }} ({{ $p->metodo_pago }})
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('quioscos.recibo', $p->id) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1 bg-slate-100 hover:bg-slate-800 hover:text-white rounded-lg text-xs font-bold transition-all">
                                <i class="bi bi-printer"></i> Recibo
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-400">
                            No se han registrado cobros de arrendamiento de quioscos aún.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nuevo Quiosco -->
<div id="modalNuevoQuiosco" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 p-6 space-y-4">
        <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
            <i class="bi bi-shop text-amber-600"></i> Registrar Espacio de Quiosco / Cafetín
        </h3>
        <form action="{{ route('quioscos.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nombre o Número de Quiosco *</label>
                <input type="text" name="nombre" required placeholder="Ej: Quiosco N° 02 (Patio de Primaria)" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ubicación</label>
                <input type="text" name="ubicacion" placeholder="Ej: Costado del pabellón B" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modalNuevoQuiosco').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Cancelar</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-md">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Nueva Concesión (Licitación) -->
<div id="modalNuevaConcesion" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 p-6 space-y-4">
        <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
            <i class="bi bi-award text-amber-600"></i> Adjudicar Concesión de Quiosco (Ley 30021)
        </h3>
        <form action="{{ route('quioscos.concesion.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Seleccionar Quiosco *</label>
                <select name="quiosco_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    @foreach($quioscos as $q)
                    <option value="{{ $q->id }}">{{ $q->nombre }} ({{ $q->ubicacion }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nombre Concesionario *</label>
                    <input type="text" name="nombre_concesionario" required placeholder="Persona Natural o Jurídica" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">DNI / RUC *</label>
                    <input type="text" name="dni_ruc" required placeholder="10xxxxxxxx o DNI" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Canon Mensual (S/) *</label>
                    <input type="number" step="1" name="canon_mensual" required value="400.00" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold text-emerald-700">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Garantía (S/)</label>
                    <input type="number" step="1" name="garantia" value="400.00" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Fecha Inicio *</label>
                    <input type="date" name="fecha_inicio" required value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Fecha Fin *</label>
                    <input type="date" name="fecha_fin" required value="{{ date('Y-12-31') }}" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                </div>
            </div>

            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 flex items-center gap-2">
                <input type="checkbox" name="cumple_ley_alimentacion_saludable" id="chkLey" checked class="w-4 h-4 text-emerald-600 rounded">
                <label for="chkLey" class="text-xs font-semibold text-emerald-950">
                    Acredita cumplimiento de la Ley N° 30021 de Alimentación Saludable y expendio libre de octógonos.
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modalNuevaConcesion').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Cancelar</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-md">Adjudicar Contrato</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Registrar Pago de Canon Mensual -->
<div id="modalCobroCanon" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 p-6 space-y-4">
        <div>
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                <i class="bi bi-calendar-check"></i> Pago Mensual Obligatorio
            </span>
            <h3 class="font-bold text-slate-800 text-base flex items-center gap-2 mt-1">
                <i class="bi bi-cash-stack text-emerald-600"></i> Cobro de Canon Mensual de Quiosco
            </h3>
            <p id="labelQuioscoCobro" class="text-xs text-slate-500 font-semibold mt-0.5"></p>
        </div>

        <div class="bg-amber-50/70 p-3 rounded-xl border border-amber-200 text-xs text-amber-900 flex items-start gap-2">
            <i class="bi bi-info-circle-fill text-amber-600 mt-0.5"></i>
            <p class="leading-relaxed text-[11px]">
                El canon de arrendamiento de quioscos <strong>se paga al mes</strong> (mensualidades del año escolar). Al registrar el pago se emitirá el recibo oficial con correlativo y se registrará en el Libro de Caja de la APAFA.
            </p>
        </div>

        <div id="alertaTodosMesesPagados" class="hidden bg-emerald-50 border border-emerald-300 text-emerald-800 p-3 rounded-xl text-xs flex items-center gap-2">
            <i class="bi bi-check-circle-fill text-emerald-600 text-base"></i>
            <div>
                <strong class="font-bold">¡Cronograma al día!</strong> Todos los meses del contrato escolar ya están pagados.
            </div>
        </div>

        <form id="formCobroCanon" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Mes que se Cancela (Cuota Mensual) *</label>
                <select id="selectMesCobro" name="mes_correspondiente" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none" onchange="toggleMesManual(this.value)">
                </select>
                <input type="text" id="inputMesPersonalizado" name="mes_personalizado" placeholder="Escriba el mes (Ej: Noviembre 2026)" class="hidden mt-2 w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-bold">
                <p id="mesAyudaTexto" class="text-[11px] text-slate-400 mt-1">Los meses ya cancelados aparecen bloqueados para evitar pagos duplicados.</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Monto al Mes (S/) *</label>
                    <input type="number" step="0.5" id="inputMontoCanon" name="monto" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold text-emerald-700 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Método de Pago</label>
                    <select name="metodo_pago" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold">
                        <option value="EFECTIVO">Efectivo en Caja</option>
                        <option value="TRANSFERENCIA">Transferencia Bancaria</option>
                        <option value="YAPE_PLIN">Yape / Plin Directiva</option>
                        <option value="DEPOSITO_BANCARIO">Depósito en Cuenta</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Fecha de Cobro / Pago</label>
                <input type="date" name="fecha_pago" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Observaciones / N° Operación</label>
                <input type="text" name="observaciones" placeholder="N° de operación bancaria o voucher..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modalCobroCanon').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Cancelar</button>
                <button type="submit" id="btnSubmitCobro" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md flex items-center gap-1.5">
                    <i class="bi bi-check-circle"></i> Emitir Recibo del Mes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    window.cronogramasQuioscos = {
        @foreach($quioscos as $quioscoItem)
            @if($quioscoItem->concesionVigente)
                "{{ $quioscoItem->concesionVigente->id }}": @json($quioscoItem->concesionVigente->cronograma_mensual),
            @endif
        @endforeach
    };

    function abrirModalCobro(concesionId, quioscoNombre, canonMensual, mesSugerido) {
        document.getElementById('labelQuioscoCobro').textContent = quioscoNombre + ' - Canon: S/ ' + parseFloat(canonMensual).toFixed(2) + ' al mes';
        document.getElementById('inputMontoCanon').value = canonMensual;
        document.getElementById('formCobroCanon').action = `/quioscos/concesion/${concesionId}/pago`;

        const select = document.getElementById('selectMesCobro');
        select.innerHTML = '';

        let meses = [];
        if (window.cronogramasQuioscos && window.cronogramasQuioscos[concesionId]) {
            meses = window.cronogramasQuioscos[concesionId];
        }

        let primerMesDisponible = null;
        let tieneMesesPendientes = false;

        if (meses && meses.length > 0) {
            meses.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m.mes_nombre;

                if (m.pagado) {
                    opt.disabled = true;
                    let reciboTxt = m.pago && m.pago.comprobante_numero ? ` (${m.pago.comprobante_numero})` : '';
                    opt.textContent = `✓ ${m.mes_nombre} [CANCELADO${reciboTxt}]`;
                    opt.className = 'text-slate-400 bg-slate-100';
                } else {
                    tieneMesesPendientes = true;
                    if (!primerMesDisponible) {
                        primerMesDisponible = m.mes_nombre;
                    }
                    if (m.estado === 'VENCIDO') {
                        opt.textContent = `⚠️ ${m.mes_nombre} [Vencido en Mora - Pendiente]`;
                        opt.className = 'text-rose-700 font-bold';
                    } else if (m.estado === 'PENDIENTE') {
                        opt.textContent = `⏳ ${m.mes_nombre} [Mes en Curso - Por Pagar]`;
                        opt.className = 'text-amber-800 font-bold';
                    } else {
                        opt.textContent = `⏩ ${m.mes_nombre} [Pago Adelantado]`;
                        opt.className = 'text-indigo-700 font-medium';
                    }
                }

                select.appendChild(opt);
            });
        } else {
            const mesesDef = ['Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            const anio = new Date().getFullYear();
            mesesDef.forEach(m => {
                const opt = document.createElement('option');
                const nom = m + ' ' + anio;
                opt.value = nom;
                opt.textContent = nom + ' (Mensualidad)';
                select.appendChild(opt);
                if (!primerMesDisponible) primerMesDisponible = nom;
                tieneMesesPendientes = true;
            });
        }

        const optOtro = document.createElement('option');
        optOtro.value = '__OTRO__';
        optOtro.textContent = '✏️ Otro mes o periodo personalizado...';
        optOtro.className = 'text-indigo-700 font-bold';
        select.appendChild(optOtro);

        // Alerta de todos pagados
        const alerta = document.getElementById('alertaTodosMesesPagados');
        if (alerta) {
            if (!tieneMesesPendientes) {
                alerta.classList.remove('hidden');
            } else {
                alerta.classList.add('hidden');
            }
        }

        // Selección robusta del mes apropiado (nunca uno que ya esté pagado)
        let mesASeleccionar = null;
        if (mesSugerido) {
            for (let i = 0; i < select.options.length; i++) {
                if (!select.options[i].disabled && select.options[i].value.trim().toLowerCase() === String(mesSugerido).trim().toLowerCase()) {
                    mesASeleccionar = select.options[i].value;
                    break;
                }
            }
        }

        if (!mesASeleccionar && primerMesDisponible) {
            mesASeleccionar = primerMesDisponible;
        }

        if (mesASeleccionar) {
            select.value = mesASeleccionar;
        } else {
            select.value = '__OTRO__';
        }

        toggleMesManual(select.value);
        document.getElementById('modalCobroCanon').classList.remove('hidden');
    }

    function toggleMesManual(val) {
        const input = document.getElementById('inputMesPersonalizado');
        if (val === '__OTRO__') {
            input.classList.remove('hidden');
            input.required = true;
            input.focus();
        } else {
            input.classList.add('hidden');
            input.required = false;
        }
    }
</script>
@endsection
