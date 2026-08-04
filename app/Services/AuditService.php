<?php

namespace App\Services;

use App\Models\LoggingIntoAccount;
use App\Models\OperationAuditing;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public function logActivity(string $user, string $task): void
    {
        LoggingIntoAccount::create([
            'log_date' => now()->format('Y-m-d H:i:s'),
            'user'     => $user,
            'task'     => $task,
        ]);
    }

    public function logAudit(string $reason, ?string $doneBy = null): void
    {
        OperationAuditing::create([
            'log_date'   => now()->format('Y-m-d H:i:s'),
            'log_reason' => $reason,
            'done_by'    => $doneBy ?? (Auth::user() ? Auth::user()->fullName() : 'System'),
        ]);
    }

    public function getAdminFullName(): string
    {
        $admin = \App\Models\User::where('job_role', 'System Admin')->first();

        return $admin ? trim($admin->first_name.' '.($admin->last_name ?? '')) : 'System Admin';
    }
}
