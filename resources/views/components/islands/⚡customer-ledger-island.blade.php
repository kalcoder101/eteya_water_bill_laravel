<?php

use App\Models\ActiveCustomer;
use App\Models\BillFinance;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    #[Url(except: '')]
    public string $meterSerial = '';

    #[Url(except: '')]
    public string $year = '';

    public string $searchFilter = '';

    public function mount(?string $meterSerial = null, ?string $year = null): void
    {
        if ($meterSerial !== null) {
            $this->meterSerial = $meterSerial;
        }
        if ($year !== null) {
            $this->year = $year;
        }
        if (empty($this->year)) {
            $this->year = (string) get_setting('current_bill_year', date('Y'));
        }
    }

    public function selectCustomer(string $serial): void
    {
        $this->meterSerial = $serial;
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

        return view('components.islands.⚡customer-ledger-island', [
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
    <!-- Customer Selection & Search Toolbar Island -->
    <flux:card class="p-4 mb-5">
        <div class="flex flex-wrap gap-3.5 items-end">
            <div class="flex-1 min-w-[280px]">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Select Customer') }}</label>
                <flux:input wire:model.live.debounce.300ms="searchFilter" placeholder="{{ t('Type code, name, phone to filter list...') }}" icon="magnifying-glass" class="mb-1.5" />
                <select wire:model.live="meterSerial" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                    <option value="">— {{ t('Choose a customer account') }} —</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->meter_serial }}">
                            {{ $c->meter_serial }} — {{ trim(($c->first_name ?? '').' '.($c->middle_name ?? '').' '.($c->last_name ?? '')) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-[140px]">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Billing Year') }}</label>
                <select wire:model.live="year" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                    @foreach ($availableYears as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </flux:card>

    @if (empty($customer))
        <flux:card class="py-14 px-5 text-center">
            <div class="text-slate-300 mb-3 flex justify-center">{!! icon('book-open', 54) !!}</div>
            <h3 class="m-0 text-[15px] font-semibold text-slate-700">{{ t('No Customer Account Selected') }}</h3>
            <p class="text-xs text-slate-500 mt-1.5">{{ t('Search and select a customer account from the dropdown above to load their ledger statement history.') }}</p>
        </flux:card>
    @else
        <!-- KPI Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
            <x-kpi :label="t('Total Billed')" :value="number_format($grandTotal, 0).' ETB'" :subvalue="count($ledger).' '.t('Bills in').' '.$year" icon="receipt" color="emerald" />
            <x-kpi :label="t('Water Consumption')" :value="number_format($totalConsumption, 1).' m³'" :subvalue="(count($ledger) > 0 ? number_format($totalConsumption / count($ledger), 1) : 0).' m³ '.t('Avg / Month')" icon="water" color="sky" />
            <x-kpi :label="t('Paid Revenue')" :value="number_format($paidTotal, 0).' ETB'" :subvalue="$paidBills.' '.t('Paid Bills')" icon="check" color="emerald" :active="true" />
            <x-kpi :label="t('Unpaid Balance')" :value="number_format($unpaidTotal, 0).' ETB'" :subvalue="$unpaidBills.' '.t('Unpaid Bills')" icon="x" color="rose" />
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
                                    <flux:button variant="subtle" size="sm" icon="printer" href="{{ route('bills.print', $row->bill_finance_id) }}" target="_blank">
                                        {{ t('Receipt') }}
                                    </flux:button>
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
</div>