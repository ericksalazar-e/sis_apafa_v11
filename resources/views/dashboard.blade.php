@extends('layouts.app')

@section('title', 'Dashboard de Gestión y Métricas')

@section('content')
<div class="space-y-6">

    <!-- KPI Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <x-stat-card 
            title="Padres Afiliados" 
            :value="number_format($totalPadres)" 
            icon="fa-solid fa-users" 
            color="indigo" 
            subtitle="100% en esta institución" 
        />
        <x-stat-card 
            title="Alumnos Matriculados" 
            :value="number_format($totalEstudiantes)" 
            icon="fa-solid fa-user-graduate" 
            color="purple" 
            subtitle="Hijos de asociados" 
        />
        <x-stat-card 
            title="Recaudación Total" 
            :value="'S/ ' . number_format($totalRecaudado, 2)" 
            icon="fa-solid fa-sack-dollar" 
            color="emerald" 
            subtitle="Ingresos ingresados a caja" 
        />
        <x-stat-card 
            title="Deuda por Cobrar" 
            :value="'S/ ' . number_format($deudaTotal, 2)" 
            icon="fa-solid fa-clock-rotate-left" 
            color="rose" 
            subtitle="Pendiente de regularización" 
        />
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Line/Bar Chart (Recaudación) -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="text-base font-bold text-slate-800 tracking-tight">Evolución de Recaudación Mensual</h4>
                    <p class="text-xs text-slate-500">Métricas de ingresos en Soles (PEN) por periodo</p>
                </div>
                <x-badge color="indigo">Año Fiscal 2026</x-badge>
            </div>
            <div class="h-72">
                <canvas id="recaudacionChart"></canvas>
            </div>
        </div>

        <!-- Doughnut Chart (Aportes Estado) -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-base font-bold text-slate-800">Cumplimiento de Aportes</h4>
                        <p class="text-xs text-slate-500">Padres al día vs pendientes</p>
                    </div>
                </div>
                <div class="h-56 relative flex items-center justify-center">
                    <canvas id="aportesDoughnutChart"></canvas>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 pt-4 border-t border-slate-100 text-center text-xs">
                <div class="p-2 rounded-xl bg-emerald-50 text-emerald-800 font-bold">
                    <p class="text-[10px] uppercase text-emerald-600">Al Día</p>
                    <p class="text-base font-extrabold mt-0.5">{{ $aportesAlDia }}</p>
                </div>
                <div class="p-2 rounded-xl bg-rose-50 text-rose-800 font-bold">
                    <p class="text-[10px] uppercase text-rose-600">Con Deuda</p>
                    <p class="text-base font-extrabold mt-0.5">{{ $aportesConDeuda }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Payments & Electronic Billing Status -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h4 class="text-base font-bold text-slate-800">Últimos Pagos y Facturación Electrónica (CPE)</h4>
                <p class="text-xs text-slate-500">Comprobantes procesados mediante el microservicio asíncrono</p>
            </div>
            <a href="{{ route('pagos.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                Ir a Punto de Venta <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-500 font-semibold border-b border-slate-200/60 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-5 py-3.5">ID</th>
                        <th class="px-5 py-3.5">Padre de Familia</th>
                        <th class="px-5 py-3.5">Concepto</th>
                        <th class="px-5 py-3.5">Monto</th>
                        <th class="px-5 py-3.5">Comprobante</th>
                        <th class="px-5 py-3.5">Estado SUNAT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ultimosPagos as $pago)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-3.5 font-mono text-slate-400">#{{ $pago->id }}</td>
                            <td class="px-5 py-3.5 font-bold text-slate-800">
                                {{ $pago->padreFamilia->nombre_completo ?? 'N/A' }}
                                <span class="block text-[10px] text-slate-400 font-normal">DNI: {{ $pago->padreFamilia->padre_dni ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $pago->pago_concepto }}</td>
                            <td class="px-5 py-3.5 font-extrabold text-emerald-600">S/ {{ number_format($pago->monto_pago, 2) }}</td>
                            <td class="px-5 py-3.5">
                                <a href="{{ route('pagos.ticket', $pago) }}" target="_blank" title="Ver e imprimir ticket" class="inline-flex items-center px-2 py-0.5 rounded font-mono text-[11px] font-bold bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-200 text-slate-700 border border-slate-200 transition-colors">
                                    {{ $pago->serie }}-{{ $pago->numero }}
                                    <i class="fa-solid fa-arrow-up-right-from-square ml-1 text-[9px] opacity-60"></i>
                                </a>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($pago->estado_facturacion === 'EMITIDO')
                                    <x-badge color="emerald" dot>Aceptado SUNAT</x-badge>
                                @elseif($pago->estado_facturacion === 'EN_COLA')
                                    <x-badge color="amber" dot class="animate-pulse">En cola async</x-badge>
                                @else
                                    <x-badge color="slate">{{ $pago->estado_facturacion }}</x-badge>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                No hay pagos registrados en esta institución aún.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // 1. Chart de Recaudación Mensual (Chart.js)
    const ctxRecaudacion = document.getElementById('recaudacionChart').getContext('2d');
    new Chart(ctxRecaudacion, {
        type: 'line',
        data: {
            labels: {!! json_encode($meses) !!},
            datasets: [{
                label: 'Recaudación S/',
                data: {!! json_encode($recaudacionMensual) !!},
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.08)',
                borderWidth: 3,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#4f46e5',
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: { callback: value => 'S/ ' + value }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // 2. Chart de Cumplimiento de Aportes (Chart.js Doughnut)
    const ctxAportes = document.getElementById('aportesDoughnutChart').getContext('2d');
    new Chart(ctxAportes, {
        type: 'doughnut',
        data: {
            labels: ['Al Día', 'Pendiente / Deuda'],
            datasets: [{
                data: [{{ $aportesAlDia }}, {{ $aportesConDeuda }}],
                backgroundColor: ['#10b981', '#f43f5e'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
            },
            cutout: '72%'
        }
    });
</script>
@endpush
