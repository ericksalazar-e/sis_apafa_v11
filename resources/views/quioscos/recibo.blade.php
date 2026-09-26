<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Arrendamiento de Quiosco - APAFA</title>
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

    <div class="max-w-2xl w-full bg-white p-8 rounded-2xl shadow-lg border border-slate-200 print-border">
        <div class="no-print mb-6 flex justify-end gap-2">
            <button onclick="window.print()" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold text-xs shadow-md flex items-center gap-1.5">
                <i class="bi bi-printer"></i> Imprimir Recibo
            </button>
            <button onclick="window.close()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-bold text-xs">
                Cerrar
            </button>
        </div>

        <div class="border-b-2 border-slate-800 pb-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center p-2 border border-slate-200">
                    <img src="{{ !empty($empresa->logo_url) ? asset('storage/' . $empresa->logo_url) : asset('img/logo-default.png') }}" onerror="this.src='https://ui-avatars.com/api/?name=APAFA&background=d97706&color=fff&size=128'" class="max-h-full max-w-full object-contain" alt="Logo">
                </div>
                <div>
                    <h1 class="text-sm font-black text-slate-900 uppercase">{{ $empresa->nombre ?? 'I.E. EMBLEMÁTICA NERY GARCÍA ZÁRATE' }}</h1>
                    <p class="text-[11px] text-slate-600 font-bold">APAFA • TESORERÍA Y CAJA DE RECAUDACIÓN</p>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-block px-2.5 py-0.5 bg-amber-50 text-amber-800 border border-amber-300 rounded font-black text-xs">
                    RECIBO DE ARRENDAMIENTO
                </span>
                <p class="text-xs font-mono font-bold text-slate-700 mt-1">{{ $pago->comprobante_numero }}</p>
            </div>
        </div>

        <div class="my-6 space-y-3 text-xs text-slate-800">
            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-1">
                <p><strong>QUIOSCO / CAFETÍN:</strong> {{ $pago->concesion->quiosco->nombre ?? 'Quiosco Escolar' }}</p>
                <p><strong>CONCESIONARIO(A):</strong> {{ $pago->concesion->nombre_concesionario ?? 'N/A' }}</p>
                <p><strong>DNI / RUC:</strong> <span class="font-mono">{{ $pago->concesion->dni_ruc ?? 'N/A' }}</span></p>
                <p><strong>CONCEPTO:</strong> Pago de Canon Mensual de Arrendamiento (Ley N° 28628 &amp; Ley N° 30021)</p>
                <p><strong>MES CANCELADO:</strong> <span class="font-bold underline text-emerald-800 uppercase">{{ strtoupper($pago->mes_correspondiente) }}</span> (Cuota Mensual)</p>
            </div>

            <div class="flex items-center justify-between p-4 bg-emerald-50 rounded-xl border border-emerald-200 text-emerald-950 font-bold">
                <span>IMPORTE RECIBIDO:</span>
                <span class="text-xl font-black">S/ {{ number_format($pago->monto, 2) }}</span>
            </div>

            <div class="flex justify-between text-slate-500 pt-1">
                <span>Método de Pago: <strong>{{ $pago->metodo_pago }}</strong></span>
                <span>Fecha: <strong>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</strong></span>
            </div>

            @if($pago->observaciones)
            <p class="text-slate-500 text-[11px] bg-slate-50 p-2 rounded">
                <strong>Observaciones:</strong> {{ $pago->observaciones }}
            </p>
            @endif
        </div>

        <div class="mt-10 pt-4 border-t border-slate-200 flex justify-between items-center text-center text-xs">
            <div class="border-t border-slate-400 pt-1 w-36">
                <p class="font-bold text-slate-800">CONCESIONARIO</p>
                <p class="text-[10px] text-slate-400">Firma de Entrega</p>
            </div>
            <div class="border-t border-slate-400 pt-1 w-36">
                <p class="font-bold text-slate-800">TESORERO APAFA</p>
                <p class="text-[10px] text-slate-400">Recibí Conforme</p>
            </div>
        </div>
    </div>

</body>
</html>
