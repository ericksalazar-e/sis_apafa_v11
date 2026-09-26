<?php

namespace App\Jobs;

use App\Models\Pago;
use App\Services\FacturacionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EmitirComprobanteSunatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $pagoId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $pagoId)
    {
        $this->pagoId = $pagoId;
    }

    /**
     * Execute the job.
     */
    public function handle(FacturacionService $facturacionService): void
    {
        $pago = Pago::withoutGlobalScopes()->find($this->pagoId);
        if (!$pago) {
            return;
        }

        try {
            $resultado = $facturacionService->procesarComprobante($pago);

            if ($resultado['success'] ?? false) {
                $pago->update([
                    'estado_facturacion' => 'EMITIDO',
                    'serie' => $resultado['serie'] ?? $pago->serie,
                    'numero' => $resultado['numero'] ?? $pago->numero,
                    'hash_cpe' => $resultado['hash_cpe'] ?? null,
                    'mensaje_sunat' => $resultado['mensaje_sunat'] ?? 'Aceptado por SUNAT',
                ]);
            } else {
                $pago->update([
                    'estado_facturacion' => 'ERROR',
                    'mensaje_sunat' => $resultado['mensaje'] ?? 'Error desconocido al emitir comprobante',
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("Error emitiendo comprobante para Pago #{$this->pagoId}: " . $e->getMessage());
            $pago->update([
                'estado_facturacion' => 'ERROR',
                'mensaje_sunat' => 'Excepción: ' . $e->getMessage(),
            ]);
        }
    }
}
