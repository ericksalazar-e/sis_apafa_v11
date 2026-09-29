<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50 scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual de Usuario Interactivo - SIS-APAFA V11</title>

    <!-- Favicon del Navegador (Logo Dinámico Institucional) -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-header {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .step-badge {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }
        @media print {
            .no-print { display: none !important; }
            .print-break-inside { break-inside: avoid; }
            body { background: white !important; color: black !important; }
            .shadow-xl, .shadow-2xl, .shadow-sm { box-shadow: none !important; }
        }
    </style>
</head>
<body class="min-h-full flex flex-col text-slate-800 antialiased selection:bg-indigo-600 selection:text-white">

    <!-- Top Sticky Navigation & Role Filter Bar -->
    <header class="sticky top-0 z-50 glass-header border-b border-slate-200/80 shadow-xs transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20 gap-4">
                
                <!-- Logo & Brand -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-600 text-white flex items-center justify-center text-xl shadow-lg shadow-indigo-600/30">
                        <i class="fa-solid fa-book-open-reader"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-base sm:text-lg font-black text-slate-900 tracking-tight leading-none">
                                SIS-APAFA
                            </h1>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-indigo-100 text-indigo-700 border border-indigo-200">
                                Guía Visual
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Manual de Operación de Usuario</p>
                    </div>
                </div>

                <!-- Live Search Box -->
                <div class="hidden md:flex flex-1 max-w-md mx-4 relative no-print">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </div>
                    <input type="text" id="manual-search" oninput="filtrarManual(this.value)" 
                           placeholder="¿Qué deseas hacer? (ej. cobrar cuota, registrar alumno, escanear QR...)" 
                           class="w-full pl-10 pr-4 py-2 bg-slate-100/80 border border-slate-200/90 rounded-2xl text-xs font-semibold text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all shadow-inner">
                </div>

                <!-- Action Buttons: Print & Back to System -->
                <div class="flex items-center gap-2 no-print">
                    <button onclick="window.print()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center gap-2 transition-all">
                        <i class="fa-solid fa-print"></i>
                        <span class="hidden sm:inline">Imprimir / PDF</span>
                    </button>
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center gap-2 shadow-md shadow-indigo-600/20 transition-all">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Ir al Sistema</span>
                    </a>
                </div>
            </div>

            <!-- Role Selector Filter Chips (Client-side interactive) -->
            <div class="py-2.5 border-t border-slate-100 flex items-center gap-2 overflow-x-auto no-scrollbar no-print">
                <span class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider whitespace-nowrap pl-1">
                    Ver por mi rol:
                </span>
                <button onclick="filtrarPorRol('TODOS')" class="btn-rol px-3 py-1 rounded-xl text-xs font-bold transition-all bg-slate-900 text-white shadow-xs" data-rol="TODOS">
                    <i class="fa-solid fa-layer-group mr-1.5"></i> Todos los Módulos
                </button>
                <button onclick="filtrarPorRol('SECRETARIO')" class="btn-rol px-3 py-1 rounded-xl text-xs font-bold transition-all bg-white text-slate-600 hover:bg-slate-100 border border-slate-200" data-rol="SECRETARIO">
                    <i class="fa-solid fa-id-card mr-1.5 text-blue-600"></i> Secretario (Padrón y Alumnos)
                </button>
                <button onclick="filtrarPorRol('TESORERO')" class="btn-rol px-3 py-1 rounded-xl text-xs font-bold transition-all bg-white text-slate-600 hover:bg-slate-100 border border-slate-200" data-rol="TESORERO">
                    <i class="fa-solid fa-cash-register mr-1.5 text-emerald-600"></i> Tesorero (Cobros POS y Caja)
                </button>
                <button onclick="filtrarPorRol('VOCAL_OPERADOR')" class="btn-rol px-3 py-1 rounded-xl text-xs font-bold transition-all bg-white text-slate-600 hover:bg-slate-100 border border-slate-200" data-rol="VOCAL_OPERADOR">
                    <i class="fa-solid fa-qrcode mr-1.5 text-amber-600"></i> Vocal (Portería y Escáner QR)
                </button>
                <button onclick="filtrarPorRol('ADMIN')" class="btn-rol px-3 py-1 rounded-xl text-xs font-bold transition-all bg-white text-slate-600 hover:bg-slate-100 border border-slate-200" data-rol="ADMIN">
                    <i class="fa-solid fa-crown mr-1.5 text-purple-600"></i> Presidente / Administrador
                </button>
                <button onclick="filtrarPorRol('COMITE_VIGILANCIA')" class="btn-rol px-3 py-1 rounded-xl text-xs font-bold transition-all bg-white text-slate-600 hover:bg-slate-100 border border-slate-200" data-rol="COMITE_VIGILANCIA">
                    <i class="fa-solid fa-eye mr-1.5 text-rose-600"></i> Consejo de Vigilancia (Auditor)
                </button>
                <a href="#ficha-tecnica" class="px-3 py-1 rounded-xl text-xs font-bold transition-all bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 whitespace-nowrap ml-auto">
                    <i class="fa-solid fa-microchip mr-1.5 text-indigo-600"></i> Ficha Técnica & Extensiones
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">

        <!-- Hero Section: Bienvenida al Usuario Final -->
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white p-8 sm:p-10 shadow-2xl border border-indigo-900/40">
            <div class="absolute -right-20 -bottom-20 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="relative z-10 max-w-3xl space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-bold border border-indigo-400/30">
                    <i class="fa-solid fa-circle-check text-emerald-400"></i> Guía Rápida para el Operador
                </div>
                <h2 class="text-2xl sm:text-4xl font-black tracking-tight text-white leading-tight">
                    ¿Cómo manejar el sistema <span class="bg-gradient-to-r from-indigo-400 to-violet-400 bg-clip-text text-transparent">SIS-APAFA</span> de forma fácil y sin errores?
                </h2>
                <p class="text-sm sm:text-base text-slate-300 font-medium leading-relaxed">
                    Este manual interactivo te muestra paso a paso dónde hacer clic, cómo atender a los padres de familia en ventanilla, cómo registrar la asistencia con pistola o celular en portería y cómo mantener al día las cuentas de la institución.
                </p>

                <!-- Quick KPI Badges -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                    <div class="bg-white/5 backdrop-blur-md p-3 rounded-2xl border border-white/10 text-center">
                        <p class="text-lg font-black text-indigo-300">< 2 seg</p>
                        <p class="text-[10px] text-slate-400 font-bold uppercase">Cobro en POS</p>
                    </div>
                    <div class="bg-white/5 backdrop-blur-md p-3 rounded-2xl border border-white/10 text-center">
                        <p class="text-lg font-black text-emerald-300">< 1 seg</p>
                        <p class="text-[10px] text-slate-400 font-bold uppercase">Escaneo QR Puerta</p>
                    </div>
                    <div class="bg-white/5 backdrop-blur-md p-3 rounded-2xl border border-white/10 text-center">
                        <p class="text-lg font-black text-amber-300">1 Familia</p>
                        <p class="text-[10px] text-slate-400 font-bold uppercase">= 1 Cuota Única</p>
                    </div>
                    <div class="bg-white/5 backdrop-blur-md p-3 rounded-2xl border border-white/10 text-center">
                        <p class="text-lg font-black text-cyan-300">80 mm</p>
                        <p class="text-[10px] text-slate-400 font-bold uppercase">Ticket Térmico SUNAT</p>
                    </div>
                </div>
            </div>
        </section>


        <!-- ========================================================================= -->
        <!-- FICHA TÉCNICA: STACK TECNOLÓGICO, HERRAMIENTAS Y EXTENSIONES PHP          -->
        <!-- ========================================================================= -->
        <section id="ficha-tecnica" class="modulo-card print-break-inside bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-sm space-y-8" data-roles="TODOS,ADMIN,SECRETARIO,TESORERO,VOCAL_OPERADOR,COMITE_VIGILANCIA">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-5 gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-slate-900 to-indigo-900 text-white flex items-center justify-center font-bold text-xl shadow-md shadow-indigo-900/20">
                        <i class="fa-solid fa-microchip"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-xl font-black text-slate-900 tracking-tight">Ficha Técnica: Stack, Herramientas y Extensiones</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Producción HA
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Especificaciones completas de desarrollo, contenedores, extensiones PHP nativas y automatización</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 font-mono">PHP 8.2 / 8.3</span>
                    <span class="px-3 py-1 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 font-mono">Laravel 11.x</span>
                    <span class="px-3 py-1 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 font-mono">Docker HA</span>
                </div>
            </div>

            <!-- 1. Tecnologías y Stack Base -->
            <div class="space-y-4">
                <h4 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-server text-indigo-600"></i> 1. Tecnologías y Arquitectura Base
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-indigo-300 transition-all space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                                <i class="fa-brands fa-laravel text-red-500 text-base"></i> Laravel 11.x & PHP 8.2/8.3
                            </span>
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md bg-red-100 text-red-700">Backend</span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Framework principal con arquitectura Multi-Tenant nativa (separación de instituciones por <code class="font-mono bg-white px-1 py-0.5 rounded text-indigo-600 font-bold">empresa_id</code>), colas asíncronas, Eloquent ORM y políticas de autorización por roles.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-indigo-300 transition-all space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-database text-blue-600 text-base"></i> MySQL 8.0 / MariaDB
                            </span>
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md bg-blue-100 text-blue-700">Base de Datos</span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Motor transaccional InnoDB con integridad referencial ACID, bloqueos pesimistas para caja en ventanilla (<code class="font-mono bg-white px-1 py-0.5 rounded text-indigo-600 font-bold">lockForUpdate</code>), juegos de caracteres UTF8mb4 y healthcheck nativo.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-indigo-300 transition-all space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-bolt text-red-600 text-base"></i> Redis 7 Alpine
                            </span>
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md bg-red-100 text-red-700">Caché & Colas</span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Almacenamiento en memoria ultrarrápido para workers de cola asíncronos (<code class="font-mono bg-white px-1 py-0.5 rounded text-indigo-600 font-bold">EmitirComprobanteSunatJob</code>), caché distribuido y sesiones concurrentes de alta velocidad.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-indigo-300 transition-all space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-network-wired text-emerald-600 text-base"></i> Nginx 1.31+ Alpine HA
                            </span>
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-700">Proxy Inverso</span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Balanceador de carga upstream con algoritmo <code class="font-mono bg-white px-1 py-0.5 rounded text-indigo-600 font-bold">least_conn</code>, keep-alive persistente, buffers FastCGI de 64k, compresión Gzip y Rate Limiting por IP para mitigar ataques DDoS.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-indigo-300 transition-all space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                                <i class="fa-brands fa-docker text-cyan-600 text-base"></i> Docker & Compose v2
                            </span>
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md bg-cyan-100 text-cyan-700">Contenedores</span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Contenedores aislados multi-stage (PHP-FPM, Nginx, MySQL, Redis y phpMyAdmin). Soporta escalamiento horizontal en 1 comando: <code class="font-mono bg-white px-1 py-0.5 rounded text-indigo-600 font-bold">docker compose up -d --scale app=3</code>.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-indigo-300 transition-all space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-receipt text-amber-600 text-base"></i> Microservicio SUNAT UBL 2.1
                            </span>
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md bg-amber-100 text-amber-700">Facturación</span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Módulo fiscal aislado en <code class="font-mono bg-white px-1 py-0.5 rounded text-indigo-600 font-bold">facturacion-service/</code> para generación de XML UBL 2.1, firmado digital, cálculo de Hash SHA-256 y Código QR oficial de ticket térmico 80mm.
                        </p>
                    </div>

                </div>
            </div>

            <!-- 2. Extensiones de PHP Requeridas y Compiladas -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-brands fa-php text-indigo-600 text-base"></i> 2. Extensiones PHP Nativas Requeridas y Compiladas
                    </h4>
                    <span class="text-xs font-bold text-slate-500">10 extensiones críticas optimizadas</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    
                    <div class="p-3.5 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-indigo-400 transition-all space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-black text-xs text-indigo-600">pdo_mysql</span>
                            <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                        </div>
                        <p class="text-[11px] font-bold text-slate-800">Conectividad MySQL</p>
                        <p class="text-[10px] text-slate-500 leading-tight">Consultas preparadas, alta velocidad y transacciones seguras con la base de datos.</p>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-indigo-400 transition-all space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-black text-xs text-indigo-600">gd (Freetype)</span>
                            <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                        </div>
                        <p class="text-[11px] font-bold text-slate-800">Renderizado Gráfico</p>
                        <p class="text-[10px] text-slate-500 leading-tight">Generación dinámica de carnés escolares con foto, credenciales oficiales y sellos.</p>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-indigo-400 transition-all space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-black text-xs text-indigo-600">intl (ICU)</span>
                            <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                        </div>
                        <p class="text-[11px] font-bold text-slate-800">Internacionalización</p>
                        <p class="text-[10px] text-slate-500 leading-tight">Formateo de moneda nacional (S/. PEN), fechas peruanas y ordenamiento alfabético.</p>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-indigo-400 transition-all space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-black text-xs text-indigo-600">mbstring</span>
                            <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                        </div>
                        <p class="text-[11px] font-bold text-slate-800">Cadenas Multibyte</p>
                        <p class="text-[10px] text-slate-500 leading-tight">Soporte completo para tildes, ñ, nombres compuestos en español y caracteres UTF-8.</p>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-indigo-400 transition-all space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-black text-xs text-indigo-600">zip / libzip</span>
                            <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                        </div>
                        <p class="text-[11px] font-bold text-slate-800">Compresión ZIP</p>
                        <p class="text-[10px] text-slate-500 leading-tight">Carga masiva de estudiantes desde archivos Excel/CSV y descarga de respaldos.</p>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-indigo-400 transition-all space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-black text-xs text-indigo-600">bcmath</span>
                            <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                        </div>
                        <p class="text-[11px] font-bold text-slate-800">Precisión Financiera</p>
                        <p class="text-[10px] text-slate-500 leading-tight">Cálculos matemáticos exactos en cobros de cuotas, multas e IGV sin pérdida de decimales.</p>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-indigo-400 transition-all space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-black text-xs text-indigo-600">exif</span>
                            <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                        </div>
                        <p class="text-[11px] font-bold text-slate-800">Metadatos de Foto</p>
                        <p class="text-[10px] text-slate-500 leading-tight">Orientación automática de fotografías tomadas con celulares para carnés y sellos.</p>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-indigo-400 transition-all space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-black text-xs text-indigo-600">pcntl</span>
                            <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                        </div>
                        <p class="text-[11px] font-bold text-slate-800">Control de Procesos</p>
                        <p class="text-[10px] text-slate-500 leading-tight">Manejo de señales de sistema y workers de colas en segundo plano sin bloquear el POS.</p>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-indigo-400 transition-all space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-black text-xs text-indigo-600">redis (PECL)</span>
                            <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                        </div>
                        <p class="text-[11px] font-bold text-slate-800">Driver Nativo C</p>
                        <p class="text-[10px] text-slate-500 leading-tight">Comunicación directa en milisegundos con Redis para colas y sesiones concurrentes.</p>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-indigo-400 transition-all space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-black text-xs text-indigo-600">opcache</span>
                            <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                        </div>
                        <p class="text-[11px] font-bold text-slate-800">Caché de Bytecode</p>
                        <p class="text-[10px] text-slate-500 leading-tight">Precompilación del código PHP en RAM para respuestas de página en menos de 15ms.</p>
                    </div>

                </div>
            </div>

            <!-- 3. Herramientas de Desarrollo, Calidad y Automatización -->
            <div class="space-y-4">
                <h4 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-screwdriver-wrench text-indigo-600"></i> 3. Herramientas de Calidad, Testing y Automatización
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    
                    <div class="p-4 rounded-2xl bg-slate-900 text-white space-y-2">
                        <div class="flex items-center justify-between text-indigo-400">
                            <span class="font-bold text-xs"><i class="fa-solid fa-wand-magic-sparkles mr-1.5"></i> Laravel Pint</span>
                            <span class="text-[10px] font-mono bg-white/10 px-1.5 py-0.5 rounded text-indigo-300">PSR-12</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Linter y formateador automático con reglas estrictas configuradas en <code class="font-mono text-indigo-200">pint.json</code>. Asegura 0 inconsistencias de estilo.
                        </p>
                        <div class="text-[10px] font-mono text-emerald-400 pt-1">
                            $ ./vendor/bin/pint --test
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-900 text-white space-y-2">
                        <div class="flex items-center justify-between text-indigo-400">
                            <span class="font-bold text-xs"><i class="fa-solid fa-shield-halved mr-1.5"></i> PHPStan / Larastan</span>
                            <span class="text-[10px] font-mono bg-white/10 px-1.5 py-0.5 rounded text-indigo-300">Nivel 5</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Motor de análisis estático configurado en <code class="font-mono text-indigo-200">phpstan.neon</code>. Inspecciona llamadas a métodos, tipos y previene bugs antes de llegar a producción.
                        </p>
                        <div class="text-[10px] font-mono text-emerald-400 pt-1">
                            $ ./vendor/bin/phpstan analyse
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-900 text-white space-y-2">
                        <div class="flex items-center justify-between text-indigo-400">
                            <span class="font-bold text-xs"><i class="fa-solid fa-vial-circle-check mr-1.5"></i> PHPUnit 11 & Pest</span>
                            <span class="text-[10px] font-mono bg-white/10 px-1.5 py-0.5 rounded text-emerald-300">80 Tests OK</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Batería con 703 aserciones que certifican el aislamiento multi-tenant, emisión de comprobantes UBL 2.1, control de caja y padrón electoral.
                        </p>
                        <div class="text-[10px] font-mono text-emerald-400 pt-1">
                            $ php artisan test
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-900 text-white space-y-2">
                        <div class="flex items-center justify-between text-indigo-400">
                            <span class="font-bold text-xs"><i class="fa-solid fa-code-commit mr-1.5"></i> Git Hooks & CI/CD</span>
                            <span class="text-[10px] font-mono bg-white/10 px-1.5 py-0.5 rounded text-indigo-300">GitHub Actions</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Hooks en <code class="font-mono text-indigo-200">.git/hooks/</code> (pre-commit y pre-push) y pipeline <code class="font-mono text-indigo-200">ci.yml</code> con matriz PHP 8.2/8.3, auditoría de seguridad y build check.
                        </p>
                        <div class="text-[10px] font-mono text-emerald-400 pt-1">
                            $ git push origin main
                        </div>
                    </div>

                </div>
            </div>

            <!-- 4. Resiliencia, Monitoreo y Tolerancia a Fallos -->
            <div class="p-5 rounded-2xl bg-gradient-to-r from-indigo-50/70 to-slate-50 border border-indigo-100 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h5 class="text-xs font-black text-indigo-950 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-heart-pulse text-indigo-600"></i> Autorecuperación (Self-Healing) y Respaldos Automatizados
                    </h5>
                    <p class="text-xs text-slate-600">
                        El sistema incluye un centinela activo en <code class="font-mono font-bold text-indigo-700">scripts/health-watchdog.ps1</code> que supervisa <code class="font-mono font-bold text-indigo-700">/healthz</code> cada 30 segundos y reinicia contenedores ante contingencias. Asimismo, el comando <code class="font-mono font-bold text-indigo-700">php artisan apafa:backup --keep=14</code> realiza copias comprimidas automáticas todos los días a las 02:00 am.
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ url('/healthz') }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-indigo-700 font-bold text-xs flex items-center gap-1.5 shadow-xs transition-all">
                        <i class="fa-solid fa-stethoscope"></i> Ver /healthz en Vivo
                    </a>
                </div>
            </div>
        </section>


        <!-- ========================================================================= -->
        <!-- SECCIÓN 1: INICIO DE SESIÓN Y ACCESO RÁPIDO                              -->
        <!-- ========================================================================= -->
        <section class="modulo-card print-break-inside bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-sm space-y-6" data-roles="TODOS,ADMIN,SECRETARIO,TESORERO,VOCAL_OPERADOR,COMITE_VIGILANCIA">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg">
                        1
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Inicio de Sesión y Acceso por Roles</h3>
                        <p class="text-xs text-slate-500 font-medium">Cómo ingresar a tu terminal de trabajo según tu cargo</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700">Ruta: /login</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <div class="space-y-4">
                    <h4 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-key text-indigo-600"></i> Pasos para Ingresar:
                    </h4>
                    <ol class="space-y-3 text-xs text-slate-600">
                        <li class="flex items-start gap-3">
                            <span class="step-badge text-white font-black w-5 h-5 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 mt-0.5">1</span>
                            <div>
                                <strong>Abre el navegador:</strong> Ingresa a <code class="bg-slate-100 px-1.5 py-0.5 rounded text-indigo-700 font-bold font-mono">http://127.0.0.1:8000/login</code>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="step-badge text-white font-black w-5 h-5 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 mt-0.5">2</span>
                            <div>
                                <strong>Opción A (Ingreso con Contraseña):</strong> Digita tu usuario institucional y tu contraseña registrada.
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="step-badge text-white font-black w-5 h-5 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 mt-0.5">3</span>
                            <div>
                                <strong>Opción B (Botón de Acceso Rápido):</strong> En la parte inferior verás los botones de acceso directo según tu rol (Presidente, Tesorero, Secretario, Vocal o Vigilancia) para iniciar sesión en 1 clic.
                            </div>
                        </li>
                    </ol>

                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200/80 text-amber-900 text-xs flex items-start gap-3">
                        <i class="fa-solid fa-lightbulb text-amber-500 text-sm mt-0.5 flex-shrink-0"></i>
                        <div>
                            <strong>Redirección Inteligente:</strong> Si ingresas con el rol de <strong>Vocal / Operador</strong>, el sistema te llevará directamente a la pantalla de escáner en portería para que no pierdas tiempo navegando entre menús.
                        </div>
                    </div>
                </div>

                <!-- Visual Interactive Mockup of the Login Screen -->
                <div class="bg-slate-900 rounded-3xl p-6 text-white border border-slate-800 shadow-xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-mono text-slate-400 pl-2">Pantalla de Ingreso</span>
                        </div>
                        <span class="text-[10px] bg-indigo-500/20 text-indigo-300 px-2 py-0.5 rounded-full font-bold">Oficial</span>
                    </div>

                    <div class="space-y-3">
                        <div class="bg-slate-950 p-3 rounded-xl border border-slate-800">
                            <p class="text-[10px] uppercase font-bold text-slate-400">Usuario o Correo</p>
                            <p class="text-xs font-mono text-indigo-300">secretario@apafa.edu.pe</p>
                        </div>
                        <div class="bg-slate-950 p-3 rounded-xl border border-slate-800">
                            <p class="text-[10px] uppercase font-bold text-slate-400">Contraseña</p>
                            <p class="text-xs font-mono text-slate-500">••••••••••••</p>
                        </div>
                        <div class="pt-2 grid grid-cols-3 gap-2">
                            <a href="{{ route('login.quick', 'SECRETARIO') }}" class="p-2 text-center rounded-xl bg-blue-600/20 text-blue-300 border border-blue-500/30 text-[10px] font-bold hover:bg-blue-600/30 transition-all">
                                <i class="fa-solid fa-id-card block text-sm mb-1"></i> Secretario
                            </a>
                            <a href="{{ route('login.quick', 'TESORERO') }}" class="p-2 text-center rounded-xl bg-emerald-600/20 text-emerald-300 border border-emerald-500/30 text-[10px] font-bold hover:bg-emerald-600/30 transition-all">
                                <i class="fa-solid fa-cash-register block text-sm mb-1"></i> Tesorero
                            </a>
                            <a href="{{ route('login.quick', 'VOCAL_OPERADOR') }}" class="p-2 text-center rounded-xl bg-amber-600/20 text-amber-300 border border-amber-500/30 text-[10px] font-bold hover:bg-amber-600/30 transition-all">
                                <i class="fa-solid fa-qrcode block text-sm mb-1"></i> Vocal Puerta
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <!-- ========================================================================= -->
        <!-- SECCIÓN 2: PADRÓN DE PADRES Y TARJETA CON CÓDIGO DE BARRAS                -->
        <!-- ========================================================================= -->
        <section class="modulo-card print-break-inside bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-sm space-y-6" data-roles="TODOS,ADMIN,SECRETARIO,COMITE_VIGILANCIA">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                        2
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Padrón de Padres de Familia (Asociados)</h3>
                        <p class="text-xs text-slate-500 font-medium">Registro de apoderados, cónyuge y emisión de Tarjeta de Asociado CR-80</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700">Ruta: /padres</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <div class="space-y-4">
                    <h4 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-user-plus text-blue-600"></i> ¿Cómo registrar un nuevo Padre de Familia?
                    </h4>
                    <ol class="space-y-3 text-xs text-slate-600">
                        <li class="flex items-start gap-3">
                            <span class="step-badge text-white font-black w-5 h-5 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 mt-0.5">1</span>
                            <div>
                                Haz clic en el botón azul <strong class="text-indigo-600">+ Nuevo Padre de Familia</strong>.
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="step-badge text-white font-black w-5 h-5 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 mt-0.5">2</span>
                            <div>
                                <strong>Ingresa el DNI del padre:</strong> Debe tener exactamente 8 dígitos. El sistema verifica automáticamente que no exista duplicado.
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="step-badge text-white font-black w-5 h-5 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 mt-0.5">3</span>
                            <div>
                                <strong>Datos del Cónyuge (Opcional):</strong> Registra a la madre o conviviente para que ambos tengan derecho a representación legal en asambleas.
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="step-badge text-white font-black w-5 h-5 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 mt-0.5">4</span>
                            <div>
                                <strong>Imprimir Tarjeta CR-80:</strong> Haz clic en el icono <i class="fa-solid fa-id-card text-blue-600"></i> en la fila del padre para imprimir su credencial oficial con código de barras listo para plastificar.
                            </div>
                        </li>
                    </ol>
                </div>

                <!-- Interactive Visual Card: Simulador Tarjeta de Padre CR-80 -->
                <div class="bg-gradient-to-br from-indigo-900 to-slate-900 rounded-3xl p-6 text-white border border-indigo-500/30 shadow-2xl relative overflow-hidden">
                    <div class="flex items-center justify-between border-b border-white/10 pb-3 mb-4">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-graduation-cap text-indigo-400"></i>
                            <span class="text-[11px] font-black tracking-wider uppercase">I.E. NERY GARCIA ZARATE</span>
                        </div>
                        <span class="text-[10px] font-black px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">ASOCIADO ACTIVO</span>
                    </div>

                    <div class="flex gap-4 items-center">
                        <div class="w-16 h-20 rounded-2xl bg-slate-800 border-2 border-indigo-400/40 flex flex-col items-center justify-center text-slate-400">
                            <i class="fa-solid fa-user-tie text-2xl text-indigo-300"></i>
                            <span class="text-[8px] font-bold mt-1">FOTO</span>
                        </div>
                        <div class="flex-1 space-y-1">
                            <p class="text-[10px] text-slate-400 font-bold uppercase">Apoderado Titular</p>
                            <p class="text-sm font-black text-white leading-tight">QUISPE FLORES, MARIO CESAR</p>
                            <p class="text-xs font-mono text-indigo-300">DNI: 45678901</p>
                            <p class="text-[10px] text-slate-300">Hijos a cargo: 2 estudiantes matriculados</p>
                        </div>
                    </div>

                    <!-- Barcode Visual Representation -->
                    <div class="mt-4 pt-3 border-t border-white/10 bg-white p-2 rounded-xl text-center">
                        <div class="inline-block font-mono tracking-widest text-slate-950 font-black text-lg py-1">
                            ||| | |||| | ||| || |||| ||
                        </div>
                        <p class="text-[9px] font-mono text-slate-600 font-bold">*45678901*</p>
                    </div>
                </div>
            </div>
        </section>


        <!-- ========================================================================= -->
        <!-- SECCIÓN 3: MATRÍCULA Y CARGA MASIVA EXCEL / CSV                           -->
        <!-- ========================================================================= -->
        <section class="modulo-card print-break-inside bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-sm space-y-6" data-roles="TODOS,ADMIN,SECRETARIO,DIRECTOR">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-lg">
                        3
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Alumnos Matriculados y Carga Masiva</h3>
                        <p class="text-xs text-slate-500 font-medium">Importación rápida desde Excel (.xlsx) y generación de Carné Escolar QR</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700">Ruta: /estudiantes</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <div class="space-y-4">
                    <h4 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-file-excel text-emerald-600"></i> ¿Cómo importar la lista de alumnos en 1 minuto?
                    </h4>
                    <ol class="space-y-3 text-xs text-slate-600">
                        <li class="flex items-start gap-3">
                            <span class="step-badge text-white font-black w-5 h-5 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 mt-0.5">1</span>
                            <div>
                                En la pantalla de alumnos, haz clic en <strong class="text-emerald-700 font-bold">Descargar Plantilla Excel (.xlsx)</strong>.
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="step-badge text-white font-black w-5 h-5 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 mt-0.5">2</span>
                            <div>
                                <strong>Llena los datos de los alumnos:</strong> DNI, nombres, apellidos, nivel (Primaria/Secundaria), grado y sección.
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="step-badge text-white font-black w-5 h-5 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 mt-0.5">3</span>
                            <div>
                                <strong>Columna DNI_APODERADO (Clave):</strong> Si escribes el DNI del padre en esa columna, el sistema lo asociará automáticamente a la familia correspondiente.
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="step-badge text-white font-black w-5 h-5 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 mt-0.5">4</span>
                            <div>
                                Haz clic en el botón <strong class="text-indigo-600 font-bold">Importar Excel</strong>, selecciona el archivo y presiona Procesar. ¡Listo! Cientos de alumnos se crean al instante.
                            </div>
                        </li>
                    </ol>
                </div>

                <!-- Interactive Visual Card: Simulador Carné Escolar QR -->
                <div class="bg-gradient-to-br from-purple-950 via-slate-900 to-indigo-950 rounded-3xl p-6 text-white border border-purple-500/30 shadow-2xl relative">
                    <div class="flex items-center justify-between border-b border-white/10 pb-3 mb-4">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-school text-purple-400"></i>
                            <span class="text-[11px] font-black tracking-wider uppercase">CARNÉ ESCOLAR 2026</span>
                        </div>
                        <span class="text-[10px] font-black px-2 py-0.5 rounded bg-purple-500/30 text-purple-300 border border-purple-400/40">PRIMARIA • 1RO "A"</span>
                    </div>

                    <div class="flex gap-4 items-center">
                        <div class="w-16 h-20 rounded-2xl bg-white/10 border-2 border-purple-400/40 flex flex-col items-center justify-center text-purple-300">
                            <i class="fa-solid fa-user-graduate text-2xl"></i>
                            <span class="text-[8px] font-bold mt-1">ALUMNO</span>
                        </div>
                        <div class="flex-1 space-y-1">
                            <p class="text-xs font-black text-white leading-tight uppercase">QUISPE MENDOZA, CARLOS DANIEL</p>
                            <p class="text-xs font-mono text-purple-300">DNI: 71234501</p>
                            <p class="text-[10px] text-slate-300">Turno: Mañana • Tol: 08:00 AM</p>
                            <p class="text-[10px] text-emerald-400 font-semibold"><i class="fa-solid fa-shield-halved"></i> Apoderado Vinculado</p>
                        </div>
                    </div>

                    <!-- QR Box Mockup -->
                    <div class="mt-4 pt-3 border-t border-white/10 bg-white p-3 rounded-2xl text-slate-900 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-slate-950 text-white rounded-lg flex items-center justify-center text-xl">
                                <i class="fa-solid fa-qrcode"></i>
                            </div>
                            <div class="text-left">
                                <p class="text-xs font-black">CÓDIGO QR ÚNICO</p>
                                <p class="text-[10px] text-slate-500">Escaneo instantáneo en puerta</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-mono font-bold bg-slate-100 px-2 py-1 rounded text-slate-700">71234501</span>
                    </div>
                </div>
            </div>
        </section>


        <!-- ========================================================================= -->
        <!-- SECCIÓN 4: TERMINAL DE PORTERÍA Y ESCÁNER EN TIEMPO REAL                 -->
        <!-- ========================================================================= -->
        <section class="modulo-card print-break-inside bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-sm space-y-6" data-roles="TODOS,VOCAL_OPERADOR,SECRETARIO,ADMIN">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg">
                        4
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Terminal de Portería (Escaneo de Asistencia)</h3>
                        <p class="text-xs text-slate-500 font-medium">Control de ingreso a alta velocidad con pistola de código de barras o cámara</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700">Ruta: /asistencia-estudiantes</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <div class="space-y-4">
                    <h4 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-bolt text-amber-500"></i> ¿Cómo funciona el escaneo diario en la puerta?
                    </h4>
                    <ol class="space-y-3 text-xs text-slate-600">
                        <li class="flex items-start gap-3">
                            <span class="step-badge text-white font-black w-5 h-5 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 mt-0.5">1</span>
                            <div>
                                <strong>Opción Pistola USB:</strong> Mantén el cursor en la caja de texto blanca y apunta la pistola al carné del alumno. Al presionar el gatillo, el sistema registra el ingreso automáticamente.
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="step-badge text-white font-black w-5 h-5 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 mt-0.5">2</span>
                            <div>
                                <strong>Opción Celular / Tablet:</strong> Haz clic en <strong class="text-indigo-600">Activar Cámara</strong>. Acerca el código QR a la pantalla y se leerá al instante sin necesidad de instalar ninguna app.
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="step-badge text-white font-black w-5 h-5 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 mt-0.5">3</span>
                            <div>
                                <strong>Cálculo Automático de Tardanza:</strong> Si el alumno ingresa antes de la hora configurada (ej. 08:00 AM), se marca en <strong class="text-emerald-600">PUNTUAL (Verde)</strong>. Si pasa el límite, se marca en <strong class="text-amber-600">TARDANZA (Amarillo)</strong>.
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="step-badge text-white font-black w-5 h-5 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 mt-0.5">4</span>
                            <div>
                                <strong>Control de Salidas:</strong> Al terminar las clases, cambia la pestaña a "Salidas" y escanea nuevamente para registrar la hora en que el estudiante se retira del plantel.
                            </div>
                        </li>
                    </ol>
                </div>

                <!-- Interactive Visual Card: Simulador de Pantalla de Escaneo -->
                <div class="bg-slate-950 rounded-3xl p-6 text-white border border-slate-800 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <span class="flex items-center gap-2 text-xs font-bold text-amber-400">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span> Terminal de Ingreso en Vivo
                        </span>
                        <span class="text-[10px] font-mono text-slate-400">07:54:12 AM</span>
                    </div>

                    <!-- Scanner Input Mockup -->
                    <div class="bg-slate-900 p-4 rounded-2xl border-2 border-indigo-500/50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-barcode text-2xl text-indigo-400"></i>
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-bold">Esperando lectura de carné...</p>
                                <p class="text-xs font-mono text-white">71234501 <span class="animate-pulse">|</span></p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/30">
                            ✓ PUNTUAL
                        </span>
                    </div>

                    <!-- Last Scanned Student Details -->
                    <div class="bg-slate-900/60 p-3.5 rounded-2xl border border-slate-800 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600/30 text-indigo-300 flex items-center justify-center text-lg font-bold">
                            <i class="fa-solid fa-circle-check text-emerald-400"></i>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-xs font-bold text-white truncate">QUISPE MENDOZA, CARLOS DANIEL</p>
                            <p class="text-[10px] text-slate-400">1ro "A" Primaria • Entrada registrada: 07:54 AM</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <!-- ========================================================================= -->
        <!-- SECCIÓN 5: COBRANZA EN VENTANILLA (POS) Y TICKETS TÉRMICOS                -->
        <!-- ========================================================================= -->
        <section class="modulo-card print-break-inside bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-sm space-y-6" data-roles="TODOS,TESORERO,ADMIN">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg">
                        5
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Cobro en Ventanilla (POS) y Ticket Térmico</h3>
                        <p class="text-xs text-slate-500 font-medium">Cobro de cuotas, fraccionamiento y emisión de comprobantes con QR SUNAT</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">Ruta: /pagos</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <div class="space-y-4">
                    <h4 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-cash-register text-emerald-600"></i> ¿Cómo cobrar una cuota o multa al padre?
                    </h4>
                    <ol class="space-y-3 text-xs text-slate-600">
                        <li class="flex items-start gap-3">
                            <span class="step-badge text-white font-black w-5 h-5 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 mt-0.5">1</span>
                            <div>
                                <strong>Buscar al Padre:</strong> Digita el DNI o escanea la tarjeta plástica del padre. El sistema mostrará su deuda en pantalla.
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="step-badge text-white font-black w-5 h-5 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 mt-0.5">2</span>
                            <div>
                                <strong>Seleccionar Concepto:</strong> Marca la casilla del aporte o multa que va a pagar (ej. Cuota Ordinaria 2026: S/ 50.00).
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="step-badge text-white font-black w-5 h-5 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 mt-0.5">3</span>
                            <div>
                                <strong>Pago Total o en Partes (Fraccionamiento):</strong> Si el padre solo tiene S/ 20.00 hoy, escribe 20.00 en el monto a pagar. El sistema guardará el abono y mantendrá el saldo de S/ 30.00 pendiente para la próxima.
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="step-badge text-white font-black w-5 h-5 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 mt-0.5">4</span>
                            <div>
                                <strong>Emitir e Imprimir Ticket:</strong> Presiona <strong class="text-emerald-600 font-bold">Cobrar e Imprimir</strong>. Saldrá el ticket térmico de 80mm de inmediato con su código QR para el padre.
                            </div>
                        </li>
                    </ol>
                </div>

                <!-- Interactive Visual Card: Simulador Ticket Térmico 80mm -->
                <div class="bg-white rounded-3xl p-6 text-slate-900 border-2 border-dashed border-slate-300 shadow-xl max-w-sm mx-auto font-mono text-xs space-y-2">
                    <div class="text-center pb-2 border-b border-dashed border-slate-400">
                        <p class="font-black text-sm uppercase">I.E. NERY GARCIA ZARATE</p>
                        <p class="text-[10px] text-slate-600">RUC: 20456789123 • APAFA</p>
                        <p class="font-bold text-[11px] mt-1 bg-slate-100 py-0.5 rounded">BOLETA DE VENTA: B001-000452</p>
                    </div>

                    <div class="text-[11px] space-y-1">
                        <p><strong>FECHA:</strong> 26/09/2026 10:45 AM</p>
                        <p><strong>PADRE:</strong> QUISPE FLORES, MARIO</p>
                        <p><strong>DNI:</strong> 45678901</p>
                        <p><strong>HIJO(S):</strong> CARLOS DANIEL (1RO A)</p>
                    </div>

                    <div class="pt-2 border-t border-dashed border-slate-400">
                        <div class="flex justify-between font-bold text-[11px]">
                            <span>CUOTA ORDINARIA 2026 (PARCIAL)</span>
                            <span>S/ 20.00</span>
                        </div>
                        <div class="flex justify-between text-[10px] text-slate-500">
                            <span>Saldo Restante Pendiente:</span>
                            <span>S/ 30.00</span>
                        </div>
                    </div>

                    <div class="pt-2 border-t-2 border-slate-900 flex justify-between font-black text-sm">
                        <span>TOTAL PAGADO:</span>
                        <span>S/ 20.00</span>
                    </div>

                    <!-- QR and Legal Footer -->
                    <div class="pt-3 text-center border-t border-dashed border-slate-400 flex flex-col items-center">
                        <div class="w-16 h-16 bg-slate-900 text-white flex items-center justify-center text-3xl rounded-lg mb-1">
                            <i class="fa-solid fa-qrcode"></i>
                        </div>
                        <p class="text-[9px] text-slate-500">Representación impresa UBL 2.1 SUNAT</p>
                        <p class="text-[8px] text-slate-400 font-bold">Consulte con su DNI en el portal público</p>
                    </div>
                </div>
            </div>
        </section>


        <!-- ========================================================================= -->
        <!-- SECCIÓN 6: ASAMBLEAS GENERALES Y MULTAS AUTOMÁTICAS                      -->
        <!-- ========================================================================= -->
        <section class="modulo-card print-break-inside bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-sm space-y-6" data-roles="TODOS,SECRETARIO,VOCAL_OPERADOR,ADMIN,DIRECTOR">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-lg">
                        6
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Asambleas Generales y Control de Quórum</h3>
                        <p class="text-xs text-slate-500 font-medium">Control de quórum en vivo, cartas poder y liquidación de multas</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700">Ruta: /reuniones</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-xs text-slate-600">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <span class="step-badge text-white font-black w-6 h-6 rounded-full flex items-center justify-center text-xs">1</span>
                    <h4 class="font-extrabold text-slate-900 text-sm">Crear Convocatoria</h4>
                    <p>Digita el título de la asamblea (ej. Aprobación del Balance Económico), fecha, hora y el monto de la multa por inasistencia (ej. S/ 20.00).</p>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <span class="step-badge text-white font-black w-6 h-6 rounded-full flex items-center justify-center text-xs">2</span>
                    <h4 class="font-extrabold text-slate-900 text-sm">Escaneo en Puerta</h4>
                    <p>Conforme van ingresando los padres, el vocal de puerta escanea la tarjeta o ingresa el DNI. El medidor de quórum en pantalla sube en tiempo real.</p>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <span class="step-badge text-white font-black w-6 h-6 rounded-full flex items-center justify-center text-xs">3</span>
                    <h4 class="font-extrabold text-slate-900 text-sm">Cerrar y Multar</h4>
                    <p>Al finalizar la asamblea, presiona "Culminar Reunión". El sistema generará automáticamente la multa solo a quienes faltaron sin justificación.</p>
                </div>
            </div>
        </section>


        <!-- ========================================================================= -->
        <!-- SECCIÓN 7: PREGUNTAS FRECUENTES Y TRUCOS PARA EL OPERADOR                 -->
        <!-- ========================================================================= -->
        <section class="modulo-card print-break-inside bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-sm space-y-6" data-roles="TODOS,ADMIN,SECRETARIO,TESORERO,VOCAL_OPERADOR,COMITE_VIGILANCIA">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 rounded-2xl bg-cyan-50 text-cyan-600 flex items-center justify-center font-bold text-lg">
                    7
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">Preguntas Frecuentes y Trucos de Velocidad</h3>
                    <p class="text-xs text-slate-500 font-medium">Solución rápida a situaciones comunes en ventanilla y portería</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                    <h5 class="font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-circle-question text-indigo-600"></i> ¿Un padre con 3 hijos en el colegio debe pagar 3 cuotas?
                    </h5>
                    <p class="text-slate-600">
                        <strong>No.</strong> La Ley N° 28628 prohíbe el cobro múltiple por familia. El sistema asocia a los 3 hermanos al mismo padre y solo genera una cuota ordinaria de APAFA.
                    </p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                    <h5 class="font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-circle-question text-indigo-600"></i> ¿La pistola de código de barras no envía el formulario?
                    </h5>
                    <p class="text-slate-600">
                        Asegúrate de que el lector óptico USB esté configurado con el sufijo "Enter" automático (CR). En los manuales de la pistola viene un código de barras de configuración rápida para activarlo.
                    </p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                    <h5 class="font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-circle-question text-indigo-600"></i> ¿Cómo consulta el padre de familia su estado de cuenta?
                    </h5>
                    <p class="text-slate-600">
                        El padre no necesita usuario ni contraseña: simplemente ingresa a la portada del colegio <code class="bg-white px-1 py-0.5 rounded font-bold">http://127.0.0.1:8000/</code>, digita su DNI y ve sus pagos y asistencias al instante.
                    </p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                    <h5 class="font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-circle-question text-indigo-600"></i> ¿Qué hacer si un alumno olvida su carné escolar?
                    </h5>
                    <p class="text-slate-600">
                        En la terminal de portería puedes escribir directamente el número de DNI del estudiante en el teclado y presionar Enter para marcar su asistencia sin problemas.
                    </p>
                </div>

                <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 space-y-1.5 md:col-span-2">
                    <h5 class="font-extrabold text-amber-950 flex items-center gap-2">
                        <i class="fa-solid fa-calendar-check text-amber-600"></i> ¿Qué pasa si se termina el año escolar y cómo inicio el nuevo registro de matrícula y padres?
                    </h5>
                    <p class="text-slate-700 leading-relaxed">
                        <strong>Solución integrada:</strong> No necesita borrar la base de datos ni volver a escribir los alumnos. Vaya a <strong>Configuración &gt; 6. Cierre y Apertura de Nuevo Año Escolar</strong> (o en <strong>Alumnos &gt; "Promoción de Año"</strong>).<br>
                        1. El sistema avanza automáticamente a los alumnos al grado siguiente (ej. de 1° a 2°, de 2° a 3°, etc.) conservando su sección.<br>
                        2. Los estudiantes de 5° de secundaria pasan automáticamente a estado <strong>"EGRESADO"</strong>.<br>
                        3. Se genera la nueva cuota anual oficial de APAFA para todos los padres asociados.<br>
                        4. Para los nuevos ingresantes (1° grado, Inicial o traslados), simplemente use el botón <strong>"Matricular Estudiante"</strong> o la <strong>"Carga Masiva Excel"</strong>. El historial contable y los comprobantes de años anteriores permanecen 100% seguros y respaldados.
                    </p>
                </div>

                <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 space-y-1.5 md:col-span-2">
                    <h5 class="font-extrabold text-emerald-950 flex items-center gap-2">
                        <i class="fa-solid fa-vault text-emerald-600"></i> ¿Qué pasa si hay un monto en caja que funcionaba sin el sistema y ahora se quiere agregar?
                    </h5>
                    <p class="text-slate-700 leading-relaxed">
                        <strong>Solución consolidada (2 vías oficiales):</strong><br>
                        1. <strong>Saldo Inicial Institucional (Fondo de Entrega de Cargo):</strong> Ingrese a <strong>Configuración &gt; 5. Fondo de Apertura y Saldo Inicial</strong>. Registre el monto en soles (ej. S/ 3,500.00), la fecha y el número de Acta de Entrega de Recepción o Arqueo físico. Este monto se integrará automáticamente al balance de ingresos auditados de Vigilancia, al presupuesto del POA y a la rendición pública de cuentas.<br>
                        2. <strong>Cobros previos a asociados por talonario manual:</strong> Si un padre ya pagó en un talonario de recibos antes de instalar el software, vaya al <strong>POS / Cobro en Ventanilla</strong>, marque la casilla <strong>"Regularizar pago previo / Talonario físico"</strong> e ingrese el número de recibo de referencia. El sistema dejará al asociado al día sin duplicar efectivo.
                    </p>
                </div>
            </div>
        </section>

    </main>

    <!-- Client-side Interactive Search & Role Filter Script -->
    <script>
        function filtrarPorRol(rol) {
            // Actualizar apariencia de botones
            document.querySelectorAll('.btn-rol').forEach(btn => {
                if (btn.getAttribute('data-rol') === rol) {
                    btn.classList.remove('bg-white', 'text-slate-600', 'border', 'border-slate-200');
                    btn.classList.add('bg-slate-900', 'text-white', 'shadow-xs');
                } else {
                    btn.classList.remove('bg-slate-900', 'text-white', 'shadow-xs');
                    btn.classList.add('bg-white', 'text-slate-600', 'border', 'border-slate-200');
                }
            });

            // Filtrar módulos visibles en pantalla
            const modulos = document.querySelectorAll('.modulo-card');
            modulos.forEach(mod => {
                const rolesPermitidos = mod.getAttribute('data-roles') || '';
                if (rol === 'TODOS' || rolesPermitidos.includes(rol)) {
                    mod.classList.remove('hidden');
                } else {
                    mod.classList.add('hidden');
                }
            });
        }

        function filtrarManual(query) {
            const cleanQuery = query.trim().toLowerCase();
            const modulos = document.querySelectorAll('.modulo-card');

            if (!cleanQuery) {
                modulos.forEach(m => m.classList.remove('hidden'));
                return;
            }

            modulos.forEach(mod => {
                const text = mod.innerText.toLowerCase();
                if (text.includes(cleanQuery)) {
                    mod.classList.remove('hidden');
                } else {
                    mod.classList.add('hidden');
                }
            });
        }
    </script>
</body>
</html>
