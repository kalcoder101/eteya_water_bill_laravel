@props([
    'horizontal' => false,
    'size' => 'md',
])

@php
$classes = Flux::classes()
    ->add('w-full')
    ->add($horizontal ? 'flex flex-row overflow-x-auto py-3 gap-6 sm:gap-8 items-start' : 'flex flex-col gap-5 py-2')
    ;
@endphp

<div 
    {{ $attributes->class($classes) }}
    data-flux-timeline
    @if ($horizontal) data-flux-timeline-horizontal @endif
    data-flux-timeline-size="{{ $size }}"
>
    {{ $slot }}
</div>
