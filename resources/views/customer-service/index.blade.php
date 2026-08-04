@extends('layouts.app')

@section('content')

<!-- Page Header & Banner -->
<div class="page-header-block gsap-hero">
    <div class="page-info">
        <div style="font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">
            {{ t('Operations') }} &bull; {{ t('Customer Registry') }}
        </div>
        <h2 style="margin: 0; display: flex; align-items: center; gap: 10px;">
            {!! icon('users', 24) !!} {{ t('Customer Service Management') }}
        </h2>
        <p style="margin-top: 4px; color: var(--text-muted);">{{ t('Register, search, update and manage all water meter customers across Kebeles') }}</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openRegisterModal()" style="box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);">
            {!! icon('plus', 16) !!} {{ t('Register New Customer') }}
        </button>
    </div>
</div>

<!-- KPI Stat Cards Bar -->
<div class="card-grid cols-4" style="margin-bottom: 20px;">
    <div class="stat-card accent gsap-stat-card gsap-hover-card">
        <div class="stat-label">{{ t('Total Registered') }}</div>
        <div class="stat-value" data-gsap-counter data-target-val="{{ $totalCount }}">{{ number_format($totalCount) }}</div>
        <div class="stat-meta">{{ t('Water Meter Accounts') }}</div>
    </div>
    <div class="stat-card success gsap-stat-card gsap-hover-card">
        <div class="stat-label">{{ t('Active Accounts') }}</div>
        <div class="stat-value" style="color: var(--success);" data-gsap-counter data-target-val="{{ $activeCount }}">{{ number_format($activeCount) }}</div>
        <div class="stat-meta">{{ number_format(($totalCount > 0 ? ($activeCount/$totalCount)*100 : 0), 1) }}% {{ t('connected') }}</div>
    </div>
    <div class="stat-card danger gsap-stat-card gsap-hover-card">
        <div class="stat-label">{{ t('Disconnected (DC)') }}</div>
        <div class="stat-value" style="color: var(--danger);" data-gsap-counter data-target-val="{{ $dcCount }}">{{ number_format($dcCount) }}</div>
        <div class="stat-meta">{{ t('Cut off accounts') }}</div>
    </div>
    <div class="stat-card warning gsap-stat-card gsap-hover-card">
        <div class="stat-label">{{ t('Updated / Pending') }}</div>
        <div class="stat-value" style="color: var(--warning);" data-gsap-counter data-target-val="{{ max(0, $totalCount - $activeCount - $dcCount) }}">{{ number_format(max(0, $totalCount - $activeCount - $dcCount)) }}</div>
        <div class="stat-meta">{{ t('Requires verification') }}</div>
    </div>
</div>

<!-- EOS Chart.js Analytics Grid -->
<div class="card-grid cols-2 gsap-chart-card" style="margin-bottom: 20px;">
    <div class="panel" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); padding: 16px;">
        <div style="font-weight: 700; font-size: 13.5px; color: var(--on-surface); margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
            <span style="display: flex; align-items: center; gap: 8px;">{!! icon('pie-chart', 16) !!} {{ t('Customer Status Distribution') }}</span>
            <span class="badge badge-secondary">{{ $totalCount }} {{ t('Total') }}</span>
        </div>
        <div style="height: 180px; position: relative;">
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    <div class="panel" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); padding: 16px;">
        <div style="font-weight: 700; font-size: 13.5px; color: var(--on-surface); margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
            <span style="display: flex; align-items: center; gap: 8px;">{!! icon('bar-chart', 16) !!} {{ t('Branch Coverage Overview') }}</span>
            <span class="badge badge-success">4 {{ t('Branches') }}</span>
        </div>
        <div style="height: 180px; position: relative;">
            <canvas id="branchChart"></canvas>
        </div>
    </div>
</div>

<!-- Toolbar & Segmented Filter Bar -->
<div class="toolbar" style="background: var(--surface-container-lowest); padding: 12px 16px; border-radius: var(--r-lg); border: 1px solid var(--outline-variant); margin-bottom: 20px; gap: 12px;">
    <div class="segmented">
        <a class="{{ $filter==='all' ? 'active' : '' }}" href="?filter=all">{{ t('All Customers') }} <span class="badge badge-secondary">{{ $totalCount }}</span></a>
        <a class="status-active {{ ($filter==='status' && $status==='Active') ? 'active status-active' : '' }}" href="?filter=status&status=Active">{!! icon('check', 12) !!} {{ t('Active') }} <span class="badge badge-success">{{ $activeCount }}</span></a>
        <a class="status-dc {{ ($filter==='status' && $status==='DC') ? 'active status-dc' : '' }}" href="?filter=status&status=DC">{!! icon('x', 12) !!} DC <span class="badge badge-danger">{{ $dcCount }}</span></a>
        <a class="status-updated {{ ($filter==='status' && $status==='Updated') ? 'active status-updated' : '' }}" href="?filter=status&status=Updated">{!! icon('refresh', 12) !!} {{ t('Updated') }}</a>
        <a class="status-deleted {{ ($filter==='status' && $status==='Deleted') ? 'active status-deleted' : '' }}" href="?filter=status&status=Deleted">{!! icon('trash', 12) !!} {{ t('Deleted') }}</a>
    </div>

    <form method="get" action="" style="display:flex; gap:6px; align-items:center; flex: 1; max-width: 360px;">
        <input type="hidden" name="filter" value="search">
        <div style="position: relative; width: 100%;">
            <input type="text" name="search" class="form-control" placeholder="{{ t('Search code, name, phone...') }}" value="{{ $search }}" style="padding-left: 32px;">
            <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); opacity: 0.6;">{!! icon('search', 14) !!}</span>
        </div>
        <button type="submit" class="btn btn-primary">{!! icon('search', 14) !!}</button>
    </form>

    <div style="display: flex; gap: 8px; align-items: center; margin-left: auto;">
        <button class="btn btn-sm" onclick="openExcelImportModal()">{!! icon('upload', 14) !!} {{ t('Import Excel') }}</button>
        <button class="btn btn-sm" onclick="exportCustomersCSV()">{!! icon('download', 14) !!} {{ t('Export CSV') }}</button>
    </div>
</div>

<!-- Two Column Panel (Quick Lookup & Bulk Actions) -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
    <div class="panel" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); overflow: hidden;">
        <div class="panel-header" style="background: var(--surface-container-low); padding: 12px 18px; border-bottom: 1px solid var(--outline-variant); font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 8px; color: var(--on-surface);">
            {!! icon('search', 16) !!} {{ t('Quick Customer Lookup') }}
        </div>
        <div class="panel-body" style="padding: 16px;">
            <div style="display:flex; gap:8px; align-items:flex-end;">
                <div class="form-group" style="flex:1; margin:0;">
                    <label style="font-size: 12px; font-weight: 600;">{{ t('Customer Meter Code') }}</label>
                    <input type="text" id="lookupCode" class="form-control" placeholder="e.g. ETY-0001" onkeydown="if(event.key==='Enter') lookupCustomer()">
                </div>
                <button class="btn btn-primary" onclick="lookupCustomer()">{!! icon('search', 16) !!} {{ t('Lookup') }}</button>
            </div>
            <div id="lookupResult" style="margin-top:12px;"></div>
        </div>
    </div>

    <div class="panel" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); overflow: hidden;">
        <div class="panel-header" style="background: var(--surface-container-low); padding: 12px 18px; border-bottom: 1px solid var(--outline-variant); font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 8px; color: var(--on-surface);">
            {!! icon('database', 16) !!} {{ t('Bulk Tools & Utility Fetch') }}
        </div>
        <div class="panel-body" style="padding: 16px;">
            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:10px;">
                <button type="button" class="btn" onclick="newCodeFetch()">{!! icon('plus', 14) !!} {{ t('Next Code') }}</button>
                <button type="button" class="btn" onclick="syncCustomerList()">{!! icon('sync', 14) !!} {{ t('Sync Database') }}</button>
                <button type="button" class="btn" onclick="exportCustomersCSV()">{!! icon('download', 14) !!} {{ t('CSV Export') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Main Registered Customers Data Table -->
<div class="panel" style="background: var(--surface-container-lowest); border: 1px solid var(--outline-variant); border-radius: var(--r-lg); overflow: hidden; box-shadow: var(--shadow-sm); position: relative;">
    <div style="height: 4px; background: var(--primary-container);"></div>
    <div class="panel-header" style="padding: 14px 20px; background: var(--surface-container-lowest); border-bottom: 1px solid var(--outline-variant); display: flex; justify-content: space-between; align-items: center;">
        <span style="font-weight: 700; font-size: 14px; color: var(--on-surface);">
            {{ t('Registered Customers Registry') }}
            <span class="badge badge-secondary" style="margin-left: 8px;">{{ count($customers) }} {{ t('shown') }} / {{ $totalCount }} {{ t('total') }}</span>
        </span>
        <span style="font-size: 12px; color: var(--text-muted);">
            Active: <strong style="color: var(--success);">{{ $activeCount }}</strong> &bull; DC: <strong style="color: var(--danger);">{{ $dcCount }}</strong>
        </span>
    </div>
    <div class="scrollable-table">
        <div class="scroll-progress"><div class="scroll-progress-bar"></div></div>
        <div class="table-scroll-view">
            <table class="data-table compact" id="customersTable">
                <thead>
                    <tr>
                        <th>{{ t('Code') }}</th>
                        <th>{{ t('Customer Details') }}</th>
                        <th>{{ t('Kebele / Phone') }}</th>
                        <th>{{ t('Meter Specs') }}</th>
                        <th>{{ t('Type & Payment') }}</th>
                        <th>{{ t('Branch') }}</th>
                        <th>{{ t('Status') }}</th>
                        <th style="text-align: right;">{{ t('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($customers as $c)
                    <tr>
                        <td><span style="font-family: monospace; font-weight: 700; font-size: 13px; color: var(--primary); background: var(--surface-container-low); padding: 4px 8px; border-radius: var(--r-sm); border: 1px solid var(--outline-variant);">{{ $c->meter_serial }}</span></td>
                        <td>
                            <div style="font-weight: 700; color: var(--on-surface); font-size: 13.5px;">{{ trim(($c->first_name ?? '').' '.($c->middle_name ?? '').' '.($c->last_name ?? '')) }}</div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Bill #: {{ $c->bill_num ?? '—' }} &bull; Block: {{ $c->reader_block ?? '—' }}</div>
                        </td>
                        <td>
                            <div>{{ $c->kebele ? 'Kebele '.$c->kebele : '—' }}</div>
                            <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">{{ $c->phone_number ?? '—' }}</div>
                        </td>
                        <td>
                            <div><strong>{{ $c->meter_size ?? '1/2"' }}</strong></div>
                            <div style="font-size: 11.5px; color: var(--text-muted);">Start: {{ number_format($c->start_value ?? 0, 1) }} m³</div>
                        </td>
                        <td>
                            <div>{{ $c->customer_type ?? 'Domestic' }}</div>
                            <div style="font-size: 11.5px; color: var(--text-muted);">{{ $c->payment_way ?? 'Postpaid' }}</div>
                        </td>
                        <td>{{ $c->customer_branch ?? 'Main' }}</td>
                        <td>
                            @if ($c->customer_status === 'Active')
                                <span class="badge badge-success">{!! icon('check', 12) !!} {{ t('Active') }}</span>
                            @elseif ($c->customer_status === 'DC')
                                <span class="badge badge-danger">{!! icon('x', 12) !!} DC</span>
                            @else
                                <span class="badge badge-warning">{!! icon('refresh', 12) !!} {{ $c->customer_status }}</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div style="display:inline-flex; gap:6px;">
                                <a href="{{ route('customer-ledger.index') }}?meterSerial={{ urlencode($c->meter_serial) }}" class="btn btn-sm" title="View Financial Ledger">{!! icon('book-open', 14) !!}</a>
                                <button type="button" class="btn btn-sm" onclick='editCustomer({{ json_encode($c->meter_serial) }})'>{!! icon('edit', 14) !!} {{ t('Edit') }}</button>
                                <button type="button" class="btn btn-sm btn-danger" onclick='deleteCustomer({{ json_encode($c->meter_serial) }}, {{ json_encode(trim(($c->first_name ?? '').' '.($c->middle_name ?? '').' '.($c->last_name ?? ''))) }})'>{!! icon('trash', 14) !!}</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
                @if ($customers->isEmpty())
                    <tr><td colspan="8" style="text-align:center; padding: 40px; color:var(--text-muted);">{{ t('No customer records found in the database') }}</td></tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Collapsible Floating Quick Action Menu (FAB) -->
<div class="fab-wrapper">
    <div class="fab-menu" id="fabMenu">
        <button type="button" class="fab-item" onclick="openRegisterModal(); toggleFabMenu();">
            <span class="icon">{!! icon('plus', 16) !!}</span>
            <span class="label">{{ t('Register New Customer') }}</span>
        </button>
        <button type="button" class="fab-item" onclick="openExcelImportModal(); toggleFabMenu();">
            <span class="icon">{!! icon('upload', 16) !!}</span>
            <span class="label">{{ t('Import Excel (CSV)') }}</span>
        </button>
        <button type="button" class="fab-item" onclick="syncCustomerList(); toggleFabMenu();">
            <span class="icon">{!! icon('sync', 16) !!}</span>
            <span class="label">{{ t('Sync Database') }}</span>
        </button>
        <button type="button" class="fab-item" onclick="exportCustomersCSV(); toggleFabMenu();">
            <span class="icon">{!! icon('download', 16) !!}</span>
            <span class="label">{{ t('Export CSV') }}</span>
        </button>
    </div>
    <button type="button" class="fab-trigger-btn" onclick="toggleFabMenu()" title="Quick Actions">
        <span class="fab-icon-main">{!! icon('plus', 22) !!}</span>
    </button>
</div>

<!-- Multi-Step Registration Modal -->
<div class="modal-backdrop v2" id="registerModal">
    <div class="modal v2" style="max-width: 780px;">
        <div class="modal-header">
            <div class="modal-icon">{!! icon('plus', 20) !!}</div>
            <div class="modal-title">
                <h3>{{ t('Customer Registration Form') }}</h3>
                <div class="modal-subtitle">{{ t('Register a new water meter customer in the system') }}</div>
            </div>
            <button class="close" onclick="closeModal('registerModal')">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Step Wizard Nav Tabs -->
            <div class="step-wizard-nav">
                <div class="step-wizard-item active" id="tab-step-1" onclick="switchRegStep(1)">
                    <span class="step-num">1</span> <span>Identity & Personal</span>
                </div>
                <div class="step-wizard-item" id="tab-step-2" onclick="switchRegStep(2)">
                    <span class="step-num">2</span> <span>Meter & Reading</span>
                </div>
                <div class="step-wizard-item" id="tab-step-3" onclick="switchRegStep(3)">
                    <span class="step-num">3</span> <span>Tariff & Branch</span>
                </div>
            </div>

            <form id="registerForm">
                <!-- STEP 1: IDENTITY -->
                <div id="reg-step-1-content" class="reg-step-pane">
                    <div style="background:var(--surface-container-low); border:1px solid var(--outline-variant); border-radius:var(--r-lg); padding:16px;">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>{{ t('Customer Code') }} <span class="required">*</span></label>
                                <div style="display:flex; gap:6px;">
                                    <input type="text" name="meterSerial" required class="form-control" placeholder="ETY-0001">
                                    <button type="button" class="btn btn-sm" onclick="generateCode()">{!! icon('refresh', 12) !!} {{ t('Auto') }}</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>{{ t('Kebele') }} <span class="required">*</span></label>
                                <input type="text" name="kebele" required class="form-control" placeholder="e.g. 01">
                            </div>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>{{ t('First Name') }} <span class="required">*</span></label>
                                <input type="text" name="firstName" required class="form-control" placeholder="e.g. Abebe">
                            </div>
                            <div class="form-group">
                                <label>{{ t('Middle Name') }}</label>
                                <input type="text" name="middleName" class="form-control" placeholder="e.g. Kebede">
                            </div>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>{{ t('Last Name') }}</label>
                                <input type="text" name="lastName" class="form-control" placeholder="e.g. Tadesse">
                            </div>
                            <div class="form-group">
                                <label>{{ t('Phone Number') }}</label>
                                <input type="text" name="phoneNumber" class="form-control" placeholder="+251911223344">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: METER SPECS -->
                <div id="reg-step-2-content" class="reg-step-pane" style="display:none;">
                    <div style="background:var(--surface-container-low); border:1px solid var(--outline-variant); border-radius:var(--r-lg); padding:16px;">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>{{ t('Meter Size') }}</label>
                                <select name="meterSize" class="fancy">
                                    @foreach ($meterSizes as $v)
                                        <option value="{{ $v }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>{{ t('Meter Number') }}</label>
                                <input type="number" name="meterNum" value="0" class="form-control">
                            </div>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>{{ t('Serial Number (Bill #)') }}</label>
                                <input type="text" name="billNum" placeholder="SN-0001" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>{{ t('Start Reading') }} (m³)</label>
                                <input type="number" step="0.01" name="startValue" value="0" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{ t('Sold Date') }}</label>
                            <input type="date" name="soldDate" value="{{ date('Y-m-d') }}" class="form-control">
                        </div>
                    </div>
                </div>

                <!-- STEP 3: CLASSIFICATION -->
                <div id="reg-step-3-content" class="reg-step-pane" style="display:none;">
                    <div style="background:var(--surface-container-low); border:1px solid var(--outline-variant); border-radius:var(--r-lg); padding:16px;">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>{{ t('Customer Type') }}</label>
                                <select name="customerType" class="fancy">
                                    @foreach ($customerTypes as $v)
                                        <option value="{{ $v }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>{{ t('Payment Way') }}</label>
                                <select name="paymentWay" class="fancy">
                                    @foreach ($paymentWays as $v)
                                        <option value="{{ $v }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>{{ t('Customer Branch') }}</label>
                                <select name="customerBranch" class="fancy">
                                    @foreach ($branches as $v)
                                        <option value="{{ $v }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>{{ t('Status') }}</label>
                                <select name="customerStatus" class="fancy">
                                    @foreach ($customerStatuses as $v)
                                        <option value="{{ $v }}" @if($v==='Active') selected @endif>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{ t('Reader Block') }}</label>
                            <input type="text" name="readerBlock" placeholder="Block-A" class="form-control">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer" style="justify-content: space-between;">
            <button class="btn" id="regPrevBtn" onclick="prevRegStep()" style="display:none;">&larr; {{ t('Previous') }}</button>
            <span style="flex:1;"></span>
            <button class="btn" onclick="closeModal('registerModal')">{{ t('Cancel') }}</button>
            <button class="btn btn-primary" id="regNextBtn" onclick="nextRegStep()">{{ t('Next Step') }} &rarr;</button>
            <button class="btn btn-success" id="regSubmitBtn" onclick="submitRegister()" style="display:none;">{!! icon('check', 16) !!} {{ t('Complete Registration') }}</button>
        </div>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal-backdrop v2" id="editModal">
    <div class="modal v2" style="max-width: 860px;">
        <div class="modal-header">
            <div class="modal-icon">{!! icon('edit', 20) !!}</div>
            <div class="modal-title">
                <h3>{{ t('Edit Customer Record') }} — <span id="editCode" style="color: var(--primary);"></span></h3>
                <div class="modal-subtitle">{{ t('Update customer profile & operational settings') }}</div>
            </div>
            <button class="close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div id="editInfo" style="margin-bottom: 16px; padding: 12px 16px; background: var(--surface-container-low); border-radius: var(--r-md); border-left: 4px solid var(--primary-container);"></div>

            <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:10px;">{{ t('Select Field Operation to Update') }}</div>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:10px;">
                <button class="btn btn-sm" onclick="promptUpdate('update-customer-info', ['meterSerial','firstName','middleName','lastName','phoneNumber'], 'Update Customer Info')">{!! icon('file-text', 14) !!} Update Personal Info</button>
                <button class="btn btn-sm" onclick="promptUpdate('update-first-name', ['meterSerial','firstName'], 'Update First Name')">{!! icon('edit', 14) !!} Update First Name</button>
                <button class="btn btn-sm" onclick="promptUpdate('update-middle-name', ['meterSerial','middleName'], 'Update Middle Name')">{!! icon('edit', 14) !!} Update Middle Name</button>
                <button class="btn btn-sm" onclick="promptUpdate('update-last-name', ['meterSerial','lastName'], 'Update Last Name')">{!! icon('edit', 14) !!} Update Last Name</button>
                <button class="btn btn-sm" onclick="promptUpdate('update-phone-number', ['meterSerial','phoneNumber'], 'Update Phone Number')">{!! icon('phone', 14) !!} Update Phone</button>
                <button class="btn btn-sm" onclick="promptUpdate('update-kebele', ['meterSerial','kebele'], 'Update Kebele')">{!! icon('map-pin', 14) !!} Update Kebele</button>
                <button class="btn btn-sm" onclick="promptUpdate('update-customer-type', ['meterSerial','customerType'], 'Update Customer Type', {customerType: {{ json_encode($customerTypes) }} })">{!! icon('tag', 14) !!} Customer Type</button>
                <button class="btn btn-sm" onclick="promptUpdate('update-meter-size', ['meterSerial','meterSize','meterNum'], 'Update Meter Size', {meterSize: {{ json_encode($meterSizes) }} })">{!! icon('edit', 14) !!} Meter Size</button>
                <button type="button" class="btn btn-sm" onclick="promptUpdate('update-payment-way', ['meterSerial','paymentWay'], 'Update Payment Way', {paymentWay: {{ json_encode($paymentWays) }} })">{!! icon('credit-card', 14) !!} Payment Way</button>
                <button type="button" class="btn btn-sm" onclick="promptUpdate('update-customer-branch', ['meterSerial','customerBranch'], 'Update Branch', {customerBranch: {{ json_encode($branches) }} })">{!! icon('building', 14) !!} Branch</button>
                <button type="button" class="btn btn-sm" onclick="promptUpdate('update-bill-num', ['meterSerial','billNum'], 'Update Bill Number')">{!! icon('receipt', 14) !!} Bill Number</button>
                <button type="button" class="btn btn-sm" onclick="promptUpdate('update-reader-block', ['meterSerial','readerBlock'], 'Update Reader Block')">{!! icon('alert', 14) !!} Reader Block</button>
                <button type="button" class="btn btn-sm" onclick="promptUpdate('update-start-reading', ['meterSerial','startValue'], 'Update Start Reading')">{!! icon('clock', 14) !!} Start Reading</button>

                <button type="button" class="btn btn-sm btn-warning" onclick="updateStatus('Updated')">{!! icon('tag', 14) !!} Mark Updated</button>
                <button type="button" class="btn btn-sm btn-warning" onclick="updateStatus('DC')">{!! icon('zap', 14) !!} Disconnect (DC)</button>
                <button type="button" class="btn btn-sm btn-success" onclick="updateStatus('Active')">{!! icon('check', 14) !!} Re-Activate</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="updateStatus('Deleted')">{!! icon('x', 14) !!} Mark Deleted</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="syncOne()">{!! icon('sync', 14) !!} Sync Customer</button>
                <button type="button" class="btn btn-sm" onclick="submitLocation()">{!! icon('map-pin', 14) !!} GPS Location</button>
                <button type="button" class="btn btn-sm" onclick="meterOwnerTransfer()">{!! icon('sync', 14) !!} Owner Transfer</button>
                <button type="button" class="btn btn-sm" onclick="changeNewMeter()">{!! icon('wrench', 14) !!} Install New Meter</button>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closeModal('editModal')">Close</button>
        </div>
    </div>
</div>

<!-- Excel Import Modal -->
<div class="modal-backdrop v2" id="excelModal">
    <div class="modal v2" style="max-width: 560px;">
        <div class="modal-header">
            <div class="modal-icon">{!! icon('upload', 20) !!}</div>
            <div class="modal-title">
                <h3>{{ t('Import Customers from Excel/CSV') }}</h3>
                <div class="modal-subtitle">{{ t('Bulk register customers from a spreadsheet') }}</div>
            </div>
            <button class="close" onclick="closeModal('excelModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="alert alert-info" style="background: var(--surface-container-low); border: 1px solid var(--outline-variant); border-radius: var(--r-md); padding: 12px 14px; margin-bottom: 14px;">
                <strong style="color: var(--primary);">{{ t('Required columns in CSV') }}:</strong>
                <code style="display:block; margin-top:6px; font-size:11px; word-break:break-all; background: var(--surface); padding: 6px 10px; border-radius: 4px; border: 1px solid var(--outline-variant);">meterSerial, firstName, middleName, lastName, kebele, phoneNumber, meterSize, customerType, billNum, startValue, paymentWay, customerBranch, readerBlock</code>
            </div>
            <form id="excelForm" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>{{ t('CSV File') }}</label>
                    <input type="file" name="excelFile" accept=".csv,.xlsx" required class="form-control">
                </div>
            </form>
            <div style="margin-top:12px;">
                <a href="{{ $baseUrl }}/sample-customer-template.csv" download class="btn btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">{!! icon('download', 14) !!} {{ t('Download CSV template') }}</a>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closeModal('excelModal')">{{ t('Cancel') }}</button>
            <button class="btn btn-primary" onclick="submitExcel()">{!! icon('upload', 16) !!} {{ t('Register From Excel') }}</button>
        </div>
    </div>
</div>

<!-- Update Prompt Modal -->
<div class="modal-backdrop v2" id="promptModal">
    <div class="modal v2" style="max-width: 500px;">
        <div class="modal-header">
            <div class="modal-icon">{!! icon('edit', 20) !!}</div>
            <div class="modal-title">
                <h3 id="promptTitle">{{ t('Update') }}</h3>
                <div class="modal-subtitle">{{ t('Enter new value below') }}</div>
            </div>
            <button class="close" onclick="closeModal('promptModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="promptForm">
                <div id="promptFields" style="background:var(--surface-container-low); border:1px solid var(--outline-variant); border-radius:var(--r-md); padding:14px;"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closeModal('promptModal')">{{ t('Cancel') }}</button>
            <button class="btn btn-primary" onclick="submitPromptUpdate()">{!! icon('check', 16) !!} {{ t('Submit Update') }}</button>
        </div>
    </div>
</div>

<script>
let currentEditCode = null;
let promptEndpoint = null;
let promptFields = null;
let currentRegStep = 1;

function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }

function toggleFabMenu() {
    const wrapper = document.querySelector('.fab-wrapper');
    if (wrapper) wrapper.classList.toggle('open');
}

function switchRegStep(step) {
    currentRegStep = step;
    [1, 2, 3].forEach(s => {
        const pane = document.getElementById(`reg-step-${s}-content`);
        const tab = document.getElementById(`tab-step-${s}`);
        if (pane) pane.style.display = (s === step) ? 'block' : 'none';
        if (tab) tab.classList.toggle('active', s === step);
    });

    const prevBtn = document.getElementById('regPrevBtn');
    const nextBtn = document.getElementById('regNextBtn');
    const submitBtn = document.getElementById('regSubmitBtn');

    if (prevBtn) prevBtn.style.display = (step > 1) ? 'inline-flex' : 'none';
    if (nextBtn) nextBtn.style.display = (step < 3) ? 'inline-flex' : 'none';
    if (submitBtn) submitBtn.style.display = (step === 3) ? 'inline-flex' : 'none';
}

function nextRegStep() {
    if (currentRegStep < 3) switchRegStep(currentRegStep + 1);
}

function prevRegStep() {
    if (currentRegStep > 1) switchRegStep(currentRegStep - 1);
}

function openRegisterModal() {
    document.getElementById('registerForm').reset();
    document.querySelector('[name=soldDate]').value = new Date().toISOString().slice(0,10);
    switchRegStep(1);
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
        headers: {'Content-Type':'application/json'},
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
              div.innerHTML = '<div class="alert alert-warning" style="margin:0;">No customer found.</div>';
              return;
          }
          const c = rows[0];
          div.innerHTML = `
            <table class="data-table" style="margin-bottom:8px;">
              <tr><th>Code</th><td><strong style="color:var(--primary);">${c.meter_serial}</strong></td></tr>
              <tr><th>Name</th><td>${c.first_name} ${c.middle_name||''} ${c.last_name||''}</td></tr>
              <tr><th>Kebele</th><td>${c.kebele||'—'}</td></tr>
              <tr><th>Phone</th><td>${c.phone_number||'—'}</td></tr>
              <tr><th>Meter</th><td>${c.meter_size||'—'} (#${c.meter_num||0})</td></tr>
              <tr><th>Type</th><td>${c.customer_type||'—'}</td></tr>
              <tr><th>Branch</th><td>${c.customer_branch||'—'}</td></tr>
              <tr><th>Status</th><td><span class="badge ${c.customer_status==='Active'?'badge-success':'badge-danger'}">${c.customer_status}</span></td></tr>
              <tr><th>Start Reading</th><td>${c.start_value} m³</td></tr>
            </table>
            <button class="btn btn-primary btn-sm" type="button" onclick='editCustomer(${JSON.stringify(c.meter_serial)})'>Edit Customer</button>
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
            <div style="font-weight:700; font-size:14px; color:var(--on-surface);">${c.first_name} ${c.middle_name||''} ${c.last_name||''}</div>
            <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">
              Kebele: ${c.kebele||'—'} &bull; Phone: ${c.phone_number||'—'} &bull; Type: ${c.customer_type||'—'} &bull; Status: <strong>${c.customer_status}</strong>
            </div>
          `;
      });
    openModal('editModal');
}

function deleteCustomer(code, name) {
    confirmDialog(`Delete customer ${code}?`, `Are you sure you want to mark customer ${name} as deleted?`, 'danger')
      .then(ok => {
          if (!ok) return;
          fetch(apiUrl(`active_customers/update-single-customer-status`) + `?meterSerial=${encodeURIComponent(code)}&customerStatus=Deleted`, {method:'PUT'})
            .then(r => r.text())
            .then(() => { showToast('Customer marked as Deleted', 'success'); setTimeout(()=>location.reload(), 800); });
      });
}

function updateStatus(newStatus) {
    if (!currentEditCode) return;
    confirmDialog(`Change Status`, `Change status of ${currentEditCode} to "${newStatus}"?`, 'warning')
      .then(ok => {
          if (!ok) return;
          fetch(apiUrl(`active_customers/update-single-customer-status`) + `?meterSerial=${encodeURIComponent(currentEditCode)}&customerStatus=${encodeURIComponent(newStatus)}`, {method:'PUT'})
            .then(r => r.text())
            .then(() => { showToast(`Status updated to ${newStatus}`, 'success'); setTimeout(()=>location.reload(), 800); });
      });
}

function syncOne() {
    if (!currentEditCode) return;
    fetch(apiUrl(`active_customers/update-sync-status`) + `?meterSerial=${encodeURIComponent(currentEditCode)}`, {method:'PUT'})
      .then(r => r.text())
      .then(() => showToast('Customer synced successfully', 'success'));
}

function syncCustomerList() {
    confirmDialog('Sync Customer Database', 'Sync all active customer records to cloud server?', 'info')
      .then(ok => {
          if (!ok) return;
          fetch(apiUrl('active_customers/send-active-customers'), {method:'POST'})
            .then(r => r.json())
            .then(d => showToast(`Synced ${d.count} customer records`, 'success'));
      });
}

function newCodeFetch() {
    const kebele = prompt('Enter kebele number:');
    if (!kebele) return;
    fetch(apiUrl('active_customers/get-recent-code') + `?kebele=${encodeURIComponent(kebele)}`)
      .then(r => r.text())
      .then(code => showToast(`Next available code for Kebele ${kebele}: ${code}`, 'info'));
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
        group.style.marginBottom = '10px';
        const lbl = document.createElement('label');
        lbl.textContent = f.replace(/([A-Z])/g, ' $1').replace(/^./, s => s.toUpperCase());
        group.appendChild(lbl);
        let input;
        if (dropdowns[f]) {
            input = document.createElement('select');
            input.className = 'fancy';
            dropdowns[f].forEach(v => {
                const o = document.createElement('option');
                o.value = v; o.textContent = v;
                input.appendChild(o);
            });
        } else {
            input = document.createElement('input');
            input.className = 'form-control';
            input.type = (f.includes('Value') || f.includes('Num')) ? 'number' : 'text';
            input.step = '0.01';
        }
        input.name = f;
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
      .then(() => { showToast('Owner transferred successfully', 'success'); setTimeout(()=>location.reload(), 800); });
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
            .then(() => { showToast('New meter installed successfully', 'success'); setTimeout(()=>location.reload(), 800); });
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

(function initCustomerCharts() {
    const run = () => {
        if (typeof Chart === 'undefined') return;

        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            const oldChart = Chart.getChart(statusCtx);
            if (oldChart) oldChart.destroy();
            new Chart(statusCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Active', 'Disconnected (DC)', 'Updated / Pending'],
                    datasets: [{
                        data: [{{ $activeCount }}, {{ $dcCount }}, {{ max(0, $totalCount - $activeCount - $dcCount) }}],
                        backgroundColor: ['#10B981', '#EF4444', '#FEA619'],
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

        const branchCtx = document.getElementById('branchChart');
        if (branchCtx) {
            const oldBranch = Chart.getChart(branchCtx);
            if (oldBranch) oldBranch.destroy();
            new Chart(branchCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Eteya', 'Hurutaa', 'Heexosaa', 'Dheeraa'],
                    datasets: [{
                        label: 'Customers',
                        data: [{{ intval($totalCount * 0.45) }}, {{ intval($totalCount * 0.25) }}, {{ intval($totalCount * 0.18) }}, {{ intval($totalCount * 0.12) }}],
                        backgroundColor: '#10B981',
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
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        setTimeout(run, 100);
    }
})();
</script>
@endsection
