@extends('layouts.app')

@section('content')

<!-- Page Header & Banner -->
<div class="page-header-block gsap-hero">
    <div class="page-info">
        <div style="font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">
            {{ t('Financial Operations') }} &bull; {{ t('Customer Ledger') }}
        </div>
        <h2 style="margin: 0; display: flex; align-items: center; gap: 10px;">
            {!! icon('book-open', 24) !!} {{ t('Customers Ledger Statement') }}
        </h2>
        <p style="margin-top: 4px; color: var(--text-muted);">
            {{ t('View comprehensive water consumption and billing history by customer and year') }}
            @if ($customer)
                &bull; <span class="badge badge-primary" style="font-weight: 700;">{{ $customer->meter_serial }} — {{ trim(($customer->first_name ?? '').' '.($customer->middle_name ?? '').' '.($customer->last_name ?? '')) }}</span>
            @endif
        </p>
    </div>
    <div class="page-actions">
        @if (! empty($customer))
            <button class="btn" onclick="window.print()">{!! icon('print', 14) !!} {{ t('Print Statement') }}</button>
            <button class="btn btn-primary" onclick="exportLedger()">{!! icon('download', 14) !!} {{ t('Export Ledger CSV') }}</button>
        @endif
    </div>
</div>

<!-- Customer Selection & Search Toolbar -->
<div class="panel" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); padding: 16px; margin-bottom: 20px;">
    <form method="get" action="{{ route('customer-ledger.index') }}" style="display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 2; min-width: 280px;">
            <label style="font-size: 12px; font-weight: 700; color: var(--on-surface); margin-bottom: 4px; display: block;">{{ t('Select Customer') }}</label>
            <input id="customerSearch" class="form-control" type="text" placeholder="{{ t('Type code, name, phone to filter...') }}" oninput="filterCustomerOptions()" style="margin-bottom: 6px;">
            <select id="customerSelect" name="meterSerial" class="fancy" onchange="this.form.submit()" style="width:100%;">
                <option value="">— {{ t('Choose a customer account') }} —</option>
                @foreach ($customers as $c)
                    <option value="{{ $c->meter_serial }}" @if($meterSerial===$c->meter_serial) selected @endif>
                        {{ $c->meter_serial }} — {{ trim(($c->first_name ?? '').' '.($c->middle_name ?? '').' '.($c->last_name ?? '')) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="flex: 1; min-width: 140px;">
            <label style="font-size: 12px; font-weight: 700; color: var(--on-surface); margin-bottom: 4px; display: block;">{{ t('Billing Year') }}</label>
            <select name="year" class="fancy" onchange="this.form.submit()" style="width:100%;">
                @foreach ($availableYears as $y)
                    <option value="{{ $y }}" @if((string)$y === (string)$year) selected @endif>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <button class="btn btn-primary" type="submit">{!! icon('search', 16) !!} {{ t('Load Ledger') }}</button>
    </form>
</div>

@if (empty($customer))
<div class="panel" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); padding: 50px 20px; text-align: center;">
    <div style="opacity: 0.4; margin-bottom: 12px;">{!! icon('book-open', 54) !!}</div>
    <h3 style="margin: 0; color: var(--on-surface);">{{ t('No Customer Account Selected') }}</h3>
    <p style="color: var(--text-muted); margin-top: 6px;">{{ t('Search and select a customer account from the dropdown above to load their ledger history.') }}</p>
</div>
@else

<!-- KPI Stat Cards Bar -->
<div class="card-grid cols-4" style="margin-bottom: 20px;">
    <div class="stat-card accent gsap-stat-card gsap-hover-card">
        <div class="stat-label">{{ t('Total Billed') }}</div>
        <div class="stat-value" data-gsap-counter data-target-val="{{ $grandTotal }}">{{ number_format($grandTotal, 0) }} <span style="font-size:13px;">ETB</span></div>
        <div class="stat-meta">{{ count($ledger) }} {{ t('Bills in') }} {{ $year }}</div>
    </div>

    <div class="stat-card success gsap-stat-card gsap-hover-card">
        <div class="stat-label">{{ t('Water Consumption') }}</div>
        <div class="stat-value" style="color: var(--success);" data-gsap-counter data-target-val="{{ $totalConsumption }}">{{ number_format($totalConsumption, 1) }} <span style="font-size:13px;">m³</span></div>
        <div class="stat-meta">{{ count($ledger) > 0 ? number_format($totalConsumption / count($ledger), 1) : 0 }} m³ {{ t('Avg / Month') }}</div>
    </div>

    <div class="stat-card success gsap-stat-card gsap-hover-card">
        <div class="stat-label">{{ t('Paid Revenue') }}</div>
        <div class="stat-value" style="color: var(--success);" data-gsap-counter data-target-val="{{ $paidTotal }}">{{ number_format($paidTotal, 0) }} <span style="font-size:13px;">ETB</span></div>
        <div class="stat-meta">{{ $paidBills }} {{ t('Paid Bills') }}</div>
    </div>

    <div class="stat-card danger gsap-stat-card gsap-hover-card">
        <div class="stat-label">{{ t('Unpaid Balance') }}</div>
        <div class="stat-value" style="color: var(--danger);" data-gsap-counter data-target-val="{{ $unpaidTotal }}">{{ number_format($unpaidTotal, 0) }} <span style="font-size:13px;">ETB</span></div>
        <div class="stat-meta">{{ $unpaidBills }} {{ t('Unpaid Bills') }}</div>
    </div>
</div>

<!-- EOS Chart.js Consumption & Monthly Cost Trends Grid -->
<div class="card-grid cols-2 gsap-chart-card" style="margin-bottom: 20px;">
    <div class="panel" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); padding: 16px;">
        <div style="font-weight: 700; font-size: 13.5px; color: var(--on-surface); margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
            <span style="display: flex; align-items: center; gap: 8px;">{!! icon('water', 16) !!} {{ t('Monthly Consumption Trend (m³)') }}</span>
            <span class="badge badge-success">{{ number_format($totalConsumption, 1) }} m³ Total</span>
        </div>
        <div style="height: 190px; position: relative;">
            <canvas id="ledgerConsChart"></canvas>
        </div>
    </div>

    <div class="panel" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); padding: 16px;">
        <div style="font-weight: 700; font-size: 13.5px; color: var(--on-surface); margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
            <span style="display: flex; align-items: center; gap: 8px;">{!! icon('bar-chart', 16) !!} {{ t('Monthly Billed Cost Trend (ETB)') }}</span>
            <span class="badge badge-secondary">{{ number_format($grandTotal, 0) }} ETB Total</span>
        </div>
        <div style="height: 190px; position: relative;">
            <canvas id="ledgerCostChart"></canvas>
        </div>
    </div>
</div>

<!-- Two Column Layout: Ledger History Table & Customer Profile Side Card -->
<div style="display: grid; grid-template-columns: 2.2fr 1fr; gap: 20px; margin-bottom: 20px;">
    <!-- Main Billing Ledger History Table -->
    <div class="panel gsap-section-card" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); overflow: hidden; box-shadow: var(--shadow-sm); position: relative;">
        <div style="height: 4px; background: var(--primary-container);"></div>
        <div class="panel-header" style="padding: 14px 20px; background: var(--surface-container-lowest); border-bottom: 1px solid var(--outline-variant); display: flex; justify-content: space-between; align-items: center;">
            <span style="font-weight: 700; font-size: 14px; color: var(--on-surface);">
                {{ t('Billing & Meter Reading History') }} — {{ $year }}
            </span>
            <span style="font-size: 12px; color: var(--text-muted);">
                {{ count($ledger) }} {{ t('entries') }}
            </span>
        </div>

        @if ($ledger->isEmpty())
            <div style="padding: 40px; text-align: center; color: var(--text-muted);">
                {!! icon('file-text', 40) !!}
                <div style="margin-top: 8px; font-weight: 600;">{{ t('No billing history found for this customer in') }} {{ $year }}</div>
            </div>
        @else
            <div class="scrollable-table">
                <div class="scroll-progress"><div class="scroll-progress-bar"></div></div>
                <div class="table-scroll-view">
                    <table class="data-table compact" style="width:100%;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ t('Month') }}</th>
                                <th>{{ t('Read Date') }}</th>
                                <th>{{ t('Prev R') }}</th>
                                <th>{{ t('Cur R') }}</th>
                                <th>m³</th>
                                <th>{{ t('Water Fee') }}</th>
                                <th>{{ t('Meter') }}</th>
                                <th>{{ t('Svc Fee') }}</th>
                                <th>{{ t('Fund') }}</th>
                                <th>{{ t('Total Cost') }}</th>
                                <th>{{ t('Status') }}</th>
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
                            <tr>
                                <td>{{ $idx }}</td>
                                <td><strong style="color: var(--on-surface);">{{ $row->bill_month }}</strong></td>
                                <td>{{ $row->reading_date ?? '—' }}</td>
                                <td>{{ number_format($prev, 1) }}</td>
                                <td>{{ number_format($cur, 1) }}</td>
                                <td><strong style="color: var(--primary);">{{ number_format($use, 1) }}</strong></td>
                                <td>{{ number_format($row->consumption_cost, 0) }}</td>
                                <td>{{ number_format($row->meter_price, 0) }}</td>
                                <td>{{ number_format($row->service_price, 0) }}</td>
                                <td>{{ number_format($row->state_price, 0) }}</td>
                                <td><strong style="color: var(--on-surface);">{{ number_format($row->total_monthly_cost, 0) }} ETB</strong></td>
                                <td>
                                    @if ($row->payment_status === 'Paid')
                                        <span class="badge badge-success">{!! icon('check', 12) !!} {{ t('Paid') }}</span>
                                    @else
                                        <span class="badge badge-danger">{!! icon('x', 12) !!} {{ t('Unpaid') }}</span>
                                    @endif
                                </td>
                            </tr>
                            @php $prev = $cur; @endphp
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background: var(--surface-container-low); font-weight: 700;">
                                <td colspan="10" style="text-align:right; padding: 12px 14px;">{{ t('Annual Grand Total') }}:</td>
                                <td style="color: var(--primary); font-size: 14px;">{{ number_format($grandTotal, 0) }} ETB</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- Customer Details & Quick Actions Panel -->
    <div style="display: flex; flex-direction: column; gap: 16px;">
        <!-- Customer Profile Card -->
        <div class="panel" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); overflow: hidden;">
            <div class="panel-header" style="background: var(--surface-container-low); padding: 12px 16px; border-bottom: 1px solid var(--outline-variant); font-weight: 700; font-size: 13px; color: var(--on-surface); display: flex; align-items: center; gap: 8px;">
                {!! icon('user', 16) !!} {{ t('Customer Profile') }}
            </div>
            <div class="panel-body" style="padding: 16px;">
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div>
                        <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; font-weight: 700; color: var(--text-muted);">{{ t('Meter Serial Code') }}</div>
                        <div style="font-family: monospace; font-size: 16px; font-weight: 800; color: var(--primary); margin-top: 2px;">{{ $customer->meter_serial }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; font-weight: 700; color: var(--text-muted);">{{ t('Full Name') }}</div>
                        <div style="font-size: 14px; font-weight: 700; color: var(--on-surface); margin-top: 2px;">{{ trim(($customer->first_name ?? '').' '.($customer->middle_name ?? '').' '.($customer->last_name ?? '')) }}</div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; border-top: 1px solid var(--outline-variant); padding-top: 10px;">
                        <div>
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">{{ t('Kebele') }}</div>
                            <div style="font-size: 13px; font-weight: 700;">{{ $customer->kebele ?? '—' }}</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">{{ t('Phone') }}</div>
                            <div style="font-size: 13px; font-weight: 700;">{{ $customer->phone_number ?? '—' }}</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">{{ t('Meter Size') }}</div>
                            <div style="font-size: 13px; font-weight: 700;">{{ $customer->meter_size ?? '1/2"' }}</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">{{ t('Status') }}</div>
                            <div>
                                @if ($customer->customer_status === 'Active')
                                    <span class="badge badge-success">{!! icon('check', 12) !!} {{ t('Active') }}</span>
                                @else
                                    <span class="badge badge-danger">{!! icon('x', 12) !!} {{ $customer->customer_status }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Meter Reading Adjustment Panel -->
        <div class="panel" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); overflow: hidden;">
            <div class="panel-header" style="background: var(--surface-container-low); padding: 12px 16px; border-bottom: 1px solid var(--outline-variant); font-weight: 700; font-size: 13px; color: var(--on-surface); display: flex; align-items: center; gap: 8px;">
                {!! icon('wrench', 16) !!} {{ t('Operational Quick Actions') }}
            </div>
            <div class="panel-body" style="padding: 16px;">
                <div class="form-group" style="margin-bottom: 10px;">
                    <label style="font-size: 12px; font-weight: 600;">{{ t('Update Start Reading (m³)') }}</label>
                    <div style="display:flex; gap:6px;">
                        <input type="number" step="0.01" id="startReading" value="{{ $customer->start_value }}" class="form-control">
                        <button class="btn btn-primary" onclick="updateStartReading()">{!! icon('check', 14) !!}</button>
                    </div>
                </div>
                <button class="btn btn-warning btn-block" onclick="disconnectCustomer()" style="width: 100%; margin-top: 8px;">
                    {!! icon('x', 14) !!} {{ t('Disconnect Customer (DC)') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Collapsible Floating Quick Action Menu (FAB) -->
<div class="fab-wrapper">
    <div class="fab-menu" id="fabMenu">
        <button type="button" class="fab-item" onclick="window.print(); toggleFabMenu();">
            <span class="icon">{!! icon('print', 16) !!}</span>
            <span class="label">{{ t('Print Statement') }}</span>
        </button>
        <button type="button" class="fab-item" onclick="exportLedger(); toggleFabMenu();">
            <span class="icon">{!! icon('download', 16) !!}</span>
            <span class="label">{{ t('Export Ledger CSV') }}</span>
        </button>
    </div>
    <button type="button" class="fab-trigger-btn" onclick="toggleFabMenu()" title="Quick Actions">
        <span class="fab-icon-main">{!! icon('plus', 22) !!}</span>
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
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.15)',
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
                        y: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 10 } } },
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
                        backgroundColor: '#111C2D',
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
@endif
@endsection
