@extends('layouts.app')

@section('content')

<!-- Page Header & Banner -->
<div class="gsap-hero flex flex-wrap items-end justify-between gap-4 mb-6">
    <div class="min-w-0">
        <h2 class="m-0 text-[22px] font-bold tracking-tight text-slate-900 flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-700 inline-flex items-center justify-center shrink-0">{!! icon('book-open', 20) !!}</span>
            <span>{{ t('Customers Ledger Statement') }}</span>
        </h2>
        <p class="mt-2 text-[13px] text-slate-500">
            {{ t('View comprehensive water consumption and billing history by customer and year') }}
            @if ($customer)
                &mdash; <flux:badge color="emerald" size="sm">{{ $customer->meter_serial }} — {{ trim(($customer->first_name ?? '').' '.($customer->middle_name ?? '').' '.($customer->last_name ?? '')) }}</flux:badge>
            @endif
        </p>
    </div>
    <div class="flex items-center gap-2.5">
        @if (! empty($customer))
            <flux:button icon="printer" variant="subtle" onclick="window.print()">
                {{ t('Print Statement') }}
            </flux:button>
        @endif
    </div>
</div>

<!-- Customer Ledger Livewire 4 Island -->
<livewire:islands.customer-ledger-island :meter-serial="$meterSerial" :year="$year" />

        <!-- Operational Quick Actions -->
        <flux:card class="overflow-hidden p-0">
            <div class="px-4 py-3 border-b border-slate-100 font-bold text-[13px] text-slate-900 flex items-center gap-2 bg-slate-50/50">
                <span class="text-amber-600">{!! icon('wrench', 15) !!}</span> {{ t('Operational Quick Actions') }}
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Update Start Reading (m³)') }}</label>
                    <div class="flex gap-1.5">
                        <flux:input type="number" step="0.01" id="startReading" value="{{ $customer->start_value }}" class="flex-1" />
                        <flux:button variant="primary" icon="check" type="button" onclick="updateStartReading()" title="{{ t('Save Start Reading') }}" />
                    </div>
                </div>
                <flux:button type="button" onclick="disconnectCustomer()" variant="danger" icon="x-mark" class="w-full justify-center">
                    {{ t('Disconnect Customer (DC)') }}
                </flux:button>
            </div>
        </flux:card>
    </div>
</div>

<!-- Collapsible Floating Quick Action Menu (FAB) -->
<div class="fab-wrapper">
    <div class="fab-menu" id="fabMenu">
        <button type="button" class="fab-item inline-flex items-center gap-2.5 px-4 py-2.5 rounded-full bg-slate-900 text-white text-[13px] font-semibold border border-white/15 shadow-lg hover:bg-emerald-700 transition" onclick="window.print(); toggleFabMenu();">
            {!! icon('print', 16) !!} <span>{{ t('Print Statement') }}</span>
        </button>
        <button type="button" class="fab-item inline-flex items-center gap-2.5 px-4 py-2.5 rounded-full bg-slate-900 text-white text-[13px] font-semibold border border-white/15 shadow-lg hover:bg-emerald-700 transition" onclick="exportLedger(); toggleFabMenu();">
            {!! icon('download', 16) !!} <span>{{ t('Export Ledger CSV') }}</span>
        </button>
    </div>
    <button type="button" class="fab-trigger-btn w-14 h-14 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white shadow-[0_8px_24px_rgba(5,150,105,0.45)] flex items-center justify-center transition" onclick="toggleFabMenu()" title="Quick Actions">
        {!! icon('plus', 22) !!}
    </button>
</div>

<script>
function toggleFabMenu() {
    const wrapper = document.querySelector('.fab-wrapper');
    if (wrapper) wrapper.classList.toggle('open');
}

function updateStartReading() {
    const code = {{ json_encode($meterSerial) }};
    const val = document.getElementById('startReading').value;
    fetch(apiUrl(`active_customers/update-start-reading`) + `?meterSerial=${encodeURIComponent(code)}&startValue=${val}`, {method:'PUT'})
      .then(r => r.text())
      .then(() => showToast('Start reading updated successfully', 'success'));
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
    const query = document.getElementById('customerSearch').value.toLowerCase().trim();
    document.querySelectorAll('#customerSelect option').forEach(opt => {
        if (!opt.value) return opt.hidden = false;
        const text = opt.textContent.toLowerCase();
        opt.hidden = query && !text.includes(query);
    });
}

(function initLedgerCharts() {
    const run = () => {
        if (typeof Chart === 'undefined') return;

        @php
            $monthsList = ['Amajjii', 'Guraandhala', 'Bitootessa', 'Elba', 'Caamsaa', 'Waxabajjii', 'Adooleessa', 'Hagayya', 'Fuulbaana', 'Onkololeessa', 'Sadaasa', 'Muddee'];
            $consData = [];
            $costData = [];
            foreach ($ledger as $r) {
                $consData[] = (float) $r->consumption;
                $costData[] = (float) $r->total_monthly_cost;
            }
        @endphp

        const consCtx = document.getElementById('ledgerConsChart');
        if (consCtx) {
            const oldChart = Chart.getChart(consCtx);
            if (oldChart) oldChart.destroy();
            new Chart(consCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($ledger->pluck('bill_month')->toArray()) !!},
                    datasets: [{
                        label: 'Consumption (m³)',
                        data: {!! json_encode($consData) !!},
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.15)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { grid: { color: 'rgba(15,23,42,0.05)' }, ticks: { font: { size: 10 } } },
                        x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                    }
                }
            });
        }

        const costCtx = document.getElementById('ledgerCostChart');
        if (costCtx) {
            const oldCost = Chart.getChart(costCtx);
            if (oldCost) oldCost.destroy();
            new Chart(costCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($ledger->pluck('bill_month')->toArray()) !!},
                    datasets: [{
                        label: 'Billed Cost (ETB)',
                        data: {!! json_encode($costData) !!},
                        backgroundColor: '#059669',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { grid: { color: 'rgba(15,23,42,0.05)' }, ticks: { font: { size: 10, family: 'Inter' } } },
                        x: { grid: { display: false }, ticks: { font: { size: 11, family: 'Inter' } } }
                    }
                }
            });
        }
    };

    let retries = 0;
    const safeRun = () => {
        const testCanvas = document.getElementById('ledgerConsChart') || document.getElementById('ledgerCostChart');
        if (!testCanvas || typeof Chart === 'undefined') return;

        if (testCanvas.parentElement && testCanvas.parentElement.clientHeight === 0 && retries < 10) {
            retries++;
            setTimeout(safeRun, 50);
            return;
        }

        run();
    };

    if (document.readyState === 'complete') {
        safeRun();
    } else {
        document.addEventListener('DOMContentLoaded', safeRun);
        window.addEventListener('load', safeRun);
    }
    window.addEventListener('resize', safeRun);
})();
</script>
@endif
@endsection
