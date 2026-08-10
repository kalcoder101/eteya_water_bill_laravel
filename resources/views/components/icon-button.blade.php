@props([
    'variant' => 'slate', // slate, emerald, danger, ghost, outline
    'size'    => 'md',     // sm, md, lg
    'icon'    => null,
    'type'    => 'button',
    'title'   => null,
])

@php
    $variants = [
        'slate'   => 'bg-slate-50 border border-slate-200 text-slate-500 hover:bg-slate-100 hover:text-slate-800 hover:border-slate-300',
        'emerald' => 'bg-slate-50 border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300',
        'danger'  => 'bg-slate-50 border border-slate-200 text-slate-500 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200',
        'ghost'   => 'hover:bg-slate-100 text-slate-500 hover:text-slate-800',
        'outline' => 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50',
    ];

    $sizes = [
        'sm' => 'w-8 h-8 rounded-lg',
        'md' => 'w-9 h-9 rounded-lg',
        'lg' => 'w-10 h-10 rounded-xl',
    ];

    $classes = 'inline-flex items-center justify-center shrink-0 cursor-pointer transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 active:scale-[0.96] disabled:opacity-50 disabled:cursor-not-allowed '
        . ($variants[$variant] ?? $variants['slate']) . ' '
        . ($sizes[$size] ?? $sizes['md']);
@endphp

<button type="{{ $type }}" title="{{ $title }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        {!! icon($icon, $size === 'lg' ? 18 : ($size === 'md' ? 16 : 14)) !!}
    @else
        {{ $slot }}
    @endif
</button>
