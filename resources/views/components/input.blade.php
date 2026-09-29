@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'required' => false,
    'placeholder' => null,
    'icon' => null,
    'hint' => null,
])

<div class="space-y-1">
    @if($label)
        <label for="{{ $name }}" class="block font-semibold text-slate-700 text-xs">
            {{ $label }}
            @if($required)
                <span class="text-rose-500 font-bold">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        @if($icon)
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                <i class="{{ $icon }}"></i>
            </div>
        @endif

        <input 
            type="{{ $type }}" 
            name="{{ $name }}" 
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            @if($required) required @endif
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            {{ $attributes->merge(['class' => 'w-full ' . ($icon ? 'pl-9 ' : 'px-3.5 ') . 'py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:outline-none transition-all ' . ($errors->has($name) ? 'border-rose-400 bg-rose-50/30' : '')]) }}
        >
    </div>

    @if($hint)
        <p class="text-[10px] text-slate-400 mt-0.5">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="text-[11px] text-rose-600 font-medium mt-1 flex items-center gap-1">
            <i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}
        </p>
    @enderror
</div>
