<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cédula Oficial de Sufragio - Elecciones APAFA {{ $comicio->año_electoral }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 11px !important; margin: 0; padding: 5mm; }
            @page { size: A4 portrait; margin: 8mm; }
            .cedula-card { page-break-inside: avoid; border: 2px solid #0f172a !important; }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans p-4 sm:p-8">

    <!-- Barra de Acciones Superior (No Imprimible) -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="{{ route('elecciones.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-slate-700 rounded-xl shadow-sm border border-slate-200 hover:bg-slate-50 text-sm font-semibold">
            <i class="bi bi-arrow-left"></i> Volver a Comicios
        </a>
        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-500 font-medium">Cada hoja A4 contiene el formato oficial de balota electoral</span>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-600 text-white rounded-xl shadow-md hover:bg-purple-700 text-sm font-bold transition-all">
                <i class="bi bi-printer-fill"></i> Imprimir Cédulas de Sufragio
            </button>
        </div>
    </div>

    <!-- Contenedor de Cédulas Oficiales -->
    <div class="max-w-3xl mx-auto space-y-8">
        @for($i = 1; $i <= 2; $i++)
        <!-- Cédula de Sufragio Individual (Se imprimen 2 por hoja A4 para optimizar papel institucional) -->
        <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border-2 border-slate-900 cedula-card relative">
            <!-- Encabezado Electoral -->
            <div class="border-b-2 border-slate-900 pb-4 mb-4 text-center">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">COMITÉ ELECTORAL APAFA</span>
                    <span class="text-[10px] font-black text-purple-700 uppercase px-2 py-0.5 bg-purple-50 rounded border border-purple-200">
                        ELECCIONES GENERALES {{ $comicio->año_electoral }}
                    </span>
                    <span class="text-[10px] font-mono text-slate-400">SERIE: BALOTA-{{ $comicio->año_electoral }}</span>
                </div>
                <h1 class="text-base sm:text-lg font-black text-slate-900 uppercase leading-snug">
                    {{ $empresa->nombre ?? 'I.E. EMBLEMÁTICA NERY GARCÍA ZÁRATE' }}
                </h1>
                <p class="text-xs font-bold text-slate-700 uppercase tracking-wide">
                    CÉDULA OFICIAL DE SUFRAGIO • JUNTA DIRECTIVA Y CONSEJO DE VIGILANCIA
                </p>
                <p class="text-[10px] text-slate-500">
                    Ley N.º 28628 • D.S. N.º 007-2026-MINEDU • Sufragio universal, directo y secreto
                </p>
            </div>

            <!-- Instrucción para el Elector -->
            <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-300 text-center mb-5 text-[11px] text-slate-800">
                <strong>INSTRUCCIÓN:</strong> Marque con una cruz (<strong>+</strong>) o aspa (<strong>X</strong>) dentro del recuadro de la lista de su preferencia.
            </div>

            <!-- Lista de Opciones Electorales -->
            <div class="space-y-3">
                @forelse($comicio->listas as $lista)
                <div class="flex items-center justify-between border-2 border-slate-800 rounded-xl p-3 bg-white">
                    <div class="flex items-center gap-4 flex-1">
                        <!-- Número de Lista -->
                        <div class="w-12 h-12 bg-slate-900 text-white rounded-lg flex flex-col items-center justify-center font-black">
                            <span class="text-[9px] uppercase tracking-tighter leading-none text-slate-300">LISTA</span>
                            <span class="text-xl leading-none mt-0.5">{{ $lista->numero_lista }}</span>
                        </div>

                        <!-- Detalles de la Lista y Candidato Presidencial -->
                        <div>
                            <h3 class="font-black text-slate-900 text-sm uppercase">{{ $lista->nombre_lista }}</h3>
                            @if($lista->eslogan)
                                <p class="text-[10px] text-slate-500 italic">"{{ $lista->eslogan }}"</p>
                            @endif
                            <div class="text-[11px] text-slate-700 mt-0.5">
                                @if($lista->presidente)
                                    <strong>Candidato(a) a Presidente:</strong> {{ $lista->presidente->padreFamilia->apellidos ?? '' }}, {{ $lista->presidente->padreFamilia->nombres ?? '' }}
                                @else
                                    <span class="text-slate-400 italic">Candidatura registrada</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Casillero para la Marca del Elector -->
                    <div class="w-14 h-14 border-2 border-slate-900 rounded-lg flex items-center justify-center bg-slate-50 flex-shrink-0 ml-4">
                        <span class="text-slate-300 font-light text-2xl select-none">+ / x</span>
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-slate-400 border border-dashed border-slate-300 rounded-xl">
                    No se han registrado listas electorales aún. Inscríbalas en el sistema para generar la cédula.
                </div>
                @endforelse

                <!-- Voto Blanco / Viciados opcional en Acta -->
            </div>

            <!-- Pie de Cédula con Firma de Miembros de Mesa -->
            <div class="mt-6 pt-4 border-t border-slate-200 grid grid-cols-2 gap-4 text-center text-[10px] text-slate-500">
                <div>
                    <div class="border-b border-dotted border-slate-400 w-3/4 mx-auto h-4 mb-1"></div>
                    <span class="font-bold uppercase">Presidente de Mesa</span>
                </div>
                <div>
                    <div class="border-b border-dotted border-slate-400 w-3/4 mx-auto h-4 mb-1"></div>
                    <span class="font-bold uppercase">Secretario de Mesa</span>
                </div>
            </div>
        </div>

        @if($i == 1)
        <!-- Línea divisoria de corte -->
        <div class="border-t-2 border-dashed border-slate-400 my-4 text-center relative no-print">
            <span class="bg-slate-100 px-3 text-[10px] text-slate-500 uppercase font-mono relative -top-2.5">
                <i class="bi bi-scissors"></i> Línea de corte entre papeletas
            </span>
        </div>
        @endif
        @endfor
    </div>
</body>
</html>
