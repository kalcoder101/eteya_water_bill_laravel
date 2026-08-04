@extends('layouts.app')

@section('content')

<!-- Page Header & Banner -->
<div class="page-header-block gsap-hero">
    <div class="page-info">
        <div style="font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">
            {{ t('Analytics & Reports') }} &bull; {{ t('Customer Demographics') }}
        </div>
        <h2 style="margin: 0; display: flex; align-items: center; gap: 10px;">
            {!! icon('line-chart', 24) !!} {{ t('Customer Detail Statistics') }}
        </h2>
        <p style="margin-top: 4px; color: var(--text-muted);">{{ $title }} &bull; {{ t('Comprehensive classification, status breakdown and Kebele distribution') }}</p>
    </div>
    <div class="page-actions">
        <div class="segmented" style="background: var(--surface-container-low); padding: 3px;">
            <a class="{{ $reportType==='typeStatus'?'active':'' }}" href="?report=typeStatus">{{ t('Type × Status') }}</a>
            <a class="{{ $reportType==='type'?'active':'' }}" href="?report=type">{{ t('By Type') }}</a>
            <a class="{{ $reportType==='status'?'active':'' }}" href="?report=status">{{ t('By Status') }}</a>
        </div>
        <button class="btn btn-primary" onclick="window.print()" style="box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);">{!! icon('print', 14) !!} {{ t('Print Report') }}</button>
    </div>
</div>

<!-- KPI Stat Cards Bar -->
<div class="card-grid cols-4" style="margin-bottom: 20px;">
    <div class="stat-card accent gsap-stat-card gsap-hover-card">
        <div class="stat-label">{{ t('Total Registered') }}</div>
        <div class="stat-value" data-gsap-counter data-target-val="{{ $totalCustomers }}">{{ number_format($totalCustomers) }}</div>
        <div class="stat-meta">{{ t('Customer accounts') }}</div>
    </div>
    <div class="stat-card success gsap-stat-card gsap-hover-card">
        <div class="stat-label">{{ t('Active Accounts') }}</div>
        <div class="stat-value" style="color: var(--success);" data-gsap-counter data-target-val="{{ $byStatus['Active'] ?? 0 }}">{{ number_format($byStatus['Active'] ?? 0) }}</div>
        <div class="stat-meta">{{ $totalCustomers > 0 ? number_format((($byStatus['Active'] ?? 0)/$totalCustomers)*100, 1) : 0 }}% {{ t('connected') }}</div>
    </div>
    <div class="stat-card danger gsap-stat-card gsap-hover-card">
        <div class="stat-label">Disconnected (DC)</div>
        <div class="stat-value" style="color: var(--danger);" data-gsap-counter data-target-val="{{ $byStatus['DC'] ?? 0 }}">{{ number_format($byStatus['DC'] ?? 0) }}</div>
        <div class="stat-meta">{{ t('Cut off accounts') }}</div>
    </div>
    <div class="stat-card warning gsap-stat-card gsap-hover-card">
        <div class="stat-label">{{ t('Total Kebeles') }}</div>
        <div class="stat-value" style="color: var(--warning);" data-gsap-counter data-target-val="{{ $totalKebeles }}">{{ number_format($totalKebeles) }}</div>
        <div class="stat-meta">{{ t('Municipal kebeles') }}</div>
    </div>
</div>

<!-- Futuristic Chart.js Analytics Grid Section 1 -->
<div class="card-grid cols-2 gsap-chart-card" style="margin-bottom: 20px;">
    <div class="panel" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); padding: 16px;">
        <div style="font-weight: 700; font-size: 13.5px; color: var(--on-surface); margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
            <span style="display: flex; align-items: center; gap: 8px;">{!! icon('pie-chart', 16) !!} {{ t('Customers by Category Type') }}</span>
            <span class="badge badge-secondary">{{ count($byType) }} {{ t('Categories') }}</span>
        </div>
        <div style="height: 220px; position: relative;">
            <canvas id="typeChart"></canvas>
        </div>
    </div>

    <div class="panel" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); padding: 16px;">
        <div style="font-weight: 700; font-size: 13.5px; color: var(--on-surface); margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
            <span style="display: flex; align-items: center; gap: 8px;">{!! icon('bar-chart', 16) !!} {{ t('Customers by Account Status') }}</span>
            <span class="badge badge-success">{{ $totalCustomers }} {{ t('Total') }}</span>
        </div>
        <div style="height: 220px; position: relative;">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<!-- Futuristic Chart.js Analytics Grid Section 2 -->
<div class="card-grid cols-2 gsap-chart-card" style="margin-bottom: 20px;">
    <div class="panel" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); padding: 16px;">
        <div style="font-weight: 700; font-size: 13.5px; color: var(--on-surface); margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
            <span style="display: flex; align-items: center; gap: 8px;">{!! icon('map-pin', 16) !!} {{ t('Kebele Customer Density Overview') }}</span>
            <span class="badge badge-secondary">Top Kebeles</span>
        </div>
        <div style="height: 240px; position: relative;">
            <canvas id="kebeleChart"></canvas>
        </div>
    </div>

    <div class="panel" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); padding: 16px;">
        <div style="font-weight: 700; font-size: 13.5px; color: var(--on-surface); margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
            <span style="display: flex; align-items: center; gap: 8px;">{!! icon('line-chart', 16) !!} {{ t('Monthly Registration Trend (Last 12 Months)') }}</span>
            <span class="badge badge-primary">Growth Trend</span>
        </div>
        <div style="height: 240px; position: relative;">
            <canvas id="trendChart"></canvas>
        </div>
    </div>
</div>

<!-- Detailed Data Breakdown Table -->
<div class="panel gsap-section-card" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 20px;">
    <div style="height: 4px; background: var(--primary-container);"></div>
    <div class="panel-header" style="padding: 14px 20px; background: var(--surface-container-lowest); border-bottom: 1px solid var(--outline-variant); display: flex; justify-content: space-between; align-items: center;">
        <span style="font-weight: 700; font-size: 14px; color: var(--on-surface);">
            {{ $title }}
        </span>
        <span style="font-size: 12px; color: var(--text-muted);">
            {{ count($rows) }} {{ t('Kebele rows') }}
        </span>
    </div>

    <div class="scrollable-table">
        <div class="scroll-progress"><div class="scroll-progress-bar"></div></div>
        <div class="table-scroll-view">
            @if ($reportType === 'type')
                <table class="data-table compact" id="statsTable" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>{{ t('Kebele') }}</th>
                            <th>{{ t('Dhunfaa (Private)') }}</th>
                            <th>{{ t('Government') }}</th>
                            <th>{{ t('NGO') }}</th>
                            <th>{{ t('Commercial') }}</th>
                            <th>{{ t('Total Accounts') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @php $t1 = $t2 = $t3 = $t4 = $t5 = 0; @endphp
                    @foreach ($rows as $r)
                        @php
                            $t1 += $r->privateCount;
                            $t2 += $r->governmentCount;
                            $t3 += $r->nonGovernmentCount;
                            $t4 += $r->commercialCount;
                            $t5 += $r->total;
                        @endphp
                        <tr>
                            <td><strong style="color: var(--primary);">Kebele {{ $r->kebele }}</strong></td>
                            <td>{{ $r->privateCount }}</td>
                            <td>{{ $r->governmentCount }}</td>
                            <td>{{ $r->nonGovernmentCount }}</td>
                            <td>{{ $r->commercialCount }}</td>
                            <td><strong style="color: var(--on-surface);">{{ $r->total }}</strong></td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: var(--surface-container-low); font-weight: 700;">
                            <td>TOTAL SUMMARY</td>
                            <td>{{ $t1 }}</td>
                            <td>{{ $t2 }}</td>
                            <td>{{ $t3 }}</td>
                            <td>{{ $t4 }}</td>
                            <td style="color: var(--primary); font-size: 14px;">{{ $t5 }}</td>
                        </tr>
                    </tfoot>
                </table>
            @elseif ($reportType === 'status')
                <table class="data-table compact" id="statsTable" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>{{ t('Kebele') }}</th>
                            <th>{{ t('Active') }}</th>
                            <th>DC</th>
                            <th>{{ t('Updated') }}</th>
                            <th>{{ t('Deleted') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @php $t1 = $t2 = $t3 = $t4 = 0; @endphp
                    @foreach ($rows as $r)
                        @php
                            $t1 += $r->activeCount;
                            $t2 += $r->dcCount;
                            $t3 += $r->updatedCount;
                            $t4 += $r->deletedCount;
                        @endphp
                        <tr>
                            <td><strong style="color: var(--primary);">Kebele {{ $r->kebele }}</strong></td>
                            <td><span class="badge badge-success">{{ $r->activeCount }}</span></td>
                            <td><span class="badge badge-danger">{{ $r->dcCount }}</span></td>
                            <td><span class="badge badge-warning">{{ $r->updatedCount }}</span></td>
                            <td><span class="badge badge-secondary">{{ $r->deletedCount }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: var(--surface-container-low); font-weight: 700;">
                            <td>TOTAL SUMMARY</td>
                            <td style="color: var(--success);">{{ $t1 }}</td>
                            <td style="color: var(--danger);">{{ $t2 }}</td>
                            <td>{{ $t3 }}</td>
                            <td>{{ $t4 }}</td>
                        </tr>
                    </tfoot>
                </table>
            @else
                <table class="data-table compact" id="statsTable" style="width: 100%;">
                    <thead>
                        <tr>
                            <th rowspan="2">{{ t('Kebele') }}</th>
                            <th colspan="4" style="text-align: center; background: var(--surface-container-low);">Dhunfaa</th>
                            <th colspan="4" style="text-align: center; background: var(--surface-container-low);">Daldaltoota & Industry</th>
                            <th colspan="4" style="text-align: center; background: var(--surface-container-low);">Waajjira Motummaa</th>
                            <th colspan="4" style="text-align: center; background: var(--surface-container-low);">Waajjira Miti-Motummaa</th>
                        </tr>
                        <tr>
                            <th>Active</th><th>DC</th><th>Upd.</th><th>Del.</th>
                            <th>Active</th><th>DC</th><th>Upd.</th><th>Del.</th>
                            <th>Active</th><th>DC</th><th>Upd.</th><th>Del.</th>
                            <th>Active</th><th>DC</th><th>Upd.</th><th>Del.</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($rows as $r)
                        <tr>
                            <td><strong style="color: var(--primary);">Kebele {{ $r->kebele }}</strong></td>
                            <td>{{ $r->dhunfaaActive }}</td><td>{{ $r->dhunfaaDc }}</td><td>{{ $r->dhunfaaUpdated }}</td><td>{{ $r->dhunfaaDeleted }}</td>
                            <td>{{ $r->daldaltootaIndustryActive }}</td><td>{{ $r->daldaltootaIndustryDc }}</td><td>{{ $r->daldaltootaIndustryUpdated }}</td><td>{{ $r->daldaltootaIndustryDeleted }}</td>
                            <td>{{ $r->waajjiraMotummaaActive }}</td><td>{{ $r->waajjiraMotummaaDc }}</td><td>{{ $r->waajjiraMotummaaUpdated }}</td><td>{{ $r->waajjiraMotummaaDeleted }}</td>
                            <td>{{ $r->waajjiraMitiMotummaaActive }}</td><td>{{ $r->waajjiraMitiMotummaaDc }}</td><td>{{ $r->waajjiraMitiMotummaaUpdated }}</td><td>{{ $r->waajjiraMitiMotummaaDeleted }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

<!-- Collapsible Floating Quick Action Menu (FAB) -->
<div class="fab-wrapper">
    <div class="fab-menu" id="fabMenu">
        <button type="button" class="fab-item" onclick="window.print(); toggleFabMenu();">
            <span class="icon">{!! icon('print', 16) !!}</span>
            <span class="label">{{ t('Print Report') }}</span>
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
                        backgroundColor: ['#10B981', '#3B82F6', '#6366F1', '#FEA619', '#8B5CF6'],
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
                        backgroundColor: ['#10B981', '#EF4444', '#FEA619', '#64748B'],
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
                        backgroundColor: '#3B82F6',
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

        // 4. Registration Trend Smooth Line Chart
        const trendCtx = document.getElementById('trendChart');
        if (trendCtx) {
            const oldTrend = Chart.getChart(trendCtx);
            if (oldTrend) oldTrend.destroy();

            const ctx = trendCtx.getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, 200);
            gradient.addColorStop(0, 'rgba(16, 185, 129, 0.45)');
            gradient.addColorStop(1, 'rgba(16, 185, 129, 0.01)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trend.map(r => r.ym),
                    datasets: [{
                        label: 'New Customers',
                        data: trend.map(r => r.cnt),
                        borderColor: '#10B981',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        fill: true,
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
