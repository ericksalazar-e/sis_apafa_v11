@extends('layouts.app')

@section('title', 'Control de Asistencia - ' . $reunion->titulo)

@section('content')
<div class="space-y-6">

    <!-- Meeting Title & Status Banner -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Asamblea {{ $reunion->tipo ?? 'General' }}
                </span>
                <span class="text-xs font-semibold text-slate-500">
                    <i class="fa-regular fa-calendar text-slate-400"></i> {{ date('d/m/Y', strtotime($reunion->fecha)) }}
                </span>
            </div>
            <h1 class="text-lg sm:text-xl font-black text-slate-900 mt-1">{{ $reunion->titulo }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">{{ $reunion->descripcion ?? 'Registro de quórum y asistencia de padres asociados.' }}</p>
        </div>

        <a href="{{ route('reuniones.index') }}" 
           class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors inline-flex items-center gap-2 self-start sm:self-center">
            <i class="fa-solid fa-arrow-left"></i> Volver a Asambleas
        </a>
    </div>

    <!-- Meeting Summary & Scanner Card -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: QR / Barcode Scanner Field (Optimized for Mobile & Desktop) -->
        <div class="lg:col-span-1 bg-white rounded-2xl p-4 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between space-y-4">
            <div>
                <!-- Mode Switcher -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-barcode"></i>
                        </div>
                        <div>
                            <h3 class="text-xs sm:text-sm font-bold text-slate-800">Escáner de Asistencia</h3>
                            <p class="text-[10px] text-slate-400">Padres de Familia Asociados</p>
                        </div>
                    </div>

                    <!-- Mode Toggle -->
                    <div class="inline-flex p-0.5 bg-slate-100 rounded-xl">
                        <button type="button" id="tab-reunion-cam" onclick="switchReunionMode('camera')" 
                                class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all bg-indigo-600 text-white shadow-sm">
                            📱 Cámara
                        </button>
                        <button type="button" id="tab-reunion-gun" onclick="switchReunionMode('gun')" 
                                class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all text-slate-600 hover:text-slate-900">
                            🔫 Pistola
                        </button>
                    </div>
                </div>

                <!-- MODE 1: SMARTPHONE CAMERA VIEW -->
                <div id="reunion-mode-camera" class="space-y-3">
                    <div class="relative w-full overflow-hidden rounded-2xl bg-black aspect-square flex items-center justify-center border-2 border-indigo-500/40 shadow-inner">
                        <div id="parent-qr-reader" class="w-full h-full"></div>
                        <div id="reunion-cam-placeholder" class="text-center p-4 space-y-2 absolute">
                            <div class="w-12 h-12 mx-auto rounded-xl bg-indigo-500/20 text-indigo-300 flex items-center justify-center text-2xl">
                                <i class="fa-solid fa-camera"></i>
                            </div>
                            <p class="text-[11px] text-slate-300">Escanee el carné APAFA del asociado</p>
                            <button type="button" onclick="startParentCamera()" 
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center gap-1.5 mx-auto">
                                <i class="fa-solid fa-play"></i>
                                <span>Activar Cámara</span>
                            </button>
                        </div>
                        <div id="reunion-scan-laser" class="hidden absolute left-3 right-3 h-0.5 bg-gradient-to-r from-transparent via-red-500 to-transparent shadow-[0_0_10px_rgba(239,68,68,1)] animate-pulse pointer-events-none top-1/2"></div>
                    </div>

                    <!-- Camera buttons -->
                    <div class="flex items-center justify-center gap-2 pt-1">
                        <button type="button" id="btn-reunion-stop" onclick="toggleParentCameraState()" 
                                class="hidden px-3 py-1.5 bg-rose-600 text-white font-bold text-xs rounded-xl transition-all items-center gap-1.5">
                            <i class="fa-solid fa-stop"></i> Detener
                        </button>
                        <button type="button" id="btn-reunion-switch" onclick="switchParentCameraFacing()" 
                                class="hidden px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl border border-slate-300 transition-all items-center gap-1.5">
                            <i class="fa-solid fa-repeat"></i> Cambiar
                        </button>
                    </div>

                    <!-- Live scan feedback card -->
                    <div id="reunion-live-card" class="hidden p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-left transition-all">
                        <!-- Populated by JS -->
                    </div>
                </div>

                <!-- MODE 2: HARDWARE PISTOL / MANUAL DNI -->
                <div id="reunion-mode-gun" class="hidden space-y-3">
                    <form action="{{ route('reuniones.asistencia', $reunion) }}" method="POST" id="form-reunion-scan" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">DNI del Asociado o Código QR</label>
                            <div class="relative">
                                <input type="text" name="dni" id="reunion-dni-input" maxlength="150" required 
                                       placeholder="Escanee con pistola o escriba DNI..."
                                       class="w-full pl-9 pr-4 py-3 bg-slate-50 border-2 border-indigo-500/40 rounded-xl text-base font-mono font-bold text-slate-800 focus:border-indigo-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                <i class="fa-solid fa-qrcode absolute left-3.5 top-4 text-indigo-500 text-base"></i>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/30 transition-all flex items-center justify-center gap-2 active:scale-95">
                            <i class="fa-solid fa-check"></i> Registrar Asistencia
                        </button>
                    </form>
                </div>
            </div>

            <!-- Stats (Quorum & Attendance) -->
            <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-100 text-center text-xs">
                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                    <p class="text-[10px] uppercase font-bold text-emerald-600">Asistieron</p>
                    <p id="stat-asistieron" class="text-xl font-black text-emerald-800 mt-0.5">{{ $totalAsistieron }}</p>
                </div>
                <div class="p-3 bg-rose-50 rounded-xl border border-rose-100">
                    <p class="text-[10px] uppercase font-bold text-rose-600">Inasistentes</p>
                    <p id="stat-faltaron" class="text-xl font-black text-rose-800 mt-0.5">{{ $totalFaltaron }}</p>
                </div>
            </div>
        </div>

        <!-- Right: Attendees List (Responsive Table) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-bold text-slate-800">Padrón de Asistencia</h4>
                    <p class="text-xs text-slate-500">{{ $asistencias->count() }} padres convocados para esta asamblea</p>
                </div>
                <div class="text-xs font-bold text-slate-500">
                    Quórum: <span id="stat-quorum-pct" class="text-indigo-600 font-extrabold">{{ $asistencias->count() > 0 ? round(($totalAsistieron / $asistencias->count()) * 100, 1) : 0 }}%</span>
                </div>
            </div>

            <div class="flex-1 overflow-x-auto max-h-[500px]">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200 sticky top-0 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-4 py-3">DNI</th>
                            <th class="px-4 py-3">Padre de Familia</th>
                            <th class="px-4 py-3">Hora Ingreso</th>
                            <th class="px-4 py-3">Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-asistencia-padres" class="divide-y divide-slate-100">
                        @foreach($asistencias as $asist)
                            <tr id="padre-row-{{ $asist->padreFamilia->padre_dni ?? $asist->id }}" class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-4 py-3 font-mono font-bold text-slate-700">
                                    {{ $asist->padreFamilia->padre_dni ?? '-' }}
                                </td>
                                <td class="px-4 py-3 font-bold text-slate-800">
                                    {{ $asist->padreFamilia->nombre_completo ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 font-mono text-slate-600 td-hora">
                                    {{ $asist->asistencia ? $asist->asistencia->format('H:i:s') : '-' }}
                                </td>
                                <td class="px-4 py-3 td-estado">
                                    @if($asist->asistencia)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            <i class="fa-solid fa-check"></i> Presente
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                            <i class="fa-solid fa-xmark"></i> Falta
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<!-- Camera and Scanner Scripts for Assemblies -->
<script>
    function playBeepSound(success = true) {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = success ? 'sine' : 'square';
            osc.frequency.setValueAtTime(success ? 900 : 400, ctx.currentTime);
            if (success) osc.frequency.setValueAtTime(1400, ctx.currentTime + 0.08);
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.2);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.2);
        } catch (e) {}
    }

    function triggerAssemblyHaptic() {
        if ('vibrate' in navigator) {
            try { navigator.vibrate([70, 40, 70]); } catch (e) {}
        }
    }

    function switchReunionMode(mode) {
        const modeCam = document.getElementById('reunion-mode-camera');
        const modeGun = document.getElementById('reunion-mode-gun');
        const tabCam = document.getElementById('tab-reunion-cam');
        const tabGun = document.getElementById('tab-reunion-gun');

        if (mode === 'camera') {
            modeCam.classList.remove('hidden');
            modeGun.classList.add('hidden');
            tabCam.className = "px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all bg-indigo-600 text-white shadow-sm";
            tabGun.className = "px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all text-slate-600 hover:text-slate-900";
        } else {
            modeCam.classList.add('hidden');
            modeGun.classList.remove('hidden');
            tabGun.className = "px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all bg-indigo-600 text-white shadow-sm";
            tabCam.className = "px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all text-slate-600 hover:text-slate-900";
            stopParentCamera();
            const input = document.getElementById('reunion-dni-input');
            if (input) setTimeout(() => input.focus(), 150);
        }
    }

    let parentQrCode = null;
    let isParentCamActive = false;
    let parentCameraFacing = "environment";
    let isProcessingParentScan = false;

    function startParentCamera() {
        const placeholder = document.getElementById('reunion-cam-placeholder');
        const btnStop = document.getElementById('btn-reunion-stop');
        const btnSwitch = document.getElementById('btn-reunion-switch');
        const laser = document.getElementById('reunion-scan-laser');

        if (typeof Html5Qrcode === 'undefined') {
            alert('Librería del escáner en carga. Espere un momento.');
            return;
        }

        if (!parentQrCode) {
            parentQrCode = new Html5Qrcode("parent-qr-reader");
        }

        const config = {
            fps: 15,
            qrbox: { width: 220, height: 220 },
            aspectRatio: 1.0,
            formatsToSupport: [
                Html5QrcodeSupportedFormats.QR_CODE,
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.EAN_13,
            ]
        };

        parentQrCode.start(
            { facingMode: parentCameraFacing },
            config,
            onParentScanSuccess,
            () => {}
        ).then(() => {
            isParentCamActive = true;
            if (placeholder) placeholder.style.display = 'none';
            if (btnStop) {
                btnStop.classList.remove('hidden');
                btnStop.classList.add('inline-flex');
            }
            if (btnSwitch) {
                btnSwitch.classList.remove('hidden');
                btnSwitch.classList.add('inline-flex');
            }
            if (laser) laser.classList.remove('hidden');
        }).catch(err => {
            console.error("Error cámara asamblea:", err);
            alert("No se pudo iniciar la cámara. Conceda permisos o use la pestaña 'Pistola'.");
        });
    }

    function stopParentCamera() {
        if (parentQrCode && isParentCamActive) {
            parentQrCode.stop().then(() => {
                isParentCamActive = false;
                const placeholder = document.getElementById('reunion-cam-placeholder');
                const btnStop = document.getElementById('btn-reunion-stop');
                const btnSwitch = document.getElementById('btn-reunion-switch');
                const laser = document.getElementById('reunion-scan-laser');
                if (placeholder) placeholder.style.display = 'block';
                if (btnStop) {
                    btnStop.classList.remove('inline-flex');
                    btnStop.classList.add('hidden');
                }
                if (btnSwitch) {
                    btnSwitch.classList.remove('inline-flex');
                    btnSwitch.classList.add('hidden');
                }
                if (laser) laser.classList.add('hidden');
            }).catch(err => console.error(err));
        }
    }

    function toggleParentCameraState() {
        if (isParentCamActive) stopParentCamera();
        else startParentCamera();
    }

    function switchParentCameraFacing() {
        if (!isParentCamActive) return;
        parentCameraFacing = parentCameraFacing === "environment" ? "user" : "environment";
        stopParentCamera();
        setTimeout(() => startParentCamera(), 300);
    }

    function onParentScanSuccess(decodedText) {
        if (isProcessingParentScan) return;
        isProcessingParentScan = true;

        playBeepSound(true);
        triggerAssemblyHaptic();

        const card = document.getElementById('reunion-live-card');
        if (card) {
            card.classList.remove('hidden');
            card.className = "p-3 rounded-xl bg-indigo-50 border border-indigo-200 text-left transition-all";
            card.innerHTML = `
                <div class="flex items-center gap-2.5 text-xs text-indigo-700">
                    <div class="w-5 h-5 rounded-full border-2 border-indigo-600 border-t-transparent animate-spin"></div>
                    <span class="font-bold">Verificando DNI / Carné: ${decodedText}...</span>
                </div>
            `;
        }

        fetch("{{ route('reuniones.asistencia', $reunion) }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({ dni: decodedText })
        })
        .then(async res => {
            const data = await res.json();
            if (res.ok && data.success) {
                renderParentSuccess(data);
            } else {
                playBeepSound(false);
                renderParentError(data.message || 'El asociado no está convocado a esta asamblea.');
            }
        })
        .catch(err => {
            console.error(err);
            playBeepSound(false);
            renderParentError('Error de conexión con el servidor.');
        })
        .finally(() => {
            setTimeout(() => {
                isProcessingParentScan = false;
            }, 2500);
        });
    }

    function renderParentSuccess(data) {
        const card = document.getElementById('reunion-live-card');
        if (!card) return;

        const p = data.padre || {};
        card.className = "p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-left transition-all";
        card.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    <div>
                        <p class="text-xs font-black text-slate-800 leading-tight">${p.nombre || 'Padre de Familia'}</p>
                        <p class="text-[10px] text-slate-500 font-mono">DNI: <strong>${p.dni}</strong> &bull; Hora: <strong>${p.asistencia}</strong></p>
                    </div>
                </div>
                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-extrabold uppercase">
                    Presente
                </span>
            </div>
        `;

        // Update row in table if present, or prepend if newly incorporated
        const tbody = document.getElementById('tabla-asistencia-padres');
        const row = document.getElementById(`padre-row-${p.dni}`);
        if (row) {
            row.classList.add('bg-emerald-50/70');
            const tdHora = row.querySelector('.td-hora');
            const tdEstado = row.querySelector('.td-estado');
            if (tdHora) tdHora.textContent = p.asistencia;
            if (tdEstado) tdEstado.innerHTML = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800"><i class="fa-solid fa-check"></i> Presente</span>`;
        } else if (tbody) {
            const newRow = document.createElement('tr');
            newRow.id = `padre-row-${p.dni}`;
            newRow.className = 'hover:bg-slate-50/60 transition-colors bg-emerald-50/70';
            newRow.innerHTML = `
                <td class="px-4 py-3 font-mono font-bold text-slate-700">${p.dni}</td>
                <td class="px-4 py-3 font-bold text-slate-800">${p.nombre}</td>
                <td class="px-4 py-3 font-mono text-slate-600 td-hora">${p.asistencia}</td>
                <td class="px-4 py-3 td-estado">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                        <i class="fa-solid fa-check"></i> Presente
                    </span>
                </td>
            `;
            tbody.prepend(newRow);
        }

        // Update stats and Quorum percentage in real time
        const statAsist = document.getElementById('stat-asistieron');
        const statFalt = document.getElementById('stat-faltaron');
        const statQuorum = document.getElementById('stat-quorum-pct');
        if (statAsist && statFalt) {
            let a = parseInt(statAsist.textContent) || 0;
            let f = parseInt(statFalt.textContent) || 0;
            statAsist.textContent = a + 1;
            if (f > 0 && row) statFalt.textContent = f - 1;
            const total = (a + 1) + (f > 0 && row ? f - 1 : f);
            if (statQuorum && total > 0) {
                statQuorum.textContent = (((a + 1) / total) * 100).toFixed(1) + '%';
            }
        }
    }

    function renderParentError(msg) {
        const card = document.getElementById('reunion-live-card');
        if (!card) return;
        card.className = "p-3 rounded-xl bg-rose-50 border border-rose-200 text-left transition-all";
        card.innerHTML = `
            <div class="flex items-center gap-2 text-rose-700">
                <i class="fa-solid fa-circle-xmark text-rose-500 text-base flex-shrink-0"></i>
                <p class="text-xs font-bold leading-tight">${msg}</p>
            </div>
        `;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('reunion-dni-input');
        if (input && !document.getElementById('reunion-mode-gun').classList.contains('hidden')) {
            input.focus();
        }
    });
</script>
@endsection

