@extends('layouts.app')

@section('content')
<div class="page-header-block">
    <div class="page-info">
        <h2>{{ t('Reading Correction') }}</h2>
        <p>{{ t('Submit and process meter-reading complaints') }}</p>
    </div>
    <div class="page-actions">
        <div class="segmented">
            <a class="{{ $view==='all'?'active':'' }}" href="?view=all">{{ t('All') }}</a>
            <a class="{{ $view==='daily'?'active':'' }}" href="?view=daily&date={{ date('Y-m-d') }}">{{ t('Daily') }}</a>
            <a class="{{ $view==='monthly'?'active':'' }}" href="?view=monthly&year={{ get_setting('current_bill_year', date('Y')) }}&month={{ $months[0] }}">{{ t('Monthly') }}</a>
            <a class="{{ $view==='annual'?'active':'' }}" href="?view=annual&year={{ get_setting('current_bill_year', date('Y')) }}">{{ t('Annual') }}</a>
            <a class="{{ $view==='personal'?'active':'' }}" href="?view=personal">{{ t('Personal') }}</a>
        </div>
    </div>
</div>

@if ($view === 'personal')
<div class="toolbar" style="margin-bottom: 14px;">
    <form method="get" action="" style="display:flex; gap:8px; align-items:center;">
        <input type="hidden" name="view" value="personal">
        <input type="text" name="customerCode" placeholder="{{ t('Customer Code') }}" value="{{ request()->get('customerCode', '') }}" style="min-width: 220px;">
        <button class="btn btn-sm btn-primary">{!! icon('search', 14) !!} {{ t('Search') }}</button>
    </form>
</div>
@endif

<div class="card-grid cols-4" style="margin-bottom: 14px;">
    <div class="kpi-mini"><div class="kpi-icon lime">{!! icon('file-text', 18) !!}</div><div><div class="kpi-label">{{ t('Total') }}</div><div class="kpi-value">{{ count($complaints) }}</div></div></div>
    <div class="kpi-mini"><div class="kpi-icon yellow">{!! icon('clock', 18) !!}</div><div><div class="kpi-label">{{ t('Pending') }}</div><div class="kpi-value">{{ $pendingCount }}</div></div></div>
    <div class="kpi-mini"><div class="kpi-icon green">{!! icon('check', 18) !!}</div><div><div class="kpi-label">{{ t('Approved') }}</div><div class="kpi-value">{{ $approvedCount }}</div></div></div>
    <div class="kpi-mini"><div class="kpi-icon red">{!! icon('x', 18) !!}</div><div><div class="kpi-label">{{ t('Rejected') }}</div><div class="kpi-value">{{ $rejectedCount }}</div></div></div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1.5fr; gap:14px;">

<div class="form-card">
    <div class="form-card-header">
        {!! icon('wrench', 22) !!}
        <div>
            <h3>{{ t('Reading Correction Form') }}</h3>
            <div class="subtitle">{{ t('Submit a new meter-reading complaint') }}</div>
        </div>
    </div>
    <div class="form-card-body">
        <form id="complaintForm">
            <div class="field-row">
                <label>{{ t('Customer Code') }} <span class="required">*</span></label>
                <input type="text" id="customerCode" name="customerCode" required placeholder="ETY-0001">
            </div>
            <div class="field-row">
                <label>{{ t('Year') }} <span class="required">*</span></label>
                <input type="text" name="readingYear" value="{{ get_setting('current_bill_year', date('Y')) }}" required>
            </div>
            <div class="field-row">
                <label>{{ t('Reading Month') }} <span class="required">*</span></label>
                <select name="readingMonth" class="fancy" required>
                    @foreach ($months as $m)
                        <option value="{{ $m }}">{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field-row">
                <label>{{ t('Complain Date') }} <span class="required">*</span></label>
                <input type="text" name="complainDateTime" value="{{ date('Y-m-d H:i:s') }}" required>
            </div>
        </form>
    </div>
    <div class="form-card-footer">
        <button type="button" class="btn btn-warning" onclick="submitComplaint()">{!! icon('send', 16) !!} {{ t('Send Complain') }}</button>
    </div>
</div>

<div>
    <div class="panel">
        <div class="panel-header">
            <span>{{ t('Complaints') }} ({{ count($complaints) }})</span>
        </div>
        <div class="panel-body" style="padding: 12px; max-height: 700px; overflow-y: auto;">
            @if ($complaints->isEmpty())
                <div class="empty-state">
                    {!! icon('file-text', 48) !!}
                    <div class="empty-title">{{ t('No complaints') }}</div>
                    <div class="empty-text">{{ t('Submit a complaint using the form on the left.') }}</div>
                </div>
            @else
                @foreach ($complaints as $c)
                    <div class="complaint-card {{ strtolower($c->correction_status) }}">
                        <div class="complaint-header">
                            <div>
                                <div class="complaint-title">{{ $c->customer_code }} — {{ $c->reading_month }} {{ $c->reading_year }}</div>
                                <div class="complaint-meta">{{ t('Submitted') }}: {{ $c->complain_date_time }} · {{ t('By') }}: {{ $c->sending_department }}</div>
                            </div>
                            @if ($c->correction_status === 'Approved')
                                <span class="badge badge-success">{{ t('Approved') }}</span>
                            @elseif ($c->correction_status === 'Rejected')
                                <span class="badge badge-danger">{{ t('Rejected') }}</span>
                            @else
                                <span class="badge badge-warning">{{ t('Pending') }}</span>
                            @endif
                        </div>
                        <div class="complaint-body">
                            <div class="meta-item"><div class="label">{{ t('New Reading') }}</div><div class="value">{{ $c->new_reading }}</div></div>
                            <div class="meta-item"><div class="label">{{ t('Approved By') }}</div><div class="value">{{ $c->approved_name }}</div></div>
                        </div>
                        @if ($c->correction_status === 'Pending')
                        <div style="margin-top: 10px; display: flex; gap: 6px;">
                            <button class="btn btn-sm btn-success" onclick="approveComplaint({{ $c->id }}, '{{ e($c->customer_code) }}', '{{ e($c->complain_date_time) }}')">{!! icon('check', 14) !!} {{ t('Approve') }}</button>
                            <button class="btn btn-sm btn-danger" onclick="rejectComplaint('{{ e($c->customer_code) }}', '{{ e($c->complain_date_time) }}')">{!! icon('x', 14) !!} {{ t('Reject') }}</button>
                        </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

</div>

<div class="modal-backdrop" id="approveModal">
    <div class="modal" style="max-width: 420px;">
        <div class="modal-header">
            <div class="modal-icon">{!! icon('check', 20) !!}</div>
            <div class="modal-title">
                <h3>{{ t('Approve Reading Correction') }}</h3>
                <div class="modal-subtitle">{{ t('Enter the corrected meter reading value') }}</div>
            </div>
            <button class="close" onclick="closeModal('approveModal')">{!! icon('x', 18) !!}</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="approveId">
            <input type="hidden" id="approveCode">
            <input type="hidden" id="approveDate">
            <div class="field-row" style="grid-template-columns: 1fr;">
                <label>{{ t('Corrected Reading Value') }} <span class="required">*</span></label>
                <input type="number" step="0.01" id="correctedValue" placeholder="e.g. 105.5">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closeModal('approveModal')">{{ t('Cancel') }}</button>
            <button class="btn btn-success" onclick="confirmApprove()">{!! icon('check', 16) !!} {{ t('Approve') }}</button>
        </div>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }

function submitComplaint() {
    const form = document.getElementById('complaintForm');
    const fd = new FormData(form);
    const body = {
        customerCode: fd.get('customerCode'),
        readingYear: fd.get('readingYear'),
        readingMonth: fd.get('readingMonth'),
        complainDateTime: fd.get('complainDateTime'),
        sendingDepartment: '{{ auth()->user()?->fullName() ?? 'Customer Service' }}',
        correctionStatus: 'Pending',
        newReading: 'NotInserted',
        approvedName: 'Pending',
        syncStatus: 'New',
    };
    if (!body.customerCode || !body.readingYear || !body.readingMonth) {
        showToast('Please fill all required fields', 'warning'); return;
    }
    fetch(apiUrl('reading_correction/add-reading-correction'), {
        method: 'POST', headers: {'Content-Type':'application/json'},
        body: JSON.stringify(body),
    }).then(r => {
        if (r.status === 201) { showToast('Complaint submitted successfully', 'success'); setTimeout(() => location.reload(), 1200); }
        else throw new Error('Failed');
    }).catch(e => showToast('Failed to submit complaint', 'error'));
}

function approveComplaint(id, code, date) {
    document.getElementById('approveId').value = id;
    document.getElementById('approveCode').value = code;
    document.getElementById('approveDate').value = date;
    document.getElementById('correctedValue').value = '';
    openModal('approveModal');
}

function confirmApprove() {
    const code = document.getElementById('approveCode').value;
    const date = document.getElementById('approveDate').value;
    const val  = document.getElementById('correctedValue').value;
    if (!val) { showToast('Enter corrected value', 'warning'); return; }
    const fullName = '{{ auth()->user()?->fullName() ?? 'System Admin' }}';
    const params1 = new URLSearchParams({newReading: val, customerCode: code, complainDateTime: date});
    const params2 = new URLSearchParams({approvedName: fullName, customerCode: code, complainDateTime: date});
    const params3 = new URLSearchParams({customerCode: code, complainDateTime: date, correctionStatus: 'Approved'});
    Promise.all([
        fetch(apiUrl(`reading_correction/update-new-reading`) + `?${params1.toString()}`, {method:'PUT'}),
        fetch(apiUrl(`reading_correction/update-approved-name`) + `?${params2.toString()}`, {method:'PUT'}),
        fetch(apiUrl(`reading_correction/update-customer-complain`) + `?${params3.toString()}`, {method:'PUT'}),
    ]).then(() => { showToast('Complaint approved', 'success'); setTimeout(() => location.reload(), 1200); });
}

function rejectComplaint(code, date) {
    confirmDialog('Reject this complaint?', 'This will mark the complaint as Rejected.', 'danger')
      .then(ok => {
          if (!ok) return;
          const fullName = '{{ auth()->user()?->fullName() ?? 'System Admin' }}';
          const params2 = new URLSearchParams({approvedName: fullName, customerCode: code, complainDateTime: date});
          const params3 = new URLSearchParams({customerCode: code, complainDateTime: date, correctionStatus: 'Rejected'});
          Promise.all([
              fetch(apiUrl(`reading_correction/update-approved-name`) + `?${params2.toString()}`, {method:'PUT'}),
              fetch(apiUrl(`reading_correction/update-customer-complain`) + `?${params3.toString()}`, {method:'PUT'}),
          ]).then(() => { showToast('Complaint rejected', 'info'); setTimeout(() => location.reload(), 1200); });
      });
}
</script>
@endsection
