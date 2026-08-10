<?php

use App\Livewire\Forms\UserAccountForm;
use App\Models\User;
use App\Services\AuditService;
use Flux\Flux;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public UserAccountForm $form;
    public bool $isEditing = false;
    public bool $showPassword = false;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $roleFilter = 'all';

    public function openCreateModal(): void
    {
        $this->form->reset();
        $this->isEditing = false;
        $this->modal('user-form-modal')->show();
    }

    public function saveUser(): void
    {
        $this->form->save();
        $this->form->reset();
        $this->isEditing = false;
        $this->modal('user-form-modal')->close();

        Flux::toast('User account saved successfully.', variant: 'success');
    }

    public function editUser(string $userId): void
    {
        $user = User::find($userId);
        if (! $user) {
            Flux::toast('User account not found.', variant: 'danger');
            return;
        }

        $this->form->fillFromUser($user);
        $this->isEditing = true;
        $this->modal('user-form-modal')->show();
    }

    public function cancelEdit(): void
    {
        $this->form->reset();
        $this->isEditing = false;
        $this->modal('user-form-modal')->close();
    }

    public function togglePasswordVisibility(): void
    {
        $this->showPassword = ! $this->showPassword;
    }

    public function deleteUser(string $userId): void
    {
        $user = User::where('user_id', $userId)
            ->where('job_role', '!=', 'System Admin')
            ->first();

        if ($user) {
            $user->delete();
            app(AuditService::class)->logAudit(
                'Deleted user account '.$userId,
                auth()->user()?->fullName() ?? 'System Admin'
            );

            Flux::toast('User account deleted successfully.', variant: 'success');

            if ($this->form->userId === $userId) {
                $this->cancelEdit();
            }
        } else {
            Flux::toast('Cannot delete System Admin account.', variant: 'danger');
        }
    }

    public function render(): mixed
    {
        $roles = job_roles_list();
        $allUsers = User::orderBy('job_role')->orderBy('user_id')->get();

        $counts = [
            'total'   => $allUsers->count(),
            'admins'  => $allUsers->whereIn('job_role', ['System Admin', 'Admin'])->count(),
            'cs'      => $allUsers->where('job_role', 'Customer Service')->count(),
            'field'   => $allUsers->whereIn('job_role', ['Bill Reader', 'Data Encoder', 'Secretary', 'Manager'])->count(),
        ];

        $filteredUsers = $allUsers->filter(function ($u) {
            $s = trim(strtolower($this->search));
            $matchSearch = empty($s) || (
                str_contains(strtolower($u->user_id), $s) ||
                str_contains(strtolower($u->first_name ?? ''), $s) ||
                str_contains(strtolower($u->last_name ?? ''), $s) ||
                str_contains(strtolower($u->user_name), $s) ||
                str_contains(strtolower($u->email_id ?? ''), $s)
            );

            $matchRole = $this->roleFilter === 'all' || $u->job_role === $this->roleFilter;

            return $matchSearch && $matchRole;
        });

        return view('pages.⚡account-register', [
            'roles' => $roles,
            'users' => $filteredUsers,
            'counts' => $counts,
        ]);
    }
};
?>

<div>
    <!-- Page Header Banner -->
    <div class="gsap-hero flex flex-wrap items-end justify-between gap-4 mb-6">
        <div class="min-w-0">
            <h2 class="m-0 text-[22px] font-bold tracking-tight text-slate-900 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-700 inline-flex items-center justify-center shrink-0">{!! icon('user', 20) !!}</span>
                <span>{{ t('Staff Account Management') }}</span>
            </h2>
            <p class="mt-2 text-[13px] text-slate-500">{{ t('Register, manage, and configure system staff user accounts, permissions, and roles') }}</p>
        </div>
        <div>
            <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
                {{ t('Register New Staff Account') }}
            </flux:button>
        </div>
    </div>

    <!-- KPI Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-kpi :label="t('Total Staff Accounts')" :value="number_format($counts['total'])" :subvalue="t('Active System Users')" icon="users" color="emerald" />
        <x-kpi :label="t('System Administrators')" :value="number_format($counts['admins'])" :subvalue="t('Supervisors & Admins')" icon="shield" color="emerald" :active="true" />
        <x-kpi :label="t('Customer Service')" :value="number_format($counts['cs'])" :subvalue="t('Front Desk Agents')" icon="customers" color="sky" />
        <x-kpi :label="t('Field & Operational')" :value="number_format($counts['field'])" :subvalue="t('Readers & Encoders')" icon="wrench" color="amber" />
    </div>

    <!-- Toolbar & Filter Controls -->
    <flux:card class="p-4 mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-3 flex-1 min-w-[260px]">
            <div class="relative flex-1 max-w-[360px]">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ t('Search ID, name, username, email...') }}" icon="magnifying-glass" />
            </div>

            <div class="min-w-[180px]">
                <flux:select wire:model.live="roleFilter" placeholder="{{ t('Filter by Role') }}">
                    <flux:select.option value="all">— {{ t('All Staff Roles') }} —</flux:select.option>
                    @foreach ($roles as $roleName => $roleInfo)
                        <flux:select.option value="{{ $roleName }}">{{ $roleInfo['display'] }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <div>
            <flux:badge color="zinc" size="sm">{{ count($users) }} {{ t('Staff Members') }}</flux:badge>
        </div>
    </flux:card>

    <!-- Staff Users Data Table -->
    <flux:card class="p-0 overflow-hidden">
        <div class="h-1 bg-emerald-600"></div>
        <div class="flex items-center justify-between gap-3 px-5 py-3.5 border-b border-slate-100 bg-slate-50/50">
            <span class="font-bold text-sm text-slate-900">{{ t('Registered Staff Members Directory') }}</span>
            <flux:badge color="emerald" size="sm" icon="check">{{ t('System Active') }}</flux:badge>
        </div>

        <div class="scrollable-table border-0 rounded-none">
            <div class="scroll-progress"><div class="scroll-progress-bar"></div></div>
            <div class="table-scroll-view">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold">
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('User ID') }}</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Staff Member') }}</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Phone Number') }}</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">Email</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Assigned Role') }}</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Username') }}</th>
                            <th class="text-right px-4 py-3 whitespace-nowrap">{{ t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($users as $u)
                        @php
                            $roleInfo = $roles[$u->job_role] ?? ['display' => $u->job_role];
                            $badgeColor = match($u->job_role) {
                                'System Admin', 'Admin' => 'emerald',
                                'Manager' => 'sky',
                                'Customer Service' => 'indigo',
                                'Data Encoder', 'Secretary' => 'amber',
                                default => 'zinc',
                            };
                            $photoUrl = $u->photo
                                ? route('api.user.photo', ['userId' => $u->user_id])
                                : null;
                        @endphp
                        <tr class="border-b border-slate-100 odd:bg-white even:bg-slate-50/40 hover:bg-emerald-50/60 transition-colors">
                            <td class="px-4 py-2.5 text-slate-700 align-middle"><strong class="font-mono text-emerald-700 text-[12.5px] bg-emerald-50 px-2 py-1 rounded border border-emerald-200">{{ $u->user_id }}</strong></td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle">
                                <div class="flex items-center gap-3">
                                    @if ($photoUrl)
                                        <img src="{{ $photoUrl }}" alt="photo" class="h-9 w-9 rounded-full object-cover border border-emerald-200">
                                    @else
                                        <div class="h-9 w-9 rounded-full bg-slate-100 border border-slate-200 text-slate-500 font-bold text-xs flex items-center justify-center">
                                            {{ strtoupper(substr($u->first_name ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-bold text-slate-900 text-[13.5px]">{{ trim(($u->first_name ?? '').' '.($u->middle_name ?? '').' '.($u->last_name ?? '')) }}</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">{{ $u->job_role }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle">{{ $u->phone_number ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle font-mono text-xs">{{ $u->email_id ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle">
                                <flux:badge color="{{ $badgeColor }}" size="sm">{{ $roleInfo['display'] }}</flux:badge>
                            </td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle font-mono font-semibold">{{ $u->user_name }}</td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <flux:button size="sm" icon="pencil-square" wire:click="editUser('{{ $u->user_id }}')" title="{{ t('Edit Account') }}">
                                        {{ t('Edit') }}
                                    </flux:button>
                                    @if ($u->job_role !== 'System Admin')
                                        <flux:button variant="danger" size="sm" icon="trash" wire:click="deleteUser('{{ $u->user_id }}')" wire:confirm="Are you sure you want to delete user {{ $u->user_id }}?">
                                            {{ t('Delete') }}
                                        </flux:button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if ($users->isEmpty())
                        <tr>
                            <td colspan="7" class="text-center py-10 px-4 text-slate-500 text-xs">{{ t('No staff account records found matching your filters.') }}</td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </flux:card>

    <!-- Staff Account Registration & Edit Flux Modal -->
    <flux:modal name="user-form-modal" class="md:w-[680px]">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ $isEditing ? t('Edit Staff Account').' ('.$form->userId.')' : t('Staff Account Registration') }}</flux:heading>
                <flux:subheading>{{ t('Configure user identity, system role permissions, credentials and staff photo') }}</flux:subheading>
            </div>

            <form wire:submit.prevent="saveUser" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <flux:field>
                            <flux:label class="text-xs font-bold text-slate-500 mb-1">{{ t('Job Role') }} *</flux:label>
                            <flux:select wire:model.live="form.jobRole" placeholder="Select assigned system role...">
                                <flux:select.option value="">— Select assigned system role —</flux:select.option>
                                @foreach ($roles as $roleName => $roleInfo)
                                    <flux:select.option value="{{ $roleName }}">{{ $roleInfo['display'] }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            @error('form.jobRole') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        </flux:field>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">{{ t('User ID') }} *</label>
                        <flux:input wire:model="form.userId" placeholder="e.g. EMP007" :disabled="$isEditing" required />
                        @error('form.userId') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">{{ t('First Name') }} *</label>
                        <flux:input wire:model="form.firstName" placeholder="First Name" required />
                        @error('form.firstName') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">{{ t('Middle Name') }}</label>
                        <flux:input wire:model="form.middleName" placeholder="Middle Name" />
                        @error('form.middleName') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">{{ t('Last Name') }}</label>
                        <flux:input wire:model="form.lastName" placeholder="Last Name" />
                        @error('form.lastName') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">{{ t('Phone Number') }}</label>
                        <flux:input wire:model="form.phoneNumber" placeholder="+2519..." />
                        @error('form.phoneNumber') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">Email</label>
                        <flux:input type="email" wire:model="form.emailId" placeholder="user@watersteward.gov.et" />
                        @error('form.emailId') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">{{ t('Username') }} *</label>
                        <flux:input wire:model="form.userName" placeholder="Username" required />
                        @error('form.userName') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">{{ t('Password') }} @if(!$isEditing)*@endif</label>
                        <div class="relative">
                            <flux:input wire:model="form.userPassword" :type="$showPassword ? 'text' : 'password'" placeholder="{{ $isEditing ? 'Leave empty to keep current' : 'Password' }}" />
                            <button type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-emerald-700 transition" wire:click="togglePasswordVisibility" title="Show/hide password">
                                {!! icon($showPassword ? 'eye-off' : 'eye', 18) !!}
                            </button>
                        </div>
                        @error('form.userPassword') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-500 mb-1">Staff Profile Photo</label>
                        <input type="file" wire:model="form.photo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:px-3.5 file:py-2 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-emerald-700 file:font-bold file:text-xs hover:file:bg-emerald-100 transition">
                        @error('form.photo') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        @if ($form->photo && is_object($form->photo))
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-xs text-emerald-600 font-semibold">Photo selected</span>
                                <img src="{{ $form->photo->temporaryUrl() }}" class="w-9 h-9 rounded-full object-cover border border-emerald-300">
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex gap-2 justify-end pt-3">
                    <flux:button type="button" variant="subtle" wire:click="cancelEdit">
                        {{ t('Cancel') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary" icon="check">
                        {{ $isEditing ? t('Update Staff Account') : t('Register Staff Account') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>