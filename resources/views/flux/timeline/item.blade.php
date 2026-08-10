@props([
    'completed' => false,
    'active' => false,
])

<div 
    {{ $attributes->class([
        'relative flex-1 flex flex-col items-start min-w-[140px] group',
    ]) }}
    data-flux-timeline-item
>
    {{ $slot }}
</div>
