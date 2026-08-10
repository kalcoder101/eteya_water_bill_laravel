@extends('layouts.app')

@section('content')

<!-- Page Header Banner -->
<div class="gsap-hero flex flex-wrap items-end justify-between gap-4 mb-6">
    <div class="min-w-0">
        <h2 class="m-0 text-[22px] font-bold tracking-tight text-slate-900 flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-700 inline-flex items-center justify-center shrink-0">{!! icon('receipt', 20) !!}</span>
            <span>{{ t('Bills & Printing Management') }}</span>
        </h2>
        <p class="mt-2 text-[13px] text-slate-500">
            {{ t('Generate, review, calculate and print customer water utility bills') }}
            <span class="badge badge-primary ml-1" style="font-weight: 700;">{{ $year }} {{ $month }}</span>
        </p>
    </div>
    <div class="flex flex-wrap items-end gap-3">
        <form method="get" action="" class="period-picker flex flex-wrap gap-3 items-end bg-white p-3 border border-slate-200 rounded-lg">
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ t('Year') }}:</label>
                <select name="year" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500" onchange="this.form.submit()">
                    @foreach ($years as $y)
                        <option value="{{ $y }}" @if((string)$y===(string)$year) selected @endif>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ t('Month') }}:</label>
                <select name="month" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500" onchange="this.form.submit()">
                    @foreach ($months as $m)
                        <option value="{{ $m }}" @if($m===$month) selected @endif>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

<!-- KPI Stat Cards Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="gsap-stat-card gsap-hover-card flex items-center gap-4 p-4 bg-white border border-slate-200 rounded-xl shadow-card">
        <div class="w-11 h-11 rounded-lg flex items-center justify-center text-white bg-emerald-600 shrink-0">{!! icon('receipt', 20) !!}</div>
        <div class="min-w-0">
            <div class="text-[11px] uppercase tracking-wider text-slate-500 font-bold">{{ t('Total Bills Generated') }}</div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-[22px] font-bold text-slate-900 font-mono tabular-nums" data-gsap-counter data-target-val="{{ count($bills) }}">{{ number_format(count($bills)) }}</span>
            </div>
            <div class="text-[11px] text-slate-500 mt-0.5">{{ number_format($totalAmount, 0) }} ETB {{ t('Total Billed') }}</div>
        </div>
    </div>

    <div class="gsap-stat-card gsap-hover-card flex items-center gap-4 p-4 bg-white border border-slate-200 rounded-xl shadow-card">
        <div class="w-11 h-11 rounded-lg flex items-center justify-center text-white bg-emerald-500 shrink-0">{!! icon('check', 20) !!}</div>
        <div class="min-w-0">
            <div class="text-[11px] uppercase tracking-wider text-slate-500 font-bold">{{ t('Paid Revenue') }}</div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-[22px] font-bold text-emerald-600 font-mono tabular-nums" data-gsap-counter data-target-val="{{ $paidAmount }}">{{ number_format($paidAmount, 0) }}</span>
                <span class="text-xs font-bold text-slate-400">ETB</span>
            </div>
            <div class="text-[11px] text-slate-500 mt-0.5">
                {{ $paidCount }} {{ t('Paid Accounts') }} ({{ count($bills) > 0 ? number_format(($paidCount/count($bills))*100, 1) : 0 }}%)
            </div>
        </div>
    </div>

    <div class="gsap-stat-card gsap-hover-card flex items-center gap-4 p-4 bg-white border border-slate-200 rounded-xl shadow-card">
        <div class="w-11 h-11 rounded-lg flex items-center justify-center text-white bg-rose-600 shrink-0">{!! icon('alert', 20) !!}</div>
        <div class="min-w-0">
            <div class="text-[11px] uppercase tracking-wider text-slate-500 font-bold">{{ t('Unpaid Balance') }}</div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-[22px] font-bold text-rose-600 font-mono tabular-nums" data-gsap-counter data-target-val="{{ $unpaidAmount }}">{{ number_format($unpaidAmount, 0) }}</span>
                <span class="text-xs font-bold text-slate-400">ETB</span>
            </div>
            <div class="text-[11px] text-slate-500 mt-0.5">{{ $unpaidCount }} {{ t('Pending Accounts') }}</div>
        </div>
    </div>

    <div class="gsap-stat-card gsap-hover-card flex items-center gap-4 p-4 bg-white border border-slate-200 rounded-xl shadow-card">
        <div class="w-11 h-11 rounded-lg flex items-center justify-center text-white bg-amber-500 shrink-0">{!! icon('water', 20) !!}</div>
        <div class="min-w-0">
            <div class="text-[11px] uppercase tracking-wider text-slate-500 font-bold">{{ t('Average Bill') }}</div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-[22px] font-bold text-amber-600 font-mono tabular-nums" data-gsap-counter data-target-val="{{ count($bills) > 0 ? intval($totalAmount / count($bills)) : 0 }}">{{ number_format(count($bills) > 0 ? $totalAmount / count($bills) : 0, 0) }}</span>
                <span class="text-xs font-bold text-slate-400">ETB</span>
            </div>
            <div class="text-[11px] text-slate-500 mt-0.5">{{ t('Per customer account') }}</div>
        </div>
    </div>
</div>

<!-- EOS Chart.js Analytics Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    <div class="gsap-chart-card bg-white border border-slate-200 rounded-xl shadow-card p-5">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <span class="font-serif font-bold text-slate-900 text-sm flex items-center gap-2">
                <span class="text-emerald-600">{!! icon('pie-chart', 16) !!}</span> {{ t('Payment Status Revenue') }}
            </span>
            <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-600 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider border border-slate-200">{{ number_format($totalAmount, 0) }} ETB</span>
        </div>
        <div class="chart-wrapper-md h-[190px] relative flex items-center justify-center" style="min-height: 190px;">
            <canvas id="billingStatusChart"></canvas>
        </div>
    </div>

    <div class="gsap-chart-card bg-white border border-slate-200 rounded-xl shadow-card p-5">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <span class="font-serif font-bold text-slate-900 text-sm flex items-center gap-2">
                <span class="text-emerald-600">{!! icon('line-chart', 16) !!}</span> {{ t('Cost Components Breakdown') }}
            </span>
            <span class="inline-flex items-center rounded-full bg-emerald-50 text-emerald-800 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider border border-emerald-300">{{ $year }} {{ $month }}</span>
        </div>
        <div class="chart-wrapper-md h-[190px] relative flex items-center justify-center" style="min-height: 190px;">
            <canvas id="billingComponentsChart"></canvas>
        </div>
    </div>
</div>

<!-- Toolbar & Segmented Filter Bar -->
<div class="bg-white border border-slate-200 rounded-xl shadow-card p-4 mb-6 flex flex-wrap items-center gap-3">
    <div class="segmented">
        <button class="btn btn-sm active" id="btn-filter-all" onclick="filterBillsTable('all')">{{ t('All Bills') }} <span class="badge badge-secondary">{{ count($bills) }}</span></button>
        <button class="btn btn-sm" id="btn-filter-paid" onclick="filterBillsTable('Paid')">{!! icon('check', 12) !!} {{ t('Paid') }} <span class="badge badge-success">{{ $paidCount }}</span></button>
        <button class="btn btn-sm" id="btn-filter-unpaid" onclick="filterBillsTable('Unpaid')">{!! icon('alert', 12) !!} {{ t('Unpaid') }} <span class="badge badge-danger">{{ $unpaidCount }}</span></button>
    </div>

    <div class="relative flex-1 min-w-[220px] max-w-[360px]">
        <input type="text" id="billSearchInput" class="w-full pl-9 pr-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder:text-slate-400" placeholder="{{ t('Search customer code, name...') }}" onkeyup="searchBillsTable()">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">{!! icon('search', 14) !!}</span>
    </div>

    <div class="flex gap-2.5 items-center ml-auto">
        <x-button variant="secondary" icon="download" type="button" onclick="exportBillsCSV()">
            {{ t('Export CSV') }}
        </x-button>
        <x-button variant="primary" icon="print" type="button" onclick="printAllBills()">
            {{ t('Batch Print Receipts') }}
        </x-button>
    </div>
</div>

<!-- Bills Registry Data Table -->
<div class="gsap-section-card bg-white border border-slate-200 rounded-xl shadow-card overflow-hidden relative">
    <div class="h-1 bg-emerald-600"></div>
    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5 border-b border-slate-100">
        <span class="font-bold text-sm text-slate-900">
            {{ t('Generated Water Bills') }} — {{ $year }} {{ $month }}
            <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-600 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider border border-slate-200 ml-2">{{ count($bills) }} {{ t('records') }}</span>
        </span>
        <span class="text-xs text-slate-500">
            Paid: <strong class="text-emerald-700 font-mono tabular-nums">{{ number_format($paidAmount, 0) }} ETB</strong> &bull; Unpaid: <strong class="text-rose-600 font-mono tabular-nums">{{ number_format($unpaidAmount, 0) }} ETB</strong>
        </span>
    </div>

    <div class="scrollable-table border-0 rounded-none">
        <div class="scroll-progress"><div class="scroll-progress-bar"></div></div>
        <div class="table-scroll-view">
            <table class="w-full text-[13px]" id="billsTable">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold">
                        <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Code') }}</th>
                        <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Customer Name') }}</th>
                        <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Cons. (m³)') }}</th>
                        <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Water Fee') }}</th>
                        <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Meter') }}</th>
                        <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Svc Fee') }}</th>
                        <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Penalty') }}</th>
                        <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Fund') }}</th>
                        <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Total Cost') }}</th>
                        <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Status') }}</th>
                        <th class="text-right px-4 py-3 whitespace-nowrap">{{ t('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($bills as $b)
                    <tr data-status="{{ $b->payment_status }}" data-search="{{ strtolower($b->meter_serial.' '.($b->customer ? trim(($b->customer->first_name ?? '').' '.($b->customer->middle_name ?? '').' '.($b->customer->last_name ?? '')) : $b->full_name)) }}" class="border-b border-slate-100 odd:bg-white even:bg-slate-50/40 hover:bg-emerald-50/60 transition-colors">
                        <td class="px-4 py-2.5 text-slate-700 align-middle"><span class="inline-block font-mono font-bold text-[12.5px] text-emerald-700 bg-emerald-50 px-2 py-1 rounded-md border border-emerald-200">{{ $b->meter_serial }}</span></td>
                        <td class="px-4 py-2.5 text-slate-700 align-middle">
                            <div class="font-bold text-slate-900 text-[13px]">{{ $b->customer ? trim(($b->customer->first_name ?? '').' '.($b->customer->middle_name ?? '').' '.($b->customer->last_name ?? '')) : $b->full_name }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">Kebele: {{ $b->customer->kebele ?? '01' }}</div>
                        </td>
                        <td class="px-4 py-2.5 text-slate-700 align-middle font-mono tabular-nums"><strong>{{ number_format($b->consumption, 1) }}</strong></td>
                        <td class="px-4 py-2.5 text-slate-700 align-middle font-mono tabular-nums">{{ number_format($b->consumption_cost, 0) }}</td>
                        <td class="px-4 py-2.5 text-slate-700 align-middle font-mono tabular-nums">{{ number_format($b->meter_price, 0) }}</td>
                        <td class="px-4 py-2.5 text-slate-700 align-middle font-mono tabular-nums">{{ number_format($b->service_price, 0) }}</td>
                        <td class="px-4 py-2.5 text-slate-700 align-middle font-mono tabular-nums">{{ number_format($b->penalty_cost, 0) }}</td>
                        <td class="px-4 py-2.5 text-slate-700 align-middle font-mono tabular-nums">{{ number_format($b->state_price, 0) }}</td>
                        <td class="px-4 py-2.5 text-slate-700 align-middle"><strong class="text-emerald-700 text-[13.5px] font-mono tabular-nums">{{ number_format($b->total_monthly_cost, 0) }} ETB</strong></td>
                        <td class="px-4 py-2.5 text-slate-700 align-middle">
                            @if ($b->payment_status === 'Paid')
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider">{!! icon('check', 11) !!} {{ t('Paid') }}</span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 text-rose-800 border border-rose-300 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider">{!! icon('x', 11) !!} {{ t('Unpaid') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-slate-700 align-middle">
                            <div class="flex items-center justify-end gap-2">
                                <x-button variant="primary" size="sm" icon="print" type="button" onclick='previewReceipt({{ json_encode($b) }})' :title="t('Print Receipt')">
                                    {{ t('Print') }}
                                </x-button>
                                @if ($b->payment_status !== 'Paid')
                                    <x-button variant="primary" size="sm" icon="check" :href="route('bills.mark-paid', ['id' => $b->bill_finance_id])" :title="t('Mark Paid')">
                                        {{ t('Pay') }}
                                    </x-button>
                                @endif
                                <x-button variant="secondary" size="sm" icon="book-open" :href="route('customer-ledger.index').'?meterSerial='.urlencode($b->meter_serial)" :title="t('View Financial Ledger')" />
                            </div>
                        </td>
                    </tr>
                @endforeach
                @if ($bills->isEmpty())
                    <tr>
                        <td colspan="11" class="text-center px-6 py-10 text-slate-500 text-[13px]">{{ t('No bill records generated for this month. Click "Calculate Bills" to generate.') }}</td>
                    </tr>
                @endif
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-bold border-t border-slate-200">
                        <td colspan="8" class="text-right px-4 py-3 text-slate-700">{{ t('Total Period Monthly Cost') }}:</td>
                        <td class="px-4 py-3 text-emerald-700 text-sm font-mono tabular-nums">{{ number_format($totalAmount, 0) }} ETB</td>
                        <td colspan="2" class="px-4 py-3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Collapsible Floating Quick Action Menu (FAB) -->
<div class="fab-wrapper">
    <div class="fab-menu" id="fabMenu">
        <button type="button" class="fab-item flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-white border border-slate-200 shadow-lg text-[12.5px] font-bold text-slate-700 hover:bg-emerald-50 hover:text-emerald-900 transition" onclick="calculateBills(); toggleFabMenu();">
            <span class="icon text-emerald-600">{!! icon('zap', 16) !!}</span>
            <span class="label">{{ t('Calculate Period Bills') }}</span>
        </button>
        <button type="button" class="fab-item flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-white border border-slate-200 shadow-lg text-[12.5px] font-bold text-slate-700 hover:bg-emerald-50 hover:text-emerald-900 transition" onclick="printAllBills(); toggleFabMenu();">
            <span class="icon text-emerald-600">{!! icon('print', 16) !!}</span>
            <span class="label">{{ t('Batch Print Receipts') }}</span>
        </button>
        <button type="button" class="fab-item flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-white border border-slate-200 shadow-lg text-[12.5px] font-bold text-slate-700 hover:bg-emerald-50 hover:text-emerald-900 transition" onclick="exportBillsCSV(); toggleFabMenu();">
            <span class="icon text-emerald-600">{!! icon('download', 16) !!}</span>
            <span class="label">{{ t('Export Bills CSV') }}</span>
        </button>
    </div>
    <button type="button" class="fab-trigger-btn w-14 h-14 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-[0_8px_20px_rgba(5,150,105,0.4)] flex items-center justify-center cursor-pointer transition" onclick="toggleFabMenu()" title="Quick Actions">
        <span class="fab-icon-main">{!! icon('plus', 22) !!}</span>
    </button>
</div>

<!-- Quick Receipt Print Preview Modal -->
<div class="modal-backdrop v2" id="receiptModal">
    <div class="modal v2 bg-white rounded-2xl shadow-2xl w-full max-w-[540px] max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-gradient-to-br from-slate-50 to-white">
            <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">{!! icon('print', 20) !!}</div>
            <div class="flex-1 min-w-0">
                <h3 class="m-0 text-base font-bold text-slate-900">{{ t('Water Utility Receipt Preview') }}</h3>
                <div class="text-xs text-slate-500 mt-0.5">{{ t('Official Customer Payment Voucher') }} &bull; <span id="receiptSerial"></span></div>
            </div>
            <button class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition" onclick="closeModal('receiptModal')">&times;</button>
        </div>
        <div class="p-5 overflow-y-auto">
            <div id="receiptPrintArea" class="bg-white text-slate-900 p-4 rounded-lg border border-slate-200 font-mono text-[11px] leading-relaxed">
                <div class="text-center border-b-2 border-dashed border-slate-800 pb-3 mb-3">
                    <div class="font-extrabold text-[13px]">HHD WATER SUPPLY & SEWERAGE</div>
                    <div class="text-[10.5px]">WATERSTEWARD ENTERPRISE</div>
                    <div class="text-[10px] mt-1">Period: {{ $year }} {{ $month }}</div>
                </div>

                <div class="flex justify-between mb-1.5">
                    <span>Meter Code: <strong id="rCode"></strong></span>
                    <span>Status: <strong id="rStatus"></strong></span>
                </div>
                <div class="mb-3 font-bold border-b border-slate-200 pb-1.5">
                    Customer Name: <span id="rName"></span>
                </div>

                <table class="w-full border-collapse mb-3 text-[11px]">
                    <tr class="border-b border-slate-300">
                        <th class="text-left py-1">Item Description</th>
                        <th class="text-right py-1">Amount (ETB)</th>
                    </tr>
                    <tr><td class="py-0.5">Consumption (<span id="rCons"></span> m³)</td><td class="text-right" id="rCost"></td></tr>
                    <tr><td class="py-0.5">Meter Rent Fee</td><td class="text-right" id="rMeter"></td></tr>
                    <tr><td class="py-0.5">Service Fee</td><td class="text-right" id="rSvc"></td></tr>
                    <tr><td class="py-0.5">Penalties / Fine</td><td class="text-right" id="rPen"></td></tr>
                    <tr><td class="py-0.5">Community Water Fund</td><td class="text-right" id="rFund"></td></tr>
                    <tr class="border-t-2 border-slate-800 font-extrabold text-[12px]">
                        <td class="pt-2">TOTAL DUE COST</td>
                        <td class="text-right pt-2" id="rTotal"></td>
                    </tr>
                </table>

                <div class="text-[10px] text-center text-slate-500 border-t border-dashed border-slate-400 pt-2">
                    Thank you for using WaterSteward Enterprise. Please retain this receipt.
                </div>
            </div>
        </div>
        <div class="px-5 py-4 border-t border-slate-100 bg-slate-50 flex gap-3 justify-end">
            <x-button variant="secondary" type="button" onclick="closeModal('receiptModal')">
                {{ t('Close') }}
            </x-button>
            <x-button variant="primary" icon="print" type="button" onclick="printSingleReceipt()">
                {{ t('Print Receipt') }}
            </x-button>
        </div>
    </div>
</div>

<script>
let currentReceiptId = null;

function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }

function toggleFabMenu() {
    const wrapper = document.querySelector('.fab-wrapper');
    if (wrapper) wrapper.classList.toggle('open');
}

function calculateBills() {
    confirmDialog(
        '{{ t('Calculate Bills') }}',
        '{{ t('Generate bill records for all active customers based on readings for') }} {{ $year }} {{ $month }}?',
        'warning'
    ).then(ok => {
        if (!ok) return;
        fetch(`{{ route('bills.calculate') }}?year=${encodeURIComponent('{{ $year }}')}&month=${encodeURIComponent('{{ $month }}')}`)
          .then(r => r.json())
          .then(d => {
              if (d.error) { showToast(d.error, 'error'); return; }
              showToast(`Calculated ${d.created} new, ${d.updated} updated bills`, 'success');
              setTimeout(() => location.reload(), 1200);
          })
          .catch(e => showToast('Failed: ' + e.message, 'error'));
    });
}

function exportBillsCSV() {
    window.location.href = `{{ route('export.bills') }}?year={{ $year }}&month=${encodeURIComponent('{{ $month }}')}`;
}

function filterBillsTable(status) {
    document.querySelectorAll('.segmented .btn').forEach(b => b.classList.remove('active'));
    if (status === 'all') document.getElementById('btn-filter-all').classList.add('active');
    if (status === 'Paid') document.getElementById('btn-filter-paid').classList.add('active');
    if (status === 'Unpaid') document.getElementById('btn-filter-unpaid').classList.add('active');

    document.querySelectorAll('#billsTable tbody tr').forEach(tr => {
        if (status === 'all' || tr.getAttribute('data-status') === status) {
            tr.style.display = '';
        } else {
            tr.style.display = 'none';
        }
    });
}

function searchBillsTable() {
    const query = document.getElementById('billSearchInput').value.toLowerCase().trim();
    document.querySelectorAll('#billsTable tbody tr').forEach(tr => {
        const text = tr.getAttribute('data-search') || '';
        if (text.includes(query)) {
            tr.style.display = '';
        } else {
            tr.style.display = 'none';
        }
    });
}

function previewReceipt(b) {
    currentReceiptId = b.bill_finance_id;
    document.getElementById('receiptSerial').textContent = b.meter_serial;
    document.getElementById('rCode').textContent = b.meter_serial;
    document.getElementById('rName').textContent = b.customer ? (b.customer.first_name + ' ' + (b.customer.middle_name||'') + ' ' + (b.customer.last_name||'')) : b.full_name;
    document.getElementById('rStatus').textContent = b.payment_status;
    document.getElementById('rCons').textContent = parseFloat(b.consumption || 0).toFixed(1);
    document.getElementById('rCost').textContent = parseFloat(b.consumption_cost || 0).toFixed(0);
    document.getElementById('rMeter').textContent = parseFloat(b.meter_price || 0).toFixed(0);
    document.getElementById('rSvc').textContent = parseFloat(b.service_price || 0).toFixed(0);
    document.getElementById('rPen').textContent = parseFloat(b.penalty_cost || 0).toFixed(0);
    document.getElementById('rFund').textContent = parseFloat(b.state_price || 0).toFixed(0);
    document.getElementById('rTotal').textContent = parseFloat(b.total_monthly_cost || 0).toFixed(0) + ' ETB';

    openModal('receiptModal');
}

function printSingleReceipt() {
    if (!currentReceiptId) return;
    window.open(`{{ $baseUrl }}/bills/print/${encodeURIComponent(currentReceiptId)}`, '_blank');
}

function printAllBills() {
    window.print();
}

(function initBillingCharts() {
    const run = () => {
        if (typeof Chart === 'undefined') return;

        // Payment Revenue Status Doughnut Chart
        const statusCtx = document.getElementById('billingStatusChart');
        if (statusCtx) {
            const oldChart = Chart.getChart(statusCtx);
            if (oldChart) oldChart.destroy();
            new Chart(statusCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Paid Revenue', 'Unpaid Outstanding'],
                    datasets: [{
                        data: [{{ $paidAmount }}, {{ $unpaidAmount }}],
                        backgroundColor: ['#10B981', '#EF4444'],
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

        // Cost Components Line Chart
        const compCtx = document.getElementById('billingComponentsChart');
        if (compCtx) {
            const oldComp = Chart.getChart(compCtx);
            if (oldComp) oldComp.destroy();

            const ctxComp = compCtx.getContext('2d');
            const gradComp = ctxComp.createLinearGradient(0, 0, 0, 180);
            gradComp.addColorStop(0, 'rgba(5, 150, 105, 0.35)');
            gradComp.addColorStop(1, 'rgba(5, 150, 105, 0.01)');

            new Chart(ctxComp, {
                type: 'line',
                data: {
                    labels: ['Water Cons.', 'Meter Fee', 'Service Fee', 'Penalty', 'Fund'],
                    datasets: [{
                        label: 'Cost (ETB)',
                        data: [
                            {{ $bills->sum('consumption_cost') }},
                            {{ $bills->sum('meter_price') }},
                            {{ $bills->sum('service_price') }},
                            {{ $bills->sum('penalty_cost') }},
                            {{ $bills->sum('state_price') }}
                        ],
                        borderColor: '#059669',
                        backgroundColor: '#059669',
                        borderWidth: 3,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#059669',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { grid: { color: 'rgba(15,23,42,0.05)' }, ticks: { font: { size: 10, family: 'Inter' } } },
                        x: { grid: { display: false }, ticks: { font: { size: 10, family: 'Inter' } } }
                    }
                }
            });
        }
    };

    let retries = 0;
    const safeRun = () => {
        const testCanvas = document.getElementById('billingStatusChart') || document.getElementById('billingComponentsChart');
        if (!testCanvas || typeof Chart === 'undefined') return;

        if (testCanvas.parentElement && testCanvas.parentElement.clientHeight === 0 && retries < 10) {
            retries++;
            setTimeout(safeRun, 50);
            return;
        }

        run();
    };

    if (document.readyState === 'complete') {
        safeRun();
    } else {
        document.addEventListener('DOMContentLoaded', safeRun);
        window.addEventListener('load', safeRun);
    }
    window.addEventListener('resize', safeRun);
})();
</script>
@endsection
