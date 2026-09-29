<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-900 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="30">
    <title>503 - Mantenimiento Programado | SIS-APAFA</title>
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
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-lg w-full bg-slate-900/90 border border-slate-800 rounded-3xl p-8 sm:p-10 shadow-2xl backdrop-blur-xl relative z-10 text-center">
        <!-- Maintenance Icon -->
        <div class="w-16 h-16 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center mx-auto mb-6 shadow-inner animate-pulse">
            <i class="fa-solid fa-screwdriver-wrench text-2xl"></i>
        </div>

        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 mb-3">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-ping"></span>
            Actualización del Sistema en Progreso
        </span>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mb-3">
            Mantenimiento y Optimización
        </h1>

        <p class="text-slate-400 text-sm leading-relaxed mb-6">
            El sistema está aplicando una actualización de alto rendimiento para garantizar la disponibilidad y seguridad de sus operaciones. Esta ventana suele tardar menos de 60 segundos.
        </p>

        <!-- Status Card -->
        <div class="p-3 bg-slate-950/70 border border-slate-800/80 rounded-xl mb-6 text-left space-y-1 text-xs">
            <div class="flex items-center justify-between text-slate-300">
                <span class="flex items-center gap-1.5">
                    <i class="fa-solid fa-shield-halved text-emerald-400"></i> Integridad de Datos:
                </span>
                <span class="font-bold text-emerald-400">100% Protegido</span>
            </div>
            <div class="flex items-center justify-between text-slate-400 text-[11px]">
                <span>Reconexión automática:</span>
                <span class="font-mono text-indigo-400 font-bold">Cada 30 segundos</span>
            </div>
        </div>

        <div class="flex justify-center">
            <button onclick="window.location.reload();" 
                    class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold text-xs shadow-lg shadow-amber-500/20 transition-all flex items-center gap-2">
                <i class="fa-solid fa-arrows-rotate"></i> Verificar Disponibilidad Ahora
            </button>
        </div>
    </div>
</body>
</html>
