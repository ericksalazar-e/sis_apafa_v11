<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acta Oficial de Proclamación del Consejo Directivo APAFA {{ $comicio->año_electoral }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 11px !important; margin: 0; padding: 10mm; }
            @page { size: A4 portrait; margin: 10mm; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans p-4 sm:p-8">

    <!-- Barra de Acciones (No Imprimible) -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="{{ route('elecciones.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-slate-700 rounded-xl shadow-sm border border-slate-200 hover:bg-slate-50 text-sm font-semibold">
            <i class="bi bi-arrow-left"></i> Volver a Elecciones
        </a>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-600 text-white rounded-xl shadow-md hover:bg-purple-700 text-sm font-bold transition-all">
                <i class="bi bi-printer-fill"></i> Imprimir Acta de Proclamación
            </button>
        </div>
    </div>

    <!-- Hoja A4 Oficial de Proclamación -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-2xl shadow-sm border border-slate-200">
        <!-- Membrete Oficial -->
        <div class="border-b-2 border-slate-900 pb-5 mb-6 text-center">
            <span class="text-[11px] font-extrabold uppercase tracking-widest text-slate-500 block">REPÚBLICA DEL PERÚ • MINISTERIO DE EDUCACIÓN</span>
            <h1 class="text-xl font-black text-slate-900 uppercase leading-snug mt-1">
                {{ $empresa->nombre ?? 'I.E. EMBLEMÁTICA NERY GARCÍA ZÁRATE' }}
            </h1>
            <p class="text-xs font-bold text-purple-800 uppercase tracking-wider">
                COMITÉ ELECTORAL DE LA ASOCIACIÓN DE PADRES DE FAMILIA (APAFA)
            </p>
            <p class="text-[11px] text-slate-500 mt-0.5">
                Ley N.º 28628 • D.S. N.º 007-2026-MINEDU (Reglamento de APAFA)
            </p>
        </div>

        <!-- Título del Acta -->
        <div class="text-center my-6">
            <h2 class="text-lg font-black uppercase text-slate-900 tracking-wide underline underline-offset-8 decoration-purple-600">
                ACTA OFICIAL DE PROCLAMACIÓN Y JURAMENTACIÓN DE LA LISTA GANADORA
            </h2>
            <p class="text-xs font-bold text-slate-600 mt-2">
                PERÍODO DE GESTIÓN: {{ $comicio->año_electoral }} - {{ $comicio->año_electoral + 2 }}
            </p>
        </div>

        <!-- Cuerpo del Acta -->
        <div class="text-slate-800 text-xs leading-relaxed space-y-4 text-justify">
            <p>
                En las instalaciones de la Institución Educativa <strong>{{ $empresa->nombre ?? 'I.E. Emblemática Nery García Zárate' }}</strong>, siendo las horas señaladas del día <strong>{{ \Carbon\Carbon::parse($comicio->fecha_elecciones)->isoFormat('LL') }}</strong>, el <strong>Comité Electoral</strong>, en estricto cumplimiento de la <strong>Ley N° 28628</strong> y el <strong>Decreto Supremo N.º 007-2026-MINEDU</strong> que regula el cronograma anticipado y garantiza la participación de las familias, procede a dar lectura formal a los resultados del escrutinio general y a <strong>PROCLAMAR</strong> como ganadora a la fórmula electoral que obtuvo la mayoría de votos válidos:
            </p>

            <!-- Cuadro de la Lista Ganadora -->
            <div class="bg-purple-50 p-4 rounded-xl border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-purple-700 uppercase tracking-wider">LISTA ELECTORAL GANADORA</span>
                        <h3 class="text-base font-black text-purple-950 uppercase">
                            LISTA N° {{ $listaGanadora->numero_lista ?? '1' }} - {{ $listaGanadora->nombre_lista ?? 'LISTA ELECTA' }}
                        </h3>
                        @if(!empty($listaGanadora->eslogan))
                            <p class="text-xs text-purple-800 italic mt-0.5">"{{ $listaGanadora->eslogan }}"</p>
                        @endif
                    </div>
                    <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-lg font-extrabold text-xs border border-emerald-300">
                        ELECTA CON MAYORÍA
                    </span>
                </div>
            </div>

            <p>
                Habiéndose verificado que cada uno de los candidatos cumple con los requisitos reglamentarios de idoneidad (Ley N° 28628 Art. 45), se proclama formalmente a los siguientes integrantes para asumir la conducción del <strong>Consejo Directivo (Órgano de Gestión y Administración)</strong> y el <strong>Consejo de Vigilancia (Órgano de Control y Fiscalización)</strong> de la APAFA:
            </p>

            <!-- Nómina de Candidatos Electos -->
            <div class="border border-slate-200 rounded-xl overflow-hidden my-4">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-700 uppercase font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">Cargo Institucional Asumido</th>
                            <th class="py-2.5 px-3">Apellidos y Nombres</th>
                            <th class="py-2.5 px-3">DNI</th>
                            <th class="py-2.5 px-3">Estudiante(s) Vinculado(s)</th>
                            <th class="py-2.5 px-3 text-center">Condición Legal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @if($listaGanadora && $listaGanadora->candidatos)
                            @foreach($listaGanadora->candidatos as $c)
                            <tr>
                                <td class="py-2.5 px-3 font-bold text-purple-900">{{ $c->cargo_texto }}</td>
                                <td class="py-2.5 px-3 font-semibold text-slate-900">{{ $c->padreFamilia->apellidos ?? 'N/A' }}, {{ $c->padreFamilia->nombres ?? '' }}</td>
                                <td class="py-2.5 px-3 font-mono">{{ $c->padreFamilia->dni ?? 'N/A' }}</td>
                                <td class="py-2.5 px-3 text-slate-600">
                                    @if($c->padreFamilia && $c->padreFamilia->estudiantes)
                                        @foreach($c->padreFamilia->estudiantes as $est)
                                            <span class="inline-block bg-slate-100 px-1.5 py-0.5 rounded text-[10px] mr-1">
                                                {{ $est->nombres }} ({{ $est->grado }}° {{ $est->seccion }})
                                            </span>
                                        @endforeach
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded text-[10px] font-bold">
                                        Proclamado y Habilitado
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400 italic">No hay candidatos asociados a esta lista.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <p>
                Los directivos proclamados asumen sus funciones con plenitud de derechos y obligaciones, quedando facultados para gestionar las cuentas institucionales, convocar asambleas y administrar los aportes y quioscos escolares en estricto beneficio de los estudiantes de la institución.
            </p>

            <p class="text-[11px] text-slate-500">
                Se expide la presente Acta de Proclamación en tres (03) ejemplares originales: uno para el archivo del Comité Electoral, uno para la Dirección de la I.E. y uno para su remisión a la Unidad de Gestión Educativa Local (UGEL) correspondiente para su registro y reconocimiento formal.
            </p>
        </div>

        <!-- Firmas Oficiales -->
        <div class="grid grid-cols-3 gap-6 pt-12 mt-8 border-t border-slate-200 text-center text-xs text-slate-700">
            <div>
                <div class="border-t border-slate-400 w-4/5 mx-auto pt-2 font-bold uppercase">
                    Presidente del Comité Electoral
                </div>
                <span class="text-[10px] text-slate-500 block">Firma y Sello</span>
            </div>
            <div>
                <div class="border-t border-slate-400 w-4/5 mx-auto pt-2 font-bold uppercase">
                    Secretario del Comité Electoral
                </div>
                <span class="text-[10px] text-slate-500 block">Firma y Sello</span>
            </div>
            <div>
                <div class="border-t border-slate-400 w-4/5 mx-auto pt-2 font-bold uppercase">
                    Veedor / Director I.E.
                </div>
                <span class="text-[10px] text-slate-500 block">Conformidad Institucional</span>
            </div>
        </div>

        <!-- Pie de Página -->
        <div class="mt-8 pt-3 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400">
            <span>Generado digitalmente por SIS-APAFA conforme a D.S. N.º 007-2026-MINEDU</span>
            <span>Fecha de emisión: {{ now()->format('d/m/Y H:i:s') }}</span>
        </div>
    </div>
</body>
</html>
