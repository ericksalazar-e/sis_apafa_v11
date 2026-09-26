<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credencial Oficial del Consejo Directivo - APAFA</title>
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

    <div class="max-w-4xl w-full bg-white p-8 sm:p-12 rounded-2xl shadow-lg border border-slate-200 relative print-border">
        
        <div class="no-print mb-6 flex justify-end gap-3">
            <button onclick="window.print()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm shadow-md flex items-center gap-2">
                <i class="bi bi-printer"></i> Imprimir Credencial Institucional
            </button>
            <button onclick="window.close()" class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-bold text-sm">
                Cerrar
            </button>
        </div>

        <!-- Encabezado Oficial -->
        <div class="border-b-2 border-slate-800 pb-5 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-slate-100 rounded-xl flex items-center justify-center p-2 border border-slate-200">
                    <img src="{{ !empty($empresa->logo_url) ? asset('storage/' . $empresa->logo_url) : asset('img/logo-default.png') }}" onerror="this.src='https://ui-avatars.com/api/?name=APAFA&background=1e3a8a&color=fff&size=128'" class="max-h-full max-w-full object-contain" alt="Logo">
                </div>
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-slate-500 font-extrabold">Ministerio de Educación • UGEL</h2>
                    <h1 class="text-lg font-black text-slate-900 uppercase">{{ $empresa->nombre ?? 'I.E. EMBLEMÁTICA NERY GARCÍA ZÁRATE' }}</h1>
                    <p class="text-xs text-slate-600 font-bold">ASOCIACIÓN DE PADRES DE FAMILIA • LEY N° 28628</p>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-1 bg-blue-50 text-blue-800 border border-blue-300 rounded-lg text-xs font-black">
                    CREDENCIAL OFICIAL
                </span>
                <p class="text-[11px] font-mono text-slate-500 mt-1">D.S. N.º 007-2026-MINEDU</p>
            </div>
        </div>

        <div class="text-center my-8">
            <h2 class="text-xl sm:text-2xl font-black uppercase text-slate-900 tracking-wide underline underline-offset-8 decoration-blue-600">
                Credencial de Reconocimiento y Recomposición Directiva
            </h2>
            <p class="text-xs font-semibold text-slate-500 mt-3">
                Conforme al principio de continuidad institucional y causales de reemplazo reglamentario
            </p>
        </div>

        <div class="text-slate-800 text-sm leading-relaxed space-y-4 text-justify">
            <p>
                El Consejo Directivo de la Asociación de Padres de Familia de la <strong>{{ $empresa->nombre ?? 'I.E. Emblemática Nery García Zárate' }}</strong>, en concordancia con el <strong>Decreto Supremo N.º 007-2026-MINEDU</strong> que regula la continuidad de gestión y la asunción oportuna de directivos, certifica que los siguientes asociados ejercen legalmente los cargos de representación:
            </p>

            <!-- Tabla de Miembros Directivos -->
            <table class="w-full text-left text-xs border border-slate-300 my-4">
                <thead class="bg-slate-100 uppercase font-bold text-slate-700 border-b border-slate-300">
                    <tr>
                        <th class="p-2.5 border-r border-slate-300">Cargo Directivo</th>
                        <th class="p-2.5 border-r border-slate-300">Apellidos y Nombres</th>
                        <th class="p-2.5 border-r border-slate-300">DNI</th>
                        <th class="p-2.5">Estudiante(s) Vinculado(s)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($miembrosDirectiva as $m)
                    <tr>
                        <td class="p-2.5 border-r border-slate-200 font-bold text-slate-800">
                            {{ $m->cargo->nombre ?? 'DIRECTIVO' }}
                        </td>
                        <td class="p-2.5 border-r border-slate-200 font-semibold text-slate-900">
                            {{ $m->apellidos }}, {{ $m->nombres }}
                        </td>
                        <td class="p-2.5 border-r border-slate-200 font-mono">
                            {{ $m->dni }}
                        </td>
                        <td class="p-2.5 text-slate-600">
                            @foreach($m->estudiantes as $est)
                                <span>{{ $est->nombres }} ({{ $est->grado }}° "{{ $est->seccion }}")</span>
                            @endforeach
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-4 text-center text-slate-400">
                            No se han asignado cargos directivos aún.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if($movimientos->count() > 0)
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2 mt-4">
                <h4 class="font-bold text-xs uppercase text-slate-700">Historial Reciente de Vacancias y Asunciones (D.S. 007-2026-MINEDU):</h4>
                @foreach($movimientos as $mov)
                <div class="text-xs text-slate-600 border-b border-slate-200 pb-1.5 flex justify-between items-start gap-2">
                    <div>
                        • <strong>{{ $mov->cargo_nombre }}:</strong> Cesó <em>{{ $mov->directivo_saliente_nombre }}</em> (Causal: <span class="font-semibold">{{ $mov->causal_label }}</span>).
                        @if($mov->directivo_entrante_nombre && !str_contains($mov->directivo_entrante_nombre, 'VACANTE'))
                            Asume: <strong>{{ $mov->directivo_entrante_nombre }}</strong> (DNI: {{ $mov->directivo_entrante_dni ?? 'S/D' }}).
                        @else
                            <em>Vacante temporal por cubrir</em>.
                        @endif
                    </div>
                    <span class="font-mono text-[10px] text-slate-400 whitespace-nowrap">{{ \Carbon\Carbon::parse($mov->fecha_vacancia)->format('d/m/Y') }}</span>
                </div>
                @endforeach
            </div>
            @endif

            <p class="text-xs text-slate-500 pt-2">
                Se expide la presente credencial oficial para su presentación ante la Dirección del Plantel, UGEL y entidades financieras.
            </p>
        </div>

        <div class="mt-14 pt-8 border-t border-slate-200 grid grid-cols-2 gap-8 text-center text-xs">
            <div class="border-t border-slate-700 pt-2">
                <p class="font-bold text-slate-900">PRESIDENTE APAFA</p>
                <p class="text-[10px] text-slate-500">{{ $empresa->nombre ?? 'I.E. Nery García Zárate' }}</p>
            </div>
            <div class="border-t border-slate-700 pt-2">
                <p class="font-bold text-slate-900">DIRECTOR DE LA I.E.</p>
                <p class="text-[10px] text-slate-500">Visto Bueno Institucional</p>
            </div>
        </div>

    </div>

</body>
</html>
