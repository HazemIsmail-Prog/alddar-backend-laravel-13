<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController
{
    public function login(Request $request)
    {

        $request->validate([
            'civil_id' => 'required|numeric|digits:12',
            'password' => 'required',
        ]);

        $user = User::where('civil_id', $request->civil_id)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json($token);
    }

    public function getUser(Request $request)
    {
        // clone the user and append the can attribute
        $user = $request->user()->fresh()->toArray();
        $can = array_values((array) $request->user()->can);
        $user['can'] = $can;
        return response()->json($user);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
