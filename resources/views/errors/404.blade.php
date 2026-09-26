<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página no encontrada | SIS-APAFA</title>
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
<body class="h-full flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-x-hidden selection:bg-indigo-500 selection:text-white">

    <div class="absolute -top-32 -left-32 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 -right-32 w-96 h-96 bg-violet-600/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-lg relative z-10 text-center">
        <div class="bg-slate-900/90 backdrop-blur-2xl p-8 rounded-3xl border border-slate-800 shadow-2xl">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-indigo-500/15 text-indigo-400 border border-indigo-500/30 flex items-center justify-center text-3xl mb-4">
                <i class="fa-solid fa-compass"></i>
            </div>
            
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-800 text-slate-300 text-xs font-bold mb-3">
                Error 404 &bull; Recurso No Encontrado
            </div>

            <h1 class="text-2xl font-black text-white mb-2">
                Página o registro no disponible
            </h1>
            <p class="text-xs text-slate-400 mb-6 leading-relaxed">
                El enlace que intentó abrir no existe o ha sido reubicado. Verifique la dirección o regrese al inicio del sistema.
            </p>

            <div class="flex flex-col sm:flex-row gap-2 justify-center">
                <a href="{{ route('dashboard') }}" class="py-2.5 px-5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl transition-all flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/30">
                    <i class="fa-solid fa-chart-pie"></i> Ir al Dashboard
                </a>
                <button onclick="window.history.back()" class="py-2.5 px-4 bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs rounded-xl border border-slate-700 transition-all flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-arrow-left"></i> Volver Atrás
                </button>
            </div>
        </div>
    </div>
</body>
</html>
