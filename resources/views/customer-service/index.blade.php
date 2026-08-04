@extends('layouts.app')

@section('content')

<!-- Page Header & Banner -->
<div class="gsap-hero flex flex-wrap items-end justify-between gap-4 mb-6">
    <div>
        <div class="text-[11px] uppercase tracking-widest font-bold text-slate-500 mb-1">
            {{ t('Operations') }} &bull; {{ t('Customer Registry') }}
        </div>
        <h2 class="m-0 text-[22px] font-bold tracking-tight text-slate-900 flex items-center gap-2.5">
            <span class="text-emerald-600">{!! icon('users', 24) !!}</span> {{ t('Customer Service Management') }}
        </h2>
        <p class="mt-1 text-[13px] text-slate-500">{{ t('Register, search, update and manage all water meter customers across Kebeles') }}</p>
    </div>
</div>

<!-- KPI Stat Cards Bar -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="gsap-stat-card gsap-hover-card p-5 rounded-xl bg-white border border-slate-200 shadow-card space-y-2">
        <div class="flex items-center justify-between text-xs">
            <span class="font-bold uppercase tracking-wider text-[11px] text-emerald-800">{{ t('Total Registered') }}</span>
            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                {!! icon('users', 16) !!}
            </div>
        </div>
        <div class="text-2xl font-bold text-slate-900 font-mono tabular-nums" data-gsap-counter data-target-val="{{ $totalCount }}">{{ number_format($totalCount) }}</div>
        <div class="text-[11px] text-slate-500">{{ t('Water Meter Accounts') }}</div>
    </div>

    <div class="gsap-stat-card gsap-hover-card p-5 rounded-xl bg-white border border-slate-200 shadow-card space-y-2">
        <div class="flex items-center justify-between text-xs">
            <span class="font-bold uppercase tracking-wider text-[11px] text-sky-800">{{ t('Active Accounts') }}</span>
            <div class="w-8 h-8 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center">
                {!! icon('check', 16) !!}
            </div>
        </div>
        <div class="text-2xl font-bold text-slate-900 font-mono tabular-nums text-emerald-600" data-gsap-counter data-target-val="{{ $activeCount }}">{{ number_format($activeCount) }}</div>
        <div class="text-[11px] text-slate-500">{{ number_format(($totalCount > 0 ? ($activeCount/$totalCount)*100 : 0), 1) }}% {{ t('connected') }}</div>
    </div>

    <div class="gsap-stat-card gsap-hover-card p-5 rounded-xl bg-white border border-slate-200 shadow-card space-y-2">
        <div class="flex items-center justify-between text-xs">
            <span class="font-bold uppercase tracking-wider text-[11px] text-rose-800">{{ t('Disconnected (DC)') }}</span>
            <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center">
                {!! icon('x', 16) !!}
            </div>
        </div>
        <div class="text-2xl font-bold text-slate-900 font-mono tabular-nums text-rose-600" data-gsap-counter data-target-val="{{ $dcCount }}">{{ number_format($dcCount) }}</div>
        <div class="text-[11px] text-slate-500">{{ t('Cut off accounts') }}</div>
    </div>

    <div class="gsap-stat-card gsap-hover-card p-5 rounded-xl bg-white border border-slate-200 shadow-card space-y-2">
        <div class="flex items-center justify-between text-xs">
            <span class="font-bold uppercase tracking-wider text-[11px] text-amber-800">{{ t('Updated / Pending') }}</span>
            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                {!! icon('refresh', 16) !!}
            </div>
        </div>
        <div class="text-2xl font-bold text-slate-900 font-mono tabular-nums text-amber-600" data-gsap-counter data-target-val="{{ max(0, $totalCount - $activeCount - $dcCount) }}">{{ number_format(max(0, $totalCount - $activeCount - $dcCount)) }}</div>
        <div class="text-[11px] text-slate-500">{{ t('Requires verification') }}</div>
    </div>
</div>

<!-- EOS Chart.js Analytics Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    <div class="gsap-chart-card p-5 rounded-xl bg-white border border-slate-200 shadow-card">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <h3 class="m-0 text-[15px] font-serif font-bold text-slate-900 flex items-center gap-2">
                <span class="text-emerald-600">{!! icon('pie-chart', 18) !!}</span> {{ t('Customer Status Distribution') }}
            </h3>
            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider border bg-slate-100 text-slate-700 border-slate-200">{{ $totalCount }} {{ t('Total') }}</span>
        </div>
        <div class="h-[180px] relative">
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    <div class="gsap-chart-card p-5 rounded-xl bg-white border border-slate-200 shadow-card">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <h3 class="m-0 text-[15px] font-serif font-bold text-slate-900 flex items-center gap-2">
                <span class="text-emerald-600">{!! icon('bar-chart', 18) !!}</span> {{ t('Branch Coverage Overview') }}
            </h3>
            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider border bg-emerald-100 text-emerald-800 border-emerald-300">4 {{ t('Branches') }}</span>
        </div>
        <div class="h-[180px] relative">
            <canvas id="branchChart"></canvas>
        </div>
    </div>
</div>

<!-- Toolbar & Segmented Filter Bar -->
<div class="bg-white border border-slate-200 rounded-xl shadow-card px-4 py-3 mb-6 flex flex-wrap items-center gap-3">
    <div class="segmented">
        <a class="{{ $filter==='all' ? 'active' : '' }}" href="?filter=all">{{ t('All Customers') }} <span class="badge badge-secondary">{{ $totalCount }}</span></a>
        <a class="status-active {{ ($filter==='status' && $status==='Active') ? 'active status-active' : '' }}" href="?filter=status&status=Active">{!! icon('check', 12) !!} {{ t('Active') }} <span class="badge badge-success">{{ $activeCount }}</span></a>
        <a class="status-dc {{ ($filter==='status' && $status==='DC') ? 'active status-dc' : '' }}" href="?filter=status&status=DC">{!! icon('x', 12) !!} DC <span class="badge badge-danger">{{ $dcCount }}</span></a>
        <a class="status-updated {{ ($filter==='status' && $status==='Updated') ? 'active status-updated' : '' }}" href="?filter=status&status=Updated">{!! icon('refresh', 12) !!} {{ t('Updated') }}</a>
        <a class="status-deleted {{ ($filter==='status' && $status==='Deleted') ? 'active status-deleted' : '' }}" href="?filter=status&status=Deleted">{!! icon('trash', 12) !!} {{ t('Deleted') }}</a>
    </div>

    <form method="get" action="" class="flex items-center gap-2 flex-1 min-w-[240px] max-w-[360px]">
        <input type="hidden" name="filter" value="search">
        <div class="relative flex-1">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">{!! icon('search', 14) !!}</span>
            <input type="text" name="search" class="w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder:text-slate-400" placeholder="{{ t('Search code, name, phone...') }}" value="{{ $search }}">
        </div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm shrink-0">{!! icon('search', 14) !!}</button>
    </form>

    <div class="flex items-center gap-2 ml-auto">
        <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="openExcelImportModal()">{!! icon('upload', 14) !!} {{ t('Import Excel') }}</button>
        <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="exportCustomersCSV()">{!! icon('download', 14) !!} {{ t('Export CSV') }}</button>
    </div>
</div>

<!-- Two Column Panel (Quick Lookup & Bulk Actions) -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    <div class="gsap-section-card bg-white border border-slate-200 rounded-xl shadow-card overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 border-b border-slate-100 font-bold text-[13px] text-slate-900">
            <span class="text-emerald-600">{!! icon('search', 16) !!}</span> {{ t('Quick Customer Lookup') }}
        </div>
        <div class="p-4">
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Customer Meter Code') }}</label>
                    <input type="text" id="lookupCode" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder:text-slate-400" placeholder="e.g. ETY-0001" onkeydown="if(event.key==='Enter') lookupCustomer()">
                </div>
                <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm shrink-0" onclick="lookupCustomer()">{!! icon('search', 16) !!} {{ t('Lookup') }}</button>
            </div>
            <div id="lookupResult" class="mt-3"></div>
        </div>
    </div>

    <div class="gsap-section-card bg-white border border-slate-200 rounded-xl shadow-card overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 border-b border-slate-100 font-bold text-[13px] text-slate-900">
            <span class="text-emerald-600">{!! icon('ledger', 16) !!}</span> {{ t('Bulk Tools & Utility Fetch') }}
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                <button type="button" class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="newCodeFetch()">{!! icon('plus', 14) !!} {{ t('Next Code') }}</button>
                <button type="button" class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="syncCustomerList()">{!! icon('sync', 14) !!} {{ t('Sync Database') }}</button>
                <button type="button" class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="exportCustomersCSV()">{!! icon('download', 14) !!} {{ t('CSV Export') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Main Registered Customers Data Table -->
<div class="gsap-section-card bg-white border border-slate-200 rounded-xl shadow-card overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5 border-b border-slate-100">
        <span class="font-bold text-sm text-slate-900 flex items-center gap-2">
            <span class="text-emerald-600">{!! icon('users', 16) !!}</span> {{ t('Registered Customers Registry') }}
            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider border bg-slate-100 text-slate-700 border-slate-200">{{ count($customers) }} {{ t('shown') }} / {{ $totalCount }} {{ t('total') }}</span>
        </span>
        <span class="text-xs text-slate-500">
            {{ t('Active') }}: <strong class="text-emerald-600">{{ $activeCount }}</strong> &bull; {{ t('DC') }}: <strong class="text-rose-600">{{ $dcCount }}</strong>
        </span>
    </div>
    <div class="scrollable-table border-0 rounded-none">
        <div class="scroll-progress"><div class="scroll-progress-bar"></div></div>
        <div class="table-scroll-view">
            <table class="w-full text-[13px]" id="customersTable">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold">
                        <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Code') }}</th>
                        <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Customer Details') }}</th>
                        <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Kebele / Phone') }}</th>
                        <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Meter Specs') }}</th>
                        <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Type & Payment') }}</th>
                        <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Branch') }}</th>
                        <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Status') }}</th>
                        <th class="text-right px-4 py-3 whitespace-nowrap">{{ t('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($customers as $c)
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
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 text-amber-800 border border-amber-300 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider">{!! icon('refresh', 12) !!} {{ $c->customer_status }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 align-middle">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('customer-ledger.index') }}?meterSerial={{ urlencode($c->meter_serial) }}" title="{{ t('View Financial Ledger') }}" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition">{!! icon('book-open', 14) !!}</a>
                                <button type="button" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick='editCustomer({{ json_encode($c->meter_serial) }})'>{!! icon('edit', 14) !!} {{ t('Edit') }}</button>
                                <button type="button" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-rose-100 hover:bg-rose-200 text-rose-700 text-xs font-bold transition" onclick='deleteCustomer({{ json_encode($c->meter_serial) }}, {{ json_encode(trim(($c->first_name ?? '').' '.($c->middle_name ?? '').' '.($c->last_name ?? ''))) }})'>{!! icon('trash', 14) !!}</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
                @if ($customers->isEmpty())
                    <tr>
                        <td colspan="8" class="text-center px-4 py-10 text-slate-500">{{ t('No customer records found in the database') }}</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Collapsible Floating Quick Action Menu (FAB) -->
<div class="fab-wrapper">
    <div class="fab-menu" id="fabMenu">
        <button type="button" class="fab-item inline-flex items-center gap-2.5 px-4 py-2.5 rounded-full bg-slate-900 text-white text-[13px] font-semibold border border-white/15 shadow-lg hover:bg-emerald-700 transition" onclick="openRegisterModal(); toggleFabMenu();">
            {!! icon('plus', 16) !!} <span>{{ t('Register New Customer') }}</span>
        </button>
        <button type="button" class="fab-item inline-flex items-center gap-2.5 px-4 py-2.5 rounded-full bg-slate-900 text-white text-[13px] font-semibold border border-white/15 shadow-lg hover:bg-emerald-700 transition" onclick="openExcelImportModal(); toggleFabMenu();">
            {!! icon('upload', 16) !!} <span>{{ t('Import Excel (CSV)') }}</span>
        </button>
        <button type="button" class="fab-item inline-flex items-center gap-2.5 px-4 py-2.5 rounded-full bg-slate-900 text-white text-[13px] font-semibold border border-white/15 shadow-lg hover:bg-emerald-700 transition" onclick="syncCustomerList(); toggleFabMenu();">
            {!! icon('sync', 16) !!} <span>{{ t('Sync Database') }}</span>
        </button>
        <button type="button" class="fab-item inline-flex items-center gap-2.5 px-4 py-2.5 rounded-full bg-slate-900 text-white text-[13px] font-semibold border border-white/15 shadow-lg hover:bg-emerald-700 transition" onclick="exportCustomersCSV(); toggleFabMenu();">
            {!! icon('download', 16) !!} <span>{{ t('Export CSV') }}</span>
        </button>
    </div>
    <button type="button" class="fab-trigger-btn w-14 h-14 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white shadow-[0_8px_24px_rgba(5,150,105,0.45)] flex items-center justify-center transition" onclick="toggleFabMenu()" title="Quick Actions">
        {!! icon('plus', 22) !!}
    </button>
</div>

<!-- Multi-Step Registration Modal -->
<div class="modal-backdrop v2" id="registerModal">
    <div class="modal v2 bg-white rounded-2xl shadow-2xl w-full max-w-[780px] max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-gradient-to-br from-slate-50 to-white shrink-0">
            <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">
                {!! icon('plus', 20) !!}
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="m-0 text-base font-bold text-slate-900">{{ t('Customer Registration Form') }}</h3>
                <div class="text-xs text-slate-500 mt-0.5">{{ t('Register a new water meter customer in the system') }}</div>
            </div>
            <button type="button" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition" onclick="closeModal('registerModal')">&times;</button>
        </div>
        <div class="p-5 overflow-y-auto">
            <!-- Step Wizard Nav Tabs -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mb-5">
                <button type="button" id="tab-step-1" onclick="switchRegStep(1)" class="group active flex items-center gap-2.5 px-3.5 py-2.5 rounded-lg border border-slate-200 bg-white text-[13px] font-semibold text-slate-600 transition [&.active]:bg-emerald-600 [&.active]:border-emerald-600 [&.active]:text-white shadow-sm">
                    <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-[11px] font-bold shrink-0 group-[.active]:bg-white/25 group-[.active]:text-white">1</span>
                    <span>{{ t('Identity & Personal') }}</span>
                </button>
                <button type="button" id="tab-step-2" onclick="switchRegStep(2)" class="group flex items-center gap-2.5 px-3.5 py-2.5 rounded-lg border border-slate-200 bg-white text-[13px] font-semibold text-slate-600 transition [&.active]:bg-emerald-600 [&.active]:border-emerald-600 [&.active]:text-white shadow-sm">
                    <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-[11px] font-bold shrink-0 group-[.active]:bg-white/25 group-[.active]:text-white">2</span>
                    <span>{{ t('Meter & Reading') }}</span>
                </button>
                <button type="button" id="tab-step-3" onclick="switchRegStep(3)" class="group flex items-center gap-2.5 px-3.5 py-2.5 rounded-lg border border-slate-200 bg-white text-[13px] font-semibold text-slate-600 transition [&.active]:bg-emerald-600 [&.active]:border-emerald-600 [&.active]:text-white shadow-sm">
                    <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-[11px] font-bold shrink-0 group-[.active]:bg-white/25 group-[.active]:text-white">3</span>
                    <span>{{ t('Tariff & Branch') }}</span>
                </button>
            </div>

            <form id="registerForm">
                <!-- STEP 1: IDENTITY -->
                <div id="reg-step-1-content" class="reg-step-pane">
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Customer Code') }} <span class="text-rose-500">*</span></label>
                                <div class="flex gap-2">
                                    <input type="text" name="meterSerial" required class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder:text-slate-400" placeholder="ETY-0001">
                                    <button type="button" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition shrink-0" onclick="generateCode()">{!! icon('refresh', 12) !!} {{ t('Auto') }}</button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Kebele') }} <span class="text-rose-500">*</span></label>
                                <input type="text" name="kebele" required class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder:text-slate-400" placeholder="e.g. 01">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('First Name') }} <span class="text-rose-500">*</span></label>
                                <input type="text" name="firstName" required class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder:text-slate-400" placeholder="e.g. Abebe">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Middle Name') }}</label>
                                <input type="text" name="middleName" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder:text-slate-400" placeholder="e.g. Kebede">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Last Name') }}</label>
                                <input type="text" name="lastName" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder:text-slate-400" placeholder="e.g. Tadesse">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Phone Number') }}</label>
                                <input type="text" name="phoneNumber" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder:text-slate-400" placeholder="+251911223344">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: METER SPECS -->
                <div id="reg-step-2-content" class="reg-step-pane" style="display:none;">
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Meter Size') }}</label>
                                <select name="meterSize" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                                    @foreach ($meterSizes as $v)
                                        <option value="{{ $v }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Meter Number') }}</label>
                                <input type="number" name="meterNum" value="0" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Serial Number (Bill #)') }}</label>
                                <input type="text" name="billNum" placeholder="SN-0001" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder:text-slate-400">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Start Reading') }} (m³)</label>
                                <input type="number" step="0.01" name="startValue" value="0" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Sold Date') }}</label>
                            <input type="date" name="soldDate" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                        </div>
                    </div>
                </div>

                <!-- STEP 3: CLASSIFICATION -->
                <div id="reg-step-3-content" class="reg-step-pane" style="display:none;">
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Customer Type') }}</label>
                                <select name="customerType" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                                    @foreach ($customerTypes as $v)
                                        <option value="{{ $v }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Payment Way') }}</label>
                                <select name="paymentWay" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                                    @foreach ($paymentWays as $v)
                                        <option value="{{ $v }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Customer Branch') }}</label>
                                <select name="customerBranch" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                                    @foreach ($branches as $v)
                                        <option value="{{ $v }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Status') }}</label>
                                <select name="customerStatus" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                                    @foreach ($customerStatuses as $v)
                                        <option value="{{ $v }}" @if($v==='Active') selected @endif>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Reader Block') }}</label>
                            <input type="text" name="readerBlock" placeholder="Block-A" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder:text-slate-400">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="px-5 py-4 border-t border-slate-100 bg-slate-50 flex gap-3 justify-end shrink-0">
            <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" id="regPrevBtn" onclick="prevRegStep()" style="display:none;">&larr; {{ t('Previous') }}</button>
            <span class="flex-1"></span>
            <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="closeModal('registerModal')">{{ t('Cancel') }}</button>
            <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm" id="regNextBtn" onclick="nextRegStep()">{{ t('Next Step') }} &rarr;</button>
            <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm" id="regSubmitBtn" onclick="submitRegister()" style="display:none;">{!! icon('check', 16) !!} {{ t('Complete Registration') }}</button>
        </div>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal-backdrop v2" id="editModal">
    <div class="modal v2 bg-white rounded-2xl shadow-2xl w-full max-w-[860px] max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-gradient-to-br from-slate-50 to-white shrink-0">
            <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">
                {!! icon('edit', 20) !!}
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="m-0 text-base font-bold text-slate-900">{{ t('Edit Customer Record') }} — <span id="editCode" class="text-emerald-600"></span></h3>
                <div class="text-xs text-slate-500 mt-0.5">{{ t('Update customer profile & operational settings') }}</div>
            </div>
            <button type="button" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition" onclick="closeModal('editModal')">&times;</button>
        </div>
        <div class="p-5 overflow-y-auto">
            <div id="editInfo" class="mb-4 p-3.5 bg-slate-50 rounded-lg border-l-4 border-emerald-500"></div>

            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2.5">{{ t('Select Field Operation to Update') }}</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                <button type="button" class="inline-flex items-center justify-start gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="promptUpdate('update-customer-info', ['meterSerial','firstName','middleName','lastName','phoneNumber'], 'Update Customer Info')">{!! icon('file-text', 14) !!} Update Personal Info</button>
                <button type="button" class="inline-flex items-center justify-start gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="promptUpdate('update-first-name', ['meterSerial','firstName'], 'Update First Name')">{!! icon('edit', 14) !!} Update First Name</button>
                <button type="button" class="inline-flex items-center justify-start gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="promptUpdate('update-middle-name', ['meterSerial','middleName'], 'Update Middle Name')">{!! icon('edit', 14) !!} Update Middle Name</button>
                <button type="button" class="inline-flex items-center justify-start gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="promptUpdate('update-last-name', ['meterSerial','lastName'], 'Update Last Name')">{!! icon('edit', 14) !!} Update Last Name</button>
                <button type="button" class="inline-flex items-center justify-start gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="promptUpdate('update-phone-number', ['meterSerial','phoneNumber'], 'Update Phone Number')">{!! icon('phone', 14) !!} Update Phone</button>
                <button type="button" class="inline-flex items-center justify-start gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="promptUpdate('update-kebele', ['meterSerial','kebele'], 'Update Kebele')">{!! icon('map-pin', 14) !!} Update Kebele</button>
                <button type="button" class="inline-flex items-center justify-start gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="promptUpdate('update-customer-type', ['meterSerial','customerType'], 'Update Customer Type', {customerType: {{ json_encode($customerTypes) }} })">{!! icon('tag', 14) !!} Customer Type</button>
                <button type="button" class="inline-flex items-center justify-start gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="promptUpdate('update-meter-size', ['meterSerial','meterSize','meterNum'], 'Update Meter Size', {meterSize: {{ json_encode($meterSizes) }} })">{!! icon('edit', 14) !!} Meter Size</button>
                <button type="button" class="inline-flex items-center justify-start gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="promptUpdate('update-payment-way', ['meterSerial','paymentWay'], 'Update Payment Way', {paymentWay: {{ json_encode($paymentWays) }} })">{!! icon('credit-card', 14) !!} Payment Way</button>
                <button type="button" class="inline-flex items-center justify-start gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="promptUpdate('update-customer-branch', ['meterSerial','customerBranch'], 'Update Branch', {customerBranch: {{ json_encode($branches) }} })">{!! icon('building', 14) !!} Branch</button>
                <button type="button" class="inline-flex items-center justify-start gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="promptUpdate('update-bill-num', ['meterSerial','billNum'], 'Update Bill Number')">{!! icon('receipt', 14) !!} Bill Number</button>
                <button type="button" class="inline-flex items-center justify-start gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="promptUpdate('update-reader-block', ['meterSerial','readerBlock'], 'Update Reader Block')">{!! icon('alert', 14) !!} Reader Block</button>
                <button type="button" class="inline-flex items-center justify-start gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="promptUpdate('update-start-reading', ['meterSerial','startValue'], 'Update Start Reading')">{!! icon('clock', 14) !!} Start Reading</button>

                <button type="button" class="inline-flex items-center justify-start gap-1.5 px-2.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition" onclick="updateStatus('Updated')">{!! icon('tag', 14) !!} Mark Updated</button>
                <button type="button" class="inline-flex items-center justify-start gap-1.5 px-2.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition" onclick="updateStatus('DC')">{!! icon('zap', 14) !!} Disconnect (DC)</button>
                <button type="button" class="inline-flex items-center justify-start gap-1.5 px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition" onclick="updateStatus('Active')">{!! icon('check', 14) !!} Re-Activate</button>
                <button type="button" class="inline-flex items-center justify-start gap-1.5 px-2.5 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition" onclick="updateStatus('Deleted')">{!! icon('x', 14) !!} Mark Deleted</button>
                <button type="button" class="inline-flex items-center justify-start gap-1.5 px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition" onclick="syncOne()">{!! icon('sync', 14) !!} Sync Customer</button>
                <button type="button" class="inline-flex items-center justify-start gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="submitLocation()">{!! icon('map-pin', 14) !!} GPS Location</button>
                <button type="button" class="inline-flex items-center justify-start gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="meterOwnerTransfer()">{!! icon('sync', 14) !!} Owner Transfer</button>
                <button type="button" class="inline-flex items-center justify-start gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="changeNewMeter()">{!! icon('wrench', 14) !!} Install New Meter</button>
            </div>
        </div>
        <div class="px-5 py-4 border-t border-slate-100 bg-slate-50 flex gap-3 justify-end shrink-0">
            <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="closeModal('editModal')">{{ t('Close') }}</button>
        </div>
    </div>
</div>

<!-- Excel Import Modal -->
<div class="modal-backdrop v2" id="excelModal">
    <div class="modal v2 bg-white rounded-2xl shadow-2xl w-full max-w-[560px] max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-gradient-to-br from-slate-50 to-white shrink-0">
            <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">
                {!! icon('upload', 20) !!}
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="m-0 text-base font-bold text-slate-900">{{ t('Import Customers from Excel/CSV') }}</h3>
                <div class="text-xs text-slate-500 mt-0.5">{{ t('Bulk register customers from a spreadsheet') }}</div>
            </div>
            <button type="button" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition" onclick="closeModal('excelModal')">&times;</button>
        </div>
        <div class="p-5 overflow-y-auto">
            <div class="flex items-start gap-2.5 rounded-lg border border-sky-200 border-l-4 border-l-sky-500 bg-sky-50 text-sky-800 px-3.5 py-3 text-[13px] font-medium mb-4">
                <span class="mt-0.5 shrink-0">{!! icon('info', 16) !!}</span>
                <div>
                    <strong>{{ t('Required columns in CSV') }}:</strong>
                    <code class="block mt-1.5 text-[11px] break-all bg-white border border-sky-200 rounded px-2 py-1.5">meterSerial, firstName, middleName, lastName, kebele, phoneNumber, meterSize, customerType, billNum, startValue, paymentWay, customerBranch, readerBlock</code>
                </div>
            </div>
            <form id="excelForm" enctype="multipart/form-data">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('CSV File') }}</label>
                    <input type="file" name="excelFile" accept=".csv,.xlsx" required class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                </div>
            </form>
            <div class="mt-3">
                <a href="{{ $baseUrl }}/sample-customer-template.csv" download class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition">{!! icon('download', 14) !!} {{ t('Download CSV template') }}</a>
            </div>
        </div>
        <div class="px-5 py-4 border-t border-slate-100 bg-slate-50 flex gap-3 justify-end shrink-0">
            <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="closeModal('excelModal')">{{ t('Cancel') }}</button>
            <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm" onclick="submitExcel()">{!! icon('upload', 16) !!} {{ t('Register From Excel') }}</button>
        </div>
    </div>
</div>

<!-- Update Prompt Modal -->
<div class="modal-backdrop v2" id="promptModal">
    <div class="modal v2 bg-white rounded-2xl shadow-2xl w-full max-w-[500px] max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-gradient-to-br from-slate-50 to-white shrink-0">
            <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">
                {!! icon('edit', 20) !!}
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="m-0 text-base font-bold text-slate-900" id="promptTitle">{{ t('Update') }}</h3>
                <div class="text-xs text-slate-500 mt-0.5">{{ t('Enter new value below') }}</div>
            </div>
            <button type="button" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition" onclick="closeModal('promptModal')">&times;</button>
        </div>
        <div class="p-5 overflow-y-auto">
            <form id="promptForm">
                <div id="promptFields" class="bg-slate-50 border border-slate-200 rounded-lg p-3.5 space-y-2.5"></div>
            </form>
        </div>
        <div class="px-5 py-4 border-t border-slate-100 bg-slate-50 flex gap-3 justify-end shrink-0">
            <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" onclick="closeModal('promptModal')">{{ t('Cancel') }}</button>
            <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm" onclick="submitPromptUpdate()">{!! icon('check', 16) !!} {{ t('Submit Update') }}</button>
        </div>
    </div>
</div>

<style>
    /* Fallback styling for fields generated at runtime by promptUpdate()
       (createElement with legacy classes form-group / form-control / fancy).
       View HTML uses Tailwind utilities directly; this is only for JS output. */
    .form-group { margin-bottom: 0.75rem; }
    .form-control, .fancy {
        width: 100%;
        padding: 10px 12px;
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        font-size: 14px;
        color: #0F172A;
        font-family: inherit;
    }
    .form-control:focus, .fancy:focus {
        outline: none;
        border-color: #10B981;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.4);
    }
    .form-control::placeholder { color: #94A3B8; }
</style>

<script>
let currentEditCode = null;
let promptEndpoint = null;
let promptFields = null;
let currentRegStep = 1;

function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }

function toggleFabMenu() {
    const wrapper = document.querySelector('.fab-wrapper');
    if (wrapper) wrapper.classList.toggle('open');
}

function switchRegStep(step) {
    currentRegStep = step;
    [1, 2, 3].forEach(s => {
        const pane = document.getElementById(`reg-step-${s}-content`);
        const tab = document.getElementById(`tab-step-${s}`);
        if (pane) pane.style.display = (s === step) ? 'block' : 'none';
        if (tab) tab.classList.toggle('active', s === step);
    });

    const prevBtn = document.getElementById('regPrevBtn');
    const nextBtn = document.getElementById('regNextBtn');
    const submitBtn = document.getElementById('regSubmitBtn');

    if (prevBtn) prevBtn.style.display = (step > 1) ? 'inline-flex' : 'none';
    if (nextBtn) nextBtn.style.display = (step < 3) ? 'inline-flex' : 'none';
    if (submitBtn) submitBtn.style.display = (step === 3) ? 'inline-flex' : 'none';
}

function nextRegStep() {
    if (currentRegStep < 3) switchRegStep(currentRegStep + 1);
}

function prevRegStep() {
    if (currentRegStep > 1) switchRegStep(currentRegStep - 1);
}

function openRegisterModal() {
    document.getElementById('registerForm').reset();
    document.querySelector('[name=soldDate]').value = new Date().toISOString().slice(0,10);
    switchRegStep(1);
    openModal('registerModal');
}

function openExcelImportModal() { openModal('excelModal'); }

function generateCode() {
    const kebele = document.querySelector('[name=kebele]').value || '00';
    fetch(apiUrl('active_customers/get-recent-code') + `?kebele=${encodeURIComponent(kebele)}`)
      .then(r => r.text())
      .then(code => document.querySelector('[name=meterSerial]').value = code);
}

function submitRegister() {
    const form = document.getElementById('registerForm');
    const fd = new FormData(form);
    const body = {};
    fd.forEach((v, k) => body[k] = v);
    body.meterNum = parseInt(body.meterNum || 0);
    body.startValue = parseFloat(body.startValue || 0);
    fetch(apiUrl('active_customers/add-active-customer'), {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify(body),
    }).then(r => {
        if (r.status === 201) {
            showToast('Customer registered successfully', 'success');
            closeModal('registerModal');
            setTimeout(() => location.reload(), 800);
        } else throw new Error('Failed');
    }).catch(e => showToast('Failed to register customer', 'danger'));
}

function lookupCustomer() {
    const code = document.getElementById('lookupCode').value.trim();
    if (!code) return showToast('Enter a customer code', 'warning');
    fetch(apiUrl(`active_customers/get-single-customer/${encodeURIComponent(code)}`))
      .then(r => r.json())
      .then(rows => {
          const div = document.getElementById('lookupResult');
          if (!rows || !rows.length) {
              div.innerHTML = '<div class="flex items-center gap-2.5 rounded-lg border border-amber-200 border-l-4 border-l-amber-500 bg-amber-50 text-amber-800 px-3.5 py-3 text-[13px] font-medium">No customer found.</div>';
              return;
          }
          const c = rows[0];
          div.innerHTML = `
            <div class="rounded-xl border border-slate-200 overflow-hidden mb-2">
              <table class="w-full text-[13px]">
                <tbody>
                  <tr class="border-b border-slate-100 last:border-0">
                    <th class="bg-slate-50 text-left px-3 py-2 whitespace-nowrap text-[11px] uppercase tracking-wider font-bold text-slate-500">Code</th>
                    <td class="px-3 py-2 text-slate-700 font-mono font-bold text-emerald-700">${c.meter_serial}</td>
                  </tr>
                  <tr class="border-b border-slate-100">
                    <th class="bg-slate-50 text-left px-3 py-2 whitespace-nowrap text-[11px] uppercase tracking-wider font-bold text-slate-500">Name</th>
                    <td class="px-3 py-2 text-slate-700">${c.first_name} ${c.middle_name||''} ${c.last_name||''}</td>
                  </tr>
                  <tr class="border-b border-slate-100">
                    <th class="bg-slate-50 text-left px-3 py-2 whitespace-nowrap text-[11px] uppercase tracking-wider font-bold text-slate-500">Kebele</th>
                    <td class="px-3 py-2 text-slate-700">${c.kebele||'—'}</td>
                  </tr>
                  <tr class="border-b border-slate-100">
                    <th class="bg-slate-50 text-left px-3 py-2 whitespace-nowrap text-[11px] uppercase tracking-wider font-bold text-slate-500">Phone</th>
                    <td class="px-3 py-2 text-slate-700">${c.phone_number||'—'}</td>
                  </tr>
                  <tr class="border-b border-slate-100">
                    <th class="bg-slate-50 text-left px-3 py-2 whitespace-nowrap text-[11px] uppercase tracking-wider font-bold text-slate-500">Meter</th>
                    <td class="px-3 py-2 text-slate-700">${c.meter_size||'—'} (#${c.meter_num||0})</td>
                  </tr>
                  <tr class="border-b border-slate-100">
                    <th class="bg-slate-50 text-left px-3 py-2 whitespace-nowrap text-[11px] uppercase tracking-wider font-bold text-slate-500">Type</th>
                    <td class="px-3 py-2 text-slate-700">${c.customer_type||'—'}</td>
                  </tr>
                  <tr class="border-b border-slate-100">
                    <th class="bg-slate-50 text-left px-3 py-2 whitespace-nowrap text-[11px] uppercase tracking-wider font-bold text-slate-500">Branch</th>
                    <td class="px-3 py-2 text-slate-700">${c.customer_branch||'—'}</td>
                  </tr>
                  <tr class="border-b border-slate-100">
                    <th class="bg-slate-50 text-left px-3 py-2 whitespace-nowrap text-[11px] uppercase tracking-wider font-bold text-slate-500">Status</th>
                    <td class="px-3 py-2 text-slate-700"><span class="badge ${c.customer_status==='Active'?'badge-success':'badge-danger'}">${c.customer_status}</span></td>
                  </tr>
                  <tr>
                    <th class="bg-slate-50 text-left px-3 py-2 whitespace-nowrap text-[11px] uppercase tracking-wider font-bold text-slate-500">Start Reading</th>
                    <td class="px-3 py-2 text-slate-700">${c.start_value} m³</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-sm" onclick='editCustomer(${JSON.stringify(c.meter_serial)})'>Edit Customer</button>
          `;
      });
}

function editCustomer(code) {
    currentEditCode = code;
    document.getElementById('editCode').textContent = code;
    fetch(apiUrl(`active_customers/get-active-customer/${encodeURIComponent(code)}`))
      .then(r => r.json())
      .then(c => {
          if (!c) return showToast('Customer not found', 'danger');
          document.getElementById('editInfo').innerHTML = `
            <div class="font-bold text-[14px] text-slate-900">${c.first_name} ${c.middle_name||''} ${c.last_name||''}</div>
            <div class="text-xs text-slate-500 mt-0.5">
              Kebele: ${c.kebele||'—'} &bull; Phone: ${c.phone_number||'—'} &bull; Type: ${c.customer_type||'—'} &bull; Status: <strong>${c.customer_status}</strong>
            </div>
          `;
      });
    openModal('editModal');
}

function deleteCustomer(code, name) {
    confirmDialog(`Delete customer ${code}?`, `Are you sure you want to mark customer ${name} as deleted?`, 'danger')
      .then(ok => {
          if (!ok) return;
          fetch(apiUrl(`active_customers/update-single-customer-status`) + `?meterSerial=${encodeURIComponent(code)}&customerStatus=Deleted`, {method:'PUT'})
            .then(r => r.text())
            .then(() => { showToast('Customer marked as Deleted', 'success'); setTimeout(()=>location.reload(), 800); });
      });
}

function updateStatus(newStatus) {
    if (!currentEditCode) return;
    confirmDialog(`Change Status`, `Change status of ${currentEditCode} to "${newStatus}"?`, 'warning')
      .then(ok => {
          if (!ok) return;
          fetch(apiUrl(`active_customers/update-single-customer-status`) + `?meterSerial=${encodeURIComponent(currentEditCode)}&customerStatus=${encodeURIComponent(newStatus)}`, {method:'PUT'})
            .then(r => r.text())
            .then(() => { showToast(`Status updated to ${newStatus}`, 'success'); setTimeout(()=>location.reload(), 800); });
      });
}

function syncOne() {
    if (!currentEditCode) return;
    fetch(apiUrl(`active_customers/update-sync-status`) + `?meterSerial=${encodeURIComponent(currentEditCode)}`, {method:'PUT'})
      .then(r => r.text())
      .then(() => showToast('Customer synced successfully', 'success'));
}

function syncCustomerList() {
    confirmDialog('Sync Customer Database', 'Sync all active customer records to cloud server?', 'info')
      .then(ok => {
          if (!ok) return;
          fetch(apiUrl('active_customers/send-active-customers'), {method:'POST'})
            .then(r => r.json())
            .then(d => showToast(`Synced ${d.count} customer records`, 'success'));
      });
}

function newCodeFetch() {
    const kebele = prompt('Enter kebele number:');
    if (!kebele) return;
    fetch(apiUrl('active_customers/get-recent-code') + `?kebele=${encodeURIComponent(kebele)}`)
      .then(r => r.text())
      .then(code => showToast(`Next available code for Kebele ${kebele}: ${code}`, 'info'));
}

function promptUpdate(endpoint, fields, title, dropdowns={}) {
    if (!currentEditCode) return;
    promptEndpoint = endpoint;
    promptFields = fields;
    document.getElementById('promptTitle').textContent = title;
    const div = document.getElementById('promptFields');
    div.innerHTML = '';
    fields.forEach(f => {
        if (f === 'meterSerial') return;
        const group = document.createElement('div');
        group.className = 'form-group';
        group.style.marginBottom = '10px';
        const lbl = document.createElement('label');
        lbl.textContent = f.replace(/([A-Z])/g, ' $1').replace(/^./, s => s.toUpperCase());
        group.appendChild(lbl);
        let input;
        if (dropdowns[f]) {
            input = document.createElement('select');
            input.className = 'fancy';
            dropdowns[f].forEach(v => {
                const o = document.createElement('option');
                o.value = v; o.textContent = v;
                input.appendChild(o);
            });
        } else {
            input = document.createElement('input');
            input.className = 'form-control';
            input.type = (f.includes('Value') || f.includes('Num')) ? 'number' : 'text';
            input.step = '0.01';
        }
        input.name = f;
        group.appendChild(input);
        div.appendChild(group);
    });
    openModal('promptModal');
}

function submitPromptUpdate() {
    const form = document.getElementById('promptForm');
    const fd = new FormData(form);
    const params = new URLSearchParams();
    params.set('meterSerial', currentEditCode);
    fd.forEach((v, k) => params.set(k, v));
    fetch(apiUrl(`active_customers/${promptEndpoint}`) + `?${params.toString()}`, {method:'PUT'})
      .then(r => r.text())
      .then(() => {
          showToast('Update successful', 'success');
          closeModal('promptModal');
          setTimeout(()=>location.reload(), 800);
      });
}

function submitLocation() {
    if (!currentEditCode) return;
    if (!navigator.geolocation) return showToast('Geolocation not available', 'danger');
    navigator.geolocation.getCurrentPosition(pos => {
        const lat = pos.coords.latitude.toFixed(6);
        const lng = pos.coords.longitude.toFixed(6);
        fetch(apiUrl('meter_location/add-meter-location'), {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({customerCode: currentEditCode, latitudeVal: lat, longitudeVal: lng})
        }).then(r => {
            if (r.status === 201) showToast(`Location saved: ${lat}, ${lng}`, 'success');
            else showToast('Failed to save location', 'danger');
        });
    }, () => showToast('Cannot get location', 'danger'));
}

function meterOwnerTransfer() {
    const newOwner = prompt('Enter new owner full name (First Middle Last):');
    if (!newOwner) return;
    const parts = newOwner.trim().split(/\s+/);
    const params = new URLSearchParams({
        meterSerial: currentEditCode,
        firstName: parts[0] || '',
        middleName: parts[1] || '',
        lastName: parts.slice(2).join(' '),
        phoneNumber: ''
    });
    fetch(apiUrl(`active_customers/update-customer-info`) + `?${params.toString()}`, {method:'PUT'})
      .then(r => r.text())
      .then(() => { showToast('Owner transferred successfully', 'success'); setTimeout(()=>location.reload(), 800); });
}

function changeNewMeter() {
    const newSerial = prompt('Enter new meter serial number:');
    if (!newSerial) return;
    const newSize = prompt('Enter new meter size (1/2", 3/4", 1", 1 and 1/2", 2"):', '1/2"');
    if (!newSize) return;
    const newNum = prompt('Enter new meter number (integer):', '1');
    const params = new URLSearchParams({meterSerial: currentEditCode, meterSize: newSize, meterNum: newNum || 1});
    fetch(apiUrl(`active_customers/update-meter-size`) + `?${params.toString()}`, {method:'PUT'})
      .then(r => r.text())
      .then(() => {
          const p2 = new URLSearchParams({meterSerial: currentEditCode, billNum: newSerial});
          fetch(apiUrl(`active_customers/update-bill-num`) + `?${p2.toString()}`, {method:'PUT'})
            .then(() => { showToast('New meter installed successfully', 'success'); setTimeout(()=>location.reload(), 800); });
      });
}

function submitExcel() {
    const form = document.getElementById('excelForm');
    const fd = new FormData(form);
    fetch('{{ route('import.customers') }}', {method:'POST', body: fd, headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}})
      .then(r => r.json())
      .then(d => {
          if (d.error) { showToast(d.error, 'danger'); return; }
          showToast(`Imported ${d.imported} customers (${d.skipped} skipped)`, 'success');
          closeModal('excelModal');
          setTimeout(()=>location.reload(), 1000);
      });
}

function exportCustomersCSV() { window.location.href = '{{ route('export.customers') }}'; }

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.scrollable-table').forEach(wrapper => {
        const view = wrapper.querySelector('.table-scroll-view');
        const bar = wrapper.querySelector('.scroll-progress-bar');
        if (!view || !bar) return;
        const update = () => {
            const max = view.scrollHeight - view.clientHeight;
            const width = max > 0 ? (view.scrollTop / max) * 100 : 0;
            bar.style.width = width + '%';
        };
        view.addEventListener('scroll', update);
        update();
    });
});

(function initCustomerCharts() {
    const run = () => {
        if (typeof Chart === 'undefined') return;

        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            const oldChart = Chart.getChart(statusCtx);
            if (oldChart) oldChart.destroy();
            new Chart(statusCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Active', 'Disconnected (DC)', 'Updated / Pending'],
                    datasets: [{
                        data: [{{ $activeCount }}, {{ $dcCount }}, {{ max(0, $totalCount - $activeCount - $dcCount) }}],
                        backgroundColor: ['#10B981', '#EF4444', '#FEA619'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11, family: 'Inter' } } }
                    },
                    cutout: '68%'
                }
            });
        }

        const branchCtx = document.getElementById('branchChart');
        if (branchCtx) {
            const oldBranch = Chart.getChart(branchCtx);
            if (oldBranch) oldBranch.destroy();
            new Chart(branchCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Eteya', 'Hurutaa', 'Heexosaa', 'Dheeraa'],
                    datasets: [{
                        label: 'Customers',
                        data: [{{ intval($totalCount * 0.45) }}, {{ intval($totalCount * 0.25) }}, {{ intval($totalCount * 0.18) }}, {{ intval($totalCount * 0.12) }}],
                        backgroundColor: '#10B981',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 10 } } },
                        x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                    }
                }
            });
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        setTimeout(run, 100);
    }
})();
</script>
@endsection
