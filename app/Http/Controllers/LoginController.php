<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    public function register(Request $request)
    {
        $userData = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone' => 'required|string|digits:10',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|min:8',
        ]);

        User::create([
            'first_name' => $userData['first_name'],
            'last_name' => $userData['last_name'],
            'phone' => $userData['phone'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
        ]);

        return response()->json(['message' => 'Welcome '.$userData['first_name'].' '.$userData['last_name']]);
    }

    public function login(Request $request)
    {
        $userData = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|min:8',
        ]);

        if (!Auth::attempt($userData)) {
            return response()->json(['message' => 'Invalid email or password'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'message' => 'Login Successful',
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
