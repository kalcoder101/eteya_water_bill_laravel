@props([
    'accent' => false,
    'padding' => 'p-5',
])

<div {{ $attributes->merge(['class' => 'bg-white border border-slate-200 rounded-xl shadow-card overflow-hidden transition-all duration-200 ' . $padding]) }}>
    @if($accent)
        <div class="h-1 bg-emerald-600 -mt-5 -mx-5 mb-4"></div>
    @endif
    {{ $slot }}
</div>
