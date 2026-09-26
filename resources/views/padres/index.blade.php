@extends('layouts.app')

@section('title', 'Padrón de Padres de Familia')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <!-- Search -->
        <form action="{{ route('padres.index') }}" method="GET" class="w-full sm:w-80">
            <div class="relative">
                <input type="text" name="search" value="{{ $search ?? '' }}" 
                       placeholder="Buscar por DNI o Apellidos..." 
                       class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none shadow-sm">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400 text-xs"></i>
            </div>
        </form>

        <!-- New Parent Modal Trigger -->
        <button onclick="document.getElementById('modal-nuevo-padre').classList.remove('hidden')" 
                class="w-full sm:w-auto px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-sm transition-all">
                <i class="fa-solid fa-user-plus"></i>
            Registrar Padre de Familia
        </button>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-500 font-semibold border-b border-slate-200/60 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-5 py-3.5">DNI</th>
                        <th class="px-5 py-3.5">Apellidos y Nombres</th>
                        <th class="px-5 py-3.5">Cargo APAFA</th>
                        <th class="px-5 py-3.5">Parentesco</th>
                        <th class="px-5 py-3.5">Hijos Asociados</th>
                        <th class="px-5 py-3.5">Contacto</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($padres as $padre)
                        @php
                            $padreJson = [
                                'id' => $padre->id,
                                'padre_dni' => $padre->padre_dni,
                                'padre_nombres' => $padre->padre_nombres,
                                'padre_ape_pat' => $padre->padre_ape_pat,
                                'padre_ape_mat' => $padre->padre_ape_mat,
                                'celular' => $padre->celular,
                                'direccion' => $padre->direccion,
                                'cargo_id' => $mapCargosCanonicos[$padre->cargo_id] ?? $padre->cargo_id,
                                'tipo_consanguinidad_id' => $padre->tipo_consanguinidad_id,
                                'conyuge_nombres' => $padre->conyuge_nombres,
                                'conyuge_dni' => $padre->conyuge_dni,
                                'conyuge_celular' => $padre->conyuge_celular,
                                'nombre_completo' => $padre->nombre_completo,
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-3.5 font-mono font-bold text-slate-700">{{ $padre->padre_dni }}</td>
                            <td class="px-5 py-3.5 font-bold text-slate-800">
                                {{ $padre->nombre_completo }}
                                @if($padre->conyuge_nombres)
                                    <div class="text-[11px] text-indigo-700 font-semibold flex items-center gap-1 mt-0.5">
                                        <i class="fa-solid fa-people-roof text-indigo-500"></i>
                                        <span>Cónyuge: {{ $padre->conyuge_nombres }} @if($padre->conyuge_dni)(DNI: {{ $padre->conyuge_dni }})@endif</span>
                                    </div>
                                @endif
                                <span class="block text-[10px] text-slate-400 font-normal truncate max-w-xs">{{ $padre->direccion ?? 'Sin dirección' }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ ($padre->cargo && mb_strtoupper(trim($padre->cargo->nombre)) !== 'PADRE DE FAMILIA') ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $padre->cargo->nombre ?? 'PADRE DE FAMILIA' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                @php
                                    $par = strtoupper($padre->parentesco_texto);
                                    $badgeClass = 'bg-slate-100 text-slate-600 border-slate-200';
                                    $icon = 'fa-solid fa-user';
                                    if (str_contains($par, 'AMBOS') || str_contains($par, 'PADRE O MADRE') || str_contains($par, 'TÍO O TÍA') || str_contains($par, 'ABUELO O ABUELA')) {
                                        $badgeClass = 'bg-purple-50 text-purple-700 border-purple-200';
                                        $icon = 'fa-solid fa-people-roof';
                                    } elseif (str_contains($par, 'HERMANO') || str_contains($par, 'HERMANA')) {
                                        $badgeClass = 'bg-teal-50 text-teal-700 border-teal-200';
                                        $icon = 'fa-solid fa-user-group';
                                    } elseif (str_contains($par, 'MADRE') || str_contains($par, 'TÍA') || str_contains($par, 'ABUELA')) {
                                        $badgeClass = 'bg-pink-50 text-pink-700 border-pink-200';
                                        $icon = 'fa-solid fa-person-dress';
                                    } elseif (str_contains($par, 'PADRE') || str_contains($par, 'TÍO') || str_contains($par, 'ABUELO')) {
                                        $badgeClass = 'bg-indigo-50 text-indigo-700 border-indigo-200';
                                        $icon = 'fa-solid fa-person';
                                    } elseif (str_contains($par, 'TUTOR') || str_contains($par, 'APODERADO')) {
                                        $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
                                        $icon = 'fa-solid fa-user-shield';
                                    }
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold border {{ $badgeClass }}">
                                    <i class="{{ $icon }}"></i> {{ $padre->parentesco_texto }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="h-6 w-6 rounded-full bg-violet-100 text-violet-700 font-bold flex items-center justify-center text-[11px]">
                                        {{ $padre->estudiantes->count() }}
                                    </span>
                                    <span class="text-[11px] text-slate-500">alumno(s)</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600 font-mono text-[11px]">
                                <div>{{ $padre->celular ?? '-' }}</div>
                                @if($padre->conyuge_celular)
                                    <div class="text-[10px] text-slate-400">2do: {{ $padre->conyuge_celular }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                @if(in_array(auth()->user()->rol ?? '', ['ADMIN', 'SECRETARIO']))
                                <button type="button" 
                                        onclick='abrirModalEditarPadre(@json($padreJson))'
                                        class="px-2.5 py-1.5 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-600 text-xs font-bold rounded-xl transition-all inline-flex items-center gap-1.5" 
                                        title="Editar datos del padre o apoderado (DNI, nombres, cónyuge)">
                                    <i class="fa-solid fa-pen-to-square"></i> Editar
                                </button>
                                @endif
                                <a href="{{ route('carta_poder.padre', $padre) }}" target="_blank"
                                   class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-700 text-xs font-bold rounded-xl transition-all inline-flex items-center gap-1.5" title="Generar Carta Poder Simple (D.S. N.º 007-2026-MINEDU)">
                                    <i class="fa-solid fa-file-signature"></i> Carta Poder
                                </a>
                                <a href="{{ route('padres.tarjeta', $padre) }}" target="_blank"
                                   class="px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-600 hover:text-white text-indigo-700 text-xs font-bold rounded-xl transition-all inline-flex items-center gap-1.5" title="Ver / Imprimir Carné APAFA">
                                    <i class="fa-solid fa-id-card"></i> Tarjeta APAFA
                                </a>
                                @if(in_array(auth()->user()->rol ?? '', ['ADMIN', 'SECRETARIO']))
                                <form action="{{ route('padres.destroy', $padre) }}" method="POST" 
                                      onsubmit="return confirmDelete(event, this, '¿Eliminar padre de familia \'{{ addslashes($padre->padre_nombres . ' ' . $padre->padre_ape_pat) }}\'?', 'Esta acción no se puede deshacer. Se removerá del padrón institucional.');" 
                                      class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors" title="Eliminar">
                                        <i class="fa-regular fa-trash-can text-sm"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-slate-400">
                                No se encontraron registros de padres de familia para esta búsqueda o institución.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($padres->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $padres->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Registro Nuevo Padre -->
<div id="modal-nuevo-padre" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-indigo-600"></i> Nuevo Padre de Familia
            </h3>
            <button onclick="document.getElementById('modal-nuevo-padre').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('padres.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">DNI (8 dígitos) *</label>
                    <input type="text" name="padre_dni" maxlength="8" pattern="[0-9]{8}" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Celular</label>
                    <input type="text" name="celular" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nombres *</label>
                    <input type="text" name="padre_nombres" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 uppercase">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Apellido Paterno *</label>
                    <input type="text" name="padre_ape_pat" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 uppercase">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Apellido Materno</label>
                <input type="text" name="padre_ape_mat" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 uppercase">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Parentesco del Titular *</label>
                    <select name="tipo_consanguinidad_id" id="select-parentesco" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white font-medium" onchange="verificarParentescoAmbos()">
                        @foreach($consanguinidades as $tc)
                            <option value="{{ $tc->id }}" {{ $tc->nombre === 'PADRE' ? 'selected' : '' }}>{{ $tc->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Cargo en Directiva</label>
                    <select name="cargo_id" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white font-medium">
                        @if(!$cargos->contains(fn($c) => mb_strtoupper(trim($c->nombre)) === 'PADRE DE FAMILIA'))
                            <option value="">PADRE DE FAMILIA (Ordinario / Sin Cargo Directivo)</option>
                        @endif
                        @foreach($cargos as $c)
                            <option value="{{ $c->id }}" {{ mb_strtoupper(trim($c->nombre)) === 'PADRE DE FAMILIA' ? 'selected' : '' }}>
                                {{ mb_strtoupper(trim($c->nombre)) === 'PADRE DE FAMILIA' ? 'PADRE DE FAMILIA (Ordinario / Sin Cargo Directivo)' : $c->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Sección Opcional: Madre / Cónyuge / 2do Apoderado (Ambos) -->
            <div class="space-y-2 pt-1">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="chk-conyuge" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" onchange="toggleConyugeBox()">
                    <label for="chk-conyuge" class="font-semibold text-slate-700 text-xs cursor-pointer select-none">
                        Registrar también a la Madre / Cónyuge / 2do Apoderado (Ambos)
                    </label>
                </div>

                <div id="box-conyuge" class="p-3.5 bg-indigo-50/70 rounded-2xl border border-indigo-100 space-y-3 hidden">
                    <span class="text-[11px] font-bold text-indigo-900 uppercase tracking-wider block">
                        <i class="fa-solid fa-people-roof text-indigo-600"></i> Datos del Segundo Apoderado (Madre / Cónyuge)
                    </span>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nombres y Apellidos Completos</label>
                        <input type="text" name="conyuge_nombres" placeholder="Ej: MARÍA ELENA MENDOZA QUISPE" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 uppercase">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">DNI Cónyuge</label>
                            <input type="text" name="conyuge_dni" maxlength="8" placeholder="DNI 8 dígitos" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Celular Cónyuge</label>
                            <input type="text" name="conyuge_celular" placeholder="Celular" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono">
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Dirección de Domicilio</label>
                <input type="text" name="direccion" placeholder="Ej: Jr. Los Libertadores 245" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-nuevo-padre').classList.add('hidden')" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl font-semibold">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-md shadow-indigo-600/30">
                    Guardar Asociado
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Padre de Familia -->
<div id="modal-editar-padre" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 max-h-[92vh] overflow-y-auto space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-user-pen text-indigo-600"></i> Modificar Datos del Padre de Familia
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Corrige el DNI, nombres, apellidos o parentesco en caso de error o confusión</p>
            </div>
            <button onclick="document.getElementById('modal-editar-padre').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="form-editar-padre" method="POST" action="" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">DNI (8 dígitos) *</label>
                    <input type="text" name="padre_dni" id="editar_padre_dni" maxlength="8" pattern="[0-9]{8}" required 
                           class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono font-bold bg-amber-50/50">
                    <span class="text-[10px] text-amber-700 font-semibold mt-0.5 block">Corregir si hubo error de digitación</span>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Celular</label>
                    <input type="text" name="celular" id="editar_padre_celular" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nombres *</label>
                    <input type="text" name="padre_nombres" id="editar_padre_nombres" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 uppercase font-semibold">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Apellido Paterno *</label>
                    <input type="text" name="padre_ape_pat" id="editar_padre_ape_pat" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 uppercase font-semibold">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Apellido Materno</label>
                <input type="text" name="padre_ape_mat" id="editar_padre_ape_mat" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 uppercase font-semibold">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Parentesco del Titular *</label>
                    <select name="tipo_consanguinidad_id" id="editar_select_parentesco" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white font-medium" onchange="verificarParentescoAmbosEditar()">
                        @foreach($consanguinidades as $tc)
                            <option value="{{ $tc->id }}">{{ $tc->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Cargo en Directiva</label>
                    <select name="cargo_id" id="editar_padre_cargo_id" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white font-medium">
                        @if(!$cargos->contains(fn($c) => mb_strtoupper(trim($c->nombre)) === 'PADRE DE FAMILIA'))
                            <option value="">PADRE DE FAMILIA (Ordinario / Sin Cargo Directivo)</option>
                        @endif
                        @foreach($cargos as $c)
                            <option value="{{ $c->id }}">
                                {{ mb_strtoupper(trim($c->nombre)) === 'PADRE DE FAMILIA' ? 'PADRE DE FAMILIA (Ordinario / Sin Cargo Directivo)' : $c->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Sección: Madre / Cónyuge / 2do Apoderado (Ambos) -->
            <div class="space-y-2 pt-1">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="editar_chk_conyuge" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" onchange="toggleConyugeBoxEditar()">
                    <label for="editar_chk_conyuge" class="font-semibold text-slate-700 text-xs cursor-pointer select-none">
                        Registrar también a la Madre / Cónyuge / 2do Apoderado (Ambos)
                    </label>
                </div>

                <div id="editar_box_conyuge" class="p-3.5 bg-indigo-50/70 rounded-2xl border border-indigo-100 space-y-3 hidden">
                    <span class="text-[11px] font-bold text-indigo-900 uppercase tracking-wider block">
                        <i class="fa-solid fa-people-roof text-indigo-600"></i> Datos del Segundo Apoderado (Madre / Cónyuge)
                    </span>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nombres y Apellidos Completos</label>
                        <input type="text" name="conyuge_nombres" id="editar_conyuge_nombres" placeholder="Ej: MARÍA ELENA MENDOZA QUISPE" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 uppercase">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">DNI Cónyuge</label>
                            <input type="text" name="conyuge_dni" id="editar_conyuge_dni" maxlength="8" placeholder="DNI 8 dígitos" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Celular Cónyuge</label>
                            <input type="text" name="conyuge_celular" id="editar_conyuge_celular" placeholder="Celular" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono">
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Dirección de Domicilio</label>
                <input type="text" name="direccion" id="editar_padre_direccion" placeholder="Ej: Jr. Los Libertadores 245" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-editar-padre').classList.add('hidden')" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl font-semibold">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-md shadow-indigo-600/30 flex items-center gap-1.5">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function verificarParentescoAmbos() {
    const sel = document.getElementById('select-parentesco');
    const selectedText = sel.options[sel.selectedIndex].text.toUpperCase();
    const chk = document.getElementById('chk-conyuge');

    if (selectedText.includes('AMBOS') || selectedText.includes('PADRE O MADRE') || selectedText.includes('TÍO O TÍA') || selectedText.includes('ABUELO O ABUELA')) {
        chk.checked = true;
    }
    toggleConyugeBox();
}

function toggleConyugeBox() {
    const chk = document.getElementById('chk-conyuge');
    const box = document.getElementById('box-conyuge');
    if (chk.checked) {
        box.classList.remove('hidden');
    } else {
        box.classList.add('hidden');
    }
}

function abrirModalEditarPadre(padre) {
    const form = document.getElementById('form-editar-padre');
    form.action = `/padres/${padre.id}`;

    document.getElementById('editar_padre_dni').value = padre.padre_dni || '';
    document.getElementById('editar_padre_celular').value = padre.celular || '';
    document.getElementById('editar_padre_nombres').value = padre.padre_nombres || '';
    document.getElementById('editar_padre_ape_pat').value = padre.padre_ape_pat || '';
    document.getElementById('editar_padre_ape_mat').value = padre.padre_ape_mat || '';
    document.getElementById('editar_padre_direccion').value = padre.direccion || '';

    const selParentesco = document.getElementById('editar_select_parentesco');
    if (padre.tipo_consanguinidad_id) {
        selParentesco.value = String(padre.tipo_consanguinidad_id);
    }

    const selCargo = document.getElementById('editar_padre_cargo_id');
    if (padre.cargo_id && selCargo.querySelector(`option[value="${padre.cargo_id}"]`)) {
        selCargo.value = String(padre.cargo_id);
    } else {
        const ordinarioOpt = Array.from(selCargo.options).find(o => o.text.toUpperCase().includes('PADRE DE FAMILIA'));
        selCargo.value = ordinarioOpt ? ordinarioOpt.value : (selCargo.options[0]?.value || '');
    }

    // Cónyuge
    const chk = document.getElementById('editar_chk_conyuge');
    document.getElementById('editar_conyuge_nombres').value = padre.conyuge_nombres || '';
    document.getElementById('editar_conyuge_dni').value = padre.conyuge_dni || '';
    document.getElementById('editar_conyuge_celular').value = padre.conyuge_celular || '';

    if (padre.conyuge_nombres || padre.conyuge_dni || padre.conyuge_celular) {
        chk.checked = true;
    } else {
        chk.checked = false;
    }
    toggleConyugeBoxEditar();

    document.getElementById('modal-editar-padre').classList.remove('hidden');
}

function toggleConyugeBoxEditar() {
    const chk = document.getElementById('editar_chk_conyuge');
    const box = document.getElementById('editar_box_conyuge');
    if (chk.checked) {
        box.classList.remove('hidden');
    } else {
        box.classList.add('hidden');
    }
}

function verificarParentescoAmbosEditar() {
    const sel = document.getElementById('editar_select_parentesco');
    const selectedText = sel.options[sel.selectedIndex].text.toUpperCase();
    const chk = document.getElementById('editar_chk_conyuge');

    if (selectedText.includes('AMBOS') || selectedText.includes('PADRE O MADRE') || selectedText.includes('TÍO O TÍA') || selectedText.includes('ABUELO O ABUELA')) {
        chk.checked = true;
    }
    toggleConyugeBoxEditar();
}
</script>
@endsection
