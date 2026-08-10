@props([
    'heading' => 'Drop files here or click to browse',
    'text' => null,
    'icon' => 'cloud-arrow-up',
])

<div {{ $attributes->class([
    'flex flex-col items-center justify-center p-6 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50/70 hover:bg-emerald-50/40 hover:border-emerald-400 transition-all duration-200 text-center select-none cursor-pointer group',
]) }}>
    <div class="w-10 h-10 rounded-full bg-emerald-50 border border-emerald-200/80 text-emerald-600 flex items-center justify-center mb-2.5 group-hover:scale-110 transition-transform">
        {!! icon($icon, 20) !!}
    </div>
    <div class="text-xs font-bold text-slate-800 group-hover:text-emerald-700 transition-colors">
        {{ $heading }}
    </div>
    @if ($text)
        <div class="text-[11px] text-slate-500 mt-1">
            {{ $text }}
        </div>
    @endif
</div>
