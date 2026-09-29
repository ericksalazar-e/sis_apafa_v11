<?php

namespace App\Services;

use App\Models\ConfiguracionCpe;
use App\Models\Pago;
use Illuminate\Support\Facades\Storage;

class FacturacionService
{
    /**
     * Envía la solicitud al microservicio de facturación electrónica UBL 2.1.
     */
    public function procesarComprobante(Pago $pago): array
    {
        $padre = $pago->padreFamilia;
        $empresa = $pago->empresa;
        $empresaId = $pago->empresa_id ?? 1;

        // Cargar parámetros de configuración CPE del colegio/tenant
        $configCpe = ConfiguracionCpe::obtenerParaEmpresa($empresaId);

        $tipo = $pago->tipo_comprobante ?? 'BOLETA';
        $serie = $pago->serie ?? $configCpe->obtenerSerie($tipo);
        $numero = $pago->numero ?? sprintf('%06d', $pago->id);

        $payload = [
            'tipo_comprobante' => $tipo,
            'serie' => $serie,
            'numero' => $numero,
            'total' => (float) $pago->monto_pago,
            'ambiente' => $configCpe->ambiente ?? 'BETA',
            'emisor' => [
                'nombre' => $configCpe->razon_social ?: ($empresa->nombre ?? 'I.E. APAFA'),
                'razon_social' => $configCpe->razon_social ?: ($empresa->nombre ?? 'I.E. APAFA'),
                'ruc' => $configCpe->ruc_emisor ?: ($empresa->ruc ?? '20601234567'),
                'direccion' => $configCpe->direccion_fiscal ?: ($empresa->ubicacion ?? 'Av. Principal S/N'),
                'ubigeo' => $configCpe->ubigeo ?? '150101',
            ],
            'cliente' => [
                'dni' => $padre->padre_dni ?? '00000000',
                'ruc' => $padre->padre_dni ?? '00000000',
                'nombre' => $padre->nombre_completo ?? 'CLIENTE GENERAL',
                'direccion' => $padre->direccion ?? '',
            ],
            'items' => [
                [
                    'descripcion' => $pago->pago_concepto,
                    'monto' => (float) $pago->monto_pago,
                ],
            ],
            'fecha_emision' => $pago->created_at ? $pago->created_at->format('Y-m-d') : date('Y-m-d'),
            'hora_emision' => $pago->created_at ? $pago->created_at->format('H:i:s') : date('H:i:s'),
        ];

        // Llamada al microservicio UBL 2.1 aislado
        require_once base_path('facturacion-service/InvoiceEmitter.php');
        $emitter = new \FacturacionService\InvoiceEmitter;
        $resultado = $emitter->emitir($payload);

        // Guardar archivo XML UBL 2.1 en disco local para auditoría y consulta
        if (! empty($resultado['xml_content'])) {
            $xmlPath = "comprobantes_xml/{$serie}-{$numero}.xml";
            Storage::disk('local')->put($xmlPath, $resultado['xml_content']);
            $resultado['xml_path'] = $xmlPath;
        }

        return $resultado;
    }
}
