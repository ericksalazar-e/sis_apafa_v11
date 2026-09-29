@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header & Status Bar -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-slate-800">
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none text-9xl">
            <i class="fa-solid fa-microchip"></i>
        </div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        Microservicio UBL 2.1 Activo
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $configuracion->esProduccion() ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-sky-500/20 text-sky-300 border border-sky-500/30' }}">
                        <i class="fa-solid {{ $configuracion->esProduccion() ? 'fa-shield-halved' : 'fa-flask' }}"></i>
                        Ambiente: {{ $configuracion->ambiente }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono bg-slate-800/80 text-slate-300 border border-slate-700">
                        <i class="fa-solid fa-server text-indigo-400"></i>
                        Cola Asíncrona: {{ config('queue.default') }} ({{ $jobsEnCola }} en espera)
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-3">
                    <i class="fa-solid fa-file-invoice-dollar text-indigo-400"></i>
                    Facturación CPE & Microservicio UBL 2.1
                </h1>
                <p class="text-slate-300 text-sm mt-1 max-w-2xl">
                    Emisión electrónica de Boletas, Facturas y Notas de Venta con cálculo de Hash SHA-256 DigestValue, código QR SUNAT y despacho en segundo plano.
                </p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex flex-wrap items-center gap-3">
                <form action="{{ route('facturacion.procesar_cola') }}" method="POST" onsubmit="return confirm('¿Desea procesar todos los comprobantes pendientes en cola ahora?')">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-2xl text-xs sm:text-sm shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-bolt"></i>
                        Procesar Cola Ahora
                    </button>
                </form>

                <button type="button" onclick="cambiarPestana('diagnostico')" class="px-4 py-2.5 bg-slate-800/90 hover:bg-slate-700 text-slate-200 font-semibold rounded-2xl text-xs sm:text-sm border border-slate-700 flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-vial-virus text-indigo-400"></i>
                    Simulador UBL 2.1
                </button>
            </div>
        </div>
    </div>

    <!-- KPI Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Emitidos Aceptados -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Comprobantes Emitidos</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-800">{{ number_format($totalEmitidos) }}</span>
                <span class="text-xs text-emerald-600 font-bold">CDR Aceptado</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Con Hash SHA-256 generado</p>
        </div>

        <!-- 2. En Cola Asíncrona -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">En Cola / Pendientes</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-800">{{ number_format($totalEnCola) }}</span>
                <span class="text-xs text-amber-600 font-bold">Despacho Asíncrono</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">En cola de base de datos</p>
        </div>

        <!-- 3. Rechazados o con Error -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Rechazados / Error</span>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black {{ $totalError > 0 ? 'text-rose-600' : 'text-slate-800' }}">{{ number_format($totalError) }}</span>
                <span class="text-xs text-slate-400 font-medium">Requieren reintento</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Reintentable en 1 clic</p>
        </div>

        <!-- 4. Total Recaudado -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Facturado</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-coins"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-1">
                <span class="text-sm font-bold text-indigo-600">S/.</span>
                <span class="text-3xl font-black text-slate-800">{{ number_format($totalRecaudado, 2) }}</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Monto en comprobantes válidos</p>
        </div>
    </div>

    <!-- Alert / Diagnostic Result Banner if exists -->
    @if(session('test_result'))
    @php $res = session('test_result'); @endphp
    <div class="bg-slate-900 border border-indigo-500/40 rounded-3xl p-6 text-white shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-400 animate-ping"></span>
                <h3 class="font-bold text-base text-emerald-400">Resultado de Simulación UBL 2.1 en Vivo</h3>
            </div>
            <span class="text-xs text-slate-400 font-mono">{{ $res['timestamp'] ?? date('Y-m-d H:i:s') }}</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 text-xs font-mono">
            <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800">
                <span class="text-slate-400 block font-sans font-semibold mb-1">Comprobante Emitido:</span>
                <span class="text-indigo-300 font-bold text-sm">{{ $res['comprobante_completo'] ?? '--' }}</span>
                <span class="text-slate-400 block mt-1">Ambiente: <b class="text-white">{{ $res['ambiente'] ?? 'BETA' }}</b></span>
            </div>
            <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800">
                <span class="text-slate-400 block font-sans font-semibold mb-1">Hash SHA-256 (DigestValue):</span>
                <span class="text-emerald-400 font-bold break-all">{{ $res['hash_cpe'] ?? '--' }}</span>
            </div>
            <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800">
                <span class="text-slate-400 block font-sans font-semibold mb-1">Respuesta CDR SUNAT:</span>
                <span class="text-amber-300 font-bold">{{ $res['mensaje_sunat'] ?? '--' }}</span>
            </div>
        </div>
        <div class="mt-3 bg-slate-950/80 p-3 rounded-xl border border-slate-800 text-[11px] font-mono text-slate-400 overflow-x-auto">
            <span class="text-slate-300 font-sans font-semibold block mb-1">Cadena Código QR Oficial SUNAT:</span>
            <code>{{ $res['qr_string'] ?? '--' }}</code>
        </div>
    </div>
    @endif

    <!-- Main Tabs Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <!-- Tab Headers -->
        <div class="flex border-b border-slate-200 overflow-x-auto bg-slate-50/50 p-2 gap-2">
            <button type="button" onclick="cambiarPestana('parametros')" id="tab-btn-parametros"
                    class="tab-btn active px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold flex items-center gap-2 transition-all">
                <i class="fa-solid fa-sliders text-indigo-500"></i>
                Parámetros UBL 2.1 & SUNAT
            </button>

            <button type="button" onclick="cambiarPestana('monitor')" id="tab-btn-monitor"
                    class="tab-btn px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold flex items-center gap-2 transition-all">
                <i class="fa-solid fa-list-check text-slate-400"></i>
                Monitor de Comprobantes & Envíos
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-200 text-slate-700 font-mono">{{ $comprobantes->total() }}</span>
            </button>

            <button type="button" onclick="cambiarPestana('diagnostico')" id="tab-btn-diagnostico"
                    class="tab-btn px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold flex items-center gap-2 transition-all">
                <i class="fa-solid fa-flask-vial text-slate-400"></i>
                Consola & Simulador en Vivo
            </button>

            <button type="button" onclick="cambiarPestana('arquitectura')" id="tab-btn-arquitectura"
                    class="tab-btn px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold flex items-center gap-2 transition-all">
                <i class="fa-solid fa-book-bookmark text-slate-400"></i>
                Guía Técnica & Normativa
            </button>
        </div>

        <!-- ================= TAB 1: PARÁMETROS UBL 2.1 & SUNAT ================= -->
        <div id="tab-content-parametros" class="p-6 sm:p-8 space-y-6">
            <form action="{{ route('facturacion.config.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Entorno de Trabajo -->
                <div class="p-5 rounded-2xl bg-indigo-50/50 border border-indigo-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-network-wired text-indigo-600"></i> Ambiente de Emisión SUNAT
                        </h4>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Seleccione 'BETA' para pruebas internas y homologación sin valor tributario real, o 'PRODUCCIÓN' para comprobantes oficiales ante SUNAT.
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-sm">
                            <input type="radio" name="ambiente" value="BETA" {{ $configuracion->ambiente === 'BETA' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-xs font-bold text-slate-700">🧪 BETA / PRUEBAS</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-sm">
                            <input type="radio" name="ambiente" value="PRODUCCION" {{ $configuracion->ambiente === 'PRODUCCION' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-xs font-bold text-amber-700">🛡️ PRODUCCIÓN OFICIAL</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                    <!-- Columna 1: Datos Fiscales del Emisor -->
                    <div class="space-y-4">
                        <h3 class="font-bold text-slate-900 text-base pb-2 border-b border-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-building-columns text-indigo-600"></i>
                            1. Datos Fiscales del Emisor (APAFA / Colegio)
                        </h3>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">RUC del Emisor (11 Dígitos) *</label>
                            <input type="text" name="ruc_emisor" value="{{ old('ruc_emisor', $configuracion->ruc_emisor) }}" required maxlength="11" pattern="[0-9]{11}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 text-sm font-mono font-bold text-slate-800"
                                   placeholder="Ej: 20601234567">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Razón Social Oficial *</label>
                            <input type="text" name="razon_social" value="{{ old('razon_social', $configuracion->razon_social) }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 text-sm"
                                   placeholder="ASOCIACION DE PADRES DE FAMILIA - I.E. ...">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nombre Comercial (Opcional)</label>
                            <input type="text" name="nombre_comercial" value="{{ old('nombre_comercial', $configuracion->nombre_comercial) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 text-sm"
                                   placeholder="APAFA I.E. EJEMPLO">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Dirección Fiscal Completa *</label>
                            <input type="text" name="direccion_fiscal" value="{{ old('direccion_fiscal', $configuracion->direccion_fiscal) }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 text-sm"
                                   placeholder="Av. Principal Nro 123">
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Ubigeo</label>
                                <input type="text" name="ubigeo" value="{{ old('ubigeo', $configuracion->ubigeo ?? '150101') }}" class="w-full px-2.5 py-2 text-xs rounded-xl border border-slate-200 font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Dpto.</label>
                                <input type="text" name="departamento" value="{{ old('departamento', $configuracion->departamento ?? 'LIMA') }}" class="w-full px-2.5 py-2 text-xs rounded-xl border border-slate-200">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Provincia</label>
                                <input type="text" name="provincia" value="{{ old('provincia', $configuracion->provincia ?? 'LIMA') }}" class="w-full px-2.5 py-2 text-xs rounded-xl border border-slate-200">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Distrito</label>
                                <input type="text" name="distrito" value="{{ old('distrito', $configuracion->distrito ?? 'LIMA') }}" class="w-full px-2.5 py-2 text-xs rounded-xl border border-slate-200">
                            </div>
                        </div>
                    </div>

                    <!-- Columna 2: Credenciales SOL & Certificado Digital -->
                    <div class="space-y-4">
                        <h3 class="font-bold text-slate-900 text-base pb-2 border-b border-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-key text-indigo-600"></i>
                            2. Credenciales SOL & Certificado Digital (.p12 / .pem)
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Usuario SOL SUNAT *</label>
                                <input type="text" name="usuario_sol" value="{{ old('usuario_sol', $configuracion->usuario_sol) }}" required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 text-sm font-mono"
                                       placeholder="MODDATOS">
                                <span class="text-[10px] text-slate-400 mt-1 block">En Beta usar: <code>MODDATOS</code></span>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Clave SOL SUNAT *</label>
                                <div class="relative">
                                    <input type="password" id="input_clave_sol" name="clave_sol" value="{{ old('clave_sol', $configuracion->clave_sol) }}" required
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 text-sm font-mono pr-9"
                                           placeholder="moddatos">
                                    <button type="button" onclick="togglePasswordVisibility('input_clave_sol', this)" class="absolute right-3 top-3 text-slate-400 hover:text-slate-600">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>
                                </div>
                                <span class="text-[10px] text-slate-400 mt-1 block">En Beta usar: <code>moddatos</code></span>
                            </div>
                        </div>

                        <!-- Certificado Digital -->
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                            <label class="block text-xs font-bold text-slate-800 flex items-center justify-between">
                                <span><i class="fa-solid fa-certificate text-indigo-500"></i> Certificado Digital (.p12 / .pfx / .pem)</span>
                                @if($configuracion->certificado_path)
                                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                    <i class="fa-solid fa-check"></i> Archivo cargado
                                </span>
                                @else
                                <span class="text-[10px] text-slate-400">Modo demo activo (firma simulada)</span>
                                @endif
                            </label>
                            
                            <input type="file" name="certificado_file" accept=".p12,.pfx,.pem,.cer" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">

                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Contraseña del Certificado Digital</label>
                                <input type="password" name="certificado_password" value="{{ old('certificado_password', $configuracion->certificado_password) }}"
                                       class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-mono" placeholder="••••••••">
                            </div>
                        </div>

                        <!-- Series y Correlativos UBL 2.1 -->
                        <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider mt-4">Series & Correlativos de Comprobantes</h4>
                        <div class="grid grid-cols-3 gap-3">
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                                <label class="block text-[11px] font-bold text-indigo-700 mb-1">Boleta Electrónica</label>
                                <input type="text" name="serie_boleta" value="{{ old('serie_boleta', $configuracion->serie_boleta) }}" required maxlength="4" class="w-full text-center py-1.5 px-2 rounded-lg border font-mono font-bold text-sm uppercase">
                                <div class="mt-1 flex items-center justify-center gap-1 text-[10px] text-slate-500">
                                    <span>Próx:</span>
                                    <input type="number" name="correlativo_boleta" value="{{ $configuracion->correlativo_boleta }}" class="w-16 py-0.5 px-1 border rounded text-center font-mono">
                                </div>
                            </div>

                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                                <label class="block text-[11px] font-bold text-emerald-700 mb-1">Factura Electrónica</label>
                                <input type="text" name="serie_factura" value="{{ old('serie_factura', $configuracion->serie_factura) }}" required maxlength="4" class="w-full text-center py-1.5 px-2 rounded-lg border font-mono font-bold text-sm uppercase">
                                <div class="mt-1 flex items-center justify-center gap-1 text-[10px] text-slate-500">
                                    <span>Próx:</span>
                                    <input type="number" name="correlativo_factura" value="{{ $configuracion->correlativo_factura }}" class="w-16 py-0.5 px-1 border rounded text-center font-mono">
                                </div>
                            </div>

                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Nota de Venta</label>
                                <input type="text" name="serie_nota_venta" value="{{ old('serie_nota_venta', $configuracion->serie_nota_venta) }}" required maxlength="4" class="w-full text-center py-1.5 px-2 rounded-lg border font-mono font-bold text-sm uppercase">
                                <div class="mt-1 flex items-center justify-center gap-1 text-[10px] text-slate-500">
                                    <span>Próx:</span>
                                    <input type="number" name="correlativo_nota_venta" value="{{ $configuracion->correlativo_nota_venta }}" class="w-16 py-0.5 px-1 border rounded text-center font-mono">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Opciones del Motor Asíncrono & Formato -->
                <div class="mt-6 pt-6 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Modo de Procesamiento</label>
                        <select name="modo_emision" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs bg-white font-medium">
                            <option value="ASINCRONO_COLA" {{ $configuracion->modo_emision === 'ASINCRONO_COLA' ? 'selected' : '' }}>
                                ⚡ Asíncrono en Cola (Recomendado - No bloquea POS)
                            </option>
                            <option value="SINCRONO_DIRECTO" {{ $configuracion->modo_emision === 'SINCRONO_DIRECTO' ? 'selected' : '' }}>
                                ⏳ Síncrono Directo (Emisión en tiempo real)
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Formato de Impresión por Defecto</label>
                        <select name="formato_impresion" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs bg-white font-medium">
                            <option value="TICKET_80MM" {{ $configuracion->formato_impresion === 'TICKET_80MM' ? 'selected' : '' }}>
                                🧾 Ticketera Térmica 80mm
                            </option>
                            <option value="A4" {{ $configuracion->formato_impresion === 'A4' ? 'selected' : '' }}>
                                📄 Hoja Bond Formato A4
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Leyenda al Pie del Comprobante</label>
                        <input type="text" name="leyenda_pie" value="{{ old('leyenda_pie', $configuracion->leyenda_pie) }}"
                               class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs"
                               placeholder="Representación impresa del CPE UBL 2.1">
                    </div>
                </div>

                <!-- Footer Botón Guardar -->
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-400">
                        <i class="fa-solid fa-lock text-emerald-500"></i> Los datos se guardan cifrados bajo arquitectura multi-inquilino.
                    </span>
                    <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl text-sm shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Parámetros de Facturación
                    </button>
                </div>
            </form>
        </div>

        <!-- ================= TAB 2: MONITOR DE COMPROBANTES & ENVÍOS ================= -->
        <div id="tab-content-monitor" class="p-6 sm:p-8 space-y-4 hidden">
            <!-- Barra de Filtros -->
            <form action="{{ route('facturacion.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="tab" value="monitor">
                
                <div class="flex-1 min-w-[240px]">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                        <input type="text" name="buscar" value="{{ request('buscar') }}"
                               placeholder="Buscar por DNI, RUC, nombre o número de comprobante..."
                               class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <select name="tipo" onchange="this.form.submit()" class="px-3 py-2 border border-slate-200 rounded-xl text-xs bg-white font-medium">
                        <option value="">Todos los tipos</option>
                        <option value="BOLETA" {{ request('tipo') === 'BOLETA' ? 'selected' : '' }}>Boletas Electrónicas</option>
                        <option value="FACTURA" {{ request('tipo') === 'FACTURA' ? 'selected' : '' }}>Facturas Electrónicas</option>
                        <option value="NOTA_VENTA" {{ request('tipo') === 'NOTA_VENTA' ? 'selected' : '' }}>Notas de Venta</option>
                    </select>
                </div>

                <div>
                    <select name="estado" onchange="this.form.submit()" class="px-3 py-2 border border-slate-200 rounded-xl text-xs bg-white font-medium">
                        <option value="">Todos los estados</option>
                        <option value="EMITIDO" {{ request('estado') === 'EMITIDO' ? 'selected' : '' }}>✅ Emitidos (Aceptado SUNAT)</option>
                        <option value="EN_COLA" {{ request('estado') === 'EN_COLA' ? 'selected' : '' }}>⏳ En Cola / Pendiente</option>
                        <option value="ERROR" {{ request('estado') === 'ERROR' ? 'selected' : '' }}>❌ Error / Rechazado</option>
                    </select>
                </div>

                @if(request('buscar') || request('tipo') || request('estado'))
                <a href="{{ route('facturacion.index', ['tab' => 'monitor']) }}" class="px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-xl transition-colors">
                    <i class="fa-solid fa-xmark"></i> Limpiar
                </a>
                @endif
            </form>

            <!-- Tabla de Comprobantes -->
            <div class="overflow-x-auto rounded-2xl border border-slate-200/80">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">Fecha / Hora</th>
                            <th class="py-3 px-4">Comprobante</th>
                            <th class="py-3 px-4">Tipo</th>
                            <th class="py-3 px-4">Cliente / Apoderado</th>
                            <th class="py-3 px-4">Concepto</th>
                            <th class="py-3 px-4 text-right">Total</th>
                            <th class="py-3 px-4 text-center">Estado SUNAT</th>
                            <th class="py-3 px-4">Hash SHA-256</th>
                            <th class="py-3 px-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($comprobantes as $pago)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-4 whitespace-nowrap text-slate-500 font-mono text-[11px]">
                                {{ $pago->created_at ? $pago->created_at->format('d/m/Y H:i') : '--' }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap font-mono font-bold text-slate-900">
                                {{ $pago->serie }}-{{ $pago->numero }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($pago->tipo_comprobante === 'FACTURA')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Factura UBL</span>
                                @elseif($pago->tipo_comprobante === 'BOLETA')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800">Boleta UBL</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">Nota Venta</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800 truncate max-w-[160px]" title="{{ $pago->padreFamilia->nombre_completo ?? 'Cliente General' }}">
                                    {{ $pago->padreFamilia->nombre_completo ?? 'CLIENTE GENERAL' }}
                                </div>
                                <span class="text-[10px] text-slate-400 font-mono">DNI: {{ $pago->padreFamilia->padre_dni ?? '00000000' }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-slate-700 truncate block max-w-[180px]" title="{{ $pago->pago_concepto }}">{{ $pago->pago_concepto }}</span>
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 whitespace-nowrap">
                                S/. {{ number_format($pago->monto_pago, 2) }}
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if($pago->estado_facturacion === 'EMITIDO')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800" title="{{ $pago->mensaje_sunat ?? 'Aceptado por SUNAT' }}">
                                        <i class="fa-solid fa-circle-check text-emerald-600"></i> Aceptado
                                    </span>
                                @elseif($pago->estado_facturacion === 'EN_COLA' || $pago->estado_facturacion === 'PENDIENTE')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">
                                        <i class="fa-solid fa-clock"></i> En Cola
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800" title="{{ $pago->mensaje_sunat }}">
                                        <i class="fa-solid fa-circle-xmark text-rose-600"></i> Error
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($pago->hash_cpe)
                                    <div class="flex items-center gap-1.5 font-mono text-[10px] text-slate-600">
                                        <span class="truncate max-w-[90px]" title="{{ $pago->hash_cpe }}">{{ $pago->hash_cpe }}</span>
                                        <button type="button" onclick="navigator.clipboard.writeText('{{ $pago->hash_cpe }}'); alert('Hash copiado al portapapeles');" class="text-slate-400 hover:text-indigo-600">
                                            <i class="fa-regular fa-copy text-xs"></i>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-slate-300 font-mono">--</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Ver Ticket -->
                                    <a href="{{ route('pagos.ticket', $pago) }}" target="_blank" title="Imprimir Ticket" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-xs transition-colors">
                                        <i class="fa-solid fa-print"></i>
                                    </a>

                                    <!-- Ver / Descargar XML UBL 2.1 -->
                                    <a href="{{ route('facturacion.xml', $pago) }}" target="_blank" title="Descargar XML UBL 2.1" class="w-7 h-7 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs transition-colors">
                                        <i class="fa-solid fa-file-code"></i>
                                    </a>

                                    <!-- Reintentar si está en error o en cola -->
                                    @if($pago->estado_facturacion !== 'EMITIDO')
                                    <form action="{{ route('facturacion.reintentar', $pago) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" title="Reintentar Emisión SUNAT" class="w-7 h-7 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-600 flex items-center justify-center text-xs transition-colors">
                                            <i class="fa-solid fa-rotate-right"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">
                                <i class="fa-solid fa-folder-open text-2xl text-slate-300 mb-2 block"></i>
                                No se encontraron comprobantes registrados en este filtro.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-4">
                {{ $comprobantes->links() }}
            </div>
        </div>

        <!-- ================= TAB 3: DIAGNÓSTICO & SIMULADOR EN VIVO ================= -->
        <div id="tab-content-diagnostico" class="p-6 sm:p-8 space-y-6 hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Simulador de Emisión -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                        <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-play text-indigo-600"></i> Simulador de Emisión UBL 2.1
                        </h4>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-indigo-100 text-indigo-700">Sin impacto fiscal</span>
                    </div>
                    <p class="text-xs text-slate-600">
                        Ejecute una emisión de prueba para verificar la generación del Hash SHA-256 DigestValue, la estructura XML estándar y la cadena del código QR SUNAT con sus credenciales actuales.
                    </p>

                    <form action="{{ route('facturacion.test') }}" method="POST" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tipo de Comprobante</label>
                                <select name="tipo_comprobante" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white font-medium">
                                    <option value="BOLETA">Boleta Electrónica (B001)</option>
                                    <option value="FACTURA">Factura Electrónica (F001)</option>
                                    <option value="NOTA_VENTA">Nota de Venta (NV01)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Monto de Prueba (S/.)</label>
                                <input type="number" step="0.10" name="monto" value="50.00" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-mono">
                            </div>
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-2 shadow-md shadow-indigo-600/20 transition-all">
                            <i class="fa-solid fa-paper-plane"></i>
                            Ejecutar Emisión de Prueba UBL 2.1
                        </button>
                    </form>
                </div>

                <!-- Estado del Daemon & Worker de Fondo -->
                <div class="p-6 rounded-2xl bg-slate-900 text-white border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                        <h4 class="font-bold text-slate-200 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-terminal text-indigo-400"></i> Worker de Segundo Plano (Queue Daemon)
                        </h4>
                        <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-800 text-slate-300">Laravel 11 Worker</span>
                    </div>

                    <p class="text-xs text-slate-300">
                        Para el funcionamiento asíncrono en ventanilla de cobro (POS), el sistema coloca los comprobantes en cola en la tabla <code>jobs</code> y los despacha sin congelar la interfaz.
                    </p>

                    <div class="bg-slate-950 p-3 rounded-xl border border-slate-800 text-xs font-mono text-indigo-300">
                        <span class="text-slate-500 block text-[10px] mb-1 font-sans">Comando de consola para iniciar worker en el servidor:</span>
                        <code>php artisan queue:work --tries=3 --timeout=60</code>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-2">
                        <span class="text-slate-400">Trabajos pendientes en cola:</span>
                        <span class="font-mono font-bold text-amber-400">{{ $jobsEnCola }} tareas</span>
                    </div>

                    <form action="{{ route('facturacion.procesar_cola') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-xl text-xs flex items-center justify-center gap-2 border border-slate-700 transition-all">
                            <i class="fa-solid fa-arrows-rotate"></i>
                            Vaciar y Procesar Cola Manualmente
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ================= TAB 4: GUÍA TÉCNICA & NORMATIVA ================= -->
        <div id="tab-content-arquitectura" class="p-6 sm:p-8 space-y-6 hidden text-slate-700 text-xs leading-relaxed">
            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 space-y-4">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-scale-balanced text-indigo-600"></i>
                    Marco Legal y Arquitectura UBL 2.1 en Instituciones Educativas y APAFA
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-sm space-y-2">
                        <h4 class="font-bold text-slate-900 text-xs flex items-center gap-1.5 text-indigo-600">
                            <i class="fa-solid fa-gavel"></i> Ley N° 28628 y D.S. N° 007-2026-MINEDU
                        </h4>
                        <p class="text-slate-600">
                            Las Asociaciones de Padres de Familia no persiguen fines de lucro y sus ingresos por cuotas ordinarias o extraordinarias no constituyen hecho generador del IGV (código de inafectación/exoneración <b>EXO 9997</b>). Los comprobantes emitidos sirven como constancia fehaciente ante la Dirección Escolar y los padres asociados.
                        </p>
                    </div>

                    <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-sm space-y-2">
                        <h4 class="font-bold text-slate-900 text-xs flex items-center gap-1.5 text-indigo-600">
                            <i class="fa-solid fa-microchip"></i> Microservicio Aislado /facturacion-service
                        </h4>
                        <p class="text-slate-600">
                            Ubicado en el directorio raíz <code>facturacion-service/</code>, contiene la clase <code>InvoiceEmitter</code>. Puede operar de forma embebida o como daemon CLI independiente mediante llamadas de terminal para integrarse a cualquier ecosistema de backend.
                        </p>
                    </div>
                </div>

                <div class="p-4 bg-indigo-50/70 rounded-xl border border-indigo-100 text-indigo-900 space-y-1.5">
                    <span class="font-bold block text-xs">Especificaciones UBL 2.1 soportadas:</span>
                    <ul class="list-disc list-inside space-y-1 text-[11px] text-indigo-800">
                        <li>Esquema XML OASIS Universal Business Language (UBL) 2.1 (Invoice-2).</li>
                        <li>Firma digital XML-DSig con algoritmo RSA-SHA1 / SHA-256 DigestValue.</li>
                        <li>Generación de cadena de Código QR bajo Resolución de Superintendencia N° 206-2019/SUNAT.</li>
                        <li>Generación y almacenamiento de CDR (Constancia de Recepción) simétrica.</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Script para tabs y utilidades -->
<script>
    function cambiarPestana(tabId) {
        const tabs = ['parametros', 'monitor', 'diagnostico', 'arquitectura'];
        tabs.forEach(t => {
            const btn = document.getElementById(`tab-btn-${t}`);
            const content = document.getElementById(`tab-content-${t}`);
            if (t === tabId) {
                btn.classList.add('bg-white', 'text-indigo-600', 'shadow-sm');
                btn.classList.remove('text-slate-600');
                content.classList.remove('hidden');
            } else {
                btn.classList.remove('bg-white', 'text-indigo-600', 'shadow-sm');
                btn.classList.add('text-slate-600');
                content.classList.add('hidden');
            }
        });

        // Actualizar URL sin recargar
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.replaceState({}, '', url);
    }

    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Inicializar pestaña según URL param
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        if (tab && ['parametros', 'monitor', 'diagnostico', 'arquitectura'].includes(tab)) {
            cambiarPestana(tab);
        } else {
            cambiarPestana('parametros');
        }
    });
</script>
@endsection
