@extends('layouts.app')

@section('content')
<div class="page-header-block">
    <div class="page-info">
        <h2>{{ t('Customers Ledger') }}</h2>
        <p>{{ t('View billing history by customer and year') }}</p>
    </div>
    <div class="page-actions">
        @if (! empty($customer))
        <button class="btn btn-sm" onclick="window.print()">{!! icon('print', 14) !!} {{ t('Ledger Print') }}</button>
        <button class="btn btn-sm" onclick="exportLedger()">{!! icon('download', 14) !!} {{ t('Export CSV') }}</button>
        @endif
    </div>
</div>

<div class="panel" style="margin-bottom: 14px;">
    <div class="panel-body">
        <form method="get" action="{{ route('customer-ledger.index') }}" class="period-picker" style="width:100%;">
            <div class="field" style="flex:1; min-width: 220px;">
                <label>{{ t('Customer') }}</label>
                <input id="customerSearch" class="form-control" type="text" placeholder="{{ t('Search customer by code or name') }}" oninput="filterCustomerOptions()">
                <select id="customerSelect" name="meterSerial" class="fancy" onchange="this.form.submit()" style="width:100%; margin-top:8px;">
                    <option value="">— {{ t('Select customer') }} —</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->meter_serial }}" @if($meterSerial===$c->meter_serial) selected @endif>
                            {{ $c->meter_serial }} — {{ trim(($c->first_name ?? '').' '.($c->middle_name ?? '').' '.($c->last_name ?? '')) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>{{ t('Year') }}</label>
                <select name="year" class="fancy" onchange="this.form.submit()">
                    @foreach ($availableYears as $y)
                        <option value="{{ $y }}" @if((string)$y === (string)$year) selected @endif>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary">{!! icon('search', 16) !!} {{ t('Load') }}</button>
        </form>
    </div>
</div>

@if (empty($customer))
<div class="panel">
    <div class="empty-state">
        {!! icon('ledger', 64) !!}
        <div class="empty-title">{{ t('No customer selected') }}</div>
        <div class="empty-text">{{ t('Choose a customer above to view their billing ledger.') }}</div>
    </div>
</div>
@else

<div class="card-grid cols-4" style="margin-bottom: 14px;">
    <div class="kpi-mini">
        <div class="kpi-icon lime">{!! icon('file-text', 18) !!}</div>
        <div>
            <div class="kpi-label">{{ t('Bills') }}</div>
            <div class="kpi-value">{{ count($ledger) }}</div>
        </div>
    </div>
    <div class="kpi-mini">
        <div class="kpi-icon blue">{!! icon('water', 18) !!}</div>
        <div>
            <div class="kpi-label">{{ t('Total m³') }}</div>
            <div class="kpi-value">{{ number_format($totalConsumption, 1) }}</div>
        </div>
    </div>
    <div class="kpi-mini">
        <div class="kpi-icon green">{!! icon('check', 18) !!}</div>
        <div>
            <div class="kpi-label">{{ t('Paid') }}</div>
            <div class="kpi-value">{{ $paidBills }} <span style="font-size:11px; font-weight:500; color:var(--gray-500);">/ {{ number_format($paidTotal, 0) }}</span></div>
        </div>
    </div>
    <div class="kpi-mini">
        <div class="kpi-icon red">{!! icon('alert', 18) !!}</div>
        <div>
            <div class="kpi-label">{{ t('Unpaid') }}</div>
            <div class="kpi-value">{{ $unpaidBills }} <span style="font-size:11px; font-weight:500; color:var(--gray-500);">/ {{ number_format($unpaidTotal, 0) }}</span></div>
        </div>
    </div>
</div>

<div class="card-grid cols-3" style="margin-bottom: 14px;">
    <div class="panel" style="grid-column: span 2;">
        <div class="panel-header">{{ t('Billing History') }} — {{ $year }}</div>
        @if (empty($ledger))
            <div class="empty-state">
                {!! icon('file-text', 48) !!}
                <div class="empty-title">{{ t('No billing records') }}</div>
                <div class="empty-text">{{ t('No bills found for this customer in') }} {{ $year }}.</div>
            </div>
        @else
            <table class="data-table compact" style="width:100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ t('Month') }}</th>
                        <th>{{ t('Read Date') }}</th>
                        <th>{{ t('Prev R') }}</th>
                        <th>{{ t('Cur R') }}</th>
                        <th>m³</th>
                        <th>{{ t('Bill Cost') }}</th>
                        <th>{{ t('Meter') }}</th>
                        <th>{{ t('Svc') }}</th>
                        <th>{{ t('Fund') }}</th>
                        <th>{{ t('Total') }}</th>
                        <th>{{ t('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                @php
                    $prev = (float) ($customer->start_value ?? 0);
                    $i = 0;
                @endphp
                @foreach ($ledger as $row)
                    @php
                        $i++;
                        $cur = (float) ($row->current_reading ?? 0);
                        $use = $cur - $prev;
                    @endphp
                    <tr>
                        <td>{{ $i }}</td>
                        <td><strong>{{ $row->bill_month }}</strong></td>
                        <td>{{ $row->reading_date ?? '—' }}</td>
                        <td>{{ number_format($prev, 1) }}</td>
                        <td>{{ number_format($cur, 1) }}</td>
                        <td>{{ number_format($use, 1) }}</td>
                        <td>{{ number_format($row->consumption_cost, 0) }}</td>
                        <td>{{ number_format($row->meter_price, 0) }}</td>
                        <td>{{ number_format($row->service_price, 0) }}</td>
                        <td>{{ number_format($row->state_price, 0) }}</td>
                        <td><strong>{{ number_format($row->total_monthly_cost, 0) }}</strong></td>
                        <td>
                            @if ($row->payment_status === 'Paid')
                                <span class="badge badge-success">{{ t('Paid') }}</span>
                            @else
                                <span class="badge badge-danger">{{ t('Unpaid') }}</span>
                            @endif
                        </td>
                    </tr>
                    @php $prev = $cur; @endphp
                @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: var(--cream-100); font-weight: 700; color: var(--green-900);">
                        <td colspan="10" style="text-align:right;">{{ t('Grand Total') }}:</td>
                        <td>{{ number_format($grandTotal, 0) }} ETB</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>

    <div>
        <div class="panel" style="margin-bottom: 14px;">
            <div class="panel-header">{{ t('Customer Info') }}</div>
            <div class="panel-body">
                <div style="display:flex; flex-direction:column; gap: 10px;">
                    <div>
                        <div style="font-size:11px; color:var(--gray-500); text-transform:uppercase; letter-spacing:0.04em; font-weight:600;">{{ t('Code') }}</div>
                        <div style="font-size:15px; font-weight:700; color:var(--green-900);">{{ $customer->meter_serial }}</div>
                    </div>
                    <div>
                        <div style="font-size:11px; color:var(--gray-500); text-transform:uppercase; letter-spacing:0.04em; font-weight:600;">{{ t('Name') }}</div>
                        <div style="font-size:14px; font-weight:600;">{{ trim(($customer->first_name ?? '').' '.($customer->middle_name ?? '').' '.($customer->last_name ?? '')) }}</div>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                        <div>
                            <div style="font-size:11px; color:var(--gray-500); text-transform:uppercase; letter-spacing:0.04em; font-weight:600;">{{ t('Kebele') }}</div>
                            <div style="font-size:13px; font-weight:600;">{{ $customer->kebele ?? '—' }}</div>
                        </div>
                        <div>
                            <div style="font-size:11px; color:var(--gray-500); text-transform:uppercase; letter-spacing:0.04em; font-weight:600;">{{ t('Phone') }}</div>
                            <div style="font-size:13px; font-weight:600;">{{ $customer->phone_number ?? '—' }}</div>
                        </div>
                        <div>
                            <div style="font-size:11px; color:var(--gray-500); text-transform:uppercase; letter-spacing:0.04em; font-weight:600;">{{ t('Meter Size') }}</div>
                            <div style="font-size:13px; font-weight:600;">{{ $customer->meter_size ?? '—' }}</div>
                        </div>
                        <div>
                            <div style="font-size:11px; color:var(--gray-500); text-transform:uppercase; letter-spacing:0.04em; font-weight:600;">{{ t('Status') }}</div>
                            <div>
                                @if ($customer->customer_status === 'Active')
                                    <span class="badge badge-success">{{ t('Active') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ $customer->customer_status }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">{{ t('Update Start Reading') }}</div>
            <div class="panel-body">
                <div class="form-inline">
                    <div class="form-group" style="flex:1;">
                        <label>{{ t('Start Reading') }}</label>
                        <input type="number" step="0.01" id="startReading" value="{{ $customer->start_value }}">
                    </div>
                    <button class="btn btn-primary" onclick="updateStartReading()">{!! icon('check', 14) !!} {{ t('Update') }}</button>
                </div>
                <div style="margin-top:10px;">
                    <button class="btn btn-warning btn-sm btn-block" onclick="disconnectCustomer()">{!! icon('x', 14) !!} {{ t('Disconnect (DC)') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateStartReading() {
    const code = {{ json_encode($meterSerial) }};
    const val = document.getElementById('startReading').value;
    fetch(apiUrl(`active_customers/update-start-reading`) + `?meterSerial=${encodeURIComponent(code)}&startValue=${val}`, {method:'PUT'})
      .then(r => r.text())
      .then(() => showToast('Start reading updated', 'success'));
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
    const query = document.getElementById('customerSearch').value.toLowerCase();
    document.querySelectorAll('#customerSelect option').forEach(opt => {
        if (!opt.value) return opt.hidden = false;
        const text = opt.textContent.toLowerCase();
        opt.hidden = query && !text.includes(query);
    });
}
</script>
@endif
@endsection
