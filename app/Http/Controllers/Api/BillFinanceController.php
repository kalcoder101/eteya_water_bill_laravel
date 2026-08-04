<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BillFinance;
use App\Models\SeasonalConsumption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillFinanceController extends Controller
{
    public function getBillFinance(?string $param1 = null): JsonResponse
    {
        $row = BillFinance::where('bill_finance_id', $param1)
            ->orWhere('meter_serial', $param1)
            ->orderByDesc('created_at')
            ->first();

        return response()->json($row);
    }

    public function customerLedgerList(Request $request): JsonResponse
    {
        $year = $request->query('billYear', '');
        $ms   = $request->query('meterSerial', '');

        $rows = BillFinance::leftJoin('seasonal_consumptions', function ($join) {
            $join->on('seasonal_consumptions.meter_serial', '=', 'bill_finances.meter_serial')
                 ->whereColumn('seasonal_consumptions.reading_year', 'bill_finances.bill_year')
                 ->whereColumn('seasonal_consumptions.reading_month', 'bill_finances.bill_month');
        })
            ->where('bill_finances.bill_year', $year)
            ->where('bill_finances.meter_serial', $ms)
            ->orderBy('bill_finances.bill_finance_id')
            ->get([
                'bill_finances.*',
                'seasonal_consumptions.meter_reading_id',
                'seasonal_consumptions.reading_date',
                'seasonal_consumptions.current_reading',
                'seasonal_consumptions.collector',
                'seasonal_consumptions.reading_year',
                'seasonal_consumptions.reading_month',
                'seasonal_consumptions.reading_branch',
            ]);

        $out = $rows->map(function ($r) {
            return [
                'seasonalConsumption' => [
                    'meterReadingId' => $r->meter_reading_id ?? null,
                    'meterSerial'    => $r->meter_serial,
                    'readingDate'    => $r->reading_date ?? null,
                    'currentReading' => (float) ($r->current_reading ?? 0),
                    'collector'      => $r->collector ?? null,
                    'readingYear'    => $r->reading_year ?? null,
                    'readingMonth'   => $r->reading_month ?? null,
                    'readingBranch'  => $r->reading_branch ?? null,
                ],
                'billFinance' => [
                    'billFinanceId'        => $r->bill_finance_id,
                    'meterSerial'          => $r->meter_serial,
                    'meterPrice'           => (float) $r->meter_price,
                    'servicePrice'         => (float) $r->service_price,
                    'consumption'          => (float) $r->consumption,
                    'penaltyCost'          => (float) $r->penalty_cost,
                    'communityCost'        => (float) $r->community_cost,
                    'totalMonthlyCost'     => (float) $r->total_monthly_cost,
                    'consumptionCost'      => (float) $r->consumption_cost,
                    'totalAggregationCost' => (float) $r->total_aggregation_cost,
                    'depositedCost'        => (float) $r->deposited_cost,
                    'paymentStatus'        => $r->payment_status,
                    'billYear'             => $r->bill_year,
                    'billMonth'            => $r->bill_month,
                    'statePrice'           => (float) $r->state_price,
                    'depositFund'          => (float) $r->deposit_fund,
                    'billPeriod'           => $r->bill_period,
                    'vatPrice'             => (float) $r->vat_price,
                    'fullName'             => $r->full_name,
                    'kebele'               => $r->kebele,
                    'meterNum'             => (int) $r->meter_num,
                    'customerType'         => $r->customer_type,
                    'printDate'            => $r->print_date,
                    'printPerson'          => $r->print_person,
                    'billNumber'           => $r->bill_number,
                    'windowNumber'         => $r->window_number,
                    'customerBranch'       => $r->customer_branch,
                ],
            ];
        });

        return response()->json($out);
    }
}
