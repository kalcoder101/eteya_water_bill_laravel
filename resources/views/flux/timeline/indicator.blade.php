@props([
    'color' => 'emerald',
])

@php
$colorClasses = match ($color) {
    'emerald', 'success' => 'bg-emerald-50 text-emerald-700 border-emerald-300 ring-2 ring-emerald-100',
    'amber', 'warning' => 'bg-amber-50 text-amber-700 border-amber-300 ring-2 ring-amber-100',
    'sky', 'info' => 'bg-sky-50 text-sky-700 border-sky-300 ring-2 ring-sky-100',
    'rose', 'danger' => 'bg-rose-50 text-rose-700 border-rose-300 ring-2 ring-rose-100',
    default => 'bg-slate-100 text-slate-600 border-slate-300',
};
@endphp

<div 
    {{ $attributes->class([
        'w-8 h-8 rounded-full border flex items-center justify-center shrink-0 text-xs transition-transform group-hover:scale-105 z-10 bg-white mb-2',
        $colorClasses
    ]) }}
    data-flux-timeline-indicator
>
    {{ $slot }}
</div>
