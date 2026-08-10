<div 
    {{ $attributes->class([
        'flex flex-col gap-0.5 min-w-0 text-start',
    ]) }}
    data-flux-timeline-content
>
    {{ $slot }}
</div>
