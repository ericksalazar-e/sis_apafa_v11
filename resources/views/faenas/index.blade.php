@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header con Leyenda D.S. 007-2026-MINEDU -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300">
                    <i class="bi bi-shield-check mr-1"></i> D.S. N.º 007-2026-MINEDU Art. 78
                </span>
                <span class="text-xs text-slate-500 font-medium">Compensación por Trabajo y Servicios Comunales</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <i class="bi bi-tools text-emerald-600"></i> Faenas Escolares y Servicios Comunales
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Alternativa legal obligatoria a la cuota ordinaria de APAFA mediante trabajos comunitarios de mantenimiento, pintado y mejoras en la I.E.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button onclick="document.getElementById('modalNuevaFaena').classList.remove('hidden')" class="btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold shadow-sm text-sm">
                <i class="bi bi-plus-circle"></i> Convocar Nueva Faena
            </button>
        </div>
    </div>

    <!-- Alerta Preventiva de Seguridad y Prohibición de Labores Peligrosas (Ley N° 29783 / MINEDU) -->
    <div class="bg-gradient-to-r from-red-50 to-amber-50 border-l-4 border-red-500 p-5 rounded-2xl shadow-sm text-slate-800">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-xl font-bold flex-shrink-0 mt-0.5">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="space-y-2 flex-1">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h3 class="font-extrabold text-red-900 text-sm uppercase tracking-wide flex items-center gap-2">
                        <span>⚠️ PROTOCOLO ESTRICTO DE SEGURIDAD: PROHIBICIÓN DE TRABAJOS DE ALTO RIESGO</span>
                    </h3>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-red-200 text-red-900">
                        Prevención de Accidentes Fatales (Ley N° 29783)
                    </span>
                </div>
                <p class="text-xs text-slate-700 leading-relaxed">
                    A raíz de lamentables tragedias ocurridas en instituciones educativas del país (como el colapso de torres o muros), <strong>está TERMINANTEMENTE PROHIBIDO</strong> que en las faenas comunales o de APAFA se encomiende labores de alto riesgo que comprometan la vida de las personas:
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5 text-xs pt-1">
                    <div class="flex items-start gap-2 bg-white/90 p-3 rounded-xl border border-red-200 shadow-xs">
                        <i class="bi bi-x-octagon-fill text-red-600 mt-0.5 flex-shrink-0 text-base"></i>
                        <div>
                            <strong class="text-red-900 block font-bold">PROHIBIDO Demolición o Derribo de Estructuras</strong>
                            <span class="text-slate-600 text-[11px] leading-tight block mt-0.5">Nunca tumbar muros, torres de agua, postes, columnas ni tinglados. Las demoliciones corresponden a personal técnico de PRONIED/UGEL con maquinaria especializada.</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-2 bg-white/90 p-3 rounded-xl border border-red-200 shadow-xs">
                        <i class="bi bi-x-octagon-fill text-red-600 mt-0.5 flex-shrink-0 text-base"></i>
                        <div>
                            <strong class="text-red-900 block font-bold">PROHIBIDO la Participación de Menores de Edad</strong>
                            <span class="text-slate-600 text-[11px] leading-tight block mt-0.5">Bajo ninguna circunstancia los escolares o niños pueden realizar faenas, cargar pesos o estar en áreas de trabajo. Las faenas son exclusivas de adultos.</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-2 bg-white/90 p-3 rounded-xl border border-red-200 shadow-xs">
                        <i class="bi bi-x-octagon-fill text-red-600 mt-0.5 flex-shrink-0 text-base"></i>
                        <div>
                            <strong class="text-red-900 block font-bold">PROHIBIDO Trabajos en Altura sin Arneses</strong>
                            <span class="text-slate-600 text-[11px] leading-tight block mt-0.5">Prohibido subir a techos resbaladizos, tanques elevados, andamios improvisados o copas de árboles por riesgo de caída mortal.</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-2 bg-white/90 p-3 rounded-xl border border-red-200 shadow-xs">
                        <i class="bi bi-x-octagon-fill text-red-600 mt-0.5 flex-shrink-0 text-base"></i>
                        <div>
                            <strong class="text-red-900 block font-bold">PROHIBIDO Electricidad y Excavaciones Profundas</strong>
                            <span class="text-slate-600 text-[11px] leading-tight block mt-0.5">Manipular cables de media/alta tensión, tableros o cavar pozos ciegos sin entibado expone a riesgos letales de electrocución o asfixia.</span>
                        </div>
                    </div>
                </div>
                <div class="text-[11px] text-emerald-800 font-semibold bg-emerald-50 px-3 py-2 rounded-xl border border-emerald-200 flex items-center gap-2 mt-1">
                    <i class="bi bi-check-circle-fill text-emerald-600 text-sm"></i>
                    <span><strong>Actividades Únicamente Autorizadas en APAFA:</strong> Mantenimiento menor preventivo a nivel de suelo (pintado de paredes de aulas, limpieza general, jardinería, lijado y barnizado de carpetas).</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas del impacto económico social -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-cash-coin"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Monto Compensado / Ahorrado</p>
                <h3 class="text-2xl font-extrabold text-slate-800">S/ {{ number_format($totalCompensado, 2) }}</h3>
                <p class="text-xs text-emerald-600 font-medium mt-0.5">En beneficio de la economía familiar</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-people"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Familias Participantes</p>
                <h3 class="text-2xl font-extrabold text-slate-800">{{ $totalAsistencias }}</h3>
                <p class="text-xs text-blue-600 font-medium mt-0.5">Asistencias a faenas registradas</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Convocatorias Realizadas</p>
                <h3 class="text-2xl font-extrabold text-slate-800">{{ $totalFaenas }}</h3>
                <p class="text-xs text-amber-600 font-medium mt-0.5">Jornadas de mejora comunitaria</p>
            </div>
        </div>
    </div>

    <!-- Listado de Faenas -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 flex items-center gap-2">
                <i class="bi bi-list-check text-slate-400"></i> Historial de Jornadas y Faenas de la I.E.
            </h2>
            <span class="text-xs text-slate-400">Conforme a Ley 28628 y D.S. N.º 007-2026-MINEDU</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4">Jornada / Faena</th>
                        <th class="px-6 py-4">Fecha & Lugar</th>
                        <th class="px-6 py-4">Compensación</th>
                        <th class="px-6 py-4">Asistentes / Cupo</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($faenas as $faena)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-bold text-slate-800">{{ $faena->titulo }}</span>
                            @if($faena->descripcion)
                            <p class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ $faena->descripcion }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-700 flex items-center gap-1.5">
                                <i class="bi bi-calendar3 text-emerald-600"></i>
                                {{ \Carbon\Carbon::parse($faena->fecha_faena)->isoFormat('LL') }}
                            </div>
                            <span class="text-xs text-slate-500 flex items-center gap-1 mt-0.5">
                                <i class="bi bi-geo-alt"></i> {{ $faena->lugar }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                S/ {{ number_format($faena->valor_compensacion, 2) }}
                            </span>
                            <span class="text-[11px] text-slate-400 block mt-0.5">Descuento de Cuota APAFA</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-800">{{ $faena->asistencias_count }}</span>
                                <span class="text-xs text-slate-400">/ {{ $faena->cupo_maximo }} padres</span>
                            </div>
                            <div class="w-24 bg-slate-100 rounded-full h-1.5 mt-1 overflow-hidden">
                                @php $pct = min(100, ($faena->asistencias_count / max(1, $faena->cupo_maximo)) * 100); @endphp
                                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($faena->estado === 'PROGRAMADA')
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">Programada</span>
                            @elseif($faena->estado === 'EN_CURSO')
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">En Curso</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">Concluida</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('faenas.show', $faena->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-700 rounded-lg text-xs font-semibold transition-all">
                                <i class="bi bi-qr-code-scan"></i> Asistencia & QR
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <i class="bi bi-tools text-4xl block mb-2 text-slate-300"></i>
                            No hay faenas o jornadas registradas aún.
                            <p class="text-xs text-slate-400 mt-1">Convoque una nueva jornada comunitaria para habilitar la compensación de cuotas a los padres de familia.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($faenas->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $faenas->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Convocar Nueva Faena -->
<div id="modalNuevaFaena" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div>
                <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                    <i class="bi bi-calendar-plus text-emerald-600"></i> Convocar Faena Escolar
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Conforme a facilidades del D.S. N.º 007-2026-MINEDU</p>
            </div>
            <button onclick="document.getElementById('modalNuevaFaena').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">
                &times;
            </button>
        </div>

        <form action="{{ route('faenas.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Título de la Jornada / Faena *</label>
                <input type="text" name="titulo" required placeholder="Ej: I Faena de Pintado de Aulas y Mantenimiento de Áreas Verdes" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Fecha de la Faena *</label>
                    <input type="date" name="fecha_faena" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Lugar / Pabellón *</label>
                    <input type="text" name="lugar" required value="Local Central I.E." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Valor de Compensación (S/) *</label>
                    <input type="number" step="0.50" name="valor_compensacion" required value="40.00" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <span class="text-[11px] text-slate-400">Monto descontado de la cuota anual</span>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Cupo Máximo (Padres) *</label>
                    <input type="number" name="cupo_maximo" required value="60" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Descripción de las Actividades / Materiales</label>
                <textarea name="descripcion" rows="2" placeholder="Traer brochas, rodillos, lijas o herramientas de jardinería..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
            </div>

            <!-- Protocolo de Seguridad Obligatorio para Prevenir Accidentes Fatales -->
            <div class="bg-red-50 p-4 rounded-xl border border-red-200 space-y-2">
                <div class="flex items-center gap-2 text-xs font-bold text-red-900">
                    <i class="bi bi-shield-fill-x text-red-600"></i>
                    <span>COMPROMISO OBLIGATORIO DE SEGURIDAD (LEY N° 29783)</span>
                </div>
                <p class="text-[11px] text-red-800 leading-tight">
                    Para prevenir accidentes mortales (como caídas o colapso de torres/muros), <strong>está terminantemente prohibido</strong> incluir labores de demolición, derribos de estructuras, alturas sin arnés o manipulación de alta tensión. <strong>Queda estrictamente prohibida la participación de escolares.</strong>
                </p>
                <label class="flex items-start gap-2 pt-1 cursor-pointer">
                    <input type="checkbox" name="declaracion_seguridad" required value="1" class="mt-0.5 rounded text-red-600 focus:ring-red-500">
                    <span class="text-[11px] font-semibold text-slate-800">
                        Declaro bajo responsabilidad que esta jornada contempla exclusivamente labores seguras de mantenimiento menor a nivel de piso y no pondrá en riesgo la vida de nadie.
                    </span>
                </label>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modalNuevaFaena').classList.add('hidden')" class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-all">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md transition-all">
                    Guardar y Publicar Convocatoria
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
