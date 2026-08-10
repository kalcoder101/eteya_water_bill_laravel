<?php

use App\Livewire\Forms\UserAccountForm;
use App\Models\User;
use App\Services\AuditService;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public UserAccountForm $form;
    public bool $isEditing = false;
    public bool $showPassword = false;

    public function saveUser(): void
    {
        $this->form->save();
        $this->form->reset();
        $this->isEditing = false;

        Flux::toast('User account saved successfully.', variant: 'success');
    }

    public function editUser(string $userId): void
    {
        $user = User::find($userId);
        if (! $user) {
            Flux::toast('User not found.', variant: 'danger');
            return;
        }

        $this->form->fillFromUser($user);
        $this->isEditing = true;
    }

    public function cancelEdit(): void
    {
        $this->form->reset();
        $this->isEditing = false;
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
                'Deleted user '.$userId,
                auth()->user()?->fullName() ?? 'System Admin'
            );

            Flux::toast('User deleted successfully.', variant: 'success');

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
        $users = User::orderBy('job_role')->orderBy('user_id')->get();

        return view('pages.⚡account-register', [
            'roles' => $roles,
            'users' => $users,
        ]);
    }
};
?>

<div>
    <!-- Page Header -->
    <div class="gsap-hero flex flex-wrap items-end justify-between gap-4 mb-6">
        <div class="min-w-0">
            <h2 class="m-0 text-[22px] font-bold tracking-tight text-slate-900 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-700 inline-flex items-center justify-center shrink-0">{!! icon('user', 20) !!}</span>
                <span>{{ t('Account Registration') }}</span>
            </h2>
            <p class="mt-2 text-[13px] text-slate-500">{{ t('Register a new system user account with full personal, role and photo details') }}</p>
        </div>
    </div>

    <!-- Registration / Edit Form Card -->
    <flux:card class="p-0 overflow-hidden mb-6">
        <div class="h-1 bg-emerald-600"></div>
        <div class="flex items-center justify-between gap-3 px-5 py-3.5 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-2">
                <span class="text-emerald-600">{!! icon('user', 16) !!}</span>
                <span class="font-bold text-slate-900 text-sm">
                    {{ $isEditing ? t('Edit User Account').' ('.$form->userId.')' : t('Account Registration') }}
                </span>
            </div>
            @if ($isEditing)
                <flux:badge color="amber" size="sm">Editing Mode</flux:badge>
            @endif
        </div>
        <div class="p-5">
            <form wire:submit.prevent="saveUser" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Job Role') }} <span class="text-rose-600">*</span></label>
                        <select wire:model.live="form.jobRole" required class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                            <option value="">Select a role...</option>
                            @foreach ($roles as $roleName => $roleInfo)
                                <option value="{{ $roleName }}">{{ $roleInfo['display'] }}</option>
                            @endforeach
                        </select>
                        @error('form.jobRole') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        @if ($form->jobRole && isset($roles[$form->jobRole]))
                            <div class="mt-2 px-3 py-2 bg-slate-50 border-l-4 border-emerald-500 text-xs text-slate-600 rounded-r-md">
                                {{ $roles[$form->jobRole]['display'] }}
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('User ID') }} <span class="text-rose-600">*</span></label>
                        <flux:input wire:model="form.userId" placeholder="e.g. EMP007" :disabled="$isEditing" required />
                        @error('form.userId') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('First Name') }} <span class="text-rose-600">*</span></label>
                        <flux:input wire:model="form.firstName" placeholder="First Name" required />
                        @error('form.firstName') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Middle Name') }}</label>
                        <flux:input wire:model="form.middleName" placeholder="Middle Name" />
                        @error('form.middleName') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Phone Number') }}</label>
                        <flux:input wire:model="form.phoneNumber" placeholder="+2519..." />
                        @error('form.phoneNumber') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Email</label>
                        <flux:input type="email" wire:model="form.emailId" placeholder="user@example.com" />
                        @error('form.emailId') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Username') }} <span class="text-rose-600">*</span></label>
                        <flux:input wire:model="form.userName" placeholder="Username" required />
                        @error('form.userName') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Password') }} @if(!$isEditing)<span class="text-rose-600">*</span>@endif</label>
                        <div class="relative">
                            <flux:input wire:model="form.userPassword" :type="$showPassword ? 'text' : 'password'" placeholder="{{ $isEditing ? 'Leave empty to keep current' : 'Password' }}" />
                            <button type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-emerald-700 transition" wire:click="togglePasswordVisibility" title="Show/hide password">
                                {!! icon($showPassword ? 'eye-off' : 'eye', 18) !!}
                            </button>
                        </div>
                        @error('form.userPassword') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Photo</label>
                        <input type="file" wire:model="form.photo" accept="image/*" class="w-full text-sm text-slate-500 file:mr-3 file:px-3.5 file:py-2 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-emerald-700 file:font-bold file:text-xs hover:file:bg-emerald-100 transition">
                        @error('form.photo') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        @if ($form->photo && is_object($form->photo))
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-xs text-emerald-600 font-semibold">Photo selected</span>
                                <img src="{{ $form->photo->temporaryUrl() }}" class="w-8 h-8 rounded-full object-cover border border-emerald-300">
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-5 flex gap-3">
                    <flux:button type="submit" variant="primary" icon="check">
                        {{ $isEditing ? t('Update Account') : t('Register Account') }}
                    </flux:button>
                    @if ($isEditing)
                        <flux:button type="button" variant="subtle" wire:click="cancelEdit">
                            {{ t('Cancel') }}
                        </flux:button>
                    @endif
                </div>
            </form>
        </div>
    </flux:card>

    <!-- Users List Table Card -->
    <flux:card class="p-0 overflow-hidden">
        <div class="h-1 bg-emerald-600"></div>
        <div class="flex items-center justify-between gap-3 px-5 py-3.5 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-2">
                <span class="text-emerald-600">{!! icon('customers', 16) !!}</span>
                <span class="font-bold text-slate-900 text-sm">{{ t('System Users') }}</span>
            </div>
            <flux:badge color="zinc" size="sm">{{ count($users) }} {{ t('Users') }}</flux:badge>
        </div>
        <div class="scrollable-table border-0 rounded-none">
            <div class="scroll-progress"><div class="scroll-progress-bar"></div></div>
            <div class="table-scroll-view">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold">
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('User ID') }}</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Name') }}</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Phone') }}</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">Email</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Role') }}</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">{{ t('Username') }}</th>
                            <th class="text-left px-4 py-3 whitespace-nowrap">Photo</th>
                            <th class="text-right px-4 py-3 whitespace-nowrap">{{ t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($users as $u)
                        @php
                            $roleInfo = $roles[$u->job_role] ?? ['display' => $u->job_role, 'badge' => 'badge-default'];
                            $badgeColor = match($u->job_role) {
                                'System Admin', 'Admin' => 'emerald',
                                'Manager' => 'sky',
                                'Data Encoder' => 'amber',
                                default => 'zinc',
                            };
                        @endphp
                        <tr class="border-b border-slate-100 odd:bg-white even:bg-slate-50/40 hover:bg-emerald-50/60 transition-colors">
                            <td class="px-4 py-2.5 text-slate-700 align-middle"><strong class="font-mono text-emerald-700">{{ $u->user_id }}</strong></td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle"><strong class="text-slate-900">{{ trim(($u->first_name ?? '').' '.($u->last_name ?? '')) }}</strong></td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle">{{ $u->phone_number ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle">{{ $u->email_id ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle">
                                <flux:badge color="{{ $badgeColor }}" size="sm">{{ $roleInfo['display'] }}</flux:badge>
                            </td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle font-mono">{{ $u->user_name }}</td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle">
                                @if ($u->photo)
                                    <img src="{{ route('api.user.photo', ['userId' => $u->user_id]) }}" alt="photo" class="h-8 w-8 rounded-full object-cover border border-slate-200">
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-slate-700 align-middle">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button size="sm" icon="pencil-square" wire:click="editUser('{{ $u->user_id }}')">
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
                    </tbody>
                </table>
            </div>
        </div>
    </flux:card>
</div>