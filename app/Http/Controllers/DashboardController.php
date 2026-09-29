<?php

namespace App\Http\Controllers;

use App\Models\AportePadre;
use App\Models\Estudiante;
use App\Models\PadreFamilia;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $empresaId = session('empresa_id') ?? auth()->user()->empresa_id ?? 1;

        // Métricas y KPIs consolidadas y aisladas estrictamente por Tenant
        $totalPadres = PadreFamilia::where('empresa_id', $empresaId)->count();
        $totalEstudiantes = Estudiante::where('empresa_id', $empresaId)->count();

        $empresa = \App\Models\Empresa::find($empresaId) ?? \App\Models\Empresa::first();
        $saldoInicialCaja = (float) ($empresa->saldo_inicial_caja ?? 0.00);

        // Recaudación total válida (incluyendo saldo inicial, cuotas y quioscos)
        $totalRecaudadoCuotas = (float) Pago::where('empresa_id', $empresaId)
            ->where('estado_facturacion', '!=', 'ERROR')
            ->sum('monto_pago');
        $totalRecaudadoQuioscos = (float) \App\Models\PagoQuiosco::where('empresa_id', $empresaId)->sum('monto');
        $totalRecaudado = $saldoInicialCaja + $totalRecaudadoCuotas + $totalRecaudadoQuioscos;

        $deudaTotal = (float) AportePadre::where('empresa_id', $empresaId)->sum('debe');

        // Últimos pagos registrados (consolidados, sin duplicados y ordenados por id desc)
        $ultimosPagos = Pago::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->with('padreFamilia')
            ->orderBy('id', 'desc')
            ->take(8)
            ->get();

        // Datos para Chart.js - Métricas de Recaudación Mensual del Año Fiscal Actual
        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        $pagosPorMes = Pago::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->where('estado_facturacion', '!=', 'ERROR')
            ->whereYear('created_at', date('Y'))
            ->selectRaw('MONTH(created_at) as mes, SUM(monto_pago) as total')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->pluck('total', 'mes')
            ->toArray();

        $recaudacionMensual = [];
        for ($i = 1; $i <= 12; $i++) {
            $recaudacionMensual[] = (float) ($pagosPorMes[$i] ?? 0);
        }

        // Distribución de Estado de Aportes para Chart.js (Doughnut)
        $aportesAlDia = AportePadre::where('empresa_id', $empresaId)->where('debe', '<=', 0)->count();
        $aportesConDeuda = AportePadre::where('empresa_id', $empresaId)->where('debe', '>', 0)->count();

        // Comprobantes emitidos por tipo
        $boletasCount = Pago::where('empresa_id', $empresaId)->where('tipo_comprobante', 'BOLETA')->count();
        $facturasCount = Pago::where('empresa_id', $empresaId)->where('tipo_comprobante', 'FACTURA')->count();
        $recibosCount = Pago::where('empresa_id', $empresaId)->where('tipo_comprobante', 'NOTA_VENTA')->count();

        return view('dashboard', compact(
            'totalPadres',
            'totalEstudiantes',
            'totalRecaudado',
            'deudaTotal',
            'ultimosPagos',
            'meses',
            'recaudacionMensual',
            'aportesAlDia',
            'aportesConDeuda',
            'boletasCount',
            'facturasCount',
            'recibosCount'
        ));
    }
}
