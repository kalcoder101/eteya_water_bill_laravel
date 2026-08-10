<?php

use App\Models\ActiveCustomer;
use App\Models\BillFinance;
use Flux\Flux;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    #[Url(except: '')]
    public string $meterSerial = '';

    #[Url(except: '')]
    public string $year = '';

    public string $searchFilter = '';

    public ?BillFinance $selectedBill = null;

    public function mount(?string $meterSerial = null, ?string $year = null): void
    {
        if (request()->has('meterSerial')) {
            $this->meterSerial = (string) request()->query('meterSerial');
        } elseif ($meterSerial !== null) {
            $this->meterSerial = $meterSerial;
        }

        if (request()->has('year')) {
            $this->year = (string) request()->query('year');
        } elseif ($year !== null) {
            $this->year = $year;
        }

        if (empty($this->year)) {
            $this->year = (string) get_setting('current_bill_year', date('Y'));
        }
    }

    public function updatedMeterSerial(string $value): void
    {
        if (!empty($value)) {
            Flux::toast("Loaded financial statement for account {$value}.", variant: 'info');
        }
    }

    public function updatedYear(string $value): void
    {
        if (!empty($this->meterSerial)) {
            Flux::toast("Switched ledger statement to year {$value}.", variant: 'info');
        }
    }

    public function clearCustomer(): void
    {
        $this->meterSerial = '';
        $this->searchFilter = '';
        Flux::toast('Customer selection cleared.', variant: 'subtle');
    }

    public function selectCustomer(string $serial): void
    {
        $this->meterSerial = $serial;
        Flux::toast("Selected customer account {$serial}.", variant: 'success');
    }

    public function previewReceipt(string $billFinanceId): void
    {
        $this->selectedBill = BillFinance::with('customer')->where('bill_finance_id', $billFinanceId)->first();
        if ($this->selectedBill) {
            $this->modal('ledger-receipt-modal')->show();
        } else {
            Flux::toast('Bill record not found.', variant: 'danger');
        }
    }

    public function render(): mixed
    {
        $customersQuery = ActiveCustomer::orderBy('meter_serial');
        if (!empty($this->searchFilter)) {
            $s = trim($this->searchFilter);
            $customersQuery->where(function ($q) use ($s) {
                $q->where('meter_serial', 'like', "%{$s}%")
                  ->orWhere('first_name', 'like', "%{$s}%")
                  ->orWhere('middle_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%")
                  ->orWhere('phone_number', 'like', "%{$s}%");
            });
        }
        $customers = $customersQuery->get();

        $availableYears = BillFinance::distinct()
            ->pluck('bill_year')
            ->filter()
            ->sort()
            ->reverse()
            ->values()
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [(string) date('Y')];
        }

        $customer = null;
        $ledger = collect();
        $totalConsumption = 0.0;
        $grandTotal = 0.0;
        $paidTotal = 0.0;
        $unpaidTotal = 0.0;
        $paidBills = 0;
        $unpaidBills = 0;

        if (!empty($this->meterSerial)) {
            $customer = ActiveCustomer::where('meter_serial', $this->meterSerial)->first();

            $ledger = BillFinance::where('meter_serial', $this->meterSerial)
                ->where('bill_year', $this->year)
                ->orderBy('created_at', 'asc')
                ->get();

            $totalConsumption = (float) $ledger->sum('consumption');
            $grandTotal       = (float) $ledger->sum('total_monthly_cost');
            $paidTotal        = (float) $ledger->where('payment_status', 'Paid')->sum('total_monthly_cost');
            $unpaidTotal      = $grandTotal - $paidTotal;
            $paidBills        = $ledger->where('payment_status', 'Paid')->count();
            $unpaidBills      = count($ledger) - $paidBills;
        }

        return view('pages.⚡customer-ledger', [
            'customers'        => $customers,
            'availableYears'   => $availableYears,
            'customer'         => $customer,
            'ledger'           => $ledger,
            'totalConsumption' => $totalConsumption,
            'grandTotal'       => $grandTotal,
            'paidTotal'        => $paidTotal,
            'unpaidTotal'      => $unpaidTotal,
            'paidBills'        => $paidBills,
            'unpaidBills'      => $unpaidBills,
        ]);
    }
};
?>

<div>
    <!-- Page Header Banner -->
    <div class="gsap-hero flex flex-wrap items-end justify-between gap-4 mb-6">
        <div class="min-w-0">
            <h2 class="m-0 text-[22px] font-bold tracking-tight text-slate-900 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-700 inline-flex items-center justify-center shrink-0">{!! icon('book-open', 20) !!}</span>
                <span>{{ t('Customers Ledger Statement') }}</span>
            </h2>
            <p class="mt-2 text-[13px] text-slate-500">
                {{ t('View comprehensive water consumption and billing history by customer account and year') }}
                @if (!empty($meterSerial))
                    <flux:badge color="emerald" size="sm" class="ml-1">{{ $meterSerial }} ({{ $year }})</flux:badge>
                @endif
            </p>
        </div>
        @if (!empty($customer))
            <div class="flex items-center gap-2">
                <flux:button variant="subtle" icon="printer" onclick="window.print()">
                    {{ t('Print Statement') }}
                </flux:button>
            </div>
        @endif
    </div>

    <!-- Customer Selection Toolbar Card -->
    <flux:card class="p-4 mb-5 space-y-4">
        <div class="flex flex-wrap gap-4 items-end justify-between">
            <div class="flex-1 min-w-[280px]">
                <flux:field>
                    <flux:label class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Select Customer Account') }}</flux:label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <flux:input wire:model.live.debounce.300ms="searchFilter" placeholder="{{ t('Type code, name, phone to filter...') }}" icon="magnifying-glass" />
                        <flux:select wire:model.live="meterSerial" placeholder="{{ t('Choose a customer account') }}">
                            <flux:select.option value="">— {{ t('Choose a customer account') }} —</flux:select.option>
                            @foreach ($customers as $c)
                                <flux:select.option value="{{ $c->meter_serial }}">
                                    {{ $c->meter_serial }} — {{ trim(($c->first_name ?? '').' '.($c->middle_name ?? '').' '.($c->last_name ?? '')) }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </flux:field>
            </div>

            <div class="min-w-[150px]">
                <flux:field>
                    <flux:label class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Billing Year') }}</flux:label>
                    <flux:select wire:model.live="year" placeholder="{{ t('Select Year') }}">
                        @foreach ($availableYears as $y)
                            <flux:select.option value="{{ $y }}">{{ $y }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            @if (!empty($customer))
                <div class="flex items-center gap-2">
                    <flux:dropdown>
                        <flux:button variant="subtle" icon="ellipsis-vertical">
                            {{ t('Actions') }}
                        </flux:button>
                        <flux:menu>
                            <flux:menu.item icon="printer" onclick="window.print()">{{ t('Print Statement') }}</flux:menu.item>
                            <flux:menu.item icon="arrow-down-tray" href="{{ route('export.ledger') }}?year={{ $year }}&meterSerial={{ urlencode($meterSerial) }}">{{ t('Export Ledger CSV') }}</flux:menu.item>
                            <flux:menu.item icon="x-mark" wire:click="clearCustomer">{{ t('Clear Selection') }}</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            @endif
        </div>
    </flux:card>

    @if (empty($customer))
        <flux:card class="py-14 px-5 text-center">
            <div class="text-slate-300 mb-3 flex justify-center">{!! icon('book-open', 54) !!}</div>
            <h3 class="m-0 text-[15px] font-semibold text-slate-700">{{ t('No Customer Account Selected') }}</h3>
            <p class="text-xs text-slate-500 mt-1.5">{{ t('Search and select a customer account from the dropdown toolbar above to view their financial statement.') }}</p>
        </flux:card>
    @else
        <!-- Customer Details Banner -->
        <flux:card class="p-4 mb-5 border-l-4 border-l-emerald-600 bg-slate-50/60">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h3 class="m-0 text-base font-bold text-slate-900">
                            {{ trim(($customer->first_name ?? '').' '.($customer->middle_name ?? '').' '.($customer->last_name ?? '')) }}
                        </h3>
                        <flux:badge color="emerald" size="sm" icon="check">{{ $customer->customer_status }}</flux:badge>
                    </div>
                    <div class="text-xs text-slate-500 mt-1 flex flex-wrap gap-x-4 gap-y-1">
                        <span>Code: <strong class="text-emerald-700 font-mono">{{ $customer->meter_serial }}</strong></span>
                        <span>Kebele: <strong>{{ $customer->kebele }}</strong></span>
                        <span>Phone: <strong>{{ $customer->phone_number ?? '—' }}</strong></span>
                        <span>Type: <strong>{{ $customer->customer_type }}</strong></span>
                        <span>Meter Size: <strong>{{ $customer->meter_size }}</strong></span>
                        <span>Bill Serial: <strong>{{ $customer->bill_num ?? '—' }}</strong></span>
                    </div>
                </div>
                <div>
                    <flux:button variant="subtle" size="sm" icon="pencil-square" href="{{ route('customer-service.index').'?search='.urlencode($customer->meter_serial) }}">
                        {{ t('Manage Customer') }}
                    </flux:button>
                </div>
            </div>
        </flux:card>

        <!-- Customer Account Lifecycle & Billing Horizontal Timeline -->
        <flux:card class="p-5 mb-5 overflow-hidden">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div class="font-bold text-sm text-slate-900 flex items-center gap-2">
                    {!! icon('clock', 18) !!}
                    <span>{{ t('Account Lifecycle & Billing Progression') }}</span>
                </div>
                <flux:badge color="emerald" size="sm">{{ $year }} {{ t('Timeline') }}</flux:badge>
            </div>

            <flux:timeline horizontal>
                <flux:timeline.item>
                    <flux:timeline.indicator color="emerald">
                        {!! icon('check', 14) !!}
                    </flux:timeline.indicator>
                    <flux:timeline.content>
                        <flux:heading class="text-xs font-bold text-slate-900">{{ t('Account Registered') }}</flux:heading>
                        <div class="text-[11px] text-slate-500">{{ $customer->created_at ? $customer->created_at->format('M Y') : 'Active User' }}</div>
                    </flux:timeline.content>
                </flux:timeline.item>

                <flux:timeline.item>
                    <flux:timeline.indicator color="sky">
                        {!! icon('wrench', 14) !!}
                    </flux:timeline.indicator>
                    <flux:timeline.content>
                        <flux:heading class="text-xs font-bold text-slate-900">{{ t('Meter Serial Assigned') }}</flux:heading>
                        <div class="text-[11px] font-mono text-emerald-700 font-semibold">{{ $customer->meter_serial }}</div>
                    </flux:timeline.content>
                </flux:timeline.item>

                <flux:timeline.item>
                    <flux:timeline.indicator color="emerald">
                        {!! icon('receipt', 14) !!}
                    </flux:timeline.indicator>
                    <flux:timeline.content>
                        <flux:heading class="text-xs font-bold text-slate-900">{{ t('Monthly Invoices') }}</flux:heading>
                        <div class="text-[11px] text-slate-500">{{ count($ledger) }} {{ t('Records in') }} {{ $year }}</div>
                    </flux:timeline.content>
                </flux:timeline.item>

                <flux:timeline.item>
                    <flux:timeline.indicator color="{{ $unpaidBills == 0 ? 'emerald' : 'amber' }}">
                        {!! icon($unpaidBills == 0 ? 'check' : 'credit-card', 14) !!}
                    </flux:timeline.indicator>
                    <flux:timeline.content>
                        <flux:heading class="text-xs font-bold text-slate-900">{{ $unpaidBills == 0 ? t('Account Current') : t('Payment Pending') }}</flux:heading>
                        <div class="text-[11px] text-slate-500">{{ $paidBills }}/{{ count($ledger) }} {{ t('Paid') }}</div>
                    </flux:timeline.content>
                </flux:timeline.item>
            </flux:timeline>
        </flux:card>

        <!-- KPI Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
            <x-kpi :label="t('Total Billed')" :value="number_format($grandTotal, 0).' ETB'" :subvalue="count($ledger).' '.t('Bills in').' '.$year" icon="receipt" color="emerald" />
            <x-kpi :label="t('Water Consumption')" :value="number_format($totalConsumption, 1).' m³'" :subvalue="(count($ledger) > 0 ? number_format($totalConsumption / count($ledger), 1) : 0).' m³ '.t('Avg / Month')" icon="water" color="sky" />
            <x-kpi :label="t('Paid Revenue')" :value="number_format($paidTotal, 0).' ETB'" :subvalue="$paidBills.' '.t('Paid Bills')" icon="check" color="emerald" :active="true" />
            <x-kpi :label="t('Unpaid Balance')" :value="number_format($unpaidTotal, 0).' ETB'" :subvalue="$unpaidBills.' '.t('Unpaid Bills')" icon="x-mark" color="rose" />
        </div>

        <!-- Ledger Statement Data Table -->
        <flux:card class="overflow-hidden p-0 mb-5">
            <div class="h-1 bg-emerald-600"></div>
            <div class="flex items-center justify-between gap-3 px-5 py-3.5 border-b border-slate-100 bg-slate-50/50">
                <span class="font-bold text-sm text-slate-900">
                    {{ t('Financial Statement Ledger') }} &mdash; {{ $customer->meter_serial }} ({{ $year }})
                </span>
                <flux:badge color="zinc" size="sm">{{ count($ledger) }} {{ t('Monthly Records') }}</flux:badge>
            </div>

            <div class="scrollable-table border-0 rounded-none">
                <div class="scroll-progress"><div class="scroll-progress-bar"></div></div>
                <div class="table-scroll-view">
                    <table class="w-full text-[13px]">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold">
                                <th class="text-left px-4 py-3">{{ t('Period') }}</th>
                                <th class="text-left px-4 py-3">{{ t('Cons. (m³)') }}</th>
                                <th class="text-left px-4 py-3">{{ t('Water Fee') }}</th>
                                <th class="text-left px-4 py-3">{{ t('Meter') }}</th>
                                <th class="text-left px-4 py-3">{{ t('Svc Fee') }}</th>
                                <th class="text-left px-4 py-3">{{ t('Penalty') }}</th>
                                <th class="text-left px-4 py-3">{{ t('Fund') }}</th>
                                <th class="text-left px-4 py-3">{{ t('Total Billed') }}</th>
                                <th class="text-left px-4 py-3">{{ t('Status') }}</th>
                                <th class="text-right px-4 py-3">{{ t('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($ledger as $row)
                            <tr class="border-b border-slate-100 odd:bg-white even:bg-slate-50/40 hover:bg-emerald-50/60 transition-colors">
                                <td class="px-4 py-2.5 font-bold text-slate-900">{{ $row->bill_month }} {{ $row->bill_year }}</td>
                                <td class="px-4 py-2.5 font-mono tabular-nums"><strong>{{ number_format($row->consumption, 1) }}</strong></td>
                                <td class="px-4 py-2.5 font-mono tabular-nums">{{ number_format($row->consumption_cost, 0) }}</td>
                                <td class="px-4 py-2.5 font-mono tabular-nums">{{ number_format($row->meter_price, 0) }}</td>
                                <td class="px-4 py-2.5 font-mono tabular-nums">{{ number_format($row->service_price, 0) }}</td>
                                <td class="px-4 py-2.5 font-mono tabular-nums">{{ number_format($row->penalty_cost, 0) }}</td>
                                <td class="px-4 py-2.5 font-mono tabular-nums">{{ number_format($row->state_price, 0) }}</td>
                                <td class="px-4 py-2.5"><strong class="text-emerald-700 font-mono tabular-nums">{{ number_format($row->total_monthly_cost, 0) }} ETB</strong></td>
                                <td class="px-4 py-2.5">
                                    @if ($row->payment_status === 'Paid')
                                        <flux:badge color="emerald" icon="check" size="sm">Paid</flux:badge>
                                    @else
                                        <flux:badge color="rose" icon="x-mark" size="sm">Unpaid</flux:badge>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <flux:button variant="subtle" size="sm" icon="eye" wire:click="previewReceipt('{{ $row->bill_finance_id }}')" title="{{ t('Preview Receipt') }}">
                                            {{ t('Preview') }}
                                        </flux:button>
                                        <flux:button variant="subtle" size="sm" icon="printer" href="{{ route('bills.print', $row->bill_finance_id) }}" target="_blank">
                                            {{ t('Receipt') }}
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @if ($ledger->isEmpty())
                            <tr>
                                <td colspan="10" class="text-center py-8 px-4 text-slate-500 text-xs">{{ t('No ledger records generated for this customer in') }} {{ $year }}.</td>
                            </tr>
                        @endif
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-50 font-bold border-t border-slate-200">
                                <td class="px-4 py-3 text-slate-700">{{ t('Annual Total') }}:</td>
                                <td class="px-4 py-3 font-mono tabular-nums">{{ number_format($totalConsumption, 1) }} m³</td>
                                <td colspan="5"></td>
                                <td class="px-4 py-3 text-emerald-700 text-sm font-mono tabular-nums">{{ number_format($grandTotal, 0) }} ETB</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </flux:card>
    @endif

    <!-- Quick Receipt Preview Modal -->
    <flux:modal name="ledger-receipt-modal" class="md:w-[540px]">
        @if ($selectedBill)
            <div class="space-y-4">
                <div>
                    <flux:heading size="lg">Customer Statement Voucher</flux:heading>
                    <flux:subheading>Customer: {{ $selectedBill->meter_serial }} &bull; Period: {{ $selectedBill->bill_period }}</flux:subheading>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 font-mono text-xs space-y-2">
                    <div class="flex justify-between border-b border-slate-200 pb-2">
                        <span>Account Name:</span>
                        <strong class="text-slate-900">{{ $selectedBill->customer ? trim(($selectedBill->customer->first_name ?? '').' '.($selectedBill->customer->middle_name ?? '').' '.($selectedBill->customer->last_name ?? '')) : $selectedBill->full_name }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span>Water Consumption:</span>
                        <span>{{ number_format($selectedBill->consumption, 1) }} m³</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Water Fee:</span>
                        <span>{{ number_format($selectedBill->consumption_cost, 0) }} ETB</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Meter Rent Fee:</span>
                        <span>{{ number_format($selectedBill->meter_price, 0) }} ETB</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Service Fee:</span>
                        <span>{{ number_format($selectedBill->service_price, 0) }} ETB</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Penalty Fee:</span>
                        <span>{{ number_format($selectedBill->penalty_cost, 0) }} ETB</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Community Water Fund:</span>
                        <span>{{ number_format($selectedBill->state_price, 0) }} ETB</span>
                    </div>
                    <div class="flex justify-between border-t-2 border-slate-800 pt-2 text-sm font-extrabold text-emerald-800">
                        <span>TOTAL MONTHLY BILLED:</span>
                        <span>{{ number_format($selectedBill->total_monthly_cost, 0) }} ETB</span>
                    </div>
                </div>

                <div class="flex gap-2 justify-end pt-2">
                    <flux:modal.close>
                        <flux:button variant="subtle">Close</flux:button>
                    </flux:modal.close>
                    <flux:button variant="primary" icon="printer" href="{{ route('bills.print', $selectedBill->bill_finance_id) }}" target="_blank">
                        Print Official Voucher
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
