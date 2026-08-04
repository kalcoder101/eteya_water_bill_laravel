@extends('layouts.app')

@section('content')
<div class="page-header-block">
    <div class="page-info">
        <h2>{{ t('Detail Statistics') }}</h2>
        <p>{{ $title }}</p>
    </div>
    <div class="page-actions">
        <div class="segmented">
            <a class="{{ $reportType==='typeStatus'?'active':'' }}" href="?report=typeStatus">{{ t('Type × Status') }}</a>
            <a class="{{ $reportType==='type'?'active':'' }}" href="?report=type">{{ t('By Type') }}</a>
            <a class="{{ $reportType==='status'?'active':'' }}" href="?report=status">{{ t('By Status') }}</a>
        </div>
        <button class="btn btn-sm" onclick="window.print()">{!! icon('print', 14) !!} {{ t('Print Out') }}</button>
    </div>
</div>

<div class="card-grid cols-4" style="margin-bottom: 14px;">
    <div class="kpi-mini">
        <div class="kpi-icon lime">{!! icon('customers', 18) !!}</div>
        <div>
            <div class="kpi-label">{{ t('Total Customers') }}</div>
            <div class="kpi-value">{{ $totalCustomers }}</div>
        </div>
    </div>
    <div class="kpi-mini">
        <div class="kpi-icon green">{!! icon('check', 18) !!}</div>
        <div>
            <div class="kpi-label">{{ t('Active') }}</div>
            <div class="kpi-value">{{ $byStatus['Active'] ?? 0 }}</div>
        </div>
    </div>
    <div class="kpi-mini">
        <div class="kpi-icon red">{!! icon('x', 18) !!}</div>
        <div>
            <div class="kpi-label">DC</div>
            <div class="kpi-value">{{ $byStatus['DC'] ?? 0 }}</div>
        </div>
    </div>
    <div class="kpi-mini">
        <div class="kpi-icon blue">{!! icon('map-pin', 18) !!}</div>
        <div>
            <div class="kpi-label">{{ t('Kebeles') }}</div>
            <div class="kpi-value">{{ $totalKebeles }}</div>
        </div>
    </div>
</div>

<div class="card-grid cols-2" style="margin-bottom: 14px;">
    <div class="chart-card">
        <div class="chart-title">{{ t('Customers by Type') }}</div>
        <div class="chart-subtitle">{{ t('Distribution across all customer types') }}</div>
        <div class="chart-canvas-wrap">
            <canvas id="typeChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <div class="chart-title">{{ t('Customers by Status') }}</div>
        <div class="chart-subtitle">Active vs DC vs Updated vs Deleted</div>
        <div class="chart-canvas-wrap">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<div class="card-grid cols-2" style="margin-bottom: 14px;">
    <div class="chart-card">
        <div class="chart-title">{{ t('Top 10 Kebeles') }}</div>
        <div class="chart-subtitle">{{ t('Customer count per kebele') }}</div>
        <div class="chart-canvas-wrap" style="height: 280px;">
            <canvas id="kebeleChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <div class="chart-title">{{ t('Registration Trend') }}</div>
        <div class="chart-subtitle">{{ t('New customers per month (last 12 months)') }}</div>
        <div class="chart-canvas-wrap" style="height: 280px;">
            <canvas id="trendChart"></canvas>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <span>{{ $title }}</span>
    </div>
    <div style="overflow-x:auto;">
        @if ($reportType === 'type')
            <table class="data-table compact" id="statsTable">
                <thead>
                    <tr>
                        <th>{{ t('Kebele') }}</th>
                        <th>{{ t('Dhunfaa') }}</th>
                        <th>{{ t('Govt') }}</th>
                        <th>{{ t('NGO') }}</th>
                        <th>{{ t('Commercial') }}</th>
                        <th>{{ t('Total') }}</th>
                    </tr>
                </thead>
                <tbody>
                @php
                    $t1 = $t2 = $t3 = $t4 = $t5 = 0;
                @endphp
                @foreach ($rows as $r)
                    @php
                        $t1 += $r->privateCount;
                        $t2 += $r->governmentCount;
                        $t3 += $r->nonGovernmentCount;
                        $t4 += $r->commercialCount;
                        $t5 += $r->total;
                    @endphp
                    <tr>
                        <td><strong>{{ $r->kebele }}</strong></td>
                        <td>{{ $r->privateCount }}</td>
                        <td>{{ $r->governmentCount }}</td>
                        <td>{{ $r->nonGovernmentCount }}</td>
                        <td>{{ $r->commercialCount }}</td>
                        <td><strong>{{ $r->total }}</strong></td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: var(--cream-100); font-weight: 700; color: var(--green-900);">
                        <td>TOTAL</td>
                        <td>{{ $t1 }}</td>
                        <td>{{ $t2 }}</td>
                        <td>{{ $t3 }}</td>
                        <td>{{ $t4 }}</td>
                        <td>{{ $t5 }}</td>
                    </tr>
                </tfoot>
            </table>
        @elseif ($reportType === 'status')
            <table class="data-table compact" id="statsTable">
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
                @php
                    $t1 = $t2 = $t3 = $t4 = 0;
                @endphp
                @foreach ($rows as $r)
                    @php
                        $t1 += $r->activeCount;
                        $t2 += $r->dcCount;
                        $t3 += $r->updatedCount;
                        $t4 += $r->deletedCount;
                    @endphp
                    <tr>
                        <td><strong>{{ $r->kebele }}</strong></td>
                        <td>{{ $r->activeCount }}</td>
                        <td>{{ $r->dcCount }}</td>
                        <td>{{ $r->updatedCount }}</td>
                        <td>{{ $r->deletedCount }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: var(--cream-100); font-weight: 700; color: var(--green-900);">
                        <td>TOTAL</td>
                        <td>{{ $t1 }}</td>
                        <td>{{ $t2 }}</td>
                        <td>{{ $t3 }}</td>
                        <td>{{ $t4 }}</td>
                    </tr>
                </tfoot>
            </table>
        @else
            <table class="data-table compact" id="statsTable">
                <thead>
                    <tr>
                        <th rowspan="2">{{ t('Kebele') }}</th>
                        <th colspan="4">Dhunfaa</th>
                        <th colspan="4">Daldaltoota fi Industry</th>
                        <th colspan="4">Waajjira Motummaa</th>
                        <th colspan="4">Waajjira Miti-Motummaa</th>
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
                        <td><strong>{{ $r->kebele }}</strong></td>
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

<script>
const byType = {{ json_encode($byType) }};
const byStatus = {{ json_encode($byStatus) }};
const byKebele = {{ json_encode($byKebele) }};
const trend = {{ json_encode($trend) }};

new Chart(document.getElementById('typeChart'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(byType),
        datasets: [{
            data: Object.values(byType),
            backgroundColor: ['#27187E', '#4A3AB8', '#6B5BD0', '#9D8FE0', '#C8BFF7'],
        }],
    },
    options: { responsive: true, maintainAspectRatio: false },
});

new Chart(document.getElementById('statusChart'), {
    type: 'bar',
    data: {
        labels: Object.keys(byStatus),
        datasets: [{
            label: 'Count',
            data: Object.values(byStatus),
            backgroundColor: ['#16A34A', '#DC2626', '#D97706', '#7B7896'],
        }],
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } },
});

new Chart(document.getElementById('kebeleChart'), {
    type: 'bar',
    data: {
        labels: byKebele.map(r => r.kebele),
        datasets: [{
            label: 'Customers',
            data: byKebele.map(r => r.cnt),
            backgroundColor: '#27187E',
        }],
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } },
});

new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: trend.map(r => r.ym),
        datasets: [{
            label: 'New Customers',
            data: trend.map(r => r.cnt),
            borderColor: '#27187E',
            backgroundColor: 'rgba(74, 58, 184, 0.18)',
            tension: 0.3,
            fill: true,
        }],
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } },
});
</script>
@endsection
