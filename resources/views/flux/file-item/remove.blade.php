<button 
    type="button" 
    {{ $attributes->class([
        'p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer',
    ]) }} 
    title="{{ t('Remove file') }}"
>
    {!! icon('x-mark', 16) !!}
</button>
