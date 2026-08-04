<?php

namespace App\Http\Controllers;

use App\Models\ActiveCustomer;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Writer;

class ImportExportController extends Controller
{
    public function importCustomers(Request $request)
    {
        $request->validate([
            'excelFile' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('excelFile');
        $csv = Reader::createFromPath($file->getRealPath(), 'r');
        $csv->setHeaderOffset(0);

        $header = $csv->getHeader();
        $records = $csv->getRecords();

        $imported = 0;
        $skipped = 0;

        foreach ($records as $row) {
            $get = function ($key, $default = null) use ($row) {
                foreach ([$key, strtolower($key), str_replace('_', '', $key)] as $variant) {
                    if (isset($row[$variant])) {
                        return $row[$variant];
                    }
                }

                return $default;
            };

            $code = $get('meterSerial') ?: $get('meter_serial') ?: $get('code');
            if (! $code) {
                $skipped++;
                continue;
            }

            try {
                ActiveCustomer::updateOrCreate(
                    ['meter_serial' => $code],
                    [
                        'first_name'       => $get('firstName') ?: $get('first_name'),
                        'middle_name'     => $get('middleName') ?: $get('middle_name'),
                        'last_name'       => $get('lastName') ?: $get('last_name'),
                        'kebele'          => $get('kebele'),
                        'sold_date'       => $get('soldDate') ?: $get('sold_date') ?: date('Y-m-d'),
                        'meter_num'       => (int) ($get('meterNum') ?: $get('meter_num') ?: 0),
                        'meter_size'      => $get('meterSize') ?: $get('meter_size') ?: '1/2"',
                        'customer_type'   => $get('customerType') ?: $get('customer_type') ?: 'Dhunfaa',
                        'bill_num'        => $get('billNum') ?: $get('bill_num'),
                        'phone_number'    => $get('phoneNumber') ?: $get('phone_number'),
                        'start_value'     => (float) ($get('startValue') ?: $get('start_value') ?: 0),
                        'payment_way'     => $get('paymentWay') ?: $get('payment_way') ?: 'BANK',
                        'customer_branch' => $get('customerBranch') ?: $get('customer_branch') ?: 'Eteya',
                        'customer_status' => $get('customerStatus') ?: $get('customer_status') ?: 'Active',
                        'sync_status'     => 'New',
                        'reader_block'    => $get('readerBlock') ?: $get('reader_block'),
                    ]
                );
                $imported++;
            } catch (\Throwable $e) {
                $skipped++;
            }
        }

        app(AuditService::class)->logAudit(
            "Imported $imported customers from Excel",
            auth()->user()?->fullName() ?? 'Unknown'
        );

        return response()->json(['imported' => $imported, 'skipped' => $skipped]);
    }

    public function exportCustomers(Request $request)
    {
        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->setOutputBOM(Reader::BOM_UTF8);
        $csv->insertOne([
            'meterSerial', 'firstName', 'middleName', 'lastName', 'kebele', 'soldDate',
            'meterNum', 'meterSize', 'customerType', 'billNum', 'phoneNumber', 'startValue',
            'paymentWay', 'customerBranch', 'customerStatus', 'syncStatus', 'readerBlock',
        ]);

        $rows = ActiveCustomer::orderBy('meter_serial')->get();
        foreach ($rows as $r) {
            $csv->insertOne([
                $r->meter_serial, $r->first_name, $r->middle_name, $r->last_name,
                $r->kebele, $r->sold_date, $r->meter_num, $r->meter_size,
                $r->customer_type, $r->bill_num, $r->phone_number, $r->start_value,
                $r->payment_way, $r->customer_branch, $r->customer_status, $r->sync_status,
                $r->reader_block,
            ]);
        }

        return response($csv->getContent(), 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="customers-'.date('Y-m-d').'.csv"',
        ]);
    }

    public function exportBills(Request $request)
    {
        $year  = $request->get('year', date('Y'));
        $month = $request->get('month', 'Fulbaana');

        $rows = DB::table('bill_finances')
            ->join('active_customers', 'active_customers.meter_serial', '=', 'bill_finances.meter_serial')
            ->where('bill_finances.bill_year', $year)
            ->where('bill_finances.bill_month', $month)
            ->orderBy('bill_finances.meter_serial')
            ->get();

        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->setOutputBOM(Reader::BOM_UTF8);
        $csv->insertOne(['Code', 'Customer', 'Phone', 'Consumption', 'BillCost', 'MeterRent', 'Service', 'Penalty', 'Community', 'WaterFund', 'Deposit', 'Total', 'Status']);

        foreach ($rows as $r) {
            $csv->insertOne([
                $r->meter_serial,
                trim(($r->first_name ?? '').' '.($r->middle_name ?? '').' '.($r->last_name ?? '')),
                $r->phone_number,
                $r->consumption,
                $r->consumption_cost,
                $r->meter_price,
                $r->service_price,
                $r->penalty_cost,
                $r->community_cost,
                $r->state_price,
                $r->deposited_cost,
                $r->total_monthly_cost,
                $r->payment_status,
            ]);
        }

        return response($csv->getContent(), 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="bills-'.$year.'-'.$month.'-'.date('Y-m-d').'.csv"',
        ]);
    }

    public function exportLedger(Request $request)
    {
        $meterSerial = $request->get('meterSerial', '');
        $year = $request->get('year', '');
        if (! $meterSerial || ! $year) {
            abort(400, 'Missing parameters');
        }

        $customer = ActiveCustomer::where('meter_serial', $meterSerial)->first();

        $rows = DB::table('bill_finances')
            ->leftJoin('seasonal_consumptions', function ($join) {
                $join->on('seasonal_consumptions.meter_serial', '=', 'bill_finances.meter_serial')
                     ->whereColumn('seasonal_consumptions.reading_year', 'bill_finances.bill_year')
                     ->whereColumn('seasonal_consumptions.reading_month', 'bill_finances.bill_month');
            })
            ->where('bill_finances.meter_serial', $meterSerial)
            ->where('bill_finances.bill_year', $year)
            ->orderBy('bill_finances.bill_finance_id')
            ->get();

        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->setOutputBOM(Reader::BOM_UTF8);
        $csv->insertOne(['Customer Code', 'Customer Name', 'Bill Year']);
        $csv->insertOne([
            $meterSerial,
            $customer ? trim($customer->first_name.' '.$customer->middle_name.' '.$customer->last_name) : '',
            $year,
        ]);
        $csv->insertOne([]);
        $csv->insertOne(['#', 'Month', 'Reading Date', 'Previous R', 'Current R', 'Use (m³)', 'Bill Cost', 'Meter Rent', 'Service', 'Penalty', 'Community', 'Water Fund', 'Deposit', 'Total', 'Status']);

        $prev = (float) ($customer->start_value ?? 0);
        $i = 0;
        foreach ($rows as $r) {
            $i++;
            $cur = (float) ($r->current_reading ?? 0);
            $use = $cur - $prev;
            $csv->insertOne([
                $i, $r->bill_month, $r->reading_date,
                $prev, $cur, $use,
                $r->consumption_cost, $r->meter_price, $r->service_price,
                $r->penalty_cost, $r->community_cost, $r->state_price,
                $r->deposited_cost, $r->total_monthly_cost, $r->payment_status,
            ]);
            $prev = $cur;
        }

        return response($csv->getContent(), 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="ledger-'.$meterSerial.'-'.$year.'.csv"',
        ]);
    }
}
