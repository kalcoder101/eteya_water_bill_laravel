<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActiveCustomer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ActiveCustomerController extends Controller
{
    public function getRecentCode(Request $request): Response
    {
        $kebele = $request->query('kebele', '');

        return response(generate_customer_code($kebele), 200, ['Content-Type' => 'text/plain']);
    }

    public function getAllActiveCustomers(): JsonResponse
    {
        return response()->json(
            ActiveCustomer::whereIn('customer_status', ['Active', 'DC'])
                ->orderBy('meter_serial')
                ->get()
        );
    }

    public function getSingleCustomer(?string $meterSerial = null): JsonResponse
    {
        return response()->json(
            ActiveCustomer::where('meter_serial', $meterSerial)->get()
        );
    }

    public function getActiveCustomer(?string $meterSerial = null): JsonResponse
    {
        return response()->json(
            ActiveCustomer::where('meter_serial', $meterSerial)->first()
        );
    }

    public function count(): Response
    {
        $n = ActiveCustomer::whereIn('customer_status', ['Active', 'DC'])->count();

        return response((string) $n, 200, ['Content-Type' => 'text/plain']);
    }

    public function countStatus(Request $request): Response
    {
        $st = $request->query('customerStatus', 'Active');
        $n = ActiveCustomer::where('customer_status', $st)->count();

        return response((string) $n, 200, ['Content-Type' => 'text/plain']);
    }

    public function checkCustomerExists(?string $meterSerial = null): Response
    {
        $exists = ActiveCustomer::where('meter_serial', $meterSerial)->exists();

        return response($exists ? 'true' : 'false', 200, ['Content-Type' => 'text/plain']);
    }

    public function search(Request $request): JsonResponse
    {
        $name = $request->query('name', '');
        $like = "%{$name}%";

        return response()->json(
            ActiveCustomer::whereIn('customer_status', ['Active', 'DC'])
                ->where(function ($q) use ($like) {
                    $q->where('first_name', 'like', $like)
                      ->orWhere('middle_name', 'like', $like)
                      ->orWhere('last_name', 'like', $like)
                      ->orWhere('meter_serial', 'like', $like)
                      ->orWhere('phone_number', 'like', $like);
                })
                ->orderBy('meter_serial')
                ->get()
        );
    }

    public function paymentWayList(Request $request): JsonResponse
    {
        $pw = $request->query('paymentWay', '');
        $st = $request->query('customerStatus', 'Active');

        return response()->json(
            ActiveCustomer::where('payment_way', $pw)
                ->where('customer_status', $st)
                ->orderBy('meter_serial')
                ->get()
        );
    }

    public function fetchCustomersSoldDate(Request $request): JsonResponse
    {
        $m = $request->query('month', '');
        $y = $request->query('year', '');

        return response()->json(
            ActiveCustomer::where('sold_date', 'like', "%{$y}-{$m}%")
                ->orderBy('meter_serial')
                ->get()
        );
    }

    public function fetchCustomersSoldDateCount(Request $request): Response
    {
        $m = $request->query('month', '');
        $y = $request->query('year', '');
        $n = ActiveCustomer::where('sold_date', 'like', "%{$y}-{$m}%")->count();

        return response((string) $n, 200, ['Content-Type' => 'text/plain']);
    }

    public function reports(Request $request, ?string $reportType = null): JsonResponse
    {
        if ($reportType === 'kebele-customerType-pivot') {
            $sql = "SELECT kebele,
                      SUM(CASE WHEN customer_type='Dhunfaa' THEN 1 ELSE 0 END) AS privateCount,
                      SUM(CASE WHEN customer_type='Waajjira Motummaa' THEN 1 ELSE 0 END) AS governmentCount,
                      SUM(CASE WHEN customer_type='Waajjira Miti-Motummaa' THEN 1 ELSE 0 END) AS nonGovernmentCount,
                      SUM(CASE WHEN customer_type IN ('Daldaltoota fi Industry','Boonoo') THEN 1 ELSE 0 END) AS commercialCount,
                      COUNT(*) AS total
                    FROM active_customers
                    WHERE customer_status IN ('Active','DC')
                    GROUP BY kebele ORDER BY kebele";

            return response()->json(DB::select($sql));
        }

        if ($reportType === 'kebele-customerStatus-pivot') {
            $sql = "SELECT kebele,
                      SUM(CASE WHEN customer_status='Active'  THEN 1 ELSE 0 END) AS activeCount,
                      SUM(CASE WHEN customer_status='DC'      THEN 1 ELSE 0 END) AS dcCount,
                      SUM(CASE WHEN customer_status='Updated' THEN 1 ELSE 0 END) AS updatedCount,
                      SUM(CASE WHEN customer_status='Deleted' THEN 1 ELSE 0 END) AS deletedCount
                    FROM active_customers
                    GROUP BY kebele ORDER BY kebele";

            return response()->json(DB::select($sql));
        }

        if ($reportType === 'kebele-customerTypeStatus-pivot') {
            $sql = "SELECT kebele,
                      SUM(CASE WHEN customer_type='Dhunfaa' AND customer_status='Active'  THEN 1 ELSE 0 END) AS dhunfaaActive,
                      SUM(CASE WHEN customer_type='Dhunfaa' AND customer_status='DC'      THEN 1 ELSE 0 END) AS dhunfaaDc,
                      SUM(CASE WHEN customer_type='Dhunfaa' AND customer_status='Updated' THEN 1 ELSE 0 END) AS dhunfaaUpdated,
                      SUM(CASE WHEN customer_type='Dhunfaa' AND customer_status='Deleted' THEN 1 ELSE 0 END) AS dhunfaaDeleted,
                      SUM(CASE WHEN customer_type='Daldaltoota fi Industry' AND customer_status='Active'  THEN 1 ELSE 0 END) AS daldaltootaIndustryActive,
                      SUM(CASE WHEN customer_type='Daldaltoota fi Industry' AND customer_status='DC'      THEN 1 ELSE 0 END) AS daldaltootaIndustryDc,
                      SUM(CASE WHEN customer_type='Daldaltoota fi Industry' AND customer_status='Updated' THEN 1 ELSE 0 END) AS daldaltootaIndustryUpdated,
                      SUM(CASE WHEN customer_type='Daldaltoota fi Industry' AND customer_status='Deleted' THEN 1 ELSE 0 END) AS daldaltootaIndustryDeleted,
                      SUM(CASE WHEN customer_type='Waajjira Motummaa' AND customer_status='Active'  THEN 1 ELSE 0 END) AS waajjiraMotummaaActive,
                      SUM(CASE WHEN customer_type='Waajjira Motummaa' AND customer_status='DC'      THEN 1 ELSE 0 END) AS waajjiraMotummaaDc,
                      SUM(CASE WHEN customer_type='Waajjira Motummaa' AND customer_status='Updated' THEN 1 ELSE 0 END) AS waajjiraMotummaaUpdated,
                      SUM(CASE WHEN customer_type='Waajjira Motummaa' AND customer_status='Deleted' THEN 1 ELSE 0 END) AS waajjiraMotummaaDeleted,
                      SUM(CASE WHEN customer_type='Waajjira Miti-Motummaa' AND customer_status='Active'  THEN 1 ELSE 0 END) AS waajjiraMitiMotummaaActive,
                      SUM(CASE WHEN customer_type='Waajjira Miti-Motummaa' AND customer_status='DC'      THEN 1 ELSE 0 END) AS waajjiraMitiMotummaaDc,
                      SUM(CASE WHEN customer_type='Waajjira Miti-Motummaa' AND customer_status='Updated' THEN 1 ELSE 0 END) AS waajjiraMitiMotummaaUpdated,
                      SUM(CASE WHEN customer_type='Waajjira Miti-Motummaa' AND customer_status='Deleted' THEN 1 ELSE 0 END) AS waajjiraMitiMotummaaDeleted
                    FROM active_customers
                    GROUP BY kebele ORDER BY kebele";

            return response()->json(DB::select($sql));
        }

        return response()->json(['error' => 'Unknown report'], 404);
    }

    public function addActiveCustomer(Request $request): JsonResponse
    {
        $b = $request->json()->all();

        $payload = [
            'meter_serial'     => $b['meterSerial']      ?? '',
            'first_name'       => $b['firstName']        ?? null,
            'middle_name'      => $b['middleName']       ?? null,
            'last_name'        => $b['lastName']         ?? null,
            'kebele'           => $b['kebele']           ?? null,
            'sold_date'        => $b['soldDate']         ?? null,
            'meter_num'        => $b['meterNum']         ?? 0,
            'meter_size'       => $b['meterSize']        ?? null,
            'customer_type'    => $b['customerType']     ?? null,
            'bill_num'         => $b['billNum']          ?? null,
            'phone_number'     => $b['phoneNumber']      ?? null,
            'start_value'      => $b['startValue']       ?? 0,
            'payment_way'      => $b['paymentWay']       ?? null,
            'customer_branch'  => $b['customerBranch']   ?? null,
            'customer_status'  => $b['customerStatus']   ?? 'Active',
            'sync_status'      => $b['syncStatus']       ?? 'New',
            'reader_block'     => $b['readerBlock']      ?? null,
        ];

        ActiveCustomer::updateOrCreate(
            ['meter_serial' => $payload['meter_serial']],
            $payload
        );

        return response()->json([
            'status'       => 'created',
            'meterSerial'  => $b['meterSerial'] ?? '',
        ], 201);
    }

    public function sendActiveCustomers(): JsonResponse
    {
        $n = ActiveCustomer::count();

        return response()->json(['status' => 'synced', 'count' => $n]);
    }

    public function updateCustomerInfo(Request $request): Response
    {
        ActiveCustomer::where('meter_serial', $request->query('meterSerial', ''))
            ->update([
                'first_name'   => $request->query('firstName'),
                'middle_name'  => $request->query('middleName'),
                'last_name'    => $request->query('lastName'),
                'phone_number' => $request->query('phoneNumber'),
            ]);

        return response('1', 200, ['Content-Type' => 'text/plain']);
    }

    public function updateSyncStatus(Request $request): Response
    {
        ActiveCustomer::where('meter_serial', $request->query('meterSerial', ''))
            ->update(['sync_status' => 'Synced']);

        return response('1', 200, ['Content-Type' => 'text/plain']);
    }

    public function updateSingleCustomerStatus(Request $request): Response
    {
        ActiveCustomer::where('meter_serial', $request->query('meterSerial', ''))
            ->update(['customer_status' => $request->query('customerStatus', 'Active')]);

        return response('1', 200, ['Content-Type' => 'text/plain']);
    }

    public function updateStartReading(Request $request): Response
    {
        ActiveCustomer::where('meter_serial', $request->query('meterSerial', ''))
            ->update(['start_value' => (float) $request->query('startValue', 0)]);

        return response('1', 200, ['Content-Type' => 'text/plain']);
    }

    public function updateCustomerType(Request $request): Response
    {
        ActiveCustomer::where('meter_serial', $request->query('meterSerial', ''))
            ->update(['customer_type' => $request->query('customerType', '')]);

        return response('1', 200, ['Content-Type' => 'text/plain']);
    }

    public function updatePhoneNumber(Request $request): Response
    {
        ActiveCustomer::where('meter_serial', $request->query('meterSerial', ''))
            ->update(['phone_number' => $request->query('phoneNumber', '')]);

        return response('1', 200, ['Content-Type' => 'text/plain']);
    }

    public function updatePaymentWay(Request $request): Response
    {
        ActiveCustomer::where('meter_serial', $request->query('meterSerial', ''))
            ->update(['payment_way' => $request->query('paymentWay', '')]);

        return response('1', 200, ['Content-Type' => 'text/plain']);
    }

    public function updateCustomerBranch(Request $request): Response
    {
        ActiveCustomer::where('meter_serial', $request->query('meterSerial', ''))
            ->update(['customer_branch' => $request->query('customerBranch', '')]);

        return response('1', 200, ['Content-Type' => 'text/plain']);
    }

    public function updateMeterSize(Request $request): Response
    {
        ActiveCustomer::where('meter_serial', $request->query('meterSerial', ''))
            ->update([
                'meter_size' => $request->query('meterSize', ''),
                'meter_num'  => (int) $request->query('meterNum', 0),
            ]);

        return response('1', 200, ['Content-Type' => 'text/plain']);
    }

    public function updateFirstName(Request $request): Response
    {
        ActiveCustomer::where('meter_serial', $request->query('meterSerial', ''))
            ->update(['first_name' => $request->query('firstName', '')]);

        return response('1', 200, ['Content-Type' => 'text/plain']);
    }

    public function updateMiddleName(Request $request): Response
    {
        ActiveCustomer::where('meter_serial', $request->query('meterSerial', ''))
            ->update(['middle_name' => $request->query('middleName', '')]);

        return response('1', 200, ['Content-Type' => 'text/plain']);
    }

    public function updateLastName(Request $request): Response
    {
        ActiveCustomer::where('meter_serial', $request->query('meterSerial', ''))
            ->update(['last_name' => $request->query('lastName', '')]);

        return response('1', 200, ['Content-Type' => 'text/plain']);
    }

    public function updateKebele(Request $request): Response
    {
        ActiveCustomer::where('meter_serial', $request->query('meterSerial', ''))
            ->update(['kebele' => $request->query('kebele', '')]);

        return response('1', 200, ['Content-Type' => 'text/plain']);
    }

    public function updateReaderBlock(Request $request): Response
    {
        ActiveCustomer::where('meter_serial', $request->query('meterSerial', ''))
            ->update(['reader_block' => $request->query('readerBlock', '')]);

        return response('1', 200, ['Content-Type' => 'text/plain']);
    }

    public function updateBillNum(Request $request): Response
    {
        ActiveCustomer::where('meter_serial', $request->query('meterSerial', ''))
            ->update(['bill_num' => $request->query('billNum', '')]);

        return response('1', 200, ['Content-Type' => 'text/plain']);
    }
}
