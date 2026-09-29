@props([
    'headers' => [],
])

<div {{ $attributes->merge(['class' => 'overflow-x-auto rounded-2xl border border-slate-200/80 shadow-xs']) }}>
    <table class="w-full text-left border-collapse text-xs">
        @if(count($headers) > 0 || isset($thead))
            <thead class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-extrabold uppercase tracking-wider text-slate-500">
                @if(isset($thead))
                    {{ $thead }}
                @else
                    <tr>
                        @foreach($headers as $header)
                            <th scope="col" class="px-5 py-3.5">{{ $header }}</th>
                        @endforeach
                    </tr>
                @endif
            </thead>
        @endif
        <tbody class="divide-y divide-slate-100 bg-white">
            {{ $slot }}
        </tbody>
    </table>
</div>
