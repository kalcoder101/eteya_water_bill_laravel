@extends('layouts.app')

@section('content')

<div class="page-header-block" style="margin-bottom: var(--space-5); display: flex; justify-content: space-between; align-items: center;">
    <div class="page-info">
        <h2 style="margin: 0; font-size: 22px; font-weight: 700; color: var(--text-strong);">{{ t('Dashboard Overview') }}</h2>
        <p style="margin-top: 4px; color: var(--text-muted);">{{ t('Welcome back') }}, <strong>{{ auth()->user()?->fullName() }}</strong> — {{ t('here is what is happening across the utility today.') }}</p>
    </div>
    <div class="page-actions" style="display: flex; gap: 10px;">
        <button type="button" class="btn btn-sm btn-primary" onclick="openModal('quickCmdModal')">
            {!! icon('search', 14) !!} <span>Quick Search</span> <kbd style="margin-left: 6px; padding: 2px 5px; background: rgba(255,255,255,0.25); border-radius: 4px; font-size: 10px;">Ctrl+K</kbd>
        </button>
    </div>
</div>

<div class="stat-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-4); margin-bottom: var(--space-6);">
    <div class="stat-card accent">
        <div class="stat-label">{{ t('Total Customers') }}</div>
        <div class="stat-value">{{ $totalCustomers }}</div>
        <div class="stat-meta">{{ t('Active') }} + {{ t('Disconnected (DC)') }}</div>
    </div>
    <div class="stat-card success">
        <div class="stat-label">{{ t('Active') }}</div>
        <div class="stat-value" style="color: var(--success);">{{ $activeCount }}</div>
        <div class="stat-meta">{{ $activePct }}% {{ t('of total registered') }}</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-label">{{ t('Disconnected (DC)') }}</div>
        <div class="stat-value" style="color: var(--danger);">{{ $dcCount }}</div>
        <div class="stat-meta">{{ $totalCustomers - $activeCount - $dcCount }} {{ t('other status') }}</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-label">{{ t('Unpaid Bills') }}</div>
        <div class="stat-value" style="color: var(--warning);">{{ $unpaidBills }}</div>
        <div class="stat-meta">{{ $paidBills }} {{ t('paid bills') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">{{ t('Pending Complaints') }}</div>
        <div class="stat-value">{{ $pendingComplaints }}</div>
        <div class="stat-meta">{{ t('Reading Correction') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">{{ t('System Users') }}</div>
        <div class="stat-value">{{ $totalUsers }}</div>
        <div class="stat-meta">{{ t('Staff Accounts') }}</div>
    </div>
</div>

<!-- Interactive Chart + Quick Actions Grid -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--space-4); margin-bottom: var(--space-6);">
    <div class="panel">
        <div class="panel-header">
            <span>{!! icon('statistics', 16) !!} Customer Status Distribution</span>
            <div class="actions">
                <span class="badge badge-success">Live Analytics</span>
            </div>
        </div>
        <div class="panel-body" style="height: 240px; position: relative;">
            <canvas id="dashboardStatusChart"></canvas>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">{!! icon('dashboard', 16) !!} Quick Operations</div>
        <div class="panel-body" style="display: flex; flex-direction: column; gap: 10px;">
            <a href="{{ route('customer-service.index') }}" class="btn" style="justify-content: flex-start; padding: 12px 16px;">
                {!! icon('plus', 16) !!} <span>Register New Customer</span>
            </a>
            <a href="{{ route('bills.index') }}" class="btn btn-primary" style="justify-content: flex-start; padding: 12px 16px;">
                {!! icon('receipt', 16) !!} <span>Calculate & Print Bills</span>
            </a>
            <a href="{{ route('customer-ledger.index') }}" class="btn" style="justify-content: flex-start; padding: 12px 16px;">
                {!! icon('ledger', 16) !!} <span>View Customer Ledger</span>
            </a>
            <a href="{{ route('reading-correction.index') }}" class="btn" style="justify-content: flex-start; padding: 12px 16px;">
                {!! icon('wrench', 16) !!} <span>Review Complaints</span>
            </a>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 2fr 1fr; gap:var(--space-4);">

<div class="panel">
    <div class="panel-header">
        <span>{!! icon('customers', 16) !!} {{ t('Recent Customers') }}</span>
        <div class="actions">
            <a href="{{ route('customer-service.index') }}" class="btn btn-sm">{{ t('View All') }} {!! icon('arrow-right', 14) !!}</a>
        </div>
    </div>
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Full Name</th>
                    <th>Kebele</th>
                    <th>Type</th>
                    <th>Phone</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($recentCustomers as $c)
                <tr>
                    <td><strong>{{ $c->meter_serial }}</strong></td>
                    <td>{{ trim(($c->first_name ?? '').' '.($c->middle_name ?? '').' '.($c->last_name ?? '')) }}</td>
                    <td>{{ $c->kebele }}</td>
                    <td>{{ $c->customer_type }}</td>
                    <td>{{ $c->phone_number }}</td>
                    <td>
                        @if ($c->customer_status === 'Active')
                            <span class="badge badge-success">Active</span>
                        @elseif ($c->customer_status === 'DC')
                            <span class="badge badge-danger">DC</span>
                        @else
                            <span class="badge badge-default">{{ $c->customer_status }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="panel">
    <div class="panel-header">{!! icon('clock', 16) !!} {{ t('Recent Activity') }}</div>
    <div style="padding: 6px 0;">
        @if ($recentAudit->isEmpty())
            <p style="color: var(--text-muted); font-size: 13px; text-align: center; padding: 32px 0;">No recent activity.</p>
        @else
            @foreach ($recentAudit as $a)
                <div style="padding: 14px 20px; border-bottom: 1px solid var(--indigo-border); display: flex; gap: 12px; align-items: flex-start;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--indigo-wash); color: var(--persian-indigo); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        {!! icon('check', 14) !!}
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="color: var(--text-strong); font-weight: 500; font-size: 13px; line-height: 1.4;">{{ $a->log_reason }}</div>
                        <div style="color: var(--text-muted); font-size: 11.5px; margin-top: 3px; display: flex; gap: 6px; align-items: center;">
                            <span>{!! icon('clock', 11) !!}</span>
                            <span>{{ substr($a->log_date, 5) }}</span>
                            <span style="opacity: 0.5;">•</span>
                            <span>{{ $a->done_by }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

</div>

<!-- Quick Command Search Modal -->
<div class="modal-backdrop v2" id="quickCmdModal" onclick="if(event.target===this) closeModal('quickCmdModal')">
    <div class="modal v2" style="max-width: 520px;">
        <div class="modal-header">
            <h3>Quick Command & Customer Search</h3>
            <button type="button" class="close-btn" onclick="closeModal('quickCmdModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group" style="margin-bottom: 16px;">
                <label>Search Customer Code or Module</label>
                <input type="text" id="cmdSearchInput" class="form-control" placeholder="Type ETY-0001, ledger, or customer..." oninput="runQuickCmdSearch(this.value)">
            </div>
            <div id="cmdSearchResults" style="max-height: 240px; overflow-y: auto; display: flex; flex-direction: column; gap: 6px;">
                <a href="{{ route('customer-service.index') }}" class="btn" style="justify-content: flex-start;">
                    {!! icon('customers', 16) !!} <span>Customer Service Management</span>
                </a>
                <a href="{{ route('customer-ledger.index') }}" class="btn" style="justify-content: flex-start;">
                    {!! icon('ledger', 16) !!} <span>Customer Ledger Reports</span>
                </a>
                <a href="{{ route('bills.index') }}" class="btn" style="justify-content: flex-start;">
                    {!! icon('receipt', 16) !!} <span>Bills & Receipt Printing</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('dashboardStatusChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Active Customers', 'Disconnected (DC)', 'Unpaid Bills', 'Pending Complaints'],
                datasets: [{
                    data: [{{ $activeCount }}, {{ $dcCount }}, {{ $unpaidBills }}, {{ $pendingComplaints }}],
                    backgroundColor: ['#16A34A', '#DC2626', '#D97706', '#4A3AB8'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { font: { family: 'Inter', size: 12 } } }
                }
            }
        });
    }
});

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
                {!! icon('ledger', 16) !!} View Ledger for "${q.toUpperCase()}"
            </a>
        `;
    }
}
</script>

@endsection
