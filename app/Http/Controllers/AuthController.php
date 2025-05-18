<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'lastName' => 'required|string|max:255',
            'firstName' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'last_name' => $validatedData['lastName'],
            'first_name' => $validatedData['firstName'],
            'email' => $validatedData['email'],
            'role' => $validatedData['role'] ?? 'user',
            'password' => Hash::make($validatedData['password']),
        ]);

        $customClaims = ['role' => $user->role, 'firstName' => $user->first_name, 'lastName' => $user->last_name];
        $token = JWTAuth::fromUser($user, $customClaims);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            // 'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
        ]);

        // Check if the user is already logged in
        if (Auth::check()) {
            return response()->json(['message' => 'User already logged in'], 200);
        }

        Log::info('Login attempt', ['email' => $credentials['email'], 'password' => $credentials['password'], Auth::attempt($credentials)]);

        // Tente l'authentification
        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Récupère l'utilisateur authentifié
        $user = Auth::user();
        $customClaims = ['role' => $user->role, 'firstName' => $user->first_name, 'lastName' => $user->last_name];
        $token = JWTAuth::fromUser($user, $customClaims);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 200);
    }

    public function me()
    {
        return response()->json(Auth::user());
    }
}
