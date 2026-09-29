@props([
    'title' => null,
    'icon' => null,
    'subtitle' => null,
    'actions' => null,
    'padding' => 'p-6',
    'class' => '',
])

<div {{ $attributes->merge(['class' => "bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden {$class}"]) }}>
    @if($title || $icon || $actions)
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
            <div class="flex items-center gap-2.5">
                @if($icon)
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0 text-sm shadow-xs">
                        <i class="{{ $icon }}"></i>
                    </div>
                @endif
                <div>
                    @if($title)
                        <h3 class="font-extrabold text-slate-800 text-sm tracking-tight">{{ $title }}</h3>
                    @endif
                    @if($subtitle)
                        <p class="text-xs text-slate-500 font-normal mt-0.5">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
            @if($actions)
                <div class="flex items-center gap-2 flex-wrap">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>
</div>
