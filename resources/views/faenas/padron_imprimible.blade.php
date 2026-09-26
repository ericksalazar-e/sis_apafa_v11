<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Padrón Oficial de Asistencia a Faena - {{ $faena->titulo }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 11px !important; margin: 0; padding: 10mm; }
            @page { size: A4 portrait; margin: 8mm; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans p-4 sm:p-8">

    <!-- Barra Superior de Acciones (No Imprimible) -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="{{ route('faenas.show', $faena->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-slate-700 rounded-xl shadow-sm border border-slate-200 hover:bg-slate-50 text-sm font-semibold">
            <i class="bi bi-arrow-left"></i> Volver a la Faena
        </a>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white rounded-xl shadow-md hover:bg-emerald-700 text-sm font-bold transition-all">
                <i class="bi bi-printer-fill"></i> Imprimir Padrón Oficial
            </button>
        </div>
    </div>

    <!-- Hoja A4 Oficial de Padrón de Asistentes -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-2xl shadow-sm border border-slate-200">
        <!-- Cabecera Oficial -->
        <div class="border-b-2 border-slate-800 pb-5 mb-6">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    @if(!empty($empresa->logo))
                        <img src="{{ asset('storage/' . $empresa->logo) }}" alt="Logo" class="h-16 w-16 object-contain rounded-lg">
                    @else
                        <div class="w-16 h-16 rounded-xl bg-emerald-700 text-white flex items-center justify-center font-black text-2xl">
                            {{ substr($empresa->nombre ?? 'IE', 0, 2) }}
                        </div>
                    @endif
                    <div>
                        <span class="text-[11px] font-bold tracking-widest text-emerald-800 uppercase block">Asociación de Padres de Familia (APAFA)</span>
                        <h1 class="text-xl font-black text-slate-900 leading-tight uppercase">{{ $empresa->nombre ?? 'Institución Educativa' }}</h1>
                        <p class="text-xs text-slate-500 font-medium">Ley N.º 28628 • D.S. N.º 007-2026-MINEDU (Compensación de Cuota por Trabajo Comunal)</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-block px-3 py-1 bg-emerald-50 text-emerald-800 border border-emerald-300 rounded-lg text-xs font-bold uppercase">
                        PADRÓN DE ASISTENTES
                    </span>
                    <p class="text-[11px] text-slate-400 mt-1 font-mono">Cód: FAE-{{ str_pad($faena->id, 5, '0', STR_PAD_LEFT) }}-{{ date('Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Ficha Resumen de la Jornada -->
        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 mb-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-slate-400 uppercase font-bold block text-[10px]">Faena / Actividad</span>
                <span class="font-bold text-slate-800 text-sm">{{ $faena->titulo }}</span>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-bold block text-[10px]">Fecha Realizada</span>
                <span class="font-bold text-slate-800">{{ \Carbon\Carbon::parse($faena->fecha_faena)->isoFormat('LL') }}</span>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-bold block text-[10px]">Lugar / Sector</span>
                <span class="font-bold text-slate-800">{{ $faena->lugar }}</span>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-bold block text-[10px]">Valor de Compensación</span>
                <span class="font-black text-emerald-700 text-sm">S/ {{ number_format($faena->valor_compensacion, 2) }} / padre</span>
            </div>
        </div>

        <!-- Métricas Rápidas -->
        <div class="flex items-center justify-between mb-4 px-1">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i class="bi bi-people-fill text-emerald-600"></i> Relación Nominal de Padres y Apoderados Acreditados
            </h2>
            <div class="text-xs font-semibold text-slate-600">
                Total Asistieron: <strong class="text-emerald-700 font-extrabold">{{ $faena->asistencias->count() }}</strong> padres |
                Total Compensado: <strong class="text-emerald-700 font-extrabold">S/ {{ number_format($faena->asistencias->sum('monto_compensado'), 2) }}</strong>
            </div>
        </div>

        <!-- Tabla Nominal de Asistentes -->
        <div class="overflow-x-auto border border-slate-200 rounded-xl mb-8">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-100 text-slate-700 uppercase font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-3 w-8 text-center">N°</th>
                        <th class="py-2.5 px-3">DNI</th>
                        <th class="py-2.5 px-3">Apellidos y Nombres (Padre / Tutor)</th>
                        <th class="py-2.5 px-3">Estudiante(s) Vinculado(s)</th>
                        <th class="py-2.5 px-3 text-center">Horas</th>
                        <th class="py-2.5 px-3 text-right">Deducción</th>
                        <th class="py-2.5 px-3 text-center w-28">Firma / Huella</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($faena->asistencias as $index => $asistencia)
                    @php $padre = $asistencia->padreFamilia; @endphp
                    <tr>
                        <td class="py-2.5 px-3 text-center font-bold text-slate-500">{{ $index + 1 }}</td>
                        <td class="py-2.5 px-3 font-mono font-bold">{{ $padre->dni ?? 'N/A' }}</td>
                        <td class="py-2.5 px-3 font-semibold text-slate-900">
                            {{ $padre->apellidos ?? 'N/A' }}, {{ $padre->nombres ?? '' }}
                            @if($asistencia->observaciones)
                                <span class="block text-[10px] text-slate-500 font-normal italic">{{ $asistencia->observaciones }}</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3 text-slate-600">
                            @if($padre && $padre->estudiantes && $padre->estudiantes->count() > 0)
                                @foreach($padre->estudiantes as $est)
                                    <span class="inline-block bg-slate-100 px-1.5 py-0.5 rounded text-[10px] mr-1">
                                        {{ $est->ape_pat }} {{ $est->nombres }} ({{ $est->nivel }} {{ $est->grado }}° "{{ $est->seccion }}")
                                    </span>
                                @endforeach
                            @else
                                <span class="text-slate-400 italic">No registrado</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3 text-center font-bold">{{ $asistencia->horas_trabajadas }} hrs</td>
                        <td class="py-2.5 px-3 text-right font-extrabold text-emerald-800">
                            S/ {{ number_format($asistencia->monto_compensado, 2) }}
                        </td>
                        <td class="py-2.5 px-3 border-l border-slate-200 text-center">
                            <div class="h-8 border-b border-dotted border-slate-400 w-full"></div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 italic">
                            No se registraron asistencias para esta faena.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Cláusula Obligatoria de Seguridad y Salud en el Trabajo (Ley N° 29783 / MINEDU) -->
        <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-[10px] text-red-950 mb-6 space-y-1">
            <span class="font-bold flex items-center gap-1 uppercase tracking-wide">
                <i class="bi bi-shield-fill-exclamation text-red-600"></i> PROTOCOLO VITAL DE SEGURIDAD (PREVENCIÓN DE ACCIDENTES MORTALES):
            </span>
            <p class="leading-tight text-slate-700">
                Se deja constancia expresa de que las actividades autorizadas correspondieron exclusivamente a mantenimiento menor preventivo no peligroso a nivel de piso. <strong>Queda terminantemente PROHIBIDO</strong> encomendar labores de demolición de torres, muros o estructuras, trabajos en altura sobre techos, manipulación de redes eléctricas de media/alta tensión, y la participación física de escolares menores de edad.
            </p>
        </div>

        <!-- Secciones de Firmas Oficiales y Conformidad -->
        <div class="grid grid-cols-3 gap-6 pt-6 border-t border-slate-200 text-center text-xs text-slate-700">
            <div>
                <div class="border-t border-slate-400 w-4/5 mx-auto pt-2 font-bold uppercase">
                    Presidente APAFA
                </div>
                <span class="text-[10px] text-slate-500 block">Sello y Firma</span>
            </div>
            <div>
                <div class="border-t border-slate-400 w-4/5 mx-auto pt-2 font-bold uppercase">
                    Tesorero(a) APAFA
                </div>
                <span class="text-[10px] text-slate-500 block">Conformidad de Deducción</span>
            </div>
            <div>
                <div class="border-t border-slate-400 w-4/5 mx-auto pt-2 font-bold uppercase">
                    Consejo de Vigilancia
                </div>
                <span class="text-[10px] text-slate-500 block">Veedor y Fiscalización</span>
            </div>
        </div>

        <!-- Pie de Página Legal -->
        <div class="mt-8 pt-3 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400">
            <span>Generado digitalmente por SIS-APAFA conforme a D.S. N.º 007-2026-MINEDU</span>
            <span>Fecha y hora de emisión: {{ now()->format('d/m/Y H:i:s') }}</span>
        </div>
    </div>
</body>
</html>
