<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Main API Routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail']);
Route::post('/set-password', [AuthController::class, 'setPassword']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'sendOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class);
    Route::post('/logout', [AuthController::class, 'logout']);
});

/*
|--------------------------------------------------------------------------
| Temporary Dev/Test Routes (Remove after verification)
|--------------------------------------------------------------------------
*/

// ✅ Setup test user, simulate full flow: register, verify, set password
Route::get('/__test-setup-user', function () {
    $email = 'testuser' . rand(1000, 9999) . '@example.com';

    // 1. Register
    $registerResponse = Http::post(url('/api/register'), [
        'name' => 'Test User',
        'email' => $email
    ]);

    // 2. Get token from DB
    $user = User::where('email', $email)->first();
    if (!$user) return response()->json(['error' => 'User not created'], 500);

    $token = $user->email_token;

    // 3. Verify
    Http::get(url("/api/verify-email/{$token}"));

    // 4. Set password
    $password = 'Test@1234';
    Http::post(url('/api/set-password'), [
        'email' => $email,
        'password' => $password,
        'password_confirmation' => $password
    ]);

    return response()->json([
        'email' => $email,
        'password' => $password,
        'message' => 'Test user registered and password set successfully ✅'
    ]);
});

// ✅ Login test user via browser
Route::get('/__test-login', function () {
    $email = request()->query('email');
    $password = request()->query('password');

    $res = Http::post(url('/api/login'), [
        'email' => $email,
        'password' => $password
    ]);

    return $res->json();
});

// ✅ Test OTP send
Route::get('/__test-forgot', function () {
    $email = request()->query('email');

    $res = Http::post(url('/api/forgot-password'), [
        'email' => $email
    ]);

    return $res->json();
});
