<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante {{ $pago->serie }}-{{ $pago->numero }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace;
        }
        body {
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        .ticket {
            background: #fff;
            width: 80mm;
            padding: 15px 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            font-size: 11px;
            line-height: 1.3;
            color: #111;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .divider {
            border-bottom: 1px dashed #444;
            margin: 8px 0;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            margin: 4px 0;
        }
        .qr-container {
            display: flex;
            justify-content: center;
            margin: 10px 0;
        }
        .actions {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
        }
        .btn {
            background: #4f46e5;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-family: sans-serif;
            font-size: 13px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .btn-back {
            background: #64748b;
        }
        @media print {
            body {
                background: none;
                padding: 0;
            }
            .ticket {
                width: 100%;
                box-shadow: none;
                padding: 0;
            }
            .actions {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="actions">
        <a href="{{ route('pagos.index') }}" class="btn btn-back">&larr; Volver</a>
        <button onclick="window.print()" class="btn">&#128424; Imprimir Ticket</button>
    </div>

    <div class="ticket">
        <div class="text-center">
            <p class="font-bold" style="font-size: 13px;">{{ $pago->empresa->nombre ?? 'I.E. APAFA' }}</p>
            <p style="font-size: 10px;">CÓD. MODULAR: {{ $pago->empresa->cod_modular ?? '000000' }}</p>
            <p style="font-size: 10px;">{{ $pago->empresa->ubicacion ?? 'PERÚ' }}</p>
            <p style="font-size: 10px;">TELÉFONO: {{ $pago->empresa->telefono ?? '-' }}</p>
        </div>

        <div class="divider"></div>

        <div class="text-center font-bold" style="font-size: 12px;">
            @if($pago->tipo_comprobante === 'FACTURA')
                FACTURA ELECTRÓNICA
            @elseif($pago->tipo_comprobante === 'BOLETA')
                BOLETA DE VENTA ELECTRÓNICA
            @else
                RECIBO DE CAJA INTERNO
            @endif
            <br>
            {{ $pago->serie }}-{{ $pago->numero }}
        </div>

        <div class="divider"></div>

        <div>
            <p><b>FECHA:</b> {{ $pago->created_at->format('d/m/Y H:i:s') }}</p>
            <p><b>DNI:</b> {{ $pago->padreFamilia->padre_dni ?? '-' }}</p>
            <p><b>ASOCIADO:</b> {{ $pago->padreFamilia->nombre_completo ?? 'N/A' }}</p>
            @if($pago->padreFamilia->direccion)
                <p><b>DIR:</b> {{ $pago->padreFamilia->direccion }}</p>
            @endif
        </div>

        <div class="divider"></div>

        <div class="font-bold item-row" style="font-size: 10px;">
            <span>DESCRIPCIÓN</span>
            <span>TOTAL</span>
        </div>

        <div class="divider"></div>

        <div class="item-row">
            <span style="max-width: 65%;">{{ $pago->pago_concepto }}</span>
            <span class="font-bold">S/ {{ number_format($pago->monto_pago, 2) }}</span>
        </div>

        @if($pago->modalidad && $pago->modalidad !== 'CONTADO')
            <div style="font-size: 10px; margin: 6px 0; background: #f3f4f6; padding: 4px; border-radius: 4px;">
                <p><b>MODALIDAD:</b> {{ $pago->modalidad }}</p>
                @if($pago->modalidad === 'FRACCIONADO')
                    <p><b>CUOTA:</b> {{ $pago->numero_cuota }} de {{ $pago->total_cuotas }}</p>
                    @if($pago->saldo_pendiente > 0)
                        <p><b>SALDO PENDIENTE:</b> S/ {{ number_format($pago->saldo_pendiente, 2) }}</p>
                    @endif
                @endif
            </div>
        @endif

        <div class="divider"></div>

        <div class="item-row font-bold" style="font-size: 13px;">
            <span>TOTAL A PAGAR:</span>
            <span>S/ {{ number_format($pago->monto_pago, 2) }}</span>
        </div>

        <div class="divider"></div>

        <!-- SUNAT QR Code & Hash -->
        <div class="text-center" style="font-size: 9px; color: #555;">
            @php
                $qrData = "20601234567|{$pago->tipo_comprobante}|{$pago->serie}|{$pago->numero}|0.00|{$pago->monto_pago}|" . $pago->created_at->format('Y-m-d') . "|1|" . ($pago->padreFamilia->padre_dni ?? '00000000') . "|" . ($pago->hash_cpe ?? 'HASH') . "|";
                $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=" . urlencode($qrData);
            @endphp

            <div class="qr-container">
                <img src="{{ $qrUrl }}" alt="Código QR SUNAT" width="100" height="100">
            </div>

            @if($pago->hash_cpe)
                <p><b>HASH SHA-256:</b></p>
                <p style="word-break: break-all;">{{ $pago->hash_cpe }}</p>
            @endif

            <p style="margin-top: 6px;">Representación impresa de la {{ $pago->tipo_comprobante }} Electrónica.</p>
            <p>¡Gracias por su colaboración con la APAFA!</p>
        </div>
    </div>

</body>
</html>
