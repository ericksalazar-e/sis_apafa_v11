<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarjeta APAFA - {{ $estudiante->nombre_completo }}</title>
    
    <!-- Google Fonts: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Share+Tech+Mono&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- JsBarcode CDN para renderizar Código de Barras Code 128 real -->
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
            padding: 3.5mm 4.5mm;
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
            gap: 8px;
            border-bottom: 1.5px solid #e2e8f0;
            padding-bottom: 2.5mm;
        }

        .insignia-logo {
            width: 10.5mm;
            height: 10.5mm;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 2mm;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
            flex-shrink: 0;
            box-shadow: 0 2px 4px rgba(79, 70, 229, 0.3);
        }

        .header-text {
            flex: 1;
            line-height: 1.15;
        }

        .header-text h1 {
            font-size: 8.5pt;
            font-weight: 800;
            color: #1e1b4b;
            text-transform: uppercase;
            letter-spacing: -0.2px;
        }

        .header-text p {
            font-size: 6.5pt;
            font-weight: 700;
            color: #4f46e5;
            text-transform: uppercase;
            margin-top: 1px;
        }

        .anverso-body {
            display: flex;
            gap: 8px;
            align-items: center;
            margin: 2mm 0;
        }

        .foto-estudiante {
            width: 17mm;
            height: 21mm;
            background: #f1f5f9;
            border: 1px dashed #cbd5e1;
            border-radius: 2mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 18px;
            flex-shrink: 0;
        }
        .foto-estudiante span {
            font-size: 5pt;
            text-transform: uppercase;
            margin-top: 2px;
            font-weight: 600;
        }

        .datos-estudiante {
            flex: 1;
            font-size: 7pt;
            line-height: 1.35;
        }

        .dato-item {
            margin-bottom: 1.5px;
        }

        .dato-label {
            font-size: 5.5pt;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .dato-valor {
            font-size: 7.5pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
        }

        .anverso-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-top: 1.5mm;
            border-top: 1px solid #f1f5f9;
        }

        .correlativo-box {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #e11d48;
            font-family: 'Share Tech Mono', monospace;
            font-size: 7.5pt;
            font-weight: 800;
            padding: 1.5px 6px;
            border-radius: 4px;
            letter-spacing: 0.5px;
        }

        .tag-apafa {
            font-size: 6pt;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
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
                Tarjeta de Asistencia del Estudiante 2026
            </h2>
            <p style="font-size: 12px; color: #64748b;">
                Carné Escolar de: <b>{{ $estudiante->nombre_completo }}</b> (DNI: {{ $estudiante->dni }})
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('estudiantes.index') }}" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Volver al Padrón
            </a>
            <button onclick="window.print()" class="btn">
                <i class="fa-solid fa-file-pdf"></i> Descargar / Imprimir en PDF
            </button>
        </div>
    </div>

    <!-- Cards Container (CR-80 Standard ID Card) -->
    <div class="cards-wrapper">
        
        <!-- 📌 CARA A: ANVERSO (Identificación Visual) -->
        <div class="tarjeta-cr80 cara-anverso">
            <!-- Encabezado Institucional -->
            <div class="anverso-header">
                <div class="insignia-logo">
                    @if(!empty($estudiante->empresa->foto_institucion))
                        <img src="{{ asset($estudiante->empresa->foto_institucion) }}" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    @else
                        <i class="fa-solid fa-graduation-cap"></i>
                    @endif
                </div>
                <div class="header-text">
                    <h1>{{ $estudiante->empresa->nombre ?? 'I.E. NERY GARCIA ZARATE' }}</h1>
                    <p>Control de Asistencia Escolar - {{ $estudiante->empresa->periodo_apafa ?? '2026' }}</p>
                </div>
            </div>

            <!-- Bloque de Datos del Estudiante -->
            <div class="anverso-body">
                <div class="foto-estudiante">
                    <i class="fa-solid fa-user"></i>
                    <span>Foto</span>
                </div>
                <div class="datos-estudiante">
                    <div class="dato-item">
                        <span class="dato-label">Estudiante:</span>
                        <div class="dato-valor">{{ $estudiante->nombre_completo }}</div>
                    </div>
                    <div class="dato-item" style="margin-top: 1.5px;">
                        <span class="dato-label">Grado y Sección:</span>
                        <div class="dato-valor" style="color: #4f46e5;">{{ $estudiante->grado }} "{{ $estudiante->seccion }}"</div>
                    </div>
                    <div class="dato-item">
                        <span class="dato-label">Vínculo con Apoderado:</span>
                        <div class="dato-valor" style="font-size: 6.5pt; color: #0284c7; font-weight: 800;">
                            <i class="fa-solid fa-people-roof"></i> {{ $estudiante->parentesco ?? 'HIJO(A)' }}
                        </div>
                    </div>
                    <div class="dato-item">
                        <span class="dato-label">Nivel / Turno:</span>
                        <div class="dato-valor" style="font-size: 6pt;">{{ $estudiante->nivel }} - {{ $estudiante->turno }}</div>
                    </div>
                </div>
            </div>

            <!-- Elemento de Control -->
            <div class="anverso-footer">
                <span class="tag-apafa">TARJETA DE ASISTENCIA</span>
                <div class="correlativo-box">
                    {{ $estudiante->correlativo ?? 'N° 00245 - 2026' }}
                </div>
            </div>
        </div>

        <!-- 📌 CARA B: REVERSO (Tecnología y Control de Asistencia) -->
        <div class="tarjeta-cr80 cara-reverso">
            <!-- Header Reverso -->
            <div class="reverso-header">
                <h3>Control de Asistencia Diaria</h3>
                <span style="font-size: 6pt; font-weight: 700; color: #64748b; font-family: monospace;">
                    ALUMNO DNI: {{ $estudiante->dni }}
                </span>
            </div>

            <!-- Bloque de Datos del Padre / Apoderado -->
            <div class="datos-apoderado">
                <p>
                    <span style="font-weight: 700; color: #64748b;">APODERADO ({{ $estudiante->parentesco ?? 'TUTOR' }}):</span>
                    <b style="color: #0f172a; text-transform: uppercase;">{{ $estudiante->padreFamilia->nombre_completo ?? 'N/A' }}</b>
                </p>
                <div style="display: flex; justify-content: space-between; margin-top: 2px;">
                    <p><span style="font-weight: 700; color: #64748b;">DNI APODERADO:</span> <b class="font-mono">{{ $estudiante->padreFamilia->padre_dni ?? '00000000' }}</b></p>
                    <p><span style="font-weight: 700; color: #64748b;">TEL. EMERGENCIA:</span> <b>{{ $estudiante->padreFamilia->celular ?? '-' }}</b></p>
                </div>
            </div>

            <!-- Sección Tecnológica (Centralizada: QR + Código de Barras) -->
            <div class="tecnologia-row">
                <!-- Código QR (2.5 x 2.5 cm aprox) -->
                @php
                    $dniApoderado = $estudiante->padreFamilia->padre_dni ?? '00000000';
                    $qrContent = "ASISTENCIA_ESCOLAR|DNI:{$estudiante->dni}|ALUMNO:{$estudiante->nombre_completo}|APODERADO:{$dniApoderado}";
                    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($qrContent);
                @endphp
                <div class="qr-box" title="Escanear con Smartphone para Asistencia del Alumno">
                    <img src="{{ $qrUrl }}" alt="QR Asistencia Alumno" style="width: 100%; height: 100%;">
                </div>

                <!-- Código de Barras (Code 128 Horizontal para pistola escáner con DNI del Alumno) -->
                <div class="barcode-box">
                    <svg id="barcode-canvas-estudiante"></svg>
                </div>
            </div>

            <!-- Nota Legal / Compromiso -->
            <div class="nota-legal">
                "Esta tarjeta es de uso obligatorio para el registro diario de asistencia escolar en la puerta de ingreso de la institución educativa."
            </div>
        </div>

    </div>

    <!-- Generación del Código de Barras Code 128 con el DNI del Alumno -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            JsBarcode("#barcode-canvas-estudiante", "{{ $estudiante->dni ?? '60976643' }}", {
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
