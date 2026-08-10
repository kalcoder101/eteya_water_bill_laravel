<div>
    <!-- Interactive Stat Cards Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <button type="button" wire:click="$set('status', 'all')"
                class="group text-left p-4 rounded-xl bg-white border transition-all duration-200 cursor-pointer {{ $status==='all' ? 'border-emerald-500 ring-2 ring-emerald-500/20 shadow-md bg-emerald-50/20' : 'border-slate-200 hover:border-emerald-300 hover:shadow-sm' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 group-hover:text-emerald-700 transition">{{ t('Total Accounts') }}</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center transition {{ $status==='all' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white' }}">
                    {!! icon('users', 18) !!}
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black font-mono tracking-tight text-slate-900">{{ number_format($counts['total']) }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $status==='all' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600' }}">{{ t('All') }}</span>
            </div>
        </button>

        <button type="button" wire:click="$set('status', 'Active')"
                class="group text-left p-4 rounded-xl bg-white border transition-all duration-200 cursor-pointer {{ $status==='Active' ? 'border-emerald-500 ring-2 ring-emerald-500/20 shadow-md bg-emerald-50/20' : 'border-slate-200 hover:border-emerald-300 hover:shadow-sm' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">{{ t('Active Connected') }}</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center transition {{ $status==='Active' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-100 text-emerald-700 group-hover:bg-emerald-600 group-hover:text-white' }}">
                    {!! icon('check', 18) !!}
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black font-mono tracking-tight text-emerald-600">{{ number_format($counts['active']) }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">{{ number_format(($counts['total'] > 0 ? ($counts['active']/$counts['total'])*100 : 0), 1) }}%</span>
            </div>
        </button>

        <button type="button" wire:click="$set('status', 'DC')"
                class="group text-left p-4 rounded-xl bg-white border transition-all duration-200 cursor-pointer {{ $status==='DC' ? 'border-rose-500 ring-2 ring-rose-500/20 shadow-md bg-rose-50/20' : 'border-slate-200 hover:border-rose-300 hover:shadow-sm' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700">{{ t('Disconnected (DC)') }}</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center transition {{ $status==='DC' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-100 text-rose-700 group-hover:bg-rose-600 group-hover:text-white' }}">
                    {!! icon('x', 18) !!}
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black font-mono tracking-tight text-rose-600">{{ number_format($counts['dc']) }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 border border-rose-200">{{ t('Cut Off') }}</span>
            </div>
        </button>

        <button type="button" wire:click="$set('status', 'Updated')"
                class="group text-left p-4 rounded-xl bg-white border transition-all duration-200 cursor-pointer {{ $status==='Updated' ? 'border-amber-500 ring-2 ring-amber-500/20 shadow-md bg-amber-50/20' : 'border-slate-200 hover:border-amber-300 hover:shadow-sm' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700">{{ t('Pending / Updated') }}</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center transition {{ $status==='Updated' ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-100 text-amber-700 group-hover:bg-amber-600 group-hover:text-white' }}">
                    {!! icon('refresh', 18) !!}
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black font-mono tracking-tight text-amber-600">{{ number_format($counts['updated']) }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200">{{ t('Pending') }}</span>
            </div>
        </button>
    </div>

    <!-- Glassmorphic Elevated Control Panel -->
    <div class="bg-white/90 backdrop-blur-md border border-slate-200 rounded-2xl shadow-[0_4px_20px_rgba(15,23,42,0.04)] p-4 mb-6 space-y-3">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Real-time search bar -->
            <div class="relative flex-1 min-w-[280px]">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">{!! icon('search', 16) !!}</span>
                <input type="text" wire:model.live.debounce.300ms="search"
                       class="w-full pl-10 pr-9 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 focus:bg-white placeholder:text-slate-400 transition"
                       placeholder="{{ t('Search meter code, customer name, phone number, or bill #...') }}">
                @if (!empty($search))
                    <button type="button" wire:click="$set('search', '')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                        {!! icon('x', 14) !!}
                    </button>
                @endif
            </div>

            <!-- Kebele Filter Dropdown -->
            <div class="relative shrink-0">
                <select wire:model.live="kebele" class="appearance-none pl-3.5 pr-8 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition cursor-pointer">
                    <option value="all">📍 {{ t('All Kebeles') }}</option>
                    @foreach ($kebeles as $k)
                        <option value="{{ $k }}">Kebele {{ $k }}</option>
                    @endforeach
                </select>
                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">{!! icon('chevron-down', 12) !!}</span>
            </div>

            <!-- Customer Type Filter -->
            <div class="relative shrink-0">
                <select wire:model.live="customerType" class="appearance-none pl-3.5 pr-8 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition cursor-pointer">
                    <option value="all">🏢 {{ t('All Customer Types') }}</option>
                    @foreach ($types as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select>
                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">{!! icon('chevron-down', 12) !!}</span>
            </div>

            <!-- Reader Block Filter -->
            @if(count($blocks) > 0)
            <div class="relative shrink-0">
                <select wire:model.live="readerBlock" class="appearance-none pl-3.5 pr-8 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition cursor-pointer">
                    <option value="all">📦 {{ t('All Blocks') }}</option>
                    @foreach ($blocks as $b)
                        <option value="{{ $b }}">Block {{ $b }}</option>
                    @endforeach
                </select>
                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">{!! icon('chevron-down', 12) !!}</span>
            </div>
            @endif

            <!-- Livewire Activity Loading Indicator -->
            <div wire:loading class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold shrink-0">
                <span class="w-3.5 h-3.5 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin"></span>
                <span>Updating registry...</span>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center gap-2 ml-auto shrink-0">
                @if ($hasActiveFilters)
                    <button type="button" wire:click="resetFilters" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold transition">
                        {!! icon('x', 14) !!} {{ t('Clear Filters') }}
                    </button>
                @endif
                <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="openExcelImportModal()">
                    {!! icon('upload', 14) !!} {{ t('Import') }}
                </button>
                <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="exportCustomersCSV()">
                    {!! icon('download', 14) !!} {{ t('Export CSV') }}
                </button>
            </div>
        </div>

        <!-- Active Filters Tag Pills -->
        @if ($hasActiveFilters)
            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100 text-xs">
                <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">{{ t('Active Filters:') }}</span>
                @if (!empty($search))
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold border border-emerald-200">
                        Query: "{{ $search }}"
                        <button type="button" wire:click="$set('search', '')" class="hover:text-emerald-900">{!! icon('x', 12) !!}</button>
                    </span>
                @endif
                @if ($status !== 'all')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold border border-emerald-200">
                        Status: {{ $status }}
                        <button type="button" wire:click="$set('status', 'all')" class="hover:text-emerald-900">{!! icon('x', 12) !!}</button>
                    </span>
                @endif
                @if ($kebele !== 'all')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold border border-emerald-200">
                        Kebele: {{ $kebele }}
                        <button type="button" wire:click="$set('kebele', 'all')" class="hover:text-emerald-900">{!! icon('x', 12) !!}</button>
                    </span>
                @endif
                @if ($customerType !== 'all')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold border border-emerald-200">
                        Type: {{ $customerType }}
                        <button type="button" wire:click="$set('customerType', 'all')" class="hover:text-emerald-900">{!! icon('x', 12) !!}</button>
                    </span>
                @endif
            </div>
        @endif
    </div>

    <!-- Customer Data Table Container -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-[0_4px_20px_rgba(15,23,42,0.04)] overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center justify-center">
                    {!! icon('users', 18) !!}
                </div>
                <div>
                    <h3 class="m-0 text-sm font-bold text-slate-900 leading-tight">{{ t('Active Customer Accounts Registry') }}</h3>
                    <p class="m-0 text-xs text-slate-500 mt-0.5">Displaying {{ $customers->count() }} of {{ number_format($customers->total()) }} total records</p>
                </div>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Active: {{ number_format($counts['active']) }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 font-bold border border-rose-200">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> DC: {{ number_format($counts['dc']) }}
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100/70 text-slate-600 text-[11px] font-bold uppercase tracking-wider border-b border-slate-200 select-none">
                        <th class="px-5 py-3.5 cursor-pointer hover:text-emerald-700 transition" wire:click="sortByField('meter_serial')">
                            <div class="flex items-center gap-1.5">
                                <span>{{ t('Meter Code') }}</span>
                                @if ($sortBy==='meter_serial')
                                    <span class="text-emerald-600 font-black">{!! $sortDir==='asc' ? '↑' : '↓' !!}</span>
                                @endif
                            </div>
                        </th>
                        <th class="px-5 py-3.5 cursor-pointer hover:text-emerald-700 transition" wire:click="sortByField('first_name')">
                            <div class="flex items-center gap-1.5">
                                <span>{{ t('Customer Identity') }}</span>
                                @if ($sortBy==='first_name')
                                    <span class="text-emerald-600 font-black">{!! $sortDir==='asc' ? '↑' : '↓' !!}</span>
                                @endif
                            </div>
                        </th>
                        <th class="px-5 py-3.5">{{ t('Location / Kebele') }}</th>
                        <th class="px-5 py-3.5">{{ t('Meter Specs') }}</th>
                        <th class="px-5 py-3.5">{{ t('Category & Payment') }}</th>
                        <th class="px-5 py-3.5">{{ t('Status') }}</th>
                        <th class="px-5 py-3.5 text-right">{{ t('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($customers as $c)
                    @php
                        $fullName = trim(($c->first_name ?? '').' '.($c->middle_name ?? '').' '.($c->last_name ?? ''));
                        $initials = strtoupper(substr($c->first_name ?? 'C', 0, 1) . substr($c->last_name ?? '', 0, 1));
                    @endphp
                    <tr class="hover:bg-emerald-50/40 transition-colors group">
                        <td class="px-5 py-3.5 align-middle">
                            <span class="font-mono font-bold text-xs text-emerald-800 bg-emerald-50/80 px-2.5 py-1 rounded-lg border border-emerald-200/80 inline-block shadow-2xs">
                                {{ $c->meter_serial }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 align-middle">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 text-slate-700 font-extrabold text-[11px] flex items-center justify-center shrink-0 group-hover:border-emerald-300 group-hover:bg-emerald-100 group-hover:text-emerald-800 transition">
                                    {{ $initials ?: 'CS' }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-900 text-sm leading-snug group-hover:text-emerald-900 transition">{{ $fullName ?: 'Unnamed Customer' }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-2">
                                        <span>Bill #: <strong>{{ $c->bill_num ?? '—' }}</strong></span>
                                        <span>&bull;</span>
                                        <span>Block: <strong>{{ $c->reader_block ?? '—' }}</strong></span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 align-middle">
                            <div class="font-semibold text-slate-800 flex items-center gap-1">
                                <span class="text-slate-400">{!! icon('map-pin', 12) !!}</span>
                                <span>{{ $c->kebele ? 'Kebele '.$c->kebele : '—' }}</span>
                            </div>
                            <div class="text-[11px] text-slate-500 mt-0.5 font-mono">{{ $c->phone_number ?? '—' }}</div>
                        </td>
                        <td class="px-5 py-3.5 align-middle">
                            <div class="font-bold text-slate-900">{{ $c->meter_size ?? '1/2"' }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">Start: {{ number_format($c->start_value ?? 0, 1) }} m³</div>
                        </td>
                        <td class="px-5 py-3.5 align-middle">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-800 font-bold text-[11px]">
                                {{ $c->customer_type ?? 'Domestic' }}
                            </span>
                            <div class="text-[11px] text-slate-500 mt-1">{{ $c->payment_way ?? 'Postpaid' }}</div>
                        </td>
                        <td class="px-5 py-3.5 align-middle">
                            @if ($c->customer_status === 'Active')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 font-bold text-[10px] uppercase tracking-wider shadow-2xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                    {{ t('Active') }}
                                </span>
                            @elseif ($c->customer_status === 'DC')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-100 text-rose-800 border border-rose-300 font-bold text-[10px] uppercase tracking-wider shadow-2xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                    DC (Disconnected)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-800 border border-amber-300 font-bold text-[10px] uppercase tracking-wider shadow-2xs">
                                    {{ $c->customer_status }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right align-middle">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('customer-ledger.index') }}?meterSerial={{ urlencode($c->meter_serial) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-emerald-100 hover:text-emerald-800 text-slate-700 font-bold text-xs transition border border-slate-200"
                                   title="{{ t('View Financial Ledger') }}">
                                    {!! icon('book-open', 14) !!}
                                    <span>Ledger</span>
                                </a>
                                <button type="button"
                                        class="p-1.5 rounded-lg bg-slate-100 hover:bg-emerald-100 hover:text-emerald-800 text-slate-700 transition border border-slate-200"
                                        onclick="editCustomer({{ json_encode($c->meter_serial) }})"
                                        title="{{ t('Edit Account') }}">
                                    {!! icon('edit', 15) !!}
                                </button>
                                <button type="button"
                                        class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 hover:text-rose-800 text-rose-600 transition border border-rose-200"
                                        onclick="deleteCustomer({{ json_encode($c->meter_serial) }}, {{ json_encode($fullName) }})"
                                        title="{{ t('Delete Account') }}">
                                    {!! icon('trash', 15) !!}
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="max-w-xs mx-auto text-center space-y-3">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 border border-slate-200 text-slate-400 flex items-center justify-center mx-auto">
                                    {!! icon('search', 24) !!}
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 m-0">No Customers Found</h4>
                                    <p class="text-xs text-slate-500 mt-1 m-0">No records match your active search filters or query.</p>
                                </div>
                                @if ($hasActiveFilters)
                                    <button type="button" wire:click="resetFilters" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm">
                                        {!! icon('x', 14) !!} {{ t('Reset All Filters') }}
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50 flex flex-wrap items-center justify-between gap-4">
            <div class="text-xs text-slate-500">
                Showing <strong class="text-slate-800">{{ $customers->firstItem() ?? 0 }}</strong> to <strong class="text-slate-800">{{ $customers->lastItem() ?? 0 }}</strong> of <strong class="text-slate-800">{{ number_format($customers->total()) }}</strong> entries
            </div>
            <div>
                {{ $customers->links() }}
            </div>
        </div>
    </div>
</div>
