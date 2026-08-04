<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReadingCorrection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReadingCorrectionController extends Controller
{
    public function getReadingCorrection(): Response
    {
        // /api/reading_correction/get-reading-correction/last-id
        $n = ReadingCorrection::count();

        return response((string) ($n + 1), 200, ['Content-Type' => 'text/plain']);
    }

    public function addReadingCorrection(Request $request): JsonResponse
    {
        $b = $request->json()->all();
        $rc = ReadingCorrection::create([
            'customer_code'      => $b['customerCode']      ?? '',
            'reading_year'       => $b['readingYear']       ?? null,
            'reading_month'      => $b['readingMonth']      ?? null,
            'sending_department' => $b['sendingDepartment'] ?? null,
            'complain_date_time' => $b['complainDateTime']  ?? now()->format('Y-m-d H:i:s'),
            'correction_status' => $b['correctionStatus']  ?? 'Pending',
            'new_reading'        => $b['newReading']        ?? 'NotInserted',
            'approved_name'      => $b['approvedName']      ?? 'Pending',
            'sync_status'        => $b['syncStatus']        ?? 'New',
        ]);

        return response()->json(['status' => 'created', 'id' => $rc->id], 201);
    }

    public function getDailyReadingComplain(Request $request): JsonResponse
    {
        $dt = $request->query('complainDateTime', '');
        $rows = ReadingCorrection::whereDate('complain_date_time', $dt)
            ->orWhere('complain_date_time', 'like', "$dt%")
            ->orderByDesc('id')
            ->get();

        return response()->json($rows);
    }

    public function getMonthlyReadingComplain(Request $request): JsonResponse
    {
        $y = $request->query('readingYear', '');
        $m = $request->query('readingMonth', '');
        $rows = ReadingCorrection::where('reading_year', $y)
            ->where('reading_month', $m)
            ->orderByDesc('id')
            ->get();

        return response()->json($rows);
    }

    public function getAnnualReadingComplain(Request $request): JsonResponse
    {
        $y = $request->query('readingYear', '');
        $rows = ReadingCorrection::where('reading_year', $y)
            ->orderByDesc('id')
            ->get();

        return response()->json($rows);
    }

    public function getCustomerReadingComplain(Request $request): JsonResponse
    {
        $code = $request->query('customerCode', '');
        $rows = ReadingCorrection::where('customer_code', $code)
            ->orderByDesc('id')
            ->get();

        return response()->json($rows);
    }

    public function updateNewReading(Request $request): Response
    {
        ReadingCorrection::where('customer_code', $request->query('customerCode', ''))
            ->where('complain_date_time', $request->query('complainDateTime', ''))
            ->update(['new_reading' => $request->query('newReading', '')]);

        return response('1', 200, ['Content-Type' => 'text/plain']);
    }

    public function updateApprovedName(Request $request): Response
    {
        ReadingCorrection::where('customer_code', $request->query('customerCode', ''))
            ->where('complain_date_time', $request->query('complainDateTime', ''))
            ->update(['approved_name' => $request->query('approvedName', '')]);

        return response('1', 200, ['Content-Type' => 'text/plain']);
    }

    public function updateCustomerComplain(Request $request): Response
    {
        ReadingCorrection::where('customer_code', $request->query('customerCode', ''))
            ->where('complain_date_time', $request->query('complainDateTime', ''))
            ->update(['correction_status' => $request->query('correctionStatus', 'Pending')]);

        return response('1', 200, ['Content-Type' => 'text/plain']);
    }
}
