@props([
    'type' => 'info',
    'message' => null,
    'dismissible' => true,
])

@php
    $style = match($type) {
        'success' => [
            'bg' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
            'icon' => 'fa-solid fa-circle-check text-emerald-600',
        ],
        'error', 'danger' => [
            'bg' => 'bg-rose-50 border-rose-200 text-rose-800',
            'icon' => 'fa-solid fa-circle-xmark text-rose-600',
        ],
        'warning' => [
            'bg' => 'bg-amber-50 border-amber-200 text-amber-800',
            'icon' => 'fa-solid fa-triangle-exclamation text-amber-600',
        ],
        default => [
            'bg' => 'bg-blue-50 border-blue-200 text-blue-800',
            'icon' => 'fa-solid fa-circle-info text-blue-600',
        ],
    };
@endphp

<div {{ $attributes->merge(['class' => "p-4 rounded-2xl border flex items-start gap-3 {$style['bg']} shadow-xs relative transition-all"]) }} role="alert">
    <div class="text-base flex-shrink-0 mt-0.5">
        <i class="{{ $style['icon'] }}"></i>
    </div>
    <div class="flex-1 text-xs leading-relaxed font-medium">
        {{ $message ?? $slot }}
    </div>
    @if($dismissible)
        <button type="button" onclick="this.closest('[role=alert]').remove()" class="text-slate-400 hover:text-slate-600 transition-colors p-1 -mr-1 -mt-1 rounded-lg" aria-label="Cerrar">
            <i class="fa-solid fa-xmark text-xs"></i>
        </button>
    @endif
</div>
