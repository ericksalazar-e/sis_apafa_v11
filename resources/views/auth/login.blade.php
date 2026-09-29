<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema - SIS-APAFA</title>

    <!-- Favicon del Navegador (Logo Dinámico Institucional) -->
    @php
        $favLogin = $currentTenant?->foto_institucion ?? 'favicon.png';
        $favVerL = file_exists(public_path($favLogin)) ? filemtime(public_path($favLogin)) : time();
    @endphp
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v={{ $favVerL }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v={{ $favVerL }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v={{ $favVerL }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ $favVerL }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v={{ $favVerL }}">

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
<body class="h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-x-hidden selection:bg-indigo-500 selection:text-white">

    <!-- Background glow ambient effects -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 -right-40 w-96 h-96 bg-violet-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 left-1/3 w-96 h-96 bg-emerald-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 text-center">
        <!-- Logo Icon -->
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-500 text-white shadow-xl shadow-indigo-500/30 mb-4 border border-indigo-400/30">
            <i class="fa-solid fa-graduation-cap text-3xl"></i>
        </div>

        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
            SIS-APAFA
        </h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1 font-medium">
            Gestión Integral de Asociación de Padres de Familia
        </p>

        <!-- Current Tenant Badge -->
        <div class="mt-3 inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-900/90 border border-slate-800 text-xs text-slate-300 shadow-inner">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="truncate max-w-[280px]">{{ $currentTenant->nombre ?? 'I.E. NERY GARCIA ZARATE' }}</span>
        </div>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md relative z-10 px-4 sm:px-0">
        <!-- Main Login Card -->
        <div class="bg-slate-900/90 backdrop-blur-xl py-8 px-6 sm:px-8 shadow-2xl rounded-3xl border border-slate-800/90">
            
            <!-- Alert Messages -->
            @if(session('success'))
                <div class="mb-5 p-3.5 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-check text-emerald-400 text-sm flex-shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 p-3.5 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-exclamation text-rose-400 text-sm flex-shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs space-y-1">
                    <div class="font-bold flex items-center gap-1.5 text-rose-200 mb-1">
                        <i class="fa-solid fa-triangle-exclamation text-rose-400"></i> No se pudo iniciar sesión:
                    </div>
                    @foreach($errors->all() as $err)
                        <div class="flex items-center gap-2 pl-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                            <span>{{ $err }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                
                <!-- Usuario o Email -->
                <div>
                    <label for="login" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                        Usuario o Correo Institucional
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-sm">
                            <i class="fa-regular fa-envelope"></i>
                        </div>
                        <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus
                               placeholder="ej. secretario@apafa.edu.pe o secretario"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-950/80 border @error('login') border-rose-500 ring-1 ring-rose-500/40 @else border-slate-800 @enderror rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    @error('login')
                        <p class="mt-1.5 text-xs text-rose-400 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-xmark text-[11px]"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Contraseña -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Contraseña de Acceso
                        </label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-sm">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input id="password" name="password" type="password" required
                               placeholder="••••••••"
                               class="w-full pl-10 pr-11 py-2.5 bg-slate-950/80 border @error('password') border-rose-500 ring-1 ring-rose-500/40 @else border-slate-800 @enderror rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        <button type="button" onclick="togglePasswordVisibility()" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 text-sm focus:outline-none">
                            <i id="eye-icon" class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-400 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-xmark text-[11px]"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Remember me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-950 border-slate-800 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900">
                        <span class="text-xs text-slate-400 font-medium">Recordar mis datos</span>
                    </label>
                    <span class="text-[11px] text-slate-500">Acceso Seguro SSL</span>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" 
                            class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:scale-[0.99] text-white font-bold text-sm shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                        <span>Ingresar al Sistema</span>
                    </button>
                </div>
            </form>

            <!-- Separador -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-800"></div>
                </div>
                <div class="relative flex justify-center text-xs">
                    <span class="px-3 bg-slate-900 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        Roles y Responsabilidades APAFA
                    </span>
                </div>
            </div>

            <!-- Accesos Rápidos de Prueba (Roles) -->
            <div class="space-y-2">
                <p class="text-[11px] text-slate-400 text-center font-medium mb-3">
                    Haga clic en cualquiera de los roles para ingresar directamente al perfil correspondiente:
                </p>

                <div class="grid grid-cols-2 gap-2 text-left">
                    <!-- 1. Secretario -->
                    <a href="{{ route('login.quick', 'SECRETARIO') }}" 
                       class="p-2.5 rounded-xl bg-slate-950/60 hover:bg-slate-800/80 border border-slate-800/80 hover:border-blue-500/50 transition-all group">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center text-xs group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-file-signature"></i>
                            </span>
                            <div class="overflow-hidden">
                                <p class="text-xs font-bold text-white leading-tight">Secretario</p>
                                <p class="text-[10px] text-slate-500 truncate">Padrón y Alumnos</p>
                            </div>
                        </div>
                    </a>

                    <!-- 2. Tesorero -->
                    <a href="{{ route('login.quick', 'TESORERO') }}" 
                       class="p-2.5 rounded-xl bg-slate-950/60 hover:bg-slate-800/80 border border-slate-800/80 hover:border-emerald-500/50 transition-all group">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xs group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-cash-register"></i>
                            </span>
                            <div class="overflow-hidden">
                                <p class="text-xs font-bold text-white leading-tight">Tesorero</p>
                                <p class="text-[10px] text-slate-500 truncate">Cuotas, POS y Caja</p>
                            </div>
                        </div>
                    </a>

                    <!-- 3. Comité de Vigilancia -->
                    <a href="{{ route('login.quick', 'COMITE_VIGILANCIA') }}" 
                       class="p-2.5 rounded-xl bg-slate-950/60 hover:bg-slate-800/80 border border-slate-800/80 hover:border-amber-500/50 transition-all group">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center text-xs group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-eye"></i>
                            </span>
                            <div class="overflow-hidden">
                                <p class="text-xs font-bold text-white leading-tight">C. Vigilancia</p>
                                <p class="text-[10px] text-slate-500 truncate">Auditoría (Lectura)</p>
                            </div>
                        </div>
                    </a>

                    <!-- 4. Vocal / Operador de Puerta -->
                    <a href="{{ route('login.quick', 'VOCAL_OPERADOR') }}" 
                       class="p-2.5 rounded-xl bg-slate-950/60 hover:bg-slate-800/80 border border-slate-800/80 hover:border-cyan-500/50 transition-all group">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-cyan-500/10 text-cyan-400 flex items-center justify-center text-xs group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-qrcode"></i>
                            </span>
                            <div class="overflow-hidden">
                                <p class="text-xs font-bold text-white leading-tight">Vocal / Puerta</p>
                                <p class="text-[10px] text-slate-500 truncate">Escáner Portería</p>
                            </div>
                        </div>
                    </a>

                    <!-- 5. Director I.E. -->
                    <a href="{{ route('login.quick', 'DIRECTOR') }}" 
                       class="p-2.5 rounded-xl bg-slate-950/60 hover:bg-slate-800/80 border border-slate-800/80 hover:border-slate-500/50 transition-all group">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-slate-700/50 text-slate-300 flex items-center justify-center text-xs group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-school"></i>
                            </span>
                            <div class="overflow-hidden">
                                <p class="text-xs font-bold text-white leading-tight">Director I.E.</p>
                                <p class="text-[10px] text-slate-500 truncate">Quórum y Reportes</p>
                            </div>
                        </div>
                    </a>

                    <!-- 6. Administrador / Presidente -->
                    <a href="{{ route('login.quick', 'ADMIN') }}" 
                       class="p-2.5 rounded-xl bg-slate-950/60 hover:bg-slate-800/80 border border-slate-800/80 hover:border-purple-500/50 transition-all group">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center text-xs group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-crown"></i>
                            </span>
                            <div class="overflow-hidden">
                                <p class="text-xs font-bold text-white leading-tight">Presidente</p>
                                <p class="text-[10px] text-slate-500 truncate">Control Total</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

        </div>

        <!-- Enlace al Manual de Usuario -->
        <div class="mt-4 text-center">
            <a href="{{ route('manual') }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-slate-900/90 border border-slate-800 text-xs font-bold text-indigo-400 hover:text-indigo-300 hover:border-indigo-500/50 transition-all shadow-inner">
                <i class="fa-solid fa-book-open-reader text-indigo-400"></i>
                <span>¿Primera vez aquí? Ver Manual de Usuario</span>
            </a>
        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-slate-600 mt-4">
            Conforme a la Ley General de APAFA N° 28628 y D.S. N° 004-2006-ED.
        </p>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
