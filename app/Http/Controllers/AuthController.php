<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str; // ✅ Required for group_code generation

class AuthController extends Controller
{
    // 🔐 Register
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|min:5',
            'email'             => 'required|email|unique:users',
            'password'          => 'required|string|min:6',
            'relationship_type' => 'required|string',
            'group_code'        => 'nullable|string',
        ]);

        // ✅ Auto-generate group_code if not provided
        $groupCode = $validated['group_code'] ?? strtoupper(Str::random(6));

        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password'          => bcrypt($validated['password']), // ✅ hash password
            'relationship_type' => $validated['relationship_type'],
            'group_code'        => $groupCode,
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    // 🔑 Login
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid email'], 401);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Incorrect password'], 401);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    // 👤 Get logged-in user
    public function me()
    {
        return response()->json(auth()->user());
    }

    // 🚪 Logout
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }
}
