<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoggingIntoAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LoggingAuditingController extends Controller
{
    public function lastId(): Response
    {
        $n = LoggingIntoAccount::count();

        return response((string) ($n + 1), 200, ['Content-Type' => 'text/plain']);
    }

    public function addLog(Request $request): JsonResponse
    {
        $b = $request->json()->all();
        LoggingIntoAccount::create([
            'log_date' => $b['logDate'] ?? now()->format('Y-m-d H:i:s'),
            'user'     => $b['user']     ?? '',
            'task'     => $b['task']     ?? '',
        ]);

        return response()->json(['status' => 'created'], 201);
    }
}
