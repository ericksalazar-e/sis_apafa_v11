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

        <form id="form-pago-pos" action="{{ route('pagos.store') }}" method="POST" onsubmit="return prevenirDobleEnvioPago(this);" class="space-y-4 text-xs">
            @csrf

            <!-- Búsqueda y Escáner de Asociados (DNI / Código de Barras / QR) -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block font-bold text-slate-700">
                        <span class="inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-barcode text-indigo-600"></i>
                            Buscar / Escanear Asociado *
                        </span>
                    </label>
                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Lector Listo
                    </span>
                </div>

                <!-- Input con Soporte de Pistola de Código de Barras / QR / DNI -->
                <div class="relative">
                    <div class="flex gap-1.5">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400" id="scan-icon-status">
                                <i class="fa-solid fa-qrcode text-sm"></i>
                            </div>
                            <input type="text" id="pos_scanner_input" 
                                   placeholder="Escanear carné o ingresar DNI / Nombre..."
                                   autocomplete="off"
                                   class="w-full pl-9 pr-8 py-2.5 bg-slate-50 hover:bg-white focus:bg-white border-2 border-indigo-200 focus:border-indigo-600 rounded-xl text-xs font-semibold text-slate-800 transition-all shadow-inner focus:ring-2 focus:ring-indigo-100">
                            
                            <button type="button" id="btn-clear-scan" onclick="limpiarSeleccionAsociado()" 
                                    class="hidden absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors" title="Limpiar búsqueda">
                                <i class="fa-solid fa-circle-xmark text-sm"></i>
                            </button>
                        </div>

                        <!-- Botón Buscar -->
                        <button type="button" onclick="ejecutarBusquedaDesdeBoton()" 
                                id="btn-buscar-pos"
                                class="px-3.5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-sm transition-all flex items-center gap-1.5 text-xs active:scale-95" title="Buscar por DNI o Código">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <span class="hidden sm:inline">Buscar</span>
                        </button>

                        <!-- Botón Escaneo por Cámara Web / Móvil -->
                        <button type="button" onclick="abrirCamaraPos()" 
                                class="px-3 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-sm transition-all flex items-center gap-1.5 text-xs active:scale-95" title="Escanear con Cámara Web / Smartphone">
                            <i class="fa-solid fa-camera"></i>
                            <span class="hidden sm:inline">Cámara</span>
                        </button>
                    </div>

                    <!-- Mensaje de ayuda y toggle lista manual -->
                    <div class="flex items-center justify-between mt-1 text-[10px] text-slate-400 px-0.5">
                        <span class="truncate">
                            <i class="fa-solid fa-keyboard mr-1 text-slate-400"></i> Pistola USB / Enter automático / DNI 8 dígitos
                        </span>
                        <button type="button" onclick="toggleSelectorManual()" class="text-indigo-600 hover:text-indigo-800 font-semibold underline shrink-0 ml-2">
                            <span id="txt-toggle-manual">Ver lista desplegable</span>
                        </button>
                    </div>
                </div>

                <!-- Campo oculto requerido para enviar el padre_familia_id al form -->
                <input type="hidden" name="padre_familia_id" id="padre_familia_id" required>

                <!-- Selector Manual Alternativo (Colapsable) -->
                <div id="wrapper-selector-manual" class="hidden p-2.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                    <label class="block text-[11px] font-semibold text-slate-600">Seleccionar manualmente de la lista:</label>
                    <select id="select_padre_manual" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs bg-white text-slate-800 font-medium" onchange="seleccionarPadreManual(this.value)">
                        <option value="">-- Seleccionar padre de familia --</option>
                        @foreach($padres as $p)
                            <option value="{{ $p->id }}" data-dni="{{ $p->padre_dni }}">
                                {{ $p->padre_dni }} &bull; {{ $p->nombre_completo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Lista de Coincidencias Múltiples (Si la búsqueda por nombre devolvió varios) -->
                <div id="contenedor-multiples-coincidencias" class="hidden p-3 bg-indigo-50/70 border border-indigo-200 rounded-xl space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-indigo-900 flex items-center gap-1.5">
                            <i class="fa-solid fa-users text-indigo-600"></i> Se encontraron varios asociados:
                        </span>
                        <span class="text-[10px] text-indigo-600 font-semibold">Haga clic para seleccionar</span>
                    </div>
                    <div id="lista-coincidencias-multiples" class="space-y-1.5 max-h-48 overflow-y-auto pr-1">
                        <!-- Generado dinámicamente con JS -->
                    </div>
                </div>

                <!-- Tarjeta Ejecutiva del Asociado Identificado -->
                <div id="tarjeta-asociado-seleccionado" class="hidden p-3.5 bg-gradient-to-br from-indigo-50/90 via-white to-emerald-50/50 border-2 border-indigo-300 rounded-2xl shadow-sm transition-all duration-200">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white font-bold flex items-center justify-center shadow-md shadow-indigo-600/20 shrink-0">
                                <i class="fa-solid fa-user-check text-lg"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 id="asociado-nombre" class="font-extrabold text-slate-900 text-xs truncate leading-tight">
                                    -
                                </h4>
                                <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-white text-slate-700 border border-slate-200">
                                        <i class="fa-solid fa-id-card text-indigo-500"></i> DNI: <span id="asociado-dni">-</span>
                                    </span>
                                    <span id="asociado-correlativo-badge" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-indigo-100 text-indigo-800">
                                        Carné: <span id="asociado-correlativo">-</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Botón para Cambiar / Limpiar Asociado -->
                        <button type="button" onclick="limpiarSeleccionAsociado()" 
                                class="text-[11px] text-slate-400 hover:text-rose-600 font-semibold px-2 py-1 rounded-lg hover:bg-rose-50 border border-transparent hover:border-rose-200 transition-colors shrink-0" 
                                title="Cambiar de Asociado">
                            <i class="fa-solid fa-arrows-rotate mr-1"></i> Cambiar
                        </button>
                    </div>

                    <!-- Motivo de Match (Identificado por hijo o carné) -->
                    <div id="box-motivo-match" class="mt-2 px-2 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-semibold flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                        <span id="asociado-motivo" class="truncate">-</span>
                    </div>

                    <!-- Lista de Hijos / Estudiantes Asociados -->
                    <div id="box-estudiantes-asociados" class="mt-2.5 pt-2 border-t border-slate-200/70">
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-children text-indigo-500 mr-1"></i> Hijos Matriculados:
                        </span>
                        <div id="lista-estudiantes-chips" class="flex flex-wrap gap-1.5">
                            <!-- Chips de hijos -->
                        </div>
                    </div>

                    <!-- Cuotas y Aportes Pendientes (Auto-completar en 1 Clic) -->
                    <div id="box-deudas-asociado" class="mt-2.5 pt-2 border-t border-slate-200/70 hidden">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[10px] font-bold text-amber-900 flex items-center gap-1">
                                <i class="fa-solid fa-receipt text-amber-600"></i> Cuotas Pendientes (Clic para cobrar):
                            </span>
                            <span class="text-[9px] text-amber-700 font-medium">Auto-completa concepto y monto</span>
                        </div>
                        <div id="lista-deudas-chips" class="space-y-1.5">
                            <!-- Chips de deudas -->
                        </div>
                    </div>

                    <div id="box-al-dia-asociado" class="mt-2.5 pt-2 border-t border-slate-200/70 hidden text-center">
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded-full">
                            <i class="fa-solid fa-award"></i> Asociado al día en sus aportes
                        </span>
                    </div>
                </div>
            </div>

            <!-- Concepto -->
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Concepto de Pago *</label>
                <input type="text" name="pago_concepto" id="input_pago_concepto" list="conceptos_sugeridos" required 
                       placeholder="Ej: CUOTA ORDINARIA APAFA 2026"
                       class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all font-medium text-slate-800">
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
                    <input type="number" step="0.50" min="0.50" name="monto_pago" id="input_monto_pago" required 
                           placeholder="50.00"
                           class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono font-bold text-slate-800 transition-all">
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

            <!-- Regularización de Pagos Previos (Talonario Manual) -->
            <div class="p-3 bg-amber-50/70 rounded-xl border border-amber-200/80 space-y-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="es_regularizacion" id="chkRegularizacion" value="1" 
                           onchange="toggleRegularizacion(this.checked)"
                           class="w-4 h-4 rounded text-amber-600 focus:ring-amber-500 border-amber-300">
                    <span class="font-bold text-amber-900 text-xs">Regularizar pago previo / Talonario físico</span>
                </label>
                <p class="text-[10px] text-amber-700 leading-tight">
                    Active esta casilla si el padre ya canceló antes de usar el sistema (recibo manual, boleta física o libreta) para actualizar su saldo y padrón.
                </p>
                <div id="boxReferenciaManual" class="hidden pt-1">
                    <label class="block font-semibold text-amber-900 text-[11px] mb-1">N° de Recibo Físico / Talonario de Referencia *</label>
                    <input type="text" name="recibo_manual_referencia" id="input_recibo_manual"
                           placeholder="Ej. Talonario 001 - Recibo N° 045"
                           class="w-full px-2.5 py-1.5 border border-amber-300 rounded-lg text-xs bg-white text-slate-800 font-mono">
                </div>
            </div>

            <!-- Notice async -->
            <div class="p-3 rounded-xl bg-indigo-50/60 border border-indigo-100 flex items-start gap-2 text-indigo-900 text-[11px] leading-relaxed">
                <i class="fa-solid fa-bolt text-indigo-600 mt-0.5"></i>
                <p>El ticket se genera al instante. El comprobante fiscal se firma y remite a SUNAT en segundo plano sin ralentizar el cobro.</p>
            </div>

            <button type="submit" id="btn-submit-pago" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-md shadow-emerald-600/30 transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-circle-check"></i>
                <span>Registrar y Emitir CPE</span>
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
                                @if($pago->es_regularizacion)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-300 mt-0.5">
                                        <i class="fa-solid fa-receipt"></i> Físico: {{ $pago->recibo_manual_referencia ?? 'Manual' }}
                                    </span>
                                @endif
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

<!-- Modal Escáner de Carné con Cámara Web / Smartphone en POS -->
<div id="modal-camara-pos" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-100 animate-in fade-in zoom-in-95 duration-200">
        <div class="p-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                    <i class="fa-solid fa-camera text-sm"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sm leading-tight">Escáner de Carné en Ventanilla</h4>
                    <p class="text-[10px] text-slate-400">Código de Barras y QR de Asociado o Alumno</p>
                </div>
            </div>
            <button type="button" onclick="cerrarCamaraPos()" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-4 bg-slate-950 flex flex-col items-center relative">
            <div id="reader-pos" class="w-full rounded-2xl overflow-hidden bg-black aspect-square max-h-[320px] shadow-inner"></div>
            
            <div class="mt-3 flex items-center gap-2 text-xs text-slate-300 bg-slate-900/90 px-3 py-1.5 rounded-full border border-slate-800">
                <i class="fa-solid fa-qrcode text-emerald-400"></i>
                <span>Acerque el carné frente a la cámara</span>
            </div>
        </div>

        <div class="p-3.5 bg-slate-50 border-t border-slate-200 flex justify-between items-center">
            <button type="button" id="btn-switch-cam-pos" onclick="cambiarCamaraPos()" 
                    class="text-xs text-slate-700 bg-white border border-slate-300 hover:bg-slate-100 font-bold px-3 py-2 rounded-xl flex items-center gap-1.5 shadow-sm transition-all">
                <i class="fa-solid fa-arrows-rotate"></i> Cambiar Cámara
            </button>
            <button type="button" onclick="cerrarCamaraPos()" 
                    class="text-xs text-slate-600 hover:text-slate-900 font-bold px-4 py-2 rounded-xl hover:bg-slate-200/60 transition-colors">
                Cancelar
            </button>
        </div>
    </div>
</div>

<script>
    // ================= SCANNER AUDIO BEEP FEEDBACK (WEB AUDIO API) =================
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
                gain.gain.setValueAtTime(0.18, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.22);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.22);
            } else {
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(320, ctx.currentTime);
                osc.frequency.setValueAtTime(220, ctx.currentTime + 0.12);
                gain.gain.setValueAtTime(0.20, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.35);
            }
        } catch (e) {
            console.warn('Audio feedback error:', e);
        }
    }

    // ================= BUSCADOR Y LECTOR EN POS =================
    const scannerInput = document.getElementById('pos_scanner_input');
    const hiddenPadreId = document.getElementById('padre_familia_id');
    const tarjetaAsociado = document.getElementById('tarjeta-asociado-seleccionado');
    const multipleBox = document.getElementById('contenedor-multiples-coincidencias');
    const multipleList = document.getElementById('lista-coincidencias-multiples');
    const btnClearScan = document.getElementById('btn-clear-scan');
    let searchDebounceTimer = null;
    let isSearchingPos = false;

    // Escuchar tipeo o pistola USB (detecta Enter o longitud 8 de DNI)
    if (scannerInput) {
        scannerInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                ejecutarBusquedaDesdeBoton();
            }
        });

        scannerInput.addEventListener('input', function() {
            const val = this.value.trim();
            if (val.length > 0) {
                btnClearScan.classList.remove('hidden');
            } else {
                btnClearScan.classList.add('hidden');
            }

            // Detección automática al completar DNI de 8 dígitos
            if (/^\d{8}$/.test(val)) {
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(() => {
                    buscarAsociadoEnPos(val);
                }, 180);
            }
        });
    }

    function ejecutarBusquedaDesdeBoton() {
        if (!scannerInput) return;
        const val = scannerInput.value.trim();
        if (!val) {
            scannerInput.focus();
            return;
        }
        buscarAsociadoEnPos(val);
    }

    async function buscarAsociadoEnPos(query) {
        if (isSearchingPos) return;
        isSearchingPos = true;

        const btnBuscar = document.getElementById('btn-buscar-pos');
        const iconStatus = document.getElementById('scan-icon-status');
        if (btnBuscar) {
            btnBuscar.disabled = true;
            btnBuscar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        }
        if (iconStatus) {
            iconStatus.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-indigo-600"></i>';
        }

        try {
            const response = await fetch(`{{ route('pagos.buscar_asociado') }}?query=${encodeURIComponent(query)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                playAudioBeep('error');
                mostrarAlertaPos(data.message || 'No se encontró ningún asociado registrado.');
                return;
            }

            // Caso múltiples coincidencias (por nombre parcial)
            if (data.multiple && data.resultados && data.resultados.length > 0) {
                playAudioBeep('success');
                mostrarMultiplesCoincidencias(data.resultados);
                return;
            }

            // Caso asociado único encontrado
            if (data.padre) {
                playAudioBeep('success');
                aplicarAsociadoSeleccionado(data);
            }

        } catch (error) {
            console.error('Error buscando asociado:', error);
            playAudioBeep('error');
            mostrarAlertaPos('Ocurrió un error al consultar el servidor.');
        } finally {
            isSearchingPos = false;
            if (btnBuscar) {
                btnBuscar.disabled = false;
                btnBuscar.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> <span class="hidden sm:inline">Buscar</span>';
            }
            if (iconStatus) {
                iconStatus.innerHTML = '<i class="fa-solid fa-qrcode text-sm"></i>';
            }
        }
    }

    function mostrarMultiplesCoincidencias(resultados) {
        tarjetaAsociado.classList.add('hidden');
        multipleList.innerHTML = '';
        resultados.forEach(item => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'w-full text-left p-2 rounded-xl bg-white hover:bg-indigo-50 border border-indigo-100 hover:border-indigo-300 transition-colors flex items-center justify-between text-xs';
            btn.innerHTML = `
                <div>
                    <span class="font-bold text-slate-800">${item.nombre_completo}</span>
                    <span class="block text-[10px] text-slate-400">DNI: ${item.dni} ${item.estudiantes_resumen ? '&bull; Hijos: ' + item.estudiantes_resumen : ''}</span>
                </div>
                <span class="px-2 py-1 bg-indigo-600 text-white rounded-lg text-[10px] font-bold shrink-0">Seleccionar</span>
            `;
            btn.onclick = () => {
                multipleBox.classList.add('hidden');
                buscarAsociadoEnPos(item.dni);
            };
            multipleList.appendChild(btn);
        });
        multipleBox.classList.remove('hidden');
    }

    function aplicarAsociadoSeleccionado(data) {
        const padre = data.padre;
        multipleBox.classList.add('hidden');

        // Asignar ID al input hidden
        hiddenPadreId.value = padre.id;

        // Sincronizar selector manual si existe
        const manualSelect = document.getElementById('select_padre_manual');
        if (manualSelect) {
            manualSelect.value = padre.id;
        }

        // Llenar datos de la tarjeta
        document.getElementById('asociado-nombre').textContent = padre.nombre_completo;
        document.getElementById('asociado-dni').textContent = padre.dni;
        document.getElementById('asociado-correlativo').textContent = padre.correlativo || 'S/C';

        // Motivo del match
        const boxMotivo = document.getElementById('box-motivo-match');
        if (data.motivo_match) {
            document.getElementById('asociado-motivo').textContent = data.motivo_match;
            boxMotivo.classList.remove('hidden');
        } else {
            boxMotivo.classList.add('hidden');
        }

        // Lista de hijos matriculados
        const listaEstudiantes = document.getElementById('lista-estudiantes-chips');
        listaEstudiantes.innerHTML = '';
        if (padre.estudiantes && padre.estudiantes.length > 0) {
            padre.estudiantes.forEach(est => {
                const badge = document.createElement('span');
                badge.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-900 text-[10px] font-bold';
                badge.innerHTML = `<i class="fa-solid fa-graduation-cap text-indigo-500"></i> ${est.nombre_completo} <span class="text-indigo-400 font-normal">(${est.grado} "${est.seccion}")</span>`;
                listaEstudiantes.appendChild(badge);
            });
            document.getElementById('box-estudiantes-asociados').classList.remove('hidden');
        } else {
            document.getElementById('box-estudiantes-asociados').classList.add('hidden');
        }

        // Deudas / Cuotas pendientes
        const boxDeudas = document.getElementById('box-deudas-asociado');
        const boxAlDia = document.getElementById('box-al-dia-asociado');
        const listaDeudas = document.getElementById('lista-deudas-chips');
        listaDeudas.innerHTML = '';

        if (data.deudas && data.deudas.length > 0) {
            data.deudas.forEach(d => {
                const btnDeuda = document.createElement('button');
                btnDeuda.type = 'button';
                btnDeuda.className = 'w-full text-left p-2 rounded-xl bg-amber-50 hover:bg-amber-100/80 border border-amber-200 hover:border-amber-400 text-amber-950 text-xs font-semibold flex items-center justify-between transition-all group active:scale-[0.99]';
                btnDeuda.innerHTML = `
                    <div class="min-w-0 pr-2">
                        <span class="block font-bold text-slate-800 text-[11px] truncate group-hover:text-amber-900">
                            <i class="fa-solid fa-bolt text-amber-500 mr-1"></i> ${d.titulo}
                        </span>
                        <span class="block text-[10px] text-amber-700 font-normal">
                            Aporte total: S/ ${d.monto_aporte.toFixed(2)} &bull; Pagado: S/ ${d.monto_aportado.toFixed(2)}
                        </span>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="font-mono font-extrabold text-rose-600 text-xs block">
                            Debe S/ ${d.debe.toFixed(2)}
                        </span>
                        <span class="text-[9px] bg-amber-600 text-white font-bold px-1.5 py-0.5 rounded shadow-sm inline-block mt-0.5">
                            Cobrar
                        </span>
                    </div>
                `;
                btnDeuda.onclick = () => autoCompletarPagoPorDeuda(d.titulo, d.debe);
                listaDeudas.appendChild(btnDeuda);
            });
            boxDeudas.classList.remove('hidden');
            boxAlDia.classList.add('hidden');
        } else {
            boxDeudas.classList.add('hidden');
            boxAlDia.classList.remove('hidden');
        }

        // Mostrar la tarjeta y actualizar input de búsqueda con el DNI
        tarjetaAsociado.classList.remove('hidden');
        if (scannerInput) {
            scannerInput.value = padre.dni + ' - ' + padre.nombre_completo;
            btnClearScan.classList.remove('hidden');
        }
    }

    function autoCompletarPagoPorDeuda(titulo, monto) {
        const inputConcepto = document.getElementById('input_pago_concepto');
        const inputMonto = document.getElementById('input_monto_pago');

        if (inputConcepto) {
            inputConcepto.value = 'APORTE: ' + titulo;
            animarResalte(inputConcepto);
        }
        if (inputMonto) {
            inputMonto.value = parseFloat(monto).toFixed(2);
            animarResalte(inputMonto);
        }
        playAudioBeep('success');
    }

    function animarResalte(elemento) {
        elemento.classList.add('ring-4', 'ring-emerald-400', 'bg-emerald-50/50');
        setTimeout(() => {
            elemento.classList.remove('ring-4', 'ring-emerald-400', 'bg-emerald-50/50');
        }, 800);
    }

    function limpiarSeleccionAsociado() {
        hiddenPadreId.value = '';
        if (scannerInput) {
            scannerInput.value = '';
            scannerInput.focus();
        }
        btnClearScan.classList.add('hidden');
        tarjetaAsociado.classList.add('hidden');
        multipleBox.classList.add('hidden');
        const manualSelect = document.getElementById('select_padre_manual');
        if (manualSelect) {
            manualSelect.value = '';
        }
    }

    function toggleSelectorManual() {
        const wrapper = document.getElementById('wrapper-selector-manual');
        const txt = document.getElementById('txt-toggle-manual');
        if (wrapper.classList.contains('hidden')) {
            wrapper.classList.remove('hidden');
            txt.textContent = 'Ocultar lista';
        } else {
            wrapper.classList.add('hidden');
            txt.textContent = 'Ver lista desplegable';
        }
    }

    function seleccionarPadreManual(id) {
        if (!id) {
            limpiarSeleccionAsociado();
            return;
        }
        const select = document.getElementById('select_padre_manual');
        const selectedOption = select.options[select.selectedIndex];
        const dni = selectedOption ? selectedOption.getAttribute('data-dni') : null;
        if (dni) {
            buscarAsociadoEnPos(dni);
        } else {
            hiddenPadreId.value = id;
        }
    }

    function mostrarAlertaPos(mensaje) {
        // Mensaje sutil y no intrusivo
        alert(mensaje);
        if (scannerInput) {
            scannerInput.select();
            scannerInput.focus();
        }
    }

    // ================= INTEGRACIÓN DE CÁMARA (HTML5-QRCODE) =================
    let html5QrPos = null;
    let camaraPosActiva = false;
    let camaraFacingPos = "environment";

    function abrirCamaraPos() {
        const modal = document.getElementById('modal-camara-pos');
        modal.classList.remove('hidden');

        if (typeof Html5Qrcode === 'undefined') {
            alert('La librería del escáner de cámara se está cargando. Verifique su conexión.');
            return;
        }

        if (!html5QrPos) {
            html5QrPos = new Html5Qrcode("reader-pos");
        }

        const config = {
            fps: 15,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0,
            formatsToSupport: [
                Html5QrcodeSupportedFormats.QR_CODE,
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8,
                Html5QrcodeSupportedFormats.UPC_A
            ]
        };

        html5QrPos.start(
            { facingMode: camaraFacingPos },
            config,
            (decodedText) => {
                // Escaneo exitoso
                playAudioBeep('success');
                cerrarCamaraPos();
                if (scannerInput) {
                    scannerInput.value = decodedText;
                }
                buscarAsociadoEnPos(decodedText);
            },
            (errorMsg) => {
                // Ignorar frames vacíos
            }
        ).then(() => {
            camaraPosActiva = true;
        }).catch(err => {
            console.error('Error al inicializar cámara:', err);
            alert('No se pudo acceder a la cámara. Verifique los permisos en su navegador.');
            cerrarCamaraPos();
        });
    }

    function cerrarCamaraPos() {
        const modal = document.getElementById('modal-camara-pos');
        modal.classList.add('hidden');
        if (html5QrPos && camaraPosActiva) {
            html5QrPos.stop().then(() => {
                camaraPosActiva = false;
            }).catch(err => console.error(err));
        }
    }

    function cambiarCamaraPos() {
        camaraFacingPos = (camaraFacingPos === "environment") ? "user" : "environment";
        if (html5QrPos && camaraPosActiva) {
            html5QrPos.stop().then(() => {
                camaraPosActiva = false;
                abrirCamaraPos();
            });
        }
    }

    // ================= ENVÍO Y CONTROL DEL FORMULARIO =================
    let isSubmittingPago = false;
    function prevenirDobleEnvioPago(form) {
        if (!hiddenPadreId.value) {
            playAudioBeep('error');
            alert('Debe buscar y seleccionar un Padre de Familia o Asociado antes de registrar el cobro.');
            if (scannerInput) scannerInput.focus();
            return false;
        }

        if (isSubmittingPago) {
            return false;
        }
        isSubmittingPago = true;
        const btn = document.getElementById('btn-submit-pago');
        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i> Emitiendo CPE...';
        }
        return true;
    }

    function toggleFraccionamiento(val) {
        const box = document.getElementById('boxFraccionamiento');
        if (val === 'FRACCIONADO') {
            box.classList.remove('hidden');
        } else {
            box.classList.add('hidden');
        }
    }

    function toggleRegularizacion(checked) {
        const box = document.getElementById('boxReferenciaManual');
        const input = document.getElementById('input_recibo_manual');
        if (box) {
            if (checked) {
                box.classList.remove('hidden');
                if (input) input.required = true;
            } else {
                box.classList.add('hidden');
                if (input) input.required = false;
            }
        }
    }

    // Al cargar la página, enfocar automáticamente en la barra de escaneo
    document.addEventListener('DOMContentLoaded', () => {
        if (scannerInput) {
            setTimeout(() => scannerInput.focus(), 200);
        }
    });
</script>
@endsection
