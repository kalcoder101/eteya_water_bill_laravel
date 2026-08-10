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
    <flux:card class="p-0 overflow-visible relative z-[30] mb-6">
        <!-- Toolbar & Filter Header -->
        <div class="p-4 border-b border-slate-200/80 bg-slate-50/60 dark:bg-zinc-800/40 rounded-t-2xl space-y-4.5 overflow-visible">

            <!-- Primary Action Bar (Search + Dropdown Filters + Export Actions) -->
            <div class="flex flex-wrap items-center justify-between gap-3 overflow-visible">
                <!-- Search Input -->
                <div class="relative flex-1 min-w-[280px] max-w-[440px]">
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ t('Search customer code, name, phone, serial...') }}" icon="magnifying-glass" />
                </div>

                <!-- Dropdowns & Action Buttons Group -->
                <div class="flex flex-wrap items-center gap-3 overflow-visible">
                    <!-- Kebele Flux Select -->
                    <div class="w-44 min-w-[170px] overflow-visible">
                        <flux:select wire:model.live="kebele" size="sm" placeholder="{{ t('Kebele (All)') }}">
                            <flux:select.option value="all">— {{ t('Kebele (All)') }} —</flux:select.option>
                            @foreach ($kebeles as $k)
                                <flux:select.option value="{{ $k }}">Kebele {{ $k }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <!-- Customer Type Flux Select -->
                    <div class="w-48 min-w-[190px] overflow-visible">
                        <flux:select wire:model.live="customerType" size="sm" placeholder="{{ t('Type (All)') }}">
                            <flux:select.option value="all">— {{ t('Type (All)') }} —</flux:select.option>
                            @foreach ($types as $t)
                                <flux:select.option value="{{ $t }}">{{ $t }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2 shrink-0">
                        @if ($hasActiveFilters)
                            <flux:button size="sm" variant="subtle" icon="x-mark" wire:click="resetFilters">
                                {{ t('Reset') }}
                            </flux:button>
                        @endif
                        <flux:button size="sm" variant="subtle" icon="arrow-down-tray" href="{{ route('export.customers') }}">
                            {{ t('Export CSV') }}
                        </flux:button>
                    </div>
                </div>
            </div>

            <!-- Status Filter Pills -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200/60 pt-3">
                <div class="flex flex-wrap items-center gap-1.5 p-1 rounded-xl bg-zinc-100 dark:bg-zinc-800/80 border border-zinc-200/80 dark:border-zinc-700">
                    <flux:button
                        size="sm"
                        variant="{{ $status === 'all' ? 'primary' : 'subtle' }}"
                        wire:click="$set('status', 'all')"
                        class="font-bold text-xs"
                    >
                        {{ t('All') }} <flux:badge size="sm" color="{{ $status === 'all' ? 'zinc' : 'emerald' }}" class="ml-1 font-extrabold">{{ $counts['total'] }}</flux:badge>
                    </flux:button>

                    <flux:button
                        size="sm"
                        variant="{{ $status === 'Active' ? 'primary' : 'subtle' }}"
                        wire:click="$set('status', 'Active')"
                        class="font-bold text-xs"
                    >
                        {{ t('Active') }} <flux:badge size="sm" color="{{ $status === 'Active' ? 'zinc' : 'emerald' }}" class="ml-1 font-extrabold">{{ $counts['active'] }}</flux:badge>
                    </flux:button>

                    <flux:button
                        size="sm"
                        variant="{{ $status === 'DC' ? 'primary' : 'subtle' }}"
                        wire:click="$set('status', 'DC')"
                        class="font-bold text-xs"
                    >
                        {{ t('DC') }} <flux:badge size="sm" color="{{ $status === 'DC' ? 'zinc' : 'rose' }}" class="ml-1 font-extrabold">{{ $counts['dc'] }}</flux:badge>
                    </flux:button>

                    <flux:button
                        size="sm"
                        variant="{{ $status === 'Updated' ? 'primary' : 'subtle' }}"
                        wire:click="$set('status', 'Updated')"
                        class="font-bold text-xs"
                    >
                        {{ t('Updated') }} <flux:badge size="sm" color="{{ $status === 'Updated' ? 'zinc' : 'amber' }}" class="ml-1 font-extrabold">{{ $counts['updated'] }}</flux:badge>
                    </flux:button>
                </div>

                <div class="text-xs text-slate-500 font-medium">
                    Showing <strong class="text-slate-900 font-bold">{{ $customers->total() }}</strong> customer accounts
                </div>
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
                            <td class="px-4 py-3 text-slate-600">
                                @php
                                    $typeColor = match(strtolower($c->customer_type ?? '')) {
                                        'commercial' => 'indigo',
                                        'residential', 'domestic' => 'sky',
                                        'institutional', 'government' => 'purple',
                                        'public tap', 'public' => 'amber',
                                        default => 'zinc',
                                    };
                                @endphp
                                <flux:badge color="{{ $typeColor }}" size="sm">{{ $c->customer_type ?? 'Standard' }}</flux:badge>
                            </td>
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
    <flux:modal name="register-modal" class="md:w-[760px]">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ t('Customer Registration Form') }}</flux:heading>
                <flux:subheading>{{ t('Register a new water meter customer in the system') }}</flux:subheading>
            </div>

            <!-- Step Progress Indicator -->
            <div class="grid grid-cols-3 gap-2">
                <button type="button" wire:click="setRegStep(1)" class="p-2.5 rounded-xl border flex items-center justify-between transition-all cursor-pointer {{ $regStep===1 ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                    <span class="text-xs font-bold">1. {{ t('Customer Identity') }}</span>
                    @if ($regStep > 1)
                        <flux:badge color="emerald" size="sm" icon="check" />
                    @endif
                </button>

                <button type="button" wire:click="setRegStep(2)" class="p-2.5 rounded-xl border flex items-center justify-between transition-all cursor-pointer {{ $regStep===2 ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                    <span class="text-xs font-bold">2. {{ t('Meter & Reading') }}</span>
                    @if ($regStep > 2)
                        <flux:badge color="emerald" size="sm" icon="check" />
                    @endif
                </button>

                <button type="button" wire:click="setRegStep(3)" class="p-2.5 rounded-xl border flex items-center justify-between transition-all cursor-pointer {{ $regStep===3 ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                    <span class="text-xs font-bold">3. {{ t('Tariff & Branch') }}</span>
                </button>
            </div>

            <form wire:submit.prevent="registerCustomer" class="space-y-4">
                <flux:card class="p-5 bg-slate-50/50 border border-slate-200">
                    @if ($regStep === 1)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <flux:field>
                                    <flux:label class="text-xs font-bold text-slate-700 mb-1">{{ t('Customer Code') }} *</flux:label>
                                    <div class="flex gap-2">
                                        <flux:input wire:model="regForm.meterSerial" placeholder="ETY-0001" required class="flex-1" />
                                        <flux:button type="button" variant="subtle" size="sm" icon="sparkles" wire:click="generateNextCode">Auto</flux:button>
                                    </div>
                                    @error('regForm.meterSerial') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                                </flux:field>
                            </div>

                            <div>
                                <flux:field>
                                    <flux:label class="text-xs font-bold text-slate-700 mb-1">{{ t('Kebele / Zone') }} *</flux:label>
                                    <flux:input wire:model="regForm.kebele" placeholder="01" required />
                                    @error('regForm.kebele') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                                </flux:field>
                            </div>

                            <div>
                                <flux:field>
                                    <flux:label class="text-xs font-bold text-slate-700 mb-1">{{ t('First Name') }} *</flux:label>
                                    <flux:input wire:model="regForm.firstName" placeholder="Abebe" required />
                                    @error('regForm.firstName') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                                </flux:field>
                            </div>

                            <div>
                                <flux:field>
                                    <flux:label class="text-xs font-bold text-slate-700 mb-1">{{ t('Middle Name') }}</flux:label>
                                    <flux:input wire:model="regForm.middleName" placeholder="Kebede" />
                                </flux:field>
                            </div>

                            <div>
                                <flux:field>
                                    <flux:label class="text-xs font-bold text-slate-700 mb-1">{{ t('Last Name') }}</flux:label>
                                    <flux:input wire:model="regForm.lastName" placeholder="Tadesse" />
                                </flux:field>
                            </div>

                            <div>
                                <flux:field>
                                    <flux:label class="text-xs font-bold text-slate-700 mb-1">{{ t('Phone Number') }}</flux:label>
                                    <flux:input wire:model="regForm.phoneNumber" placeholder="+251911223344" />
                                </flux:field>
                            </div>
                        </div>
                    @elseif ($regStep === 2)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <flux:field>
                                    <flux:label class="text-xs font-bold text-slate-700 mb-1">{{ t('Meter Size') }}</flux:label>
                                    <flux:select wire:model="regForm.meterSize">
                                        @foreach ($meterSizes as $v)
                                            <flux:select.option value="{{ $v }}">{{ $v }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </flux:field>
                            </div>

                            <div>
                                <flux:field>
                                    <flux:label class="text-xs font-bold text-slate-700 mb-1">{{ t('Meter Number') }}</flux:label>
                                    <flux:input type="number" wire:model="regForm.meterNum" placeholder="10001" />
                                </flux:field>
                            </div>

                            <div>
                                <flux:field>
                                    <flux:label class="text-xs font-bold text-slate-700 mb-1">{{ t('Bill Serial Number') }}</flux:label>
                                    <flux:input wire:model="regForm.billNum" placeholder="SN-0001" />
                                </flux:field>
                            </div>

                            <div>
                                <flux:field>
                                    <flux:label class="text-xs font-bold text-slate-700 mb-1">{{ t('Start Reading (m³)') }}</flux:label>
                                    <flux:input type="number" step="0.01" wire:model="regForm.startValue" placeholder="0.00" />
                                </flux:field>
                            </div>

                            <div class="md:col-span-2">
                                <flux:field>
                                    <flux:label class="text-xs font-bold text-slate-700 mb-1">{{ t('Sold Date') }}</flux:label>
                                    <flux:input type="date" wire:model="regForm.soldDate" />
                                </flux:field>
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <flux:field>
                                    <flux:label class="text-xs font-bold text-slate-700 mb-1">{{ t('Customer Type') }}</flux:label>
                                    <flux:select wire:model="regForm.customerType">
                                        @foreach ($customerTypes as $v)
                                            <flux:select.option value="{{ $v }}">{{ $v }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </flux:field>
                            </div>

                            <div>
                                <flux:field>
                                    <flux:label class="text-xs font-bold text-slate-700 mb-1">{{ t('Payment Way') }}</flux:label>
                                    <flux:select wire:model="regForm.paymentWay">
                                        @foreach ($paymentWays as $v)
                                            <flux:select.option value="{{ $v }}">{{ $v }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </flux:field>
                            </div>

                            <div>
                                <flux:field>
                                    <flux:label class="text-xs font-bold text-slate-700 mb-1">{{ t('Customer Branch') }}</flux:label>
                                    <flux:select wire:model="regForm.customerBranch">
                                        @foreach ($branches as $v)
                                            <flux:select.option value="{{ $v }}">{{ $v }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </flux:field>
                            </div>

                            <div>
                                <flux:field>
                                    <flux:label class="text-xs font-bold text-slate-700 mb-1">{{ t('Customer Status') }}</flux:label>
                                    <flux:select wire:model="regForm.customerStatus">
                                        @foreach ($customerStatuses as $v)
                                            <flux:select.option value="{{ $v }}">{{ $v }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </flux:field>
                            </div>

                            <div class="md:col-span-2">
                                <flux:field>
                                    <flux:label class="text-xs font-bold text-slate-700 mb-1">{{ t('Reader Block') }}</flux:label>
                                    <flux:input wire:model="regForm.readerBlock" placeholder="Block-A" />
                                </flux:field>
                            </div>
                        </div>
                    @endif
                </flux:card>

                <div class="flex items-center justify-between pt-2">
                    @if ($regStep > 1)
                        <flux:button type="button" variant="subtle" icon="arrow-left" wire:click="setRegStep({{ $regStep - 1 }})">{{ t('Back') }}</flux:button>
                    @else
                        <div></div>
                    @endif

                    @if ($regStep < 3)
                        <flux:button type="button" variant="primary" icon="arrow-right" wire:click="setRegStep({{ $regStep + 1 }})">{{ t('Next Step') }}</flux:button>
                    @else
                        <flux:button type="submit" variant="primary" icon="check">{{ t('Complete Registration') }}</flux:button>
                    @endif
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Comprehensive Customer Operations & 22-Field Edit Modal -->
    <flux:modal name="edit-modal" class="!max-w-5xl !w-full">
        @if ($editingCustomer)
            <div x-data="{ activeTab: 'overview' }" class="space-y-4">
                <!-- Modal Header -->
                <div>
                    <flux:heading size="lg" class="flex items-center gap-3">
                        <span>{{ t('Customer Account Record') }}</span>
                        <span class="font-mono text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-md border border-emerald-300 text-sm font-bold">{{ $editingCustomer->meter_serial }}</span>
                    </flux:heading>
                    <flux:subheading class="mt-1">
                        {{ trim(($editingCustomer->first_name ?? '').' '.($editingCustomer->middle_name ?? '').' '.($editingCustomer->last_name ?? '')) }}
                        &bull; Kebele {{ $editingCustomer->kebele }}
                        &bull;
                        @if ($editingCustomer->customer_status === 'Active')
                            <flux:badge color="emerald" icon="check" size="sm">{{ t('Active') }}</flux:badge>
                        @elseif ($editingCustomer->customer_status === 'DC')
                            <flux:badge color="rose" icon="x-mark" size="sm">{{ t('Disconnected') }}</flux:badge>
                        @else
                            <flux:badge color="amber" size="sm">{{ $editingCustomer->customer_status }}</flux:badge>
                        @endif
                        @php
                            $typeColor = match(strtolower($editingCustomer->customer_type ?? '')) {
                                'commercial' => 'indigo',
                                'residential', 'domestic' => 'sky',
                                'institutional', 'government' => 'purple',
                                'public tap', 'public' => 'amber',
                                default => 'zinc',
                            };
                        @endphp
                        <flux:badge color="{{ $typeColor }}" size="sm">{{ $editingCustomer->customer_type }}</flux:badge>
                    </flux:subheading>
                </div>

                <!-- Step / Tab Navigation — same style as register-modal step buttons -->
                <div class="grid grid-cols-4 gap-2">
                    <button type="button" x-on:click="activeTab = 'overview'"
                        class="p-2.5 rounded-xl border flex items-center justify-between transition-all cursor-pointer"
                        :class="activeTab === 'overview' ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'">
                        <span class="text-xs font-bold">1. {{ t('Account Overview') }}</span>
                        <span x-show="activeTab !== 'overview'" class="opacity-40 text-[10px]">{!! icon('user', 12) !!}</span>
                        <flux:badge x-show="activeTab === 'overview'" color="lime" size="sm" icon="check" />
                    </button>

                    <button type="button" x-on:click="activeTab = 'status'"
                        class="p-2.5 rounded-xl border flex items-center justify-between transition-all cursor-pointer"
                        :class="activeTab === 'status' ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'">
                        <span class="text-xs font-bold">2. {{ t('Status & Sync') }}</span>
                        <span x-show="activeTab !== 'status'" class="opacity-40">{!! icon('shield', 12) !!}</span>
                        <flux:badge x-show="activeTab === 'status'" color="lime" size="sm" icon="check" />
                    </button>

                    <button type="button" x-on:click="activeTab = 'fields'"
                        class="p-2.5 rounded-xl border flex items-center justify-between transition-all cursor-pointer"
                        :class="activeTab === 'fields' ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'">
                        <span class="text-xs font-bold">3. {{ t('Field Edits') }}</span>
                        <span x-show="activeTab !== 'fields'" class="opacity-40">{!! icon('pencil-square', 12) !!}</span>
                        <flux:badge x-show="activeTab === 'fields'" color="lime" size="sm" icon="check" />
                    </button>

                    <button type="button" x-on:click="activeTab = 'workflows'"
                        class="p-2.5 rounded-xl border flex items-center justify-between transition-all cursor-pointer"
                        :class="activeTab === 'workflows' ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'">
                        <span class="text-xs font-bold">4. {{ t('Workflows') }}</span>
                        <span x-show="activeTab !== 'workflows'" class="opacity-40">{!! icon('wrench', 12) !!}</span>
                        <flux:badge x-show="activeTab === 'workflows'" color="lime" size="sm" icon="check" />
                    </button>
                </div>

                <!-- Tab 1: Account Overview — two stacked wide cards like register-modal -->
                <div x-show="activeTab === 'overview'" x-transition class="space-y-4">
                    <flux:card class="p-5 bg-slate-50/50 border border-slate-200">
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2 mb-3 pb-2 border-b border-slate-200">
                            {!! icon('user', 13) !!}
                            <span>{{ t('Customer Identity & Contact') }}</span>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ t('First Name') }}</span>
                                <strong class="text-slate-900 text-sm block">{{ $editingCustomer->first_name ?? '—' }}</strong>
                            </div>
                            <div>
                                <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ t('Middle Name') }}</span>
                                <strong class="text-slate-900 text-sm block">{{ $editingCustomer->middle_name ?? '—' }}</strong>
                            </div>
                            <div>
                                <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ t('Last Name') }}</span>
                                <strong class="text-slate-900 text-sm block">{{ $editingCustomer->last_name ?? '—' }}</strong>
                            </div>
                            <div>
                                <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ t('Phone Number') }}</span>
                                <strong class="text-slate-900 text-sm block font-mono">{{ $editingCustomer->phone_number ?? '—' }}</strong>
                            </div>
                            <div>
                                <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ t('Kebele / Zone') }}</span>
                                <strong class="text-slate-900 text-sm block">Kebele {{ $editingCustomer->kebele ?? '—' }}</strong>
                            </div>
                            <div>
                                <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ t('Branch Office') }}</span>
                                <strong class="text-slate-900 text-sm block">{{ $editingCustomer->customer_branch ?? 'Main' }}</strong>
                            </div>
                            <div>
                                <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ t('Customer Type') }}</span>
                                <strong class="text-slate-900 text-sm block">{{ $editingCustomer->customer_type }}</strong>
                            </div>
                            <div>
                                <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ t('Status') }}</span>
                                <strong class="text-slate-900 text-sm block">{{ $editingCustomer->customer_status }}</strong>
                            </div>
                        </div>
                    </flux:card>

                    <flux:card class="p-5 bg-slate-50/50 border border-slate-200">
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2 mb-3 pb-2 border-b border-slate-200">
                            {!! icon('wrench', 13) !!}
                            <span>{{ t('Meter & Billing Specifications') }}</span>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ t('Meter Code') }}</span>
                                <strong class="text-emerald-700 font-mono text-sm block">{{ $editingCustomer->meter_serial }}</strong>
                            </div>
                            <div>
                                <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ t('Meter Size') }}</span>
                                <strong class="text-slate-900 text-sm block">{{ $editingCustomer->meter_size }}</strong>
                            </div>
                            <div>
                                <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ t('Meter Number') }}</span>
                                <strong class="text-slate-900 font-mono text-sm block">{{ $editingCustomer->meter_num }}</strong>
                            </div>
                            <div>
                                <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ t('Bill Serial Num') }}</span>
                                <strong class="text-emerald-700 font-mono text-sm block">{{ $editingCustomer->bill_num ?? '—' }}</strong>
                            </div>
                            <div>
                                <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ t('Start Reading (m³)') }}</span>
                                <strong class="text-slate-900 font-mono text-sm block">{{ number_format($editingCustomer->start_value, 2) }}</strong>
                            </div>
                            <div>
                                <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ t('Payment Method') }}</span>
                                <strong class="text-slate-900 text-sm block">{{ $editingCustomer->payment_way }}</strong>
                            </div>
                            <div>
                                <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ t('Reader Block') }}</span>
                                <strong class="text-slate-900 font-mono text-sm block">{{ $editingCustomer->reader_block ?? '—' }}</strong>
                            </div>
                            <div>
                                <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ t('Registration Date') }}</span>
                                <strong class="text-slate-900 text-sm block">{{ $editingCustomer->sold_date ?? '—' }}</strong>
                            </div>
                        </div>
                    </flux:card>
                </div>

                <!-- Tab 2: Status & Sync Operations -->
                <div x-show="activeTab === 'status'" x-transition>
                    <flux:card class="p-5 bg-slate-50/50 border border-slate-200 space-y-4">
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-200">
                            {!! icon('shield', 13) !!}
                            <span>{{ t('Quick Status & Sync Operations') }}</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            <div class="p-3 rounded-xl border border-emerald-200 bg-white space-y-2">
                                <div class="text-[10.5px] font-bold text-emerald-700 uppercase tracking-wider">{{ t('Activate Service') }}</div>
                                <p class="text-xs text-slate-500">{{ t('Mark this customer account as Active and restore service.') }}</p>
                                <flux:button size="sm" variant="primary" icon="check" wire:click="updateCustomerStatus('Active')" class="w-full justify-center">{{ t('Re-Activate') }}</flux:button>
                            </div>
                            <div class="p-3 rounded-xl border border-rose-200 bg-white space-y-2">
                                <div class="text-[10.5px] font-bold text-rose-700 uppercase tracking-wider">{{ t('Disconnect Service') }}</div>
                                <p class="text-xs text-slate-500">{{ t('Mark this customer as Disconnected (DC) and halt service.') }}</p>
                                <flux:button size="sm" variant="danger" icon="x-mark" wire:click="updateCustomerStatus('DC')" class="w-full justify-center">{{ t('Disconnect (DC)') }}</flux:button>
                            </div>
                            <div class="p-3 rounded-xl border border-slate-200 bg-white space-y-2">
                                <div class="text-[10.5px] font-bold text-slate-600 uppercase tracking-wider">{{ t('Sync & Update') }}</div>
                                <p class="text-xs text-slate-500">{{ t('Mark record as Updated or sync it to the central database.') }}</p>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" icon="arrow-path" wire:click="updateCustomerStatus('Updated')">{{ t('Updated') }}</flux:button>
                                    <flux:button size="sm" variant="subtle" icon="arrow-path" wire:click="syncCustomer">{{ t('Sync') }}</flux:button>
                                </div>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-slate-200/80">
                            <flux:button size="sm" variant="subtle" icon="trash" wire:click="updateCustomerStatus('Deleted')" wire:confirm="Mark this customer record as Deleted?">{{ t('Mark Record as Deleted') }}</flux:button>
                        </div>
                    </flux:card>
                </div>

                <!-- Tab 3: Field Modifications -->
                <div x-show="activeTab === 'fields'" x-transition class="space-y-4">

                    {{-- Header --}}
                    <div class="flex items-center justify-between gap-3 pb-2 border-b border-slate-200">
                        <div>
                            <flux:heading size="md" class="flex items-center gap-2 font-bold text-slate-900">
                                <flux:icon.pencil-square class="size-4 text-emerald-600" />
                                <span>{{ t('Individual Field Modification Controls') }}</span>
                            </flux:heading>
                            <flux:subheading class="mt-0.5 text-xs text-slate-500">
                                {{ t('Categorized record modifications matching Customer Registration Form schema') }}
                            </flux:subheading>
                        </div>
                        <flux:badge color="emerald" size="sm" class="font-bold">12 {{ t('Fields') }}</flux:badge>
                    </div>

                    {{-- 3 Category Flux Cards --}}
                    <div class="space-y-4">

                        {{-- ── Card 1: Customer Identity ──────────────────────────── --}}
                        <flux:card class="p-5 bg-white border border-slate-200/90 shadow-2xs rounded-2xl space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div class="flex items-center gap-2.5">
                                    <span class="inline-flex items-center justify-center size-8 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <flux:icon.user class="size-4" />
                                    </span>
                                    <div>
                                        <flux:heading size="sm" class="!font-bold text-slate-800">{{ t('Customer Identity & Personal Contact') }}</flux:heading>
                                        <flux:subheading class="!text-[11px] text-slate-400">{{ t('Modify legal customer names and primary phone contact') }}</flux:subheading>
                                    </div>
                                </div>
                                <flux:badge color="emerald" size="sm">4 {{ t('Fields') }}</flux:badge>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach([
                                    ['first_name',   'First Name',   $editingCustomer->first_name   ?? null],
                                    ['middle_name',  'Middle Name',  $editingCustomer->middle_name  ?? null],
                                    ['last_name',    'Last Name',    $editingCustomer->last_name    ?? null],
                                    ['phone_number', 'Phone Number', $editingCustomer->phone_number ?? null],
                                ] as [$field, $label, $value])
                                <div 
                                    wire:click="openFieldUpdateModal('{{ $field }}', '{{ $label }}')"
                                    class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/70 hover:border-emerald-400 hover:bg-emerald-50/30 transition-all duration-150 flex items-center justify-between gap-3 group cursor-pointer"
                                >
                                    <div class="min-w-0 flex-1">
                                        <span class="text-[10px] font-extrabold text-slate-400 group-hover:text-emerald-700 uppercase tracking-wider block mb-0.5 transition-colors">{{ t($label) }}</span>
                                        <strong class="text-sm font-bold text-slate-900 block truncate">{{ $value ?: '—' }}</strong>
                                    </div>
                                    <flux:button 
                                        size="xs" 
                                        variant="filled" 
                                        icon="pencil"
                                        x-on:click.stop="$el.classList.add('btn-pop'); setTimeout(() => $el.classList.remove('btn-pop'), 300)"
                                        wire:click.stop="openFieldUpdateModal('{{ $field }}', '{{ $label }}')"
                                        class="btn-edit-field shrink-0 !bg-emerald-600 !text-white hover:!bg-emerald-700 active:scale-95 transition-all"
                                    >
                                        {{ t('Edit') }}
                                    </flux:button>
                                </div>
                                @endforeach
                            </div>
                        </flux:card>

                        {{-- ── Card 2: Meter & Location ───────────────────────────── --}}
                        <flux:card class="p-5 bg-white border border-slate-200/90 shadow-2xs rounded-2xl space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div class="flex items-center gap-2.5">
                                    <span class="inline-flex items-center justify-center size-8 rounded-xl bg-sky-50 text-sky-700 border border-sky-200">
                                        <flux:icon.map-pin class="size-4" />
                                    </span>
                                    <div>
                                        <flux:heading size="sm" class="!font-bold text-slate-800">{{ t('Meter & Physical Location') }}</flux:heading>
                                        <flux:subheading class="!text-[11px] text-slate-400">{{ t('Kebele location, customer type, meter size, and serial number') }}</flux:subheading>
                                    </div>
                                </div>
                                <flux:badge color="sky" size="sm">4 {{ t('Fields') }}</flux:badge>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach([
                                    ['kebele',        'Kebele',        $editingCustomer->kebele        ? 'Kebele '.$editingCustomer->kebele : null],
                                    ['customer_type', 'Customer Type', $editingCustomer->customer_type ?? null],
                                    ['meter_size',    'Meter Size',    $editingCustomer->meter_size    ?? null],
                                    ['meter_num',     'Meter Number',  $editingCustomer->meter_num     ?? null],
                                ] as [$field, $label, $value])
                                <div 
                                    wire:click="openFieldUpdateModal('{{ $field }}', '{{ $label }}')"
                                    class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/70 hover:border-sky-400 hover:bg-sky-50/30 transition-all duration-150 flex items-center justify-between gap-3 group cursor-pointer"
                                >
                                    <div class="min-w-0 flex-1">
                                        <span class="text-[10px] font-extrabold text-slate-400 group-hover:text-sky-700 uppercase tracking-wider block mb-0.5 transition-colors">{{ t($label) }}</span>
                                        <strong class="text-sm font-bold text-slate-900 block truncate">{{ $value ?: '—' }}</strong>
                                    </div>
                                    <flux:button 
                                        size="xs" 
                                        variant="filled" 
                                        icon="pencil"
                                        x-on:click.stop="$el.classList.add('btn-pop'); setTimeout(() => $el.classList.remove('btn-pop'), 300)"
                                        wire:click.stop="openFieldUpdateModal('{{ $field }}', '{{ $label }}')"
                                        class="btn-edit-field shrink-0 !bg-sky-600 !text-white hover:!bg-sky-700 active:scale-95 transition-all"
                                    >
                                        {{ t('Edit') }}
                                    </flux:button>
                                </div>
                                @endforeach
                            </div>
                        </flux:card>

                        {{-- ── Card 3: Billing Parameters ─────────────────────────── --}}
                        <flux:card class="p-5 bg-white border border-slate-200/90 shadow-2xs rounded-2xl space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div class="flex items-center gap-2.5">
                                    <span class="inline-flex items-center justify-center size-8 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <flux:icon.receipt-percent class="size-4" />
                                    </span>
                                    <div>
                                        <flux:heading size="sm" class="!font-bold text-slate-800">{{ t('Tariff & Billing Parameters') }}</flux:heading>
                                        <flux:subheading class="!text-[11px] text-slate-400">{{ t('Payment channel, utility branch, bill serial number, and reader block') }}</flux:subheading>
                                    </div>
                                </div>
                                <flux:badge color="indigo" size="sm">4 {{ t('Fields') }}</flux:badge>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach([
                                    ['payment_way',     'Payment Way',     $editingCustomer->payment_way     ?? null],
                                    ['customer_branch', 'Branch Office',   $editingCustomer->customer_branch ?? null],
                                    ['bill_num',        'Bill Serial Num', $editingCustomer->bill_num        ?? null],
                                    ['reader_block',    'Reader Block',    $editingCustomer->reader_block    ?? null],
                                ] as [$field, $label, $value])
                                <div 
                                    wire:click="openFieldUpdateModal('{{ $field }}', '{{ $label }}')"
                                    class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/70 hover:border-indigo-400 hover:bg-indigo-50/30 transition-all duration-150 flex items-center justify-between gap-3 group cursor-pointer"
                                >
                                    <div class="min-w-0 flex-1">
                                        <span class="text-[10px] font-extrabold text-slate-400 group-hover:text-indigo-700 uppercase tracking-wider block mb-0.5 transition-colors">{{ t($label) }}</span>
                                        <strong class="text-sm font-bold text-slate-900 block truncate">{{ $value ?: '—' }}</strong>
                                    </div>
                                    <flux:button 
                                        size="xs" 
                                        variant="filled" 
                                        icon="pencil"
                                        x-on:click.stop="$el.classList.add('btn-pop'); setTimeout(() => $el.classList.remove('btn-pop'), 300)"
                                        wire:click.stop="openFieldUpdateModal('{{ $field }}', '{{ $label }}')"
                                        class="btn-edit-field shrink-0 !bg-indigo-600 !text-white hover:!bg-indigo-700 active:scale-95 transition-all"
                                    >
                                        {{ t('Edit') }}
                                    </flux:button>
                                </div>
                                @endforeach
                            </div>
                        </flux:card>

                    </div>
                </div>





                <!-- Tab 4: Special Workflows -->
                <div x-show="activeTab === 'workflows'" x-transition>
                    <flux:card class="p-5 bg-emerald-50/40 border border-emerald-200 space-y-4">
                        <div class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-emerald-200/60">
                            {!! icon('wrench', 13) !!}
                            <span>{{ t('Special Enterprise Workflows') }}</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-4 rounded-xl border border-emerald-200 bg-white space-y-2">
                                <div class="text-xs font-bold text-emerald-700">{{ t('Meter Owner Transfer') }}</div>
                                <p class="text-xs text-slate-500">{{ t('Transfer the water meter account to a new owner with full name and details.') }}</p>
                                <flux:button size="sm" variant="primary" icon="user" wire:click="openOwnerTransferModal" class="mt-1">{{ t('Start Owner Transfer') }}</flux:button>
                            </div>
                            <div class="p-4 rounded-xl border border-slate-200 bg-white space-y-2">
                                <div class="text-xs font-bold text-slate-700">{{ t('Install Replacement Meter') }}</div>
                                <p class="text-xs text-slate-500">{{ t('Record a new meter serial and size to replace the existing meter on this account.') }}</p>
                                <flux:button size="sm" variant="subtle" icon="wrench" wire:click="openInstallNewMeterModal" class="mt-1">{{ t('Install New Meter') }}</flux:button>
                            </div>
                        </div>
                    </flux:card>
                </div>

                <!-- Bottom Action Row -->
                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        {!! icon('clock', 13) !!}
                        <span>{{ t('Account') }} #{{ $editingCustomer->id }} &bull; {{ $editingCustomer->meter_serial }}</span>
                    </div>
                    <flux:modal.close>
                        <flux:button variant="subtle">{{ t('Close Record') }}</flux:button>
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