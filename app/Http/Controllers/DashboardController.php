<?php

namespace App\Http\Controllers;

use App\Models\ActiveCustomer;
use App\Models\BillFinance;
use App\Models\OperationAuditing;
use App\Models\User;
use App\Models\ReadingCorrection;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalCustomers    = ActiveCustomer::whereIn('customer_status', ['Active', 'DC'])->count();
        $activeCount       = ActiveCustomer::where('customer_status', 'Active')->count();
        $dcCount           = ActiveCustomer::where('customer_status', 'DC')->count();
        $unpaidBills       = BillFinance::where('payment_status', 'Unpaid')->count();
        $paidBills         = BillFinance::where('payment_status', 'Paid')->count();
        $pendingComplaints = ReadingCorrection::where('correction_status', 'Pending')->count();
        $totalUsers        = User::count();

        $recentCustomers = ActiveCustomer::whereIn('customer_status', ['Active', 'DC'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $recentAudit = OperationAuditing::orderByDesc('id')
            ->limit(8)
            ->get();

        $activePct = $totalCustomers > 0 ? round($activeCount / $totalCustomers * 100) : 0;

        return view('dashboard', [
            'totalCustomers'    => $totalCustomers,
            'activeCount'       => $activeCount,
            'dcCount'           => $dcCount,
            'unpaidBills'       => $unpaidBills,
            'paidBills'         => $paidBills,
            'pendingComplaints' => $pendingComplaints,
            'totalUsers'        => $totalUsers,
            'recentCustomers'   => $recentCustomers,
            'recentAudit'       => $recentAudit,
            'activePct'         => $activePct,
            'pageTitle'         => 'Dashboard',
            'pageAction'        => [
                'label' => t('Register New Customer'),
                'href'  => route('customer-service.index'),
                'icon'  => 'plus',
            ],
        ]);
    }
}
