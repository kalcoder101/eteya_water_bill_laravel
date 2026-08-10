<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserAccountDataController extends Controller
{
    /**
     * Authenticate user via credentials and return user info + Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $body = $request->json()->all();
        $username = trim($body['userName'] ?? $body['username'] ?? $request->input('user_name', ''));
        $password = (string) ($body['userPassword'] ?? $body['password'] ?? $request->input('user_password', ''));

        if (empty($username) || empty($password)) {
            return response()->json(['error' => 'Username and password are required'], 200);
        }

        $user = User::where('user_name', $username)->first();
        if (! $user && in_array(strtolower($username), ['admin', 'levinull'], true)) {
            $user = User::whereIn('user_name', ['admin', 'levinull'])->first();
        }

        if (! $user) {
            return response()->json(['error' => 'Invalid credentials'], 200);
        }

        $valid = false;
        if ($user->passwordIsHashed()) {
            $valid = Hash::check($password, $user->user_password);
        } else {
            if ($password === $user->user_password) {
                $valid = true;
                // Transparently re-hash plain-text password
                $user->user_password = Hash::make($password);
                $user->save();
            }
        }

        if (! $valid) {
            return response()->json(['error' => 'Invalid credentials'], 200);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token'    => $token,
            'userId'   => $user->user_id,
            'fullName' => trim($user->first_name.' '.($user->last_name ?? '')),
            'jobRole'  => $user->job_role,
        ]);
    }

    public function getPhoto(Request $request, ?string $userId = null)
    {
        $user = User::where('user_id', $userId)->first();
        if (! $user || ! $user->photo) {
            return response('', 404);
        }

        return response($user->photo, 200, ['Content-Type' => 'image/jpeg']);
    }

    public function getUserAccount(?string $userId = null): JsonResponse
    {
        return response()->json(User::where('user_id', $userId)->first());
    }

    public function getNameByJobRole(Request $request): Response
    {
        $jr = $request->input('jobRole', $request->query('jobRole', ''));
        $user = User::where('job_role', $jr)->first();

        return response($user ? trim($user->first_name.' '.($user->last_name ?? '')) : "Account Doesn't Exist", 200, ['Content-Type' => 'text/plain']);
    }

    public function getNameByCredentials(Request $request): Response
    {
        $u = $request->input('userName', $request->query('userName', ''));
        $p = (string) $request->input('userPassword', $request->query('userPassword', ''));

        $user = User::where('user_name', $u)->first();

        $valid = false;
        if ($user) {
            if ($user->passwordIsHashed()) {
                $valid = Hash::check($p, $user->user_password);
            } else {
                $valid = ($p === $user->user_password);
            }
        }

        return response($valid ? trim($user->first_name.' '.($user->last_name ?? '')) : "Account Doesn't Exist", 200, ['Content-Type' => 'text/plain']);
    }

    public function checkUserPassword(Request $request): Response
    {
        $password = (string) ($request->input('password') ?? $request->input('userPassword') ?? $request->query('password', ''));
        $jobRole  = $request->input('jobRole') ?? $request->input('job_role') ?? $request->query('jobRole', '');

        if (empty($password) || empty($jobRole)) {
            return response('false', 200, ['Content-Type' => 'text/plain']);
        }

        $users = User::where('job_role', $jobRole)->get();
        $found = false;

        foreach ($users as $user) {
            if ($user->passwordIsHashed()) {
                if (Hash::check($password, $user->user_password)) {
                    $found = true;
                    break;
                }
            } else {
                if ($password === $user->user_password) {
                    $found = true;
                    break;
                }
            }
        }

        return response($found ? 'true' : 'false', 200, ['Content-Type' => 'text/plain']);
    }

    public function updateUserAccount(Request $request, ?string $userId = null): JsonResponse
    {
        $authUser = Auth::guard('sanctum')->user() ?? Auth::user() ?? $request->user();
        if (! $authUser || ! in_array($authUser->job_role, ['System Admin', 'Admin', 'Manager'], true)) {
            return response()->json(['error' => 'Unauthorized. Admin role required.'], 403);
        }

        $body = $request->json()->all();
        $photo = null;
        if (! empty($body['photo'])) {
            $data = $body['photo'];
            if (str_starts_with($data, 'data:')) {
                $data = substr($data, strpos($data, ',') + 1);
            }
            $photo = base64_decode($data);
        }

        $existing = User::where('user_id', $userId)->first();
        $payload = [
            'first_name'   => $body['firstName']    ?? null,
            'last_name'    => $body['lastName']     ?? null,
            'phone_number' => $body['phoneNumber']  ?? null,
            'email_id'     => $body['emailId']      ?? null,
            'job_role'     => $body['jobRole']      ?? null,
            'user_name'    => $body['userName']     ?? null,
        ];

        if (! empty($body['userPassword'])) {
            $payload['user_password'] = Hash::make($body['userPassword']);
        }

        if ($photo !== null) {
            $payload['photo'] = $photo;
        }

        if ($existing) {
            $existing->update(array_filter($payload, fn ($v) => $v !== null) + ['photo' => $photo ?? $existing->photo]);
        } else {
            if (empty($payload['user_password'])) {
                return response()->json(['error' => 'Password is required for new accounts.'], 422);
            }
            User::create(array_merge(['user_id' => $userId], $payload));
        }

        return response()->json(['status' => 'created'], 201);
    }
}
