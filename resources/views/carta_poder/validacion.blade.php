<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de Carta Poder - SIS-APAFA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white p-8 rounded-2xl shadow-xl border border-slate-200 text-center space-y-5">
        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-3xl">
            <i class="bi bi-shield-check"></i>
        </div>

        <div>
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-300">
                Poder Válido y Registrado
            </span>
            <h1 class="text-xl font-bold text-slate-800 mt-2">Acreditación de Representación</h1>
            <p class="text-xs text-slate-500 mt-1">Conforme a D.S. N.º 007-2026-MINEDU (Arts. 13 y 14)</p>
        </div>

        @if($padre)
        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-left text-xs space-y-2">
            <p><strong class="text-slate-700">Poderdante:</strong> {{ $padre->apellidos }}, {{ $padre->nombres }} (DNI: {{ $padre->dni }})</p>
            <p><strong class="text-slate-700">Apoderado Delegado:</strong> {{ $padre->conyuge_nombres ?? 'Representante Acreditado' }} (DNI: {{ $padre->conyuge_dni ?? 'Registrado' }})</p>
            <p><strong class="text-slate-700">Parentesco:</strong> {{ $padre->tipo_consanguinidad ?? 'Apoderado Legal' }}</p>
            <p><strong class="text-slate-700">Institución Educativa:</strong> {{ $empresa->nombre ?? 'I.E. Nery García Zárate' }}</p>
            <p><strong class="text-slate-700">Vigencia:</strong> Periodo Lectivo 2026 - 2027</p>
        </div>
        @else
        <div class="bg-amber-50 p-4 rounded-xl border border-amber-200 text-xs text-amber-800">
            Código verificado en el sistema: <span class="font-mono font-bold">{{ $codigo }}</span>
        </div>
        @endif

        <a href="{{ route('home') }}" class="inline-block w-full py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-semibold transition-all">
            Ir al Portal Institucional
        </a>
    </div>
</body>
</html>
