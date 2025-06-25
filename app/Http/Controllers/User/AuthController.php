<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames()->toArray()
            ]
        ]);
    }

    public function autoLogin($uuid)
    {
        Log::info('Attempting auto login with UUID: ' . $uuid);

        try {
            $user = User::where('name', $uuid)->first();

            if (!$user) {
                Log::error('User not found for UUID: ' . $uuid);
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            if (!$user->hasRole('HR')) {
                Log::error('User does not have HR role. UUID: ' . $uuid);
                return response()->json([
                    'message' => 'Access denied'
                ], 403);
            }

            $user->tokens()->delete();

            $token = $user->createToken('auth-token')->plainTextToken;

            Log::info('Auto login successful for user: ' . $user->email);

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames()->toArray()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error during auto login: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error during auto login: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
