@extends('layouts.app')

@section('content')
<div class="panel">
    <div class="panel-header">{!! icon('user', 16) !!} {{ t('Account Registration') }}</div>
    <div class="panel-body">
        <form method="post" action="{{ route('account-register.save') }}" enctype="multipart/form-data" id="regForm">
            @csrf
            <div class="form-grid">
                <div class="form-group" style="grid-column: span 2;">
                    <label>{{ t('Job Role') }} <span style="color:red;">*</span></label>
                    <select name="jobRole" id="jobRole" required onchange="updateRoleInfo()">
                        <option value="">Select a role...</option>
                        @foreach ($roles as $roleName => $roleInfo)
                            <option value="{{ $roleName }}" data-display="{{ $roleInfo['display'] }}" data-badge="{{ $roleInfo['badge'] }}">
                                {{ $roleInfo['display'] }}
                            </option>
                        @endforeach
                    </select>
                    <div id="roleDescription" style="margin-top:8px; padding:8px; background:#f5f5f5; border-left:3px solid #ddd; display:none;"></div>
                </div>
                <div class="form-group"><label>{{ t('User ID') }}</label><input type="text" name="userId" placeholder="e.g. EMP007" required></div>
                <div class="form-group"><label>{{ t('First Name') }}</label><input type="text" name="firstName" required></div>
                <div class="form-group"><label>{{ t('Middle Name') }}</label><input type="text" name="middleName"></div>
                <div class="form-group"><label>{{ t('Phone Number') }}</label><input type="text" name="phoneNumber" placeholder="+2519..."></div>
                <div class="form-group"><label>Email</label><input type="email" name="emailId"></div>
                <div class="form-group"><label>{{ t('Username') }}</label><input type="text" name="userName" required pattern="[A-Za-z]\w{5,29}" title="6-30 chars, starts with letter"></div>
                <div class="form-group">
                    <label>{{ t('Password') }}</label>
                    <div style="position:relative;">
                        <input type="password" name="userPassword" id="passwordField" required style="padding-right: 36px;">
                        <button type="button" class="btn-icon-toggle" onclick="togglePasswordVisibility()" style="position:absolute; right:4px; top:4px; background:none; border:none; cursor:pointer; padding:4px; color:#666;">
                            {!! icon('eye', 18) !!}
                        </button>
                    </div>
                </div>
                <div class="form-group"><label>Photo</label><input type="file" name="photo" accept="image/*"></div>
            </div>
            <div style="margin-top: 16px;">
                <button type="submit" class="btn btn-success">{!! icon('check', 16) !!} {{ t('Update') }} / {{ t('Register') }}</button>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header">{!! icon('customers', 16) !!} {{ t('System Users') }} ({{ count($users) }})</div>
    <div style="overflow-x:auto; max-height: 500px; overflow-y:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ t('User ID') }}</th>
                    <th>{{ t('Name') }}</th>
                    <th>{{ t('Phone') }}</th>
                    <th>Email</th>
                    <th>{{ t('Role') }}</th>
                    <th>{{ t('Username') }}</th>
                    <th>Photo</th>
                    <th>{{ t('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($users as $u)
                @php $roleInfo = $roles[$u->job_role] ?? ['display' => $u->job_role, 'badge' => 'badge-default']; @endphp
                <tr>
                    <td><strong>{{ $u->user_id }}</strong></td>
                    <td>{{ trim(($u->first_name ?? '').' '.($u->last_name ?? '')) }}</td>
                    <td>{{ $u->phone_number }}</td>
                    <td>{{ $u->email_id }}</td>
                    <td><span class="badge {{ $roleInfo['badge'] }}">{{ $roleInfo['display'] }}</span></td>
                    <td>{{ $u->user_name }}</td>
                    <td>
                        @if ($u->photo)
                            <img src="{{ route('api.user.photo', ['userId' => $u->user_id]) }}" alt="photo" style="height:30px; width:30px; border-radius:50%; object-fit:cover;">
                        @else
                            <span style="color:#9ca3af;">—</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-sm" type="button" onclick="editUser('{{ e($u->user_id) }}')">{!! icon('edit', 14) !!} {{ t('Edit') }}</button>
                        <form method="post" action="{{ route('account-register.delete') }}" style="display:inline; margin:0;">
                            @csrf
                            <input type="hidden" name="id" value="{{ $u->user_id }}">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">{!! icon('trash', 14) !!} {{ t('Delete') }}</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
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
