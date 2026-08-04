<?php

namespace App\Http\Controllers;

use App\Models\ActiveCustomer;
use Illuminate\Http\Request;

class CustomerServiceController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $search = trim($request->get('search', ''));
        $status = $request->get('status', 'Active');

        $query = ActiveCustomer::query();

        if ($filter === 'status' && $status) {
            $query->where('customer_status', $status);
        } elseif ($filter === 'search' && $search !== '') {
            $like = "%{$search}%";
            $query->where(function ($q) use ($like) {
                $q->where('meter_serial', 'like', $like)
                  ->orWhere('first_name', 'like', $like)
                  ->orWhere('middle_name', 'like', $like)
                  ->orWhere('last_name', 'like', $like)
                  ->orWhere('phone_number', 'like', $like);
            });
        } else {
            $query->whereIn('customer_status', ['Active', 'DC']);
        }

        $customers  = $query->orderBy('meter_serial')->limit(500)->get();
        $totalCount = ActiveCustomer::whereIn('customer_status', ['Active', 'DC'])->count();
        $activeCount = ActiveCustomer::where('customer_status', 'Active')->count();
        $dcCount     = ActiveCustomer::where('customer_status', 'DC')->count();

        $branches = ['Eteya', 'Hurutaa', 'Heexosaa', 'Dheeraa'];

        return view('customer-service.index', [
            'customers'   => $customers,
            'filter'      => $filter,
            'search'      => $search,
            'status'      => $status,
            'totalCount'  => $totalCount,
            'activeCount' => $activeCount,
            'dcCount'     => $dcCount,
            'meterSizes'  => meter_sizes(),
            'customerTypes' => customer_types(),
            'customerStatuses' => customer_statuses(),
            'paymentWays' => payment_ways(),
            'branches'    => $branches,
            'pageTitle'   => 'Customer Service',
            'pageAction'  => [
                'label'   => t('Register New Customer'),
                'href'    => '#',
                'icon'    => 'plus',
                'onclick' => "openRegisterModal(); return false;",
            ],
        ]);
    }
}
