<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SanctumTokenRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SanctumAuthController extends Controller
{
    /**
     * Issue a new API token for the user.
     *
     * POST /api/sanctum/token
     *
     * Request body:
     * {
     *   "email": "user@example.com",
     *   "password": "password",
     *   "device_name": "iPhone 15"
     * }
     */
    public function issueToken(SanctumTokenRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke previous token for same device (optional - keep only 1 token per device)
        $user->tokens()
            ->where('name', $request->device_name)
            ->delete();

        // Create new token with all abilities
        $token = $user->createToken($request->device_name, ['*'])->plainTextToken;

        return response()->json([
            'message' => 'Token created successfully',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 201);
    }

    /**
     * Revoke the current API token.
     *
     * POST /api/sanctum/revoke
     */
    public function revokeToken(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Token revoked successfully',
        ]);
    }

    /**
     * Revoke a specific API token by ID.
     *
     * DELETE /api/sanctum/tokens/{tokenId}
     */
    public function revokeTokenById(Request $request, int $tokenId): JsonResponse
    {
        $request->user()->tokens()->where('id', $tokenId)->delete();

        return response()->json([
            'message' => 'Token revoked successfully',
        ]);
    }

    /**
     * Get all API tokens for the authenticated user.
     *
     * GET /api/sanctum/tokens
     */
    public function listTokens(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()->select('id', 'name', 'created_at', 'last_used_at')->get();

        return response()->json([
            'tokens' => $tokens,
        ]);
    }

    /**
     * Get the authenticated user with token abilities.
     *
     * GET /api/user
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
            'abilities' => $request->user()->tokenCan('*') ? ['*'] : [],
        ]);
    }
}
