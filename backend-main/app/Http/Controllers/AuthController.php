<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:8',
            'id_role' => 'required|exists:roles,id_role', 
        ]);

        $user = User::create([
            'username' => $validatedData['username'],
            'password' => Hash::make($validatedData['password']),
            'id_role' => $validatedData['id_role'], 
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => [
                'id_user' => $user->id_user,
                'username' => $user->username,
                'role' => $user->role->nama_role, 
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::with(['role', 'desa'])->where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials',
                'errors' => [
                    'username' => ['The username or password is incorrect.'],
                ]
            ], 401);
        }

        $user->update([
            'is_online' => true,
            'last_login_at' => now(),
        ]);

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        $responseData = [
            'status' => 'success',
            'message' => 'User logged in successfully',
            'data' => [
                'id_user' => $user->id_user,
                'username' => $user->username,
                'role' => $user->role->nama_role,
                'token' => $token,
            ]
        ];

        if ($user->id_desa) {
            $responseData['data']['id_desa'] = $user->id_desa;
            
            if ($user->desa) {
                $responseData['data']['nama_desa'] = $user->desa->nama_desa;
            }
        }

        return response()->json($responseData, 200);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No authenticated user found'
            ], 401);
        }

        $user->update([
            'is_online' => false,
        ]);

        $user->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User logged out successfully'
        ], 200);
    }
}
