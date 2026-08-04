@extends('layouts.app')

@section('content')

<div class="page-header-block">
    <div class="page-info">
        <h2 style="margin: 0;">{{ t('Bills & Printing') }}</h2>
        <p>{{ t('Generate and print customer water bills') }} — <span class="badge badge-primary">{{ $year }} {{ $month }}</span></p>
    </div>
    <div class="page-actions">
        <form method="get" action="" class="period-picker">
            <div class="field">
                <label>{{ t('Year') }}</label>
                <select name="year" class="fancy" onchange="this.form.submit()">
                    @foreach ($years as $y)
                        <option value="{{ $y }}" @if((string)$y===(string)$year) selected @endif>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>{{ t('Month') }}</label>
                <select name="month" class="fancy" onchange="this.form.submit()">
                    @foreach ($months as $m)
                        <option value="{{ $m }}" @if($m===$month) selected @endif>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

<div class="card-grid cols-4" style="margin-bottom: var(--space-4);">
    <div class="kpi-mini">
        <div class="kpi-icon lime">{!! icon('receipt', 18) !!}</div>
        <div>
            <div class="kpi-label">{{ t('Total Bills') }}</div>
            <div class="kpi-value">{{ count($bills) }}</div>
        </div>
    </div>
    <div class="kpi-mini">
        <div class="kpi-icon green">{!! icon('check', 18) !!}</div>
        <div>
            <div class="kpi-label">{{ t('Paid') }}</div>
            <div class="kpi-value">{{ $paidCount }} <span style="font-size:11px; font-weight:500; color:var(--text-muted);">/ {{ number_format($paidAmount, 0) }} ETB</span></div>
        </div>
    </div>
    <div class="kpi-mini">
        <div class="kpi-icon red">{!! icon('alert', 18) !!}</div>
        <div>
            <div class="kpi-label">{{ t('Unpaid') }}</div>
            <div class="kpi-value">{{ $unpaidCount }} <span style="font-size:11px; font-weight:500; color:var(--text-muted);">/ {{ number_format($unpaidAmount, 0) }} ETB</span></div>
        </div>
    </div>
    <div class="kpi-mini">
        <div class="kpi-icon yellow">{!! icon('zap', 18) !!}</div>
        <div>
            <div class="kpi-label">{{ t('Total Amount') }}</div>
            <div class="kpi-value">{{ number_format($totalAmount, 0) }} <span style="font-size:11px; font-weight:500; color:var(--text-muted);">ETB</span></div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <span>{!! icon('receipt', 16) !!} {{ t('Bills for') }} {{ $year }} {{ $month }}</span>
        <div class="actions">
            <button class="btn btn-sm" onclick="exportBillsCSV()">{!! icon('download', 14) !!} {{ t('Export CSV') }}</button>
        </div>
    </div>
    @if ($bills->isEmpty())
        <div class="empty-state">
            {!! icon('receipt', 48) !!}
            <div class="empty-title">{{ t('No bills found') }}</div>
            <div class="empty-text">{{ t('Click "Calculate Bills" above to generate bills for this period.') }}</div>
        </div>
    @else
        <div style="overflow-x:auto;">
        <table class="data-table compact" style="width:100%;">
            <thead>
                <tr>
                    <th>{{ t('Code') }}</th>
                    <th>{{ t('Customer') }}</th>
                    <th>{{ t('Cons.') }}</th>
                    <th>{{ t('Bill') }}</th>
                    <th>{{ t('Meter') }}</th>
                    <th>{{ t('Svc') }}</th>
                    <th>{{ t('Pen.') }}</th>
                    <th>{{ t('Comm.') }}</th>
                    <th>{{ t('Fund') }}</th>
                    <th>{{ t('Dep.') }}</th>
                    <th>{{ t('Total') }}</th>
                    <th>{{ t('Status') }}</th>
                    <th>{{ t('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($bills as $b)
                <tr>
                    <td><strong>{{ $b->meter_serial }}</strong></td>
                    <td>{{ $b->customer ? trim(($b->customer->first_name ?? '').' '.($b->customer->middle_name ?? '').' '.($b->customer->last_name ?? '')) : $b->full_name }}</td>
                    <td>{{ number_format($b->consumption, 1) }}</td>
                    <td>{{ number_format($b->consumption_cost, 0) }}</td>
                    <td>{{ number_format($b->meter_price, 0) }}</td>
                    <td>{{ number_format($b->service_price, 0) }}</td>
                    <td>{{ number_format($b->penalty_cost, 0) }}</td>
                    <td>{{ number_format($b->community_cost, 0) }}</td>
                    <td>{{ number_format($b->state_price, 0) }}</td>
                    <td>{{ number_format($b->deposited_cost, 0) }}</td>
                    <td><strong>{{ number_format($b->total_monthly_cost, 0) }}</strong></td>
                    <td>
                        @if ($b->payment_status === 'Paid')
                            <span class="badge badge-success">{{ t('Paid') }}</span>
                        @else
                            <span class="badge badge-danger">{{ t('Unpaid') }}</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <a href="{{ route('bills.print', ['id' => $b->bill_finance_id]) }}" target="_blank" class="btn btn-sm btn-primary" title="{{ t('Print Bill') }}">{!! icon('print', 14) !!}</a>
                            @if ($b->payment_status !== 'Paid')
                                <a href="{{ route('bills.mark-paid', ['id' => $b->bill_finance_id]) }}" class="btn btn-sm btn-success" title="{{ t('Mark Paid') }}">{!! icon('check', 14) !!}</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="10" style="text-align:right; padding: 12px 14px;">{{ t('Total') }}:</td>
                    <td style="color: var(--persian-indigo);">{{ number_format($totalAmount, 0) }} ETB</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
        </div>
    @endif
</div>

<script>
function calculateBills() {
    confirmDialog(
        '{{ t('Calculate Bills') }}',
        '{{ t('This will generate bill records for all active customers based on their readings for') }} {{ $year }} {{ $month }}. {{ t('Continue') }}?',
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
    const year = '{{ $year }}';
    const month = '{{ $month }}';
    window.location.href = `{{ route('export.bills') }}?year=${year}&month=${encodeURIComponent(month)}`;
}
</script>
@endsection
