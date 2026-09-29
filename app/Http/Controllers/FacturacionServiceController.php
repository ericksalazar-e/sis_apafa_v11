<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionCpe;
use App\Models\Empresa;
use App\Models\Pago;
use App\Services\FacturacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FacturacionServiceController extends Controller
{
    /**
     * Centro de Control y Monitoreo de Facturación Electrónica UBL 2.1
     */
    public function index(Request $request)
    {
        $empresaId = session('empresa_id') ?? auth()->user()->empresa_id ?? 1;
        $configuracion = ConfiguracionCpe::obtenerParaEmpresa($empresaId);
        $empresa = Empresa::find($empresaId);

        // Estadísticas de Facturación CPE (Tenant Actual)
        $totalEmitidos = Pago::where('estado_facturacion', 'EMITIDO')->count();
        $totalEnCola = Pago::whereIn('estado_facturacion', ['EN_COLA', 'PENDIENTE'])->count();
        $totalError = Pago::where('estado_facturacion', 'ERROR')->count();
        $totalRecaudado = Pago::where('estado_facturacion', 'EMITIDO')->sum('monto_pago');

        // Estado del Worker / Cola de tareas en base de datos
        $jobsEnCola = 0;

        try {
            $jobsEnCola = DB::table('jobs')->count();
        } catch (\Throwable $e) {
            $jobsEnCola = 0;
        }

        // Listado de comprobantes con filtros
        $query = Pago::with(['padreFamilia', 'empresa'])->latest();

        if ($request->filled('tipo')) {
            $query->where('tipo_comprobante', $request->tipo);
        }

        if ($request->filled('estado')) {
            $query->where('estado_facturacion', $request->estado);
        }

        if ($request->filled('buscar')) {
            $term = trim($request->buscar);
            $query->where(function ($q) use ($term) {
                $q->where('numero', 'like', "%{$term}%")
                    ->orWhere('serie', 'like', "%{$term}%")
                    ->orWhere('pago_concepto', 'like', "%{$term}%")
                    ->orWhereHas('padreFamilia', function ($qp) use ($term) {
                        $qp->where('padre_dni', 'like', "%{$term}%")
                            ->orWhere('padre_nombres', 'like', "%{$term}%")
                            ->orWhere('padre_ape_pat', 'like', "%{$term}%")
                            ->orWhere('padre_ape_mat', 'like', "%{$term}%");
                    });
            });
        }

        $comprobantes = $query->paginate(12)->withQueryString();

        return view('facturacion.index', compact(
            'configuracion',
            'empresa',
            'totalEmitidos',
            'totalEnCola',
            'totalError',
            'totalRecaudado',
            'jobsEnCola',
            'comprobantes'
        ));
    }

    /**
     * Actualiza la configuración de parámetros SUNAT UBL 2.1
     */
    public function guardarConfiguracion(Request $request)
    {
        $empresaId = session('empresa_id') ?? auth()->user()->empresa_id ?? 1;
        $configuracion = ConfiguracionCpe::obtenerParaEmpresa($empresaId);

        $validated = $request->validate([
            'ambiente' => 'required|in:BETA,PRODUCCION',
            'ruc_emisor' => ['required', 'string', 'size:11', 'regex:/^(10|20)[0-9]{9}$/'],
            'razon_social' => 'required|string|max:255',
            'nombre_comercial' => 'nullable|string|max:255',
            'direccion_fiscal' => 'required|string|max:255',
            'ubigeo' => 'nullable|string|max:10',
            'departamento' => 'nullable|string|max:100',
            'provincia' => 'nullable|string|max:100',
            'distrito' => 'nullable|string|max:100',
            'usuario_sol' => 'required|string|max:50',
            'clave_sol' => 'required|string|max:100',
            'serie_boleta' => 'required|string|max:10',
            'serie_factura' => 'required|string|max:10',
            'serie_nota_venta' => 'required|string|max:10',
            'correlativo_boleta' => 'required|integer|min:1',
            'correlativo_factura' => 'required|integer|min:1',
            'correlativo_nota_venta' => 'required|integer|min:1',
            'modo_emision' => 'required|in:ASINCRONO_COLA,SINCRONO_DIRECTO',
            'auto_envio_sunat' => 'nullable|boolean',
            'formato_impresion' => 'required|in:TICKET_80MM,A4',
            'leyenda_pie' => 'nullable|string|max:255',
            'certificado_file' => 'nullable|file|max:5120',
            'certificado_password' => 'nullable|string|max:100',
        ], [
            'ruc_emisor.regex' => 'El RUC debe tener 11 dígitos y empezar con 10 o 20.',
            'ruc_emisor.size' => 'El RUC debe tener exactamente 11 dígitos.',
        ]);

        // Procesar subida de Certificado Digital (.p12 / .pfx / .pem)
        if ($request->hasFile('certificado_file')) {
            $file = $request->file('certificado_file');
            $extension = $file->getClientOriginalExtension();
            $filename = "cert_empresa_{$empresaId}_".time().".{$extension}";
            $path = $file->storeAs('certificados_cpe', $filename, 'local');
            $validated['certificado_path'] = $path;
        }

        $validated['auto_envio_sunat'] = $request->has('auto_envio_sunat');

        $configuracion->update($validated);

        // Sincronizar RUC en modelo Empresa
        $empresa = Empresa::find($empresaId);
        if ($empresa) {
            $empresa->update([
                'ruc' => $validated['ruc_emisor'],
                'ubicacion' => $validated['direccion_fiscal'],
            ]);
        }

        return redirect()->route('facturacion.index')
            ->with('success', '¡Parámetros de Facturación UBL 2.1 y credenciales SUNAT actualizados correctamente!');
    }

    /**
     * Prueba de diagnóstico y simulación de emisión UBL 2.1 en vivo
     */
    public function testCpe(Request $request)
    {
        $empresaId = session('empresa_id') ?? auth()->user()->empresa_id ?? 1;
        $configuracion = ConfiguracionCpe::obtenerParaEmpresa($empresaId);

        $tipo = $request->input('tipo_comprobante', 'BOLETA');
        $monto = (float) $request->input('monto', 50.00);

        $payload = [
            'tipo_comprobante' => $tipo,
            'serie' => $configuracion->obtenerSerie($tipo),
            'numero' => sprintf('%06d', rand(100, 9999)),
            'total' => $monto,
            'ambiente' => $configuracion->ambiente,
            'emisor' => [
                'ruc' => $configuracion->ruc_emisor ?: '20601234567',
                'razon_social' => $configuracion->razon_social ?: 'I.E. APAFA',
                'direccion' => $configuracion->direccion_fiscal ?: 'Av. Principal S/N',
            ],
            'cliente' => [
                'dni' => '45678901',
                'ruc' => $tipo === 'FACTURA' ? '20456789012' : '45678901',
                'nombre' => 'PADRE DE FAMILIA PRUEBA UBL',
                'direccion' => 'Calle Los Álamos 123, Lima',
            ],
            'items' => [
                [
                    'descripcion' => 'Prueba de Emisión UBL 2.1 - Cuota Ordinaria',
                    'monto' => $monto,
                ],
            ],
            'fecha_emision' => date('Y-m-d'),
            'hora_emision' => date('H:i:s'),
        ];

        require_once base_path('facturacion-service/InvoiceEmitter.php');
        $emitter = new \FacturacionService\InvoiceEmitter;
        $resultado = $emitter->emitir($payload);

        if ($request->wantsJson()) {
            return response()->json($resultado);
        }

        return redirect()->route('facturacion.index')
            ->with('test_result', $resultado)
            ->with('success', "Prueba de emisión completada. Comprobante {$resultado['comprobante_completo']} validado con Hash UBL: {$resultado['hash_cpe']}");
    }

    /**
     * Procesa de forma inmediata todos los comprobantes que se encuentren en cola (EN_COLA)
     */
    public function procesarCola(FacturacionService $facturacionService)
    {
        $empresaId = session('empresa_id') ?? auth()->user()->empresa_id ?? 1;
        $pagosEnCola = Pago::where('empresa_id', $empresaId)
            ->whereIn('estado_facturacion', ['EN_COLA', 'PENDIENTE'])
            ->get();

        $procesados = 0;
        $errores = 0;

        foreach ($pagosEnCola as $pago) {
            try {
                $resultado = $facturacionService->procesarComprobante($pago);
                if ($resultado['success'] ?? false) {
                    $pago->update([
                        'estado_facturacion' => 'EMITIDO',
                        'serie' => $resultado['serie'] ?? $pago->serie,
                        'numero' => $resultado['numero'] ?? $pago->numero,
                        'hash_cpe' => $resultado['hash_cpe'] ?? null,
                        'mensaje_sunat' => $resultado['mensaje_sunat'] ?? 'Aceptado por SUNAT',
                        'xml_url' => $resultado['xml_path'] ?? null,
                    ]);
                    $procesados++;
                } else {
                    $pago->update([
                        'estado_facturacion' => 'ERROR',
                        'mensaje_sunat' => $resultado['mensaje'] ?? 'Error al procesar en SUNAT',
                    ]);
                    $errores++;
                }
            } catch (\Throwable $e) {
                Log::error("Error procesando cola de pago #{$pago->id}: ".$e->getMessage());
                $errores++;
            }
        }

        // También ejecutar worker de Laravel para limpiar la tabla de jobs
        try {
            Artisan::call('queue:work', [
                '--once' => true,
                '--stop-when-empty' => true,
            ]);
        } catch (\Throwable $e) {
            // Ignorar si no hay worker disponible
        }

        return redirect()->route('facturacion.index')
            ->with('success', "Cola procesada con éxito: {$procesados} comprobantes emitidos correctamente".($errores > 0 ? " ({$errores} con error)." : '.'));
    }

    /**
     * Reintenta la emisión de un comprobante que tuvo error o quedó pendiente
     */
    public function reintentar(Pago $pago, FacturacionService $facturacionService)
    {
        try {
            $resultado = $facturacionService->procesarComprobante($pago);

            if ($resultado['success'] ?? false) {
                $pago->update([
                    'estado_facturacion' => 'EMITIDO',
                    'serie' => $resultado['serie'] ?? $pago->serie,
                    'numero' => $resultado['numero'] ?? $pago->numero,
                    'hash_cpe' => $resultado['hash_cpe'] ?? null,
                    'mensaje_sunat' => $resultado['mensaje_sunat'] ?? 'Aceptado por SUNAT',
                    'xml_url' => $resultado['xml_path'] ?? null,
                ]);

                return redirect()->back()
                    ->with('success', "Comprobante {$pago->serie}-{$pago->numero} emitido satisfactoriamente a SUNAT.");
            } else {
                $pago->update([
                    'estado_facturacion' => 'ERROR',
                    'mensaje_sunat' => $resultado['mensaje'] ?? 'Error devuelto por servicio UBL 2.1',
                ]);

                return redirect()->back()
                    ->with('error', 'No se pudo emitir: '.($resultado['mensaje'] ?? 'Error desconocido'));
            }
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Excepción al emitir: '.$e->getMessage());
        }
    }

    /**
     * Descarga o visualiza el archivo XML UBL 2.1 del comprobante
     */
    public function verXml(Pago $pago)
    {
        $filename = "{$pago->serie}-{$pago->numero}.xml";

        // Si ya está guardado en disco
        if ($pago->xml_url && Storage::disk('local')->exists($pago->xml_url)) {
            $xmlContent = Storage::disk('local')->get($pago->xml_url);

            return response($xmlContent, 200, [
                'Content-Type' => 'application/xml',
                'Content-Disposition' => "inline; filename=\"{$filename}\"",
            ]);
        }

        // Si aún no está en disco, generarlo al vuelo
        require_once base_path('facturacion-service/InvoiceEmitter.php');
        $emitter = new \FacturacionService\InvoiceEmitter;
        $pago->load(['padreFamilia', 'empresa']);

        $config = ConfiguracionCpe::obtenerParaEmpresa($pago->empresa_id);
        $tipo = $pago->tipo_comprobante ?? 'BOLETA';
        $tipoDocSunat = $tipo === 'FACTURA' ? '01' : ($tipo === 'BOLETA' ? '03' : '00');
        $hashCpe = $pago->hash_cpe ?: base64_encode(substr(hash('sha256', $pago->id.time()), 0, 20));

        $xml = $emitter->generarXmlUbl21([
            'tipo' => $tipo,
            'tipo_doc_sunat' => $tipoDocSunat,
            'serie' => $pago->serie,
            'numero' => $pago->numero,
            'fecha_emision' => $pago->created_at ? $pago->created_at->format('Y-m-d') : date('Y-m-d'),
            'hora_emision' => $pago->created_at ? $pago->created_at->format('H:i:s') : date('H:i:s'),
            'emisor' => [
                'ruc' => $config->ruc_emisor ?: '20601234567',
                'razon_social' => $config->razon_social ?: 'I.E. APAFA',
                'direccion' => $config->direccion_fiscal ?: 'Av. Principal S/N',
            ],
            'cliente' => [
                'nombre' => $pago->padreFamilia->nombre_completo ?? 'CLIENTE GENERAL',
            ],
            'doc_cliente' => $pago->padreFamilia->padre_dni ?? '00000000',
            'tipo_doc_cliente' => strlen($pago->padreFamilia->padre_dni ?? '') === 11 ? '6' : '1',
            'items' => [
                [
                    'descripcion' => $pago->pago_concepto,
                    'monto' => (float) $pago->monto_pago,
                ],
            ],
            'total' => (float) $pago->monto_pago,
            'hash_cpe' => $hashCpe,
        ]);

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }
}
