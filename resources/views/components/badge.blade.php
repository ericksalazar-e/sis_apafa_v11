@props([
    'color' => 'indigo',
    'size' => 'sm',
    'dot' => false,
])

@php
    $colorClass = match($color) {
        'emerald', 'green', 'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
        'rose', 'red', 'danger' => 'bg-rose-50 text-rose-700 border-rose-200/80',
        'amber', 'yellow', 'warning' => 'bg-amber-50 text-amber-700 border-amber-200/80',
        'blue', 'sky', 'info' => 'bg-blue-50 text-blue-700 border-blue-200/80',
        'purple', 'violet' => 'bg-purple-50 text-purple-700 border-purple-200/80',
        'slate', 'gray' => 'bg-slate-100 text-slate-700 border-slate-200',
        default => 'bg-indigo-50 text-indigo-700 border-indigo-200/80',
    };

    $dotClass = match($color) {
        'emerald', 'green', 'success' => 'bg-emerald-500',
        'rose', 'red', 'danger' => 'bg-rose-500',
        'amber', 'yellow', 'warning' => 'bg-amber-500',
        'blue', 'sky', 'info' => 'bg-blue-500',
        'purple', 'violet' => 'bg-purple-500',
        'slate', 'gray' => 'bg-slate-500',
        default => 'bg-indigo-500',
    };

    $sizeClass = match($size) {
        'xs' => 'text-[10px] px-2 py-0.5',
        'md' => 'text-xs px-3 py-1.5',
        default => 'text-[11px] px-2.5 py-1',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 font-bold rounded-xl border {$colorClass} {$sizeClass}"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
    @endif
    {{ $slot }}
</span>
