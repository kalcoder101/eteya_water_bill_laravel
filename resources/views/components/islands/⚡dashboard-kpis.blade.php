<?php

use App\Models\ActiveCustomer;
use App\Models\BillFinance;
use Livewire\Attributes\Lazy;
use Livewire\Component;

new #[Lazy] class extends Component
{
    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 animate-pulse">
            <div class="h-24 bg-slate-100 rounded-xl"></div>
            <div class="h-24 bg-slate-100 rounded-xl"></div>
            <div class="h-24 bg-slate-100 rounded-xl"></div>
            <div class="h-24 bg-slate-100 rounded-xl"></div>
        </div>
        HTML;
    }

    public function render(): mixed
    {
        $totalCustomers = ActiveCustomer::count();
        $activeCount = ActiveCustomer::where('customer_status', 'Active')->count();
        $activePct = $totalCustomers > 0 ? number_format(($activeCount / $totalCustomers) * 100, 1) : '0';
        $dcCount = ActiveCustomer::where('customer_status', 'DC')->count();
        $unpaidBills = BillFinance::where('payment_status', 'Unpaid')->count();
        $paidBills = BillFinance::where('payment_status', 'Paid')->count();

        return view('components.islands.⚡dashboard-kpis', [
            'totalCustomers' => $totalCustomers,
            'activeCount'    => $activeCount,
            'activePct'      => $activePct,
            'dcCount'        => $dcCount,
            'unpaidBills'    => $unpaidBills,
            'paidBills'      => $paidBills,
        ]);
    }
};
?>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-kpi :label="t('Total Registered Customers')" :value="number_format($totalCustomers)" :subvalue="t('Active').' + '.t('Disconnected')" icon="users" color="emerald" />
    <x-kpi :label="t('Active Connected Accounts')" :value="number_format($activeCount)" :subvalue="$activePct.'% '.t('connected rate')" icon="check" color="emerald" :active="true" />
    <x-kpi :label="t('Disconnected Accounts (DC)')" :value="number_format($dcCount)" :subvalue="($totalCustomers - $activeCount - $dcCount).' '.t('pending verification')" icon="x" color="rose" />
    <x-kpi :label="t('Unpaid Billing Invoices')" :value="number_format($unpaidBills)" :subvalue="$paidBills.' '.t('paid invoices')" icon="receipt" color="amber" />
</div>