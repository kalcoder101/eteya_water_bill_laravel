@extends('layouts.app')

@section('content')

<!-- Page Header & Banner -->
<div class="gsap-hero flex flex-wrap items-end justify-between gap-4 mb-6">
    <div>
        <div class="text-[11px] uppercase tracking-widest font-bold text-slate-500 mb-1">
            {{ t('Financial Operations') }} &bull; {{ t('Customer Ledger') }}
        </div>
        <h2 class="m-0 text-[22px] font-bold tracking-tight text-slate-900 flex items-center gap-2.5">
            <span class="text-emerald-600">{!! icon('book-open', 24) !!}</span> {{ t('Customers Ledger Statement') }}
        </h2>
        <p class="mt-1 text-[13px] text-slate-500">
            {{ t('View comprehensive water consumption and billing history by customer and year') }}
            @if ($customer)
                &bull; <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider">{{ $customer->meter_serial }} — {{ trim(($customer->first_name ?? '').' '.($customer->middle_name ?? '').' '.($customer->last_name ?? '')) }}</span>
            @endif
        </p>
    </div>
    <div class="flex items-center gap-2.5">
        @if (! empty($customer))
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition">{!! icon('print', 14) !!} {{ t('Print Statement') }}</button>
        @endif
    </div>
</div>

<!-- Customer Selection & Search Toolbar -->
<div class="bg-white border border-slate-200 rounded-xl shadow-card p-4 mb-5">
    <form method="get" action="{{ route('customer-ledger.index') }}" class="flex flex-wrap gap-3.5 items-end">
        <div class="flex-1 min-w-[280px]">
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Select Customer') }}</label>
            <input id="customerSearch" type="text" placeholder="{{ t('Type code, name, phone to filter...') }}" oninput="filterCustomerOptions()"
                   class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder:text-slate-400 mb-1.5">
            <select id="customerSelect" name="meterSerial" class="fancy" onchange="this.form.submit()" style="width:100%;">
                <option value="">— {{ t('Choose a customer account') }} —</option>
                @foreach ($customers as $c)
                    <option value="{{ $c->meter_serial }}" @if($meterSerial===$c->meter_serial) selected @endif>
                        {{ $c->meter_serial }} — {{ trim(($c->first_name ?? '').' '.($c->middle_name ?? '').' '.($c->last_name ?? '')) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="min-w-[140px]">
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Billing Year') }}</label>
            <select name="year" class="fancy" onchange="this.form.submit()" style="width:100%;">
                @foreach ($availableYears as $y)
                    <option value="{{ $y }}" @if((string)$y === (string)$year) selected @endif>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm">{!! icon('search', 15) !!} {{ t('Load Ledger') }}</button>
    </form>
</div>

@if (empty($customer))
<div class="bg-white border border-slate-200 rounded-xl shadow-card py-14 px-5 text-center">
    <div class="text-slate-300 mb-3">{!! icon('book-open', 54) !!}</div>
    <h3 class="m-0 text-[15px] font-semibold text-slate-700">{{ t('No Customer Account Selected') }}</h3>
    <p class="text-xs text-slate-500 mt-1.5">{{ t('Search and select a customer account from the dropdown above to load their ledger history.') }}</p>
</div>
@else

<!-- KPI Stat Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="gsap-stat-card gsap-hover-card p-5 rounded-xl bg-white border border-slate-200 shadow-card space-y-2">
        <div class="flex items-center justify-between text-xs">
            <span class="font-bold uppercase tracking-wider text-[11px] text-emerald-800">{{ t('Total Billed') }}</span>
            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                <span class="[&>svg]:text-[16px]">{!! icon('receipt', 16) !!}</span>
            </div>
        </div>
        <div class="text-2xl font-bold text-slate-900 font-mono tabular-nums" data-gsap-counter data-target-val="{{ $grandTotal }}">{{ number_format($grandTotal, 0) }} <span class="text-[13px] text-slate-500">ETB</span></div>
        <div class="text-[11px] text-slate-500">{{ count($ledger) }} {{ t('Bills in') }} {{ $year }}</div>
    </div>

    <div class="gsap-stat-card gsap-hover-card p-5 rounded-xl bg-white border border-slate-200 shadow-card space-y-2">
        <div class="flex items-center justify-between text-xs">
            <span class="font-bold uppercase tracking-wider text-[11px] text-sky-800">{{ t('Water Consumption') }}</span>
            <div class="w-8 h-8 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center">
                <span class="[&>svg]:text-[16px]">{!! icon('water', 16) !!}</span>
            </div>
        </div>
        <div class="text-2xl font-bold text-slate-900 font-mono tabular-nums text-emerald-600" data-gsap-counter data-target-val="{{ $totalConsumption }}">{{ number_format($totalConsumption, 1) }} <span class="text-[13px] text-slate-500">m³</span></div>
        <div class="text-[11px] text-slate-500">{{ count($ledger) > 0 ? number_format($totalConsumption / count($ledger), 1) : 0 }} m³ {{ t('Avg / Month') }}</div>
    </div>

    <div class="gsap-stat-card gsap-hover-card p-5 rounded-xl bg-white border border-slate-200 shadow-card space-y-2">
        <div class="flex items-center justify-between text-xs">
            <span class="font-bold uppercase tracking-wider text-[11px] text-emerald-800">{{ t('Paid Revenue') }}</span>
            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                <span class="[&>svg]:text-[16px]">{!! icon('check', 16) !!}</span>
            </div>
        </div>
        <div class="text-2xl font-bold text-slate-900 font-mono tabular-nums text-emerald-600" data-gsap-counter data-target-val="{{ $paidTotal }}">{{ number_format($paidTotal, 0) }} <span class="text-[13px] text-slate-500">ETB</span></div>
        <div class="text-[11px] text-slate-500">{{ $paidBills }} {{ t('Paid Bills') }}</div>
    </div>

    <div class="gsap-stat-card gsap-hover-card p-5 rounded-xl bg-white border border-slate-200 shadow-card space-y-2">
        <div class="flex items-center justify-between text-xs">
            <span class="font-bold uppercase tracking-wider text-[11px] text-rose-800">{{ t('Unpaid Balance') }}</span>
            <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center">
                <span class="[&>svg]:text-[16px]">{!! icon('x', 16) !!}</span>
            </div>
        </div>
        <div class="text-2xl font-bold text-slate-900 font-mono tabular-nums text-rose-600" data-gsap-counter data-target-val="{{ $unpaidTotal }}">{{ number_format($unpaidTotal, 0) }} <span class="text-[13px] text-slate-500">ETB</span></div>
        <div class="text-[11px] text-slate-500">{{ $unpaidBills }} {{ t('Unpaid Bills') }}</div>
    </div>
</div>

<!-- Chart.js Consumption & Monthly Cost Trends -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
    <div class="gsap-chart-card p-5 rounded-xl bg-white border border-slate-200 shadow-card">
        <div class="flex items-center justify-between gap-3 mb-4">
            <span class="flex items-center gap-2 font-serif font-bold text-sm text-slate-900">
                <span class="text-emerald-600">{!! icon('water', 16) !!}</span> {{ t('Monthly Consumption Trend (m³)') }}
            </span>
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider">{{ number_format($totalConsumption, 1) }} m³ Total</span>
        </div>
        <div class="h-[190px] relative flex items-center justify-center">
            <canvas id="ledgerConsChart"></canvas>
        </div>
    </div>

    <div class="gsap-chart-card p-5 rounded-xl bg-white border border-slate-200 shadow-card">
        <div class="flex items-center justify-between gap-3 mb-4">
            <span class="flex items-center gap-2 font-serif font-bold text-sm text-slate-900">
                <span class="text-emerald-600">{!! icon('bar-chart', 16) !!}</span> {{ t('Monthly Billed Cost Trend (ETB)') }}
            </span>
            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider">{{ number_format($grandTotal, 0) }} ETB Total</span>
        </div>
        <div class="h-[190px] relative flex items-center justify-center">
            <canvas id="ledgerCostChart"></canvas>
        </div>
    </div>
</div>

<!-- Two Column: Ledger History Table & Customer Profile Side Card -->
<div class="grid grid-cols-1 lg:grid-cols-[2.2fr_1fr] gap-5 mb-5 items-start">
    <!-- Main Billing Ledger History Table -->
    <div class="gsap-section-card bg-white border border-slate-200 rounded-xl shadow-card overflow-hidden">
        <div class="h-1 bg-emerald-600"></div>
        <div class="flex items-center justify-between gap-3 px-5 py-3.5 border-b border-slate-100">
            <span class="font-bold text-sm text-slate-900">{{ t('Billing & Meter Reading History') }} — {{ $year }}</span>
            <span class="text-xs text-slate-500">{{ count($ledger) }} {{ t('entries') }}</span>
        </div>

        @if ($ledger->isEmpty())
            <div class="py-10 text-center text-slate-500">
                {!! icon('file-text', 40) !!}
                <div class="mt-2 text-sm font-semibold text-slate-700">{{ t('No billing history found for this customer in') }} {{ $year }}</div>
            </div>
        @else
            <div class="scrollable-table border-0 rounded-none">
                <div class="scroll-progress"><div class="scroll-progress-bar"></div></div>
                <div class="table-scroll-view">
                    <table class="w-full text-[13px]">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold">
                                <th class="text-left px-4 py-3">#</th>
                                <th class="text-left px-4 py-3">{{ t('Month') }}</th>
                                <th class="text-left px-4 py-3">{{ t('Read Date') }}</th>
                                <th class="text-left px-4 py-3">{{ t('Prev R') }}</th>
                                <th class="text-left px-4 py-3">{{ t('Cur R') }}</th>
                                <th class="text-left px-4 py-3">m³</th>
                                <th class="text-left px-4 py-3">{{ t('Water Fee') }}</th>
                                <th class="text-left px-4 py-3">{{ t('Meter') }}</th>
                                <th class="text-left px-4 py-3">{{ t('Svc Fee') }}</th>
                                <th class="text-left px-4 py-3">{{ t('Fund') }}</th>
                                <th class="text-left px-4 py-3">{{ t('Total Cost') }}</th>
                                <th class="text-left px-4 py-3">{{ t('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @php
                            $prev = (float) ($customer->start_value ?? 0);
                            $idx = 0;
                        @endphp
                        @foreach ($ledger as $row)
                            @php
                                $idx++;
                                $cur = (float) ($row->current_reading ?? 0);
                                $use = max(0, $cur - $prev);
                            @endphp
                            <tr class="border-b border-slate-100 odd:bg-white even:bg-slate-50/40 hover:bg-emerald-50/60 transition-colors">
                                <td class="px-4 py-2.5 text-slate-500">{{ $idx }}</td>
                                <td class="px-4 py-2.5"><strong class="text-slate-900">{{ $row->bill_month }}</strong></td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $row->reading_date ?? '—' }}</td>
                                <td class="px-4 py-2.5 text-slate-600 font-mono tabular-nums">{{ number_format($prev, 1) }}</td>
                                <td class="px-4 py-2.5 text-slate-600 font-mono tabular-nums">{{ number_format($cur, 1) }}</td>
                                <td class="px-4 py-2.5"><strong class="text-emerald-700 font-mono tabular-nums">{{ number_format($use, 1) }}</strong></td>
                                <td class="px-4 py-2.5 text-slate-600 font-mono tabular-nums">{{ number_format($row->consumption_cost, 0) }}</td>
                                <td class="px-4 py-2.5 text-slate-600 font-mono tabular-nums">{{ number_format($row->meter_price, 0) }}</td>
                                <td class="px-4 py-2.5 text-slate-600 font-mono tabular-nums">{{ number_format($row->service_price, 0) }}</td>
                                <td class="px-4 py-2.5 text-slate-600 font-mono tabular-nums">{{ number_format($row->state_price, 0) }}</td>
                                <td class="px-4 py-2.5"><strong class="text-slate-900 font-mono tabular-nums">{{ number_format($row->total_monthly_cost, 0) }} ETB</strong></td>
                                <td class="px-4 py-2.5">
                                    @if ($row->payment_status === 'Paid')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider">{!! icon('check', 11) !!} {{ t('Paid') }}</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 text-rose-800 border border-rose-300 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider">{!! icon('x', 11) !!} {{ t('Unpaid') }}</span>
                                    @endif
                                </td>
                            </tr>
                            @php $prev = $cur; @endphp
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-50 font-bold">
                                <td colspan="10" class="px-4 py-3 text-right text-slate-700">{{ t('Annual Grand Total') }}:</td>
                                <td class="px-4 py-3 text-emerald-700 text-sm font-mono tabular-nums">{{ number_format($grandTotal, 0) }} ETB</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- Customer Details & Quick Actions -->
    <div class="flex flex-col gap-4">
        <!-- Customer Profile Card -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-card overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 font-bold text-[13px] text-slate-900 flex items-center gap-2">
                <span class="text-emerald-600">{!! icon('user', 15) !!}</span> {{ t('Customer Profile') }}
            </div>
            <div class="p-4">
                <div class="flex flex-col gap-3">
                    <div>
                        <div class="text-[11px] uppercase tracking-widest font-bold text-slate-500">{{ t('Meter Serial Code') }}</div>
                        <div class="font-mono text-base font-extrabold text-emerald-700 mt-0.5">{{ $customer->meter_serial }}</div>
                    </div>
                    <div>
                        <div class="text-[11px] uppercase tracking-widest font-bold text-slate-500">{{ t('Full Name') }}</div>
                        <div class="text-sm font-bold text-slate-900 mt-0.5">{{ trim(($customer->first_name ?? '').' '.($customer->middle_name ?? '').' '.($customer->last_name ?? '')) }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-2.5 border-t border-slate-100 pt-2.5">
                        <div>
                            <div class="text-[11px] text-slate-500 font-semibold">{{ t('Kebele') }}</div>
                            <div class="text-[13px] font-bold text-slate-900">{{ $customer->kebele ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-[11px] text-slate-500 font-semibold">{{ t('Phone') }}</div>
                            <div class="text-[13px] font-bold text-slate-900">{{ $customer->phone_number ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-[11px] text-slate-500 font-semibold">{{ t('Meter Size') }}</div>
                            <div class="text-[13px] font-bold text-slate-900">{{ $customer->meter_size ?? '1/2"' }}</div>
                        </div>
                        <div>
                            <div class="text-[11px] text-slate-500 font-semibold">{{ t('Status') }}</div>
                            <div class="mt-0.5">
                                @if ($customer->customer_status === 'Active')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider">{!! icon('check', 11) !!} {{ t('Active') }}</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 text-rose-800 border border-rose-300 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider">{!! icon('x', 11) !!} {{ $customer->customer_status }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operational Quick Actions -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-card overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 font-bold text-[13px] text-slate-900 flex items-center gap-2">
                <span class="text-amber-600">{!! icon('wrench', 15) !!}</span> {{ t('Operational Quick Actions') }}
            </div>
            <div class="p-4">
                <div class="mb-2.5">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Update Start Reading (m³)') }}</label>
                    <div class="flex gap-1.5">
                        <input type="number" step="0.01" id="startReading" value="{{ $customer->start_value }}" class="flex-1 px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                        <button type="button" onclick="updateStartReading()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm">{!! icon('check', 14) !!}</button>
                    </div>
                </div>
                <button type="button" onclick="disconnectCustomer()" class="w-full mt-2 inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition">
                    {!! icon('x', 14) !!} {{ t('Disconnect Customer (DC)') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Collapsible Floating Quick Action Menu (FAB) -->
<div class="fab-wrapper">
    <div class="fab-menu" id="fabMenu">
        <button type="button" class="fab-item inline-flex items-center gap-2.5 px-4 py-2.5 rounded-full bg-slate-900 text-white text-[13px] font-semibold border border-white/15 shadow-lg hover:bg-emerald-700 transition" onclick="window.print(); toggleFabMenu();">
            {!! icon('print', 16) !!} <span>{{ t('Print Statement') }}</span>
        </button>
        <button type="button" class="fab-item inline-flex items-center gap-2.5 px-4 py-2.5 rounded-full bg-slate-900 text-white text-[13px] font-semibold border border-white/15 shadow-lg hover:bg-emerald-700 transition" onclick="exportLedger(); toggleFabMenu();">
            {!! icon('download', 16) !!} <span>{{ t('Export Ledger CSV') }}</span>
        </button>
    </div>
    <button type="button" class="fab-trigger-btn w-14 h-14 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white shadow-[0_8px_24px_rgba(5,150,105,0.45)] flex items-center justify-center transition" onclick="toggleFabMenu()" title="Quick Actions">
        {!! icon('plus', 22) !!}
    </button>
</div>

<script>
function toggleFabMenu() {
    const wrapper = document.querySelector('.fab-wrapper');
    if (wrapper) wrapper.classList.toggle('open');
}

function updateStartReading() {
    const code = {{ json_encode($meterSerial) }};
    const val = document.getElementById('startReading').value;
    fetch(apiUrl(`active_customers/update-start-reading`) + `?meterSerial=${encodeURIComponent(code)}&startValue=${val}`, {method:'PUT'})
      .then(r => r.text())
      .then(() => showToast('Start reading updated successfully', 'success'));
}

function disconnectCustomer() {
    const code = {{ json_encode($meterSerial) }};
    confirmDialog(
        'Disconnect customer?',
        'Customer ' + code + ' will be marked as DC (Disconnected).',
        'danger'
    ).then(ok => {
        if (!ok) return;
        fetch(apiUrl(`active_customers/update-single-customer-status`) + `?meterSerial=${encodeURIComponent(code)}&customerStatus=DC`, {method:'PUT'})
          .then(r => r.text())
          .then(() => {
              showToast('Customer disconnected', 'info');
              setTimeout(() => location.reload(), 1000);
          });
    });
}

function exportLedger() {
    const code = {{ json_encode($meterSerial) }};
    const year = {{ json_encode((string)$year) }};
    window.location.href = `{{ route('export.ledger') }}?meterSerial=${encodeURIComponent(code)}&year=${encodeURIComponent(year)}`;
}

function filterCustomerOptions() {
    const query = document.getElementById('customerSearch').value.toLowerCase().trim();
    document.querySelectorAll('#customerSelect option').forEach(opt => {
        if (!opt.value) return opt.hidden = false;
        const text = opt.textContent.toLowerCase();
        opt.hidden = query && !text.includes(query);
    });
}

(function initLedgerCharts() {
    const run = () => {
        if (typeof Chart === 'undefined') return;

        @php
            $monthsList = ['Amajjii', 'Guraandhala', 'Bitootessa', 'Elba', 'Caamsaa', 'Waxabajjii', 'Adooleessa', 'Hagayya', 'Fuulbaana', 'Onkololeessa', 'Sadaasa', 'Muddee'];
            $consData = [];
            $costData = [];
            foreach ($ledger as $r) {
                $consData[] = (float) $r->consumption;
                $costData[] = (float) $r->total_monthly_cost;
            }
        @endphp

        const consCtx = document.getElementById('ledgerConsChart');
        if (consCtx) {
            const oldChart = Chart.getChart(consCtx);
            if (oldChart) oldChart.destroy();
            new Chart(consCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($ledger->pluck('bill_month')->toArray()) !!},
                    datasets: [{
                        label: 'Consumption (m³)',
                        data: {!! json_encode($consData) !!},
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.15)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { grid: { color: 'rgba(15,23,42,0.05)' }, ticks: { font: { size: 10 } } },
                        x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                    }
                }
            });
        }

        const costCtx = document.getElementById('ledgerCostChart');
        if (costCtx) {
            const oldCost = Chart.getChart(costCtx);
            if (oldCost) oldCost.destroy();
            new Chart(costCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($ledger->pluck('bill_month')->toArray()) !!},
                    datasets: [{
                        label: 'Billed Cost (ETB)',
                        data: {!! json_encode($costData) !!},
                        backgroundColor: '#059669',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { grid: { color: 'rgba(15,23,42,0.05)' }, ticks: { font: { size: 10 } } },
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
@endif
@endsection
