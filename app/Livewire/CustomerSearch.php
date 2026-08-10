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
    public string $readerBlock = 'all';
    public string $sortBy = 'meter_serial';
    public string $sortDir = 'asc';

    protected $queryString = [
        'search'       => ['except' => ''],
        'status'       => ['except' => 'all'],
        'kebele'       => ['except' => 'all'],
        'customerType' => ['except' => 'all'],
        'readerBlock'  => ['except' => 'all'],
        'sortBy'       => ['except' => 'meter_serial'],
        'sortDir'      => ['except' => 'asc'],
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

    public function updatingReaderBlock()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->status = 'all';
        $this->kebele = 'all';
        $this->customerType = 'all';
        $this->readerBlock = 'all';
        $this->sortBy = 'meter_serial';
        $this->sortDir = 'asc';
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

        if ($this->readerBlock !== 'all') {
            $query->where('reader_block', $this->readerBlock);
        }

        $customers = $query->orderBy($this->sortBy, $this->sortDir)->paginate(15);

        $kebeles = ActiveCustomer::distinct()->whereNotNull('kebele')->where('kebele', '!=', '')->pluck('kebele')->sort();
        $types   = ActiveCustomer::distinct()->whereNotNull('customer_type')->where('customer_type', '!=', '')->pluck('customer_type')->sort();
        $blocks  = ActiveCustomer::distinct()->whereNotNull('reader_block')->where('reader_block', '!=', '')->pluck('reader_block')->sort();

        $counts = [
            'total'   => ActiveCustomer::count(),
            'active'  => ActiveCustomer::where('customer_status', 'Active')->count(),
            'dc'      => ActiveCustomer::where('customer_status', 'DC')->count(),
            'updated' => ActiveCustomer::where('customer_status', 'Updated')->count(),
            'deleted' => ActiveCustomer::where('customer_status', 'Deleted')->count(),
        ];

        $hasActiveFilters = !empty($this->search) || $this->status !== 'all' || $this->kebele !== 'all' || $this->customerType !== 'all' || $this->readerBlock !== 'all';

        return view('livewire.customer-search', [
            'customers'        => $customers,
            'kebeles'          => $kebeles,
            'types'            => $types,
            'blocks'           => $blocks,
            'counts'           => $counts,
            'hasActiveFilters' => $hasActiveFilters,
        ]);
    }
}
