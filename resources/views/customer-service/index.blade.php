@extends('layouts.app')

@section('content')

<div class="page-header-block">
    <div class="page-info">
        <h2 style="margin: 0;">{{ t('Customer Service') }}</h2>
        <p>{{ t('Register, search, update and manage all water meter customers') }}</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openRegisterModal()">
            {!! icon('plus', 16) !!} {{ t('Register New Customer') }}
        </button>
    </div>
</div>

<div class="toolbar">
    <div class="segmented">
        <a class="{{ $filter==='all' ? 'active' : '' }}" href="?filter=all">{{ t('All Customers') }}</a>
        <a class="status-active {{ ($filter==='status' && $status==='Active') ? 'active status-active' : '' }}" href="?filter=status&status=Active">{!! icon('check', 12) !!} {{ t('Active') }}</a>
        <a class="status-dc {{ ($filter==='status' && $status==='DC') ? 'active status-dc' : '' }}" href="?filter=status&status=DC">{!! icon('x', 12) !!} DC</a>
        <a class="status-updated {{ ($filter==='status' && $status==='Updated') ? 'active status-updated' : '' }}" href="?filter=status&status=Updated">{!! icon('refresh', 12) !!} {{ t('Updated') }}</a>
        <a class="status-deleted {{ ($filter==='status' && $status==='Deleted') ? 'active status-deleted' : '' }}" href="?filter=status&status=Deleted">{!! icon('trash', 12) !!} {{ t('Deleted') }}</a>
    </div>
    <form method="get" action="" style="display:flex; gap:6px; align-items:center;">
        <input type="hidden" name="filter" value="search">
        <input type="text" name="search" placeholder="{{ t('Search by code / name / phone') }}" value="{{ $search }}" style="min-width: 200px;">
        <button type="submit" class="btn btn-sm btn-primary">{!! icon('search', 14) !!} {{ t('Search') }}</button>
    </form>
    <span style="flex:1"></span>
    <button class="btn btn-sm" onclick="openExcelImportModal()">{!! icon('upload', 14) !!} {{ t('Load From Excel') }}</button>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
    <div class="panel">
        <div class="panel-header">{!! icon('search', 16) !!} {{ t('Quick Customer Lookup') }}</div>
        <div class="panel-body">
            <div style="display:flex; gap:8px; align-items:flex-end;">
                <div class="form-group" style="flex:1;">
                    <label>{{ t('Customer Code') }}</label>
                    <input type="text" id="lookupCode" placeholder="e.g. ETY-0001" onkeydown="if(event.key==='Enter') lookupCustomer()">
                </div>
                <button class="btn btn-primary" onclick="lookupCustomer()">{!! icon('search', 16) !!} {{ t('Single Search') }}</button>
            </div>
            <div id="lookupResult" style="margin-top:12px;"></div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">{!! icon('database', 16) !!} {{ t('Bulk Operations') }}</div>
        <div class="panel-body">
            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:8px;">
                <button type="button" class="btn" onclick="newCodeFetch()">{!! icon('plus', 14) !!} {{ t('New Code Fetch') }}</button>
                <button type="button" class="btn" onclick="syncCustomerList()">{!! icon('sync', 14) !!} {{ t('Sync Customer List') }}</button>
                <button type="button" class="btn" onclick="exportCustomersCSV()">{!! icon('download', 14) !!} {{ t('Export to CSV') }}</button>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <span>{{ t('Registered Customers') }} ({{ count($customers) }} {{ t('shown') }} / {{ $totalCount }} {{ t('total') }} — {{ t('Active') }}: {{ $activeCount }} / DC: {{ $dcCount }})</span>
    </div>
    <div class="scrollable-table">
        <div class="scroll-progress"><div class="scroll-progress-bar"></div></div>
        <div class="table-scroll-view">
            <table class="data-table compact" id="customersTable">
                <thead>
                <tr>
                    <th>{{ t('Code') }}</th>
                    <th>{{ t('Full Name') }}</th>
                    <th>{{ t('Kebele') }}</th>
                    <th>{{ t('Phone') }}</th>
                    <th>{{ t('Meter Size') }}</th>
                    <th>{{ t('Customer Type') }}</th>
                    <th>{{ t('Payment') }}</th>
                    <th>{{ t('Branch') }}</th>
                    <th>{{ t('Status') }}</th>
                    <th>{{ t('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($customers as $c)
                <tr>
                    <td><strong>{{ $c->meter_serial }}</strong></td>
                    <td>{{ trim(($c->first_name ?? '').' '.($c->middle_name ?? '').' '.($c->last_name ?? '')) }}</td>
                    <td>{{ $c->kebele }}</td>
                    <td>{{ $c->phone_number }}</td>
                    <td>{{ $c->meter_size }}</td>
                    <td>{{ $c->customer_type }}</td>
                    <td>{{ $c->payment_way }}</td>
                    <td>{{ $c->customer_branch }}</td>
                    <td>
                        @if ($c->customer_status === 'Active')
                            <span class="badge badge-success">{{ t('Active') }}</span>
                        @elseif ($c->customer_status === 'DC')
                            <span class="badge badge-danger">DC</span>
                        @else
                            <span class="badge badge-default">{{ $c->customer_status }}</span>
                        @endif
                    </td>
                    <td style="display:flex; gap:6px; flex-wrap:wrap;">
                        <button type="button" class="btn btn-sm" onclick='editCustomer({{ json_encode($c->meter_serial) }})'>{!! icon('edit', 14) !!} {{ t('Edit') }}</button>
                        <button type="button" class="btn btn-sm btn-danger" onclick='deleteCustomer({{ json_encode($c->meter_serial) }}, {{ json_encode(trim(($c->first_name ?? '').' '.($c->middle_name ?? '').' '.($c->last_name ?? ''))) }})'>{!! icon('trash', 14) !!} {{ t('Delete') }}</button>
                    </td>
                </tr>
            @endforeach
            @if ($customers->isEmpty())
                <tr><td colspan="10" style="text-align:center; padding: 30px; color:#6b7280;">{{ t('No customers found') }}</td></tr>
            @endif
            </tbody>
        </table>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="registerModal">
    <div class="modal" style="max-width: 760px;">
        <div class="modal-header">
            <div class="modal-icon">{!! icon('plus', 20) !!}</div>
            <div class="modal-title">
                <h3>{{ t('Customer Registration Form') }}</h3>
                <div class="modal-subtitle">{{ t('Register a new water meter customer') }}</div>
            </div>
            <button class="close" onclick="closeModal('registerModal')">{!! icon('x', 18) !!}</button>
        </div>
        <div class="modal-body">
            <form id="registerForm">
                <div style="font-size:11px; font-weight:700; color:var(--gray-500); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:8px;">{{ t('Identity') }}</div>
                <div style="background:#fff; border:1px solid var(--cream-200); border-radius:8px; padding:6px 14px; margin-bottom:14px;">
                    <div class="field-row">
                        <label>{{ t('Customer Code') }} <span class="required">*</span></label>
                        <div style="display:flex; gap:6px;">
                            <input type="text" name="meterSerial" required style="flex:1;">
                            <button type="button" class="btn btn-sm" onclick="generateCode()">{!! icon('refresh', 12) !!} {{ t('Auto') }}</button>
                        </div>
                    </div>
                    <div class="field-row">
                        <label>{{ t('Kebele') }} <span class="required">*</span></label>
                        <input type="text" name="kebele" required placeholder="e.g. 01">
                    </div>
                    <div class="field-row">
                        <label>{{ t('First Name') }} <span class="required">*</span></label>
                        <input type="text" name="firstName" required>
                    </div>
                    <div class="field-row">
                        <label>{{ t('Middle Name') }}</label>
                        <input type="text" name="middleName">
                    </div>
                    <div class="field-row">
                        <label>{{ t('Last Name') }}</label>
                        <input type="text" name="lastName">
                    </div>
                    <div class="field-row">
                        <label>{{ t('Phone Number') }}</label>
                        <input type="text" name="phoneNumber" placeholder="+2519...">
                    </div>
                </div>

                <div style="font-size:11px; font-weight:700; color:var(--gray-500); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:8px; margin-top:14px;">{{ t('Meter Information') }}</div>
                <div style="background:#fff; border:1px solid var(--cream-200); border-radius:8px; padding:6px 14px; margin-bottom:14px;">
                    <div class="field-row">
                        <label>{{ t('Meter Size') }}</label>
                        <select name="meterSize" class="fancy">
                            @foreach ($meterSizes as $v)
                                <option value="{{ $v }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field-row">
                        <label>{{ t('Meter Number') }}</label>
                        <input type="number" name="meterNum" value="0">
                    </div>
                    <div class="field-row">
                        <label>{{ t('Serial Number (Bill #)') }}</label>
                        <input type="text" name="billNum" placeholder="SN-0001">
                    </div>
                    <div class="field-row">
                        <label>{{ t('Start Reading') }}</label>
                        <input type="number" step="0.01" name="startValue" value="0">
                    </div>
                    <div class="field-row">
                        <label>{{ t('Sold Date') }}</label>
                        <input type="date" name="soldDate" value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div style="font-size:11px; font-weight:700; color:var(--gray-500); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:8px; margin-top:14px;">{{ t('Classification') }}</div>
                <div style="background:#fff; border:1px solid var(--cream-200); border-radius:8px; padding:6px 14px;">
                    <div class="field-row">
                        <label>{{ t('Customer Type') }}</label>
                        <select name="customerType" class="fancy">
                            @foreach ($customerTypes as $v)
                                <option value="{{ $v }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field-row">
                        <label>{{ t('Payment Way') }}</label>
                        <select name="paymentWay" class="fancy">
                            @foreach ($paymentWays as $v)
                                <option value="{{ $v }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field-row">
                        <label>{{ t('Customer Branch') }}</label>
                        <select name="customerBranch" class="fancy">
                            @foreach ($branches as $v)
                                <option value="{{ $v }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field-row">
                        <label>{{ t('Status') }}</label>
                        <select name="customerStatus" class="fancy">
                            @foreach ($customerStatuses as $v)
                                <option value="{{ $v }}" @if($v==='Active') selected @endif>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field-row">
                        <label>{{ t('Reader Block') }}</label>
                        <input type="text" name="readerBlock" placeholder="Block-A">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closeModal('registerModal')">{{ t('Cancel') }}</button>
            <button class="btn btn-success" onclick="submitRegister()">{!! icon('check', 16) !!} {{ t('Register Customer') }}</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="editModal">
    <div class="modal" style="max-width: 900px;">
        <div class="modal-header">
            <div class="modal-icon">{!! icon('edit', 20) !!}</div>
            <div class="modal-title">
                <h3>{{ t('Edit Customer') }} — <span id="editCode"></span></h3>
                <div class="modal-subtitle">{{ t('Update customer information') }}</div>
            </div>
            <button class="close" onclick="closeModal('editModal')">{!! icon('x', 18) !!}</button>
        </div>
        <div class="modal-body">
            <div id="editInfo" style="margin-bottom: 14px; padding: 12px 16px; background: var(--cream-100); border-radius: 8px; border-left: 4px solid var(--lime-500);"></div>

            <div style="font-size:11px; font-weight:700; color:var(--gray-500); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:8px;">{{ t('Update Operations') }}</div>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:8px;">
                <button class="btn btn-sm" onclick="promptUpdate('update-customer-info', ['meterSerial','firstName','middleName','lastName','phoneNumber'], 'Update Customer Info')">{!! icon('file-text', 14) !!} Update Customer Info</button>
                <button class="btn btn-sm" onclick="promptUpdate('update-first-name', ['meterSerial','firstName'], 'Update First Name')">{!! icon('edit', 14) !!} Update First Name</button>
                <button class="btn btn-sm" onclick="promptUpdate('update-middle-name', ['meterSerial','middleName'], 'Update Middle Name')">{!! icon('edit', 14) !!} Update Middle Name</button>
                <button class="btn btn-sm" onclick="promptUpdate('update-last-name', ['meterSerial','lastName'], 'Update Last Name')">{!! icon('edit', 14) !!} Update Last Name</button>
                <button class="btn btn-sm" onclick="promptUpdate('update-phone-number', ['meterSerial','phoneNumber'], 'Update Phone Number')">{!! icon('phone', 14) !!} Update Phone Number</button>
                <button class="btn btn-sm" onclick="promptUpdate('update-kebele', ['meterSerial','kebele'], 'Update Kebele')">{!! icon('map-pin', 14) !!} Update Kebele</button>
                <button class="btn btn-sm" onclick="promptUpdate('update-customer-type', ['meterSerial','customerType'], 'Update Customer Type', {customerType: {{ json_encode($customerTypes) }} })">{!! icon('tag', 14) !!} Change Customer Type</button>
                <button class="btn btn-sm" onclick="promptUpdate('update-meter-size', ['meterSerial','meterSize','meterNum'], 'Update Meter Size', {meterSize: {{ json_encode($meterSizes) }} })">{!! icon('edit', 14) !!} Update Meter Size</button>
                <button type="button" class="btn btn-sm" onclick="promptUpdate('update-payment-way', ['meterSerial','paymentWay'], 'Update Payment Way', {paymentWay: {{ json_encode($paymentWays) }} })">{!! icon('credit-card', 14) !!} Update Payment Way</button>
                <button type="button" class="btn btn-sm" onclick="promptUpdate('update-customer-branch', ['meterSerial','customerBranch'], 'Update Branch', {customerBranch: {{ json_encode($branches) }} })">{!! icon('building', 14) !!} Update Customer Branch</button>
                <button type="button" class="btn btn-sm" onclick="promptUpdate('update-bill-num', ['meterSerial','billNum'], 'Update Bill Number')">{!! icon('receipt', 14) !!} Update Bill Number</button>
                <button type="button" class="btn btn-sm" onclick="promptUpdate('update-reader-block', ['meterSerial','readerBlock'], 'Update Reader Block')">{!! icon('alert', 14) !!} Update Reader Block</button>
                <button type="button" class="btn btn-sm" onclick="promptUpdate('update-start-reading', ['meterSerial','startValue'], 'Update Start Reading')">{!! icon('clock', 14) !!} Update Start Reading</button>

                <button type="button" class="btn btn-sm btn-warning" onclick="updateStatus('Updated')">{!! icon('tag', 14) !!} Mark as Updated</button>
                <button type="button" class="btn btn-sm btn-warning" onclick="updateStatus('DC')">{!! icon('zap', 14) !!} Disconnect (DC)</button>
                <button type="button" class="btn btn-sm btn-success" onclick="updateStatus('Active')">{!! icon('check', 14) !!} Re-Activate</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="updateStatus('Deleted')">{!! icon('x', 14) !!} Mark Deleted</button>
                <button type="button" class="btn btn-sm btn-success" onclick="syncOne()">{!! icon('sync', 14) !!} Sync This Customer</button>
                <button type="button" class="btn btn-sm" onclick="submitLocation()">{!! icon('map-pin', 14) !!} Submit GPS Location</button>
                <button type="button" class="btn btn-sm" onclick="meterOwnerTransfer()">{!! icon('sync', 14) !!} Meter Owner Transfer</button>
                <button type="button" class="btn btn-sm" onclick="changeNewMeter()">{!! icon('wrench', 14) !!} Change New Meter</button>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closeModal('editModal')">Close</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="excelModal">
    <div class="modal" style="max-width: 560px;">
        <div class="modal-header">
            <div class="modal-icon">{!! icon('upload', 20) !!}</div>
            <div class="modal-title">
                <h3>{{ t('Import Customers from Excel/CSV') }}</h3>
                <div class="modal-subtitle">{{ t('Bulk register customers from a spreadsheet') }}</div>
            </div>
            <button class="close" onclick="closeModal('excelModal')">{!! icon('x', 18) !!}</button>
        </div>
        <div class="modal-body">
            <div class="alert alert-info">
                <strong>{{ t('Required columns') }}:</strong>
                <code style="display:block; margin-top:6px; font-size:11px; word-break:break-all;">meterSerial, firstName, middleName, lastName, kebele, phoneNumber, meterSize, customerType, billNum, startValue, paymentWay, customerBranch, readerBlock</code>
            </div>
            <form id="excelForm" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>{{ t('CSV File') }}</label>
                    <input type="file" name="excelFile" accept=".csv,.xlsx" required>
                </div>
            </form>
            <div class="alert alert-warning" style="margin-top:12px;">
                <a href="{{ $baseUrl }}/sample-customer-template.csv" download>{!! icon('download', 14) !!} {{ t('Download CSV template') }}</a>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closeModal('excelModal')">{{ t('Cancel') }}</button>
            <button class="btn btn-success" onclick="submitExcel()">{!! icon('upload', 16) !!} {{ t('Register From Excel') }}</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="promptModal">
    <div class="modal" style="max-width: 500px;">
        <div class="modal-header">
            <div class="modal-icon">{!! icon('edit', 20) !!}</div>
            <div class="modal-title">
                <h3 id="promptTitle">{{ t('Update') }}</h3>
                <div class="modal-subtitle">{{ t('Enter new value below') }}</div>
            </div>
            <button class="close" onclick="closeModal('promptModal')">{!! icon('x', 18) !!}</button>
        </div>
        <div class="modal-body">
            <form id="promptForm">
                <div id="promptFields" style="background:#fff; border:1px solid var(--cream-200); border-radius:8px; padding:6px 14px;"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closeModal('promptModal')">{{ t('Cancel') }}</button>
            <button class="btn btn-success" onclick="submitPromptUpdate()">{!! icon('check', 16) !!} {{ t('Submit Update') }}</button>
        </div>
    </div>
</div>

<script>
let currentEditCode = null;
let promptEndpoint = null;
let promptFields = null;

function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }

function openRegisterModal() {
    document.getElementById('registerForm').reset();
    document.querySelector('[name=soldDate]').value = new Date().toISOString().slice(0,10);
    openModal('registerModal');
}

function openExcelImportModal() { openModal('excelModal'); }

function generateCode() {
    const kebele = document.querySelector('[name=kebele]').value || '00';
    fetch(apiUrl('active_customers/get-recent-code') + `?kebele=${encodeURIComponent(kebele)}`)
      .then(r => r.text())
      .then(code => document.querySelector('[name=meterSerial]').value = code);
}

function submitRegister() {
    const form = document.getElementById('registerForm');
    const fd = new FormData(form);
    const body = {};
    fd.forEach((v, k) => body[k] = v);
    body.meterNum = parseInt(body.meterNum || 0);
    body.startValue = parseFloat(body.startValue || 0);
    fetch(apiUrl('active_customers/add-active-customer'), {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(body),
    }).then(r => {
        if (r.status === 201) {
            showToast('Customer registered successfully', 'success');
            closeModal('registerModal');
            setTimeout(() => location.reload(), 800);
        } else throw new Error('Failed');
    }).catch(e => showToast('Failed to register customer', 'danger'));
}

function lookupCustomer() {
    const code = document.getElementById('lookupCode').value.trim();
    if (!code) return showToast('Enter a customer code', 'warning');
    fetch(apiUrl(`active_customers/get-single-customer/${encodeURIComponent(code)}`))
      .then(r => r.json())
      .then(rows => {
          const div = document.getElementById('lookupResult');
          if (!rows || !rows.length) {
              div.innerHTML = '<div class="alert alert-warning">No customer found.</div>';
              return;
          }
          const c = rows[0];
          div.innerHTML = `
            <table class="data-table">
              <tr><th>Code</th><td>${c.meter_serial}</td></tr>
              <tr><th>Name</th><td>${c.first_name} ${c.middle_name||''} ${c.last_name||''}</td></tr>
              <tr><th>Kebele</th><td>${c.kebele||''}</td></tr>
              <tr><th>Phone</th><td>${c.phone_number||''}</td></tr>
              <tr><th>Meter</th><td>${c.meter_size||''} (#${c.meter_num||0})</td></tr>
              <tr><th>Type</th><td>${c.customer_type||''}</td></tr>
              <tr><th>Branch</th><td>${c.customer_branch||''}</td></tr>
              <tr><th>Status</th><td>${c.customer_status}</td></tr>
              <tr><th>Start Reading</th><td>${c.start_value}</td></tr>
            </table>
            <button class="btn btn-primary btn-sm" type="button" style="margin-top:8px;" onclick='editCustomer(${JSON.stringify(c.meter_serial)})'>Edit Customer</button>
          `;
      });
}

function editCustomer(code) {
    currentEditCode = code;
    document.getElementById('editCode').textContent = code;
    fetch(apiUrl(`active_customers/get-active-customer/${encodeURIComponent(code)}`))
      .then(r => r.json())
      .then(c => {
          if (!c) return showToast('Customer not found', 'danger');
          document.getElementById('editInfo').innerHTML = `
            <strong>${c.first_name} ${c.middle_name||''} ${c.last_name||''}</strong><br>
            Kebele: ${c.kebele||'—'} | Phone: ${c.phone_number||'—'} | Type: ${c.customer_type||'—'} | Status: ${c.customer_status}
          `;
      });
    openModal('editModal');
}

function deleteCustomer(code, name) {
    if (!confirm(`Delete customer ${code} (${name})?`)) return;
    fetch(apiUrl(`active_customers/update-single-customer-status`) + `?meterSerial=${encodeURIComponent(code)}&customerStatus=Deleted`, {method:'PUT'})
      .then(r => r.text())
      .then(() => { showToast('Customer marked as Deleted', 'success'); setTimeout(()=>location.reload(), 800); });
}

function updateStatus(newStatus) {
    if (!currentEditCode) return;
    if (!confirm(`Change status to "${newStatus}"?`)) return;
    fetch(apiUrl(`active_customers/update-single-customer-status`) + `?meterSerial=${encodeURIComponent(currentEditCode)}&customerStatus=${encodeURIComponent(newStatus)}`, {method:'PUT'})
      .then(r => r.text())
      .then(() => { showToast(`Status updated to ${newStatus}`, 'success'); setTimeout(()=>location.reload(), 800); });
}

function syncOne() {
    if (!currentEditCode) return;
    fetch(apiUrl(`active_customers/update-sync-status`) + `?meterSerial=${encodeURIComponent(currentEditCode)}`, {method:'PUT'})
      .then(r => r.text())
      .then(() => showToast('Customer synced', 'success'));
}

function syncCustomerList() {
    if (!confirm('Sync all customers to server?')) return;
    fetch(apiUrl('active_customers/send-active-customers'), {method:'POST'})
      .then(r => r.json())
      .then(d => showToast(`Synced ${d.count} customers`, 'success'));
}

function newCodeFetch() {
    const kebele = prompt('Enter kebele number:');
    if (!kebele) return;
    fetch(apiUrl('active_customers/get-recent-code') + `?kebele=${encodeURIComponent(kebele)}`)
      .then(r => r.text())
      .then(code => showToast(`Next code for kebele ${kebele}: ${code}`, 'info'));
}

function promptUpdate(endpoint, fields, title, dropdowns={}) {
    if (!currentEditCode) return;
    promptEndpoint = endpoint;
    promptFields = fields;
    document.getElementById('promptTitle').textContent = title;
    const div = document.getElementById('promptFields');
    div.innerHTML = '';
    fields.forEach(f => {
        if (f === 'meterSerial') return;
        const group = document.createElement('div');
        group.className = 'form-group';
        group.style.marginBottom = '8px';
        const lbl = document.createElement('label');
        lbl.textContent = f.replace(/([A-Z])/g, ' $1').replace(/^./, s => s.toUpperCase());
        group.appendChild(lbl);
        let input;
        if (dropdowns[f]) {
            input = document.createElement('select');
            dropdowns[f].forEach(v => {
                const o = document.createElement('option');
                o.value = v; o.textContent = v;
                input.appendChild(o);
            });
        } else {
            input = document.createElement('input');
            input.type = (f.includes('Value') || f.includes('Num')) ? 'number' : 'text';
            input.step = '0.01';
        }
        input.name = f;
        input.style.width = '100%';
        group.appendChild(input);
        div.appendChild(group);
    });
    openModal('promptModal');
}

function submitPromptUpdate() {
    const form = document.getElementById('promptForm');
    const fd = new FormData(form);
    const params = new URLSearchParams();
    params.set('meterSerial', currentEditCode);
    fd.forEach((v, k) => params.set(k, v));
    fetch(apiUrl(`active_customers/${promptEndpoint}`) + `?${params.toString()}`, {method:'PUT'})
      .then(r => r.text())
      .then(() => {
          showToast('Update successful', 'success');
          closeModal('promptModal');
          setTimeout(()=>location.reload(), 800);
      });
}

function submitLocation() {
    if (!currentEditCode) return;
    if (!navigator.geolocation) return showToast('Geolocation not available', 'danger');
    navigator.geolocation.getCurrentPosition(pos => {
        const lat = pos.coords.latitude.toFixed(6);
        const lng = pos.coords.longitude.toFixed(6);
        fetch(apiUrl('meter_location/add-meter-location'), {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({customerCode: currentEditCode, latitudeVal: lat, longitudeVal: lng})
        }).then(r => {
            if (r.status === 201) showToast(`Location saved: ${lat}, ${lng}`, 'success');
            else showToast('Failed to save location', 'danger');
        });
    }, () => showToast('Cannot get location', 'danger'));
}

function meterOwnerTransfer() {
    const newOwner = prompt('Enter new owner full name (First Middle Last):');
    if (!newOwner) return;
    const parts = newOwner.trim().split(/\s+/);
    const params = new URLSearchParams({
        meterSerial: currentEditCode,
        firstName: parts[0] || '',
        middleName: parts[1] || '',
        lastName: parts.slice(2).join(' '),
        phoneNumber: ''
    });
    fetch(apiUrl(`active_customers/update-customer-info`) + `?${params.toString()}`, {method:'PUT'})
      .then(r => r.text())
      .then(() => { showToast('Owner transferred', 'success'); setTimeout(()=>location.reload(), 800); });
}

function changeNewMeter() {
    const newSerial = prompt('Enter new meter serial number:');
    if (!newSerial) return;
    const newSize = prompt('Enter new meter size (1/2", 3/4", 1", 1 and 1/2", 2"):', '1/2"');
    if (!newSize) return;
    const newNum = prompt('Enter new meter number (integer):', '1');
    const params = new URLSearchParams({meterSerial: currentEditCode, meterSize: newSize, meterNum: newNum || 1});
    fetch(apiUrl(`active_customers/update-meter-size`) + `?${params.toString()}`, {method:'PUT'})
      .then(r => r.text())
      .then(() => {
          const p2 = new URLSearchParams({meterSerial: currentEditCode, billNum: newSerial});
          fetch(apiUrl(`active_customers/update-bill-num`) + `?${p2.toString()}`, {method:'PUT'})
            .then(() => { showToast('New meter installed', 'success'); setTimeout(()=>location.reload(), 800); });
      });
}

function submitExcel() {
    const form = document.getElementById('excelForm');
    const fd = new FormData(form);
    fetch('{{ route('import.customers') }}', {method:'POST', body: fd, headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}})
      .then(r => r.json())
      .then(d => {
          if (d.error) { showToast(d.error, 'danger'); return; }
          showToast(`Imported ${d.imported} customers (${d.skipped} skipped)`, 'success');
          closeModal('excelModal');
          setTimeout(()=>location.reload(), 1000);
      });
}

function exportCustomersCSV() { window.location.href = '{{ route('export.customers') }}'; }

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.scrollable-table').forEach(wrapper => {
        const view = wrapper.querySelector('.table-scroll-view');
        const bar = wrapper.querySelector('.scroll-progress-bar');
        if (!view || !bar) return;
        const update = () => {
            const max = view.scrollHeight - view.clientHeight;
            const width = max > 0 ? (view.scrollTop / max) * 100 : 0;
            bar.style.width = width + '%';
        };
        view.addEventListener('scroll', update);
        update();
    });
});
</script>
@endsection
