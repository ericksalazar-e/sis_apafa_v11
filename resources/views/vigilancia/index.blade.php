@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header con Leyenda D.S. 007-2026-MINEDU -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-300">
                    <i class="bi bi-shield-lock-fill mr-1"></i> D.S. N.º 007-2026-MINEDU (Autonomía de Vigilancia)
                </span>
                <span class="text-xs text-slate-500 font-medium">Fiscalización Contable y Auditoría Preventiva</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <i class="bi bi-journal-check text-rose-600"></i> Consejo de Vigilancia de la APAFA
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Fiscalización autónoma e ininterrumpida de los libros contables, ingresos por cuotas y quioscos, contratos y ejecución presupuestal del POA.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button onclick="document.getElementById('modalNuevoDictamen').classList.remove('hidden')" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-2 shadow-sm">
                <i class="bi bi-patch-check"></i> Emitir Dictamen de Auditoría
            </button>
        </div>
    </div>

    <!-- Balance Auditado en Vivo -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Ingresos Auditados</p>
                <h3 class="text-2xl font-extrabold text-slate-800">S/ {{ number_format($totalIngresos, 2) }}</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Cuotas (S/ {{ number_format($totalIngresosCuotas, 2) }}) + Quioscos</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-cart-check"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Gastos POA Ejecutados</p>
                <h3 class="text-2xl font-extrabold text-rose-600">S/ {{ number_format($totalGastosPoa, 2) }}</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Comprobantes verificados</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl {{ $saldoEnCaja >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center text-xl font-bold">
                <i class="bi {{ $saldoEnCaja >= 0 ? 'bi-safe2' : 'bi-exclamation-octagon' }}"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">{{ $saldoEnCaja >= 0 ? 'Saldo Real en Cuenta' : 'Déficit Real en Cuenta' }}</p>
                <h3 class="text-2xl font-extrabold {{ $saldoEnCaja >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">S/ {{ number_format($saldoEnCaja, 2) }}</h3>
                <p class="text-[11px] {{ $saldoEnCaja >= 0 ? 'text-emerald-600' : 'text-rose-600' }} mt-0.5">{{ $saldoEnCaja >= 0 ? 'Fondos disponibles' : 'Gastos superan a los ingresos' }}</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-file-earmark-diff"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Dictámenes Emitidos</p>
                <h3 class="text-2xl font-extrabold text-purple-600">{{ $totalDictamenes }}</h3>
                <p class="text-[11px] text-purple-600 mt-0.5">
                    {{ $totalFavorables }} Favorables • {{ $totalObservados }} Observados
                    @if(($totalDesaprobados ?? 0) > 0) • {{ $totalDesaprobados }} Desaprobados @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Lista de Dictámenes y Auditorías -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <i class="bi bi-file-earmark-medical text-rose-600"></i> Informes de Fiscalización y Dictámenes del Consejo
            </h3>
            <span class="text-xs text-slate-400">Publicación periódica para la Asamblea General</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3">Periodo / Fecha</th>
                        <th class="px-5 py-3">Título del Dictamen</th>
                        <th class="px-5 py-3">Resultado</th>
                        <th class="px-5 py-3">Ingresos Auditados</th>
                        <th class="px-5 py-3">Gastos Auditados</th>
                        <th class="px-5 py-3">Auditor</th>
                        <th class="px-5 py-3 text-right">Documento</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php $lastPeriodo = null; @endphp
                    @forelse($dictamenes as $d)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3 whitespace-nowrap">
                            @if($d->periodo !== $lastPeriodo)
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-slate-800 font-bold text-xs">
                                    <i class="bi bi-calendar-event text-rose-600"></i> {{ $d->periodo }}
                                </div>
                                <span class="text-[11px] text-slate-400 block mt-0.5 ml-1">{{ \Carbon\Carbon::parse($d->created_at)->format('d/m/Y') }}</span>
                                @php $lastPeriodo = $d->periodo; @endphp
                            @else
                                <span class="text-xs text-slate-400 italic pl-3" title="Mismo periodo de fiscalización">↳ Mismo periodo</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 font-semibold text-slate-700">
                            {{ $d->titulo }}
                        </td>
                        <td class="px-5 py-3">
                            @if($d->tipo_dictamen === 'FAVORABLE')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">FAVORABLE</span>
                            @elseif($d->tipo_dictamen === 'OBSERVADO')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">OBSERVADO</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">DESAPROBADO</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 font-bold text-slate-800 text-xs whitespace-nowrap">
                            S/ {{ number_format($d->monto_ingresos_auditado, 2) }}
                        </td>
                        <td class="px-5 py-3 font-bold text-rose-600 text-xs whitespace-nowrap">
                            S/ {{ number_format($d->monto_gastos_auditado, 2) }}
                        </td>
                        <td class="px-5 py-3 text-xs text-slate-500 whitespace-nowrap">
                            {{ $d->user->name ?? 'Consejo Vigilancia' }}
                        </td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5 justify-end">
                                <a href="{{ route('vigilancia.imprimir', $d->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 hover:bg-slate-900 hover:text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                                    <i class="bi bi-file-earmark-pdf"></i> Ver Dictamen
                                </a>
                                @if(in_array(auth()->user()->role ?? '', ['ADMIN', 'COMITE_VIGILANCIA']))
                                <form action="{{ route('vigilancia.destroy', $d->id) }}" method="POST" 
                                      onsubmit="return confirmDelete(event, this, '¿Eliminar dictamen de auditoría?', 'Esta acción no se puede deshacer. El dictamen será removido del registro de fiscalización.');" 
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 rounded-lg transition-colors" title="Eliminar Dictamen">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-400">
                            No se han emitido dictámenes de vigilancia aún.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dictamenes->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50">
            {{ $dictamenes->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Emitir Dictamen -->
<div id="modalNuevoDictamen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 p-6 space-y-4">
        <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
            <i class="bi bi-patch-check-fill text-rose-600"></i> Emitir Dictamen de Auditoría Interna
        </h3>
        <p class="text-xs text-slate-500">Conforme a las atribuciones de fiscalización del D.S. N.º 007-2026-MINEDU</p>

        <form action="{{ route('vigilancia.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Periodo Auditado *</label>
                <input list="listaPeriodos" type="text" name="periodo" required placeholder="Ej: I Trimestre {{ date('Y') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-bold focus:ring-rose-500 focus:border-rose-500">
                <datalist id="listaPeriodos">
                    <option value="I Trimestre {{ date('Y') }}">
                    <option value="II Trimestre {{ date('Y') }}">
                    <option value="III Trimestre {{ date('Y') }}">
                    <option value="IV Trimestre {{ date('Y') }}">
                    <option value="Balance Anual {{ date('Y') }}">
                </datalist>
                @if(!empty($periodosExistentes))
                <p class="text-[11px] text-slate-400 mt-1">
                    <i class="bi bi-info-circle"></i> Periodos ya emitidos: 
                    <span class="font-medium text-slate-600">{{ implode(', ', $periodosExistentes) }}</span> (no se pueden duplicar).
                </p>
                @endif
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Título del Dictamen *</label>
                <input type="text" name="titulo" required placeholder="Ej: Informe de Fiscalización de Gastos de Mantenimiento y Cuotas APAFA" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-bold">
            </div>

            @if($saldoEnCaja < 0)
            <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-900 text-xs flex items-start gap-2.5">
                <i class="bi bi-exclamation-triangle-fill text-rose-600 text-base flex-shrink-0 mt-0.5"></i>
                <div>
                    <p class="font-bold">Advertencia de Déficit en Caja (S/ {{ number_format($saldoEnCaja, 2) }}):</p>
                    <p class="mt-0.5 text-[11px] text-rose-700 leading-normal">
                        La institución registra una deuda/déficit porque los gastos ejecutados superan los ingresos recaudados. Según las normas contables del D.S. N.º 007-2026-MINEDU, <strong>no puede calificarse como FAVORABLE</strong>. Debe emitirse como <strong>OBSERVADO</strong> o <strong>DESAPROBADO</strong>.
                    </p>
                </div>
            </div>
            @endif

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Conclusión del Dictamen *</label>
                <select name="tipo_dictamen" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-bold">
                    @if($saldoEnCaja < 0)
                        <option value="OBSERVADO" selected>OBSERVADO (Déficit de caja / Gastos superan ingresos recaudados)</option>
                        <option value="DESAPROBADO">DESAPROBADO (Déficit crítico o irregularidades en administración)</option>
                        <option value="FAVORABLE" disabled class="text-slate-400">FAVORABLE (Bloqueado: Existe déficit en cuenta)</option>
                    @else
                        <option value="FAVORABLE" selected>FAVORABLE (Cuentas y comprobantes conformes sin observaciones)</option>
                        <option value="OBSERVADO">OBSERVADO (Existen gastos o comprobantes pendientes de subsanar)</option>
                        <option value="DESAPROBADO">DESAPROBADO (Irregularidades graves detectadas en los fondos)</option>
                    @endif
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Detalles de la Auditoría Contable *</label>
                <textarea name="detalles_auditoria" rows="3" required placeholder="Se revisaron los libros de caja, facturas electrónicas, depósitos bancarios..." class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Observaciones y Recomendaciones a la Directiva</label>
                <textarea name="observaciones_recomendaciones" rows="2" placeholder="Recomendaciones para optimizar la rendición de cuentas..." class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modalNuevoDictamen').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Cancelar</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md">Emitir Dictamen Oficial</button>
            </div>
        </form>
    </div>
</div>
@endsection
