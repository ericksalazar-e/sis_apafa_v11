<?php

namespace App\Http\Controllers;

use App\Models\Faena;
use App\Models\FaenaAsistencia;
use App\Models\PadreFamilia;
use App\Models\Pago;
use App\Models\Empresa;
use Illuminate\Http\Request;

class FaenaController extends Controller
{
    /**
     * Listado de Faenas Escolares y Servicios Comunales (D.S. 007-2026-MINEDU)
     */
    public function index()
    {
        $faenas = Faena::withCount('asistencias')
            ->orderBy('fecha_faena', 'desc')
            ->paginate(10);

        $totalFaenas = Faena::count();
        $totalCompensado = FaenaAsistencia::sum('monto_compensado');
        $totalAsistencias = FaenaAsistencia::count();

        return view('faenas.index', compact('faenas', 'totalFaenas', 'totalCompensado', 'totalAsistencias'));
    }

    /**
     * Registrar convocatoria a Faena Comunal
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'fecha_faena' => 'required|date',
            'lugar' => 'required|string|max:255',
            'valor_compensacion' => 'required|numeric|min:0',
            'cupo_maximo' => 'required|integer|min:1',
            'descripcion' => 'nullable|string',
        ]);

        // Verificación estricta de seguridad: Prohibir demoliciones, derribo de torres y trabajos de alto riesgo (Ley 29783)
        $textoAnalizar = strtolower($request->titulo . ' ' . ($request->descripcion ?? ''));
        $terminosPeligrosos = ['tumbar', 'tumben', 'demoler', 'demolicion', 'derribar', 'torre', 'alta tension', 'tanque elevado', 'fosa septica', 'pozo ciego'];

        foreach ($terminosPeligrosos as $termino) {
            if (str_contains($textoAnalizar, $termino)) {
                return back()->withInput()->with('error', 'PROHIBICIÓN ESTRICTA DE SEGURIDAD (LEY N° 29783 / MINEDU): Para prevenir muertes y accidentes fatales (como el colapso de torres o estructuras), está terminantemente prohibido convocar faenas para demolición, derribos o trabajos de alto riesgo. Las faenas de APAFA son exclusivas de mantenimiento menor preventivo a nivel de piso y está totalmente prohibida la participación de escolares.');
            }
        }

        Faena::create([
            'titulo' => $request->titulo,
            'fecha_faena' => $request->fecha_faena,
            'lugar' => $request->lugar,
            'valor_compensacion' => $request->valor_compensacion,
            'cupo_maximo' => $request->cupo_maximo,
            'descripcion' => $request->descripcion,
            'estado' => 'PROGRAMADA',
        ]);

        return redirect()->route('faenas.index')->with('success', 'Convocatoria a Faena Escolar creada exitosamente.');
    }

    /**
     * Detalle y control de asistencia a Faena
     */
    public function show($id)
    {
        $faena = Faena::with(['asistencias.padreFamilia', 'asistencias.pago'])->findOrFail($id);
        $asistentesIds = $faena->asistencias->pluck('padre_familia_id');
        $padres = PadreFamilia::whereNotIn('id', $asistentesIds)
            ->orderBy('padre_ape_pat')
            ->orderBy('padre_nombres')
            ->get();

        return view('faenas.show', compact('faena', 'padres'));
    }

    /**
     * Registrar asistencia de padre y aplicar compensación de cuota de APAFA
     */
    public function marcarAsistencia(Request $request, $id)
    {
        $faena = Faena::findOrFail($id);

        $padreId = $request->input('padre_familia_id');
        $queryText = trim($request->input('dni_o_qr', ''));

        if (!$padreId && !empty($queryText)) {
            // Si viene de escaneo QR (ej: APAFA-PADRE-00001 o DNI)
            if (preg_match('/APAFA-PADRE-(\d+)/', $queryText, $matches)) {
                $padreId = (int) $matches[1];
            } else {
                $padre = PadreFamilia::where('padre_dni', $queryText)
                    ->orWhere('correlativo', $queryText)
                    ->first();
                if ($padre) {
                    $padreId = $padre->id;
                }
            }
        }

        if (!$padreId) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No se encontró el padre de familia con el DNI o QR escaneado.'], 404);
            }
            return back()->with('error', 'Padre de familia no encontrado.');
        }

        $padre = PadreFamilia::findOrFail($padreId);

        // Verificar si ya registró asistencia en esta faena
        $existente = FaenaAsistencia::where('faena_id', $faena->id)
            ->where('padre_familia_id', $padre->id)
            ->first();

        if ($existente) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "El padre {$padre->apellidos}, {$padre->nombres} ya tiene asistencia registrada en esta faena."
                ], 422);
            }
            return back()->with('error', "El padre ya tiene asistencia registrada en esta faena.");
        }

        $montoCompensado = (float) ($request->input('monto_compensado') ?? $faena->valor_compensacion);
        $horasTrabajadas = (float) ($request->input('horas_trabajadas') ?? 4.0);

        // Crear pago de compensación en el libro de caja
        $pago = Pago::create([
            'empresa_id' => $faena->empresa_id ?? 1,
            'padre_familia_id' => $padre->id,
            'user_id' => auth()->id(),
            'pago_concepto' => "COMPENSACIÓN POR FAENA COMUNAL - {$faena->titulo} (D.S. N.º 007-2026-MINEDU)",
            'monto_pago' => $montoCompensado,
            'modalidad' => 'COMPENSACION_FAENA',
            'numero_cuota' => 1,
            'total_cuotas' => 1,
            'saldo_pendiente' => 0.00,
            'faena_id' => $faena->id,
            'debe' => 0.00,
            'tipo_comprobante' => 'CONSTANCIA_COMPENSACION',
            'serie' => 'COM1',
            'numero' => str_pad(Pago::count() + 1, 8, '0', STR_PAD_LEFT),
            'estado_facturacion' => 'EMITIDO',
            'mensaje_sunat' => 'Exoneración y compensación por trabajo comunal según Art. 78 D.S. N.º 007-2026-MINEDU',
        ]);

        $asistencia = FaenaAsistencia::create([
            'faena_id' => $faena->id,
            'padre_familia_id' => $padre->id,
            'horas_trabajadas' => $horasTrabajadas,
            'monto_compensado' => $montoCompensado,
            'observaciones' => $request->input('observaciones', 'Compensación aplicada conforme a D.S. N.º 007-2026-MINEDU'),
            'pago_id' => $pago->id,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Asistencia registrada y compensación de S/ " . number_format($montoCompensado, 2) . " aplicada a {$padre->apellidos}, {$padre->nombres}.",
                'padre' => [
                    'id' => $padre->id,
                    'nombre_completo' => "{$padre->apellidos}, {$padre->nombres}",
                    'dni' => $padre->dni,
                ],
                'asistencia_id' => $asistencia->id,
                'constancia_url' => route('faenas.constancia', [$faena->id, $padre->id]),
            ]);
        }

        return back()->with('success', "Asistencia registrada y cuota compensada (S/ " . number_format($montoCompensado, 2) . ") a favor de {$padre->apellidos}, {$padre->nombres}.");
    }

    /**
     * Emisión formal de Constancia de Compensación por Trabajo / Faena Escolar
     */
    public function constancia($faenaId, $padreId)
    {
        $faena = Faena::findOrFail($faenaId);
        $padre = PadreFamilia::with('estudiantes')->findOrFail($padreId);
        $asistencia = FaenaAsistencia::where('faena_id', $faenaId)
            ->where('padre_familia_id', $padreId)
            ->firstOrFail();

        $empresa = Empresa::find(session('empresa_id', 1)) ?? Empresa::first();

        return view('faenas.constancia', compact('faena', 'padre', 'asistencia', 'empresa'));
    }

    /**
     * Padrón Oficial Imprimible de Padres Asistentes a la Faena (D.S. 007-2026-MINEDU)
     */
    public function imprimirPadron($id)
    {
        $faena = Faena::with(['asistencias.padreFamilia.estudiantes', 'asistencias.pago'])->findOrFail($id);
        $empresa = Empresa::find(session('empresa_id', 1)) ?? Empresa::first();

        return view('faenas.padron_imprimible', compact('faena', 'empresa'));
    }
}
