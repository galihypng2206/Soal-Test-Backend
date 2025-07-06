<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

// REGISTER
Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6',
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => bcrypt($validated['password']),
    ]);

    return response()->json(['message' => 'User registered successfully'], 201);
});

// LOGIN
Route::post('/login', function (Request $request) {
    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Login success',
        'token' => $token,
    ]);
});

// ENDPOINT TERPROTEKSI TOKEN
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json($request->user());
});

use App\Http\Controllers\Api\EpresenceController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/epresence', [EpresenceController::class, 'store']);
    Route::get('/epresence', [EpresenceController::class, 'index']);
    Route::post('/epresence/{id}/approve', [EpresenceController::class, 'approve']);
});

use App\Http\Controllers\Api\RegisterController;

Route::post('/register', [RegisterController::class, 'register']);