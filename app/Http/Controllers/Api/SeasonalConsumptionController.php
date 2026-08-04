<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SeasonalConsumption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SeasonalConsumptionController extends Controller
{
    public function countReadingId(Request $request): Response
    {
        $ms = $request->query('meterSerial', '');
        $y  = $request->query('readingYear', '');
        $n = SeasonalConsumption::where('meter_serial', $ms)
            ->where('reading_year', $y)
            ->count();

        return response((string) $n, 200, ['Content-Type' => 'text/plain']);
    }

    public function readingMonthList(Request $request): JsonResponse
    {
        $ms = $request->query('meterSerial', '');
        $y  = $request->query('readingYear', '');
        $months = SeasonalConsumption::where('meter_serial', $ms)
            ->where('reading_year', $y)
            ->orderBy('meter_reading_id')
            ->distinct()
            ->pluck('reading_month')
            ->toArray();

        return response()->json($months);
    }
}
