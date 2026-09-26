<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Padrón Electoral Oficial - APAFA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans p-4 sm:p-8">

    <div class="max-w-5xl mx-auto bg-white p-8 rounded-2xl shadow-lg border border-slate-200">
        
        <!-- Botones No Print -->
        <div class="no-print mb-6 flex justify-between items-center bg-slate-50 p-4 rounded-xl border border-slate-200">
            <div>
                <span class="font-bold text-slate-800 text-sm">Padrón de Asociados Habilitados (Corte al 31 de Agosto)</span>
                <p class="text-xs text-slate-500">Total Electores: {{ $votos->count() }} familias</p>
            </div>
            <div class="flex gap-2">
                <button onclick="window.print()" class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-bold text-xs shadow-md flex items-center gap-1.5">
                    <i class="bi bi-printer"></i> Imprimir Padrón para Mesa
                </button>
                <button onclick="window.close()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-bold text-xs">
                    Cerrar
                </button>
            </div>
        </div>

        <!-- Encabezado Oficial -->
        <div class="border-b-2 border-slate-800 pb-4 mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-slate-100 rounded-xl flex items-center justify-center p-2 border border-slate-200">
                    <img src="{{ !empty($empresa->logo_url) ? asset('storage/' . $empresa->logo_url) : asset('img/logo-default.png') }}" onerror="this.src='https://ui-avatars.com/api/?name=APAFA&background=6b21a8&color=fff&size=128'" class="max-h-full max-w-full object-contain" alt="Logo">
                </div>
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-slate-500 font-extrabold">Ministerio de Educación • UGEL</h2>
                    <h1 class="text-base font-black text-slate-900 uppercase">{{ $empresa->nombre ?? 'I.E. EMBLEMÁTICA NERY GARCÍA ZÁRATE' }}</h1>
                    <p class="text-xs text-slate-600 font-bold">COMITÉ ELECTORAL DE LA ASOCIACIÓN DE PADRES DE FAMILIA (APAFA)</p>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-block px-2.5 py-1 bg-purple-50 text-purple-800 border border-purple-300 rounded-md text-xs font-black">
                    PADRÓN OFICIAL DE SUFRAGIO
                </span>
                <p class="text-[11px] font-mono text-slate-500 mt-0.5">D.S. N.º 007-2026-MINEDU</p>
                <p class="text-[10px] text-slate-400">Corte: 31/08/2026</p>
            </div>
        </div>

        <div class="text-center mb-4">
            <h2 class="text-sm font-extrabold uppercase text-slate-800 tracking-wider">
                PADRÓN DEFINITIVO DE ASOCIADOS CON DERECHO A VOTO - ELECCIONES GENERALES APAFA
            </h2>
            <p class="text-xs text-slate-500">Mesa Única de Sufragio • Conforme a Ley N° 28628 (1 Voto por Familia)</p>
        </div>

        <!-- Tabla del Padrón Electoral -->
        <table class="w-full text-left text-xs border border-slate-300">
            <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-300 uppercase">
                <tr>
                    <th class="p-2 border-r border-slate-300 w-10 text-center">N°</th>
                    <th class="p-2 border-r border-slate-300 w-24">DNI</th>
                    <th class="p-2 border-r border-slate-300">Apellidos y Nombres (Asociado)</th>
                    <th class="p-2 border-r border-slate-300">Estudiante(s) a Cargo</th>
                    <th class="p-2 border-r border-slate-300 w-36 text-center">Firma del Elector</th>
                    <th class="p-2 w-24 text-center">Huella</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($votos as $index => $v)
                <tr class="h-14">
                    <td class="p-2 border-r border-slate-200 text-center font-bold text-slate-500">
                        {{ $index + 1 }}
                    </td>
                    <td class="p-2 border-r border-slate-200 font-mono font-bold text-slate-800">
                        {{ $v->padreFamilia->dni ?? 'N/A' }}
                    </td>
                    <td class="p-2 border-r border-slate-200 font-bold text-slate-900">
                        {{ strtoupper($v->padreFamilia->apellidos ?? '') }}, {{ strtoupper($v->padreFamilia->nombres ?? '') }}
                        <span class="text-[10px] block font-normal text-slate-400">Carné: {{ $v->padreFamilia->carnet_codigo ?? 'S/C' }}</span>
                    </td>
                    <td class="p-2 border-r border-slate-200 text-slate-600 text-[11px]">
                        @if($v->padreFamilia && $v->padreFamilia->estudiantes->count() > 0)
                            @foreach($v->padreFamilia->estudiantes as $est)
                                <div>• {{ $est->nombres }} ({{ $est->nivel ?? 'Sec' }} {{ $est->grado }}° "{{ $est->seccion }}")</div>
                            @endforeach
                        @else
                            <span class="text-slate-400 italic">Asociado Registrado</span>
                        @endif
                    </td>
                    <td class="p-2 border-r border-slate-200 text-center">
                        @if($v->ha_votado)
                        <span class="text-[10px] font-bold text-emerald-600">[VOTÓ VÍA QR]</span>
                        @endif
                    </td>
                    <td class="p-2 text-center">
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-6 text-center text-slate-400">
                        No hay electores incorporados al padrón electoral aún.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Firmas de Miembros de Mesa al Pie -->
        <div class="mt-12 pt-6 border-t border-slate-300 grid grid-cols-3 gap-8 text-center text-xs">
            <div class="border-t border-slate-400 pt-2">
                <p class="font-bold text-slate-800">PRESIDENTE DE MESA</p>
                <p class="text-[10px] text-slate-500 font-mono">DNI: _________________</p>
            </div>
            <div class="border-t border-slate-400 pt-2">
                <p class="font-bold text-slate-800">SECRETARIO DE MESA</p>
                <p class="text-[10px] text-slate-500 font-mono">DNI: _________________</p>
            </div>
            <div class="border-t border-slate-400 pt-2">
                <p class="font-bold text-slate-800">VOCAL DE MESA</p>
                <p class="text-[10px] text-slate-500 font-mono">DNI: _________________</p>
            </div>
        </div>

    </div>

</body>
</html>
