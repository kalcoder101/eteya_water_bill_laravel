<?php

namespace App\Http\Controllers;

use App\Models\ActiveCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerStatisticsController extends Controller
{
    public function index(Request $request)
    {
        $reportType = $request->get('report', 'typeStatus');

        if ($reportType === 'type') {
            $sql = "SELECT kebele,
                      SUM(CASE WHEN customer_type='Dhunfaa' THEN 1 ELSE 0 END) AS privateCount,
                      SUM(CASE WHEN customer_type='Waajjira Motummaa' THEN 1 ELSE 0 END) AS governmentCount,
                      SUM(CASE WHEN customer_type='Waajjira Miti-Motummaa' THEN 1 ELSE 0 END) AS nonGovernmentCount,
                      SUM(CASE WHEN customer_type IN ('Daldaltoota fi Industry','Boonoo') THEN 1 ELSE 0 END) AS commercialCount,
                      COUNT(*) AS total
                    FROM active_customers
                    WHERE customer_status IN ('Active','DC')
                    GROUP BY kebele ORDER BY kebele";
            $title = t('Customer Statistical Data').' — '.t('by Kebele and Customer Type');
        } elseif ($reportType === 'status') {
            $sql = "SELECT kebele,
                      SUM(CASE WHEN customer_status='Active'  THEN 1 ELSE 0 END) AS activeCount,
                      SUM(CASE WHEN customer_status='DC'      THEN 1 ELSE 0 END) AS dcCount,
                      SUM(CASE WHEN customer_status='Updated' THEN 1 ELSE 0 END) AS updatedCount,
                      SUM(CASE WHEN customer_status='Deleted' THEN 1 ELSE 0 END) AS deletedCount
                    FROM active_customers
                    GROUP BY kebele ORDER BY kebele";
            $title = t('Customer Statistical Data').' — '.t('by Kebele and Customer Status');
        } else {
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
            $title = t('Customer Statistical Data').' — '.t('Kebele × Type × Status');
        }

        $rows = DB::select($sql);

        $distRows = DB::select(
            "SELECT customer_type, customer_status, COUNT(*) AS cnt
             FROM active_customers
             WHERE customer_status IN ('Active','DC')
             GROUP BY customer_type, customer_status"
        );

        $byType   = [];
        $byStatus = ['Active' => 0, 'DC' => 0, 'Updated' => 0, 'Deleted' => 0];
        foreach ($distRows as $r) {
            $byType[$r->customer_type] = ($byType[$r->customer_type] ?? 0) + $r->cnt;
            $byStatus[$r->customer_status] = ($byStatus[$r->customer_status] ?? 0) + $r->cnt;
        }

        $byKebele = DB::select(
            "SELECT kebele, COUNT(*) AS cnt
             FROM active_customers
             WHERE customer_status IN ('Active','DC')
             GROUP BY kebele
             ORDER BY cnt DESC
             LIMIT 10"
        );

        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            $trend = DB::select(
                "SELECT strftime('%Y-%m', sold_date) AS ym, COUNT(*) AS cnt
                 FROM active_customers
                 WHERE sold_date IS NOT NULL AND sold_date != ''
                   AND sold_date >= date('now', '-12 months')
                 GROUP BY ym
                 ORDER BY ym"
            );
        } else {
            $trend = DB::select(
                "SELECT DATE_FORMAT(sold_date, '%Y-%m') AS ym, COUNT(*) AS cnt
                 FROM active_customers
                 WHERE sold_date IS NOT NULL AND sold_date != ''
                   AND sold_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                 GROUP BY ym
                 ORDER BY ym"
            );
        }

        $totalCustomers = ActiveCustomer::whereIn('customer_status', ['Active', 'DC'])->count();
        $totalKebeles   = ActiveCustomer::whereNotNull('kebele')->where('kebele', '!=', '')->distinct()->count('kebele');

        return view('customer-statistics.index', [
            'reportType'      => $reportType,
            'title'           => $title,
            'rows'            => $rows,
            'byType'          => $byType,
            'byStatus'        => $byStatus,
            'byKebele'        => $byKebele,
            'trend'           => $trend,
            'totalCustomers'  => $totalCustomers,
            'totalKebeles'    => $totalKebeles,
            'pageTitle'       => 'Detail Statistics',
            'pageAction'      => [
                'label'   => t('Print Report'),
                'href'    => '#',
                'icon'    => 'print',
                'onclick' => "window.print(); return false;",
            ],
        ]);
    }
}
