@extends('layouts.app')

@section('title', 'Asambleas Generales y Control de Asistencia')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-slate-800">Convocatoria de Reuniones</h3>
            <p class="text-xs text-slate-500">Gestión de asambleas, control de multas por inasistencia y asistencia por código de barras / QR</p>
        </div>
        <button onclick="document.getElementById('modal-nueva-reunion').classList.remove('hidden')" 
                class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm transition-all">
            <i class="fa-solid fa-plus"></i> Convocar Asamblea
        </button>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs shadow-sm flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-emerald-600 text-lg flex-shrink-0"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs shadow-sm flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg flex-shrink-0"></i>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs shadow-sm flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation text-rose-500 text-lg flex-shrink-0 mt-0.5"></i>
            <div>
                <strong class="font-bold block">No se pudo convocar la asamblea:</strong>
                <ul class="list-disc list-inside mt-1 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Meetings List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($reuniones as $reu)
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $reu->estado_reunion_id == 1 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                            {{ $reu->estadoReunion->estado ?? 'ACTIVO' }}
                        </span>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-rose-600">
                                Multa: S/ {{ number_format($reu->multa_precio, 2) }}
                            </span>

                            @if(in_array(auth()->user()->rol ?? '', ['ADMIN', 'SECRETARIO']))
                                <form action="{{ route('reuniones.destroy', $reu) }}" method="POST" 
                                      onsubmit="return confirmDelete(event, this, '¿Eliminar asamblea \'{{ addslashes($reu->titulo) }}\'?', 'Esta acción no se puede deshacer. Se eliminarán los registros de asistencia vinculados.');" 
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Eliminar Asamblea" class="w-6 h-6 rounded-lg text-slate-300 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition-all ml-1">
                                        <i class="fa-solid fa-trash-can text-[11px]"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <h4 class="font-bold text-slate-800 text-sm mb-1">{{ $reu->titulo }}</h4>
                    <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                        {{ $reu->descripcion ?? 'Sin descripción adicional.' }}
                    </p>

                    <div class="mt-4 space-y-1.5 text-xs text-slate-600">
                        <p class="flex items-center gap-2">
                            <i class="fa-regular fa-calendar text-indigo-500"></i>
                            <span>{{ $reu->fecha ? $reu->fecha->format('d/m/Y') : '-' }}</span>
                        </p>
                        <p class="flex items-center gap-2">
                            <i class="fa-regular fa-clock text-indigo-500"></i>
                            <span>{{ $reu->hora ?? '-' }}</span>
                        </p>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-400">
                        <i class="fa-solid fa-users"></i> {{ $reu->reuniones_padres_count }} asociados
                    </span>
                    <a href="{{ route('reuniones.show', $reu) }}" class="px-3.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-qrcode"></i> Pasar Asistencia
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl p-10 text-center text-slate-400 border border-slate-200/80">
                No hay asambleas programadas en esta institución.
            </div>
        @endforelse
    </div>

    @if($reuniones->hasPages())
        <div class="pt-2">
            {{ $reuniones->links() }}
        </div>
    @endif

</div>

<!-- Modal Nueva Reunion -->
<div id="modal-nueva-reunion" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-calendar-plus text-indigo-600"></i> Convocar Nueva Asamblea
            </h3>
            <button onclick="document.getElementById('modal-nueva-reunion').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('reuniones.store') }}" method="POST" class="space-y-4 text-xs" onsubmit="const btn = this.querySelector('button[type=submit]'); btn.disabled = true; btn.innerHTML = '<i class=\'fa-solid fa-spinner fa-spin\'></i> Convocando...';">
            @csrf
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Título de la Asamblea *</label>
                <input type="text" name="titulo" required placeholder="Ej: ASAMBLEA GENERAL EXTRAORDINARIA" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 uppercase">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Fecha de la Asamblea *</label>
                    <input type="date" name="fecha" required value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
                    <p class="text-[11px] text-slate-400 mt-1"><i class="fa-solid fa-circle-info text-indigo-500"></i> Solo se permite 1 asamblea por día.</p>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Hora *</label>
                    <input type="time" name="hora" required value="09:00" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Monto de Multa por Inasistencia (S/ PEN) *</label>
                <input type="number" step="1.00" min="0" name="multa_precio" required placeholder="20.00" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono font-bold">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Agenda / Descripción</label>
                <textarea name="descripcion" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-nueva-reunion').classList.add('hidden')" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl font-semibold">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-md shadow-indigo-600/30">
                    Convocar Asamblea
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
