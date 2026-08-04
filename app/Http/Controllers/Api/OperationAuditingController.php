<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OperationAuditing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OperationAuditingController extends Controller
{
    public function lastId(): Response
    {
        $n = OperationAuditing::count();

        return response((string) ($n + 1), 200, ['Content-Type' => 'text/plain']);
    }

    public function addOperation(Request $request): JsonResponse
    {
        $b = $request->json()->all();
        OperationAuditing::create([
            'log_date'   => $b['logDate']   ?? now()->format('Y-m-d H:i:s'),
            'log_reason' => $b['logReason'] ?? '',
            'done_by'    => $b['doneBy']    ?? '',
        ]);

        return response()->json(['status' => 'created'], 201);
    }
}
