<?php

namespace App\Http\Controllers;

use App\Models\ActiveCustomer;
use App\Models\BillFinance;
use App\Models\SeasonalConsumption;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerLedgerController extends Controller
{
    public function index(Request $request)
    {
        $year = (string) ($request->get('year') ?? get_setting('current_bill_year', date('Y')));
        $meterSerial = $request->get('meterSerial', '');

        $currentYear = (string) get_setting('current_bill_year', date('Y'));

        $availableYears = BillFinance::select('bill_year')
            ->distinct()
            ->orderByDesc('bill_year')
            ->pluck('bill_year')
            ->toArray();

        $defaultYears = [];
        for ($i = 0; $i < 6; $i++) {
            $defaultYears[] = (string) ((int) $currentYear - $i);
        }
        $availableYears = array_unique(array_merge($availableYears, $defaultYears));
        sort($availableYears, SORT_NUMERIC);
        $availableYears = array_reverse($availableYears);

        if (! in_array((string) $year, $availableYears, true)) {
            $availableYears[] = (string) $year;
            sort($availableYears, SORT_NUMERIC);
            $availableYears = array_reverse($availableYears);
        }

        $customer = null;
        $ledger   = collect([]);

        if ($meterSerial) {
            $customer = ActiveCustomer::where('meter_serial', $meterSerial)->first();

            if ($customer) {
                $ledger = BillFinance::where('meter_serial', $meterSerial)
                    ->where('bill_year', $year)
                    ->orderBy('bill_finance_id')
                    ->get()
                    ->map(function ($bill) use ($meterSerial, $year) {
                        $reading = SeasonalConsumption::where('meter_serial', $meterSerial)
                            ->where('reading_year', $year)
                            ->where('reading_month', $bill->bill_month)
                            ->first();
                        $bill->reading_date    = $reading?->reading_date;
                        $bill->current_reading = $reading?->current_reading ?? 0;
                        $bill->collector        = $reading?->collector;

                        return $bill;
                    });
            }
        }

        $customers = ActiveCustomer::whereIn('customer_status', ['Active', 'DC'])
            ->orderBy('meter_serial')
            ->get(['meter_serial', 'first_name', 'middle_name', 'last_name']);

        // Compute totals
        $grandTotal      = 0;
        $paidTotal       = 0;
        $unpaidTotal     = 0;
        $totalConsumption = 0;
        foreach ($ledger as $row) {
            $grandTotal      += (float) $row->total_monthly_cost;
            $totalConsumption += (float) $row->consumption;
            if ($row->payment_status === 'Paid') {
                $paidTotal += (float) $row->total_monthly_cost;
            } else {
                $unpaidTotal += (float) $row->total_monthly_cost;
            }
        }
        $paidBills   = collect($ledger)->filter(fn ($r) => $r->payment_status === 'Paid')->count();
        $unpaidBills = count($ledger) - $paidBills;

        return view('customer-ledger.index', [
            'year'             => $year,
            'meterSerial'      => $meterSerial,
            'customer'         => $customer,
            'customers'        => $customers,
            'ledger'           => $ledger,
            'availableYears'   => $availableYears,
            'grandTotal'       => $grandTotal,
            'paidTotal'        => $paidTotal,
            'unpaidTotal'      => $unpaidTotal,
            'totalConsumption' => $totalConsumption,
            'paidBills'        => $paidBills,
            'unpaidBills'      => $unpaidBills,
            'pageTitle'        => 'Customers Ledger',
            'pageAction'       => ! empty($customer) ? [
                'label'   => t('Export Ledger CSV'),
                'href'    => '#',
                'icon'    => 'download',
                'onclick' => "exportLedger(); return false;",
            ] : null,
        ]);
    }
}
