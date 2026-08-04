<?php

namespace App\Http\Controllers;

use App\Models\ActiveCustomer;
use App\Models\BillFinance;
use App\Models\SeasonalConsumption;
use App\Services\AuditService;
use App\Services\BillCalculatorService;
use App\Services\I18nService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    public function index(Request $request)
    {
        $year  = (string) ($request->get('year') ?? get_setting('current_bill_year', date('Y')));
        $month = $request->get('month', get_setting('current_bill_month', 'Fulbaana'));
        $months = faan_oromo_months();

        $years = [];
        for ($i = 0; $i < 6; $i++) {
            $years[] = (string) (date('Y') - $i);
        }
        if (! in_array($year, $years)) {
            $years[] = $year;
        }

        $bills = BillFinance::with('customer')
            ->where('bill_year', $year)
            ->where('bill_month', $month)
            ->orderBy('meter_serial')
            ->get();

        $totalAmount  = $bills->sum(fn ($b) => (float) $b->total_monthly_cost);
        $paidCount    = $bills->where('payment_status', 'Paid')->count();
        $unpaidCount  = count($bills) - $paidCount;
        $paidAmount   = $bills->where('payment_status', 'Paid')->sum(fn ($b) => (float) $b->total_monthly_cost);
        $unpaidAmount = $totalAmount - $paidAmount;

        return view('bills.index', [
            'year'         => $year,
            'month'        => $month,
            'months'       => $months,
            'years'        => $years,
            'bills'        => $bills,
            'totalAmount'  => $totalAmount,
            'paidCount'    => $paidCount,
            'unpaidCount'  => $unpaidCount,
            'paidAmount'   => $paidAmount,
            'unpaidAmount' => $unpaidAmount,
            'pageTitle'    => 'Bills & Printing',
        ]);
    }

    public function calculate(Request $request)
    {
        $year  = (string) $request->get('year', date('Y'));
        $month = (string) $request->get('month', 'Fulbaana');

        try {
            DB::beginTransaction();

            $customers = ActiveCustomer::where('customer_status', 'Active')
                ->leftJoin('seasonal_consumptions', function ($join) use ($year, $month) {
                    $join->on('seasonal_consumptions.meter_serial', '=', 'active_customers.meter_serial')
                         ->where('seasonal_consumptions.reading_year', $year)
                         ->where('seasonal_consumptions.reading_month', $month);
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
                $billId = generate_bill_finance_id($c->meter_serial, $year, $month);
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
                        'bill_year'              => $year,
                        'bill_month'             => $month,
                        'state_price'            => $bill['state_price'],
                        'deposit_fund'           => 0,
                        'calculate_status'       => 'Calculated',
                        'bill_period'            => "$year - $month",
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
                "Calculated $created new + $updated updated bills for $year $month",
                auth()->user()?->fullName() ?? 'System'
            );

            return response()->json([
                'created' => $created,
                'updated' => $updated,
                'total'   => $customers->count(),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function markPaid(Request $request, string $id)
    {
        $bill = BillFinance::where('bill_finance_id', $id)->first();
        if ($bill) {
            $bill->update([
                'payment_status' => 'Paid',
                'print_date'     => now()->format('Y-m-d H:i:s'),
                'print_person'   => auth()->user()?->fullName() ?? 'Unknown',
            ]);
            app(AuditService::class)->logAudit(
                "Marked bill $id as paid",
                auth()->user()?->fullName() ?? 'Unknown'
            );
        }

        return redirect()->route('bills.index');
    }

    public function print(Request $request, string $id)
    {
        $bill = BillFinance::where('bill_finance_id', $id)->first();
        if (! $bill) {
            abort(404, 'Bill not found');
        }

        $customer = ActiveCustomer::where('meter_serial', $bill->meter_serial)->first();
        $reading = SeasonalConsumption::where('meter_serial', $bill->meter_serial)
            ->where('reading_year', $bill->bill_year)
            ->where('reading_month', $bill->bill_month)
            ->first();

        $prevReading = (float) ($customer?->start_value ?? 0);
        $curReading  = (float) ($reading?->current_reading ?? ($bill->consumption + $prevReading));
        $consumption = (float) $bill->consumption;

        $fullName = trim(
            ($customer?->first_name ?? '').' '.
            ($customer?->middle_name ?? '').' '.
            ($customer?->last_name ?? '')
        );

        $printDate  = now()->format('d/m/Y');
        $printTime  = now()->format('H:i:s');
        $billNumber = $bill->bill_number ?: ('B-'.now()->format('ymd-His'));
        $collector  = ($reading?->collector ?? $bill->print_person) ?: 'Cashier';

        return view('bills.print', [
            'bill'        => $bill,
            'customer'    => $customer,
            'reading'     => $reading,
            'prevReading' => $prevReading,
            'curReading'  => $curReading,
            'consumption'  => $consumption,
            'fullName'    => $fullName,
            'printDate'   => $printDate,
            'printTime'   => $printTime,
            'billNumber'  => $billNumber,
            'collector'   => $collector,
            'enterpriseOR' => get_setting('enterprise_name_or', "Dhaabbata Tajaajila Bishaan Dhugaatii fi Dhangala'aa"),
            'townName'    => get_setting('town_name', 'Eteya'),
            'slogan'      => get_setting('bill_slogan', 'Bishaan Lubbuu Dha!!!'),
        ]);
    }
}
