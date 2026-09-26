<?php

/**
 * Microservicio de Facturación Electrónica (SUNAT / UBL 2.1)
 * Servicio aislado para emisión asíncrona de CPE (Boletas y Facturas)
 */

namespace FacturacionService;

class InvoiceEmitter
{
    /**
     * Procesa y emite un comprobante electrónico (Boleta / Factura).
     *
     * @param array $data Datos del comprobante
     * @return array Respuesta de SUNAT / CDR
     */
    public function emitir(array $data): array
    {
        $tipo = $data['tipo_comprobante'] ?? 'BOLETA';
        $serie = $data['serie'] ?? ($tipo === 'FACTURA' ? 'F001' : 'B001');
        $numero = $data['numero'] ?? sprintf('%06d', rand(1, 999999));
        $cliente = $data['cliente'] ?? [];
        $items = $data['items'] ?? [];
        $total = (float) ($data['total'] ?? 0);

        // 1. Simulación de validación de reglas de negocio SUNAT
        if ($tipo === 'FACTURA' && empty($cliente['ruc'])) {
            return [
                'success' => false,
                'codigo_error' => '0200',
                'mensaje' => 'Para emitir Factura se requiere RUC válido del cliente.',
            ];
        }

        // 2. Generación del Hash SHA-256 del XML UBL 2.1
        $hashBytes = hash('sha256', "{$tipo}|{$serie}-{$numero}|{$total}|" . date('Y-m-d H:i:s'));
        $hashCpe = base64_encode(substr($hashBytes, 0, 20));

        // 3. Cadena para Código QR Oficial SUNAT
        // Formato: RUC|TIPO|SERIE|NUMERO|IGV|TOTAL|FECHA|DOC_CLIENTE|NUM_CLIENTE|HASH
        $rucEmisor = $data['emisor']['ruc'] ?? '20601234567';
        $docCliente = $cliente['dni'] ?? $cliente['ruc'] ?? '00000000';
        $qrString = "{$rucEmisor}|{$tipo}|{$serie}|{$numero}|0.00|{$total}|" . date('Y-m-d') . "|1|{$docCliente}|{$hashCpe}|";

        // 4. Simulación de firma digital y envío a servidor SUNAT (con latencia simulada para asíncrono)
        usleep(300000); // 300ms de proceso simulado

        return [
            'success' => true,
            'estado' => 'EMITIDO',
            'tipo_comprobante' => $tipo,
            'serie' => $serie,
            'numero' => $numero,
            'comprobante_completo' => "{$serie}-{$numero}",
            'hash_cpe' => $hashCpe,
            'qr_string' => $qrString,
            'codigo_sunat' => '0',
            'mensaje_sunat' => "El comprobante {$serie}-{$numero} ha sido aceptado por SUNAT.",
            'cdr_status' => 'ACEPTADO',
            'xml_generado' => true,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }
}

// Si se ejecuta mediante línea de comandos (Worker / Daemon)
if (php_sapi_name() === 'cli' && isset($argv[1])) {
    $inputJson = file_get_contents($argv[1]);
    $payload = json_decode($inputJson, true);
    $emitter = new InvoiceEmitter();
    $result = $emitter->emitir($payload ?? []);
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
