@props([
    'status' => 'Active', // Active, DC, Pending, Updated, Paid, Unpaid, Neutral
])

@php
    $normalized = strtolower(trim((string) $status));

    $styles = match(true) {
        in_array($normalized, ['active', 'paid', 'approved', 'connected'])
            => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        in_array($normalized, ['dc', 'disconnected', 'unpaid', 'rejected', 'cut off'])
            => 'bg-rose-100 text-rose-800 border-rose-200',
        in_array($normalized, ['pending', 'updated', 'processing', 'in progress'])
            => 'bg-amber-100 text-amber-800 border-amber-200',
        in_array($normalized, ['info', 'notice', 'new'])
            => 'bg-sky-100 text-sky-800 border-sky-200',
        default
            => 'bg-slate-100 text-slate-700 border-slate-200',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 px-2.5 py-0.5 text-[11px] font-bold rounded-full border tracking-wide ' . $styles]) }}>
    {{ $slot->isEmpty() ? $status : $slot }}
</span>
