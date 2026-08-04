<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class UserAccountDataController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $body = $request->json()->all();

        $user = User::where('user_name', $body['userName'] ?? '')
            ->where('user_password', $body['userPassword'] ?? '')
            ->first();

        if (! $user) {
            return response()->json(['error' => 'Invalid credentials'], 200);
        }

        return response()->json([
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
        $jr = $request->query('jobRole', '');
        $user = User::where('job_role', $jr)->first();

        return response($user ? trim($user->first_name.' '.($user->last_name ?? '')) : "Account Doesn't Exist", 200, ['Content-Type' => 'text/plain']);
    }

    public function getNameByCredentials(Request $request): Response
    {
        $u = $request->query('userName', '');
        $p = $request->query('userPassword', '');
        $user = User::where('user_name', $u)->where('user_password', $p)->first();

        return response($user ? trim($user->first_name.' '.($user->last_name ?? '')) : "Account Doesn't Exist", 200, ['Content-Type' => 'text/plain']);
    }

    public function checkUserPassword(Request $request, ?string $password = null): Response
    {
        $jr = $request->segment(5) ?? '';
        $count = User::where('user_password', $password ?? '')
            ->where('job_role', $jr)
            ->count();

        return response($count > 0 ? 'true' : 'false', 200, ['Content-Type' => 'text/plain']);
    }

    public function updateUserAccount(Request $request, ?string $userId = null): JsonResponse
    {
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
            'first_name'    => $body['firstName']      ?? null,
            'last_name'     => $body['lastName']       ?? null,
            'phone_number'  => $body['phoneNumber']    ?? null,
            'email_id'      => $body['emailId']        ?? null,
            'job_role'      => $body['jobRole']        ?? null,
            'user_name'     => $body['userName']       ?? null,
            'user_password' => $body['userPassword']   ?? null,
        ];
        if ($photo !== null) {
            $payload['photo'] = $photo;
        }

        if ($existing) {
            $existing->update(array_filter($payload, fn ($v) => $v !== null) + ['photo' => $photo ?? $existing->photo]);
        } else {
            User::create(array_merge(['user_id' => $userId], $payload));
        }

        return response()->json(['status' => 'created'], 201);
    }
}
