@extends('layouts.app')

@section('title', 'Rendición de Cuentas - Ejecución del POA')

@section('content')
<div class="space-y-6">

    <!-- Banner Informativo: POA y Ley 28628 -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6 border border-indigo-900/60">
        <div class="space-y-2 max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-indigo-200 text-xs font-semibold backdrop-blur-sm border border-white/15">
                <i class="fa-solid fa-scale-balanced text-amber-400"></i> Fiscalización y Rendición de Cuentas (Ley N° 28628 / D.S. N° 004-2006-ED)
            </div>
            <h3 class="text-lg md:text-xl font-black tracking-tight text-white">
                Ejecución Presupuestal del Plan Operativo Anual (POA)
            </h3>
            <p class="text-xs text-slate-300 leading-relaxed">
                Los fondos de la APAFA son de carácter <strong class="text-amber-300">estrictamente complementario</strong> para necesidades no cubiertas por el presupuesto estatal del PRONIED.
                Cada sol egresado debe respaldarse con comprobante de pago oficial (boleta/factura) y responder a los acuerdos de la Asamblea General.
            </p>
        </div>
        @if(auth()->user()->rol !== 'COMITE_VIGILANCIA')
            <button onclick="document.getElementById('modal-nuevo-gasto').classList.remove('hidden')" 
                    class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-black flex items-center gap-2 shadow-lg shadow-emerald-600/30 transition-all flex-shrink-0">
                <i class="fa-solid fa-plus-circle text-sm"></i> Registrar Gasto del POA
            </button>
        @else
            <div class="px-4 py-2.5 rounded-2xl bg-amber-500/15 border border-amber-400/30 text-amber-300 text-xs font-bold flex items-center gap-2 flex-shrink-0">
                <i class="fa-solid fa-eye"></i> Perfil Auditor: Solo Lectura
            </div>
        @endif
    </div>

    <!-- KPIs Financieros de la APAFA -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-2xl flex-shrink-0">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div>
                <span class="text-[11px] uppercase font-bold text-slate-400 tracking-wider block">Total Recaudado (Ingresos)</span>
                <span class="text-2xl font-black text-slate-900 font-mono">S/ {{ number_format($totalRecaudado, 2) }}</span>
                <span class="text-[11px] text-emerald-600 font-semibold block mt-0.5">Pagos validados en sistema</span>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-2xl flex-shrink-0">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div>
                <span class="text-[11px] uppercase font-bold text-slate-400 tracking-wider block">Total Invertido (Gastos POA)</span>
                <span class="text-2xl font-black text-rose-600 font-mono">S/ {{ number_format($totalGastos, 2) }}</span>
                <span class="text-[11px] text-slate-400 font-medium block mt-0.5">{{ $gastos->total() }} egresos sustentados</span>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl {{ $saldoDisponible >= 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-rose-100 text-rose-700' }} flex items-center justify-center font-bold text-2xl flex-shrink-0">
                <i class="fa-solid fa-vault"></i>
            </div>
            <div>
                <span class="text-[11px] uppercase font-bold text-slate-400 tracking-wider block">Saldo Disponible en Cuenta</span>
                <span class="text-2xl font-black {{ $saldoDisponible >= 0 ? 'text-indigo-600' : 'text-rose-600' }} font-mono">S/ {{ number_format($saldoDisponible, 2) }}</span>
                <span class="text-[11px] text-slate-500 font-medium block mt-0.5">Cuenta Mancomunada APAFA</span>
            </div>
        </div>
    </div>

    <!-- Desglose por las 5 Áreas Legales del POA -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="p-4 bg-white rounded-2xl border border-indigo-100 shadow-sm">
            <div class="flex items-center gap-2 text-indigo-600 text-xs font-bold mb-1">
                <i class="fa-solid fa-laptop-code"></i> Tecnología
            </div>
            <span class="text-base font-black text-slate-800 font-mono block">S/ {{ number_format($porCategoria['TECNOLOGIA'], 2) }}</span>
            <span class="text-[10px] text-slate-400">Equipos y plataformas</span>
        </div>
        <div class="p-4 bg-white rounded-2xl border border-emerald-100 shadow-sm">
            <div class="flex items-center gap-2 text-emerald-600 text-xs font-bold mb-1">
                <i class="fa-solid fa-futbol"></i> Deportes
            </div>
            <span class="text-base font-black text-slate-800 font-mono block">S/ {{ number_format($porCategoria['DEPORTE_LUDICO'], 2) }}</span>
            <span class="text-[10px] text-slate-400">Lúdico y psicomotriz</span>
        </div>
        <div class="p-4 bg-white rounded-2xl border border-purple-100 shadow-sm">
            <div class="flex items-center gap-2 text-purple-600 text-xs font-bold mb-1">
                <i class="fa-solid fa-masks-theater"></i> Cultural
            </div>
            <span class="text-base font-black text-slate-800 font-mono block">S/ {{ number_format($porCategoria['CULTURAL_TALLERES'], 2) }}</span>
            <span class="text-[10px] text-slate-400">Talleres y escuela padres</span>
        </div>
        <div class="p-4 bg-white rounded-2xl border border-rose-100 shadow-sm">
            <div class="flex items-center gap-2 text-rose-600 text-xs font-bold mb-1">
                <i class="fa-solid fa-kit-medical"></i> Emergencias
            </div>
            <span class="text-base font-black text-slate-800 font-mono block">S/ {{ number_format($porCategoria['EMERGENCIAS_BIENESTAR'], 2) }}</span>
            <span class="text-[10px] text-slate-400">Botiquín y bienestar</span>
        </div>
        <div class="p-4 bg-white rounded-2xl border border-amber-100 shadow-sm col-span-2 sm:col-span-1">
            <div class="flex items-center gap-2 text-amber-600 text-xs font-bold mb-1">
                <i class="fa-solid fa-briefcase"></i> Operativo
            </div>
            <span class="text-base font-black text-slate-800 font-mono block">S/ {{ number_format($porCategoria['GESTION_OPERATIVA'], 2) }}</span>
            <span class="text-[10px] text-slate-400">Materiales y trámites</span>
        </div>
    </div>

    <!-- Tabla Detallada de Gastos Ejecutados -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
            <h4 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-list-check text-indigo-600"></i> Registro Cronológico de Gastos y Comprobantes
            </h4>
            <span class="text-xs text-slate-400 font-medium">Auditoría permanente por el Comité de Vigilancia</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-400 uppercase font-bold border-b border-slate-100 text-[11px]">
                    <tr>
                        <th class="py-3 px-6">Fecha</th>
                        <th class="py-3 px-4">Rubro / Categoría</th>
                        <th class="py-3 px-4">Concepto y Descripción</th>
                        <th class="py-3 px-4">Proveedor / Comprobante</th>
                        <th class="py-3 px-4 text-right">Monto (S/)</th>
                        <th class="py-3 px-6 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($gastos as $g)
                        @php
                            $cat = $g->categoria_info;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-6 font-mono text-slate-700 whitespace-nowrap">
                                {{ $g->fecha ? $g->fecha->format('d/m/Y') : '--' }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold {{ $cat['bg'] }} {{ $cat['text'] }} border {{ $cat['border'] }}">
                                    <i class="{{ $cat['icono'] }}"></i> {{ $cat['nombre'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="font-bold text-slate-800">{{ $g->titulo }}</div>
                                <div class="text-[11px] text-slate-400 truncate mt-0.5">{{ $g->descripcion ?? 'Sin descripción adicional.' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-700">{{ $g->proveedor ?? 'Proveedor local' }}</div>
                                <div class="text-[11px] font-mono text-indigo-600">{{ $g->nro_comprobante ? 'Doc: ' . $g->nro_comprobante : 'Sustentado' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-black text-rose-600 text-sm whitespace-nowrap">
                                S/ {{ number_format($g->monto, 2) }}
                            </td>
                            <td class="py-3.5 px-6 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    @if($g->foto_comprobante)
                                        <a href="{{ asset($g->foto_comprobante) }}" target="_blank" 
                                           class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors" title="Ver comprobante adjunto">
                                            <i class="fa-solid fa-paperclip"></i>
                                        </a>
                                    @endif

                                    @if(auth()->user()->rol === 'ADMIN' || auth()->user()->rol === 'TESORERO')
                                        <form action="{{ route('gastos_poa.destroy', $g) }}" method="POST" 
                                              onsubmit="return confirmDelete(event, this, '¿Anular registro de egreso / gasto?', 'Esta acción no se puede deshacer y revertirá el egreso de S/ {{ number_format($g->monto, 2) }} en el balance.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors" title="Eliminar gasto">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 italic">
                                No hay gastos registrados aún en el Plan Operativo Anual (POA).
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($gastos->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $gastos->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Registrar Nuevo Gasto POA -->
<div id="modal-nuevo-gasto" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-receipt text-indigo-600"></i> Registrar Egreso del POA
            </h3>
            <button onclick="document.getElementById('modal-nuevo-gasto').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('gastos_poa.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Rubro del POA (Ley N° 28628) *</label>
                <select name="categoria" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-medium">
                    <option value="TECNOLOGIA">💻 Tecnología Educativa y Sistemas</option>
                    <option value="DEPORTE_LUDICO">⚽ Deporte y Psicomotricidad</option>
                    <option value="CULTURAL_TALLERES">🎭 Actividades Culturales y Escuela de Padres</option>
                    <option value="EMERGENCIAS_BIENESTAR">🩺 Botiquín y Emergencias Escolares</option>
                    <option value="GESTION_OPERATIVA">💼 Gestión Operativa APAFA</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Concepto del Gasto *</label>
                <input type="text" name="titulo" required placeholder="Ej: Adquisición de proyector multimedia para aula de innovación" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Monto del Gasto (S/) *</label>
                    <input type="number" step="0.10" min="0.5" name="monto" required placeholder="1200.00" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono font-bold">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Fecha de Ejecución *</label>
                    <input type="date" name="fecha" required value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Proveedor / Razón Social</label>
                    <input type="text" name="proveedor" placeholder="Ej: Corporación Tecnológica SAC" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">N° Boleta / Factura</label>
                    <input type="text" name="nro_comprobante" placeholder="Ej: F001-0004523" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Adjuntar Foto / PDF del Comprobante</label>
                <input type="file" name="comprobante_file" accept=".jpg,.jpeg,.png,.pdf" class="w-full text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Acuerdo de Asamblea / Observaciones</label>
                <textarea name="descripcion" rows="2" placeholder="Ej: Gasto aprobado en Asamblea Ordinaria de abril, acta N° 03." class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-nuevo-gasto').classList.add('hidden')" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl font-semibold">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-md shadow-emerald-600/30">
                    Guardar Egreso
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
