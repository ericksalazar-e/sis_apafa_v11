<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Declaración Jurada de Idoneidad - Candidato APAFA {{ $candidato->padreFamilia->dni }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 11px !important; margin: 0; padding: 8mm; }
            @page { size: A4 portrait; margin: 10mm; }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans p-4 sm:p-8">

    <!-- Barra de Acciones Superior (No Imprimible) -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="{{ route('elecciones.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-slate-700 rounded-xl shadow-sm border border-slate-200 hover:bg-slate-50 text-sm font-semibold">
            <i class="bi bi-arrow-left"></i> Volver a Elecciones
        </a>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-600 text-white rounded-xl shadow-md hover:bg-purple-700 text-sm font-bold transition-all">
                <i class="bi bi-printer-fill"></i> Imprimir Declaración Jurada Oficial
            </button>
        </div>
    </div>

    <!-- Hoja A4 Oficial de Declaración Jurada de Idoneidad -->
    <div class="max-w-3xl mx-auto bg-white p-8 sm:p-12 rounded-2xl shadow-sm border border-slate-200">
        <!-- Membrete Oficial -->
        <div class="border-b-2 border-slate-900 pb-5 mb-6 text-center">
            <span class="text-[11px] font-extrabold uppercase tracking-widest text-slate-500 block">REPÚBLICA DEL PERÚ • MINISTERIO DE EDUCACIÓN</span>
            <h1 class="text-xl font-black text-slate-900 uppercase leading-snug mt-1">
                {{ $empresa->nombre ?? 'I.E. EMBLEMÁTICA NERY GARCÍA ZÁRATE' }}
            </h1>
            <p class="text-xs font-bold text-purple-800 uppercase tracking-wider">
                COMITÉ ELECTORAL DE LA ASOCIACIÓN DE PADRES DE FAMILIA (APAFA)
            </p>
            <p class="text-[10px] text-slate-500 mt-0.5">
                Ley N.º 28628 • D.S. N.º 007-2026-MINEDU • DRELM / UGEL
            </p>
        </div>

        <!-- Título del Documento -->
        <div class="text-center my-6">
            <h2 class="text-base sm:text-lg font-black uppercase text-slate-900 tracking-wide underline underline-offset-8 decoration-purple-600">
                DECLARACIÓN JURADA DE IDONEIDAD Y CUMPLIMIENTO DE REQUISITOS PARA POSTULAR A CARGOS DE APAFA
            </h2>
            <p class="text-[11px] font-semibold text-slate-600 mt-2">
                Conforme al Artículo 45° de la Ley N.º 28628 y su Reglamento vigente
            </p>
        </div>

        <!-- Datos del Postulante -->
        @php $padre = $candidato->padreFamilia; @endphp
        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 mb-6 text-xs space-y-1.5">
            <div class="grid grid-cols-2 gap-2">
                <p><strong>CANDIDATO(A):</strong> {{ strtoupper($padre->apellidos) }}, {{ strtoupper($padre->nombres) }}</p>
                <p><strong>DNI / DOCUMENTO:</strong> <span class="font-mono font-bold">{{ $padre->dni }}</span></p>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <p><strong>FÓRMULA / LISTA:</strong> LISTA N° {{ $candidato->lista->numero_lista ?? '1' }} - {{ strtoupper($candidato->lista->nombre_lista ?? '') }}</p>
                <p><strong>CARGO AL QUE POSTULA:</strong> <span class="font-bold text-purple-900">{{ strtoupper($candidato->cargo_texto) }}</span></p>
            </div>
            <p><strong>ESTUDIANTE(S) VINCULADO(S):</strong>
                @if($padre && $padre->estudiantes)
                    @foreach($padre->estudiantes as $est)
                        <span class="inline-block bg-white px-2 py-0.5 rounded border border-slate-200 font-semibold mr-1">
                            {{ $est->nombres }} {{ $est->apellidos }} ({{ $est->nivel }} - {{ $est->grado }}° "{{ $est->seccion }}")
                        </span>
                    @endforeach
                @else
                    <span class="text-slate-400 italic">No registrado</span>
                @endif
            </p>
        </div>

        <!-- Declaración y Compromiso Legal Expreso -->
        <div class="text-slate-800 text-xs leading-relaxed space-y-4 text-justify">
            <p>
                Yo, <strong>{{ strtoupper($padre->apellidos) }}, {{ strtoupper($padre->nombres) }}</strong>, identificado(a) con DNI N° <strong>{{ $padre->dni }}</strong>, en mi condición de padre de familia / apoderado legal inscrito en el Padrón de la APAFA de la <strong>{{ $empresa->nombre ?? 'Institución Educativa' }}</strong>, al amparo del principio de presunción de veracidad establecido en el TUO de la Ley N° 27444 y el Artículo 45° de la Ley N° 28628:
            </p>

            <p class="font-bold text-slate-900 uppercase tracking-wide">
                DECLARO BAJO JURAMENTO LO SIGUIENTE:
            </p>

            <!-- Cuadro de Requisitos Verificados -->
            <div class="space-y-2 border border-slate-200 rounded-xl p-4 bg-white">
                <div class="flex items-start gap-2.5">
                    <i class="bi bi-check-square-fill text-purple-600 text-sm mt-0.5 flex-shrink-0"></i>
                    <div>
                        <strong class="text-slate-900">1. Condición de Asociado Activo:</strong>
                        <p class="text-slate-600 text-[11px]">Cuento con hijo(a) o pupilo legalmente matriculado en la institución educativa y me encuentro debidamente inscrito en el Padrón Electoral con corte al 31 de agosto.</p>
                    </div>
                </div>

                <div class="flex items-start gap-2.5">
                    <i class="bi bi-check-square-fill text-purple-600 text-sm mt-0.5 flex-shrink-0"></i>
                    <div>
                        <strong class="text-slate-900">2. Estar al Día en los Aportes:</strong>
                        <p class="text-slate-600 text-[11px]">Acredito estar al día en el cumplimiento de las cuotas o aportes ordinarios a la asociación (o compensación acreditada por faenas de mantenimiento comunal según D.S. N.º 007-2026-MINEDU).</p>
                    </div>
                </div>

                <div class="flex items-start gap-2.5">
                    <i class="bi bi-check-square-fill text-purple-600 text-sm mt-0.5 flex-shrink-0"></i>
                    <div>
                        <strong class="text-slate-900">3. Antecedentes Limpios:</strong>
                        <p class="text-slate-600 text-[11px]">No cuento con antecedentes penales, judiciales ni policiales por delitos comunes o contra la libertad sexual y el patrimonio.</p>
                    </div>
                </div>

                <div class="flex items-start gap-2.5">
                    <i class="bi bi-check-square-fill text-purple-600 text-sm mt-0.5 flex-shrink-0"></i>
                    <div>
                        <strong class="text-slate-900">4. No Laborar en la Institución Educativa:</strong>
                        <p class="text-slate-600 text-[11px]">No mantengo relación laboral, contrato de servicios ni ejerzo docencia o cargo administrativo en la misma institución educativa.</p>
                    </div>
                </div>

                <div class="flex items-start gap-2.5">
                    <i class="bi bi-check-square-fill text-purple-600 text-sm mt-0.5 flex-shrink-0"></i>
                    <div>
                        <strong class="text-slate-900">5. Grado del Estudiante (Incompatibilidad por último grado):</strong>
                        <p class="text-slate-600 text-[11px]">Mi hijo(a) o pupilo no se encuentra cursando el último grado de estudios que brinda el colegio (o me encuentro amparado por la excepción de Nivel Inicial o por contar con otro menor en grados inferiores), garantizando la continuidad de la gestión.</p>
                    </div>
                </div>

                <div class="flex items-start gap-2.5">
                    <i class="bi bi-check-square-fill text-purple-600 text-sm mt-0.5 flex-shrink-0"></i>
                    <div>
                        <strong class="text-slate-900">6. No haber sido Objeto de Remoción o Censura:</strong>
                        <p class="text-slate-600 text-[11px]">No he sido objeto de remoción, vacancia o sanción disciplinaria en cargos directivos previos de la APAFA.</p>
                    </div>
                </div>
            </div>

            <p class="text-[11px] text-slate-500">
                En caso de comprobarse falsedad en la presente declaración, me someto a las acciones legales penales pertinentes por falsedad genérica (Art. 411° del Código Penal) y a la tacha inmediata de mi candidatura o vacancia de pleno derecho del cargo asignado.
            </p>
        </div>

        <!-- Firmas, Huella Dactilar y Validación del Comité Electoral -->
        <div class="grid grid-cols-2 gap-8 pt-10 mt-8 border-t border-slate-200">
            <!-- Firma y Huella del Candidato -->
            <div class="flex items-center gap-4">
                <div class="w-20 h-24 border-2 border-slate-400 rounded-lg flex flex-col items-center justify-center text-center p-1 bg-slate-50">
                    <i class="bi bi-fingerprint text-3xl text-slate-300"></i>
                    <span class="text-[9px] text-slate-400 uppercase font-bold mt-1">Índice Derecho</span>
                </div>
                <div class="flex-1 text-center">
                    <div class="border-b border-slate-900 h-12 mb-1"></div>
                    <span class="font-bold text-xs block text-slate-900 uppercase">{{ $padre->nombre_completo }}</span>
                    <span class="text-[10px] text-slate-500 block font-mono">DNI: {{ $padre->dni }}</span>
                    <span class="text-[9px] text-purple-800 font-bold uppercase block">Firma del Candidato(a) Postulante</span>
                </div>
            </div>

            <!-- Visto Bueno del Comité Electoral -->
            <div class="text-center flex flex-col justify-end">
                <div class="border-b border-slate-900 h-12 mb-1"></div>
                <span class="font-bold text-xs block text-slate-900 uppercase">Comité Electoral de APAFA</span>
                <span class="text-[10px] text-slate-500 block">Visto Bueno y Acreditación de Elegibilidad</span>
                <span class="text-[9px] text-emerald-700 font-bold uppercase block">Verificado Conforme a Ley 28628</span>
            </div>
        </div>

        <!-- Pie de Página -->
        <div class="mt-8 pt-3 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400">
            <span>SIS-APAFA • Ficha de Idoneidad y Declaración Jurada Digital</span>
            <span>Fecha y hora de emisión: {{ now()->format('d/m/Y H:i:s') }}</span>
        </div>
    </div>
</body>
</html>
