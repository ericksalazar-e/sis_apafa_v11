<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-900 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>429 - Límite de Peticiones | SIS-APAFA</title>
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
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-purple-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-lg w-full bg-slate-900/90 border border-slate-800 rounded-3xl p-8 sm:p-10 shadow-2xl backdrop-blur-xl relative z-10 text-center">
        <!-- Shield Icon -->
        <div class="w-16 h-16 rounded-2xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center mx-auto mb-6 shadow-inner">
            <i class="fa-solid fa-gauge-high text-2xl"></i>
        </div>

        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-bold bg-purple-500/10 text-purple-400 border border-purple-500/20 mb-3">
            <span class="w-1.5 h-1.5 rounded-full bg-purple-400 animate-pulse"></span>
            Código de Estado: HTTP 429
        </span>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mb-3">
            Demasiadas Solicitudes
        </h1>

        <p class="text-slate-400 text-sm leading-relaxed mb-6">
            Nuestros filtros de protección y alta disponibilidad han detectado una alta frecuencia de peticiones desde su conexión. Para asegurar la estabilidad de la institución, su acceso se reanudará en unos momentos.
        </p>

        <div class="flex justify-center">
            <button onclick="setTimeout(() => window.location.reload(), 1000);" 
                    class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-lg shadow-purple-600/30 transition-all flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left"></i> Reintentar en 5 segundos
            </button>
        </div>
    </div>
</body>
</html>
