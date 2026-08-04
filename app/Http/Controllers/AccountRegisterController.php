<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AccountRegisterController extends Controller
{
    public function index()
    {
        $roles = job_roles_list();
        $users = User::orderBy('job_role')->orderBy('user_id')->get();

        return view('account-register.index', [
            'roles'     => $roles,
            'users'     => $users,
            'pageTitle' => 'Account Registration',
            'pageAction' => [
                'label'    => t('Register'),
                'href'     => '#',
                'icon'     => 'plus',
                'onclick'  => "document.getElementById('regForm').submit(); return false;",
            ],
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'userId'       => 'required|string|max:50',
            'jobRole'      => 'required|string|max:50',
            'firstName'    => 'required|string|max:100',
            'middleName'   => 'nullable|string|max:100',
            'phoneNumber'  => 'nullable|string|max:30',
            'emailId'      => 'nullable|email|max:150',
            'userName'     => 'required|string|max:50|regex:/^[A-Za-z]\w{5,29}$/',
            'userPassword' => 'required|string|max:255',
            'photo'        => 'nullable|image|max:2048',
        ]);

        $photo = null;
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $photo = file_get_contents($request->file('photo')->getRealPath());
        }

        $hashedPassword = Hash::make($validated['userPassword']);
        $lastName = $validated['middleName'] ?? null; // matches original column mapping

        $existing = User::find($validated['userId']);
        if ($existing) {
            $existing->update([
                'first_name'     => $validated['firstName'],
                'last_name'      => $lastName,
                'phone_number'   => $validated['phoneNumber'] ?? null,
                'email_id'       => $validated['emailId'] ?? null,
                'job_role'       => $validated['jobRole'],
                'user_name'      => $validated['userName'],
                'user_password'  => $hashedPassword,
                'photo'          => $photo ?? $existing->photo,
            ]);
        } else {
            User::create([
                'user_id'        => $validated['userId'],
                'first_name'     => $validated['firstName'],
                'last_name'      => $lastName,
                'phone_number'   => $validated['phoneNumber'] ?? null,
                'email_id'       => $validated['emailId'] ?? null,
                'job_role'       => $validated['jobRole'],
                'user_name'      => $validated['userName'],
                'user_password'  => $hashedPassword,
                'photo'          => $photo,
            ]);
        }

        app(AuditService::class)->logAudit(
            'Registered/updated user '.$validated['userId'].' ('.$validated['userName'].', '.$validated['jobRole'].')',
            auth()->user()?->fullName() ?? 'System Admin'
        );

        return redirect()->route('account-register.index', ['ok' => 1]);
    }

    public function delete(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string|max:50',
        ]);

        $user = User::where('user_id', $validated['id'])
            ->where('job_role', '!=', 'System Admin')
            ->first();

        if ($user) {
            $user->delete();
            app(AuditService::class)->logAudit(
                'Deleted user '.$validated['id'],
                auth()->user()?->fullName() ?? 'System Admin'
            );
        }

        return redirect()->route('account-register.index');
    }
}
