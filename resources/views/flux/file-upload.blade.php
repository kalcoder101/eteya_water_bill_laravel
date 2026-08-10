@props([
    'label' => null,
])

@php
$id = $attributes->get('id') ?? 'flux-file-upload-' . Str::random(8);
@endphp

<div data-flux-file-upload class="w-full flex flex-col gap-1.5">
    @if ($label)
        <label for="{{ $id }}" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
            {{ $label }}
        </label>
    @endif

    <div class="relative group cursor-pointer" onclick="document.getElementById('{{ $id }}').click()">
        <input 
            id="{{ $id }}" 
            type="file" 
            class="sr-only font-sans" 
            {{ $attributes->whereStartsWith(['wire:model', 'multiple', 'accept', 'disabled']) }}
        />
        {{ $slot }}
    </div>
</div>
