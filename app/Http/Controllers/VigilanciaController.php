<?php

namespace App\Http\Controllers;

use App\Models\DictamenVigilancia;
use App\Models\Empresa;
use App\Models\GastoPoa;
use App\Models\Pago;
use App\Models\PagoQuiosco;
use Illuminate\Http\Request;

class VigilanciaController extends Controller
{
    /**
     * Panel de Fiscalización y Dictámenes del Consejo de Vigilancia
     * Conforme a facultades de D.S. N.º 007-2026-MINEDU
     */
    public function index()
    {
        $dictamenes = DictamenVigilancia::with('user')->latest()->paginate(10);
        $periodosExistentes = DictamenVigilancia::pluck('periodo')->map(fn($p) => trim($p))->toArray();

        // Cálculos contables en tiempo real
        $totalIngresosCuotas = Pago::sum('monto_pago');
        $totalIngresosQuioscos = PagoQuiosco::sum('monto');
        $totalIngresos = $totalIngresosCuotas + $totalIngresosQuioscos;

        $totalGastosPoa = GastoPoa::sum('monto');
        $saldoEnCaja = $totalIngresos - $totalGastosPoa;

        $totalDictamenes = DictamenVigilancia::count();
        $totalFavorables = DictamenVigilancia::where('tipo_dictamen', 'FAVORABLE')->count();
        $totalObservados = DictamenVigilancia::where('tipo_dictamen', 'OBSERVADO')->count();
        $totalDesaprobados = DictamenVigilancia::where('tipo_dictamen', 'DESAPROBADO')->count();

        return view('vigilancia.index', compact(
            'dictamenes',
            'periodosExistentes',
            'totalIngresos',
            'totalIngresosCuotas',
            'totalIngresosQuioscos',
            'totalGastosPoa',
            'saldoEnCaja',
            'totalDictamenes',
            'totalFavorables',
            'totalObservados',
            'totalDesaprobados'
        ));
    }

    /**
     * Emitir y Registrar Dictamen de Auditoría Interna
     */
    public function store(Request $request)
    {
        $request->validate([
            'periodo' => 'required|string|max:100',
            'titulo' => 'required|string|max:255',
            'tipo_dictamen' => 'required|in:FAVORABLE,OBSERVADO,DESAPROBADO',
            'detalles_auditoria' => 'required|string',
            'observaciones_recomendaciones' => 'nullable|string',
        ]);

        $empresaId = session('empresa_id', 1);
        $periodo = trim($request->periodo);

        // Validar que no se repita el periodo del dictamen para la institución
        $existePeriodo = DictamenVigilancia::where('empresa_id', $empresaId)
            ->whereRaw('LOWER(TRIM(periodo)) = ?', [mb_strtolower($periodo)])
            ->exists();

        if ($existePeriodo) {
            return back()->withInput()->withErrors([
                'periodo' => "Ya existe un dictamen registrado para el periodo \"{$periodo}\". No se permite duplicar el periodo del dictamen."
            ]);
        }

        $totalIngresos = Pago::sum('monto_pago') + PagoQuiosco::sum('monto');
        $totalGastos = GastoPoa::sum('monto');
        $saldo = $totalIngresos - $totalGastos;

        // Inconsistencia contable: No se puede emitir un dictamen FAVORABLE si el saldo es deficitario (deuda en caja)
        if ($saldo < 0 && $request->tipo_dictamen === 'FAVORABLE') {
            $montoDeficit = number_format(abs($saldo), 2);
            return back()->withInput()->withErrors([
                'tipo_dictamen' => "Inconsistencia de auditoría contable: No se puede emitir un dictamen FAVORABLE cuando la APAFA registra un déficit / saldo negativo en cuenta de S/ -{$montoDeficit}. El resultado del dictamen debe ser calificado como OBSERVADO o DESAPROBADO."
            ]);
        }

        DictamenVigilancia::create([
            'empresa_id' => $empresaId,
            'periodo' => $periodo,
            'titulo' => $request->titulo,
            'tipo_dictamen' => $request->tipo_dictamen,
            'detalles_auditoria' => $request->detalles_auditoria,
            'observaciones_recomendaciones' => $request->observaciones_recomendaciones,
            'monto_ingresos_auditado' => $totalIngresos,
            'monto_gastos_auditado' => $totalGastos,
            'saldo_auditado' => $saldo,
            'user_id' => auth()->id() ?? 1,
        ]);

        return redirect()->route('vigilancia.index')->with('success', 'Dictamen del Consejo de Vigilancia emitido y publicado conforme a ley.');
    }

    /**
     * Descargar o Imprimir Dictamen de Auditoría
     */
    public function imprimirDictamen($id)
    {
        $dictamen = DictamenVigilancia::with('user')->findOrFail($id);
        $empresa = Empresa::find(session('empresa_id', 1)) ?? Empresa::first();

        return view('vigilancia.dictamen_imprimible', compact('dictamen', 'empresa'));
    }

    /**
     * Eliminar Dictamen de Auditoría
     */
    public function destroy($id)
    {
        $dictamen = DictamenVigilancia::findOrFail($id);
        $dictamen->delete();

        return redirect()->route('vigilancia.index')->with('success', 'Dictamen del Consejo de Vigilancia eliminado correctamente.');
    }
}
