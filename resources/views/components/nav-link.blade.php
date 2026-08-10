@props([
    'href'   => null,
    'icon'   => null,
    'active' => false,
    'accent' => [ // { icon, soft, hover, active }
        'icon'   => 'text-emerald-600',
        'soft'   => 'bg-emerald-50',
        'hover'  => 'hover:bg-emerald-50 hover:text-emerald-900',
        'active' => 'bg-emerald-600 text-white shadow-sm',
    ],
])

@php
    $iconColor = $accent['icon'] ?? 'text-emerald-600';
    $activeBg  = $active ? ($accent['active'] ?? 'bg-emerald-600 text-white shadow-sm') : ($accent['hover'] ?? 'hover:bg-slate-50 hover:text-slate-900');
@endphp

<li>
    <a href="{{ $href ?? '#' }}" wire:navigate {{ $attributes->merge([
        'class' => 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] font-semibold transition ' . $activeBg
    ]) }}>
        @if($icon)
            <span class="w-5 shrink-0 flex items-center justify-center {{ $active ? 'text-white' : $iconColor }}">{!! icon($icon, 18) !!}</span>
        @endif
        <span class="nav-label truncate">{{ $slot }}</span>
    </a>
</li>
