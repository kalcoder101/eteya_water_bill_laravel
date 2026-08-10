@extends('layouts.app')

@section('content')

<!-- Page Header -->
<div class="gsap-hero flex flex-wrap items-end justify-between gap-4 mb-6">
    <div class="min-w-0">
        <h2 class="m-0 text-[22px] font-bold tracking-tight text-slate-900 flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-700 inline-flex items-center justify-center shrink-0">{!! icon('wrench', 20) !!}</span>
            <span>{{ t('Reading Correction') }}</span>
        </h2>
        <p class="mt-2 text-[13px] text-slate-500">{{ t('Submit and process meter-reading complaints') }}</p>
    </div>
    <div class="segmented bg-slate-100 p-1">
        <a class="{{ $view==='all'?'active':'' }}" href="?view=all">{{ t('All') }}</a>
        <a class="{{ $view==='daily'?'active':'' }}" href="?view=daily&date={{ date('Y-m-d') }}">{{ t('Daily') }}</a>
        <a class="{{ $view==='monthly'?'active':'' }}" href="?view=monthly&year={{ get_setting('current_bill_year', date('Y')) }}&month={{ $months[0] }}">{{ t('Monthly') }}</a>
        <a class="{{ $view==='annual'?'active':'' }}" href="?view=annual&year={{ get_setting('current_bill_year', date('Y')) }}">{{ t('Annual') }}</a>
        <a class="{{ $view==='personal'?'active':'' }}" href="?view=personal">{{ t('Personal') }}</a>
    </div>
</div>

@if ($view === 'personal')
<div class="bg-white border border-slate-200 rounded-xl shadow-card p-3 mb-4">
    <form method="get" action="" class="flex flex-wrap gap-2 items-center">
        <input type="hidden" name="view" value="personal">
        <input type="text" name="customerCode" placeholder="{{ t('Customer Code') }}" value="{{ request()->get('customerCode', '') }}"
               class="flex-1 min-w-[220px] px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
        <x-button type="submit" variant="primary" icon="search">
            {{ t('Search') }}
        </x-button>
    </form>
</div>
@endif

<!-- KPI Mini Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="gsap-stat-card gsap-hover-card flex items-center gap-4 p-4 bg-white border border-slate-200 rounded-xl shadow-card">
        <div class="w-11 h-11 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">{!! icon('file-text', 20) !!}</div>
        <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ t('Total') }}</div>
            <div class="text-xl font-bold text-slate-900 font-mono tabular-nums" data-gsap-counter data-target-val="{{ count($complaints) }}">{{ count($complaints) }}</div>
        </div>
    </div>
    <div class="gsap-stat-card gsap-hover-card flex items-center gap-4 p-4 bg-white border border-slate-200 rounded-xl shadow-card">
        <div class="w-11 h-11 rounded-lg bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0">{!! icon('clock', 20) !!}</div>
        <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ t('Pending') }}</div>
            <div class="text-xl font-bold text-slate-900 font-mono tabular-nums text-amber-600" data-gsap-counter data-target-val="{{ $pendingCount }}">{{ $pendingCount }}</div>
        </div>
    </div>
    <div class="gsap-stat-card gsap-hover-card flex items-center gap-4 p-4 bg-white border border-slate-200 rounded-xl shadow-card">
        <div class="w-11 h-11 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">{!! icon('check', 20) !!}</div>
        <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ t('Approved') }}</div>
            <div class="text-xl font-bold text-slate-900 font-mono tabular-nums text-emerald-600" data-gsap-counter data-target-val="{{ $approvedCount }}">{{ $approvedCount }}</div>
        </div>
    </div>
    <div class="gsap-stat-card gsap-hover-card flex items-center gap-4 p-4 bg-white border border-slate-200 rounded-xl shadow-card">
        <div class="w-11 h-11 rounded-lg bg-rose-500/10 text-rose-600 flex items-center justify-center shrink-0">{!! icon('x', 20) !!}</div>
        <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ t('Rejected') }}</div>
            <div class="text-xl font-bold text-slate-900 font-mono tabular-nums text-rose-600" data-gsap-counter data-target-val="{{ $rejectedCount }}">{{ $rejectedCount }}</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-[1fr_1.5fr] gap-5 items-start">

<!-- Reading Correction Form Card -->
<div class="gsap-section-card bg-white border border-slate-200 rounded-xl shadow-card overflow-hidden">
    <div class="h-1 bg-emerald-600"></div>
    <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-gradient-to-br from-slate-50 to-white">
        <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">{!! icon('wrench', 20) !!}</div>
        <div>
            <h3 class="m-0 text-sm font-bold text-slate-900">{{ t('Reading Correction Form') }}</h3>
            <div class="text-xs text-slate-500 mt-0.5">{{ t('Submit a new meter-reading complaint') }}</div>
        </div>
    </div>
    <div class="p-5">
        <form id="complaintForm">
            <div class="mb-4">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Customer Code') }} <span class="text-rose-600">*</span></label>
                <input type="text" id="customerCode" name="customerCode" required placeholder="ETY-0001"
                       class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
            </div>
            <div class="mb-4">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Year') }} <span class="text-rose-600">*</span></label>
                <input type="text" name="readingYear" value="{{ get_setting('current_bill_year', date('Y')) }}" required
                       class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
            </div>
            <div class="mb-4">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Reading Month') }} <span class="text-rose-600">*</span></label>
                <select name="readingMonth" class="fancy w-full" required>
                    @foreach ($months as $m)
                        <option value="{{ $m }}">{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-2">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Complain Date') }} <span class="text-rose-600">*</span></label>
                <input type="text" name="complainDateTime" value="{{ date('Y-m-d H:i:s') }}" required
                       class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
            </div>
        </form>
    </div>
    <div class="px-5 py-4 bg-slate-50 border-t border-slate-100">
        <x-button type="button" onclick="submitComplaint()" class="w-full justify-center" icon="send" variant="soft">
            {{ t('Send Complain') }}
        </x-button>
    </div>
</div>

<!-- Complaints List -->
<div>
    <div class="bg-white border border-slate-200 rounded-xl shadow-card overflow-hidden">
        <div class="h-1 bg-emerald-600"></div>
        <div class="flex items-center justify-between gap-3 px-5 py-3.5 border-b border-slate-100">
            <span class="font-bold text-sm text-slate-900">{{ t('Complaints') }} ({{ count($complaints) }})</span>
        </div>
        <div class="p-3 max-h-[700px] overflow-y-auto space-y-3">
            @if ($complaints->isEmpty())
                <div class="text-center py-10 px-6 text-slate-500">
                    <div class="text-slate-300 mb-3">{!! icon('file-text', 48) !!}</div>
                    <div class="text-sm font-semibold text-slate-700">{{ t('No complaints') }}</div>
                    <div class="text-xs mt-1">{{ t('Submit a complaint using the form on the left.') }}</div>
                </div>
            @else
                @foreach ($complaints as $c)
                    @php
                        $statusKey = strtolower($c->correction_status);
                        $border = match($statusKey) { 'approved' => 'border-l-emerald-500', 'rejected' => 'border-l-rose-500', default => 'border-l-amber-500' };
                    @endphp
                    <div class="complaint-card border border-slate-200 border-l-4 {{ $border }} rounded-lg bg-white p-4 shadow-card hover:shadow-hover transition-shadow">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-[13px] font-bold text-slate-900">{{ $c->customer_code }} — {{ $c->reading_month }} {{ $c->reading_year }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ t('Submitted') }}: {{ $c->complain_date_time }} &middot; {{ t('By') }}: {{ $c->sending_department }}</div>
                            </div>
                            @if ($c->correction_status === 'Approved')
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider shrink-0">{!! icon('check', 11) !!} {{ t('Approved') }}</span>
                            @elseif ($c->correction_status === 'Rejected')
                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 text-rose-800 border border-rose-300 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider shrink-0">{!! icon('x', 11) !!} {{ t('Rejected') }}</span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 text-amber-800 border border-amber-300 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider shrink-0">{{ t('Pending') }}</span>
                            @endif
                        </div>
                        <div class="grid grid-cols-2 gap-3 mt-3 border-t border-slate-100 pt-3">
                            <div>
                                <div class="text-[11px] text-slate-500 font-semibold">{{ t('New Reading') }}</div>
                                <div class="text-[13px] font-bold text-slate-900 font-mono tabular-nums">{{ $c->new_reading }}</div>
                            </div>
                            <div>
                                <div class="text-[11px] text-slate-500 font-semibold">{{ t('Approved By') }}</div>
                                <div class="text-[13px] font-bold text-slate-900">{{ $c->approved_name }}</div>
                            </div>
                        </div>
                        @if ($c->correction_status === 'Pending')
                        <div class="mt-3 flex gap-2">
                            <x-button variant="primary" size="sm" icon="check" type="button" onclick="approveComplaint({{ $c->id }}, '{{ e($c->customer_code) }}', '{{ e($c->complain_date_time) }}')">
                                {{ t('Approve') }}
                            </x-button>
                            <x-button variant="danger" size="sm" icon="x" type="button" onclick="rejectComplaint('{{ e($c->customer_code) }}', '{{ e($c->complain_date_time) }}')">
                                {{ t('Reject') }}
                            </x-button>
                        </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

</div>

<!-- Approve Reading Correction Modal -->
<div class="modal-backdrop" id="approveModal">
    <div class="modal v2 bg-white rounded-2xl shadow-2xl w-full max-w-[420px] overflow-hidden flex flex-col" style="max-height: 90vh;">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-gradient-to-br from-slate-50 to-white">
            <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">{!! icon('check', 20) !!}</div>
            <div class="flex-1">
                <h3 class="m-0 text-sm font-bold text-slate-900">{{ t('Approve Reading Correction') }}</h3>
                <div class="text-xs text-slate-500 mt-0.5">{{ t('Enter the corrected meter reading value') }}</div>
            </div>
            <button class="close w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition" onclick="closeModal('approveModal')">{!! icon('x', 18) !!}</button>
        </div>
        <div class="p-5">
            <input type="hidden" id="approveId">
            <input type="hidden" id="approveCode">
            <input type="hidden" id="approveDate">
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Corrected Reading Value') }} <span class="text-rose-600">*</span></label>
                <input type="number" step="0.01" id="correctedValue" placeholder="e.g. 105.5"
                       class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
            </div>
        </div>
        <div class="flex items-center justify-end gap-2 px-5 py-4 bg-slate-50 border-t border-slate-100">
            <x-button variant="secondary" type="button" onclick="closeModal('approveModal')">
                {{ t('Cancel') }}
            </x-button>
            <x-button variant="primary" icon="check" type="button" onclick="confirmApprove()">
                {{ t('Approve') }}
            </x-button>
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
