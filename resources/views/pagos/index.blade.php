@extends('layouts.app')

@section('title', 'Punto de Venta (POS) y Caja APAFA')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- POS Register Payment Card (Form) -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm h-fit">
        <div class="flex items-center gap-2.5 pb-4 border-b border-slate-100 mb-5">
            <div class="h-9 w-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <i class="fa-solid fa-cash-register text-base"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800">Cobro en Ventanilla (POS)</h3>
                <p class="text-[11px] text-slate-400">Emisión asíncrona no bloqueante</p>
            </div>
        </div>

        <form action="{{ route('pagos.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <!-- Padre Selector -->
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Padre / Apoderado *</label>
                <select name="padre_familia_id" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">Seleccione un padre de familia...</option>
                    @foreach($padres as $p)
                        <option value="{{ $p->id }}">
                            {{ $p->padre_dni }} - {{ $p->nombre_completo }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Concepto -->
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Concepto de Pago *</label>
                <input type="text" name="pago_concepto" list="conceptos_sugeridos" required 
                       placeholder="Ej: CUOTA ORDINARIA APAFA 2026"
                       class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
                <datalist id="conceptos_sugeridos">
                    @foreach($aportes as $ap)
                        <option value="APORTE: {{ $ap->titulo }}">
                    @endforeach
                    <option value="MULTA REUNIÓN ASAMBLEA">
                    <option value="CARNET APAFA 2026">
                </datalist>
            </div>

            <!-- Monto -->
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Monto a Cobrar (S/ PEN) *</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-2.5 font-bold text-slate-400">S/</span>
                    <input type="number" step="0.50" min="0.50" name="monto_pago" required 
                           placeholder="50.00"
                           class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono font-bold text-slate-800">
                </div>
            </div>

            <!-- Tipo de Comprobante SUNAT -->
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Comprobante de Pago Electrónico *</label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="border border-slate-200 rounded-xl p-2.5 flex flex-col items-center gap-1 cursor-pointer hover:border-indigo-500 transition-colors">
                        <input type="radio" name="tipo_comprobante" value="BOLETA" checked class="text-indigo-600 focus:ring-indigo-500">
                        <span class="font-bold text-[11px] text-slate-700">Boleta</span>
                        <span class="text-[9px] text-slate-400 font-mono">B001</span>
                    </label>

                    <label class="border border-slate-200 rounded-xl p-2.5 flex flex-col items-center gap-1 cursor-pointer hover:border-indigo-500 transition-colors">
                        <input type="radio" name="tipo_comprobante" value="FACTURA" class="text-indigo-600 focus:ring-indigo-500">
                        <span class="font-bold text-[11px] text-slate-700">Factura</span>
                        <span class="text-[9px] text-slate-400 font-mono">F001</span>
                    </label>

                    <label class="border border-slate-200 rounded-xl p-2.5 flex flex-col items-center gap-1 cursor-pointer hover:border-indigo-500 transition-colors">
                        <input type="radio" name="tipo_comprobante" value="NOTA_VENTA" class="text-indigo-600 focus:ring-indigo-500">
                        <span class="font-bold text-[11px] text-slate-700">Recibo</span>
                        <span class="text-[9px] text-slate-400 font-mono">NV01</span>
                    </label>
                </div>
            </div>

            <!-- Modalidad D.S. N.º 007-2026-MINEDU -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                <label class="block font-semibold text-slate-700">Modalidad de Pago (D.S. N.º 007-2026)</label>
                <select name="modalidad" id="selectModalidad" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs font-bold" onchange="toggleFraccionamiento(this.value)">
                    <option value="CONTADO">Pago Total al Contado</option>
                    <option value="FRACCIONADO">Convenio de Fraccionamiento (Cuotas)</option>
                    <option value="COMPENSACION_FAENA">Compensación por Faena Escolar</option>
                </select>

                <div id="boxFraccionamiento" class="grid grid-cols-3 gap-2 hidden pt-1">
                    <div>
                        <span class="text-[10px] text-slate-500 font-bold">N° Cuota</span>
                        <input type="number" name="numero_cuota" value="1" min="1" class="w-full px-2 py-1 border rounded text-center font-bold">
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-500 font-bold">De Total</span>
                        <input type="number" name="total_cuotas" value="2" min="1" class="w-full px-2 py-1 border rounded text-center font-bold">
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-500 font-bold">Saldo (S/)</span>
                        <input type="number" step="0.5" name="saldo_pendiente" value="0.00" class="w-full px-2 py-1 border rounded text-center font-bold text-rose-600">
                    </div>
                </div>
            </div>

            <!-- Notice async -->
            <div class="p-3 rounded-xl bg-indigo-50/60 border border-indigo-100 flex items-start gap-2 text-indigo-900 text-[11px] leading-relaxed">
                <i class="fa-solid fa-bolt text-indigo-600 mt-0.5"></i>
                <p>El ticket se genera al instante. El comprobante fiscal se firma y remite a SUNAT en segundo plano sin ralentizar el cobro.</p>
            </div>

            <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-md shadow-emerald-600/30 transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-circle-check"></i>
                Registrar y Emitir CPE
            </button>
        </form>
    </div>

    <!-- Payments History Table -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h4 class="text-sm font-bold text-slate-800">Historial de Cobros y Estado SUNAT</h4>
                <p class="text-xs text-slate-500">Comprobantes procesados para esta institución</p>
            </div>
            <span class="text-xs font-mono font-bold px-2 py-1 rounded bg-slate-100 text-slate-700">
                Total: {{ $pagos->total() }}
            </span>
        </div>

        <div class="flex-1 overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-500 font-semibold border-b border-slate-200/60 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-4 py-3.5">Comprobante</th>
                        <th class="px-4 py-3.5">Padre de Familia</th>
                        <th class="px-4 py-3.5">Concepto</th>
                        <th class="px-4 py-3.5">Monto</th>
                        <th class="px-4 py-3.5">Estado Microservicio</th>
                        <th class="px-4 py-3.5 text-right">Ticket</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pagos as $pago)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-3.5">
                                <span class="font-mono font-bold text-slate-800">{{ $pago->serie }}-{{ $pago->numero }}</span>
                                <span class="block text-[10px] text-slate-400">{{ $pago->tipo_comprobante }} &bull; {{ $pago->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td class="px-4 py-3.5 font-bold text-slate-700">
                                {{ $pago->padreFamilia->nombre_completo ?? 'N/A' }}
                                <span class="block text-[10px] text-slate-400 font-normal">DNI: {{ $pago->padreFamilia->padre_dni ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-slate-600 text-[11px] max-w-xs">
                                <span class="font-medium text-slate-800">{{ $pago->pago_concepto }}</span>
                                @if($pago->modalidad === 'FRACCIONADO')
                                    <span class="block text-[10px] text-purple-600 font-bold mt-0.5">
                                        Cuota {{ $pago->numero_cuota }} de {{ $pago->total_cuotas }} @if($pago->saldo_pendiente > 0)(Saldo: S/ {{ number_format($pago->saldo_pendiente, 2) }})@endif
                                    </span>
                                @elseif($pago->modalidad === 'COMPENSACION_FAENA')
                                    <span class="block text-[10px] text-emerald-600 font-bold mt-0.5">
                                        Compensación Faena (D.S. 007-2026)
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 font-extrabold text-emerald-600 font-mono">
                                S/ {{ number_format($pago->monto_pago, 2) }}
                            </td>
                            <td class="px-4 py-3.5">
                                @if($pago->estado_facturacion === 'EMITIDO')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-check"></i> Emitido SUNAT
                                    </span>
                                    @if($pago->hash_cpe)
                                        <span class="block text-[9px] font-mono text-slate-400 truncate max-w-[120px]" title="Hash SHA-256: {{ $pago->hash_cpe }}">
                                            Hash: {{ substr($pago->hash_cpe, 0, 10) }}...
                                        </span>
                                    @endif
                                @elseif($pago->estado_facturacion === 'EN_COLA')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">
                                        <i class="fa-solid fa-spinner fa-spin"></i> En Cola Async
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                        {{ $pago->estado_facturacion }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <a href="{{ route('pagos.ticket', $pago) }}" target="_blank" 
                                   class="px-2.5 py-1.5 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-700 font-semibold rounded-lg text-[11px] transition-colors inline-flex items-center gap-1" title="Ver / Imprimir Ticket">
                                    <i class="fa-solid fa-print"></i> Ticket
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-400">
                                No hay cobros registrados en caja todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pagos->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $pagos->links() }}
            </div>
        @endif
    </div>

</div>

<script>
    function toggleFraccionamiento(val) {
        const box = document.getElementById('boxFraccionamiento');
        if (val === 'FRACCIONADO') {
            box.classList.remove('hidden');
        } else {
            box.classList.add('hidden');
        }
    }
</script>
@endsection
