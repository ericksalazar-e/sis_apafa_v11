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
        $padres = PadreFamilia::with(['estudiantes' => function ($q) {
            $q->select('id', 'padre_familia_id', 'nombres', 'ape_pat', 'ape_mat', 'grado', 'seccion', 'nivel', 'dni');
        }])->select(['id', 'padre_dni', 'padre_nombres', 'padre_ape_pat', 'padre_ape_mat', 'correlativo', 'celular'])
            ->orderBy('padre_ape_pat')
            ->get();
        $aportes = Aporte::all();

        return view('pagos.index', compact('pagos', 'padres', 'aportes'));
    }

    /**
     * Búsqueda ágil de asociados en el POS por DNI, Código de Barras o Código QR.
     * Soporta DNI del padre, DNI del alumno (carné escolar), código correlativo o lectura directa de pistola/cámara.
     */
    public function buscarAsociado(Request $request)
    {
        $term = trim($request->input('query', ''));
        if (empty($term)) {
            return response()->json(['success' => false, 'message' => 'Ingrese un DNI o escanee un código de barra / QR.'], 400);
        }

        $empresaId = session('empresa_id') ?? auth()->user()->empresa_id ?? 1;

        // 1. Extraer DNI de formatos QR oficiales si el escáner capturó la cadena completa
        $dniPadre = null;
        $dniEstudiante = null;

        if (str_contains($term, 'APAFA_ASOCIADO') && preg_match('/DNI:(\d{8})/i', $term, $m)) {
            $dniPadre = $m[1];
        } elseif (str_contains($term, 'ASISTENCIA_ESCOLAR')) {
            if (preg_match('/APODERADO:(\d{8})/i', $term, $m) && $m[1] !== '00000000') {
                $dniPadre = $m[1];
            } elseif (preg_match('/DNI:(\d{8})/i', $term, $m)) {
                $dniEstudiante = $m[1];
            }
        } elseif (preg_match('/^\d{8}$/', $term)) {
            $dniPadre = $term;
        }

        $padre = null;
        $motivoMatch = '';

        // 2. Buscar por DNI de Padre de Familia
        if ($dniPadre) {
            $padre = PadreFamilia::where('empresa_id', $empresaId)
                ->where('padre_dni', $dniPadre)
                ->with(['estudiantes'])
                ->first();
            if ($padre) {
                $motivoMatch = 'Identificado por Carné / DNI del Padre de Familia';
            }
        }

        // 3. Si no es DNI de padre, verificar si el DNI pertenece a un estudiante (Carné de Asistencia del hijo)
        $dniParaEstudiante = $dniEstudiante ?: (preg_match('/^\d{8}$/', $term) ? $term : null);
        if (! $padre && $dniParaEstudiante) {
            $estudiante = \App\Models\Estudiante::where('empresa_id', $empresaId)
                ->where('dni', $dniParaEstudiante)
                ->with(['padreFamilia.estudiantes'])
                ->first();
            if ($estudiante && $estudiante->padreFamilia) {
                $padre = $estudiante->padreFamilia;
                $motivoMatch = "Identificado mediante Carné Escolar de su Hijo(a): {$estudiante->nombre_completo} ({$estudiante->grado} \"{$estudiante->seccion}\")";
            }
        }

        // 4. Buscar por Código Correlativo de Carné (Padre o Estudiante)
        if (! $padre) {
            $padre = PadreFamilia::where('empresa_id', $empresaId)
                ->where('correlativo', $term)
                ->with(['estudiantes'])
                ->first();
            if ($padre) {
                $motivoMatch = 'Identificado por Código Correlativo de Asociado';
            }
        }

        if (! $padre) {
            $estudiante = \App\Models\Estudiante::where('empresa_id', $empresaId)
                ->where('correlativo', $term)
                ->with(['padreFamilia.estudiantes'])
                ->first();
            if ($estudiante && $estudiante->padreFamilia) {
                $padre = $estudiante->padreFamilia;
                $motivoMatch = "Identificado por Código de Carné de su Hijo(a): {$estudiante->nombre_completo}";
            }
        }

        // 5. Búsqueda por coincidencia de Nombres/Apellidos si se escribieron letras
        if (! $padre && strlen($term) >= 3) {
            $padres = PadreFamilia::where('empresa_id', $empresaId)
                ->where(function ($q) use ($term) {
                    $q->where('padre_dni', 'like', "%{$term}%")
                        ->orWhere('padre_nombres', 'like', "%{$term}%")
                        ->orWhere('padre_ape_pat', 'like', "%{$term}%")
                        ->orWhere('padre_ape_mat', 'like', "%{$term}%");
                })
                ->with(['estudiantes'])
                ->limit(6)
                ->get();

            if ($padres->count() === 1) {
                $padre = $padres->first();
                $motivoMatch = 'Coincidencia de Nombres y Apellidos';
            } elseif ($padres->count() > 1) {
                return response()->json([
                    'success' => true,
                    'multiple' => true,
                    'resultados' => $padres->map(function ($p) {
                        return [
                            'id' => $p->id,
                            'dni' => $p->padre_dni,
                            'nombre_completo' => $p->nombre_completo,
                            'estudiantes_resumen' => $p->estudiantes->map(fn ($e) => "{$e->nombre_completo} ({$e->grado})")->implode(', '),
                        ];
                    }),
                ]);
            }
        }

        if (! $padre) {
            return response()->json([
                'success' => false,
                'message' => "No se encontró ningún asociado registrado con el DNI, código de barra o QR: '{$term}'.",
            ], 404);
        }

        // 6. Obtener aportes o cuotas pendientes del asociado para sugerencia en ventanilla
        $deudas = AportePadre::where('empresa_id', $empresaId)
            ->where('padre_familia_id', $padre->id)
            ->where('debe', '>', 0)
            ->with('aporte')
            ->get()
            ->map(function ($ap) {
                return [
                    'id' => $ap->id,
                    'titulo' => $ap->aporte->titulo ?? 'Cuota APAFA',
                    'monto_aporte' => (float) $ap->monto_aporte,
                    'monto_aportado' => (float) $ap->monto_aportado,
                    'debe' => (float) $ap->debe,
                ];
            });

        return response()->json([
            'success' => true,
            'multiple' => false,
            'motivo_match' => $motivoMatch,
            'deudas' => $deudas,
            'padre' => [
                'id' => $padre->id,
                'dni' => $padre->padre_dni,
                'nombre_completo' => $padre->nombre_completo,
                'celular' => $padre->celular ?? 'Sin cel.',
                'correlativo' => $padre->correlativo ?? 'S/C',
                'estudiantes' => $padre->estudiantes->map(function ($e) {
                    return [
                        'id' => $e->id,
                        'nombre_completo' => $e->nombre_completo,
                        'grado_seccion' => "{$e->grado} \"{$e->seccion}\"",
                        'nivel' => $e->nivel,
                        'parentesco' => $e->parentesco ?? 'HIJO(A)',
                    ];
                }),
                'deudas' => $deudas,
            ],
        ]);
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
        $empresaId = session('empresa_id') ?? auth()->user()->empresa_id ?? 1;

        // 1. Obtener serie configurada para el tenant (o predeterminada)
        $configCpe = \App\Models\ConfiguracionCpe::where('empresa_id', $empresaId)->first();
        $serie = $configCpe ? $configCpe->obtenerSerie($validated['tipo_comprobante']) : ($validated['tipo_comprobante'] === 'FACTURA' ? 'F001' : ($validated['tipo_comprobante'] === 'BOLETA' ? 'B001' : 'NV01'));

        // 2. Control de Idempotencia: Prevenir pagos duplicados por doble clic en POS (ventana de 10 segundos)
        $pagoDuplicado = Pago::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->where('padre_familia_id', $padre->id)
            ->where('pago_concepto', $validated['pago_concepto'])
            ->where('monto_pago', $validated['monto_pago'])
            ->where('created_at', '>=', now()->subSeconds(10))
            ->first();

        if ($pagoDuplicado) {
            return redirect()->route('pagos.index')->with('warning', "El comprobante {$pagoDuplicado->serie}-{$pagoDuplicado->numero} ya fue procesado hace un instante. Se evitó la duplicación del cobro.");
        }

        $modalidad = $request->input('modalidad', 'CONTADO');
        $numeroCuota = (int) $request->input('numero_cuota', 1);
        $totalCuotas = (int) $request->input('total_cuotas', 1);
        $saldoPendiente = (float) $request->input('saldo_pendiente', 0.00);
        $faenaId = $request->input('faena_id');
        $esRegularizacion = $request->boolean('es_regularizacion') || $request->input('es_regularizacion') === '1';
        $reciboManualReferencia = $request->input('recibo_manual_referencia');

        $pago = \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $padre, $serie, $empresaId, $modalidad, $numeroCuota, $totalCuotas, $saldoPendiente, $faenaId, $esRegularizacion, $reciboManualReferencia) {
            // 3. Cálculo robusto, atómico e irrepetible del correlativo bajo bloqueo exclusivo
            $maxNum = Pago::withoutGlobalScopes()
                ->where('empresa_id', $empresaId)
                ->where('serie', $serie)
                ->lockForUpdate()
                ->max(\Illuminate\Support\Facades\DB::raw('CAST(numero AS UNSIGNED)')) ?? 0;
            $correlativo = sprintf('%06d', $maxNum + 1);

            // 4. Guardar Pago con garantía de unicidad
            $p = Pago::create([
                'empresa_id' => $empresaId,
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
                'es_regularizacion' => $esRegularizacion,
                'recibo_manual_referencia' => $reciboManualReferencia,
                'tipo_comprobante' => $validated['tipo_comprobante'],
                'serie' => $serie,
                'numero' => $correlativo,
                'estado_facturacion' => $validated['tipo_comprobante'] === 'NOTA_VENTA' ? 'EMITIDO' : 'EN_COLA',
            ]);

            // Amortizar / cancelar en AportePadre si coincide con algún aporte
            $conceptoLimpio = trim(preg_replace('/^aporte:\s*/i', '', $validated['pago_concepto']));
            $aporte = Aporte::where('empresa_id', $empresaId)
                ->where(function ($q) use ($validated, $conceptoLimpio) {
                    $q->where('titulo', $validated['pago_concepto'])
                        ->orWhere('titulo', $conceptoLimpio)
                        ->orWhereRaw('? LIKE CONCAT("%", titulo, "%")', [$validated['pago_concepto']]);
                })
                ->first();
            if ($aporte) {
                $apPadre = AportePadre::where('empresa_id', $empresaId)
                    ->where('aporte_id', $aporte->id)
                    ->where('padre_familia_id', $padre->id)
                    ->first();
                if ($apPadre) {
                    $nuevoAportado = $apPadre->monto_aportado + $validated['monto_pago'];
                    $nuevoDebe = max(0, $apPadre->monto_aporte - $nuevoAportado);
                    $apPadre->update([
                        'monto_aportado' => $nuevoAportado,
                        'debe' => $nuevoDebe,
                    ]);
                }
            }

            return $p;
        });

        // 2. Despachar emisión electrónica en segundo plano solo si no es nota de venta
        if ($validated['tipo_comprobante'] !== 'NOTA_VENTA') {
            EmitirComprobanteSunatJob::dispatch($pago->id);
        }

        $msgSuccess = $esRegularizacion
            ? "Pago de regularización registrado con éxito (Ref: {$reciboManualReferencia}). El asociado '{$padre->nombre_completo}' ha sido puesto al día."
            : "Pago registrado exitosamente. Comprobante {$pago->serie}-{$pago->numero} procesado.";

        return redirect()->route('pagos.index')->with('success', $msgSuccess);
    }

    public function ticket(Pago $pago)
    {
        $pago->load(['padreFamilia', 'empresa']);

        return view('pagos.ticket', compact('pago'));
    }
}
