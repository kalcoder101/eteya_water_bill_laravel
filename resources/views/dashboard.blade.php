@extends('layouts.app')

@section('content')

<div class="page-header-block" style="margin-bottom: var(--space-5);">
    <div class="page-info">
        <h2 style="margin: 0;">{{ t('Dashboard Overview') }}</h2>
        <p>{{ t('Welcome back') }}, {{ auth()->user()?->fullName() }} — {{ t('here is what is happening across the utility today.') }}</p>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card accent">
        <div class="stat-label">{{ t('Total Customers') }}</div>
        <div class="stat-value">{{ $totalCustomers }}</div>
        <div class="stat-meta">{{ t('Active') }} + {{ t('Disconnected (DC)') }}</div>
    </div>
    <div class="stat-card success">
        <div class="stat-label">{{ t('Active') }}</div>
        <div class="stat-value">{{ $activeCount }}</div>
        <div class="stat-meta">{{ $activePct }}% {{ t('of total') }}</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-label">{{ t('Disconnected (DC)') }}</div>
        <div class="stat-value">{{ $dcCount }}</div>
        <div class="stat-meta">{{ $totalCustomers - $activeCount - $dcCount }} {{ t('other') }}</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-label">{{ t('Unpaid Bills') }}</div>
        <div class="stat-value">{{ $unpaidBills }}</div>
        <div class="stat-meta">{{ $paidBills }} {{ t('paid') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">{{ t('Pending Complaints') }}</div>
        <div class="stat-value">{{ $pendingComplaints }}</div>
        <div class="stat-meta">{{ t('Reading Correction') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">{{ t('System Users') }}</div>
        <div class="stat-value">{{ $totalUsers }}</div>
        <div class="stat-meta">{{ t('All staff accounts') }}</div>
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
@endsection
