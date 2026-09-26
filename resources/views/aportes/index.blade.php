@extends('layouts.app')

@section('title', 'Aportes y Cuotas Ordinarias / Extraordinarias')

@section('content')
<div class="space-y-6">

    <!-- Banner Informativo Legal: Ley 28628 -->
    <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-slate-900 rounded-3xl p-6 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6 border border-indigo-700/50">
        <div class="space-y-2 max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-indigo-200 text-xs font-semibold backdrop-blur-sm border border-white/15">
                <i class="fa-solid fa-scale-balanced text-amber-400"></i> Marco Legal Peruano: Ley N° 28628 & D.S. N° 004-2006-ED
            </div>
            <h3 class="text-lg md:text-xl font-black tracking-tight text-white">
                Cuota Ordinaria Anual: Máximo 1.5% de la UIT (Por Padre de Familia)
            </h3>
            <p class="text-xs text-indigo-100/90 leading-relaxed">
                El <strong class="text-amber-300">Artículo 74</strong> del Reglamento de la Ley de APAFA establece que la cuota ordinaria anual es fijada en asamblea y 
                <strong class="text-white">no puede superar el 1.5% de la Unidad Impositiva Tributaria (UIT) vigente</strong> (Tope legal: <strong class="text-amber-300">S/ 80.25</strong> para UIT 2026 de S/ 5,350.00). 
                El aporte se efectúa <strong class="text-white underline decoration-amber-400 decoration-2">por padre de familia / asociado</strong>, sin multiplicar por la cantidad de hijos matriculados.
            </p>
        </div>
        <div class="flex-shrink-0 bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/15 text-center min-w-[170px]">
            <span class="text-[10px] uppercase font-bold text-indigo-200 block">Tope Legal UIT (1.5%)</span>
            <span class="text-2xl font-black text-amber-300">S/ 80.25</span>
            <span class="text-[10px] text-indigo-200 block mt-0.5">UIT Ref: S/ 5,350.00</span>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-slate-800">Catálogo de Aportes y Cuotas</h3>
            <p class="text-xs text-slate-500">Gestión de cuotas ordinarias y aportes extraordinarios asignados a los asociados</p>
        </div>
        <button onclick="document.getElementById('modal-nuevo-aporte').classList.remove('hidden')" 
                class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm transition-all">
            <i class="fa-solid fa-plus"></i> Crear Nuevo Aporte
        </button>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs shadow-sm flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-emerald-600 text-lg flex-shrink-0"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs shadow-sm flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg flex-shrink-0"></i>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs shadow-sm flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation text-rose-500 text-lg flex-shrink-0 mt-0.5"></i>
            <div>
                <strong class="font-bold block">No se pudo registrar el aporte:</strong>
                <ul class="list-disc list-inside mt-1 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($aportes as $ap)
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono">
                            S/ {{ number_format($ap->monto, 2) }}
                        </span>
                        <div class="flex items-center gap-1.5">
                            @if(($ap->tipo ?? 'ORDINARIA') === 'ORDINARIA')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    <i class="fa-solid fa-shield-halved text-[9px]"></i> Ordinaria (Ley 28628)
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    Extraordinaria
                                </span>
                            @endif

                            @if(in_array(auth()->user()->rol ?? '', ['ADMIN', 'TESORERO']))
                                <form action="{{ route('aportes.destroy', $ap) }}" method="POST" 
                                      onsubmit="return confirmDelete(event, this, '¿Eliminar cuota \'{{ addslashes($ap->titulo) }}\'?', 'Esta acción no se puede deshacer. Se eliminarán las cuotas asignadas si aún no tienen pagos recaudados.');" 
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Eliminar Cuota" class="w-6 h-6 rounded-lg text-slate-300 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition-all ml-1">
                                        <i class="fa-solid fa-trash-can text-[11px]"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <h4 class="font-bold text-slate-800 text-sm mb-1">{{ $ap->titulo }}</h4>
                    <p class="text-xs text-slate-500 leading-relaxed line-clamp-2">
                        {{ $ap->descripcion ?? 'Aporte asignado por padre de familia conforme al padrón institucional.' }}
                    </p>
                </div>
                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span class="flex items-center gap-1.5 font-medium">
                        <i class="fa-solid fa-users text-slate-400"></i>
                        {{ $ap->aportes_padres_count }} asociados asignados
                    </span>
                    <span class="text-slate-400 font-mono text-[11px]">
                        {{ $ap->fecha ? $ap->fecha->format('d/m/Y') : 'Vigente' }}
                    </span>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl p-10 text-center text-slate-400 border border-slate-200/80">
                No hay aportes registrados para esta institución.
            </div>
        @endforelse
    </div>

    @if($aportes->hasPages())
        <div class="pt-2">
            {{ $aportes->links() }}
        </div>
    @endif

</div>

<!-- Modal Nuevo Aporte -->
<div id="modal-nuevo-aporte" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-hand-holding-dollar text-indigo-600"></i> Configurar Cuota de APAFA
            </h3>
            <button onclick="document.getElementById('modal-nuevo-aporte').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('aportes.store') }}" method="POST" class="space-y-4 text-xs" id="form-aporte" onsubmit="const btn = document.getElementById('btn-guardar-aporte'); btn.disabled = true; btn.innerHTML = '<i class=\'fa-solid fa-spinner fa-spin\'></i> Guardando...';">
            @csrf
            
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Tipo de Cuota / Aporte *</label>
                <select name="tipo" id="input-tipo" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-medium" onchange="calcularTopeLegal()">
                    <option value="ORDINARIA" selected>Cuota Ordinaria Anual (Sujeta a tope legal 1.5% UIT - Ley 28628)</option>
                    <option value="EXTRAORDINARIA">Aporte Extraordinario (Aprobado expresamente por Asamblea General)</option>
                </select>
                <p class="text-[11px] text-slate-400 mt-1">
                    La cuota ordinaria es por Padre de Familia y no por número de hijos matriculados.
                </p>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Título de la Cuota *</label>
                <input type="text" name="titulo" required placeholder="Ej: CUOTA ORDINARIA ANUAL APAFA 2026" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 uppercase">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Monto de la Cuota (S/ PEN) *</label>
                    <input type="number" step="0.50" min="0.5" name="monto" id="input-monto" required placeholder="50.00" value="50.00" 
                           class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono font-bold"
                           oninput="calcularTopeLegal()">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">UIT de Referencia (S/) *</label>
                    <input type="number" step="1" min="1000" name="uit_vigente" id="input-uit" required value="5350" 
                           class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono text-slate-600"
                           oninput="calcularTopeLegal()">
                </div>
            </div>

            <!-- Caja Informativa Dinámica de Cumplimiento de Ley 28628 -->
            <div id="caja-tope-legal" class="p-3.5 bg-indigo-50/80 rounded-2xl border border-indigo-100 space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-indigo-900 flex items-center gap-1.5">
                        <i class="fa-solid fa-scale-balanced text-indigo-600"></i> Tope Máximo Legal (1.5% UIT):
                    </span>
                    <span id="label-tope-legal" class="font-mono font-black text-indigo-700 text-xs">S/ 80.25</span>
                </div>
                <p id="msg-legal-status" class="text-[11px] text-indigo-700">
                    ✓ El monto cumple con el Artículo 74 de la Ley N° 28628 (no supera el 1.5% de la UIT).
                </p>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Fecha Límite / Vencimiento *</label>
                <input type="date" name="fecha" id="input-fecha-aporte" required value="{{ date('Y-m-d') }}" 
                       onchange="verificarFechaUnica()" oninput="verificarFechaUnica()"
                       class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
                <p id="alerta-fecha-duplicada" class="text-[11px] text-rose-600 font-bold mt-1.5 hidden flex items-center gap-1.5">
                    <i class="fa-solid fa-triangle-exclamation"></i> Ya existe un aporte o cuota programada para esta fecha. Conforme al reglamento de APAFA, no se pueden registrar múltiples aportes el mismo día.
                </p>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Descripción / Acuerdo de Asamblea</label>
                <textarea name="descripcion" rows="2" placeholder="Ej: Aprobada en asamblea general ordinaria de octubre/noviembre." class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-slate-600 text-[11px] flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-sm flex-shrink-0"></i>
                <span>Al guardar, este aporte se asignará <strong>1 sola vez a cada padre de familia (asociado)</strong>, sin duplicar por sus hijos.</span>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-nuevo-aporte').classList.add('hidden')" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl font-semibold">
                    Cancelar
                </button>
                <button type="submit" id="btn-guardar-aporte" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-md shadow-indigo-600/30 transition-all">
                    Guardar y Asignar a Padres
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const fechasAportesRegistradas = @json($aportes->pluck('fecha')->map(fn($f) => \Carbon\Carbon::parse($f)->format('Y-m-d'))->values());

function verificarFechaUnica() {
    const inputFecha = document.getElementById('input-fecha-aporte');
    const alertaFecha = document.getElementById('alerta-fecha-duplicada');
    const btnGuardar = document.getElementById('btn-guardar-aporte');
    if (!inputFecha || !alertaFecha || !btnGuardar) return;

    const fechaSeleccionada = inputFecha.value;
    if (fechasAportesRegistradas.includes(fechaSeleccionada)) {
        alertaFecha.classList.remove('hidden');
        inputFecha.classList.add('border-rose-500', 'ring-1', 'ring-rose-500');
        btnGuardar.disabled = true;
        btnGuardar.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
        alertaFecha.classList.add('hidden');
        inputFecha.classList.remove('border-rose-500', 'ring-1', 'ring-rose-500');
        calcularTopeLegal(); // Re-evaluar botón según tope legal
    }
}

function calcularTopeLegal() {
    const tipo = document.getElementById('input-tipo').value;
    const monto = parseFloat(document.getElementById('input-monto').value) || 0;
    const uit = parseFloat(document.getElementById('input-uit').value) || 5350;
    const tope = +(uit * 0.015).toFixed(2);

    const caja = document.getElementById('caja-tope-legal');
    const labelTope = document.getElementById('label-tope-legal');
    const msg = document.getElementById('msg-legal-status');
    const btn = document.getElementById('btn-guardar-aporte');
    const inputFecha = document.getElementById('input-fecha-aporte');

    // Si la fecha ya está ocupada, no habilitar el botón
    if (inputFecha && fechasAportesRegistradas.includes(inputFecha.value)) {
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
        return;
    }

    labelTope.innerText = 'S/ ' + tope.toFixed(2);

    if (tipo === 'ORDINARIA') {
        caja.classList.remove('hidden');
        if (monto > tope) {
            caja.className = 'p-3.5 bg-rose-50 rounded-2xl border border-rose-200 space-y-1';
            labelTope.className = 'font-mono font-black text-rose-700 text-xs';
            msg.className = 'text-[11px] font-semibold text-rose-700';
            msg.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ¡ALERTA! El monto (S/ ' + monto.toFixed(2) + ') excede el tope legal del 1.5% de la UIT (S/ ' + tope.toFixed(2) + ') fijado por la Ley N° 28628 (Art. 74). Corrija el monto para continuar.';
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            caja.className = 'p-3.5 bg-emerald-50 rounded-2xl border border-emerald-200 space-y-1';
            labelTope.className = 'font-mono font-black text-emerald-700 text-xs';
            msg.className = 'text-[11px] font-medium text-emerald-700';
            msg.innerHTML = '<i class="fa-solid fa-circle-check"></i> Cumplimiento Legal: El monto está dentro del 1.5% de la UIT permitido por ley (Tope: S/ ' + tope.toFixed(2) + ').';
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    } else {
        caja.className = 'p-3.5 bg-amber-50 rounded-2xl border border-amber-200 space-y-1';
        labelTope.className = 'font-mono font-black text-amber-700 text-xs';
        msg.className = 'text-[11px] text-amber-800';
        msg.innerHTML = '<i class="fa-solid fa-circle-info"></i> Aporte Extraordinario: No aplica el tope del 1.5% de la UIT, pero requiere aprobación formal de la Asamblea General.';
        btn.disabled = false;
        btn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
}
document.addEventListener('DOMContentLoaded', () => {
    calcularTopeLegal();
    verificarFechaUnica();
});
</script>
@endsection
