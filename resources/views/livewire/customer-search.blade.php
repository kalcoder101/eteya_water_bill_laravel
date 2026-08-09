<div>
    <!-- Toolbar & Segmented Filter Bar -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-card px-4 py-3 mb-6 flex flex-wrap items-center gap-3">
        <div class="segmented flex flex-wrap gap-1">
            <button type="button" wire:click="$set('status', 'all')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $status==='all' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                {{ t('All Customers') }} <span class="ml-1 badge bg-white/20 text-slate-900 px-1.5 py-0.5 rounded text-[10px]">{{ $counts['total'] }}</span>
            </button>
            <button type="button" wire:click="$set('status', 'Active')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 {{ $status==='Active' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                {!! icon('check', 12) !!} {{ t('Active') }} <span class="ml-1 badge bg-emerald-200 text-emerald-900 px-1.5 py-0.5 rounded text-[10px]">{{ $counts['active'] }}</span>
            </button>
            <button type="button" wire:click="$set('status', 'DC')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 {{ $status==='DC' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 text-rose-800 hover:bg-rose-100' }}">
                {!! icon('x', 12) !!} DC <span class="ml-1 badge bg-rose-200 text-rose-900 px-1.5 py-0.5 rounded text-[10px]">{{ $counts['dc'] }}</span>
            </button>
            <button type="button" wire:click="$set('status', 'Updated')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 {{ $status==='Updated' ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
                {!! icon('refresh', 12) !!} {{ t('Updated') }}
            </button>
            <button type="button" wire:click="$set('status', 'Deleted')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 {{ $status==='Deleted' ? 'bg-slate-700 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                {!! icon('trash', 12) !!} {{ t('Deleted') }}
            </button>
        </div>

        <div class="flex items-center gap-2 flex-1 min-w-[240px] max-w-[360px]">
            <div class="relative flex-1">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">{!! icon('search', 14) !!}</span>
                <input type="text" wire:model.live.debounce.300ms="search" class="w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder:text-slate-400" placeholder="{{ t('Real-time search code, name, phone...') }}">
            </div>
        </div>

        <div class="flex items-center gap-2">
            <select wire:model.live="kebele" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                <option value="all">{{ t('All Kebeles') }}</option>
                @foreach ($kebeles as $k)
                    <option value="{{ $k }}">Kebele {{ $k }}</option>
                @endforeach
            </select>

            <select wire:model.live="customerType" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                <option value="all">{{ t('All Types') }}</option>
                @foreach ($types as $t)
                    <option value="{{ $t }}">{{ $t }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-2 ml-auto">
            <div wire:loading class="text-xs font-bold text-emerald-600 flex items-center gap-1.5">
                <span class="inline-block w-3.5 h-3.5 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin"></span>
                <span>Updating...</span>
            </div>
            <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="openExcelImportModal()">{!! icon('upload', 14) !!} {{ t('Import Excel') }}</button>
            <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="exportCustomersCSV()">{!! icon('download', 14) !!} {{ t('Export CSV') }}</button>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="gsap-section-card bg-white border border-slate-200 rounded-xl shadow-card overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5 border-b border-slate-100">
            <span class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <span class="text-emerald-600">{!! icon('users', 16) !!}</span> {{ t('Registered Customers Registry') }}
                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider border bg-slate-100 text-slate-700 border-slate-200">{{ $customers->total() }} {{ t('total accounts found') }}</span>
            </span>
            <span class="text-xs text-slate-500">
                {{ t('Active') }}: <strong class="text-emerald-600">{{ $counts['active'] }}</strong> &bull; {{ t('DC') }}: <strong class="text-rose-600">{{ $counts['dc'] }}</strong>
            </span>
        </div>

        <div class="scrollable-table border-0 rounded-none">
            <div class="table-scroll-view">
                <table class="w-full text-[13px]" id="customersTable">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold">
                            <th class="text-left px-4 py-3 whitespace-nowrap cursor-pointer select-none" wire:click="sortByField('meter_serial')">
                                {{ t('Code') }} @if ($sortBy==='meter_serial') {!! $sortDir==='asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th class="text-left px-4 py-3 whitespace-nowrap cursor-pointer select-none" wire:click="sortByField('first_name')">
                                {{ t('Customer Details') }} @if ($sortBy==='first_name') {!! $sortDir==='asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Kebele / Phone') }}</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Meter Specs') }}</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Type & Payment') }}</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Branch') }}</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Status') }}</th>
                            <th class="text-right px-4 py-3 whitespace-nowrap">{{ t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($customers as $c)
                        <tr class="border-b border-slate-100 odd:bg-white even:bg-slate-50/40 hover:bg-emerald-50/60 transition-colors">
                            <td class="px-4 py-2.5 align-middle"><span class="font-mono font-bold text-[13px] text-emerald-700 bg-slate-50 px-2 py-1 rounded border border-slate-200">{{ $c->meter_serial }}</span></td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle">
                                <div class="font-bold text-slate-900 text-[13px]">{{ trim(($c->first_name ?? '').' '.($c->middle_name ?? '').' '.($c->last_name ?? '')) }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">Bill #: {{ $c->bill_num ?? '—' }} &bull; Block: {{ $c->reader_block ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle">
                                <div>{{ $c->kebele ? 'Kebele '.$c->kebele : '—' }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $c->phone_number ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle">
                                <div><strong class="text-slate-900">{{ $c->meter_size ?? '1/2"' }}</strong></div>
                                <div class="text-[11px] text-slate-500 mt-0.5">Start: {{ number_format($c->start_value ?? 0, 1) }} m³</div>
                            </td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle">
                                <div>{{ $c->customer_type ?? 'Domestic' }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $c->payment_way ?? 'Postpaid' }}</div>
                            </td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle">{{ $c->customer_branch ?? 'Main' }}</td>
                            <td class="px-4 py-2.5 align-middle">
                                @if ($c->customer_status === 'Active')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider">{!! icon('check', 12) !!} {{ t('Active') }}</span>
                                @elseif ($c->customer_status === 'DC')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 text-rose-800 border border-rose-300 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider">{!! icon('x', 12) !!} DC</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 text-amber-800 border border-amber-300 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider">{{ $c->customer_status }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-right align-middle">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" class="p-1.5 rounded-lg bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-600 transition" onclick="viewCustomerDetails('{{ $c->meter_serial }}')" title="{{ t('View') }}">{!! icon('eye', 14) !!}</button>
                                    <button type="button" class="p-1.5 rounded-lg bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-600 transition" onclick="editCustomer('{{ $c->meter_serial }}')" title="{{ t('Edit') }}">{!! icon('edit', 14) !!}</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500 text-sm">
                                No customers found matching criteria "{{ $search }}".
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">
            {{ $customers->links() }}
        </div>
    </div>
</div>
