@props([
    'label',
    'value',
    'subvalue' => null,
    'icon' => null,
    'color' => 'emerald', // emerald, rose, amber, sky, slate/zinc
    'active' => false,
])

@php
    $badgeColors = [
        'emerald' => 'emerald',
        'rose'    => 'rose',
        'amber'   => 'amber',
        'sky'     => 'sky',
        'slate'   => 'zinc',
    ];
@endphp

<flux:card {{ $attributes->merge(['class' => 'group text-left p-4 space-y-2 hover:border-emerald-300 transition-all duration-200']) }}>
    <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 group-hover:text-emerald-700 transition">{{ $label }}</span>
        @if($icon)
            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                {!! icon($icon, 16) !!}
            </div>
        @endif
    </div>
    <div class="mt-2 flex items-baseline justify-between">
        <span class="text-2xl font-bold font-mono tracking-tight text-slate-900">{{ $value }}</span>
        @if($subvalue)
            <flux:badge size="sm" color="{{ $badgeColors[$color] ?? 'emerald' }}">{{ $subvalue }}</flux:badge>
        @endif
    </div>
</flux:card>
