<?php

use App\Models\ActiveCustomer;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Lazy] class extends Component
{
    #[Url(except: 'typeStatus')]
    public string $reportType = 'typeStatus';

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="animate-pulse space-y-4 mb-5">
            <div class="h-10 bg-slate-100 rounded-xl w-64"></div>
            <div class="h-64 bg-slate-100 rounded-xl"></div>
        </div>
        HTML;
    }

    public function setReportType(string $type): void
    {
        $this->reportType = $type;
    }

    public function render(): mixed
    {
        $reportType = $this->reportType;

        $typeList = customer_types();
        $statusList = customer_statuses();

        $rows = [];
        $columnTotals = [];
        $totalCustomers = ActiveCustomer::count();

        if ($reportType === 'type') {
            $raw = ActiveCustomer::select('kebele', 'customer_type', DB::raw('count(*) as aggregate'))
                ->whereNotNull('kebele')
                ->where('kebele', '!=', '')
                ->groupBy('kebele', 'customer_type')
                ->get();

            $grouped = [];
            foreach ($raw as $r) {
                $k = $r->kebele;
                $t = $r->customer_type;
                if (! isset($grouped[$k])) {
                    $grouped[$k] = array_fill_keys($typeList, 0);
                    $grouped[$k]['total'] = 0;
                }
                if (in_array($t, $typeList, true)) {
                    $grouped[$k][$t] = (int) $r->aggregate;
                }
                $grouped[$k]['total'] += (int) $r->aggregate;
            }

            ksort($grouped);
            $rows = $grouped;

            foreach ($typeList as $t) {
                $columnTotals[$t] = array_sum(array_column($rows, $t));
            }
            $columnTotals['total'] = array_sum(array_column($rows, 'total'));
            $title = t('Kebele Distribution by Customer Category Type');
        } elseif ($reportType === 'status') {
            $raw = ActiveCustomer::select('kebele', 'customer_status', DB::raw('count(*) as aggregate'))
                ->whereNotNull('kebele')
                ->where('kebele', '!=', '')
                ->groupBy('kebele', 'customer_status')
                ->get();

            $grouped = [];
            foreach ($raw as $r) {
                $k = $r->kebele;
                $st = $r->customer_status;
                if (! isset($grouped[$k])) {
                    $grouped[$k] = array_fill_keys($statusList, 0);
                    $grouped[$k]['total'] = 0;
                }
                if (in_array($st, $statusList, true)) {
                    $grouped[$k][$st] = (int) $r->aggregate;
                }
                $grouped[$k]['total'] += (int) $r->aggregate;
            }

            ksort($grouped);
            $rows = $grouped;

            foreach ($statusList as $st) {
                $columnTotals[$st] = array_sum(array_column($rows, $st));
            }
            $columnTotals['total'] = array_sum(array_column($rows, 'total'));
            $title = t('Kebele Distribution by Account Status');
        } else {
            $reportType = 'typeStatus';
            $raw = ActiveCustomer::select('customer_type', 'customer_status', DB::raw('count(*) as aggregate'))
                ->groupBy('customer_type', 'customer_status')
                ->get();

            $grouped = [];
            foreach ($raw as $r) {
                $t = $r->customer_type ?: 'Unknown';
                $st = $r->customer_status ?: 'Unknown';
                if (! isset($grouped[$t])) {
                    $grouped[$t] = array_fill_keys($statusList, 0);
                    $grouped[$t]['total'] = 0;
                }
                if (in_array($st, $statusList, true)) {
                    $grouped[$t][$st] = (int) $r->aggregate;
                }
                $grouped[$t]['total'] += (int) $r->aggregate;
            }

            ksort($grouped);
            $rows = $grouped;

            foreach ($statusList as $st) {
                $columnTotals[$st] = array_sum(array_column($rows, $st));
            }
            $columnTotals['total'] = array_sum(array_column($rows, 'total'));
            $title = t('Customer Category Type × Status Matrix');
        }

        return view('components.islands.⚡statistics-pivots', [
            'reportType'     => $reportType,
            'title'          => $title,
            'rows'           => $rows,
            'typeList'       => $typeList,
            'statusList'     => $statusList,
            'columnTotals'   => $columnTotals,
            'totalCustomers' => $totalCustomers,
        ]);
    }
};
?>

<div>
    <!-- Report Type Mode Switcher Toolbar -->
    <div class="flex items-center justify-between gap-3 mb-4">
        <div class="font-bold text-slate-900 text-sm flex items-center gap-2">
            <span class="text-emerald-600">{!! icon('table', 16) !!}</span> {{ $title }}
        </div>
        <div class="segmented bg-slate-100 p-1">
            <button type="button" class="{{ $reportType==='typeStatus'?'active':'' }}" wire:click="setReportType('typeStatus')">{{ t('Type × Status') }}</button>
            <button type="button" class="{{ $reportType==='type'?'active':'' }}" wire:click="setReportType('type')">{{ t('By Type') }}</button>
            <button type="button" class="{{ $reportType==='status'?'active':'' }}" wire:click="setReportType('status')">{{ t('By Status') }}</button>
        </div>
    </div>

    <!-- Detailed Data Breakdown Pivot Table Card -->
    <flux:card class="overflow-hidden p-0 mb-5">
        <div class="h-1 bg-emerald-600"></div>
        <div class="flex items-center justify-between gap-3 px-5 py-3.5 border-b border-slate-100 bg-slate-50/50">
            <span class="font-bold text-sm text-slate-900">{{ $title }}</span>
            <flux:badge color="zinc" size="sm">{{ count($rows) }} {{ t('Pivot Rows') }}</flux:badge>
        </div>

        <div class="scrollable-table border-0 rounded-none">
            <div class="scroll-progress"><div class="scroll-progress-bar"></div></div>
            <div class="table-scroll-view">
                @if ($reportType === 'type')
                    <table class="w-full text-[13px]">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold">
                                <th class="text-left px-4 py-3">{{ t('Kebele') }}</th>
                                @foreach ($typeList as $t)
                                    <th class="text-right px-4 py-3">{{ $t }}</th>
                                @endforeach
                                <th class="text-right px-4 py-3 bg-emerald-50/60 text-emerald-900">{{ t('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($rows as $k => $data)
                            <tr class="border-b border-slate-100 odd:bg-white even:bg-slate-50/40 hover:bg-emerald-50/60 transition-colors">
                                <td class="px-4 py-2.5 font-bold text-slate-900">Kebele {{ $k }}</td>
                                @foreach ($typeList as $t)
                                    <td class="px-4 py-2.5 text-right font-mono tabular-nums text-slate-700">{{ number_format($data[$t] ?? 0) }}</td>
                                @endforeach
                                <td class="px-4 py-2.5 text-right font-mono tabular-nums font-bold text-emerald-800 bg-emerald-50/40">{{ number_format($data['total'] ?? 0) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-100 font-bold border-t-2 border-slate-300 text-slate-900">
                                <td class="px-4 py-3">{{ t('Grand Total') }}</td>
                                @foreach ($typeList as $t)
                                    <td class="px-4 py-3 text-right font-mono tabular-nums">{{ number_format($columnTotals[$t] ?? 0) }}</td>
                                @endforeach
                                <td class="px-4 py-3 text-right font-mono tabular-nums text-emerald-800 bg-emerald-100/60 text-sm">{{ number_format($columnTotals['total'] ?? 0) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                @elseif ($reportType === 'status')
                    <table class="w-full text-[13px]">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold">
                                <th class="text-left px-4 py-3">{{ t('Kebele') }}</th>
                                @foreach ($statusList as $st)
                                    <th class="text-right px-4 py-3">{{ $st }}</th>
                                @endforeach
                                <th class="text-right px-4 py-3 bg-emerald-50/60 text-emerald-900">{{ t('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($rows as $k => $data)
                            <tr class="border-b border-slate-100 odd:bg-white even:bg-slate-50/40 hover:bg-emerald-50/60 transition-colors">
                                <td class="px-4 py-2.5 font-bold text-slate-900">Kebele {{ $k }}</td>
                                @foreach ($statusList as $st)
                                    <td class="px-4 py-2.5 text-right font-mono tabular-nums text-slate-700">{{ number_format($data[$st] ?? 0) }}</td>
                                @endforeach
                                <td class="px-4 py-2.5 text-right font-mono tabular-nums font-bold text-emerald-800 bg-emerald-50/40">{{ number_format($data['total'] ?? 0) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-100 font-bold border-t-2 border-slate-300 text-slate-900">
                                <td class="px-4 py-3">{{ t('Grand Total') }}</td>
                                @foreach ($statusList as $st)
                                    <td class="px-4 py-3 text-right font-mono tabular-nums">{{ number_format($columnTotals[$st] ?? 0) }}</td>
                                @endforeach
                                <td class="px-4 py-3 text-right font-mono tabular-nums text-emerald-800 bg-emerald-100/60 text-sm">{{ number_format($columnTotals['total'] ?? 0) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                @else
                    <table class="w-full text-[13px]">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold">
                                <th class="text-left px-4 py-3">{{ t('Customer Category Type') }}</th>
                                @foreach ($statusList as $st)
                                    <th class="text-right px-4 py-3">{{ $st }}</th>
                                @endforeach
                                <th class="text-right px-4 py-3 bg-emerald-50/60 text-emerald-900">{{ t('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($rows as $t => $data)
                            <tr class="border-b border-slate-100 odd:bg-white even:bg-slate-50/40 hover:bg-emerald-50/60 transition-colors">
                                <td class="px-4 py-2.5 font-bold text-slate-900">{{ $t }}</td>
                                @foreach ($statusList as $st)
                                    <td class="px-4 py-2.5 text-right font-mono tabular-nums text-slate-700">{{ number_format($data[$st] ?? 0) }}</td>
                                @endforeach
                                <td class="px-4 py-2.5 text-right font-mono tabular-nums font-bold text-emerald-800 bg-emerald-50/40">{{ number_format($data['total'] ?? 0) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-100 font-bold border-t-2 border-slate-300 text-slate-900">
                                <td class="px-4 py-3">{{ t('Grand Total') }}</td>
                                @foreach ($statusList as $st)
                                    <td class="px-4 py-3 text-right font-mono tabular-nums">{{ number_format($columnTotals[$st] ?? 0) }}</td>
                                @endforeach
                                <td class="px-4 py-3 text-right font-mono tabular-nums text-emerald-800 bg-emerald-100/60 text-sm">{{ number_format($columnTotals['total'] ?? 0) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                @endif
            </div>
        </div>
    </flux:card>
</div>