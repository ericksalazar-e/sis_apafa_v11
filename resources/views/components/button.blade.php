@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'type' => 'button',
])

@php
    $variantClass = match($variant) {
        'secondary' => 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200/80 shadow-xs',
        'danger' => 'bg-rose-600 hover:bg-rose-700 text-white shadow-md shadow-rose-600/20',
        'success' => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20',
        'outline' => 'bg-transparent hover:bg-slate-50 text-slate-700 border border-slate-300 shadow-xs',
        'dark' => 'bg-slate-900 hover:bg-black text-white shadow-md shadow-slate-900/20',
        default => 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/25',
    };

    $sizeClass = match($size) {
        'xs' => 'text-[11px] px-2.5 py-1.5 rounded-lg gap-1.5 font-bold',
        'sm' => 'text-xs px-3.5 py-2 rounded-xl gap-2 font-bold',
        'lg' => 'text-sm px-6 py-3 rounded-2xl gap-2.5 font-extrabold',
        default => 'text-xs px-4 py-2.5 rounded-xl gap-2 font-bold',
    };
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "inline-flex items-center justify-center transition-all duration-150 cursor-pointer active:scale-95 disabled:opacity-50 disabled:pointer-events-none {$variantClass} {$sizeClass}"]) }}>
    @if($icon)
        <i class="{{ $icon }} text-xs"></i>
    @endif
    {{ $slot }}
</button>
