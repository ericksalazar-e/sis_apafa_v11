<?php

namespace App\Http\Controllers;

use App\Models\GastoPoa;
use App\Models\Pago;
use Illuminate\Http\Request;

class GastoPoaController extends Controller
{
    public function index()
    {
        $gastos = GastoPoa::with('user')->latest('fecha')->paginate(12);

        $empresaId = (int) (session('empresa_id') ?? (auth()->user()->empresa_id ?? 1));
        $empresa = \App\Models\Empresa::find($empresaId) ?? \App\Models\Empresa::first();
        $saldoInicialCaja = (float) ($empresa->saldo_inicial_caja ?? 0.00);

        // Indicadores Financieros de Transparencia
        $totalRecaudadoCuotas = (float) Pago::sum('monto_pago');
        $totalRecaudadoQuioscos = (float) \App\Models\PagoQuiosco::sum('monto');
        $totalRecaudado = $saldoInicialCaja + $totalRecaudadoCuotas + $totalRecaudadoQuioscos;
        $totalGastos = (float) GastoPoa::sum('monto');
        $saldoDisponible = $totalRecaudado - $totalGastos;

        // Distribución del Gasto por Categoría Legal (POA Ley 28628)
        $porCategoria = [
            'TECNOLOGIA' => (float) GastoPoa::where('categoria', 'TECNOLOGIA')->sum('monto'),
            'DEPORTE_LUDICO' => (float) GastoPoa::where('categoria', 'DEPORTE_LUDICO')->sum('monto'),
            'CULTURAL_TALLERES' => (float) GastoPoa::where('categoria', 'CULTURAL_TALLERES')->sum('monto'),
            'EMERGENCIAS_BIENESTAR' => (float) GastoPoa::where('categoria', 'EMERGENCIAS_BIENESTAR')->sum('monto'),
            'GESTION_OPERATIVA' => (float) GastoPoa::where('categoria', 'GESTION_OPERATIVA')->sum('monto'),
        ];

        return view('gastos_poa.index', compact(
            'gastos',
            'saldoInicialCaja',
            'totalRecaudado',
            'totalRecaudadoCuotas',
            'totalRecaudadoQuioscos',
            'totalGastos',
            'saldoDisponible',
            'porCategoria'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'categoria' => 'required|in:TECNOLOGIA,DEPORTE_LUDICO,CULTURAL_TALLERES,EMERGENCIAS_BIENESTAR,GESTION_OPERATIVA',
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string|max:1000',
            'monto' => 'required|numeric|min:0.5',
            'nro_comprobante' => 'nullable|string|max:100',
            'proveedor' => 'nullable|string|max:200',
            'fecha' => 'required|date',
            'comprobante_file' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        if ($request->hasFile('comprobante_file')) {
            $file = $request->file('comprobante_file');
            $filename = 'comprobante_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/comprobantes_poa'), $filename);
            $validated['foto_comprobante'] = 'uploads/comprobantes_poa/'.$filename;
        }

        $validated['user_id'] = auth()->id();

        $gasto = GastoPoa::create($validated);

        return redirect()->route('gastos_poa.index')->with(
            'success',
            "Egreso del POA '{$gasto->titulo}' por S/ {$gasto->monto} registrado exitosamente para rendición de cuentas."
        );
    }

    public function destroy(GastoPoa $gasto)
    {
        $gasto->delete();

        return redirect()->route('gastos_poa.index')->with('success', 'Gasto del POA eliminado correctamente.');
    }
}
