<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request){

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Incorrect email or password. Please try again.',
                'errors' => [
                    'email' => ['Incorrect email or password. Please try again.']
                ]
            ], 401);
        }

        // Log::info("User logged in: " . $user->email);

        return response()->json([
            "token" => $user->createToken($request->device_name)->plainTextToken
        ], 201);
    }

    public function register(RegisterRequest $request){

        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "device_name" => $request->device_name,
        ]);

        return response()->json([
            "message" => "Registration successful.",
            "token" => $user->createToken($request->device_name)->plainTextToken
        ], 201);
    }

    public function logout(Request $request) {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Successfully logged out.']);
        }

        return response()->json(['error' => 'You are not logged in.'], 401);
    }

    public function verifyToken(Request $request){
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name
        ]);
    }
}
