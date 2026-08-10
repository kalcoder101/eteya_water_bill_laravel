<?php

use App\Livewire\Forms\CustomerRegistrationForm;
use App\Models\ActiveCustomer;
use App\Models\MeterLocation;
use App\Services\AuditService;
use Flux\Flux;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;
    use WithFileUploads;

    public CustomerRegistrationForm $regForm;

    // Search & Filter Properties
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

    // Edit modal selected customer
    public ?string $editMeterSerial = null;
    public ?ActiveCustomer $editingCustomer = null;

    // Single field update modal
    public string $updateFieldKey = '';
    public string $updateFieldLabel = '';
    public string $updateFieldValue = '';

    // Owner Transfer modal fields
    public string $transferFirstName = '';
    public string $transferMiddleName = '';
    public string $transferLastName = '';

    // New Meter Installation modal fields
    public string $newMeterSerial = '';
    public string $newMeterSize = '1/2"';
    public int $newMeterNum = 1;

    // Wizard Step
    public int $regStep = 1;

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

    public function mount(): void
    {
        $this->regForm->soldDate = date('Y-m-d');
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }
    public function updatingKebele(): void { $this->resetPage(); }
    public function updatingCustomerType(): void { $this->resetPage(); }
    public function updatingReaderBlock(): void { $this->resetPage(); }

    public function resetFilters(): void
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

    public function sortByField(string $field): void
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

    public function openRegisterModal(): void
    {
        $this->regForm->reset();
        $this->regForm->soldDate = date('Y-m-d');
        $this->regStep = 1;
        $this->modal('register-modal')->show();
    }

    public function setRegStep(int $step): void
    {
        $this->regStep = max(1, min(3, $step));
    }

    public function generateNextCode(): void
    {
        $k = $this->regForm->kebele ?: '00';
        $count = ActiveCustomer::where('kebele', $k)->count() + 1;
        $this->regForm->meterSerial = 'ETY-'.str_pad((string) $k, 2, '0', STR_PAD_LEFT).'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    public function registerCustomer(): void
    {
        $this->regForm->store();
        $this->regForm->reset();
        $this->modal('register-modal')->close();

        Flux::toast('New customer account registered successfully.', variant: 'success');
    }

    public function openEditModal(string $meterSerial): void
    {
        $this->editMeterSerial = $meterSerial;
        $this->editingCustomer = ActiveCustomer::where('meter_serial', $meterSerial)->first();
        if ($this->editingCustomer) {
            $this->modal('edit-modal')->show();
        } else {
            Flux::toast('Customer record not found.', variant: 'danger');
        }
    }

    public function updateCustomerStatus(string $newStatus): void
    {
        if (! $this->editMeterSerial) return;

        $c = ActiveCustomer::where('meter_serial', $this->editMeterSerial)->first();
        if ($c) {
            $c->update(['customer_status' => $newStatus]);
            app(AuditService::class)->logAudit(
                "Updated status for customer {$c->meter_serial} to {$newStatus}",
                auth()->user()?->fullName() ?? 'System'
            );

            Flux::toast("Customer status updated to {$newStatus}.", variant: 'success');
            $this->editingCustomer = $c->fresh();
        }
    }

    public function syncCustomer(): void
    {
        if (! $this->editMeterSerial) return;

        $c = ActiveCustomer::where('meter_serial', $this->editMeterSerial)->first();
        if ($c) {
            $c->update(['sync_status' => 'Synced']);
            app(AuditService::class)->logAudit(
                "Synced customer record {$c->meter_serial}",
                auth()->user()?->fullName() ?? 'System'
            );

            Flux::toast("Customer {$c->meter_serial} synced successfully.", variant: 'success');
            $this->editingCustomer = $c->fresh();
        }
    }

    public function openFieldUpdateModal(string $fieldKey, string $fieldLabel): void
    {
        if (! $this->editingCustomer) return;

        $this->updateFieldKey = $fieldKey;
        $this->updateFieldLabel = $fieldLabel;
        $this->updateFieldValue = (string) ($this->editingCustomer->{$fieldKey} ?? '');
        $this->modal('update-field-modal')->show();
    }

    public function saveFieldUpdate(): void
    {
        if (! $this->editMeterSerial || ! $this->updateFieldKey) return;

        $c = ActiveCustomer::where('meter_serial', $this->editMeterSerial)->first();
        if ($c) {
            $c->update([$this->updateFieldKey => $this->updateFieldValue]);
            app(AuditService::class)->logAudit(
                "Updated {$this->updateFieldKey} for customer {$c->meter_serial}",
                auth()->user()?->fullName() ?? 'System'
            );

            Flux::toast("Updated {$this->updateFieldLabel} successfully.", variant: 'success');
            $this->editingCustomer = $c->fresh();
        }

        $this->modal('update-field-modal')->close();
    }

    public function openOwnerTransferModal(): void
    {
        if (! $this->editingCustomer) return;

        $this->transferFirstName = $this->editingCustomer->first_name ?? '';
        $this->transferMiddleName = $this->editingCustomer->middle_name ?? '';
        $this->transferLastName = $this->editingCustomer->last_name ?? '';
        $this->modal('owner-transfer-modal')->show();
    }

    public function saveOwnerTransfer(): void
    {
        if (! $this->editMeterSerial) return;

        $c = ActiveCustomer::where('meter_serial', $this->editMeterSerial)->first();
        if ($c) {
            $c->update([
                'first_name'  => $this->transferFirstName,
                'middle_name' => $this->transferMiddleName ?: null,
                'last_name'   => $this->transferLastName ?: null,
            ]);
            app(AuditService::class)->logAudit(
                "Transferred meter owner for {$c->meter_serial} to {$this->transferFirstName} {$this->transferMiddleName}",
                auth()->user()?->fullName() ?? 'System'
            );

            Flux::toast('Meter owner transferred successfully.', variant: 'success');
            $this->editingCustomer = $c->fresh();
        }

        $this->modal('owner-transfer-modal')->close();
    }

    public function openInstallNewMeterModal(): void
    {
        if (! $this->editingCustomer) return;

        $this->newMeterSerial = $this->editingCustomer->bill_num ?? '';
        $this->newMeterSize = $this->editingCustomer->meter_size ?? '1/2"';
        $this->newMeterNum = (int) ($this->editingCustomer->meter_num ?? 1);
        $this->modal('install-meter-modal')->show();
    }

    public function saveNewMeterInstall(): void
    {
        if (! $this->editMeterSerial) return;

        $c = ActiveCustomer::where('meter_serial', $this->editMeterSerial)->first();
        if ($c) {
            $c->update([
                'bill_num'   => $this->newMeterSerial,
                'meter_size' => $this->newMeterSize,
                'meter_num'  => $this->newMeterNum,
            ]);
            app(AuditService::class)->logAudit(
                "Installed new meter for {$c->meter_serial}: SN {$this->newMeterSerial}, size {$this->newMeterSize}",
                auth()->user()?->fullName() ?? 'System'
            );

            Flux::toast('New meter installed successfully.', variant: 'success');
            $this->editingCustomer = $c->fresh();
        }

        $this->modal('install-meter-modal')->close();
    }

    public function render(): mixed
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

        return view('pages.⚡customer-service', [
            'customers'        => $customers,
            'kebeles'          => $kebeles,
            'types'            => $types,
            'blocks'           => $blocks,
            'counts'           => $counts,
            'hasActiveFilters' => $hasActiveFilters,
            'meterSizes'       => meter_sizes(),
            'customerTypes'    => customer_types(),
            'customerStatuses' => customer_statuses(),
            'paymentWays'      => payment_ways(),
            'branches'         => ['Eteya', 'Hurutaa', 'Heexosaa', 'Dheeraa'],
        ]);
    }
};
?>

<div>
    <!-- Page Header Banner -->
    <div class="gsap-hero flex flex-wrap items-end justify-between gap-4 mb-6">
        <div class="min-w-0">
            <h2 class="m-0 text-[22px] font-bold tracking-tight text-slate-900 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-700 inline-flex items-center justify-center shrink-0">{!! icon('users', 20) !!}</span>
                <span>{{ t('Customer Service Management') }}</span>
            </h2>
            <p class="mt-2 text-[13px] text-slate-500">{{ t('Register, search, update and manage all water meter customers across Kebeles') }}</p>
        </div>
        <div class="flex items-center gap-2.5">
            <flux:button variant="primary" icon="plus" wire:click="openRegisterModal">
                {{ t('Register New Customer') }}
            </flux:button>
        </div>
    </div>

    <!-- KPI Stat Cards Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-kpi :label="t('Total Registered')" :value="number_format($counts['total'])" :subvalue="t('Water Meter Accounts')" icon="users" color="emerald" />
        <x-kpi :label="t('Active Accounts')" :value="number_format($counts['active'])" :subvalue="($counts['total'] > 0 ? number_format(($counts['active']/$counts['total'])*100, 1) : 0).'% connected'" icon="check" color="emerald" :active="true" />
        <x-kpi label="Disconnected (DC)" :value="number_format($counts['dc'])" :subvalue="t('Cut off accounts')" icon="x" color="rose" />
        <x-kpi :label="t('Updated / Pending')" :value="number_format($counts['updated'])" :subvalue="t('Requires verification')" icon="arrow-path" color="amber" />
    </div>

    <!-- Livewire Reactive Table & Search Registry Section -->
    <flux:card class="p-0 overflow-hidden mb-6">
        <!-- Toolbar & Filter Header -->
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="relative flex-1 min-w-[260px] max-w-[420px]">
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ t('Search customer code, name, phone, serial...') }}" icon="magnifying-glass" />
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if ($hasActiveFilters)
                        <flux:button size="sm" variant="subtle" icon="x-mark" wire:click="resetFilters">
                            {{ t('Reset Filters') }}
                        </flux:button>
                    @endif
                    <flux:button size="sm" variant="subtle" icon="arrow-down-tray" href="{{ route('export.customers') }}">
                        {{ t('Export CSV') }}
                    </flux:button>
                </div>
            </div>

            <div class="flex flex-wrap gap-2.5 items-center">
                <!-- Status Filter Badges -->
                <div class="segmented bg-white border border-slate-200 p-1">
                    <button type="button" class="{{ $status==='all'?'active':'' }}" wire:click="$set('status', 'all')">All ({{ $counts['total'] }})</button>
                    <button type="button" class="{{ $status==='Active'?'active':'' }}" wire:click="$set('status', 'Active')">Active ({{ $counts['active'] }})</button>
                    <button type="button" class="{{ $status==='DC'?'active':'' }}" wire:click="$set('status', 'DC')">DC ({{ $counts['dc'] }})</button>
                    <button type="button" class="{{ $status==='Updated'?'active':'' }}" wire:click="$set('status', 'Updated')">Updated ({{ $counts['updated'] }})</button>
                </div>

                <!-- Kebele Dropdown -->
                <select wire:model.live="kebele" class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                    <option value="all">— Kebele (All) —</option>
                    @foreach ($kebeles as $k)
                        <option value="{{ $k }}">Kebele {{ $k }}</option>
                    @endforeach
                </select>

                <!-- Customer Type Dropdown -->
                <select wire:model.live="customerType" class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                    <option value="all">— Type (All) —</option>
                    @foreach ($types as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Registry Table -->
        <div class="scrollable-table border-0 rounded-none">
            <div class="scroll-progress"><div class="scroll-progress-bar"></div></div>
            <div class="table-scroll-view">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold">
                            <th class="text-left px-4 py-3 cursor-pointer hover:text-emerald-700" wire:click="sortByField('meter_serial')">
                                {{ t('Customer Code') }} @if($sortBy==='meter_serial') <span>{{ $sortDir==='asc'?'↑':'↓' }}</span> @endif
                            </th>
                            <th class="text-left px-4 py-3 cursor-pointer hover:text-emerald-700" wire:click="sortByField('first_name')">
                                {{ t('Full Name') }} @if($sortBy==='first_name') <span>{{ $sortDir==='asc'?'↑':'↓' }}</span> @endif
                            </th>
                            <th class="text-left px-4 py-3 cursor-pointer hover:text-emerald-700" wire:click="sortByField('kebele')">
                                {{ t('Kebele') }} @if($sortBy==='kebele') <span>{{ $sortDir==='asc'?'↑':'↓' }}</span> @endif
                            </th>
                            <th class="text-left px-4 py-3 cursor-pointer hover:text-emerald-700" wire:click="sortByField('customer_type')">
                                {{ t('Type') }} @if($sortBy==='customer_type') <span>{{ $sortDir==='asc'?'↑':'↓' }}</span> @endif
                            </th>
                            <th class="text-left px-4 py-3">{{ t('Phone Number') }}</th>
                            <th class="text-left px-4 py-3">{{ t('Status') }}</th>
                            <th class="text-right px-4 py-3">{{ t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($customers as $c)
                        <tr class="border-b border-slate-100 odd:bg-white even:bg-slate-50/40 hover:bg-emerald-50/60 transition-colors">
                            <td class="px-4 py-3"><span class="font-mono font-bold text-emerald-700">{{ $c->meter_serial }}</span></td>
                            <td class="px-4 py-3"><strong class="text-slate-900">{{ trim(($c->first_name ?? '').' '.($c->middle_name ?? '').' '.($c->last_name ?? '')) }}</strong></td>
                            <td class="px-4 py-3 text-slate-600">Kebele {{ $c->kebele ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $c->customer_type }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $c->phone_number ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if ($c->customer_status === 'Active')
                                    <flux:badge color="emerald" icon="check" size="sm">Active</flux:badge>
                                @elseif ($c->customer_status === 'DC')
                                    <flux:badge color="rose" icon="x-mark" size="sm">DC</flux:badge>
                                @else
                                    <flux:badge color="amber" size="sm">{{ $c->customer_status }}</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <flux:button size="sm" icon="pencil-square" wire:click="openEditModal('{{ $c->meter_serial }}')">
                                        {{ t('Edit') }}
                                    </flux:button>
                                    <flux:button size="sm" variant="subtle" icon="book-open" href="{{ route('customer-ledger.index').'?meterSerial='.urlencode($c->meter_serial) }}" title="{{ t('View Ledger') }}" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if ($customers->isEmpty())
                        <tr>
                            <td colspan="7" class="text-center py-10 px-6 text-slate-500 text-xs">{{ t('No matching customer accounts found.') }}</td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="p-3 border-t border-slate-100 bg-slate-50">
            {{ $customers->links() }}
        </div>
    </flux:card>

    <!-- Registration Multi-Step Wizard Modal -->
    <flux:modal name="register-modal" class="md:w-[720px]">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ t('Customer Registration Form') }}</flux:heading>
                <flux:subheading>{{ t('Register a new water meter customer in the system') }}</flux:subheading>
            </div>

            <!-- Step Tabs -->
            <div class="grid grid-cols-3 gap-2">
                <button type="button" wire:click="setRegStep(1)" class="px-3 py-2 rounded-lg text-xs font-bold border {{ $regStep===1?'bg-emerald-600 text-white border-emerald-600':'bg-slate-50 text-slate-600 border-slate-200' }}">1. Identity</button>
                <button type="button" wire:click="setRegStep(2)" class="px-3 py-2 rounded-lg text-xs font-bold border {{ $regStep===2?'bg-emerald-600 text-white border-emerald-600':'bg-slate-50 text-slate-600 border-slate-200' }}">2. Meter & Reading</button>
                <button type="button" wire:click="setRegStep(3)" class="px-3 py-2 rounded-lg text-xs font-bold border {{ $regStep===3?'bg-emerald-600 text-white border-emerald-600':'bg-slate-50 text-slate-600 border-slate-200' }}">3. Tariff & Branch</button>
            </div>

            <form wire:submit.prevent="registerCustomer" class="space-y-4">
                @if ($regStep === 1)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Customer Code *</label>
                            <div class="flex gap-2">
                                <flux:input wire:model="regForm.meterSerial" placeholder="ETY-0001" required class="flex-1" />
                                <flux:button type="button" variant="subtle" size="sm" wire:click="generateNextCode">Auto</flux:button>
                            </div>
                            @error('regForm.meterSerial') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Kebele *</label>
                            <flux:input wire:model="regForm.kebele" placeholder="01" required />
                            @error('regForm.kebele') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">First Name *</label>
                            <flux:input wire:model="regForm.firstName" placeholder="Abebe" required />
                            @error('regForm.firstName') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Middle Name</label>
                            <flux:input wire:model="regForm.middleName" placeholder="Kebede" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Last Name</label>
                            <flux:input wire:model="regForm.lastName" placeholder="Tadesse" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Phone Number</label>
                            <flux:input wire:model="regForm.phoneNumber" placeholder="+251911223344" />
                        </div>
                    </div>
                @elseif ($regStep === 2)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Meter Size</label>
                            <select wire:model="regForm.meterSize" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm">
                                @foreach ($meterSizes as $v) <option value="{{ $v }}">{{ $v }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Meter Number</label>
                            <flux:input type="number" wire:model="regForm.meterNum" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Bill Serial Number</label>
                            <flux:input wire:model="regForm.billNum" placeholder="SN-0001" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Start Reading (m³)</label>
                            <flux:input type="number" step="0.01" wire:model="regForm.startValue" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Sold Date</label>
                            <flux:input type="date" wire:model="regForm.soldDate" />
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Customer Type</label>
                            <select wire:model="regForm.customerType" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm">
                                @foreach ($customerTypes as $v) <option value="{{ $v }}">{{ $v }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Payment Way</label>
                            <select wire:model="regForm.paymentWay" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm">
                                @foreach ($paymentWays as $v) <option value="{{ $v }}">{{ $v }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Customer Branch</label>
                            <select wire:model="regForm.customerBranch" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm">
                                @foreach ($branches as $v) <option value="{{ $v }}">{{ $v }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Status</label>
                            <select wire:model="regForm.customerStatus" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm">
                                @foreach ($customerStatuses as $v) <option value="{{ $v }}">{{ $v }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Reader Block</label>
                            <flux:input wire:model="regForm.readerBlock" placeholder="Block-A" />
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-between pt-3">
                    @if ($regStep > 1)
                        <flux:button type="button" variant="subtle" wire:click="setRegStep({{ $regStep - 1 }})">Back</flux:button>
                    @else
                        <div></div>
                    @endif

                    @if ($regStep < 3)
                        <flux:button type="button" variant="primary" wire:click="setRegStep({{ $regStep + 1 }})">Next Step</flux:button>
                    @else
                        <flux:button type="submit" variant="primary" icon="check">Complete Registration</flux:button>
                    @endif
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Comprehensive Customer Operations & 22-Field Edit Modal -->
    <flux:modal name="edit-modal" class="md:w-[780px]">
        @if ($editingCustomer)
            <div class="space-y-5">
                <div>
                    <flux:heading size="lg">Customer Account Record — {{ $editingCustomer->meter_serial }}</flux:heading>
                    <flux:subheading>{{ trim(($editingCustomer->first_name ?? '').' '.($editingCustomer->middle_name ?? '').' '.($editingCustomer->last_name ?? '')) }} &bull; Kebele: {{ $editingCustomer->kebele }} &bull; Status: {{ $editingCustomer->customer_status }}</flux:subheading>
                </div>

                <!-- Customer Account Details Card -->
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div>
                        <span class="text-slate-500 block">Phone:</span>
                        <strong class="text-slate-900">{{ $editingCustomer->phone_number ?? '—' }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Customer Type:</span>
                        <strong class="text-slate-900">{{ $editingCustomer->customer_type }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Meter Size:</span>
                        <strong class="text-slate-900">{{ $editingCustomer->meter_size }} ({{ $editingCustomer->meter_num }})</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Bill Serial Num:</span>
                        <strong class="text-emerald-700 font-mono">{{ $editingCustomer->bill_num ?? '—' }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Start Reading:</span>
                        <strong class="text-slate-900 font-mono">{{ number_format($editingCustomer->start_value, 2) }} m³</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Payment Way:</span>
                        <strong class="text-slate-900">{{ $editingCustomer->payment_way }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Branch:</span>
                        <strong class="text-slate-900">{{ $editingCustomer->customer_branch }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Reader Block:</span>
                        <strong class="text-slate-900">{{ $editingCustomer->reader_block ?? '—' }}</strong>
                    </div>
                </div>

                <!-- Quick Status Actions -->
                <div>
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Status & Sync Controls</div>
                    <div class="flex flex-wrap gap-2">
                        <flux:button size="sm" variant="primary" icon="check" wire:click="updateCustomerStatus('Active')">Re-Activate</flux:button>
                        <flux:button size="sm" variant="danger" icon="x-mark" wire:click="updateCustomerStatus('DC')">Disconnect (DC)</flux:button>
                        <flux:button size="sm" variant="subtle" icon="arrow-path" wire:click="updateCustomerStatus('Updated')">Mark Updated</flux:button>
                        <flux:button size="sm" variant="subtle" icon="trash" wire:click="updateCustomerStatus('Deleted')" wire:confirm="Mark this customer as Deleted?">Mark Deleted</flux:button>
                        <flux:button size="sm" variant="subtle" icon="arrow-path" wire:click="syncCustomer">Sync Customer</flux:button>
                    </div>
                </div>

                <!-- 22 Field Updates Grid -->
                <div>
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Individual Field Updates</div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                        <flux:button size="sm" variant="subtle" wire:click="openFieldUpdateModal('first_name', 'First Name')">First Name</flux:button>
                        <flux:button size="sm" variant="subtle" wire:click="openFieldUpdateModal('middle_name', 'Middle Name')">Middle Name</flux:button>
                        <flux:button size="sm" variant="subtle" wire:click="openFieldUpdateModal('last_name', 'Last Name')">Last Name</flux:button>
                        <flux:button size="sm" variant="subtle" wire:click="openFieldUpdateModal('phone_number', 'Phone Number')">Phone Number</flux:button>

                        <flux:button size="sm" variant="subtle" wire:click="openFieldUpdateModal('kebele', 'Kebele')">Kebele</flux:button>
                        <flux:button size="sm" variant="subtle" wire:click="openFieldUpdateModal('customer_type', 'Customer Type')">Customer Type</flux:button>
                        <flux:button size="sm" variant="subtle" wire:click="openFieldUpdateModal('meter_size', 'Meter Size')">Meter Size</flux:button>
                        <flux:button size="sm" variant="subtle" wire:click="openFieldUpdateModal('meter_num', 'Meter Number')">Meter Number</flux:button>

                        <flux:button size="sm" variant="subtle" wire:click="openFieldUpdateModal('payment_way', 'Payment Way')">Payment Way</flux:button>
                        <flux:button size="sm" variant="subtle" wire:click="openFieldUpdateModal('customer_branch', 'Branch')">Branch</flux:button>
                        <flux:button size="sm" variant="subtle" wire:click="openFieldUpdateModal('bill_num', 'Bill Serial Num')">Bill Number</flux:button>
                        <flux:button size="sm" variant="subtle" wire:click="openFieldUpdateModal('reader_block', 'Reader Block')">Reader Block</flux:button>

                        <flux:button size="sm" variant="subtle" wire:click="openFieldUpdateModal('start_value', 'Start Reading')">Start Reading</flux:button>
                        <flux:button size="sm" variant="subtle" wire:click="openFieldUpdateModal('sold_date', 'Sold Date')">Sold Date</flux:button>
                    </div>
                </div>

                <!-- Special Workflow Operations -->
                <div>
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Special Operations</div>
                    <div class="flex flex-wrap gap-2">
                        <flux:button size="sm" variant="subtle" icon="user" wire:click="openOwnerTransferModal">Meter Owner Transfer</flux:button>
                        <flux:button size="sm" variant="subtle" icon="wrench" wire:click="openInstallNewMeterModal">Install New Meter</flux:button>
                    </div>
                </div>

                <div class="flex justify-end pt-3">
                    <flux:modal.close>
                        <flux:button variant="subtle">Close</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>

    <!-- Single Field Update Modal -->
    <flux:modal name="update-field-modal" class="md:w-96">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Update {{ $updateFieldLabel }}</flux:heading>
                <flux:subheading>Update {{ $updateFieldLabel }} for customer {{ $editMeterSerial }}</flux:subheading>
            </div>

            <div>
                @if (in_array($updateFieldKey, ['customer_type', 'payment_way', 'customer_branch', 'meter_size'], true))
                    <label class="block text-xs font-bold text-slate-500 mb-1">{{ $updateFieldLabel }}</label>
                    <select wire:model="updateFieldValue" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm">
                        @if ($updateFieldKey === 'customer_type')
                            @foreach ($customerTypes as $v) <option value="{{ $v }}">{{ $v }}</option> @endforeach
                        @elseif ($updateFieldKey === 'payment_way')
                            @foreach ($paymentWays as $v) <option value="{{ $v }}">{{ $v }}</option> @endforeach
                        @elseif ($updateFieldKey === 'customer_branch')
                            @foreach ($branches as $v) <option value="{{ $v }}">{{ $v }}</option> @endforeach
                        @elseif ($updateFieldKey === 'meter_size')
                            @foreach ($meterSizes as $v) <option value="{{ $v }}">{{ $v }}</option> @endforeach
                        @endif
                    </select>
                @elseif (in_array($updateFieldKey, ['start_value', 'meter_num'], true))
                    <flux:input type="number" step="0.01" wire:model="updateFieldValue" label="{{ $updateFieldLabel }}" required />
                @elseif ($updateFieldKey === 'sold_date')
                    <flux:input type="date" wire:model="updateFieldValue" label="{{ $updateFieldLabel }}" required />
                @else
                    <flux:input wire:model="updateFieldValue" label="{{ $updateFieldLabel }}" required />
                @endif
            </div>

            <div class="flex gap-2 justify-end">
                <flux:modal.close>
                    <flux:button variant="subtle">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="saveFieldUpdate" icon="check">
                    Save Update
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Meter Owner Transfer Modal -->
    <flux:modal name="owner-transfer-modal" class="md:w-[480px]">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Meter Owner Transfer</flux:heading>
                <flux:subheading>Transfer ownership of meter {{ $editMeterSerial }} to a new owner</flux:subheading>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">New First Name *</label>
                    <flux:input wire:model="transferFirstName" placeholder="First Name" required />
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">New Middle Name</label>
                    <flux:input wire:model="transferMiddleName" placeholder="Middle Name" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">New Last Name</label>
                    <flux:input wire:model="transferLastName" placeholder="Last Name" />
                </div>
            </div>

            <div class="flex gap-2 justify-end pt-2">
                <flux:modal.close>
                    <flux:button variant="subtle">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="saveOwnerTransfer" icon="check">
                    Transfer Ownership
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Install New Meter Modal -->
    <flux:modal name="install-meter-modal" class="md:w-[480px]">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Install New Meter</flux:heading>
                <flux:subheading>Replace meter hardware for customer account {{ $editMeterSerial }}</flux:subheading>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">New Meter Serial Number *</label>
                    <flux:input wire:model="newMeterSerial" placeholder="SN-0001" required />
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">New Meter Size</label>
                    <select wire:model="newMeterSize" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm">
                        @foreach ($meterSizes as $v) <option value="{{ $v }}">{{ $v }}</option> @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">Meter Number (Integer)</label>
                    <flux:input type="number" wire:model="newMeterNum" required />
                </div>
            </div>

            <div class="flex gap-2 justify-end pt-2">
                <flux:modal.close>
                    <flux:button variant="subtle">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="saveNewMeterInstall" icon="check">
                    Complete Installation
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>