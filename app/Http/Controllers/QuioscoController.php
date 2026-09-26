<?php

namespace App\Http\Controllers;

use App\Models\ConcesionQuiosco;
use App\Models\Empresa;
use App\Models\PagoQuiosco;
use App\Models\Quiosco;
use Illuminate\Http\Request;

class QuioscoController extends Controller
{
    /**
     * Gestión de Quioscos y Cafeterías Escolares Saludables
     * Conforme a Ley N° 30021 y D.S. N.º 007-2026-MINEDU
     */
    public function index()
    {
        $quioscos = Quiosco::with(['concesionVigente.pagos'])->get();

        // Si no existe ningún quiosco registrado inicialmente, sembramos el quiosco principal
        if ($quioscos->isEmpty()) {
            Quiosco::create([
                'empresa_id' => session('empresa_id', 1),
                'nombre' => 'Quiosco y Cafetería N° 01 (Patio Central)',
                'ubicacion' => 'Pabellón Principal de Secundaria',
                'estado' => 'DISPONIBLE',
            ]);
            $quioscos = Quiosco::with(['concesionVigente.pagos'])->get();
        }

        $concesiones = ConcesionQuiosco::with(['quiosco', 'pagos'])
            ->orderBy('fecha_inicio', 'desc')
            ->paginate(10);

        $totalRecaudadoQuioscos = PagoQuiosco::sum('monto');
        $totalConcesionesVigentes = ConcesionQuiosco::where('estado', 'VIGENTE')->count();

        // Métricas de pago mensual de quioscos (obligación por mes)
        $mesesNombres = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Setiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        $mesActualNombre = $mesesNombres[(int) date('n')] . ' ' . date('Y');
        $canonMensualTotal = (float) ConcesionQuiosco::where('estado', 'VIGENTE')->sum('canon_mensual');
        $recaudadoMesActual = (float) PagoQuiosco::where('mes_correspondiente', 'LIKE', '%' . $mesesNombres[(int) date('n')] . '%')->sum('monto');
        $totalDeudaQuioscos = (float) $quioscos->sum(function ($q) {
            return $q->concesionVigente ? $q->concesionVigente->deuda_acumulada : 0;
        });

        return view('quioscos.index', compact(
            'quioscos',
            'concesiones',
            'totalRecaudadoQuioscos',
            'totalConcesionesVigentes',
            'canonMensualTotal',
            'mesActualNombre',
            'recaudadoMesActual',
            'totalDeudaQuioscos'
        ));
    }

    /**
     * Registrar Nuevo Quiosco
     */
    public function storeQuiosco(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ubicacion' => 'nullable|string|max:255',
        ]);

        Quiosco::create([
            'empresa_id' => session('empresa_id', 1),
            'nombre' => $request->nombre,
            'ubicacion' => $request->ubicacion,
            'estado' => 'DISPONIBLE',
        ]);

        return redirect()->route('quioscos.index')->with('success', 'Quiosco escolar registrado satisfactoriamente.');
    }

    /**
     * Registrar Contrato de Concesión (Licitación Pública de Cafetines)
     */
    public function storeConcesion(Request $request)
    {
        $request->validate([
            'quiosco_id' => 'required|exists:quioscos,id',
            'nombre_concesionario' => 'required|string|max:255',
            'dni_ruc' => 'required|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'canon_mensual' => 'required|numeric|min:1',
            'garantia' => 'nullable|numeric|min:0',
        ]);

        $quiosco = Quiosco::findOrFail($request->quiosco_id);

        $concesion = ConcesionQuiosco::create([
            'quiosco_id' => $quiosco->id,
            'empresa_id' => session('empresa_id', 1),
            'nombre_concesionario' => $request->nombre_concesionario,
            'dni_ruc' => $request->dni_ruc,
            'telefono' => $request->telefono,
            'correo' => $request->correo,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'canon_mensual' => $request->canon_mensual,
            'garantia' => $request->garantia ?? 0,
            'cumple_ley_alimentacion_saludable' => $request->has('cumple_ley_alimentacion_saludable'),
            'estado' => 'VIGENTE',
        ]);

        $quiosco->update(['estado' => 'CONCESIONADO']);

        return redirect()->route('quioscos.index')->with('success', 'Adjudicación de concesión de quiosco registrada con éxito.');
    }

    /**
     * Registrar Pago del Canon Mensual de Arrendamiento
     */
    public function registrarPago(Request $request, $concesionId)
    {
        $concesion = ConcesionQuiosco::findOrFail($concesionId);

        $request->validate([
            'mes_correspondiente' => 'required|string|max:50',
            'monto' => 'required|numeric|min:1',
            'fecha_pago' => 'required|date',
            'metodo_pago' => 'required|string',
        ]);

        $mes = trim($request->mes_correspondiente);
        if ($mes === '__OTRO__' && !empty($request->mes_personalizado)) {
            $mes = trim($request->mes_personalizado);
        }

        // Validación anti-duplicados: verificar si ya existe pago registrado para este mes en la misma concesión
        $yaExistePago = PagoQuiosco::where('concesion_id', $concesion->id)
            ->whereRaw('LOWER(TRIM(mes_correspondiente)) = ?', [mb_strtolower($mes, 'UTF-8')])
            ->exists();

        if ($yaExistePago) {
            return back()->withInput()->with('error', "El mes de '{$mes}' ya cuenta con un pago de canon registrado para esta concesión. No se permiten pagos duplicados del mismo mes.");
        }

        // Generar correlativo secuencial estricto (Q-000001, Q-000002, etc.)
        $ultimoNumero = 0;
        $comprobantes = PagoQuiosco::pluck('comprobante_numero');
        foreach ($comprobantes as $comp) {
            if (preg_match('/Q-(\d+)/', $comp, $matches)) {
                $num = (int) $matches[1];
                if ($num > $ultimoNumero) {
                    $ultimoNumero = $num;
                }
            }
        }
        $correlativo = 'Q-' . str_pad($ultimoNumero + 1, 6, '0', STR_PAD_LEFT);

        $pago = PagoQuiosco::create([
            'concesion_id' => $concesion->id,
            'empresa_id' => $concesion->empresa_id,
            'mes_correspondiente' => $mes,
            'monto' => $request->monto,
            'fecha_pago' => $request->fecha_pago,
            'metodo_pago' => $request->metodo_pago,
            'comprobante_numero' => $correlativo,
            'observaciones' => $request->observaciones,
        ]);

        return redirect()->route('quioscos.index')->with('success', "Pago mensual de quiosco registrado (Mes: {$mes} - S/ " . number_format($request->monto, 2) . "). Comprobante {$correlativo} emitido.");
    }

    /**
     * Recibo Oficial de Ingreso por Canon de Quiosco
     */
    public function reciboPago($pagoId)
    {
        $pago = PagoQuiosco::with('concesion.quiosco')->findOrFail($pagoId);
        $empresa = Empresa::find(session('empresa_id', 1)) ?? Empresa::first();

        return view('quioscos.recibo', compact('pago', 'empresa'));
    }
}
