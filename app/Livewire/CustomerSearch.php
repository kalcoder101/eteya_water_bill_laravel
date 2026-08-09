<?php

namespace App\Livewire;

use App\Models\ActiveCustomer;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerSearch extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'all';
    public string $kebele = 'all';
    public string $customerType = 'all';
    public string $sortBy = 'meter_serial';
    public string $sortDir = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'all'],
        'kebele' => ['except' => 'all'],
        'customerType' => ['except' => 'all'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingKebele()
    {
        $this->resetPage();
    }

    public function updatingCustomerType()
    {
        $this->resetPage();
    }

    public function sortByField(string $field)
    {
        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDir = 'asc';
        }
    }

    public function render()
    {
        $query = ActiveCustomer::query();

        if (!empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('meter_serial', 'like', "%{$s}%")
                  ->orWhere('first_name', 'like', "%{$s}%")
                  ->orWhere('middle_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%")
                  ->orWhere('phone_number', 'like', "%{$s}%")
                  ->orWhere('bill_num', 'like', "%{$s}%");
            });
        }

        if ($this->status !== 'all') {
            $query->where('customer_status', $this->status);
        }

        if ($this->kebele !== 'all') {
            $query->where('kebele', $this->kebele);
        }

        if ($this->customerType !== 'all') {
            $query->where('customer_type', $this->customerType);
        }

        $customers = $query->orderBy($this->sortBy, $this->sortDir)->paginate(15);

        $kebeles = ActiveCustomer::distinct()->whereNotNull('kebele')->pluck('kebele')->sort();
        $types = ActiveCustomer::distinct()->whereNotNull('customer_type')->pluck('customer_type')->sort();

        $counts = [
            'total'   => ActiveCustomer::count(),
            'active'  => ActiveCustomer::where('customer_status', 'Active')->count(),
            'dc'      => ActiveCustomer::where('customer_status', 'DC')->count(),
            'updated' => ActiveCustomer::where('customer_status', 'Updated')->count(),
            'deleted' => ActiveCustomer::where('customer_status', 'Deleted')->count(),
        ];

        return view('livewire.customer-search', [
            'customers' => $customers,
            'kebeles'   => $kebeles,
            'types'     => $types,
            'counts'    => $counts,
        ]);
    }
}
