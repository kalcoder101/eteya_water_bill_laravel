<?php

use App\Livewire\Forms\ReadingCorrectionForm;
use App\Models\ReadingCorrection;
use Flux\Flux;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    public ReadingCorrectionForm $form;

    #[Url(except: 'all')]
    public string $view = 'all';

    #[Url(except: '')]
    public string $searchCustomerCode = '';

    public ?int $selectedComplaintId = null;
    public string $approveCorrectedValue = '';

    public function mount(): void
    {
        $this->form->readingYear = (string) get_setting('current_bill_year', date('Y'));
        $months = faan_oromo_months();
        $this->form->readingMonth = $months[0] ?? 'Amajjii';
        $this->form->complainDateTime = date('Y-m-d H:i:s');
    }

    public function setView(string $viewName): void
    {
        $this->view = $viewName;
    }

    public function submitComplaint(): void
    {
        $this->form->store();
        $this->form->reset('customerCode');
        $this->form->complainDateTime = date('Y-m-d H:i:s');

        Flux::toast('Meter reading complaint submitted successfully.', variant: 'success');
    }

    public function openApproveModal(int $id): void
    {
        $this->selectedComplaintId = $id;
        $this->approveCorrectedValue = '';
        $this->modal('approve-modal')->show();
    }

    public function confirmApprove(): void
    {
        $this->validate([
            'approveCorrectedValue' => 'required|numeric|min:0',
        ]);

        if (! $this->selectedComplaintId) {
            return;
        }

        $complaint = ReadingCorrection::find($this->selectedComplaintId);
        if ($complaint) {
            $user = auth()->user();
            $complaint->update([
                'new_reading'       => (string) $this->approveCorrectedValue,
                'approved_name'     => $user ? $user->fullName() : 'System Admin',
                'correction_status' => 'Approved',
            ]);

            Flux::toast('Complaint approved successfully.', variant: 'success');
        }

        $this->modal('approve-modal')->close();
        $this->selectedComplaintId = null;
        $this->approveCorrectedValue = '';
    }

    public function rejectComplaint(int $id): void
    {
        $complaint = ReadingCorrection::find($id);
        if ($complaint) {
            $user = auth()->user();
            $complaint->update([
                'approved_name'     => $user ? $user->fullName() : 'System Admin',
                'correction_status' => 'Rejected',
            ]);

            Flux::toast('Complaint rejected.', variant: 'danger');
        }
    }

    public function render(): mixed
    {
        $months = faan_oromo_months();

        $query = ReadingCorrection::query();

        if ($this->view === 'daily') {
            $date = date('Y-m-d');
            $query->where(function ($q) use ($date) {
                $q->whereDate('complain_date_time', $date)
                  ->orWhere('complain_date_time', 'like', "$date%");
            });
        } elseif ($this->view === 'monthly') {
            $y = get_setting('current_bill_year', date('Y'));
            $m = $months[0] ?? 'Amajjii';
            $query->where('reading_year', $y)->where('reading_month', $m);
        } elseif ($this->view === 'annual') {
            $y = get_setting('current_bill_year', date('Y'));
            $query->where('reading_year', $y);
        } elseif ($this->view === 'personal' && !empty($this->searchCustomerCode)) {
            $query->where('customer_code', trim($this->searchCustomerCode));
        }

        $complaints = $query->orderByDesc('id')->limit(100)->get();

        $pendingCount  = ReadingCorrection::where('correction_status', 'Pending')->count();
        $approvedCount = ReadingCorrection::where('correction_status', 'Approved')->count();
        $rejectedCount = ReadingCorrection::where('correction_status', 'Rejected')->count();

        return view('pages.⚡reading-correction', [
            'complaints'    => $complaints,
            'months'        => $months,
            'pendingCount'  => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
        ]);
    }
};
?>

<div>
    <!-- Page Header & View Filters -->
    <div class="gsap-hero flex flex-wrap items-end justify-between gap-4 mb-6">
        <div class="min-w-0">
            <h2 class="m-0 text-[22px] font-bold tracking-tight text-slate-900 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-700 inline-flex items-center justify-center shrink-0">{!! icon('wrench', 20) !!}</span>
                <span>{{ t('Reading Correction') }}</span>
            </h2>
            <p class="mt-2 text-[13px] text-slate-500">{{ t('Submit and process meter-reading complaints') }}</p>
        </div>
        <div class="segmented bg-slate-100 p-1">
            <button type="button" class="{{ $view==='all'?'active':'' }}" wire:click="setView('all')">{{ t('All') }}</button>
            <button type="button" class="{{ $view==='daily'?'active':'' }}" wire:click="setView('daily')">{{ t('Daily') }}</button>
            <button type="button" class="{{ $view==='monthly'?'active':'' }}" wire:click="setView('monthly')">{{ t('Monthly') }}</button>
            <button type="button" class="{{ $view==='annual'?'active':'' }}" wire:click="setView('annual')">{{ t('Annual') }}</button>
            <button type="button" class="{{ $view==='personal'?'active':'' }}" wire:click="setView('personal')">{{ t('Personal') }}</button>
        </div>
    </div>

    @if ($view === 'personal')
    <flux:card class="p-3 mb-4">
        <form wire:submit.prevent="$refresh" class="flex flex-wrap gap-2 items-center">
            <flux:input wire:model.live.debounce.300ms="searchCustomerCode" placeholder="{{ t('Customer Code') }}" icon="magnifying-glass" class="flex-1 min-w-[220px]" />
            <flux:button type="submit" variant="primary" icon="magnifying-glass">
                {{ t('Search') }}
            </flux:button>
        </form>
    </flux:card>
    @endif

    <!-- KPI Mini Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        <x-kpi :label="t('Total Complaints')" :value="count($complaints)" icon="file-text" color="emerald" />
        <x-kpi :label="t('Pending Approval')" :value="$pendingCount" icon="clock" color="amber" />
        <x-kpi :label="t('Approved Complaints')" :value="$approvedCount" icon="check" color="emerald" :active="true" />
        <x-kpi :label="t('Rejected Complaints')" :value="$rejectedCount" icon="x" color="rose" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[1fr_1.5fr] gap-5 items-start">
        <!-- Reading Correction Form Card -->
        <flux:card class="p-0 overflow-hidden">
            <div class="h-1 bg-emerald-600"></div>
            <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">{!! icon('wrench', 20) !!}</div>
                <div>
                    <h3 class="m-0 text-sm font-bold text-slate-900">{{ t('Reading Correction Form') }}</h3>
                    <div class="text-xs text-slate-500 mt-0.5">{{ t('Submit a new meter-reading complaint') }}</div>
                </div>
            </div>
            <div class="p-5 space-y-4">
                <form wire:submit.prevent="submitComplaint" id="complaintForm" class="space-y-4">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Customer Code') }} <span class="text-rose-600">*</span></label>
                        <flux:input wire:model="form.customerCode" placeholder="ETY-0001" required />
                        @error('form.customerCode') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Year') }} <span class="text-rose-600">*</span></label>
                        <flux:input wire:model="form.readingYear" required />
                        @error('form.readingYear') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Reading Month') }} <span class="text-rose-600">*</span></label>
                        <select wire:model="form.readingMonth" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500" required>
                            @foreach ($months as $m)
                                <option value="{{ $m }}">{{ $m }}</option>
                            @endforeach
                        </select>
                        @error('form.readingMonth') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Complain Date') }} <span class="text-rose-600">*</span></label>
                        <flux:input wire:model="form.complainDateTime" required />
                        @error('form.complainDateTime') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="pt-2">
                        <flux:button type="submit" variant="primary" icon="paper-airplane" class="w-full justify-center">
                            {{ t('Send Complain') }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </flux:card>

        <!-- Complaints List -->
        <div>
            <flux:card class="p-0 overflow-hidden">
                <div class="h-1 bg-emerald-600"></div>
                <div class="flex items-center justify-between gap-3 px-5 py-3.5 border-b border-slate-100 bg-slate-50/50">
                    <span class="font-bold text-sm text-slate-900">{{ t('Complaints') }} ({{ count($complaints) }})</span>
                </div>
                <div class="p-3 max-h-[700px] overflow-y-auto space-y-3">
                    @if ($complaints->isEmpty())
                        <div class="text-center py-10 px-6 text-slate-500">
                            <div class="flex justify-center text-slate-300 mb-3">{!! icon('file-text', 48) !!}</div>
                            <div class="text-sm font-semibold text-slate-700">{{ t('No complaints') }}</div>
                            <div class="text-xs mt-1">{{ t('Submit a complaint using the form on the left.') }}</div>
                        </div>
                    @else
                        @foreach ($complaints as $c)
                            @php
                                $statusKey = strtolower($c->correction_status);
                                $border = match($statusKey) { 'approved' => 'border-l-emerald-500', 'rejected' => 'border-l-rose-500', default => 'border-l-amber-500' };
                            @endphp
                            <div class="complaint-card border border-slate-200 border-l-4 {{ $border }} rounded-lg bg-white p-4 shadow-card hover:shadow-hover transition-shadow">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-[13px] font-bold text-slate-900">{{ $c->customer_code }} — {{ $c->reading_month }} {{ $c->reading_year }}</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">{{ t('Submitted') }}: {{ $c->complain_date_time }} &middot; {{ t('By') }}: {{ $c->sending_department }}</div>
                                    </div>
                                    @if ($c->correction_status === 'Approved')
                                        <flux:badge color="emerald" icon="check" size="sm">Approved</flux:badge>
                                    @elseif ($c->correction_status === 'Rejected')
                                        <flux:badge color="rose" icon="x-mark" size="sm">Rejected</flux:badge>
                                    @else
                                        <flux:badge color="amber" size="sm">Pending</flux:badge>
                                    @endif
                                </div>
                                <div class="grid grid-cols-2 gap-3 mt-3 border-t border-slate-100 pt-3">
                                    <div>
                                        <div class="text-[11px] text-slate-500 font-semibold">{{ t('New Reading') }}</div>
                                        <div class="text-[13px] font-bold text-slate-900 font-mono tabular-nums">{{ $c->new_reading }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[11px] text-slate-500 font-semibold">{{ t('Approved By') }}</div>
                                        <div class="text-[13px] font-bold text-slate-900">{{ $c->approved_name }}</div>
                                    </div>
                                </div>
                                @if ($c->correction_status === 'Pending')
                                <div class="mt-3 flex gap-2">
                                    <flux:button variant="primary" size="sm" icon="check" wire:click="openApproveModal({{ $c->id }})">
                                        {{ t('Approve') }}
                                    </flux:button>
                                    <flux:button variant="danger" size="sm" icon="x-mark" wire:click="rejectComplaint({{ $c->id }})" wire:confirm="Are you sure you want to reject this complaint?">
                                        {{ t('Reject') }}
                                    </flux:button>
                                </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </flux:card>
        </div>
    </div>

    <!-- Approve Reading Correction Modal -->
    <flux:modal name="approve-modal" class="md:w-96">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ t('Approve Reading Correction') }}</flux:heading>
                <flux:subheading>{{ t('Enter the corrected meter reading value below.') }}</flux:subheading>
            </div>

            <div>
                <flux:input wire:model="approveCorrectedValue" type="number" step="0.01" label="{{ t('Corrected Reading Value') }}" placeholder="e.g. 105.5" required />
                @error('approveCorrectedValue') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="flex gap-2 justify-end">
                <flux:modal.close>
                    <flux:button variant="subtle">{{ t('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="confirmApprove" icon="check">
                    {{ t('Approve') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>