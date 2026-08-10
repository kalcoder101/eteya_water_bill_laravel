<?php

namespace App\Livewire;

use App\Models\ActiveCustomer;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerSearch extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $status = 'all';

    #[Url(except: 'all')]
    public string $kebele = 'all';

    #[Url(except: 'all')]
    public string $customerType = 'all';

    #[Url(except: 'all')]
    public string $readerBlock = 'all';

    #[Url(except: 'meter_serial')]
    public string $sortBy = 'meter_serial';

    #[Url(except: 'asc')]
    public string $sortDir = 'asc';

    /**
     * Whitelist of sortable columns to prevent SQL injection / invalid column errors.
     */
    protected array $allowedSortColumns = [
        'meter_serial',
        'first_name',
        'middle_name',
        'last_name',
        'phone_number',
        'bill_num',
        'kebele',
        'customer_type',
        'reader_block',
        'customer_status',
        'created_at',
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
        if (!in_array($field, $this->allowedSortColumns, true)) {
            return;
        }

        if ($this->sortBy === $field) {
            $this->sortDir = strtolower($this->sortDir) === 'asc' ? 'desc' : 'asc';
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

        $sortColumn = in_array($this->sortBy, $this->allowedSortColumns, true) ? $this->sortBy : 'meter_serial';
        $sortDirection = in_array(strtolower($this->sortDir), ['asc', 'desc'], true) ? strtolower($this->sortDir) : 'asc';

        $customers = $query->orderBy($sortColumn, $sortDirection)->paginate(15);

        $kebeles = Cache::remember('customer_search_kebeles', 300, function () {
            return ActiveCustomer::distinct()->whereNotNull('kebele')->where('kebele', '!=', '')->pluck('kebele')->sort()->values();
        });

        $types = Cache::remember('customer_search_types', 300, function () {
            return ActiveCustomer::distinct()->whereNotNull('customer_type')->where('customer_type', '!=', '')->pluck('customer_type')->sort()->values();
        });

        $blocks = Cache::remember('customer_search_blocks', 300, function () {
            return ActiveCustomer::distinct()->whereNotNull('reader_block')->where('reader_block', '!=', '')->pluck('reader_block')->sort()->values();
        });

        $counts = Cache::remember('customer_search_counts', 60, function () {
            return [
                'total'   => ActiveCustomer::count(),
                'active'  => ActiveCustomer::where('customer_status', 'Active')->count(),
                'dc'      => ActiveCustomer::where('customer_status', 'DC')->count(),
                'updated' => ActiveCustomer::where('customer_status', 'Updated')->count(),
                'deleted' => ActiveCustomer::where('customer_status', 'Deleted')->count(),
            ];
        });

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
