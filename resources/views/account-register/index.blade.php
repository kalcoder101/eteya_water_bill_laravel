@extends('layouts.app')

@section('content')
<div class="gsap-hero flex flex-wrap items-end justify-between gap-4 mb-6">
    <div class="min-w-0">
        <h2 class="m-0 text-[22px] font-bold tracking-tight text-slate-900 flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-700 inline-flex items-center justify-center shrink-0">{!! icon('user', 20) !!}</span>
            <span>{{ t('Account Registration') }}</span>
        </h2>
        <p class="mt-2 text-[13px] text-slate-500">{{ t('Register a new water meter account with full customer, address and photo details') }}</p>
    </div>
</div>
<div class="gsap-section-card bg-white border border-slate-200 rounded-xl shadow-card overflow-hidden mb-6">
    <div class="flex items-center gap-3 px-5 py-3.5 border-b border-slate-100">
        <span class="text-emerald-600">{!! icon('user', 16) !!}</span>
        <span class="font-serif font-bold text-slate-900 text-sm">{{ t('Account Registration') }}</span>
    </div>
    <div class="p-5">
        <form method="post" action="{{ route('account-register.save') }}" enctype="multipart/form-data" id="regForm">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Job Role') }} <span class="text-rose-600">*</span></label>
                    <select name="jobRole" id="jobRole" required onchange="updateRoleInfo()" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                        <option value="">Select a role...</option>
                        @foreach ($roles as $roleName => $roleInfo)
                            <option value="{{ $roleName }}" data-display="{{ $roleInfo['display'] }}" data-badge="{{ $roleInfo['badge'] }}">
                                {{ $roleInfo['display'] }}
                            </option>
                        @endforeach
                    </select>
                    <div id="roleDescription" class="hidden mt-2 px-3 py-2 bg-slate-50 border-l-4 border-slate-300 text-xs text-slate-600 rounded-r-md"></div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('User ID') }}</label>
                    <input type="text" name="userId" placeholder="e.g. EMP007" required class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder:text-slate-400">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('First Name') }}</label>
                    <input type="text" name="firstName" required class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder:text-slate-400">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Middle Name') }}</label>
                    <input type="text" name="middleName" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder:text-slate-400">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Phone Number') }}</label>
                    <input type="text" name="phoneNumber" placeholder="+2519..." class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder:text-slate-400">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Email</label>
                    <input type="email" name="emailId" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder:text-slate-400">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Username') }}</label>
                    <input type="text" name="userName" required pattern="[A-Za-z]\w{5,29}" title="6-30 chars, starts with letter" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder:text-slate-400">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ t('Password') }}</label>
                    <div class="relative">
                        <input type="password" name="userPassword" id="passwordField" required class="w-full px-3 py-2.5 pr-10 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                        <button type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 rounded text-slate-400 hover:text-emerald-700 hover:bg-emerald-50 transition" onclick="togglePasswordVisibility()" title="Show/hide password">
                            {!! icon('eye', 18) !!}
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Photo</label>
                    <input type="file" name="photo" accept="image/*" class="w-full text-sm text-slate-500 file:mr-3 file:px-3.5 file:py-2 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-emerald-700 file:font-bold file:text-xs hover:file:bg-emerald-100 transition">
                </div>
            </div>
            <div class="mt-5 flex gap-3">
                <x-button type="submit" variant="primary" icon="check">
                    {{ t('Update') }} / {{ t('Register') }}
                </x-button>
            </div>
        </form>
    </div>
</div>

<div class="gsap-section-card bg-white border border-slate-200 rounded-xl shadow-card overflow-hidden">
    <div class="flex items-center gap-3 px-5 py-3.5 border-b border-slate-100">
        <span class="text-emerald-600">{!! icon('customers', 16) !!}</span>
        <span class="font-serif font-bold text-slate-900 text-sm">{{ t('System Users') }} <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-600 text-[10px] px-2.5 py-0.5 font-bold uppercase tracking-wider border border-slate-200 ml-1">{{ count($users) }}</span></span>
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
                    @php $roleInfo = $roles[$u->job_role] ?? ['display' => $u->job_role, 'badge' => 'badge-default']; @endphp
                    <tr class="border-b border-slate-100 odd:bg-white even:bg-slate-50/40 hover:bg-emerald-50/60 transition-colors">
                        <td class="px-4 py-2.5 text-slate-700 align-middle"><strong class="font-mono">{{ $u->user_id }}</strong></td>
                        <td class="px-4 py-2.5 text-slate-700 align-middle">{{ trim(($u->first_name ?? '').' '.($u->last_name ?? '')) }}</td>
                        <td class="px-4 py-2.5 text-slate-700 align-middle">{{ $u->phone_number }}</td>
                        <td class="px-4 py-2.5 text-slate-700 align-middle">{{ $u->email_id }}</td>
                        <td class="px-4 py-2.5 text-slate-700 align-middle"><span class="badge {{ $roleInfo['badge'] }}">{{ $roleInfo['display'] }}</span></td>
                        <td class="px-4 py-2.5 text-slate-700 align-middle">{{ $u->user_name }}</td>
                        <td class="px-4 py-2.5 text-slate-700 align-middle">
                            @if ($u->photo)
                                <img src="{{ route('api.user.photo', ['userId' => $u->user_id]) }}" alt="photo" class="h-8 w-8 rounded-full object-cover border border-slate-200">
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-slate-700 align-middle">
                            <div class="flex items-center justify-end gap-2">
                                <x-button variant="secondary" size="sm" icon="edit" type="button" onclick="editUser('{{ e($u->user_id) }}')">
                                    {{ t('Edit') }}
                                </x-button>
                                <form method="post" action="{{ route('account-register.delete') }}" class="inline m-0">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $u->user_id }}">
                                    <x-button type="submit" variant="danger" size="sm" icon="trash" onclick="return confirm('Delete this user?')">
                                        {{ t('Delete') }}
                                    </x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const roleDisplayMap = {{ json_encode(array_map(fn($r) => $r['display'], $roles)) }};

function updateRoleInfo() {
    const roleSelect = document.getElementById('jobRole');
    const roleDesc = document.getElementById('roleDescription');
    const selectedRole = roleSelect.value;
    if (selectedRole) {
        roleDesc.style.display = 'block';
        roleDesc.textContent = roleDisplayMap[selectedRole] || selectedRole;
    } else {
        roleDesc.style.display = 'none';
    }
}

function togglePasswordVisibility() {
    const field = document.getElementById('passwordField');
    const button = event.target.closest('button');
    if (field.type === 'password') {
        field.type = 'text';
        button.innerHTML = '{!! icon('eye-off', 18) !!}';
    } else {
        field.type = 'password';
        button.innerHTML = '{!! icon('eye', 18) !!}';
    }
}

function editUser(userId) {
    fetch(apiUrl(`user_account_data/get-user-account/${encodeURIComponent(userId)}`))
      .then(r => r.json())
      .then(u => {
          if (!u) return alert('User not found');
          document.querySelector('[name=userId]').value = u.user_id;
          document.querySelector('[name=jobRole]').value = u.job_role;
          updateRoleInfo();
          document.querySelector('[name=firstName]').value = u.first_name;
          document.querySelector('[name=middleName]').value = u.last_name || '';
          document.querySelector('[name=phoneNumber]').value = u.phone_number || '';
          document.querySelector('[name=emailId]').value = u.email_id || '';
          document.querySelector('[name=userName]').value = u.user_name;
          document.querySelector('[name=userPassword]').value = u.user_password;
          window.scrollTo({top: 0, behavior: 'smooth'});
      });
}
</script>
@endsection
