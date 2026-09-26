<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dictamen de Auditoría - Consejo de Vigilancia APAFA</title>
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
        
        <div class="no-print mb-6 flex justify-end gap-3">
            <button onclick="window.print()" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-sm shadow-md flex items-center gap-2">
                <i class="bi bi-printer"></i> Imprimir Dictamen Oficial
            </button>
            <button onclick="window.close()" class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-bold text-sm">
                Cerrar
            </button>
        </div>

        <!-- Encabezado Institucional -->
        <div class="border-b-2 border-slate-800 pb-5 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-slate-100 rounded-xl flex items-center justify-center p-2 border border-slate-200">
                    <img src="{{ !empty($empresa->logo_url) ? asset('storage/' . $empresa->logo_url) : asset('img/logo-default.png') }}" onerror="this.src='https://ui-avatars.com/api/?name=APAFA&background=e11d48&color=fff&size=128'" class="max-h-full max-w-full object-contain" alt="Logo">
                </div>
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-slate-500 font-extrabold">Sector Educación • Región Ayacucho</h2>
                    <h1 class="text-lg font-black text-slate-900 uppercase">{{ $empresa->nombre ?? 'I.E. EMBLEMÁTICA NERY GARCÍA ZÁRATE' }}</h1>
                    <p class="text-xs text-slate-600 font-bold">CONSEJO DE VIGILANCIA DE LA ASOCIACIÓN DE PADRES DE FAMILIA</p>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-1 bg-rose-50 text-rose-800 border border-rose-300 rounded-lg text-xs font-black">
                    DICTAMEN DE FISCALIZACIÓN
                </span>
                <p class="text-[11px] font-mono text-slate-500 mt-1">D.S. N.º 007-2026-MINEDU</p>
            </div>
        </div>

        <div class="text-center my-8">
            <h2 class="text-xl sm:text-2xl font-black uppercase text-slate-900 tracking-wide underline underline-offset-8 decoration-rose-600">
                Dictamen de Fiscalización y Auditoría Contable
            </h2>
            <p class="text-xs font-semibold text-slate-500 mt-3">
                Periodo: {{ $dictamen->periodo }} • Fecha: {{ \Carbon\Carbon::parse($dictamen->created_at)->isoFormat('LL') }}
            </p>
        </div>

        <!-- Contenido del Dictamen -->
        <div class="text-slate-800 text-sm leading-relaxed space-y-4 text-justify">
            <p>
                El <strong>Consejo de Vigilancia de la APAFA</strong>, en ejercicio de las atribuciones fiscalizadoras consagradas en el <strong>Decreto Supremo N.º 007-2026-MINEDU</strong> y la Ley N° 28628, emite el presente dictamen tras haber auditado exhaustivamente las operaciones económicas, cuentas bancarias, ingresos por cuotas y alquiler de quioscos, así como las facturas de gastos del Plan Operativo Anual (POA):
            </p>

            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                <h4 class="font-bold text-slate-800 mb-2 uppercase text-xs">Materia Auditada:</h4>
                <p class="text-sm font-semibold text-slate-900">{{ $dictamen->titulo }}</p>
            </div>

            <!-- Balances Auditados -->
            <div class="grid grid-cols-3 gap-4 text-center my-4">
                <div class="p-3 bg-blue-50 rounded-xl border border-blue-200">
                    <p class="text-[11px] text-blue-700 font-bold uppercase">Ingresos Auditados</p>
                    <p class="text-lg font-black text-blue-900 mt-1">S/ {{ number_format($dictamen->monto_ingresos_auditado, 2) }}</p>
                </div>
                <div class="p-3 bg-rose-50 rounded-xl border border-rose-200">
                    <p class="text-[11px] text-rose-700 font-bold uppercase">Gastos Verificados</p>
                    <p class="text-lg font-black text-rose-900 mt-1">S/ {{ number_format($dictamen->monto_gastos_auditado, 2) }}</p>
                </div>
                <div class="p-3 {{ $dictamen->saldo_auditado >= 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-rose-50 border-rose-200' }} rounded-xl border">
                    <p class="text-[11px] {{ $dictamen->saldo_auditado >= 0 ? 'text-emerald-700' : 'text-rose-700' }} font-bold uppercase">
                        {{ $dictamen->saldo_auditado >= 0 ? 'Saldo en Cuenta' : 'Déficit Financiero' }}
                    </p>
                    <p class="text-lg font-black {{ $dictamen->saldo_auditado >= 0 ? 'text-emerald-900' : 'text-rose-900' }} mt-1">
                        S/ {{ number_format($dictamen->saldo_auditado, 2) }}
                    </p>
                </div>
            </div>

            <div>
                <h4 class="font-bold text-slate-800 text-xs uppercase mb-1">Conclusión del Examen de Auditoría:</h4>
                <div class="p-3 rounded-xl border font-bold text-xs flex items-center gap-2 {{ $dictamen->tipo_dictamen === 'FAVORABLE' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : ($dictamen->tipo_dictamen === 'OBSERVADO' ? 'bg-amber-50 text-amber-800 border-amber-300' : 'bg-rose-50 text-rose-800 border-rose-300') }}">
                    <i class="bi {{ $dictamen->tipo_dictamen === 'FAVORABLE' ? 'bi-shield-check text-emerald-600' : ($dictamen->tipo_dictamen === 'OBSERVADO' ? 'bi-exclamation-triangle text-amber-600' : 'bi-x-octagon text-rose-600') }} text-lg"></i>
                    RESULTADO: {{ $dictamen->tipo_dictamen }}
                </div>
            </div>

            <div>
                <h4 class="font-bold text-slate-800 text-xs uppercase mb-1">Fundamentación y Hallazgos:</h4>
                <p class="text-xs text-slate-700 whitespace-pre-line bg-slate-50 p-4 rounded-xl border border-slate-200 leading-normal">
                    {{ $dictamen->detalles_auditoria }}
                </p>
            </div>

            @if($dictamen->observaciones_recomendaciones)
            <div>
                <h4 class="font-bold text-slate-800 text-xs uppercase mb-1">Recomendaciones a la Junta Directiva:</h4>
                <p class="text-xs text-slate-700 whitespace-pre-line bg-amber-50/50 p-3 rounded-xl border border-amber-200 leading-normal">
                    {{ $dictamen->observaciones_recomendaciones }}
                </p>
            </div>
            @endif

            <p class="text-xs text-slate-500 pt-2">
                El presente dictamen es de carácter vinculante y público para conocimiento de la Asamblea General de Padres de Familia y la Dirección del Plantel.
            </p>
        </div>

        <!-- Firmas Oficiales -->
        <div class="mt-14 pt-8 border-t border-slate-200 grid grid-cols-2 gap-8 text-center text-xs">
            <div class="border-t border-slate-700 pt-2 flex flex-col items-center">
                <p class="font-bold text-slate-900">PRESIDENTE</p>
                <p class="text-[10px] text-slate-500">Consejo de Vigilancia APAFA</p>
            </div>
            <div class="border-t border-slate-700 pt-2 flex flex-col items-center">
                <p class="font-bold text-slate-900">SECRETARIO / VOCAL</p>
                <p class="text-[10px] text-slate-500">Consejo de Vigilancia APAFA</p>
            </div>
        </div>

    </div>

</body>
</html>
