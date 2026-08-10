@props([
    'icon' => null,
    'label' => null,
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
        <input {{ $attributes->merge([
            'class' => 'w-full text-xs text-slate-900 bg-white border border-slate-200 rounded-lg py-2.5 transition focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 placeholder:text-slate-400 ' . ($icon ? 'pl-9 pr-3' : 'px-3') . ($error ? ' border-rose-500 ring-1 ring-rose-500' : '')
        ]) }} />
    </div>
    @if($error)
        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $error }}</p>
    @endif
</div>
