@props([
    'label',
    'value',
    'subvalue' => null,
    'icon' => null,
    'color' => 'emerald', // emerald, rose, amber, sky, slate
    'active' => false,
])

@php
    $borderColors = [
        'emerald' => 'border-emerald-500 ring-2 ring-emerald-500/20 shadow-md bg-emerald-50/20',
        'rose'    => 'border-rose-500 ring-2 ring-rose-500/20 shadow-md bg-rose-50/20',
        'amber'   => 'border-amber-500 ring-2 ring-amber-500/20 shadow-md bg-amber-50/20',
        'sky'     => 'border-sky-500 ring-2 ring-sky-500/20 shadow-md bg-sky-50/20',
        'slate'   => 'border-slate-400 ring-2 ring-slate-400/20 shadow-md bg-slate-50/20',
    ];

    $iconBg = [
        'emerald' => 'bg-emerald-600 text-white shadow-sm',
        'rose'    => 'bg-rose-600 text-white shadow-sm',
        'amber'   => 'bg-amber-600 text-white shadow-sm',
        'sky'     => 'bg-sky-600 text-white shadow-sm',
        'slate'   => 'bg-slate-700 text-white shadow-sm',
    ];

    $iconInactiveBg = [
        'emerald' => 'bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white',
        'rose'    => 'bg-rose-50 text-rose-600 group-hover:bg-rose-600 group-hover:text-white',
        'amber'   => 'bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white',
        'sky'     => 'bg-sky-50 text-sky-600 group-hover:bg-sky-600 group-hover:text-white',
        'slate'   => 'bg-slate-100 text-slate-600 group-hover:bg-slate-700 group-hover:text-white',
    ];

    $valueColor = [
        'emerald' => 'text-emerald-600',
        'rose'    => 'text-rose-600',
        'amber'   => 'text-amber-600',
        'sky'     => 'text-sky-600',
        'slate'   => 'text-slate-900',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'group text-left p-4 rounded-xl bg-white border transition-all duration-200 ' . ($active ? ($borderColors[$color] ?? $borderColors['emerald']) : 'border-slate-200 hover:border-emerald-300 hover:shadow-sm')]) }}>
    <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 group-hover:text-emerald-700 transition">{{ $label }}</span>
        @if($icon)
            <div class="w-9 h-9 rounded-xl flex items-center justify-center transition {{ $active ? ($iconBg[$color] ?? $iconBg['emerald']) : ($iconInactiveBg[$color] ?? $iconInactiveBg['emerald']) }}">
                {!! icon($icon, 18) !!}
            </div>
        @endif
    </div>
    <div class="mt-3 flex items-baseline justify-between">
        <span class="text-2xl font-black font-mono tracking-tight {{ $valueColor[$color] ?? 'text-slate-900' }}">{{ $value }}</span>
        @if($subvalue)
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $active ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $subvalue }}</span>
        @endif
    </div>
</div>
