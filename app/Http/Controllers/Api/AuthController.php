<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    // ? Register
    public function register(Request $request)
    {
        // Validate input
        $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|email|unique:users,email",
            "password" => "required|string|min:6|confirmed",
            "role_id" => "required|exists:roles,id", // mandatory role
        ]);

        // Create user
        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "role_id" => $request->role_id,
        ]);

        // Create API token using Sanctum
        $token = $user->createToken("auth_token")->plainTextToken;

        // Return response
        return response()->json([
            "message" => "User registered successfully",
            "access_token" => $token,
            "token_type" => "Bearer",
            "user" => $user
        ], 201);
    }

    // ? Login
    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "password" => "required",
        ]);

        $user = User::where("email", $request->email)->first();

        // Check user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                "message" => "Invalid credentials"
            ], 401);
        }

        // Create token
        $token = $user->createToken("auth_token")->plainTextToken;

        return response()->json([
            "message" => "Login successful",
            "user" => $user,
            "token" => $token
        ]);
    }

    // ? Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            "message" => "Logged out successfully"
        ]);
    }

    /**
     * Get user profile.
     */
    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            "success" => true,
            "user" => $request->user(),
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            "name" => "required|string|max:255",
            "email" => ["required", "email", "max:255", Rule::unique("users")->ignore($user->id)],
            "password" => "nullable|string|min:6|confirmed",
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled("password")) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            "success" => true,
            "message" => "Profile updated successfully.",
            "user" => $user,
        ]);
    }
}
