<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Acceso Denegado | SIS-APAFA</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-x-hidden selection:bg-rose-500 selection:text-white">

    <!-- Ambient Glow Effects -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-rose-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 -right-32 w-96 h-96 bg-amber-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 left-1/3 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-xl relative z-10">
        
        <!-- Brand / Institution Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-900/90 border border-slate-800 text-xs text-slate-400 mb-3 shadow-inner">
                <i class="fa-solid fa-graduation-cap text-indigo-400"></i>
                <span>SIS-APAFA &bull; Control de Seguridad y Permisos</span>
            </div>
        </div>

        <!-- Main Card -->
        <div class="bg-slate-900/90 backdrop-blur-2xl p-6 sm:p-8 rounded-3xl border border-slate-800 shadow-2xl relative overflow-hidden">
            <!-- Top Alert Stripe -->
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-amber-500 via-rose-500 to-red-600"></div>

            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 text-center sm:text-left mb-6">
                <!-- Shield Icon with Pulse -->
                <div class="relative flex-shrink-0">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-rose-600 to-amber-500 text-white flex items-center justify-center text-3xl shadow-xl shadow-rose-600/30 border border-rose-400/30">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-slate-950 border-2 border-slate-900 flex items-center justify-center text-[10px] text-amber-400 font-black">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                </div>

                <div class="flex-1">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-rose-500/15 border border-rose-500/30 text-rose-300 font-extrabold text-[11px] uppercase tracking-wider mb-2">
                        <i class="fa-solid fa-triangle-exclamation text-rose-400"></i> Error 403 &bull; Acceso Restringido
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                        No cuenta con permisos suficientes
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1 leading-relaxed">
                        {{ $exception->getMessage() ?: 'No tiene autorización para acceder a esta sección o ejecutar esta acción con su perfil actual.' }}
                    </p>
                </div>
            </div>

            <!-- Current User Profile Box -->
            @auth
            <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800/90 mb-6">
                <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-2 flex items-center justify-between">
                    <span>Sesión Activa</span>
                    <span class="text-slate-400 font-mono">ID #{{ auth()->id() }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center font-bold text-sm flex-shrink-0">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="overflow-hidden text-left">
                            <p class="text-sm font-bold text-white truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        {!! auth()->user()->rol_badge_html ?? '<span class="text-xs text-slate-400">Sin Rol</span>' !!}
                    </div>
                </div>
            </div>
            @endauth

            <!-- Explanation & Normative Note -->
            <div class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-200/90 text-xs mb-6 flex items-start gap-2.5 leading-relaxed">
                <i class="fa-solid fa-circle-info text-amber-400 text-sm mt-0.5 flex-shrink-0"></i>
                <div>
                    <span class="font-bold text-amber-300">Seguridad por Roles (Ley 28628):</span>
                    El SIS-APAFA aplica separación estricta de atribuciones directivas. Si necesita gestionar este módulo, solicite la habilitación del perfil correspondiente al Presidente o Administrador institucional.
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-2.5">
                <a href="{{ route('dashboard') }}" 
                   class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:scale-[0.99] text-white font-bold text-sm shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Ir a Mi Panel de Control (Dashboard)</span>
                </a>

                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="window.history.back()" 
                            class="py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 active:scale-[0.99] text-slate-200 font-semibold text-xs border border-slate-700 transition-all flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Volver Atrás</span>
                    </button>

                    @auth
                    <form action="{{ route('logout') }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit" 
                                class="w-full py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-rose-950/40 hover:text-rose-300 active:scale-[0.99] text-slate-300 font-semibold text-xs border border-slate-700 hover:border-rose-800/60 transition-all flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-arrow-right-from-bracket text-rose-400"></i>
                            <span>Cambiar Cuenta</span>
                        </button>
                    </form>
                    @else
                    <a href="{{ route('login') }}" 
                       class="py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs border border-slate-700 transition-all flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-arrow-right-to-bracket text-indigo-400"></i>
                        <span>Iniciar Sesión</span>
                    </a>
                    @endauth
                </div>
            </div>

        </div>

        <p class="text-center text-xs text-slate-600 mt-6">
            SIS-APAFA &bull; Seguridad Multi-Tenant y Control de Acceso RBAC
        </p>

    </div>

</body>
</html>
