<?php

namespace App\Http\Controllers;

use App\Models\ReadingCorrection;
use Illuminate\Http\Request;

class ReadingCorrectionController extends Controller
{
    public function index(Request $request)
    {
        $view = $request->get('view', 'all');
        $complaints = collect();

        if ($view === 'daily') {
            $date = $request->get('date', date('Y-m-d'));
            $complaints = ReadingCorrection::whereDate('complain_date_time', $date)
                ->orWhere('complain_date_time', 'like', "$date%")
                ->orderByDesc('id')
                ->get();
        } elseif ($view === 'monthly') {
            $y = $request->get('year', get_setting('current_bill_year', date('Y')));
            $m = $request->get('month', faan_oromo_months()[0]);
            $complaints = ReadingCorrection::where('reading_year', $y)
                ->where('reading_month', $m)
                ->orderByDesc('id')
                ->get();
        } elseif ($view === 'annual') {
            $y = $request->get('year', get_setting('current_bill_year', date('Y')));
            $complaints = ReadingCorrection::where('reading_year', $y)
                ->orderByDesc('id')
                ->get();
        } elseif ($view === 'personal') {
            $code = $request->get('customerCode', '');
            if ($code) {
                $complaints = ReadingCorrection::where('customer_code', $code)
                    ->orderByDesc('id')
                    ->get();
            }
        } else {
            $complaints = ReadingCorrection::orderByDesc('id')->limit(100)->get();
        }

        $pendingCount  = $complaints->where('correction_status', 'Pending')->count();
        $approvedCount = $complaints->where('correction_status', 'Approved')->count();
        $rejectedCount = $complaints->where('correction_status', 'Rejected')->count();

        return view('reading-correction.index', [
            'view'           => $view,
            'complaints'     => $complaints,
            'months'         => faan_oromo_months(),
            'pendingCount'   => $pendingCount,
            'approvedCount'  => $approvedCount,
            'rejectedCount'  => $rejectedCount,
            'pageTitle'      => 'Reading Correction',
            'pageAction'     => [
                'label'    => t('New Complain'),
                'href'     => '#',
                'icon'     => 'plus',
                'onclick'  => "document.getElementById('complaintForm').scrollIntoView({behavior:'smooth', block:'center'}); document.getElementById('customerCode').focus(); return false;",
            ],
        ]);
    }
}
