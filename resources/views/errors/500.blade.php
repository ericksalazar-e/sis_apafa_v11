<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-900 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Error Interno del Servidor | SIS-APAFA</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 relative overflow-hidden bg-slate-950">
    <!-- Ambient glowing orbs -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-rose-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-lg w-full bg-slate-900/90 border border-slate-800 rounded-3xl p-8 sm:p-10 shadow-2xl backdrop-blur-xl relative z-10 text-center">
        <!-- Error Badge -->
        <div class="w-16 h-16 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center mx-auto mb-6 shadow-inner">
            <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
        </div>

        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20 mb-3">
            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
            Código de Estado: HTTP 500
        </span>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mb-3">
            Error Interno del Servidor
        </h1>

        <p class="text-slate-400 text-sm leading-relaxed mb-6">
            Ocurrió un evento inesperado mientras procesábamos su solicitud. La tolerancia a fallos del sistema ha aislado la sesión y notificado al equipo técnico.
        </p>

        <!-- Incident ID Card -->
        <div class="p-3 bg-slate-950/70 border border-slate-800/80 rounded-xl mb-6 text-left">
            <div class="flex items-center justify-between text-[11px] text-slate-400 mb-1">
                <span>Identificador de Incidencia:</span>
                <span class="font-mono text-indigo-400 font-bold">
                    SIS-{{ date('Ymd') }}-{{ substr(md5(request()->fullUrl() . time()), 0, 8) }}
                </span>
            </div>
            <p class="text-[10px] text-slate-500">
                Sus datos y transacciones previas se encuentran intactos y protegidos bajo aislamiento transaccional.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <button onclick="window.location.reload();" 
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-arrows-rotate"></i> Reintentar Solicitud
            </button>
            <a href="{{ url('/dashboard') }}" 
               class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs transition-colors flex items-center justify-center gap-2 border border-slate-700">
                <i class="fa-solid fa-house"></i> Ir al Dashboard
            </a>
        </div>
    </div>
</body>
</html>
