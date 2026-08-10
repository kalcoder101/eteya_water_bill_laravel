@props([
    'headers' => [],
    'striped' => true,
])

<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-card">
    <table {{ $attributes->merge(['class' => 'w-full text-left text-xs border-collapse']) }}>
        @if(!empty($headers))
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                    @foreach($headers as $header)
                        <th class="px-4 py-3">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-slate-100 text-slate-700">
            {{ $slot }}
        </tbody>
    </table>
</div>
