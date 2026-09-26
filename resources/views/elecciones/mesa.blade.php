@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header de la Mesa de Sufragio -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('elecciones.index') }}" class="text-xs font-semibold text-purple-600 hover:underline">
                    <i class="bi bi-arrow-left"></i> Volver a Comicios
                </a>
                <span class="text-slate-300">•</span>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Mesa de Sufragio Electrónica</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <i class="bi bi-person-check-fill text-purple-600"></i> Registro de Votantes en Vivo (QR & DNI)
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Comité Electoral • Control de Sufragio Digital con prevención de doble votación en tiempo real.
            </p>
        </div>

        <div class="flex items-center gap-4 bg-purple-50 px-4 py-2 rounded-xl border border-purple-200">
            <div>
                <p class="text-[10px] text-purple-600 uppercase font-black tracking-wider">Cómputo en Mesa</p>
                <div class="text-lg font-black text-purple-950 flex items-center gap-1.5">
                    <span id="contadorVotosMesa">{{ $totalVotaron }}</span>
                    <span class="text-xs font-normal text-purple-700">/ {{ $totalElectores }} votos</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Escáner y Verificación -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Columna Izquierda: Escáner y Entrada de DNI -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <h3 class="font-bold text-slate-800 flex items-center gap-2 text-base">
                <i class="bi bi-camera text-purple-600"></i> Escáner de Carné de Padre / DNI
            </h3>

            <!-- Contenedor del Visor de Cámara -->
            <div class="relative bg-slate-900 rounded-2xl overflow-hidden min-h-[260px] flex items-center justify-center border-2 border-slate-800">
                <div id="qr-reader-mesa" class="w-full h-full"></div>
                <div id="scanner-overlay-mesa" class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center text-white bg-slate-900/80">
                    <i class="bi bi-qr-code-scan text-4xl mb-3 text-purple-400"></i>
                    <p class="text-sm font-bold">Cámara de Sufragio Desactivada</p>
                    <p class="text-xs text-slate-400 mt-1 mb-4">Habilite el visor para registrar votos escaneando el carné del asociado</p>
                    <button type="button" id="btnStartMesaCam" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-bold text-xs shadow-md transition-all flex items-center gap-2">
                        <i class="bi bi-camera-video"></i> Activar Cámara de Mesa
                    </button>
                </div>
            </div>

            <!-- Entrada Manual o Lector de Pistola USB -->
            <form id="formVotoManual" class="space-y-3 pt-2">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Lista Votada (Opcional)</label>
                        <select id="selectListaVoto" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-purple-500 focus:outline-none">
                            <option value="">-- Balota Física en Ánfora --</option>
                            @foreach($comicio->listas as $l)
                                <option value="{{ $l->id }}">Lista N° {{ $l->numero_lista }} - {{ $l->nombre_lista }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Tipo de Voto</label>
                        <select id="selectTipoVoto" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-purple-500 focus:outline-none">
                            <option value="VALIDO">Válido</option>
                            <option value="BLANCO">Voto Blanco</option>
                            <option value="NULO">Voto Nulo / Viciado</option>
                        </select>
                    </div>
                </div>

                <label class="block text-xs font-bold text-slate-700 uppercase">Ingreso por DNI o Escáner</label>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <input type="text" id="inputDniMesa" autofocus placeholder="Tipee DNI o escanee con pistola..." class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 text-sm font-mono font-bold focus:ring-2 focus:ring-purple-500 focus:outline-none">
                        <i class="bi bi-person-badge absolute left-3.5 top-3.5 text-slate-400"></i>
                    </div>
                    <button type="submit" class="px-5 py-3 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl text-sm transition-all flex items-center gap-1.5 shadow-sm">
                        <i class="bi bi-check-lg"></i> Registrar
                    </button>
                </div>
            </form>
        </div>

        <!-- Columna Derecha: Tarjeta de Resultado Instantáneo -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-slate-800 flex items-center gap-2 text-base mb-4">
                    <i class="bi bi-display text-purple-600"></i> Pantalla de Validación de Elector
                </h3>

                <!-- Estado Inicial o de Éxito / Alerta -->
                <div id="displayResultadoMesa" class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl p-8 text-center flex flex-col items-center justify-center min-h-[220px]">
                    <i class="bi bi-upc-scan text-5xl text-slate-300 mb-3"></i>
                    <p class="font-bold text-slate-700 text-base">Esperando elector...</p>
                    <p class="text-xs text-slate-400 mt-1 max-w-xs">
                        Pase el carné por la cámara o tipee el DNI del padre de familia para registrar su voto legal.
                    </p>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                <span><i class="bi bi-shield-check text-purple-600"></i> D.S. N.º 007-2026-MINEDU</span>
                <span>Proceso Electoral 2026</span>
            </div>
        </div>
    </div>
</div>

<!-- Scripts de Audio y Escaneo HTML5-QRCode -->
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    let html5QrCode = null;
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

    function beep(freq, duration, type='sine') {
        try {
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.type = type;
            osc.frequency.value = freq;
            gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + duration);
            osc.start();
            osc.stop(audioCtx.currentTime + duration);
        } catch(e) {}
    }

    function emitirVoto(codigo) {
        if (!codigo || codigo.trim() === '') return;

        const display = document.getElementById('displayResultadoMesa');
        display.innerHTML = `
            <div class="animate-pulse flex flex-col items-center">
                <i class="bi bi-hourglass-split text-4xl text-purple-600 mb-2"></i>
                <p class="font-bold text-slate-700 text-sm">Verificando en padrón oficial...</p>
            </div>
        `;

        const listaId = document.getElementById('selectListaVoto')?.value || null;
        const tipoVoto = document.getElementById('selectTipoVoto')?.value || 'VALIDO';

        fetch("{{ route('elecciones.votar', $comicio->id) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                dni_o_qr: codigo,
                lista_electoral_id: listaId,
                tipo_voto: tipoVoto
            })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                // Voto Válido
                beep(880, 0.25, 'sine');
                setTimeout(() => beep(1174, 0.35, 'sine'), 150);

                display.className = "bg-emerald-50 border-2 border-emerald-500 rounded-2xl p-6 text-center text-emerald-950 space-y-3";
                display.innerHTML = `
                    <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-3xl font-black">
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <div>
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-600 text-white">VOTO CONFORME</span>
                        <h4 class="text-lg font-black mt-2">${body.padre.nombre_completo}</h4>
                        <p class="text-xs font-mono font-bold text-emerald-800">DNI: ${body.padre.dni} • HORA: ${body.padre.hora_voto}</p>
                    </div>
                    <p class="text-xs text-emerald-700 font-medium">Asociado habilitado en padrón. Sufragio completado con éxito.</p>
                `;

                // Actualizar contador
                const cont = document.getElementById('contadorVotosMesa');
                if (cont) cont.textContent = parseInt(cont.textContent || 0) + 1;

            } else if (body.already_voted) {
                // ALERTA: Doble votación detectada
                beep(220, 0.4, 'sawtooth');
                setTimeout(() => beep(180, 0.5, 'sawtooth'), 200);

                display.className = "bg-red-50 border-2 border-red-500 rounded-2xl p-6 text-center text-red-950 space-y-3";
                display.innerHTML = `
                    <div class="w-14 h-14 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto text-3xl font-black">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div>
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-red-600 text-white">¡ALERTA! YA EMITIÓ SU VOTO</span>
                        <p class="text-sm font-bold text-red-800 mt-2">${body.message}</p>
                    </div>
                    <p class="text-xs text-red-600">Por mandato de la Ley N° 28628, solo se admite un voto por familia asociada.</p>
                `;
            } else {
                // No encontrado u otro error
                beep(300, 0.3, 'square');
                display.className = "bg-amber-50 border-2 border-amber-400 rounded-2xl p-6 text-center text-amber-950 space-y-3";
                display.innerHTML = `
                    <i class="bi bi-person-x text-4xl text-amber-600"></i>
                    <p class="text-sm font-bold text-amber-900">${body.message || 'Elector no encontrado en el padrón electoral.'}</p>
                `;
            }

            document.getElementById('inputDniMesa').value = '';
            document.getElementById('inputDniMesa').focus();
        })
        .catch(err => {
            console.error(err);
            display.innerHTML = `<p class="text-red-600 text-xs">Error de conexión al servidor.</p>`;
        });
    }

    // Formulario manual
    document.getElementById('formVotoManual').addEventListener('submit', function(e) {
        e.preventDefault();
        const val = document.getElementById('inputDniMesa').value;
        emitirVoto(val);
    });

    // Iniciar Cámara
    document.getElementById('btnStartMesaCam').addEventListener('click', function() {
        document.getElementById('scanner-overlay-mesa').classList.add('hidden');
        html5QrCode = new Html5Qrcode("qr-reader-mesa");
        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            (decodedText) => {
                emitirVoto(decodedText);
            },
            (error) => {}
        ).catch(err => {
            alert("No se pudo acceder a la cámara: " + err);
            document.getElementById('scanner-overlay-mesa').classList.remove('hidden');
        });
    });
</script>
@endsection
