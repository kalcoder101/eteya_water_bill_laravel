@extends('layouts.app')

@section('content')

<!-- Page Header & Banner -->
<div class="gsap-hero flex flex-wrap items-end justify-between gap-4 mb-6">
    <div class="min-w-0">
        <h2 class="m-0 text-[22px] font-bold tracking-tight text-slate-900 flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-700 inline-flex items-center justify-center shrink-0">{!! icon('line-chart', 20) !!}</span>
            <span>{{ t('Customer Detail Statistics') }}</span>
        </h2>
        <p class="mt-2 text-[13px] text-slate-500">{{ $title }} &mdash; {{ t('Comprehensive classification, status breakdown and Kebele distribution') }}</p>
    </div>
    <div class="flex items-center gap-2.5 flex-wrap">
        <div class="segmented bg-slate-100 p-1">
            <a class="{{ $reportType==='typeStatus'?'active':'' }}" href="?report=typeStatus">{{ t('Type × Status') }}</a>
            <a class="{{ $reportType==='type'?'active':'' }}" href="?report=type">{{ t('By Type') }}</a>
            <a class="{{ $reportType==='status'?'active':'' }}" href="?report=status">{{ t('By Status') }}</a>
        </div>
    </div>
</div>

<!-- KPI Stat Cards Bar -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <x-kpi :label="t('Total Registered')" :value="number_format($totalCustomers)" :subvalue="t('Customer accounts')" icon="users" color="emerald" />
    <x-kpi :label="t('Active Accounts')" :value="number_format($byStatus['Active'] ?? 0)" :subvalue="($totalCustomers > 0 ? number_format((($byStatus['Active'] ?? 0)/$totalCustomers)*100, 1) : 0).'% '.t('connected')" icon="check" color="emerald" :active="true" />
    <x-kpi label="Disconnected (DC)" :value="number_format($byStatus['DC'] ?? 0)" :subvalue="t('Cut off accounts')" icon="x" color="rose" />
    <x-kpi :label="t('Total Kebeles')" :value="number_format($totalKebeles)" :subvalue="t('Municipal kebeles')" icon="map-pin" color="amber" />
</div>

<!-- Chart.js Analytics Grid Section 1 -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
    <flux:card class="p-5">
        <div class="flex items-center justify-between gap-3 mb-4">
            <span class="flex items-center gap-2 font-serif font-bold text-sm text-slate-900">
                <span class="text-emerald-600">{!! icon('pie-chart', 16) !!}</span> {{ t('Customers by Category Type') }}
            </span>
            <flux:badge color="zinc" size="sm">{{ count($byType) }} {{ t('Categories') }}</flux:badge>
        </div>
        <div class="chart-wrapper-md h-[220px] relative flex items-center justify-center" style="min-height: 220px;">
            <canvas id="typeChart"></canvas>
        </div>
    </flux:card>

    <flux:card class="p-5">
        <div class="flex items-center justify-between gap-3 mb-4">
            <span class="flex items-center gap-2 font-serif font-bold text-sm text-slate-900">
                <span class="text-emerald-600">{!! icon('bar-chart', 16) !!}</span> {{ t('Customers by Account Status') }}
            </span>
            <flux:badge color="emerald" size="sm">{{ $totalCustomers }} {{ t('Total') }}</flux:badge>
        </div>
        <div class="chart-wrapper-md h-[220px] relative flex items-center justify-center" style="min-height: 220px;">
            <canvas id="statusChart"></canvas>
        </div>
    </flux:card>
</div>

<!-- Chart.js Analytics Grid Section 2 -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
    <flux:card class="p-5">
        <div class="flex items-center justify-between gap-3 mb-4">
            <span class="flex items-center gap-2 font-serif font-bold text-sm text-slate-900">
                <span class="text-emerald-600">{!! icon('map-pin', 16) !!}</span> {{ t('Kebele Customer Density Overview') }}
            </span>
            <flux:badge color="zinc" size="sm">Top Kebeles</flux:badge>
        </div>
        <div class="chart-wrapper-lg h-[240px] relative flex items-center justify-center" style="min-height: 240px;">
            <canvas id="kebeleChart"></canvas>
        </div>
    </flux:card>

    <flux:card class="p-5">
        <div class="flex items-center justify-between gap-3 mb-4">
            <span class="flex items-center gap-2 font-serif font-bold text-sm text-slate-900">
                <span class="text-emerald-600">{!! icon('line-chart', 16) !!}</span> {{ t('Monthly Registration Trend (Last 12 Months)') }}
            </span>
            <flux:badge color="emerald" size="sm">Growth Trend</flux:badge>
        </div>
        <div class="chart-wrapper-lg h-[240px] relative flex items-center justify-center" style="min-height: 240px;">
            <canvas id="trendChart"></canvas>
        </div>
    </flux:card>
</div>

<!-- Detailed Data Breakdown Pivot Table (Lazy Livewire Island) -->
<livewire:islands.statistics-pivots :report-type="$reportType" lazy />

<!-- Collapsible Floating Quick Action Menu (FAB) -->
<div class="fab-wrapper">
    <div class="fab-menu" id="fabMenu">
        <button type="button" class="fab-item inline-flex items-center gap-2.5 px-4 py-2.5 rounded-full bg-slate-900 text-white text-[13px] font-semibold border border-white/15 shadow-lg hover:bg-emerald-700 transition" onclick="window.print(); toggleFabMenu();">
            {!! icon('print', 16) !!} <span>{{ t('Print Report') }}</span>
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

(function initCustomerStatsCharts() {
    const run = () => {
        if (typeof Chart === 'undefined') return;

        const byType = {!! json_encode($byType) !!};
        const byStatus = {!! json_encode($byStatus) !!};
        const byKebele = {!! json_encode($byKebele) !!};
        const trend = {!! json_encode($trend) !!};

        // 1. Category Type Doughnut Chart
        const typeCtx = document.getElementById('typeChart');
        if (typeCtx) {
            const oldType = Chart.getChart(typeCtx);
            if (oldType) oldType.destroy();
            new Chart(typeCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(byType),
                    datasets: [{
                        data: Object.values(byType),
                        backgroundColor: ['#059669', '#3B82F6', '#6366F1', '#F59E0B', '#8B5CF6'],
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

        // 2. Account Status Bar Chart
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            const oldStatus = Chart.getChart(statusCtx);
            if (oldStatus) oldStatus.destroy();
            new Chart(statusCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: Object.keys(byStatus),
                    datasets: [{
                        label: 'Count',
                        data: Object.values(byStatus),
                        backgroundColor: ['#059669', '#EF4444', '#F59E0B', '#64748B'],
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

        // 3. Kebele Bar Chart
        const kebeleCtx = document.getElementById('kebeleChart');
        if (kebeleCtx) {
            const oldKebele = Chart.getChart(kebeleCtx);
            if (oldKebele) oldKebele.destroy();
            new Chart(kebeleCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: byKebele.map(r => 'Kebele ' + r.kebele),
                    datasets: [{
                        label: 'Customers',
                        data: byKebele.map(r => r.cnt),
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
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                    }
                }
            });
        }

        // 4. Registration Trend Smooth Line Chart
        const trendCtx = document.getElementById('trendChart');
        if (trendCtx) {
            const oldTrend = Chart.getChart(trendCtx);
            if (oldTrend) oldTrend.destroy();

            const ctx = trendCtx.getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, 200);
            gradient.addColorStop(0, 'rgba(5, 150, 105, 0.45)');
            gradient.addColorStop(1, 'rgba(5, 150, 105, 0.01)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trend.map(r => r.ym),
                    datasets: [{
                        label: 'New Customers',
                        data: trend.map(r => r.cnt),
                        borderColor: '#059669',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        fill: true,
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
                        y: { grid: { color: 'rgba(15,23,42,0.05)' }, ticks: { font: { size: 10 } } },
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                    }
                }
            });
        }
    };

    let retries = 0;
    const safeRun = () => {
        const testCanvas = document.getElementById('typeChart') || document.getElementById('statusChart');
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
