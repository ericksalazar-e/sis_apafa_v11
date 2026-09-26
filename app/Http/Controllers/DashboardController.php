<?php

namespace App\Http\Controllers;

use App\Models\Aporte;
use App\Models\AportePadre;
use App\Models\Estudiante;
use App\Models\PadreFamilia;
use App\Models\Pago;
use App\Models\Reunion;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Métricas y KPIs filtradas automáticamente por TenantScope
        $totalPadres = PadreFamilia::count();
        $totalEstudiantes = Estudiante::count();
        $totalRecaudado = Pago::sum('monto_pago');
        $deudaTotal = AportePadre::sum('debe');

        // Pagos recientes
        $ultimosPagos = Pago::with('padreFamilia')->latest()->take(5)->get();

        // Datos para Chart.js - Métricas de Recaudación
        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        
        $pagosPorMes = Pago::selectRaw('MONTH(created_at) as mes, SUM(monto_pago) as total')
            ->whereYear('created_at', date('Y'))
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();
            
        $recaudacionMensual = [];
        for ($i = 1; $i <= 12; $i++) {
            $recaudacionMensual[] = (float) ($pagosPorMes[$i] ?? 0);
        }

        // Distribución de Estado de Aportes para Chart.js (Doughnut)
        $aportesAlDia = AportePadre::where('debe', 0)->count();
        $aportesConDeuda = AportePadre::where('debe', '>', 0)->count();

        // Comprobantes emitidos por tipo
        $boletasCount = Pago::where('tipo_comprobante', 'BOLETA')->count();
        $facturasCount = Pago::where('tipo_comprobante', 'FACTURA')->count();
        $recibosCount = Pago::where('tipo_comprobante', 'NOTA_VENTA')->count();

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
