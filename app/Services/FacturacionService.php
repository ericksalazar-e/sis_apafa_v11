<?php

namespace App\Services;

use App\Models\Pago;

class FacturacionService
{
    /**
     * Envía la solicitud al microservicio de facturación electrónica.
     */
    public function procesarComprobante(Pago $pago): array
    {
        $padre = $pago->padreFamilia;
        $empresa = $pago->empresa;

        $payload = [
            'tipo_comprobante' => $pago->tipo_comprobante ?? 'BOLETA',
            'serie' => $pago->serie ?? ($pago->tipo_comprobante === 'FACTURA' ? 'F001' : 'B001'),
            'numero' => $pago->numero ?? sprintf('%06d', $pago->id),
            'total' => (float) $pago->monto_pago,
            'emisor' => [
                'nombre' => $empresa->nombre ?? 'I.E. APAFA',
                'cod_modular' => $empresa->cod_modular ?? '000000',
                'ruc' => '20609876543',
            ],
            'cliente' => [
                'dni' => $padre->padre_dni ?? '00000000',
                'nombre' => $padre->nombre_completo ?? 'CLIENTE GENERAL',
                'direccion' => $padre->direccion ?? '',
            ],
            'items' => [
                [
                    'descripcion' => $pago->pago_concepto,
                    'monto' => (float) $pago->monto_pago,
                ]
            ],
        ];

        // Llamada al microservicio aislado
        require_once base_path('facturacion-service/InvoiceEmitter.php');
        $emitter = new \FacturacionService\InvoiceEmitter();
        
        return $emitter->emitir($payload);
    }
}
