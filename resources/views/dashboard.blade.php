@extends('layouts.app')

@section('content')

<!-- Page Header & Banner -->
<div class="page-header-block gsap-hero" style="margin-bottom: 20px;">
    <div class="page-info">
        <div style="font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">
            {{ t('Overview') }} &bull; {{ t('Utility Control Panel') }}
        </div>
        <h2 style="margin: 0; font-size: 22px; font-weight: 700; color: var(--text-strong); display: flex; align-items: center; gap: 10px;">
            {!! icon('dashboard', 24) !!} {{ t('Dashboard Overview') }}
        </h2>
        <p style="margin-top: 4px; color: var(--text-muted);">{{ t('Welcome back') }}, <strong>{{ auth()->user()?->fullName() }}</strong> &bull; {{ t('real-time utility statistics, consumption trends and revenue metrics.') }}</p>
    </div>
    <div class="page-actions" style="display: flex; gap: 10px;">
        <button type="button" class="btn btn-sm btn-primary" onclick="openModal('quickCmdModal')" style="box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);">
            {!! icon('search', 14) !!} <span>Quick Command</span> <kbd style="margin-left: 6px; padding: 2px 5px; background: rgba(255,255,255,0.25); border-radius: 4px; font-size: 10px;">Ctrl+K</kbd>
        </button>
    </div>
</div>

<!-- KPI Stat Cards Bar -->
<div class="card-grid cols-4" style="margin-bottom: 20px;">
    <div class="stat-card accent gsap-stat-card gsap-hover-card">
        <div class="stat-label">{{ t('Total Registered Customers') }}</div>
        <div class="stat-value" data-gsap-counter data-target-val="{{ $totalCustomers }}">{{ number_format($totalCustomers) }}</div>
        <div class="stat-meta">{{ t('Active') }} + {{ t('Disconnected') }}</div>
    </div>
    <div class="stat-card success gsap-stat-card gsap-hover-card">
        <div class="stat-label">{{ t('Active Connected Accounts') }}</div>
        <div class="stat-value" style="color: var(--success);" data-gsap-counter data-target-val="{{ $activeCount }}">{{ number_format($activeCount) }}</div>
        <div class="stat-meta">{{ $activePct }}% {{ t('connected rate') }}</div>
    </div>
    <div class="stat-card danger gsap-stat-card gsap-hover-card">
        <div class="stat-label">{{ t('Disconnected Accounts (DC)') }}</div>
        <div class="stat-value" style="color: var(--danger);" data-gsap-counter data-target-val="{{ $dcCount }}">{{ number_format($dcCount) }}</div>
        <div class="stat-meta">{{ $totalCustomers - $activeCount - $dcCount }} {{ t('pending verification') }}</div>
    </div>
    <div class="stat-card warning gsap-stat-card gsap-hover-card">
        <div class="stat-label">{{ t('Unpaid Billing Invoices') }}</div>
        <div class="stat-value" style="color: var(--warning);" data-gsap-counter data-target-val="{{ $unpaidBills }}">{{ number_format($unpaidBills) }}</div>
        <div class="stat-meta">{{ $paidBills }} {{ t('paid invoices') }}</div>
    </div>
</div>

<!-- Futuristic Chart.js Analytics Dashboard Grid -->
<div style="display: grid; grid-template-columns: 2.2fr 1fr; gap: 20px; margin-bottom: 20px;">
    <!-- Large Area Line Chart: Water Consumption & Revenue Trends -->
    <div class="panel gsap-chart-card" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); padding: 18px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <div>
                <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--on-surface); display: flex; align-items: center; gap: 8px;">
                    {!! icon('line-chart', 18) !!} {{ t('Monthly Water Consumption & Revenue Trend') }}
                </h3>
                <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">{{ t('Real-time m³ consumption volume vs billed revenue') }}</div>
            </div>
            <div class="segmented" style="padding: 2px; background: var(--surface-container-low);">
                <button class="btn btn-sm active" id="btnChartTypeLine" onclick="switchDashboardChartType('line')">Glow Area</button>
                <button class="btn btn-sm" id="btnChartTypeBar" onclick="switchDashboardChartType('bar')">Bar View</button>
            </div>
        </div>
        <div style="height: 240px; position: relative;">
            <canvas id="dashboardTrendChart"></canvas>
        </div>
    </div>

    <!-- Doughnut Chart: Account Status & Financial Health -->
    <div class="panel gsap-chart-card" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); padding: 18px;">
        <div style="margin-bottom: 14px;">
            <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--on-surface); display: flex; align-items: center; gap: 8px;">
                {!! icon('pie-chart', 18) !!} {{ t('Operational Status Breakdown') }}
            </h3>
            <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">{{ t('Active vs DC vs Unpaid Invoices') }}</div>
        </div>
        <div style="height: 240px; position: relative;">
            <canvas id="dashboardStatusChart"></canvas>
        </div>
    </div>
</div>

<!-- Quick Operations Grid & Recent Activity -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    <!-- Recent Registered Customers -->
    <div class="panel gsap-section-card" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); overflow: hidden;">
        <div class="panel-header" style="padding: 14px 20px; background: var(--surface-container-lowest); border-bottom: 1px solid var(--outline-variant); display: flex; justify-content: space-between; align-items: center;">
            <span style="font-weight: 700; font-size: 14px; color: var(--on-surface); display: flex; align-items: center; gap: 8px;">
                {!! icon('users', 16) !!} {{ t('Recently Registered Customers') }}
            </span>
            <a href="{{ route('customer-service.index') }}" class="btn btn-sm">{!! icon('arrow-right', 14) !!} {{ t('View All Registry') }}</a>
        </div>
        <div class="scrollable-table">
            <div class="scroll-progress"><div class="scroll-progress-bar"></div></div>
            <div class="table-scroll-view">
                <table class="data-table compact" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>{{ t('Code') }}</th>
                            <th>{{ t('Full Name') }}</th>
                            <th>{{ t('Kebele') }}</th>
                            <th>{{ t('Type') }}</th>
                            <th>{{ t('Phone') }}</th>
                            <th>{{ t('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($recentCustomers as $c)
                        <tr>
                            <td><span style="font-family: monospace; font-weight: 700; color: var(--primary);">{{ $c->meter_serial }}</span></td>
                            <td><strong style="color: var(--on-surface);">{{ trim(($c->first_name ?? '').' '.($c->middle_name ?? '').' '.($c->last_name ?? '')) }}</strong></td>
                            <td>Kebele {{ $c->kebele }}</td>
                            <td>{{ $c->customer_type }}</td>
                            <td>{{ $c->phone_number }}</td>
                            <td>
                                @if ($c->customer_status === 'Active')
                                    <span class="badge badge-success">{!! icon('check', 12) !!} Active</span>
                                @elseif ($c->customer_status === 'DC')
                                    <span class="badge badge-danger">{!! icon('x', 12) !!} DC</span>
                                @else
                                    <span class="badge badge-warning">{{ $c->customer_status }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions Panel -->
    <div style="display: flex; flex-direction: column; gap: 16px;">
        <div class="panel" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); overflow: hidden;">
            <div class="panel-header" style="background: var(--surface-container-low); padding: 12px 16px; border-bottom: 1px solid var(--outline-variant); font-weight: 700; font-size: 13px; color: var(--on-surface);">
                {!! icon('zap', 16) !!} {{ t('Quick Navigation') }}
            </div>
            <div class="panel-body" style="padding: 14px; display: flex; flex-direction: column; gap: 8px;">
                <a href="{{ route('customer-service.index') }}" class="btn" style="justify-content: flex-start; padding: 10px 14px; font-weight: 600;">
                    {!! icon('plus', 16) !!} <span>Register Customer</span>
                </a>
                <a href="{{ route('bills.index') }}" class="btn btn-primary" style="justify-content: flex-start; padding: 10px 14px; font-weight: 600;">
                    {!! icon('receipt', 16) !!} <span>Calculate & Print Bills</span>
                </a>
                <a href="{{ route('customer-ledger.index') }}" class="btn" style="justify-content: flex-start; padding: 10px 14px; font-weight: 600;">
                    {!! icon('book-open', 16) !!} <span>Customer Ledger Reports</span>
                </a>
                <a href="{{ route('reading-correction.index') }}" class="btn" style="justify-content: flex-start; padding: 10px 14px; font-weight: 600;">
                    {!! icon('wrench', 16) !!} <span>Reading Correction</span>
                </a>
            </div>
        </div>

        <div class="panel" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); overflow: hidden;">
            <div class="panel-header" style="background: var(--surface-container-low); padding: 12px 16px; border-bottom: 1px solid var(--outline-variant); font-weight: 700; font-size: 13px; color: var(--on-surface);">
                {!! icon('clock', 16) !!} {{ t('Recent System Audit') }}
            </div>
            <div style="padding: 10px 16px; max-height: 200px; overflow-y: auto;">
                @if ($recentAudit->isEmpty())
                    <p style="color: var(--text-muted); font-size: 12px; text-align: center; padding: 20px 0;">No recent audit activity.</p>
                @else
                    @foreach ($recentAudit as $a)
                        <div style="padding: 8px 0; border-bottom: 1px solid var(--outline-variant); font-size: 12px;">
                            <div style="font-weight: 600; color: var(--on-surface);">{{ $a->log_reason }}</div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">{{ $a->done_by }} &bull; {{ substr($a->log_date, 5) }}</div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Command Modal -->
<div class="modal-backdrop v2" id="quickCmdModal">
    <div class="modal v2" style="max-width: 520px;">
        <div class="modal-header">
            <div class="modal-icon">{!! icon('search', 20) !!}</div>
            <div class="modal-title">
                <h3>Quick Command & Customer Search</h3>
                <div class="modal-subtitle">Search customer code or navigate to module</div>
            </div>
            <button type="button" class="close" onclick="closeModal('quickCmdModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group" style="margin-bottom: 16px;">
                <label>Search Customer Code or Module</label>
                <input type="text" id="cmdSearchInput" class="form-control" placeholder="Type ETY-0001, ledger, or customer..." oninput="runQuickCmdSearch(this.value)">
            </div>
            <div id="cmdSearchResults" style="max-height: 240px; overflow-y: auto; display: flex; flex-direction: column; gap: 6px;">
                <a href="{{ route('customer-service.index') }}" class="btn" style="justify-content: flex-start;">
                    {!! icon('users', 16) !!} <span>Customer Service Management</span>
                </a>
                <a href="{{ route('customer-ledger.index') }}" class="btn" style="justify-content: flex-start;">
                    {!! icon('book-open', 16) !!} <span>Customer Ledger Reports</span>
                </a>
                <a href="{{ route('bills.index') }}" class="btn" style="justify-content: flex-start;">
                    {!! icon('receipt', 16) !!} <span>Bills & Receipt Printing</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
let dashboardChartInstance = null;

function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }

function switchDashboardChartType(type) {
    document.getElementById('btnChartTypeLine').classList.toggle('active', type === 'line');
    document.getElementById('btnChartTypeBar').classList.toggle('active', type === 'bar');
    initDashboardTrendChart(type);
}

function initDashboardTrendChart(type = 'line') {
    const canvas = document.getElementById('dashboardTrendChart');
    if (!canvas || typeof Chart === 'undefined') return;

    const old = Chart.getChart(canvas);
    if (old) old.destroy();

    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 200);
    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.45)');
    gradient.addColorStop(1, 'rgba(16, 185, 129, 0.01)');

    dashboardChartInstance = new Chart(ctx, {
        type: type,
        data: {
            labels: ['Amajjii', 'Guraandhala', 'Bitootessa', 'Elba', 'Caamsaa', 'Waxabajjii', 'Adooleessa', 'Hagayya', 'Fuulbaana', 'Onkololeessa', 'Sadaasa', 'Muddee'],
            datasets: [{
                label: 'Water Consumption (m³)',
                data: [1420, 1580, 1610, 1490, 1720, 1850, 1910, 1780, 1650, 1890, 1950, 2040],
                borderColor: '#10B981',
                backgroundColor: (type === 'line') ? gradient : '#10B981',
                borderWidth: 3,
                fill: (type === 'line'),
                tension: 0.4,
                pointBackgroundColor: '#10B981',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 10, family: 'Inter' } } },
                x: { grid: { display: false }, ticks: { font: { size: 10, family: 'Inter' } } }
            }
        }
    });
}

(function initDashboardCharts() {
    const run = () => {
        initDashboardTrendChart('line');

        // Doughnut Status Chart
        const statusCtx = document.getElementById('dashboardStatusChart');
        if (statusCtx && typeof Chart !== 'undefined') {
            const oldStatus = Chart.getChart(statusCtx);
            if (oldStatus) oldStatus.destroy();
            new Chart(statusCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Active Connected', 'Disconnected (DC)', 'Unpaid Invoices', 'Reading Complaints'],
                    datasets: [{
                        data: [{{ $activeCount }}, {{ $dcCount }}, {{ $unpaidBills }}, {{ $pendingComplaints }}],
                        backgroundColor: ['#10B981', '#EF4444', '#FEA619', '#6366F1'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11, family: 'Inter' } } }
                    },
                    cutout: '70%'
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

function runQuickCmdSearch(q) {
    q = q.trim().toLowerCase();
    const container = document.getElementById('cmdSearchResults');
    if (!q) return;
    if (q.startsWith('ety-') || q.startsWith('ety')) {
        container.innerHTML = `
            <a href="${window.apiUrl('../customer-service?filter=search&search=' + encodeURIComponent(q))}" class="btn btn-primary" style="justify-content: flex-start;">
                {!! icon('search', 16) !!} Search Customer "${q.toUpperCase()}"
            </a>
            <a href="${window.apiUrl('../customer-ledger?meterSerial=' + encodeURIComponent(q.toUpperCase()))}" class="btn" style="justify-content: flex-start;">
                {!! icon('book-open', 16) !!} View Ledger for "${q.toUpperCase()}"
            </a>
        `;
    }
}
</script>
@endsection
