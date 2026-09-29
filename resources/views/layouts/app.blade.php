<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $currentTenant->nombre ?? 'Sistema APAFA' }} - Laravel 11 Multi-Tenant</title>

    <!-- Favicon del Navegador (Logo Dinámico Institucional) -->
    @php
        $faviconImg = $currentTenant->foto_institucion 
            ?? \App\Models\Empresa::find(session('empresa_id') ?? 1)?->foto_institucion 
            ?? \App\Models\Empresa::first()?->foto_institucion 
            ?? 'favicon.png';
        $favVer = file_exists(public_path($faviconImg)) ? filemtime(public_path($faviconImg)) : time();
    @endphp
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v={{ $favVer }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v={{ $favVer }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v={{ $favVer }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ $favVer }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v={{ $favVer }}">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
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
    
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- HTML5-QRCode Scanner Library CDN for Smartphone Camera Scanning -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <!-- SweetAlert2 CDN for Modern Confirmations & Alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full flex overflow-hidden text-slate-800 antialiased">

    <!-- Mobile Drawer Backdrop -->
    <div id="sidebar-backdrop" 
         onclick="toggleSidebar(false)" 
         class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity duration-300"></div>

    <!-- Sidebar Drawer (Fixed off-canvas on mobile, static on lg) -->
    <aside id="sidebar-drawer" 
           class="fixed inset-y-0 left-0 z-50 w-72 lg:w-64 bg-slate-900 text-slate-300 flex flex-col flex-shrink-0 border-r border-slate-800 transform -translate-x-full lg:translate-x-0 lg:static transition-transform duration-300 ease-in-out shadow-2xl lg:shadow-none">
        
        <!-- Logo / Brand & Mobile Close Button -->
        <div class="p-4 flex items-center justify-between border-b border-slate-800/80">
            <div class="flex items-center gap-3 overflow-hidden">
                @php
                    $tenantLogo = $currentTenant->foto_institucion 
                        ?? \App\Models\Empresa::find(1)->foto_institucion 
                        ?? \App\Models\Empresa::first()->foto_institucion 
                        ?? null;
                    $tenantNombre = $currentTenant->nombre 
                        ?? \App\Models\Empresa::find(1)->nombre 
                        ?? \App\Models\Empresa::first()->nombre 
                        ?? 'SIS-APAFA';
                    $tenantPeriodo = $currentTenant->periodo_apafa 
                        ?? \App\Models\Empresa::find(1)->periodo_apafa 
                        ?? '2025-2026';
                @endphp
                @if(!empty($tenantLogo))
                    <div class="h-10 w-10 rounded-xl bg-white p-1 flex items-center justify-center shadow-lg shadow-indigo-500/20 flex-shrink-0 overflow-hidden">
                        <img src="{{ asset($tenantLogo) }}" 
                             alt="Logo Institución" 
                             class="max-h-full max-w-full object-contain"
                             onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white text-base rounded-lg\'><i class=\'fa-solid fa-graduation-cap\'></i></div>';">
                    </div>
                @else
                    <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/30 flex-shrink-0">
                        <i class="fa-solid fa-graduation-cap text-lg"></i>
                    </div>
                @endif
                <div class="overflow-hidden min-w-0">
                    <h1 class="text-xs font-black text-white tracking-tight uppercase truncate">{{ $tenantNombre }}</h1>
                    <span class="inline-flex items-center text-[9px] font-semibold px-1.5 py-0.5 rounded bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 truncate max-w-full">
                        Periodo {{ $tenantPeriodo }}
                    </span>
                </div>
            </div>

            <!-- Close Drawer Button (Mobile Only) -->
            <button type="button" 
                    onclick="toggleSidebar(false)" 
                    class="lg:hidden p-2 text-slate-400 hover:text-white rounded-xl hover:bg-slate-800 transition-colors focus:outline-none"
                    aria-label="Cerrar Menú">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>


        <!-- User Role Profile Card in Sidebar -->
        @auth
        <div class="px-4 py-3 bg-slate-950/80 border-b border-slate-800/80 flex items-center justify-between">
            <div class="flex items-center gap-2.5 overflow-hidden">
                <div class="w-8 h-8 rounded-xl bg-indigo-600/30 text-indigo-300 border border-indigo-500/40 flex items-center justify-center font-bold text-xs flex-shrink-0">
                    {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()->cargo_titulo ?? auth()->user()->rol_nombre }}</p>
                </div>
            </div>
        </div>
        @endauth

        <!-- Navigation Menu -->
        <nav class="flex-1 px-3 py-4 space-y-1.5 overflow-y-auto">
            <a href="{{ route('dashboard') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i class="fa-solid fa-chart-pie text-base w-5 text-center"></i>
                Dashboard KPIs
            </a>

            @if(!auth()->check() || auth()->user()->hasRole('SECRETARIO', 'COMITE_VIGILANCIA'))
            <a href="{{ route('padres.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('padres.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i class="fa-solid fa-users text-base w-5 text-center"></i>
                Padres y Familias
            </a>
            @endif

            @if(!auth()->check() || auth()->user()->hasRole('SECRETARIO', 'DIRECTOR', 'COMITE_VIGILANCIA', 'TESORERO'))
            <a href="{{ route('estudiantes.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('estudiantes.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i class="fa-solid fa-user-graduate text-base w-5 text-center"></i>
                Alumnos Matriculados
            </a>
            @endif

            @if(!auth()->check() || auth()->user()->hasRole('SECRETARIO', 'VOCAL_OPERADOR', 'DIRECTOR', 'COMITE_VIGILANCIA'))
            <a href="{{ route('asistencia.estudiantes.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('asistencia.estudiantes.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i class="fa-solid fa-id-badge text-base w-5 text-center"></i>
                Asistencia Diaria Alumnos
            </a>
            @endif

            @if(!auth()->check() || auth()->user()->hasRole('TESORERO', 'COMITE_VIGILANCIA'))
            <a href="{{ route('aportes.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('aportes.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i class="fa-solid fa-hand-holding-dollar text-base w-5 text-center"></i>
                Aportes y Cuotas
            </a>
            @endif

            @if(!auth()->check() || auth()->user()->hasRole('SECRETARIO', 'VOCAL_OPERADOR', 'DIRECTOR', 'COMITE_VIGILANCIA'))
            <a href="{{ route('reuniones.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('reuniones.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i class="fa-solid fa-qrcode text-base w-5 text-center"></i>
                Asambleas y Asistencia
            </a>
            @endif

            @if(!auth()->check() || auth()->user()->hasRole('TESORERO', 'COMITE_VIGILANCIA'))
            <a href="{{ route('pagos.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('pagos.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i class="fa-solid fa-cash-register text-base w-5 text-center"></i>
                Punto de Venta / Pagos
            </a>
            @endif

            @if(!auth()->check() || auth()->user()->hasRole('TESORERO', 'COMITE_VIGILANCIA'))
            <a href="{{ route('gastos_poa.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('gastos_poa.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i class="fa-solid fa-receipt text-base w-5 text-center"></i>
                Rendición POA / Egresos
            </a>
            @endif

            @if(!auth()->check() || auth()->user()->hasRole('ADMIN', 'SECRETARIO', 'COMITE_VIGILANCIA'))
            <a href="{{ route('configuracion.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('configuracion.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i class="fa-solid fa-sliders text-base w-5 text-center"></i>
                Configuración & Comité
            </a>
            @endif

            <div class="pt-4 pb-1 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> D.S. 007-2026-MINEDU
            </div>

            <!-- Faenas Escolares y Compensaciones -->
            <a href="{{ route('faenas.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('faenas.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i class="fa-solid fa-screwdriver-wrench text-base w-5 text-center text-emerald-400"></i>
                Faenas & Compensación
            </a>

            <!-- Comicios Electorales y Padrón -->
            <a href="{{ route('elecciones.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('elecciones.*') ? 'bg-purple-600 text-white shadow-md shadow-purple-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i class="fa-solid fa-check-to-slot text-base w-5 text-center text-purple-400"></i>
                Elecciones y Padrón 1-Set
            </a>

            <!-- Quioscos Escolares Ley 30021 -->
            <a href="{{ route('quioscos.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('quioscos.*') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i class="fa-solid fa-mug-hot text-base w-5 text-center text-amber-400"></i>
                Quioscos y Cafetines
            </a>

            <!-- Auditoría Consejo de Vigilancia -->
            <a href="{{ route('vigilancia.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('vigilancia.*') ? 'bg-rose-600 text-white shadow-md shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i class="fa-solid fa-shield-halved text-base w-5 text-center text-rose-400"></i>
                Consejo de Vigilancia
            </a>

            <!-- Enlace al Manual de Usuario -->
            <a href="{{ route('manual') }}" target="_blank"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all text-indigo-300 hover:text-white hover:bg-indigo-600/30 border border-indigo-500/30">
                <i class="fa-solid fa-book-open-reader text-base w-5 text-center text-indigo-400"></i>
                Manual de Usuario
                <span class="ml-auto text-[10px] bg-indigo-500/20 text-indigo-300 px-1.5 py-0.5 rounded font-bold uppercase">Guía</span>
            </a>

            <div class="pt-4 pb-1 px-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                <span>Microservicios</span>
                <span class="text-[9px] text-indigo-400 font-mono">UBL 2.1</span>
            </div>

            <a href="{{ route('facturacion.index') }}" 
               class="group block px-3.5 py-2.5 rounded-xl border text-xs transition-all duration-200 {{ request()->routeIs('facturacion.*') ? 'bg-indigo-900/40 border-indigo-500/60 shadow-lg shadow-indigo-950/50' : 'bg-slate-800/40 hover:bg-slate-800/80 border-slate-700/50 hover:border-slate-600' }}">
                <div class="flex items-center justify-between text-slate-300 font-semibold mb-1">
                    <span class="flex items-center gap-1.5 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-microchip {{ request()->routeIs('facturacion.*') ? 'text-indigo-300' : 'text-indigo-400' }}"></i> Facturación CPE
                    </span>
                    <span class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 bg-emerald-500/20 text-emerald-400 rounded-full font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Activo
                    </span>
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-400 leading-tight">
                    <span>Servicio asíncrono UBL 2.1</span>
                    <i class="fa-solid fa-arrow-right text-[10px] opacity-0 group-hover:opacity-100 group-hover:translate-x-0.5 text-indigo-400 transition-all"></i>
                </div>
                <div class="mt-1 text-[10px] text-slate-500 font-mono">
                    <code>/facturacion-service</code>
                </div>
            </a>
        </nav>

        <!-- Footer / Environment -->
        <div class="p-4 border-t border-slate-800/80 bg-slate-950/40 text-xs text-slate-400 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-brands fa-php text-base text-indigo-400"></i>
                <span>PHP 8.3 &bull; L11</span>
            </div>
            <span class="px-2 py-0.5 rounded bg-slate-800 text-[10px] font-mono text-slate-300">MySQL 8.x</span>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
        
        <!-- Top Navbar (Responsive) -->
        <header class="h-16 bg-white border-b border-slate-200/80 flex items-center justify-between px-4 sm:px-6 z-10 flex-shrink-0">
            <div class="flex items-center gap-2 sm:gap-3 overflow-hidden">
                <!-- Hamburger Button for Mobile -->
                <button type="button" 
                        onclick="toggleSidebar(true)" 
                        class="lg:hidden p-2 -ml-1 text-slate-600 hover:text-slate-900 rounded-xl hover:bg-slate-100 transition-colors focus:outline-none"
                        aria-label="Abrir Menú">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>

                <h2 class="text-sm sm:text-base md:text-lg font-bold text-slate-800 truncate">
                    @yield('title', 'Panel General')
                </h2>
            </div>

            <!-- User Profile & Actions -->
            <div class="flex items-center gap-2 sm:gap-4 flex-shrink-0">
                <!-- User profile badge & Logout button -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="h-9 w-9 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-sm shadow">
                        {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="hidden sm:block text-left text-xs max-w-[150px]">
                        <p class="font-bold text-slate-800 leading-none truncate">{{ auth()->user()->name ?? 'Usuario APAFA' }}</p>
                        <div class="mt-0.5">
                            {!! auth()->user()->rol_badge_html ?? '<span class="text-slate-400 text-[10px]">Asociado</span>' !!}
                        </div>
                    </div>

                    <!-- Logout Button -->
                    @auth
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" title="Cerrar Sesión" 
                                class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors">
                            <i class="fa-solid fa-arrow-right-from-bracket text-base"></i>
                        </button>
                    </form>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Banner Fiscalizador (Solo Lectura) -->
        @auth
            @if(auth()->user()->isComiteVigilancia())
                <div class="bg-amber-50 border-b border-amber-200 px-4 sm:px-6 py-2 flex items-center gap-2 text-xs font-bold text-amber-800">
                    <i class="fa-solid fa-shield-halved text-amber-600 text-sm flex-shrink-0"></i>
                    <span class="line-clamp-2 sm:line-clamp-none">MODO FISCALIZACIÓN / AUDITORÍA (SOLO LECTURA): Acceso de inspección y control de transparencia sin manipulación de datos.</span>
                </div>
            @endif
        @endauth

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mx-4 sm:mx-6 mt-4 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-800 text-sm shadow-sm">
                <i class="fa-solid fa-circle-check text-emerald-500 text-lg flex-shrink-0"></i>
                <div class="flex-1 font-medium text-xs sm:text-sm">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="mx-4 sm:mx-6 mt-4 p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-3 text-rose-800 text-sm shadow-sm">
                <i class="fa-solid fa-circle-exclamation text-rose-500 text-lg flex-shrink-0"></i>
                <div class="flex-1 font-medium text-xs sm:text-sm">{{ session('error') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="mx-4 sm:mx-6 mt-4 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-sm shadow-sm">
                <div class="flex items-center gap-2 font-bold mb-1">
                    <i class="fa-solid fa-triangle-exclamation text-rose-500 flex-shrink-0"></i>
                    Hubo errores en el formulario:
                </div>
                <ul class="list-disc pl-5 text-xs space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Page Body Content (With extra bottom padding for mobile navigation bar) -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 pb-28 md:pb-6">
            @yield('content')
        </main>

        <!-- Mobile Bottom Navigation Bar (Visible on mobile/tablets < 768px) -->
        <nav class="md:hidden fixed bottom-0 left-0 right-0 z-30 bg-white/95 backdrop-blur-md border-t border-slate-200/90 px-3 py-2 flex items-center justify-around shadow-[0_-4px_20px_rgba(0,0,0,0.08)]">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-1 px-2 rounded-lg text-[10px] font-bold {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-slate-500 hover:text-slate-800' }}">
                <i class="fa-solid fa-chart-pie text-lg mb-0.5"></i>
                <span>Inicio</span>
            </a>

            <a href="{{ route('estudiantes.index') }}" class="flex flex-col items-center py-1 px-2 rounded-lg text-[10px] font-bold {{ request()->routeIs('estudiantes.*') ? 'text-indigo-600' : 'text-slate-500 hover:text-slate-800' }}">
                <i class="fa-solid fa-user-graduate text-lg mb-0.5"></i>
                <span>Alumnos</span>
            </a>

            <!-- HERO SCANNER BUTTON FOR GATE OPERATORS (Elevated Center Button) -->
            <a href="{{ route('asistencia.estudiantes.index') }}" 
               class="flex flex-col items-center -mt-6 group focus:outline-none" 
               title="Escanear Asistencia">
                <div class="w-14 h-14 bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-600 text-white rounded-2xl shadow-xl shadow-indigo-600/40 ring-4 ring-white flex items-center justify-center transform active:scale-95 transition-all">
                    <i class="fa-solid fa-barcode text-2xl group-hover:scale-110 transition-transform"></i>
                </div>
                <span class="text-[10px] font-extrabold text-indigo-700 mt-1">Escanear</span>
            </a>

            <a href="{{ route('reuniones.index') }}" class="flex flex-col items-center py-1 px-2 rounded-lg text-[10px] font-bold {{ request()->routeIs('reuniones.*') ? 'text-indigo-600' : 'text-slate-500 hover:text-slate-800' }}">
                <i class="fa-solid fa-qrcode text-lg mb-0.5"></i>
                <span>Asambleas</span>
            </a>

            <button type="button" onclick="toggleSidebar(true)" class="flex flex-col items-center py-1 px-2 rounded-lg text-[10px] font-bold text-slate-500 hover:text-slate-800 focus:outline-none">
                <i class="fa-solid fa-bars-staggered text-lg mb-0.5"></i>
                <span>Menú</span>
            </button>
        </nav>
    </div>

    <!-- Mobile Drawer Script -->
    <script>
        function toggleSidebar(open) {
            const sidebar = document.getElementById('sidebar-drawer');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (!sidebar || !backdrop) return;
            if (open) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }
    </script>

    <!-- Global Notifications & Delete Confirmations Engine -->
    <script>
        // Universal Delete Confirmation Dialog
        window.confirmDelete = function(event, form, title = '¿Estás seguro de que deseas eliminar este registro?', text = 'Esta acción no se puede deshacer y los datos asociados se eliminarán permanentemente.', confirmBtn = 'Sí, eliminar') {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            if (form.dataset.confirmed === 'true') {
                form.submit();
                return true;
            }

            Swal.fire({
                title: title,
                html: `<p class="text-sm text-slate-600 mt-2">${text}</p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48', // rose-600
                cancelButtonColor: '#64748b',  // slate-500
                confirmButtonText: `<i class="fa-solid fa-trash-can mr-1.5"></i> ${confirmBtn}`,
                cancelButtonText: '<i class="fa-solid fa-xmark mr-1.5"></i> Cancelar',
                reverseButtons: true,
                focusCancel: true,
                backdrop: 'rgba(15, 23, 42, 0.75)',
                customClass: {
                    popup: 'rounded-3xl shadow-2xl border border-slate-100 p-6',
                    title: 'text-lg font-black text-slate-800',
                    confirmButton: 'rounded-xl px-5 py-2.5 font-bold text-sm shadow-lg shadow-rose-600/30',
                    cancelButton: 'rounded-xl px-5 py-2.5 font-bold text-sm text-slate-700 bg-slate-100 hover:bg-slate-200 border-0'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });

            return false;
        };

        // Universal Action Confirmation Dialog
        window.confirmAction = function(event, form, title, text, icon = 'question', confirmBtn = 'Confirmar', confirmColor = '#4f46e5') {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            if (form.dataset.confirmed === 'true') {
                form.submit();
                return true;
            }

            Swal.fire({
                title: title,
                html: `<p class="text-sm text-slate-600 mt-2">${text}</p>`,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#64748b',
                confirmButtonText: confirmBtn,
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                backdrop: 'rgba(15, 23, 42, 0.75)',
                customClass: {
                    popup: 'rounded-3xl shadow-2xl border border-slate-100 p-6',
                    title: 'text-lg font-black text-slate-800',
                    confirmButton: 'rounded-xl px-5 py-2.5 font-bold text-sm text-white',
                    cancelButton: 'rounded-xl px-5 py-2.5 font-bold text-sm text-slate-700 bg-slate-100'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });

            return false;
        };

        // Automatic Global Form Interceptor for DELETE forms
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form').forEach(function(form) {
                const methodInput = form.querySelector('input[name="_method"][value="DELETE"]');
                if (methodInput && !form.getAttribute('onsubmit')) {
                    form.addEventListener('submit', function(e) {
                        if (form.dataset.confirmed === 'true') return true;
                        e.preventDefault();
                        const title = form.dataset.confirmTitle || '¿Estás seguro de que deseas eliminar este registro?';
                        const text = form.dataset.confirmText || 'Esta acción no se puede deshacer y los datos asociados se eliminarán permanentemente.';
                        const btn = form.dataset.confirmBtn || 'Sí, eliminar';
                        window.confirmDelete(e, form, title, text, btn);
                    });
                }
            });
        });

        // Flash Notifications Toast
        @if(session('success'))
            Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            }).fire({
                icon: 'success',
                title: '{{ addslashes(session('success')) }}'
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Atención / Error',
                text: '{{ addslashes(session('error')) }}',
                confirmButtonColor: '#e11d48',
                confirmButtonText: 'Entendido',
                customClass: {
                    popup: 'rounded-3xl shadow-2xl p-6 border border-slate-100',
                    title: 'text-lg font-bold text-slate-800',
                    confirmButton: 'rounded-xl px-5 py-2.5 font-bold text-sm'
                }
            });
        @endif

        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Errores en el Formulario',
                html: `<div class="text-left text-xs text-rose-700 bg-rose-50 p-3.5 rounded-2xl border border-rose-200 mt-2"><ul class="list-disc pl-4 space-y-1">@foreach($errors->all() as $err)<li>{{ addslashes($err) }}</li>@endforeach</ul></div>`,
                confirmButtonColor: '#e11d48',
                confirmButtonText: 'Revisar y Corregir',
                customClass: {
                    popup: 'rounded-3xl shadow-2xl p-6 border border-slate-100',
                    title: 'text-lg font-bold text-slate-800',
                    confirmButton: 'rounded-xl px-5 py-2.5 font-bold text-sm'
                }
            });
        @endif
    </script>

    @stack('scripts')
</body>
</html>
