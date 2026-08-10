<?php

namespace App\Livewire\Forms;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\Hash;
use Livewire\Form;

class UserAccountForm extends Form
{
    public string $userId = '';
    public string $jobRole = '';
    public string $firstName = '';
    public ?string $middleName = '';
    public ?string $phoneNumber = '';
    public ?string $emailId = '';
    public string $userName = '';
    public string $userPassword = '';
    public mixed $photo = null;

    public function rules(): array
    {
        $existing = User::find($this->userId);

        return [
            'userId'       => 'required|string|max:50',
            'jobRole'      => 'required|string|max:50',
            'firstName'    => 'required|string|max:100',
            'middleName'   => 'nullable|string|max:100',
            'phoneNumber'  => 'nullable|string|max:30',
            'emailId'      => 'nullable|email|max:150',
            'userName'     => 'required|string|max:50|regex:/^[A-Za-z]\w{5,29}$/',
            'userPassword' => $existing ? 'nullable|string|max:255' : 'required|string|max:255',
            'photo'        => 'nullable|image|max:2048',
        ];
    }

    public function save(): User
    {
        $this->validate();

        $photoData = null;
        if ($this->photo && is_object($this->photo) && method_exists($this->photo, 'getRealPath')) {
            $photoData = file_get_contents($this->photo->getRealPath());
        }

        $existing = User::find($this->userId);
        $lastName = $this->middleName;

        if ($existing) {
            $updateData = [
                'first_name'   => $this->firstName,
                'last_name'    => $lastName,
                'phone_number' => $this->phoneNumber ?: null,
                'email_id'     => $this->emailId ?: null,
                'job_role'     => $this->jobRole,
                'user_name'    => $this->userName,
            ];

            if (!empty($this->userPassword)) {
                $updateData['user_password'] = Hash::make($this->userPassword);
            }

            if ($photoData !== null) {
                $updateData['photo'] = $photoData;
            }

            $existing->update($updateData);
            $user = $existing;
        } else {
            $user = User::create([
                'user_id'       => $this->userId,
                'first_name'    => $this->firstName,
                'last_name'     => $lastName,
                'phone_number'  => $this->phoneNumber ?: null,
                'email_id'      => $this->emailId ?: null,
                'job_role'      => $this->jobRole,
                'user_name'     => $this->userName,
                'user_password' => Hash::make($this->userPassword),
                'photo'         => $photoData,
            ]);
        }

        app(AuditService::class)->logAudit(
            'Registered/updated user '.$this->userId.' ('.$this->userName.', '.$this->jobRole.')',
            auth()->user()?->fullName() ?? 'System Admin'
        );

        return $user;
    }

    public function fillFromUser(User $user): void
    {
        $this->userId = $user->user_id;
        $this->jobRole = $user->job_role;
        $this->firstName = $user->first_name;
        $this->middleName = $user->last_name;
        $this->phoneNumber = $user->phone_number;
        $this->emailId = $user->email_id;
        $this->userName = $user->user_name;
        $this->userPassword = '';
        $this->photo = null;
    }
}
