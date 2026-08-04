@extends('layouts.app')

@section('content')

<!-- Page Header Banner -->
<div class="page-header-block gsap-hero">
    <div class="page-info">
        <div style="font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">
            {{ t('Financial Operations') }} &bull; {{ t('Billing') }}
        </div>
        <h2 style="margin: 0; display: flex; align-items: center; gap: 10px;">
            {!! icon('receipt', 24) !!} {{ t('Bills & Printing Management') }}
        </h2>
        <p style="margin-top: 4px; color: var(--text-muted);">
            {{ t('Generate, review, calculate and print customer water utility bills') }} &bull;
            <span class="badge badge-primary" style="font-weight: 700;">{{ $year }} {{ $month }}</span>
        </p>
    </div>
    <div class="page-actions">
        <form method="get" action="" class="period-picker" style="display:flex; gap:10px; align-items:center; background:var(--surface-container-low); padding:6px 12px; border-radius:var(--r-lg); border:1px solid var(--outline-variant);">
            <div style="display:flex; align-items:center; gap:6px;">
                <label style="font-size:12px; font-weight:700; color:var(--text-muted);">{{ t('Year') }}:</label>
                <select name="year" class="fancy" onchange="this.form.submit()" style="padding:4px 8px; font-size:12.5px;">
                    @foreach ($years as $y)
                        <option value="{{ $y }}" @if((string)$y===(string)$year) selected @endif>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex; align-items:center; gap:6px;">
                <label style="font-size:12px; font-weight:700; color:var(--text-muted);">{{ t('Month') }}:</label>
                <select name="month" class="fancy" onchange="this.form.submit()" style="padding:4px 8px; font-size:12.5px;">
                    @foreach ($months as $m)
                        <option value="{{ $m }}" @if($m===$month) selected @endif>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
        </form>
        <button class="btn btn-primary" onclick="calculateBills()" style="box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);">
            {!! icon('zap', 16) !!} {{ t('Calculate Bills') }}
        </button>
    </div>
</div>

<!-- KPI Stat Cards Grid -->
<div class="card-grid cols-4" style="margin-bottom: 20px;">
    <div class="stat-card accent gsap-stat-card gsap-hover-card">
        <div class="stat-label">{{ t('Total Bills Generated') }}</div>
        <div class="stat-value" data-gsap-counter data-target-val="{{ count($bills) }}">{{ number_format(count($bills)) }}</div>
        <div class="stat-meta">{{ number_format($totalAmount, 0) }} ETB {{ t('Total Billed') }}</div>
    </div>

    <div class="stat-card success gsap-stat-card gsap-hover-card">
        <div class="stat-label">{{ t('Paid Revenue') }}</div>
        <div class="stat-value" style="color: var(--success);" data-gsap-counter data-target-val="{{ $paidAmount }}">{{ number_format($paidAmount, 0) }} <span style="font-size:13px;">ETB</span></div>
        <div class="stat-meta">
            {{ $paidCount }} {{ t('Paid Accounts') }} ({{ count($bills) > 0 ? number_format(($paidCount/count($bills))*100, 1) : 0 }}%)
        </div>
    </div>

    <div class="stat-card danger gsap-stat-card gsap-hover-card">
        <div class="stat-label">{{ t('Unpaid Balance') }}</div>
        <div class="stat-value" style="color: var(--danger);" data-gsap-counter data-target-val="{{ $unpaidAmount }}">{{ number_format($unpaidAmount, 0) }} <span style="font-size:13px;">ETB</span></div>
        <div class="stat-meta">{{ $unpaidCount }} {{ t('Pending Accounts') }}</div>
    </div>

    <div class="stat-card warning gsap-stat-card gsap-hover-card">
        <div class="stat-label">{{ t('Average Bill') }}</div>
        <div class="stat-value" style="color: var(--warning);" data-gsap-counter data-target-val="{{ count($bills) > 0 ? intval($totalAmount / count($bills)) : 0 }}">{{ number_format(count($bills) > 0 ? $totalAmount / count($bills) : 0, 0) }} <span style="font-size:13px;">ETB</span></div>
        <div class="stat-meta">{{ t('Per customer account') }}</div>
    </div>
</div>

<!-- EOS Chart.js Analytics Grid -->
<div class="card-grid cols-2 gsap-chart-card" style="margin-bottom: 20px;">
    <div class="panel" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); padding: 16px;">
        <div style="font-weight: 700; font-size: 13.5px; color: var(--on-surface); margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
            <span style="display: flex; align-items: center; gap: 8px;">{!! icon('pie-chart', 16) !!} {{ t('Payment Status Revenue') }}</span>
            <span class="badge badge-secondary">{{ number_format($totalAmount, 0) }} ETB</span>
        </div>
        <div style="height: 190px; position: relative;">
            <canvas id="billingStatusChart"></canvas>
        </div>
    </div>

    <div class="panel" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); padding: 16px;">
        <div style="font-weight: 700; font-size: 13.5px; color: var(--on-surface); margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
            <span style="display: flex; align-items: center; gap: 8px;">{!! icon('bar-chart', 16) !!} {{ t('Cost Components Breakdown') }}</span>
            <span class="badge badge-success">{{ $year }} {{ $month }}</span>
        </div>
        <div style="height: 190px; position: relative;">
            <canvas id="billingComponentsChart"></canvas>
        </div>
    </div>
</div>

<!-- Toolbar & Segmented Filter Bar -->
<div class="toolbar" style="background: var(--surface-container-lowest); padding: 12px 16px; border-radius: var(--r-lg); border: 1px solid var(--outline-variant); margin-bottom: 20px; gap: 12px;">
    <div class="segmented">
        <button class="btn btn-sm active" id="btn-filter-all" onclick="filterBillsTable('all')">{{ t('All Bills') }} <span class="badge badge-secondary">{{ count($bills) }}</span></button>
        <button class="btn btn-sm" id="btn-filter-paid" onclick="filterBillsTable('Paid')">{!! icon('check', 12) !!} {{ t('Paid') }} <span class="badge badge-success">{{ $paidCount }}</span></button>
        <button class="btn btn-sm" id="btn-filter-unpaid" onclick="filterBillsTable('Unpaid')">{!! icon('alert', 12) !!} {{ t('Unpaid') }} <span class="badge badge-danger">{{ $unpaidCount }}</span></button>
    </div>

    <div style="position: relative; flex: 1; max-width: 360px;">
        <input type="text" id="billSearchInput" class="form-control" placeholder="{{ t('Search customer code, name...') }}" onkeyup="searchBillsTable()" style="padding-left: 32px;">
        <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); opacity: 0.6;">{!! icon('search', 14) !!}</span>
    </div>

    <div style="display: flex; gap: 8px; align-items: center; margin-left: auto;">
        <button class="btn btn-sm" onclick="exportBillsCSV()">{!! icon('download', 14) !!} {{ t('Export CSV') }}</button>
        <button class="btn btn-sm btn-primary" onclick="printAllBills()">{!! icon('print', 14) !!} {{ t('Batch Print Receipts') }}</button>
    </div>
</div>

<!-- Bills Registry Data Table -->
<div class="panel gsap-section-card" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); overflow: hidden; box-shadow: var(--shadow-sm); position: relative;">
    <div style="height: 4px; background: var(--primary-container);"></div>
    <div class="panel-header" style="padding: 14px 20px; background: var(--surface-container-lowest); border-bottom: 1px solid var(--outline-variant); display: flex; justify-content: space-between; align-items: center;">
        <span style="font-weight: 700; font-size: 14px; color: var(--on-surface);">
            {{ t('Generated Water Bills') }} — {{ $year }} {{ $month }}
            <span class="badge badge-secondary" style="margin-left: 8px;">{{ count($bills) }} {{ t('records') }}</span>
        </span>
        <span style="font-size: 12px; color: var(--text-muted);">
            Paid: <strong style="color: var(--success);">{{ number_format($paidAmount, 0) }} ETB</strong> &bull; Unpaid: <strong style="color: var(--danger);">{{ number_format($unpaidAmount, 0) }} ETB</strong>
        </span>
    </div>

    <div class="scrollable-table">
        <div class="scroll-progress"><div class="scroll-progress-bar"></div></div>
        <div class="table-scroll-view">
            <table class="data-table compact" id="billsTable" style="width:100%;">
                <thead>
                    <tr>
                        <th>{{ t('Code') }}</th>
                        <th>{{ t('Customer Name') }}</th>
                        <th>{{ t('Cons. (m³)') }}</th>
                        <th>{{ t('Water Fee') }}</th>
                        <th>{{ t('Meter') }}</th>
                        <th>{{ t('Svc Fee') }}</th>
                        <th>{{ t('Penalty') }}</th>
                        <th>{{ t('Fund') }}</th>
                        <th>{{ t('Total Cost') }}</th>
                        <th>{{ t('Status') }}</th>
                        <th style="text-align: right;">{{ t('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($bills as $b)
                    <tr data-status="{{ $b->payment_status }}" data-search="{{ strtolower($b->meter_serial.' '.($b->customer ? trim(($b->customer->first_name ?? '').' '.($b->customer->middle_name ?? '').' '.($b->customer->last_name ?? '')) : $b->full_name)) }}">
                        <td><span style="font-family: monospace; font-weight: 700; font-size: 12.5px; color: var(--primary); background: var(--surface-container-low); padding: 4px 8px; border-radius: var(--r-sm); border: 1px solid var(--outline-variant);">{{ $b->meter_serial }}</span></td>
                        <td>
                            <div style="font-weight: 700; color: var(--on-surface); font-size: 13px;">{{ $b->customer ? trim(($b->customer->first_name ?? '').' '.($b->customer->middle_name ?? '').' '.($b->customer->last_name ?? '')) : $b->full_name }}</div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 1px;">Kebele: {{ $b->customer->kebele ?? '01' }}</div>
                        </td>
                        <td><strong>{{ number_format($b->consumption, 1) }}</strong></td>
                        <td>{{ number_format($b->consumption_cost, 0) }}</td>
                        <td>{{ number_format($b->meter_price, 0) }}</td>
                        <td>{{ number_format($b->service_price, 0) }}</td>
                        <td>{{ number_format($b->penalty_cost, 0) }}</td>
                        <td>{{ number_format($b->state_price, 0) }}</td>
                        <td><strong style="color: var(--primary); font-size: 13.5px;">{{ number_format($b->total_monthly_cost, 0) }} ETB</strong></td>
                        <td>
                            @if ($b->payment_status === 'Paid')
                                <span class="badge badge-success">{!! icon('check', 12) !!} {{ t('Paid') }}</span>
                            @else
                                <span class="badge badge-danger">{!! icon('x', 12) !!} {{ t('Unpaid') }}</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px;">
                                <button type="button" class="btn btn-sm btn-primary" onclick='previewReceipt({{ json_encode($b) }})' title="{{ t('Print Receipt') }}">{!! icon('print', 14) !!} {{ t('Print') }}</button>
                                @if ($b->payment_status !== 'Paid')
                                    <a href="{{ route('bills.mark-paid', ['id' => $b->bill_finance_id]) }}" class="btn btn-sm btn-success" title="{{ t('Mark Paid') }}">{!! icon('check', 14) !!} {{ t('Pay') }}</a>
                                @endif
                                <a href="{{ route('customer-ledger.index') }}?meterSerial={{ urlencode($b->meter_serial) }}" class="btn btn-sm" title="View Financial Ledger">{!! icon('book-open', 14) !!}</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
                @if ($bills->isEmpty())
                    <tr><td colspan="11" style="text-align:center; padding: 40px; color:var(--text-muted);">{{ t('No bill records generated for this month. Click "Calculate Bills" to generate.') }}</td></tr>
                @endif
                </tbody>
                <tfoot>
                    <tr style="background: var(--surface-container-low); font-weight: 700;">
                        <td colspan="8" style="text-align:right; padding: 12px 14px;">{{ t('Total Period Monthly Cost') }}:</td>
                        <td style="color: var(--primary); font-size: 14px;">{{ number_format($totalAmount, 0) }} ETB</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Collapsible Floating Quick Action Menu (FAB) -->
<div class="fab-wrapper">
    <div class="fab-menu" id="fabMenu">
        <button type="button" class="fab-item" onclick="calculateBills(); toggleFabMenu();">
            <span class="icon">{!! icon('zap', 16) !!}</span>
            <span class="label">{{ t('Calculate Period Bills') }}</span>
        </button>
        <button type="button" class="fab-item" onclick="printAllBills(); toggleFabMenu();">
            <span class="icon">{!! icon('print', 16) !!}</span>
            <span class="label">{{ t('Batch Print Receipts') }}</span>
        </button>
        <button type="button" class="fab-item" onclick="exportBillsCSV(); toggleFabMenu();">
            <span class="icon">{!! icon('download', 16) !!}</span>
            <span class="label">{{ t('Export Bills CSV') }}</span>
        </button>
    </div>
    <button type="button" class="fab-trigger-btn" onclick="toggleFabMenu()" title="Quick Actions">
        <span class="fab-icon-main">{!! icon('plus', 22) !!}</span>
    </button>
</div>

<!-- Quick Receipt Print Preview Modal -->
<div class="modal-backdrop v2" id="receiptModal">
    <div class="modal v2" style="max-width: 600px;">
        <div class="modal-header">
            <div class="modal-icon">{!! icon('print', 20) !!}</div>
            <div class="modal-title">
                <h3>{{ t('Water Utility Receipt Preview') }}</h3>
                <div class="modal-subtitle">{{ t('Official Customer Payment Voucher') }} &bull; <span id="receiptSerial"></span></div>
            </div>
            <button class="close" onclick="closeModal('receiptModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div id="receiptPrintArea" style="background: #fff; color: #111; padding: 20px; border-radius: 8px; border: 1px solid #ddd; font-family: monospace;">
                <div style="text-align: center; border-bottom: 2px dashed #333; padding-bottom: 12px; margin-bottom: 12px;">
                    <div style="font-weight: 800; font-size: 16px;">HHD WATER SUPPLY & SEWERAGE</div>
                    <div style="font-size: 12px;">ETEYA WATER SERVICE ENTERPRISE</div>
                    <div style="font-size: 11px; margin-top: 4px;">Period: {{ $year }} {{ $month }}</div>
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 12.5px;">
                    <span>Meter Code: <strong id="rCode"></strong></span>
                    <span>Status: <strong id="rStatus"></strong></span>
                </div>
                <div style="margin-bottom: 12px; font-size: 13px; font-weight: 700; border-bottom: 1px solid #eee; padding-bottom: 6px;">
                    Customer Name: <span id="rName"></span>
                </div>

                <table style="width: 100%; font-size: 12px; border-collapse: collapse; margin-bottom: 12px;">
                    <tr style="border-bottom: 1px solid #ddd;">
                        <th style="text-align: left; padding: 4px 0;">Item Description</th>
                        <th style="text-align: right; padding: 4px 0;">Amount (ETB)</th>
                    </tr>
                    <tr><td>Consumption (<span id="rCons"></span> m³)</td><td style="text-align: right;" id="rCost"></td></tr>
                    <tr><td>Meter Rent Fee</td><td style="text-align: right;" id="rMeter"></td></tr>
                    <tr><td>Service Fee</td><td style="text-align: right;" id="rSvc"></td></tr>
                    <tr><td>Penalties / Fine</td><td style="text-align: right;" id="rPen"></td></tr>
                    <tr><td>Community Water Fund</td><td style="text-align: right;" id="rFund"></td></tr>
                    <tr style="border-top: 2px solid #111; font-weight: 800; font-size: 14px;">
                        <td style="padding-top: 8px;">TOTAL DUE COST</td>
                        <td style="text-align: right; padding-top: 8px;" id="rTotal"></td>
                    </tr>
                </table>

                <div style="font-size: 10.5px; text-align: center; color: #555; border-top: 1px dashed #aaa; padding-top: 8px;">
                    Thank you for using Eteya Water Enterprise. Please retain this receipt.
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closeModal('receiptModal')">{{ t('Close') }}</button>
            <button class="btn btn-primary" onclick="printSingleReceipt()">{!! icon('print', 16) !!} {{ t('Print Receipt') }}</button>
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

        // Cost Components Bar Chart
        const compCtx = document.getElementById('billingComponentsChart');
        if (compCtx) {
            const oldComp = Chart.getChart(compCtx);
            if (oldComp) oldComp.destroy();
            new Chart(compCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Water Cons.', 'Meter Fee', 'Service Fee', 'Penalty', 'Fund'],
                    datasets: [{
                        label: 'ETB',
                        data: [
                            {{ $bills->sum('consumption_cost') }},
                            {{ $bills->sum('meter_price') }},
                            {{ $bills->sum('service_price') }},
                            {{ $bills->sum('penalty_cost') }},
                            {{ $bills->sum('state_price') }}
                        ],
                        backgroundColor: ['#10B981', '#3B82F6', '#6366F1', '#EF4444', '#F59E0B'],
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 10 } } },
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } }
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
