<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carné APAFA Asociado - {{ $padre->nombre_completo }}</title>
    
    <!-- Google Fonts: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Share+Tech+Mono&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- JsBarcode CDN para Código de Barras Code 128 -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #f1f5f9;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
            padding: 30px 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .header-bar {
            max-width: 800px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .btn {
            background: #4f46e5;
            color: white;
            padding: 10px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn:hover {
            background: #4338ca;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #475569;
            box-shadow: none;
        }
        .btn-secondary:hover {
            background: #cbd5e1;
            color: #1e293b;
        }

        /* Contenedor de las dos caras */
        .cards-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: center;
        }

        /* Dimensiones exactas CR-80 estándar (85.6mm x 54mm) */
        .tarjeta-cr80 {
            width: 85.6mm;
            height: 54mm;
            background: #ffffff;
            border-radius: 4mm;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3.2mm 4.5mm;
            border: 1px solid #e2e8f0;
        }

        /* ================= CARA A: ANVERSO ================= */
        .cara-anverso {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-top: 4px solid #4f46e5;
        }

        .anverso-header {
            display: flex;
            align-items: center;
            gap: 7px;
            border-bottom: 1.5px solid #e2e8f0;
            padding-bottom: 2mm;
        }

        .insignia-logo {
            width: 9.5mm;
            height: 9.5mm;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 2mm;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 15px;
            flex-shrink: 0;
            box-shadow: 0 2px 4px rgba(79, 70, 229, 0.3);
        }

        .header-text {
            flex: 1;
            line-height: 1.15;
        }

        .header-text h1 {
            font-size: 8pt;
            font-weight: 800;
            color: #1e1b4b;
            text-transform: uppercase;
            letter-spacing: -0.2px;
        }

        .header-text p {
            font-size: 6pt;
            font-weight: 700;
            color: #4f46e5;
            text-transform: uppercase;
            margin-top: 1px;
        }

        .anverso-body {
            display: flex;
            gap: 7px;
            align-items: flex-start;
            margin: 1.5mm 0;
        }

        .foto-asociado {
            width: 16mm;
            height: 19mm;
            background: #f1f5f9;
            border: 1px dashed #cbd5e1;
            border-radius: 2mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 16px;
            flex-shrink: 0;
        }
        .foto-asociado span {
            font-size: 4.5pt;
            text-transform: uppercase;
            margin-top: 2px;
            font-weight: 700;
        }

        .datos-asociado {
            flex: 1;
            font-size: 6.5pt;
            line-height: 1.25;
            min-width: 0;
        }

        .dato-item {
            margin-bottom: 1.5px;
        }

        .dato-label {
            font-size: 5pt;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }

        .dato-valor {
            font-size: 7.2pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Bloque de Hijos a Cargo */
        .bloque-hijos {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 1.5mm 2mm;
            margin-top: 1.5px;
        }

        .hijos-titulo {
            font-size: 5pt;
            font-weight: 800;
            color: #4338ca;
            text-transform: uppercase;
            margin-bottom: 1px;
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .hijo-linea {
            font-size: 5.5pt;
            color: #1e293b;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
        }

        .anverso-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1.2mm;
            border-top: 1px solid #e2e8f0;
            margin-top: auto;
            flex-shrink: 0;
        }

        .correlativo-box {
            background: #ffe4e6;
            border: 1.5px solid #f43f5e;
            color: #9f1239;
            font-family: 'Share Tech Mono', monospace;
            font-size: 7pt;
            font-weight: 800;
            padding: 1.5px 7px;
            border-radius: 4px;
            letter-spacing: 0.6px;
            line-height: 1.1;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            box-shadow: 0 1px 2px rgba(244, 63, 94, 0.15);
            white-space: nowrap;
        }

        .tag-apafa {
            font-size: 5.5pt;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        /* ================= CARA B: REVERSO ================= */
        .cara-reverso {
            background: #ffffff;
            border-top: 4px solid #0f172a;
        }

        .reverso-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1.5px solid #e2e8f0;
            padding-bottom: 1.5mm;
        }

        .reverso-header h3 {
            font-size: 7.5pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .datos-apoderado {
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 5px;
            padding: 1.5mm 2.5mm;
            font-size: 6.5pt;
            line-height: 1.25;
            margin: 1.5mm 0;
        }

        .tecnologia-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin: 1mm 0;
        }

        .qr-box {
            width: 17mm;
            height: 17mm;
            flex-shrink: 0;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            padding: 1px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .barcode-box {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .barcode-box svg {
            width: 100%;
            height: 13mm;
        }

        .nota-legal {
            font-size: 4.8pt;
            color: #64748b;
            text-align: center;
            line-height: 1.2;
            border-top: 1px solid #f1f5f9;
            padding-top: 1mm;
            margin-top: auto;
        }

        /* Reglas de Impresión y Generación PDF */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .header-bar {
                display: none;
            }
            .cards-wrapper {
                gap: 10mm;
            }
            .tarjeta-cr80 {
                box-shadow: none;
                border: 1px solid #cbd5e1;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- Top Action Bar -->
    <div class="header-bar">
        <div>
            <h2 style="font-size: 18px; font-weight: 800; color: #1e293b;">
                Tarjeta Oficial de Asociado APAFA 2026
            </h2>
            <p style="font-size: 12px; color: #64748b;">
                Carné del Padre de Familia: <b>{{ $padre->nombre_completo }}</b> (DNI: {{ $padre->padre_dni }})
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('padres.index') }}" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Volver al Padrón
            </a>
            <button onclick="window.print()" class="btn">
                <i class="fa-solid fa-file-pdf"></i> Descargar / Imprimir en PDF
            </button>
        </div>
    </div>

    <!-- Cards Container (CR-80 Standard ID Card) -->
    <div class="cards-wrapper">
        
        <!-- 📌 CARA A: ANVERSO (Identificación Visual del Padre y sus Hijos) -->
        <div class="tarjeta-cr80 cara-anverso">
            <!-- Encabezado Institucional -->
            <div class="anverso-header">
                <div class="insignia-logo">
                    @if(!empty($padre->empresa->foto_institucion))
                        <img src="{{ asset($padre->empresa->foto_institucion) }}" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;" onerror="this.parentElement.innerHTML='<i class=\'fa-solid fa-graduation-cap\'></i>';">
                    @else
                        <i class="fa-solid fa-graduation-cap"></i>
                    @endif
                </div>
                <div class="header-text">
                    <h1>{{ $padre->empresa->nombre ?? 'I.E. NERY GARCIA ZARATE' }}</h1>
                    <p>Asociación de Padres de Familia (APAFA) - {{ $padre->empresa->periodo_apafa ?? '2026' }}</p>
                </div>
            </div>

            <!-- Bloque de Datos del Padre / Asociado -->
            <div class="anverso-body">
                <div class="foto-asociado">
                    <i class="fa-solid fa-user-tie"></i>
                    <span>Foto</span>
                </div>
                <div class="datos-asociado">
                    <div class="dato-item">
                        <span class="dato-label">Asociado ({{ $padre->parentesco_texto }}):</span>
                        <div class="dato-valor">{{ $padre->nombre_completo }}</div>
                        @if($padre->conyuge_nombres)
                            <div style="font-size: 5.5pt; color: #475569; margin-top: 1px; line-height: 1.1;">
                                <b>Cónyuge/2do:</b> {{ $padre->conyuge_nombres }} @if($padre->conyuge_dni) (DNI: {{ $padre->conyuge_dni }}) @endif
                            </div>
                        @endif
                    </div>
                    <div class="dato-item" style="display: flex; gap: 8px;">
                        <div>
                            <span class="dato-label">DNI:</span>
                            <div class="dato-valor font-mono" style="font-size: 6.8pt;">{{ $padre->padre_dni }}</div>
                        </div>
                        <div>
                            <span class="dato-label">Cargo Directivo:</span>
                            <div class="dato-valor" style="color: #4f46e5; font-size: 6.5pt;">
                                {{ $padre->cargo->nombre ?? 'ASOCIADO ORDINARIO' }}
                            </div>
                        </div>
                    </div>

                    <!-- Bloque de Alumnos a su Cargo -->
                    <div class="bloque-hijos">
                        <div class="hijos-titulo">
                            <i class="fa-solid fa-user-graduate"></i> Estudiantes a su Cargo ({{ $padre->estudiantes->count() }}):
                        </div>
                        @forelse($padre->estudiantes as $hijo)
                            <div class="hijo-linea">
                                &bull; {{ $hijo->nombres }} {{ $hijo->ape_pat }} <span style="color: #4f46e5; font-size: 5pt; font-weight: 700;">[{{ $hijo->parentesco ?? 'Hijo(a)' }}]</span> - <b>{{ $hijo->grado }} "{{ $hijo->seccion }}"</b>
                            </div>
                        @empty
                            <div class="hijo-linea" style="color: #94a3b8; font-style: italic;">
                                Sin estudiantes a su cargo registrados aún
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Elemento de Control -->
            <div class="anverso-footer">
                <span class="tag-apafa">
                    <i class="fa-solid fa-address-card" style="color: #6366f1;"></i> CARNÉ DE ASOCIADO
                </span>
                <div class="correlativo-box" title="Código Correlativo Oficial del Asociado">
                    <i class="fa-solid fa-hashtag" style="font-size: 5.5pt; opacity: 0.75;"></i>
                    <span>{{ $padre->correlativo ?? ('N° ' . str_pad($padre->id, 5, '0', STR_PAD_LEFT) . ' - ' . date('Y')) }}</span>
                </div>
            </div>
        </div>

        <!-- 📌 CARA B: REVERSO (Tecnología y Control de Asistencia) -->
        <div class="tarjeta-cr80 cara-reverso">
            <!-- Header Reverso -->
            <div class="reverso-header">
                <h3>Control de Asistencia Digital</h3>
                <span style="font-size: 6pt; font-weight: 700; color: #64748b; font-family: monospace;">
                    ID PADRE: {{ sprintf('%06d', $padre->id) }}
                </span>
            </div>

            <!-- Bloque de Datos de Contacto del Asociado -->
            <div class="datos-apoderado">
                <p>
                    <span style="font-weight: 700; color: #64748b;">ASOCIADO:</span>
                    <b style="color: #0f172a; text-transform: uppercase;">{{ $padre->nombre_completo }}</b>
                </p>
                <div style="display: flex; justify-content: space-between; margin-top: 1px;">
                    <p><span style="font-weight: 700; color: #64748b;">DNI:</span> <b class="font-mono">{{ $padre->padre_dni }}</b></p>
                    <p><span style="font-weight: 700; color: #64748b;">CELULAR:</span> <b>{{ $padre->celular ?? '-' }}</b></p>
                </div>
                @if($padre->direccion)
                    <p style="margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <span style="font-weight: 700; color: #64748b;">DIR:</span> {{ $padre->direccion }}
                    </p>
                @endif
            </div>

            <!-- Sección Tecnológica (Centralizada: QR + Código de Barras) -->
            <div class="tecnologia-row">
                <!-- Código QR (2.5 x 2.5 cm aprox) -->
                @php
                    $qrContent = "APAFA|DNI:{$padre->padre_dni}|ASOCIADO:{$padre->nombre_completo}|CORR:{$padre->correlativo}";
                    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=" . urlencode($qrContent);
                @endphp
                <div class="qr-box" title="Escanear con Smartphone para Asistencia en Asamblea">
                    <img src="{{ $qrUrl }}" alt="QR Asistencia" style="width: 100%; height: 100%;">
                </div>

                <!-- Código de Barras (Code 128 Horizontal para pistola escáner) -->
                <div class="barcode-box">
                    <svg id="barcode-canvas-padre"></svg>
                </div>
            </div>

            <!-- Nota Legal / Compromiso -->
            <div class="nota-legal">
                "Este carné es personal e intransferible. Es obligatorio presentarlo para el ingreso a Asambleas Generales, Faenas y Reuniones de Aula."
            </div>
        </div>

    </div>

    <!-- Generación del Código de Barras Code 128 basado en el DNI del Padre -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            JsBarcode("#barcode-canvas-padre", "{{ $padre->padre_dni ?? '73221758' }}", {
                format: "CODE128",
                width: 1.4,
                height: 38,
                displayValue: true,
                fontSize: 9,
                font: "Share Tech Mono",
                margin: 0
            });
        });
    </script>
</body>
</html>
