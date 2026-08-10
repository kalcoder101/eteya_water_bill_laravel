<?php

use App\Models\ActiveCustomer;
use App\Models\BillFinance;
use App\Services\AuditService;
use App\Services\BillCalculatorService;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    #[Url(except: '')]
    public string $year = '';

    #[Url(except: '')]
    public string $month = '';

    #[Url(except: 'all')]
    public string $filterStatus = 'all';

    #[Url(except: '')]
    public string $search = '';

    public ?BillFinance $selectedBill = null;

    public function mount(): void
    {
        if (empty($this->year)) {
            $this->year = (string) get_setting('current_bill_year', date('Y'));
        }
        if (empty($this->month)) {
            $this->month = (string) get_setting('current_bill_month', 'Fulbaana');
        }
    }

    public function setFilterStatus(string $status): void
    {
        $this->filterStatus = $status;
    }

    public function calculateBills(): void
    {
        try {
            DB::beginTransaction();

            $customers = ActiveCustomer::where('customer_status', 'Active')
                ->leftJoin('seasonal_consumptions', function ($join) {
                    $join->on('seasonal_consumptions.meter_serial', '=', 'active_customers.meter_serial')
                         ->where('seasonal_consumptions.reading_year', $this->year)
                         ->where('seasonal_consumptions.reading_month', $this->month);
                })
                ->select('active_customers.*',
                    'seasonal_consumptions.current_reading',
                    'seasonal_consumptions.reading_date',
                    'seasonal_consumptions.collector')
                ->get();

            $calculator = app(BillCalculatorService::class);
            $created = 0;
            $updated = 0;

            foreach ($customers as $c) {
                $billId = generate_bill_finance_id($c->meter_serial, $this->year, $this->month);
                $exists = BillFinance::where('bill_finance_id', $billId)->exists();

                $curReading = (float) ($c->current_reading ?? $c->start_value);
                $prevReading = (float) $c->start_value;

                $bill = $calculator->calculate(
                    $prevReading,
                    $curReading,
                    $c->meter_size ?? '1/2"',
                    $c->customer_type ?? 'Dhunfaa'
                );

                $fullName = trim(
                    ($c->first_name ?? '').' '.
                    ($c->middle_name ?? '').' '.
                    ($c->last_name ?? '')
                );

                BillFinance::updateOrCreate(
                    ['bill_finance_id' => $billId],
                    [
                        'meter_serial'           => $c->meter_serial,
                        'meter_price'            => $bill['meter_price'],
                        'service_price'          => $bill['service_price'],
                        'consumption'            => $bill['consumption'],
                        'penalty_cost'           => $bill['penalty_cost'],
                        'community_cost'         => $bill['community_cost'],
                        'total_monthly_cost'    => $bill['total_monthly_cost'],
                        'consumption_cost'       => $bill['consumption_cost'],
                        'total_aggregation_cost' => $bill['total_monthly_cost'],
                        'deposited_cost'         => $bill['deposited_cost'],
                        'payment_status'         => 'Unpaid',
                        'bill_year'              => $this->year,
                        'bill_month'             => $this->month,
                        'state_price'            => $bill['state_price'],
                        'deposit_fund'           => 0,
                        'calculate_status'       => 'Calculated',
                        'bill_period'            => "{$this->year} - {$this->month}",
                        'vat_price'              => 0,
                        'full_name'              => $fullName,
                        'kebele'                 => $c->kebele,
                        'meter_num'              => (int) $c->meter_num,
                        'customer_type'          => $c->customer_type,
                        'customer_branch'        => $c->customer_branch,
                    ]
                );

                if ($exists) {
                    $updated++;
                } else {
                    $created++;
                }
            }

            DB::commit();

            app(AuditService::class)->logAudit(
                "Calculated {$created} new + {$updated} updated bills for {$this->year} {$this->month}",
                auth()->user()?->fullName() ?? 'System'
            );

            Flux::toast("Calculated {$created} new and {$updated} updated bills for {$this->year} {$this->month}.", variant: 'success');
        } catch (\Throwable $e) {
            DB::rollBack();
            Flux::toast('Calculation error: '.$e->getMessage(), variant: 'danger');
        }
    }

    public function markPaid(string $billFinanceId): void
    {
        $bill = BillFinance::where('bill_finance_id', $billFinanceId)->first();
        if ($bill) {
            $bill->update([
                'payment_status' => 'Paid',
                'print_date'     => now()->format('Y-m-d H:i:s'),
                'print_person'   => auth()->user()?->fullName() ?? 'Unknown',
            ]);

            app(AuditService::class)->logAudit(
                "Marked bill {$billFinanceId} as paid",
                auth()->user()?->fullName() ?? 'Unknown'
            );

            Flux::toast("Bill {$bill->meter_serial} marked as Paid.", variant: 'success');
        }
    }

    public function previewReceipt(string $billFinanceId): void
    {
        $this->selectedBill = BillFinance::with('customer')->where('bill_finance_id', $billFinanceId)->first();
        if ($this->selectedBill) {
            $this->modal('receipt-modal')->show();
        } else {
            Flux::toast('Bill record not found.', variant: 'danger');
        }
    }

    public function render(): mixed
    {
        $months = faan_oromo_months();
        $years = [];
        for ($i = 0; $i < 6; $i++) {
            $years[] = (string) (date('Y') - $i);
        }
        if (! in_array($this->year, $years, true)) {
            $years[] = $this->year;
        }

        $query = BillFinance::with('customer')
            ->where('bill_year', $this->year)
            ->where('bill_month', $this->month);

        if ($this->filterStatus !== 'all') {
            $query->where('payment_status', $this->filterStatus);
        }

        if (!empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('meter_serial', 'like', "%{$s}%")
                  ->orWhere('full_name', 'like', "%{$s}%");
            });
        }

        $bills = $query->orderBy('meter_serial')->get();

        $allBillsInPeriod = BillFinance::where('bill_year', $this->year)
            ->where('bill_month', $this->month)
            ->get();

        $totalAmount  = $allBillsInPeriod->sum(fn ($b) => (float) $b->total_monthly_cost);
        $paidCount    = $allBillsInPeriod->where('payment_status', 'Paid')->count();
        $unpaidCount  = count($allBillsInPeriod) - $paidCount;
        $paidAmount   = $allBillsInPeriod->where('payment_status', 'Paid')->sum(fn ($b) => (float) $b->total_monthly_cost);
        $unpaidAmount = $totalAmount - $paidAmount;

        return view('pages.⚡bills', [
            'months'       => $months,
            'years'        => $years,
            'bills'        => $bills,
            'totalAmount'  => $totalAmount,
            'paidCount'    => $paidCount,
            'unpaidCount'  => $unpaidCount,
            'paidAmount'   => $paidAmount,
            'unpaidAmount' => $unpaidAmount,
            'allCount'     => count($allBillsInPeriod),
        ]);
    }
};
?>

<div>
    <!-- Page Header Banner -->
    <div class="gsap-hero flex flex-wrap items-end justify-between gap-4 mb-6">
        <div class="min-w-0">
            <h2 class="m-0 text-[22px] font-bold tracking-tight text-slate-900 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-700 inline-flex items-center justify-center shrink-0">{!! icon('receipt', 20) !!}</span>
                <span>{{ t('Bills & Printing Management') }}</span>
            </h2>
            <p class="mt-2 text-[13px] text-slate-500">
                {{ t('Generate, review, calculate and print customer water utility bills') }}
                <flux:badge color="emerald" size="sm" class="ml-1">{{ $year }} {{ $month }}</flux:badge>
            </p>
        </div>

        <!-- Period Picker Toolbar -->
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 bg-white p-2.5 border border-slate-200 rounded-xl shadow-xs">
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ t('Year') }}:</span>
                    <select wire:model.live="year" class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                        @foreach ($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ t('Month') }}:</span>
                    <select wire:model.live="month" class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                        @foreach ($months as $m)
                            <option value="{{ $m }}">{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <flux:button variant="primary" icon="bolt" wire:click="calculateBills" wire:confirm="Calculate and generate bills for {{ $year }} {{ $month }}?">
                {{ t('Calculate Bills') }}
            </flux:button>
        </div>
    </div>

    <!-- KPI Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-kpi :label="t('Total Bills Generated')" :value="number_format($allCount)" :subvalue="number_format($totalAmount, 0).' ETB Total'" icon="receipt" color="emerald" />
        <x-kpi :label="t('Paid Revenue')" :value="number_format($paidAmount, 0).' ETB'" :subvalue="$paidCount.' Paid ('.($allCount > 0 ? number_format(($paidCount/$allCount)*100, 1) : 0).'%)'" icon="check" color="emerald" :active="true" />
        <x-kpi :label="t('Unpaid Balance')" :value="number_format($unpaidAmount, 0).' ETB'" :subvalue="$unpaidCount.' Pending Accounts'" icon="x-mark" color="rose" />
        <x-kpi :label="t('Average Bill')" :value="number_format($allCount > 0 ? $totalAmount / $allCount : 0, 0).' ETB'" :subvalue="t('Per customer account')" icon="water" color="sky" />
    </div>

    <!-- Toolbar & Segmented Filter Bar -->
    <flux:card class="p-4 mb-6 flex flex-wrap items-center gap-3">
        <div class="segmented bg-slate-100 p-1">
            <button type="button" class="{{ $filterStatus==='all'?'active':'' }}" wire:click="setFilterStatus('all')">{{ t('All Bills') }} ({{ $allCount }})</button>
            <button type="button" class="{{ $filterStatus==='Paid'?'active':'' }}" wire:click="setFilterStatus('Paid')">{!! icon('check', 12) !!} {{ t('Paid') }} ({{ $paidCount }})</button>
            <button type="button" class="{{ $filterStatus==='Unpaid'?'active':'' }}" wire:click="setFilterStatus('Unpaid')">{!! icon('x-mark', 12) !!} {{ t('Unpaid') }} ({{ $unpaidCount }})</button>
        </div>

        <div class="relative flex-1 min-w-[220px] max-w-[360px]">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ t('Search customer code, name...') }}" icon="magnifying-glass" />
        </div>

        <div class="flex gap-2.5 items-center ml-auto">
            <flux:button variant="subtle" icon="arrow-down-tray" href="{{ route('export.bills') }}?year={{ $year }}&month={{ urlencode($month) }}">
                {{ t('Export CSV') }}
            </flux:button>
            <flux:button variant="subtle" icon="printer" onclick="window.print()">
                {{ t('Batch Print Receipts') }}
            </flux:button>
        </div>
    </flux:card>

    <!-- Bills Registry Data Table -->
    <flux:card class="p-0 overflow-hidden">
        <div class="h-1 bg-emerald-600"></div>
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5 border-b border-slate-100 bg-slate-50/50">
            <span class="font-bold text-sm text-slate-900">
                {{ t('Generated Water Bills') }} — {{ $year }} {{ $month }}
                <flux:badge color="zinc" size="sm" class="ml-2">{{ count($bills) }} {{ t('records') }}</flux:badge>
            </span>
            <span class="text-xs text-slate-500">
                Paid: <strong class="text-emerald-700 font-mono tabular-nums">{{ number_format($paidAmount, 0) }} ETB</strong> &bull; Unpaid: <strong class="text-rose-600 font-mono tabular-nums">{{ number_format($unpaidAmount, 0) }} ETB</strong>
            </span>
        </div>

        <div class="scrollable-table border-0 rounded-none">
            <div class="scroll-progress"><div class="scroll-progress-bar"></div></div>
            <div class="table-scroll-view">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold">
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Code') }}</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Customer Name') }}</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Cons. (m³)') }}</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Water Fee') }}</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Meter') }}</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Svc Fee') }}</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Penalty') }}</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Fund') }}</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Total Cost') }}</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Status') }}</th>
                            <th class="text-right px-4 py-3 whitespace-nowrap">{{ t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($bills as $b)
                        <tr class="border-b border-slate-100 odd:bg-white even:bg-slate-50/40 hover:bg-emerald-50/60 transition-colors">
                            <td class="px-4 py-2.5 text-slate-700 align-middle"><span class="inline-block font-mono font-bold text-[12.5px] text-emerald-700 bg-emerald-50 px-2 py-1 rounded-md border border-emerald-200">{{ $b->meter_serial }}</span></td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle">
                                <div class="font-bold text-slate-900 text-[13px]">{{ $b->customer ? trim(($b->customer->first_name ?? '').' '.($b->customer->middle_name ?? '').' '.($b->customer->last_name ?? '')) : $b->full_name }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">Kebele: {{ $b->customer->kebele ?? '01' }}</div>
                            </td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle font-mono tabular-nums"><strong>{{ number_format($b->consumption, 1) }}</strong></td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle font-mono tabular-nums">{{ number_format($b->consumption_cost, 0) }}</td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle font-mono tabular-nums">{{ number_format($b->meter_price, 0) }}</td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle font-mono tabular-nums">{{ number_format($b->service_price, 0) }}</td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle font-mono tabular-nums">{{ number_format($b->penalty_cost, 0) }}</td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle font-mono tabular-nums">{{ number_format($b->state_price, 0) }}</td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle"><strong class="text-emerald-700 text-[13.5px] font-mono tabular-nums">{{ number_format($b->total_monthly_cost, 0) }} ETB</strong></td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle">
                                @if ($b->payment_status === 'Paid')
                                    <flux:badge color="emerald" icon="check" size="sm">Paid</flux:badge>
                                @else
                                    <flux:badge color="rose" icon="x-mark" size="sm">Unpaid</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button variant="subtle" size="sm" icon="eye" wire:click="previewReceipt('{{ $b->bill_finance_id }}')" title="{{ t('Preview Receipt') }}">
                                        {{ t('Preview') }}
                                    </flux:button>
                                    <flux:button variant="subtle" size="sm" icon="printer" href="{{ route('bills.print', $b->bill_finance_id) }}" target="_blank" title="{{ t('Print Receipt') }}">
                                        {{ t('Print') }}
                                    </flux:button>
                                    @if ($b->payment_status !== 'Paid')
                                        <flux:button variant="primary" size="sm" icon="check" wire:click="markPaid('{{ $b->bill_finance_id }}')" wire:confirm="Mark bill for {{ $b->meter_serial }} as Paid?" title="{{ t('Mark Paid') }}">
                                            {{ t('Pay') }}
                                        </flux:button>
                                    @endif
                                    <flux:button variant="subtle" size="sm" icon="book-open" href="{{ route('customer-ledger.index').'?meterSerial='.urlencode($b->meter_serial) }}" title="{{ t('View Financial Ledger') }}" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if ($bills->isEmpty())
                        <tr>
                            <td colspan="11" class="text-center px-6 py-10 text-slate-500 text-[13px]">{{ t('No bill records generated for this period.') }}</td>
                        </tr>
                    @endif
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50 font-bold border-t border-slate-200">
                            <td colspan="8" class="text-right px-4 py-3 text-slate-700">{{ t('Total Period Monthly Cost') }}:</td>
                            <td class="px-4 py-3 text-emerald-700 text-sm font-mono tabular-nums">{{ number_format($totalAmount, 0) }} ETB</td>
                            <td colspan="2" class="px-4 py-3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </flux:card>

    <!-- Quick Receipt Preview Modal -->
    <flux:modal name="receipt-modal" class="md:w-[540px]">
        @if ($selectedBill)
            <div class="space-y-4">
                <div>
                    <flux:heading size="lg">Water Utility Receipt Voucher</flux:heading>
                    <flux:subheading>Meter Serial: {{ $selectedBill->meter_serial }} &bull; Period: {{ $selectedBill->bill_period }}</flux:subheading>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 font-mono text-xs space-y-2">
                    <div class="flex justify-between border-b border-slate-200 pb-2">
                        <span>Customer Name:</span>
                        <strong class="text-slate-900">{{ $selectedBill->customer ? trim(($selectedBill->customer->first_name ?? '').' '.($selectedBill->customer->middle_name ?? '').' '.($selectedBill->customer->last_name ?? '')) : $selectedBill->full_name }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span>Water Consumption:</span>
                        <span>{{ number_format($selectedBill->consumption, 1) }} m³</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Consumption Cost:</span>
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
                        <span>Penalty Cost:</span>
                        <span>{{ number_format($selectedBill->penalty_cost, 0) }} ETB</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Community Water Fund:</span>
                        <span>{{ number_format($selectedBill->state_price, 0) }} ETB</span>
                    </div>
                    <div class="flex justify-between border-t-2 border-slate-800 pt-2 text-sm font-extrabold text-emerald-800">
                        <span>TOTAL MONTHLY COST:</span>
                        <span>{{ number_format($selectedBill->total_monthly_cost, 0) }} ETB</span>
                    </div>
                </div>

                <div class="flex gap-2 justify-end pt-2">
                    <flux:modal.close>
                        <flux:button variant="subtle">Close</flux:button>
                    </flux:modal.close>
                    <flux:button variant="primary" icon="printer" href="{{ route('bills.print', $selectedBill->bill_finance_id) }}" target="_blank">
                        Print Official Receipt
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>