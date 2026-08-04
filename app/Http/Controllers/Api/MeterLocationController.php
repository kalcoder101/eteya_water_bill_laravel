<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MeterLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeterLocationController extends Controller
{
    public function addMeterLocation(Request $request): JsonResponse
    {
        $b = $request->json()->all();
        MeterLocation::updateOrCreate(
            ['customer_code' => $b['customerCode'] ?? ''],
            [
                'latitude_val'  => $b['latitudeVal']  ?? null,
                'longitude_val' => $b['longitudeVal'] ?? null,
            ]
        );

        return response()->json(['status' => 'created'], 201);
    }
}
