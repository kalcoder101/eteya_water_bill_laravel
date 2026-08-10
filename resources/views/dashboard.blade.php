@extends('layouts.app')

@section('content')

<!-- Page Header -->
<div class="gsap-hero flex flex-wrap items-end justify-between gap-4 mb-6">
    <div class="min-w-0">
        <h2 class="m-0 text-[22px] font-bold tracking-tight text-slate-900 flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-700 inline-flex items-center justify-center shrink-0">{!! icon('dashboard', 20) !!}</span>
            <span>{{ t('Dashboard Overview') }}</span>
        </h2>
        <p class="mt-2 text-[13px] text-slate-500">
            <strong class="font-bold text-slate-900">{{ t('Welcome back') }}, {{ auth()->user()?->fullName() }}</strong> &mdash; {{ t('real-time utility statistics, consumption trends and revenue metrics.') }}
        </p>
    </div>
    <div class="flex items-center gap-2.5">
        <flux:button icon="magnifying-glass" variant="primary" onclick="openModal('quickCmdModal')">
            {{ t('Quick Command') }}
            <kbd class="ml-1 px-1.5 py-0.5 rounded bg-white/25 font-mono text-[10px]">Ctrl+K</kbd>
        </flux:button>
    </div>
</div>

<!-- KPI Stat Cards Grid (Lazy Livewire Island) -->
<livewire:islands.dashboard-kpis lazy />

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-[2.2fr_1fr] gap-5 mb-6">
    <!-- Consumption & Revenue Trend -->
    <flux:card class="p-5">
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
        <div class="chart-wrapper-lg h-60 relative flex items-center justify-center" style="min-height: 240px;">
            <canvas id="dashboardTrendChart"></canvas>
        </div>
    </flux:card>

    <!-- Operational Status -->
    <flux:card class="p-5">
        <div class="mb-4">
            <h3 class="m-0 text-[15px] font-serif font-bold text-slate-900 flex items-center gap-2">
                <span class="text-emerald-600">{!! icon('pie-chart', 18) !!}</span> {{ t('Operational Status Breakdown') }}
            </h3>
            <div class="text-[11.5px] text-slate-500 mt-0.5">{{ t('Active vs DC vs Unpaid Invoices') }}</div>
        </div>
        <div class="chart-wrapper-lg h-60 relative flex items-center justify-center" style="min-height: 240px;">
            <canvas id="dashboardStatusChart"></canvas>
        </div>
    </flux:card>
</div>

<!-- Recent Customers + Quick Ops -->
<div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-5">
    <!-- Recently Registered Customers -->
    <flux:card class="overflow-hidden p-0 self-start">
        <div class="flex items-center justify-between gap-3 px-5 py-3.5 border-b border-slate-100 bg-slate-50/50">
            <span class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <span class="text-emerald-600">{!! icon('users', 16) !!}</span> {{ t('Recently Registered Customers') }}
            </span>
            <flux:button size="sm" icon="arrow-right" icon:trailing="arrow-right" :href="route('customer-service.index')">
                {{ t('View All Registry') }}
            </flux:button>
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
                                    <flux:badge color="emerald" icon="check" size="sm">Active</flux:badge>
                                @elseif ($c->customer_status === 'DC')
                                    <flux:badge color="rose" icon="x-mark" size="sm">DC</flux:badge>
                                @else
                                    <flux:badge color="amber" size="sm">{{ $c->customer_status }}</flux:badge>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </flux:card>

    <!-- Right column -->
    <div class="flex flex-col gap-4 self-start">
        <!-- Quick Navigation -->
        <flux:card class="overflow-hidden p-0">
            <div class="px-4 py-3 border-b border-slate-100 font-bold text-[13px] text-slate-900 flex items-center gap-2 bg-slate-50/50">
                <span class="text-amber-600">{!! icon('zap', 15) !!}</span> {{ t('Quick Navigation') }}
            </div>
            <div class="p-3.5 flex flex-col gap-2">
                <flux:button href="{{ route('customer-service.index') }}" icon="plus" class="justify-start">
                    {{ t('Register Customer') }}
                </flux:button>
                <flux:button href="{{ route('bills.index') }}" variant="primary" icon="receipt-percent" class="justify-start">
                    {{ t('Calculate & Print Bills') }}
                </flux:button>
                <flux:button href="{{ route('customer-ledger.index') }}" icon="book-open" class="justify-start">
                    {{ t('Customer Ledger Reports') }}
                </flux:button>
                <flux:button href="{{ route('reading-correction.index') }}" icon="wrench" class="justify-start">
                    {{ t('Reading Correction') }}
                </flux:button>
            </div>
        </flux:card>

        <!-- Recent System Audit -->
        <flux:card class="overflow-hidden p-0">
            <div class="px-4 py-3 border-b border-slate-100 font-bold text-[13px] text-slate-900 flex items-center gap-2 bg-slate-50/50">
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
        </flux:card>
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
                <flux:input id="cmdSearchInput" placeholder="Type ETY-0001, ledger, or customer..." icon="magnifying-glass" oninput="runQuickCmdSearch(this.value)" />
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
    let retries = 0;
    const run = () => {
        const trendCanvas = document.getElementById('dashboardTrendChart');
        if (!trendCanvas || typeof Chart === 'undefined') return;

        if (trendCanvas.parentElement && trendCanvas.parentElement.clientHeight === 0 && retries < 10) {
            retries++;
            setTimeout(run, 50);
            return;
        }

        initDashboardTrendChart('line');

        const statusCtx = document.getElementById('dashboardStatusChart');
        if (statusCtx) {
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

    if (document.readyState === 'complete') {
        run();
    } else {
        document.addEventListener('DOMContentLoaded', run);
        window.addEventListener('load', run);
    }
    window.addEventListener('resize', run);
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
