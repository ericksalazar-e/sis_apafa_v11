@props([
    'title',
    'value',
    'icon' => 'fa-solid fa-chart-simple',
    'color' => 'indigo',
    'subtitle' => null,
    'badge' => null,
    'badgeColor' => 'emerald',
])

@php
    $colorClasses = match($color) {
        'emerald', 'green' => [
            'bg_icon' => 'bg-emerald-50 text-emerald-600',
            'border' => 'border-emerald-100/80',
            'accent' => 'text-emerald-600',
        ],
        'rose', 'red' => [
            'bg_icon' => 'bg-rose-50 text-rose-600',
            'border' => 'border-rose-100/80',
            'accent' => 'text-rose-600',
        ],
        'amber', 'yellow' => [
            'bg_icon' => 'bg-amber-50 text-amber-600',
            'border' => 'border-amber-100/80',
            'accent' => 'text-amber-600',
        ],
        'blue', 'sky' => [
            'bg_icon' => 'bg-blue-50 text-blue-600',
            'border' => 'border-blue-100/80',
            'accent' => 'text-blue-600',
        ],
        'purple', 'violet' => [
            'bg_icon' => 'bg-purple-50 text-purple-600',
            'border' => 'border-purple-100/80',
            'accent' => 'text-purple-600',
        ],
        default => [
            'bg_icon' => 'bg-indigo-50 text-indigo-600',
            'border' => 'border-indigo-100/80',
            'accent' => 'text-indigo-600',
        ],
    };
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-3xl p-5 border border-slate-200/90 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow relative overflow-hidden group']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">{{ $title }}</span>
            <div class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight font-mono">
                {{ $value }}
            </div>
        </div>
        <div class="w-12 h-12 rounded-2xl {{ $colorClasses['bg_icon'] }} flex items-center justify-center text-lg flex-shrink-0 shadow-inner group-hover:scale-105 transition-transform">
            <i class="{{ $icon }}"></i>
        </div>
    </div>

    @if($subtitle || $badge)
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
            @if($subtitle)
                <span class="text-slate-500 font-medium">{{ $subtitle }}</span>
            @endif
            @if($badge)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                    {{ $badge }}
                </span>
            @endif
        </div>
    @endif
</div>
