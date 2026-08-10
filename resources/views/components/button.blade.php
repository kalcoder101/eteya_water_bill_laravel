@props([
    'variant' => 'primary', // primary, secondary, danger, outline, soft, ghost
    'size'    => 'md',      // sm, md, lg
    'icon'    => null,
    'type'    => 'button',
    'href'    => null,
])

@php
    $variants = [
        'primary'   => 'bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-xs active:bg-emerald-800 focus:ring-emerald-500/30',
        'secondary' => 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold active:bg-slate-300 focus:ring-slate-400/20',
        'danger'    => 'bg-rose-600 hover:bg-rose-700 text-white font-bold shadow-xs active:bg-rose-800 focus:ring-rose-500/30',
        'outline'   => 'border border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold active:bg-slate-100 focus:ring-emerald-500/20',
        'soft'      => 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold focus:ring-emerald-500/20',
        'amber'     => 'bg-amber-500 hover:bg-amber-600 text-white font-bold shadow-xs active:bg-amber-700 focus:ring-amber-500/30',
        'ghost'     => 'hover:bg-slate-100 text-slate-600 hover:text-slate-900 font-semibold focus:ring-slate-400/20',
    ];

    $sizes = [
        'sm' => 'px-2.5 py-1.5 text-xs rounded-md gap-1',
        'md' => 'px-4 py-2 text-xs rounded-lg gap-1.5',
        'lg' => 'px-5 py-2.5 text-sm rounded-xl gap-2',
    ];

    $classes = 'inline-flex items-center justify-center font-sans transition-all duration-150 cursor-pointer focus:outline-none focus:ring-2 disabled:opacity-50 disabled:cursor-not-allowed '
        . ($variants[$variant] ?? $variants['primary']) . ' '
        . ($sizes[$size] ?? $sizes['md']);

    $iconSize = $size === 'sm' ? 14 : ($size === 'lg' ? 20 : 16);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            {!! icon($icon, $iconSize) !!}
        @endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            {!! icon($icon, $iconSize) !!}
        @endif
        <span>{{ $slot }}</span>
    </button>
@endif


