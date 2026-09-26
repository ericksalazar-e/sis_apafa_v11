<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Constancia de Compensación por Trabajo Comunal - APAFA</title>
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
        
        <!-- Botón Imprimir Flotante -->
        <div class="no-print mb-6 flex justify-end gap-3">
            <button onclick="window.print()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm shadow-md flex items-center gap-2">
                <i class="bi bi-printer"></i> Imprimir Constancia Oficial
            </button>
            <button onclick="window.close()" class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-bold text-sm">
                Cerrar
            </button>
        </div>

        <!-- Encabezado Oficial -->
        <div class="border-b-2 border-slate-800 pb-6 flex items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-slate-100 rounded-xl flex items-center justify-center p-2 border border-slate-200">
                    <img src="{{ !empty($empresa->logo_url) ? asset('storage/' . $empresa->logo_url) : asset('img/logo-default.png') }}" onerror="this.src='https://ui-avatars.com/api/?name=APAFA&background=059669&color=fff&size=128'" class="max-h-full max-w-full object-contain" alt="Logo">
                </div>
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-slate-500 font-extrabold">República del Perú • Ministerio de Educación</h2>
                    <h1 class="text-lg font-black text-slate-900 uppercase">{{ $empresa->nombre ?? 'I.E. EMBLEMÁTICA NERY GARCÍA ZÁRATE' }}</h1>
                    <p class="text-xs text-slate-600 font-bold">ASOCIACIÓN DE PADRES DE FAMILIA (APAFA) • REGISTRO LEY N° 28628</p>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-1 bg-emerald-50 text-emerald-800 border border-emerald-300 rounded-lg text-xs font-black">
                    D.S. N.º 007-2026-MINEDU
                </span>
                <p class="text-[11px] font-mono text-slate-500 mt-1">CÓDIGO: FAENA-{{ str_pad($asistencia->id, 6, '0', STR_PAD_LEFT) }}</p>
            </div>
        </div>

        <!-- Título del Documento -->
        <div class="text-center my-8">
            <h2 class="text-xl sm:text-2xl font-black uppercase text-slate-900 tracking-wide underline underline-offset-8 decoration-emerald-600">
                Constancia de Compensación de Aportes por Servicios Comunales
            </h2>
            <p class="text-xs font-semibold text-slate-500 mt-3">
                Conforme al Artículo 78° del Reglamento de la Ley N° 28628, modificado por Decreto Supremo N.º 007-2026-MINEDU
            </p>
        </div>

        <!-- Cuerpo Legal -->
        <div class="text-slate-800 text-sm leading-relaxed space-y-4 text-justify">
            <p>
                El Consejo Directivo de la Asociación de Padres de Familia (APAFA) de la <strong>{{ $empresa->nombre ?? 'I.E. Emblemática Nery García Zárate' }}</strong>, hace constar que el señor(a):
            </p>

            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                <p><strong>ASOCIADO(A):</strong> {{ strtoupper($padre->apellidos) }}, {{ strtoupper($padre->nombres) }}</p>
                <p><strong>DOCUMENTO DE IDENTIDAD (DNI):</strong> <span class="font-mono">{{ $padre->dni }}</span></p>
                <p><strong>PARENTESCO / CONDICIÓN:</strong> {{ $padre->tipo_consanguinidad ?? 'PADRE DE FAMILIA' }}</p>
                <p><strong>ESTUDIANTE(S) A CARGO:</strong> 
                    @foreach($padre->estudiantes as $est)
                        <span class="inline-block bg-white px-2 py-0.5 rounded border border-slate-200 text-xs font-medium mr-1 my-0.5">
                            {{ $est->nombres }} {{ $est->apellidos }} ({{ $est->nivel ?? 'Secundaria' }} - {{ $est->grado }}° "{{ $est->seccion }}")
                        </span>
                    @endforeach
                </p>
            </div>

            <p>
                Ha participado activamente cumpliendo a cabalidad con la jornada de trabajo comunal y voluntario denominada <strong>"{{ $faena->titulo }}"</strong>, realizada en las instalaciones de la institución el día <strong>{{ \Carbon\Carbon::parse($faena->fecha_faena)->isoFormat('LL') }}</strong>, habiendo aportado un total de <strong>{{ $asistencia->horas_trabajadas }} horas de labor efectiva</strong> en beneficio de la comunidad escolar.
            </p>

            <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-200 text-emerald-950">
                <p class="font-bold flex items-center gap-2">
                    <i class="bi bi-check-circle-fill text-emerald-600"></i> LIQUIDACIÓN Y COMPENSACIÓN DE CUOTA:
                </p>
                <p class="text-xs mt-1">
                    En aplicación estricta del <strong>Decreto Supremo N.º 007-2026-MINEDU</strong> que garantiza la flexibilidad económica y prohíbe cualquier afectación o condicionamiento a los estudiantes, el valor de la labor realizada queda valorizado en la suma de <strong>S/ {{ number_format($asistencia->monto_compensado, 2) }} (SOLES)</strong>, el cual queda <strong>COMPENSADO Y DEDUCIDO DE LA CUOTA ORDINARIA DE APAFA CORRESPONDIENTE AL PRESENTE AÑO</strong>.
                </p>
            </div>

            <p class="text-xs text-slate-500">
                Se expide la presente constancia a solicitud de la parte interesada para los fines pertinentes de acreditación ante la Dirección de la I.E., Comité Electoral y Asambleas Generales de la APAFA.
            </p>
        </div>

        <!-- Firmas y QR de Autenticación -->
        <div class="mt-12 pt-8 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-8">
            <div class="flex items-center gap-4">
                <div class="w-24 h-24 bg-white p-2 border border-slate-300 rounded-xl shadow-sm flex items-center justify-center">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(url()->current()) }}" class="w-full h-full" alt="QR Verificación">
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-mono block">VERIFICACIÓN DIGITAL</span>
                    <span class="text-xs font-bold text-slate-700">DOCUMENTO VÁLIDO</span>
                    <p class="text-[11px] text-slate-500">Escanee para verificar validez institucional</p>
                </div>
            </div>

            <div class="flex gap-12 text-center">
                <div class="border-t border-slate-400 pt-2 w-40">
                    <p class="text-xs font-bold text-slate-800">PRESIDENTE APAFA</p>
                    <p class="text-[10px] text-slate-500">{{ $empresa->nombre ?? 'I.E. Nery García Zárate' }}</p>
                </div>
                <div class="border-t border-slate-400 pt-2 w-40">
                    <p class="text-xs font-bold text-slate-800">TESORERO APAFA</p>
                    <p class="text-[10px] text-slate-500">Responsable Económico</p>
                </div>
            </div>
        </div>

        <!-- Nota Legal y de Seguridad (Ley N° 29783 / MINEDU) -->
        <div class="mt-8 pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between text-[10px] text-slate-400 gap-2">
            <span>* Se certifica que la labor acreditada correspondió exclusivamente a mantenimiento preventivo menor. Queda terminantemente prohibida la demolición de estructuras, torres, trabajos en altura y la participación de escolares (Ley N° 29783).</span>
            <span class="font-semibold text-slate-500">D.S. N.º 007-2026-MINEDU</span>
        </div>

    </div>

</body>
</html>
