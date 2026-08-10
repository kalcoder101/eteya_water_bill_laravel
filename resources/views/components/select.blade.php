@props([
    'label' => null,
    'icon' => null,
    'error' => null,
])

<div>
    @if($label)
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">{{ $label }}</label>
    @endif
    <div class="relative rounded-lg shadow-xs">
        @if($icon)
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                {!! icon($icon, 16) !!}
            </div>
        @endif
        <select {{ $attributes->merge([
            'class' => 'w-full text-xs text-slate-900 bg-white border border-slate-200 rounded-lg py-2.5 transition focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 pr-8 appearance-none cursor-pointer ' . ($icon ? 'pl-9' : 'px-3') . ($error ? ' border-rose-500 ring-1 ring-rose-500' : '')
        ]) }}>
            {{ $slot }}
        </select>
        <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </div>
    </div>
    @if($error)
        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $error }}</p>
    @endif
</div>
