@props([
    'heading' => 'File',
    'image' => null,
    'size' => null,
])

@php
$formattedSize = null;
if ($size && is_numeric($size)) {
    $bytes = (float) $size;
    if ($bytes >= 1048576) {
        $formattedSize = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $formattedSize = number_format($bytes / 1024, 1) . ' KB';
    } else {
        $formattedSize = $bytes . ' B';
    }
}
@endphp

<div {{ $attributes->class([
    'flex items-center justify-between p-2.5 rounded-xl border border-slate-200 bg-white shadow-xs hover:border-slate-300 transition-all',
]) }}>
    <div class="flex items-center gap-3 min-w-0">
        @if ($image)
            <img src="{{ $image }}" alt="{{ $heading }}" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shrink-0 bg-slate-100" />
        @else
            <div class="w-10 h-10 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center shrink-0">
                {!! icon('document', 20) !!}
            </div>
        @endif

        <div class="min-w-0">
            <div class="text-xs font-bold text-slate-800 truncate" title="{{ $heading }}">
                {{ $heading }}
            </div>
            @if ($formattedSize)
                <div class="text-[11px] font-mono text-slate-500 mt-0.5">
                    {{ $formattedSize }}
                </div>
            @endif
        </div>
    </div>

    @if (isset($actions))
        <div class="flex items-center gap-1.5 shrink-0 ml-3">
            {{ $actions }}
        </div>
    @endif
</div>
