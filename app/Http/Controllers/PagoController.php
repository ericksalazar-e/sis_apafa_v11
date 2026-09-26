<?php

namespace App\Http\Controllers;

use App\Jobs\EmitirComprobanteSunatJob;
use App\Models\Aporte;
use App\Models\AportePadre;
use App\Models\PadreFamilia;
use App\Models\Pago;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        $pagos = Pago::with('padreFamilia')->latest()->paginate(10);
        $padres = PadreFamilia::select(['id', 'padre_dni', 'padre_nombres', 'padre_ape_pat', 'padre_ape_mat'])->orderBy('padre_ape_pat')->get();
        $aportes = Aporte::all();

        return view('pagos.index', compact('pagos', 'padres', 'aportes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'padre_familia_id' => 'required|exists:padres_familia,id',
            'pago_concepto' => 'required|string|max:255',
            'monto_pago' => 'required|numeric|min:0.5',
            'tipo_comprobante' => 'required|in:BOLETA,FACTURA,NOTA_VENTA',
        ]);

        $padre = PadreFamilia::findOrFail($validated['padre_familia_id']);
        $serie = $validated['tipo_comprobante'] === 'FACTURA' ? 'F001' : ($validated['tipo_comprobante'] === 'BOLETA' ? 'B001' : 'NV01');

        $modalidad = $request->input('modalidad', 'CONTADO');
        $numeroCuota = (int) $request->input('numero_cuota', 1);
        $totalCuotas = (int) $request->input('total_cuotas', 1);
        $saldoPendiente = (float) $request->input('saldo_pendiente', 0.00);
        $faenaId = $request->input('faena_id');

        $pago = \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $padre, $serie, $modalidad, $numeroCuota, $totalCuotas, $saldoPendiente, $faenaId) {
            // Cálculo robusto del correlativo bajo bloqueo de transacción
            $maxNum = Pago::where('serie', $serie)->max(\Illuminate\Support\Facades\DB::raw('CAST(numero AS UNSIGNED)')) ?? 0;
            $correlativo = sprintf('%06d', $maxNum + 1);

            // 1. Guardar Pago de forma inmediata (POS No Bloqueante con soporte D.S. 007-2026-MINEDU)
            return Pago::create([
                'padre_familia_id' => $padre->id,
                'user_id' => auth()->id() ?? 1,
                'pago_concepto' => $validated['pago_concepto'],
                'monto_pago' => $validated['monto_pago'],
                'modalidad' => $modalidad,
                'numero_cuota' => $numeroCuota,
                'total_cuotas' => $totalCuotas,
                'saldo_pendiente' => $saldoPendiente,
                'faena_id' => $faenaId,
                'debe' => $saldoPendiente,
                'tipo_comprobante' => $validated['tipo_comprobante'],
                'serie' => $serie,
                'numero' => $correlativo,
                'estado_facturacion' => 'EN_COLA',
            ]);
        });

        // 2. Despachar emisión electrónica en segundo plano (Asíncrono)
        EmitirComprobanteSunatJob::dispatch($pago->id);

        return redirect()->route('pagos.index')->with('success', "Pago registrado exitosamente. Comprobante {$pago->serie}-{$pago->numero} enviado a cola de facturación electrónica.");
    }

    public function ticket(Pago $pago)
    {
        $pago->load(['padreFamilia', 'empresa']);
        return view('pagos.ticket', compact('pago'));
    }
}
