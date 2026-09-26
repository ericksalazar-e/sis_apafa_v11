@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header de la Faena -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('faenas.index') }}" class="text-xs font-semibold text-emerald-600 hover:underline">
                    <i class="bi bi-arrow-left"></i> Volver a Faenas
                </a>
                <span class="text-slate-300">•</span>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Control de Asistencia & Compensación</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">{{ $faena->titulo }}</h1>
            <p class="text-sm text-slate-500 mt-1 flex flex-wrap items-center gap-4">
                <span><i class="bi bi-calendar3 text-emerald-600"></i> {{ \Carbon\Carbon::parse($faena->fecha_faena)->isoFormat('LL') }}</span>
                <span><i class="bi bi-geo-alt text-emerald-600"></i> {{ $faena->lugar }}</span>
                <span><i class="bi bi-cash-stack text-emerald-600"></i> Compensación: <strong>S/ {{ number_format($faena->valor_compensacion, 2) }}</strong></span>
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('faenas.imprimir_padron', $faena->id) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold shadow-sm transition-all">
                <i class="bi bi-printer-fill text-emerald-400"></i> Imprimir Padrón de Asistentes
            </a>
            <span class="px-3.5 py-1.5 rounded-full text-xs font-bold {{ $faena->estado === 'CONCLUIDA' ? 'bg-slate-100 text-slate-700' : 'bg-emerald-100 text-emerald-800 border border-emerald-300' }}">
                <i class="bi bi-check-circle-fill mr-1"></i> {{ $faena->estado }}
            </span>
        </div>
    </div>

    <!-- Panel de Escaneo Rápido de Carné / DNI y Búsqueda -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna Izquierda: Formulario de Registro Rápido con Escáner -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <h3 class="font-bold text-slate-800 flex items-center gap-2 text-base">
                <i class="bi bi-qr-code-scan text-emerald-600"></i> Acreditar Padre / Escanear Carné
            </h3>
            <p class="text-xs text-slate-500">
                Pase el código QR del Carné de Padre o ingrese el DNI para registrar su presencia y deducir automáticamente su cuota ordinaria de APAFA.
            </p>

            <!-- Advertencia de Prevención y Seguridad Vital (Ley 29783) -->
            <div class="bg-red-50 p-3 rounded-xl border border-red-200 text-xs text-red-900 space-y-1">
                <div class="font-bold flex items-center gap-1.5 text-red-800 text-[11px]">
                    <i class="bi bi-exclamation-triangle-fill text-red-600"></i>
                    <span>PROTOCOLO DE SEGURIDAD (PREVENCIÓN DE MUERTES):</span>
                </div>
                <p class="text-[11px] text-slate-700 leading-tight">
                    Prohibido asignar labores peligrosas como demolición o derribo de torres/estructuras, trabajos en alturas sin arnés o electricidad. <strong>Los escolares menores de edad tienen terminantemente prohibida su participación física.</strong>
                </p>
            </div>

            <form action="{{ route('faenas.asistencia', $faena->id) }}" method="POST" id="formAsistenciaFaena" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Escanear QR o DNI de Padre</label>
                    <div class="relative">
                        <input type="text" name="dni_o_qr" id="inputScanFaena" autofocus placeholder="Enfoque aquí o tipee DNI..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <i class="bi bi-upc-scan absolute left-3.5 top-3 text-slate-400"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">O Seleccionar de la Lista</label>
                    <select name="padre_familia_id" id="selectPadreFaena" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">-- Buscar por Apellidos y Nombres --</option>
                        @foreach($padres as $p)
                        <option value="{{ $p->id }}">{{ $p->apellidos }}, {{ $p->nombres }} (DNI: {{ $p->dni }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Horas Aportadas</label>
                        <input type="number" step="0.5" name="horas_trabajadas" value="4.0" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm text-center font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Compensación (S/)</label>
                        <input type="number" step="0.5" name="monto_compensado" value="{{ $faena->valor_compensacion }}" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm text-center font-bold text-emerald-700 bg-emerald-50">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Observaciones / Tarea Asignada</label>
                    <input type="text" name="observaciones" placeholder="Ej: Mantenimiento y pintado de 2 aulas..." class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                </div>

                <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm shadow-md transition-all flex items-center justify-center gap-2">
                    <i class="bi bi-check-circle"></i> Registrar Asistencia y Compensar
                </button>
            </form>
        </div>

        <!-- Columna Derecha: Lista de Asistentes Acreditados y Constancias -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="bi bi-people-fill text-emerald-600"></i> Padres Acreditados en la Faena
                    </h3>
                    <p class="text-xs text-slate-500">Relación de padres presentes con compensación efectiva de cuota ordinaria</p>
                </div>
                <div class="flex items-center gap-2">
                    <span id="badgeContador" class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full font-bold text-xs border border-emerald-200">
                        {{ $faena->asistencias->count() }} Registrados
                    </span>
                    <a href="{{ route('faenas.imprimir_padron', $faena->id) }}" target="_blank" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-full font-bold text-xs inline-flex items-center gap-1 transition-all">
                        <i class="bi bi-printer"></i> Imprimir
                    </a>
                </div>
            </div>

            <!-- Buscador en tiempo real de asistentes -->
            <div class="p-3 bg-slate-50 border-b border-slate-100">
                <div class="relative">
                    <input type="text" id="buscadorAsistentes" placeholder="Buscar en la lista por DNI, apellidos o nombres..." class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-white">
                    <i class="bi bi-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                </div>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-sm text-slate-600" id="tablaAsistentes">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3.5">Padre / Tutor Acreditado</th>
                            <th class="px-5 py-3.5">DNI</th>
                            <th class="px-5 py-3.5 text-center">Horas</th>
                            <th class="px-5 py-3.5">Monto Deducido</th>
                            <th class="px-5 py-3.5 text-right">Constancia</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="tbodyAsistentes">
                        @forelse($faena->asistencias as $asistencia)
                        <tr class="hover:bg-slate-50 transition-colors fila-asistente">
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-slate-800 block dato-nombre">{{ $asistencia->padreFamilia->apellidos ?? 'N/A' }}, {{ $asistencia->padreFamilia->nombres ?? '' }}</span>
                                @if($asistencia->padreFamilia && $asistencia->padreFamilia->estudiantes && $asistencia->padreFamilia->estudiantes->count() > 0)
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        <i class="bi bi-person-badge text-slate-400"></i> Hijos:
                                        @foreach($asistencia->padreFamilia->estudiantes as $est)
                                            <span class="inline-block bg-slate-100 text-slate-600 px-1.5 py-0.2 rounded text-[10px] mr-1">{{ $est->nombres }} ({{ $est->grado }}° {{ $est->seccion }})</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if($asistencia->observaciones)
                                <p class="text-[11px] text-slate-400 mt-0.5"><i class="bi bi-info-circle"></i> {{ $asistencia->observaciones }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-slate-600 font-bold dato-dni">
                                {{ $asistencia->padreFamilia->dni ?? 'N/A' }}
                            </td>
                            <td class="px-5 py-3.5 text-center font-bold text-slate-700">
                                {{ $asistencia->horas_trabajadas }} hrs
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-md text-xs font-extrabold bg-emerald-100 text-emerald-800">
                                    S/ {{ number_format($asistencia->monto_compensado, 2) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('faenas.constancia', [$faena->id, $asistencia->padre_familia_id]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-800 hover:text-white text-slate-700 rounded-lg text-xs font-bold transition-all shadow-sm">
                                    <i class="bi bi-file-earmark-pdf"></i> Constancia
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr id="sinAsistentes">
                            <td colspan="5" class="px-5 py-12 text-center text-slate-400">
                                <i class="bi bi-person-x text-3xl block mb-2 text-slate-300"></i>
                                Aún no se han registrado asistencias a esta faena.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputBuscador = document.getElementById('buscadorAsistentes');
    if (inputBuscador) {
        inputBuscador.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const filas = document.querySelectorAll('.fila-asistente');
            let visibles = 0;

            filas.forEach(fila => {
                const nombre = fila.querySelector('.dato-nombre')?.textContent.toLowerCase() || '';
                const dni = fila.querySelector('.dato-dni')?.textContent.toLowerCase() || '';
                if (nombre.includes(query) || dni.includes(query)) {
                    fila.style.display = '';
                    visibles++;
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    }
});
</script>
@endsection
