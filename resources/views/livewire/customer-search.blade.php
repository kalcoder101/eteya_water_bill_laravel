<div>
    <!-- Interactive Stat Cards Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-kpi
            :label="t('Total Accounts')"
            :value="number_format($counts['total'])"
            :subvalue="t('All')"
            icon="users"
            color="slate"
            :active="$status === 'all'"
            wire:click="$set('status', 'all')"
            class="cursor-pointer"
        />

        <x-kpi
            :label="t('Active Connected')"
            :value="number_format($counts['active'])"
            :subvalue="number_format(($counts['total'] > 0 ? ($counts['active']/$counts['total'])*100 : 0), 1) . '%'"
            icon="check"
            color="emerald"
            :active="$status === 'Active'"
            wire:click="$set('status', 'Active')"
            class="cursor-pointer"
        />

        <x-kpi
            :label="t('Disconnected (DC)')"
            :value="number_format($counts['dc'])"
            :subvalue="t('Cut Off')"
            icon="x"
            color="rose"
            :active="$status === 'DC'"
            wire:click="$set('status', 'DC')"
            class="cursor-pointer"
        />

        <x-kpi
            :label="t('Pending / Updated')"
            :value="number_format($counts['updated'])"
            :subvalue="t('Pending')"
            icon="refresh"
            color="amber"
            :active="$status === 'Updated'"
            wire:click="$set('status', 'Updated')"
            class="cursor-pointer"
        />
    </div>

    <!-- Glassmorphic Elevated Control Panel -->
    <div class="bg-white/90 backdrop-blur-md border border-slate-200 rounded-2xl shadow-[0_4px_20px_rgba(15,23,42,0.04)] p-4 mb-6 space-y-3">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Real-time search bar -->
            <div class="relative flex-1 min-w-[280px]">
                <x-input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    icon="search"
                    :placeholder="t('Search meter code, customer name, phone number, or bill #...')"
                    class="py-2.5"
                />
                @if (!empty($search))
                    <button type="button" wire:click="$set('search', '')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition z-10">
                        {!! icon('x', 14) !!}
                    </button>
                @endif
            </div>

            <!-- Kebele Filter Dropdown -->
            <div class="shrink-0">
                <x-select wire:model.live="kebele">
                    <option value="all">📍 {{ t('All Kebeles') }}</option>
                    @foreach ($kebeles as $k)
                        <option value="{{ $k }}">Kebele {{ $k }}</option>
                    @endforeach
                </x-select>
            </div>

            <!-- Customer Type Filter -->
            <div class="shrink-0">
                <x-select wire:model.live="customerType">
                    <option value="all">🏢 {{ t('All Customer Types') }}</option>
                    @foreach ($types as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </x-select>
            </div>

            <!-- Reader Block Filter -->
            @if(count($blocks) > 0)
            <div class="shrink-0">
                <x-select wire:model.live="readerBlock">
                    <option value="all">📦 {{ t('All Blocks') }}</option>
                    @foreach ($blocks as $b)
                        <option value="{{ $b }}">Block {{ $b }}</option>
                    @endforeach
                </x-select>
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
                    <x-button variant="danger" size="md" wire:click="resetFilters" icon="x">
                        {{ t('Clear Filters') }}
                    </x-button>
                @endif
                <x-button variant="secondary" size="md" icon="upload" onclick="openExcelImportModal()">
                    {{ t('Import') }}
                </x-button>
                <x-button variant="secondary" size="md" icon="download" onclick="exportCustomersCSV()">
                    {{ t('Export CSV') }}
                </x-button>
            </div>
        </div>

        <!-- Active Filters Tag Pills -->
        @if ($hasActiveFilters)
            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100 text-xs">
                <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">{{ t('Active Filters:') }}</span>
                @if (!empty($search))
                    <x-badge status="Active">
                        Query: "{{ $search }}"
                        <button type="button" wire:click="$set('search', '')" class="hover:text-emerald-900 ml-1">{!! icon('x', 12) !!}</button>
                    </x-badge>
                @endif
                @if ($status !== 'all')
                    <x-badge status="Active">
                        Status: {{ $status }}
                        <button type="button" wire:click="$set('status', 'all')" class="hover:text-emerald-900 ml-1">{!! icon('x', 12) !!}</button>
                    </x-badge>
                @endif
                @if ($kebele !== 'all')
                    <x-badge status="Active">
                        Kebele: {{ $kebele }}
                        <button type="button" wire:click="$set('kebele', 'all')" class="hover:text-emerald-900 ml-1">{!! icon('x', 12) !!}</button>
                    </x-badge>
                @endif
                @if ($customerType !== 'all')
                    <x-badge status="Active">
                        Type: {{ $customerType }}
                        <button type="button" wire:click="$set('customerType', 'all')" class="hover:text-emerald-900 ml-1">{!! icon('x', 12) !!}</button>
                    </x-badge>
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
                                    <x-button variant="primary" size="md" wire:click="resetFilters" icon="x">
                                        {{ t('Reset All Filters') }}
                                    </x-button>
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
