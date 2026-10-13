<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AuthApiController extends Controller
{
    public function __construct()
    {
        $this->ensureSanctumTableExists();
    }

    /**
     * Defensively ensure personal_access_tokens table exists for Bearer tokens.
     */
    private function ensureSanctumTableExists(): void
    {
        try {
            if (!Schema::hasTable('personal_access_tokens')) {
                Schema::create('personal_access_tokens', function (Blueprint $table) {
                    $table->id();
                    $table->morphs('tokenable');
                    $table->string('name');
                    $table->string('token', 64)->unique();
                    $table->text('abilities')->nullable();
                    $table->timestamp('last_used_at')->nullable();
                    $table->timestamp('expires_at')->nullable();
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            // Ignore in constructor
        }
    }

    /**
     * POST /api/v1/auth/login
     * Authenticate Dairy Admin (or staff) and issue a Sanctum Bearer Token.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'login' => 'nullable|string',
            'email' => 'nullable|string',
            'phone' => 'nullable|string',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ]);

        $loginIdentifier = trim($validated['login'] ?? $validated['email'] ?? $validated['phone'] ?? '');
        if ($loginIdentifier === '') {
            return response()->json([
                'success' => false,
                'message' => 'Please provide login (email or phone) and password.',
            ], 422);
        }

        $user = User::where('email', $loginIdentifier)
            ->orWhere('phone', $loginIdentifier)
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials provided.',
            ], 401);
        }

        if (($user->status ?? 'active') !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is currently inactive or suspended.',
            ], 403);
        }

        $deviceName = $validated['device_name'] ?? 'DairyAdmin-API';
        $abilities = $user->isDairyAdmin() ? ['*'] : ['procurement:read', 'procurement:write'];
        $token = $user->createToken($deviceName, $abilities)->plainTextToken;

        AuditLog::log('API Bearer Token Issued', 'User', $user->id, [
            'device_name' => $deviceName,
            'role' => $user->role,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Authenticated successfully.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'is_dairy_admin' => $user->isDairyAdmin(),
                'branch_id' => $user->branch_id,
                'status' => $user->status,
            ],
        ]);
    }

    /**
     * GET /api/v1/auth/me
     * Return currently authenticated Dairy Admin user profile.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'is_dairy_admin' => $user->isDairyAdmin(),
                'branch_id' => $user->branch_id,
                'status' => $user->status,
                'permissions' => $user->permissions ?? [],
            ],
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     * Revoke current Bearer token (or all tokens if all_devices=true).
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($request->boolean('all_devices')) {
            $user->tokens()->delete();
        } else {
            $user->currentAccessToken()?->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out and Bearer token revoked successfully.',
        ]);
    }
}
