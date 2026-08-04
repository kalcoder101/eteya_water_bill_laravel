@extends('layouts.app')

@section('content')

<!-- Page Header -->
<div class="gsap-hero flex flex-wrap items-end justify-between gap-4 mb-6">
    <div>
        <div class="text-[11px] uppercase tracking-widest font-bold text-slate-500 mb-1">
            {{ t('Overview') }} &bull; {{ t('Utility Control Panel') }}
        </div>
        <h2 class="m-0 text-[22px] font-bold tracking-tight text-slate-900 flex items-center gap-2.5">
            <span class="text-emerald-600">{!! icon('dashboard', 24) !!}</span> {{ t('Dashboard Overview') }}
        </h2>
        <p class="mt-1 text-[13px] text-slate-500">
            {{ t('Welcome back') }}, <strong class="text-slate-900">{{ auth()->user()?->fullName() }}</strong> &bull; {{ t('real-time utility statistics, consumption trends and revenue metrics.') }}
        </p>
    </div>
    <div class="flex items-center gap-2.5">
        <button type="button" onclick="openModal('quickCmdModal')"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-[0_4px_14px_rgba(5,150,105,0.35)]">
            {!! icon('search', 14) !!} <span>{{ t('Quick Command') }}</span>
            <kbd class="ml-1 px-1.5 py-0.5 rounded bg-white/25 font-mono text-[10px]">Ctrl+K</kbd>
        </button>
    </div>
</div>

<!-- KPI Stat Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="gsap-stat-card gsap-hover-card p-5 rounded-xl bg-white border border-slate-200 shadow-card space-y-2">
        <div class="flex items-center justify-between text-xs">
            <span class="font-bold uppercase tracking-wider text-[11px] text-emerald-800">{{ t('Total Registered Customers') }}</span>
            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                <span class="[&>svg]:text-[16px]">{!! icon('users', 16) !!}</span>
            </div>
        </div>
        <div class="text-2xl font-bold text-slate-900 font-mono tabular-nums" data-gsap-counter data-target-val="{{ $totalCustomers }}">{{ number_format($totalCustomers) }}</div>
        <div class="text-[11px] text-slate-500">{{ t('Active') }} + {{ t('Disconnected') }}</div>
    </div>

    <div class="gsap-stat-card gsap-hover-card p-5 rounded-xl bg-white border border-slate-200 shadow-card space-y-2">
        <div class="flex items-center justify-between text-xs">
            <span class="font-bold uppercase tracking-wider text-[11px] text-sky-800">{{ t('Active Connected Accounts') }}</span>
            <div class="w-8 h-8 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center">
                <span class="[&>svg]:text-[16px]">{!! icon('check', 16) !!}</span>
            </div>
        </div>
        <div class="text-2xl font-bold text-slate-900 font-mono tabular-nums text-emerald-600" data-gsap-counter data-target-val="{{ $activeCount }}">{{ number_format($activeCount) }}</div>
        <div class="text-[11px] text-slate-500">{{ $activePct }}% {{ t('connected rate') }}</div>
    </div>

    <div class="gsap-stat-card gsap-hover-card p-5 rounded-xl bg-white border border-slate-200 shadow-card space-y-2">
        <div class="flex items-center justify-between text-xs">
            <span class="font-bold uppercase tracking-wider text-[11px] text-rose-800">{{ t('Disconnected Accounts (DC)') }}</span>
            <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center">
                <span class="[&>svg]:text-[16px]">{!! icon('x', 16) !!}</span>
            </div>
        </div>
        <div class="text-2xl font-bold text-slate-900 font-mono tabular-nums text-rose-600" data-gsap-counter data-target-val="{{ $dcCount }}">{{ number_format($dcCount) }}</div>
        <div class="text-[11px] text-slate-500">{{ $totalCustomers - $activeCount - $dcCount }} {{ t('pending verification') }}</div>
    </div>

    <div class="gsap-stat-card gsap-hover-card p-5 rounded-xl bg-white border border-slate-200 shadow-card space-y-2">
        <div class="flex items-center justify-between text-xs">
            <span class="font-bold uppercase tracking-wider text-[11px] text-amber-800">{{ t('Unpaid Billing Invoices') }}</span>
            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                <span class="[&>svg]:text-[16px]">{!! icon('receipt', 16) !!}</span>
            </div>
        </div>
        <div class="text-2xl font-bold text-slate-900 font-mono tabular-nums text-amber-600" data-gsap-counter data-target-val="{{ $unpaidBills }}">{{ number_format($unpaidBills) }}</div>
        <div class="text-[11px] text-slate-500">{{ $paidBills }} {{ t('paid invoices') }}</div>
    </div>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-[2.2fr_1fr] gap-5 mb-6">
    <!-- Consumption & Revenue Trend -->
    <div class="gsap-chart-card p-5 rounded-xl bg-white border border-slate-200 shadow-card">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="m-0 text-[15px] font-serif font-bold text-slate-900 flex items-center gap-2">
                    <span class="text-emerald-600">{!! icon('line-chart', 18) !!}</span> {{ t('Monthly Water Consumption & Revenue Trend') }}
                </h3>
                <div class="text-[11.5px] text-slate-500 mt-0.5">{{ t('Real-time m³ consumption volume vs billed revenue') }}</div>
            </div>
            <div class="segmented">
                <button type="button" class="active" id="btnChartTypeLine" onclick="switchDashboardChartType('line')">Glow Area</button>
                <button type="button" id="btnChartTypeBar" onclick="switchDashboardChartType('bar')">Bar View</button>
            </div>
        </div>
        <div class="h-60 relative flex items-center justify-center">
            <canvas id="dashboardTrendChart"></canvas>
        </div>
    </div>

    <!-- Operational Status -->
    <div class="gsap-chart-card p-5 rounded-xl bg-white border border-slate-200 shadow-card">
        <div class="mb-4">
            <h3 class="m-0 text-[15px] font-serif font-bold text-slate-900 flex items-center gap-2">
                <span class="text-emerald-600">{!! icon('pie-chart', 18) !!}</span> {{ t('Operational Status Breakdown') }}
            </h3>
            <div class="text-[11.5px] text-slate-500 mt-0.5">{{ t('Active vs DC vs Unpaid Invoices') }}</div>
        </div>
        <div class="h-60 relative flex items-center justify-center">
            <canvas id="dashboardStatusChart"></canvas>
        </div>
    </div>
</div>

<!-- Recent Customers + Quick Ops -->
<div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-5">
    <!-- Recently Registered Customers -->
    <div class="gsap-section-card bg-white border border-slate-200 rounded-xl shadow-card overflow-hidden self-start">
        <div class="flex items-center justify-between gap-3 px-5 py-3.5 border-b border-slate-100">
            <span class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <span class="text-emerald-600">{!! icon('users', 16) !!}</span> {{ t('Recently Registered Customers') }}
            </span>
            <a href="{{ route('customer-service.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition">
                {!! icon('arrow-right', 13) !!} {{ t('View All Registry') }}
            </a>
        </div>
        <div class="scrollable-table border-0 rounded-none">
            <div class="scroll-progress"><div class="scroll-progress-bar"></div></div>
            <div class="table-scroll-view">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold">
                            <th class="text-left px-4 py-3">{{ t('Code') }}</th>
                            <th class="text-left px-4 py-3">{{ t('Full Name') }}</th>
                            <th class="text-left px-4 py-3">{{ t('Kebele') }}</th>
                            <th class="text-left px-4 py-3">{{ t('Type') }}</th>
                            <th class="text-left px-4 py-3">{{ t('Phone') }}</th>
                            <th class="text-left px-4 py-3">{{ t('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($recentCustomers as $c)
                        <tr class="border-b border-slate-100 odd:bg-white even:bg-slate-50/50 hover:bg-emerald-50/60 transition-colors">
                            <td class="px-4 py-3"><span class="font-mono font-bold text-emerald-700">{{ $c->meter_serial }}</span></td>
                            <td class="px-4 py-3"><strong class="text-slate-900">{{ trim(($c->first_name ?? '').' '.($c->middle_name ?? '').' '.($c->last_name ?? '')) }}</strong></td>
                            <td class="px-4 py-3 text-slate-600">Kebele {{ $c->kebele }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $c->customer_type }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $c->phone_number }}</td>
                            <td class="px-4 py-3">
                                @if ($c->customer_status === 'Active')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider">{!! icon('check', 11) !!} Active</span>
                                @elseif ($c->customer_status === 'DC')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 text-rose-800 border border-rose-300 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider">{!! icon('x', 11) !!} DC</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 text-amber-800 border border-amber-300 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider">{{ $c->customer_status }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right column -->
    <div class="flex flex-col gap-4 self-start">
        <!-- Quick Navigation -->
        <div class="gsap-section-card bg-white border border-slate-200 rounded-xl shadow-card overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 font-bold text-[13px] text-slate-900 flex items-center gap-2">
                <span class="text-amber-600">{!! icon('zap', 15) !!}</span> {{ t('Quick Navigation') }}
            </div>
            <div class="p-3.5 flex flex-col gap-2">
                <a href="{{ route('customer-service.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg bg-slate-50 hover:bg-emerald-50 hover:text-emerald-900 text-[13px] font-semibold text-slate-700 transition">
                    {!! icon('plus', 15) !!} <span>{{ t('Register Customer') }}</span>
                </a>
                <a href="{{ route('bills.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[13px] font-semibold transition shadow-sm">
                    {!! icon('receipt', 15) !!} <span>{{ t('Calculate & Print Bills') }}</span>
                </a>
                <a href="{{ route('customer-ledger.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg bg-slate-50 hover:bg-indigo-50 hover:text-indigo-900 text-[13px] font-semibold text-slate-700 transition">
                    {!! icon('book-open', 15) !!} <span>{{ t('Customer Ledger Reports') }}</span>
                </a>
                <a href="{{ route('reading-correction.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg bg-slate-50 hover:bg-amber-50 hover:text-amber-900 text-[13px] font-semibold text-slate-700 transition">
                    {!! icon('wrench', 15) !!} <span>{{ t('Reading Correction') }}</span>
                </a>
            </div>
        </div>

        <!-- Recent System Audit -->
        <div class="gsap-section-card bg-white border border-slate-200 rounded-xl shadow-card overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 font-bold text-[13px] text-slate-900 flex items-center gap-2">
                <span class="text-emerald-600">{!! icon('clock', 15) !!}</span> {{ t('Recent System Audit') }}
            </div>
            <div class="px-4 py-2.5 max-h-52 overflow-y-auto">
                @if ($recentAudit->isEmpty())
                    <p class="text-slate-500 text-xs text-center py-5">{{ t('No recent audit activity.') }}</p>
                @else
                    @foreach ($recentAudit as $a)
                        <div class="py-2 border-b border-slate-100 last:border-0 text-xs">
                            <div class="font-semibold text-slate-900">{{ $a->log_reason }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $a->done_by }} &bull; {{ substr($a->log_date, 5) }}</div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Command Modal -->
<div class="modal-backdrop v2" id="quickCmdModal">
    <div class="modal v2 bg-white rounded-2xl shadow-2xl w-full max-w-[520px] max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-gradient-to-br from-slate-50 to-white">
            <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">
                {!! icon('search', 19) !!}
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="m-0 text-base font-bold text-slate-900">Quick Command & Customer Search</h3>
                <div class="text-xs text-slate-500 mt-0.5">Search customer code or navigate to module</div>
            </div>
            <button type="button" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition" onclick="closeModal('quickCmdModal')">&times;</button>
        </div>
        <div class="p-5 overflow-y-auto">
            <div class="mb-4">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Search Customer Code or Module</label>
                <input type="text" id="cmdSearchInput" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500" placeholder="Type ETY-0001, ledger, or customer..." oninput="runQuickCmdSearch(this.value)">
            </div>
            <div id="cmdSearchResults" class="max-h-60 overflow-y-auto flex flex-col gap-1.5">
                <a href="{{ route('customer-service.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg bg-slate-50 hover:bg-emerald-50 hover:text-emerald-900 text-[13px] font-semibold text-slate-700 transition">
                    {!! icon('users', 15) !!} <span>Customer Service Management</span>
                </a>
                <a href="{{ route('customer-ledger.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg bg-slate-50 hover:bg-emerald-50 hover:text-emerald-900 text-[13px] font-semibold text-slate-700 transition">
                    {!! icon('book-open', 15) !!} <span>Customer Ledger Reports</span>
                </a>
                <a href="{{ route('bills.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg bg-slate-50 hover:bg-emerald-50 hover:text-emerald-900 text-[13px] font-semibold text-slate-700 transition">
                    {!! icon('receipt', 15) !!} <span>Bills & Receipt Printing</span>
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

function initDashboardTrendChart(type) {
    if (typeof type === 'undefined') type = 'line';
    const canvas = document.getElementById('dashboardTrendChart');
    if (!canvas || typeof Chart === 'undefined') return;

    const old = Chart.getChart(canvas);
    if (old) old.destroy();

    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 200);
    gradient.addColorStop(0, 'rgba(5, 150, 105, 0.4)');
    gradient.addColorStop(1, 'rgba(5, 150, 105, 0.01)');

    dashboardChartInstance = new Chart(ctx, {
        type: type,
        data: {
            labels: ['Amajjii', 'Guraandhala', 'Bitootessa', 'Elba', 'Caamsaa', 'Waxabajjii', 'Adooleessa', 'Hagayya', 'Fuulbaana', 'Onkololeessa', 'Sadaasa', 'Muddee'],
            datasets: [{
                label: 'Water Consumption (m³)',
                data: [1420, 1580, 1610, 1490, 1720, 1850, 1910, 1780, 1650, 1890, 1950, 2040],
                borderColor: '#059669',
                backgroundColor: (type === 'line') ? gradient : '#059669',
                borderWidth: 3,
                fill: (type === 'line'),
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
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { grid: { color: 'rgba(15,23,42,0.05)' }, ticks: { font: { size: 10, family: 'Inter' } } },
                x: { grid: { display: false }, ticks: { font: { size: 10, family: 'Inter' } } }
            }
        }
    });
}

(function initDashboardCharts() {
    const run = () => {
        initDashboardTrendChart('line');

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
                        backgroundColor: ['#059669', '#E11D48', '#D97706', '#4F46E5'],
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
            <a href="${window.apiUrl('../customer-service?filter=search&search=' + encodeURIComponent(q))}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[13px] font-semibold transition shadow-sm">
                {!! icon('search', 15) !!} Search Customer "${q.toUpperCase()}"
            </a>
            <a href="${window.apiUrl('../customer-ledger?meterSerial=' + encodeURIComponent(q.toUpperCase()))}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg bg-slate-50 hover:bg-emerald-50 hover:text-emerald-900 text-[13px] font-semibold text-slate-700 transition">
                {!! icon('book-open', 15) !!} View Ledger for "${q.toUpperCase()}"
            </a>
        `;
    }
}
</script>
@endsection
