<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carta Poder Simple APAFA - D.S. N.º 007-2026-MINEDU</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .print-border { border: 2px solid #0f172a !important; }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans p-4 sm:p-8 flex justify-center">

    <div class="max-w-3xl w-full bg-white p-8 sm:p-12 rounded-2xl shadow-lg border border-slate-200 relative print-border">
        
        <!-- Botones de Acción -->
        <div class="no-print mb-6 flex justify-end gap-3">
            <button onclick="window.print()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm shadow-md flex items-center gap-2">
                <i class="bi bi-printer"></i> Imprimir Carta Poder
            </button>
            <button onclick="window.close()" class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-bold text-sm">
                Cerrar
            </button>
        </div>

        <!-- Encabezado Institucional -->
        <div class="border-b-2 border-slate-800 pb-5 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-slate-100 rounded-xl flex items-center justify-center p-2 border border-slate-200">
                    <img src="{{ !empty($empresa->logo_url) ? asset('storage/' . $empresa->logo_url) : asset('img/logo-default.png') }}" onerror="this.src='https://ui-avatars.com/api/?name=APAFA&background=1e40af&color=fff&size=128'" class="max-h-full max-w-full object-contain" alt="Logo">
                </div>
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-slate-500 font-extrabold">República del Perú • Sector Educación</h2>
                    <h1 class="text-lg font-black text-slate-900 uppercase">{{ $empresa->nombre ?? 'I.E. EMBLEMÁTICA NERY GARCÍA ZÁRATE' }}</h1>
                    <p class="text-xs text-slate-600 font-bold">ASOCIACIÓN DE PADRES DE FAMILIA (APAFA)</p>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-1 bg-blue-50 text-blue-800 border border-blue-300 rounded-lg text-xs font-black">
                    D.S. N.º 007-2026-MINEDU
                </span>
                <p class="text-[11px] font-mono text-slate-500 mt-1">ARTS. 13 Y 14 REGLAMENTO LEY 28628</p>
            </div>
        </div>

        <!-- Título -->
        <div class="text-center my-8">
            <h2 class="text-xl sm:text-2xl font-black uppercase text-slate-900 tracking-wide underline underline-offset-8 decoration-blue-600">
                Carta Poder Simple de Representación Familiar
            </h2>
            <p class="text-xs font-semibold text-slate-500 mt-3">
                Validez legal directa sin costo notarial para Asambleas, Comicios Electorales y Gestión Escolar
            </p>
        </div>

        <!-- Contenido de la Carta Poder -->
        <div class="text-slate-800 text-sm leading-relaxed space-y-4 text-justify">
            <p>
                Conste por el presente documento privado la <strong>CARTA PODER SIMPLE</strong> que otorgo yo:
            </p>

            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-1">
                <p><strong>PODERDANTE (PADRE / MADRE / TITULAR):</strong> 
                    <span class="font-bold text-slate-900">{{ strtoupper($padre->apellidos) }}, {{ strtoupper($padre->nombres) }}</span>
                </p>
                <p><strong>DOCUMENTO NACIONAL DE IDENTIDAD (DNI):</strong> <span class="font-mono font-bold">{{ $padre->dni }}</span></p>
                <p><strong>DOMICILIO:</strong> {{ $padre->direccion ?? 'AYACUCHO, PERÚ' }}</p>
                <p><strong>TELÉFONO / CELULAR:</strong> {{ $padre->telefono ?? 'N/A' }}</p>
            </div>

            <p>
                En pleno uso de mis facultades legales y al amparo de los <strong>Artículos 13 y 14 del Reglamento de la Ley N° 28628</strong>, modificado expresamente por el <strong>Decreto Supremo N.º 007-2026-MINEDU</strong>, otorgo <strong>PODER Y FACULTAD SUFICIENTE DE REPRESENTACIÓN</strong> a favor de:
            </p>

            <div class="bg-blue-50 p-4 rounded-xl border border-blue-200 space-y-1 text-blue-950">
                <p><strong>APODERADO(A) / REPRESENTANTE DELEGADO(A):</strong> 
                    <span class="font-bold">{{ strtoupper($padre->conyuge_nombres ?? '________________________________________________') }}</span>
                </p>
                <p><strong>DNI DEL APODERADO(A):</strong> <span class="font-mono font-bold">{{ $padre->conyuge_dni ?? '________________' }}</span></p>
                <p><strong>VÍNCULO DE PARENTESCO:</strong> 
                    <span class="font-bold underline">{{ strtoupper($padre->tipo_consanguinidad ?? 'ABUELO / HERMANO MAYOR / TÍO / APODERADO') }}</span>
                </p>
            </div>

            <p>
                Para que en mi nombre y representación ejerza plenamente los derechos y deberes que me corresponden como asociado(a) de la APAFA de la <strong>{{ $empresa->nombre ?? 'I.E. Emblemática Nery García Zárate' }}</strong>, respecto a mi menor(es) hijo(s) y/o pupilo(s):
            </p>

            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                <p class="font-bold text-xs uppercase text-slate-600 mb-2">Estudiante(s) Vinculado(s):</p>
                <div class="space-y-1">
                    @forelse($padre->estudiantes as $est)
                    <div class="flex items-center justify-between text-xs bg-white p-2 rounded border border-slate-200">
                        <span class="font-bold text-slate-800">{{ $est->apellidos }}, {{ $est->nombres }}</span>
                        <span class="text-slate-600 font-medium">Nivel: {{ $est->nivel ?? 'Secundaria' }} | Grado: {{ $est->grado }}° "{{ $est->seccion }}"</span>
                        <span class="font-mono text-slate-500">DNI: {{ $est->dni }}</span>
                    </div>
                    @empty
                    @if($estudiante)
                    <div class="flex items-center justify-between text-xs bg-white p-2 rounded border border-slate-200">
                        <span class="font-bold text-slate-800">{{ $estudiante->apellidos }}, {{ $estudiante->nombres }}</span>
                        <span class="text-slate-600 font-medium">Nivel: {{ $estudiante->nivel ?? 'Secundaria' }} | Grado: {{ $estudiante->grado }}° "{{ $estudiante->seccion }}"</span>
                        <span class="font-mono text-slate-500">DNI: {{ $estudiante->dni }}</span>
                    </div>
                    @endif
                    @endforelse
                </div>
            </div>

            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900 leading-normal">
                <strong>CLÁUSULA DE FACULTADES:</strong> La persona apoderada queda investida de voz y voto en las Asambleas Generales Ordinarias y Extraordinarias, derecho de sufragio en los Comicios Electorales de la APAFA, recepción de libretas de información y retiro autorizado del menor, <em>dejándose expresa constancia que este poder no constituye renuncia a la patria potestad</em>, en concordancia con el D.S. N.º 007-2026-MINEDU.
            </div>

            <p class="text-xs text-slate-500 pt-2">
                En señal de total conformidad y ratificación, suscribimos el presente documento el día <strong>{{ \Carbon\Carbon::now()->isoFormat('LL') }}</strong>.
            </p>
        </div>

        <!-- Firmas y Huellas Digitales -->
        <div class="mt-14 pt-8 border-t border-slate-200 grid grid-cols-2 gap-8 text-center">
            <!-- Firma Poderdante -->
            <div class="flex flex-col items-center">
                <div class="w-24 h-28 border border-dashed border-slate-300 rounded mb-2 flex items-center justify-center text-[10px] text-slate-400">
                    HUELLA DIGITAL
                </div>
                <div class="border-t border-slate-700 w-48 pt-1">
                    <p class="text-xs font-bold text-slate-900">{{ strtoupper($padre->apellidos) }}, {{ strtoupper($padre->nombres) }}</p>
                    <p class="text-[11px] text-slate-500 font-mono">DNI: {{ $padre->dni }}</p>
                    <p class="text-[10px] text-slate-600 font-semibold uppercase">PODERDANTE (PADRE/MADRE)</p>
                </div>
            </div>

            <!-- Firma Apoderado Delegado -->
            <div class="flex flex-col items-center">
                <div class="w-24 h-28 border border-dashed border-slate-300 rounded mb-2 flex items-center justify-center text-[10px] text-slate-400">
                    HUELLA DIGITAL
                </div>
                <div class="border-t border-slate-700 w-48 pt-1">
                    <p class="text-xs font-bold text-slate-900">{{ strtoupper($padre->conyuge_nombres ?? 'APODERADO DELEGADO') }}</p>
                    <p class="text-[11px] text-slate-500 font-mono">DNI: {{ $padre->conyuge_dni ?? '___________' }}</p>
                    <p class="text-[10px] text-slate-600 font-semibold uppercase">{{ strtoupper($padre->tipo_consanguinidad ?? 'APODERADO / REPRESENTANTE') }}</p>
                </div>
            </div>
        </div>

        <!-- Pie con QR de Verificación -->
        <div class="mt-8 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
            <span>Sistema SIS-APAFA • Generado Digitalmente conforme a D.S. N.º 007-2026-MINEDU</span>
            <span class="font-mono">CÓDIGO: PODER-{{ $padre->dni }}-2026</span>
        </div>

    </div>

</body>
</html>
