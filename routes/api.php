<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

Route::get('/__logs', function () {
    $logPath = storage_path('logs/laravel.log');
    if (!file_exists($logPath)) {
        return response()->json(['error' => 'Log file does not exist'], 404);
    }
    return response()->file($logPath);
});

// ✅ Test if backend is alive
Route::get('/ping', function () {
    return response()->json(['status' => 'alive 🟢']);
});

// ✅ Manual user creation for testing (avoids Http::post())
Route::get('/__test-setup-user', function () {
    $email = 'testuser' . rand(1000, 9999) . '@example.com';
    $token = Str::random(40);

    $user = User::create([
        'name' => 'Test User',
        'email' => $email,
        'email_token' => $token,
        'email_verified_at' => now(),
        'password' => Hash::make('Test@1234')
    ]);

    return response()->json([
        'email' => $email,
        'password' => 'Test@1234',
        'token' => $token,
        'message' => 'User created successfully '
    ]);
});

// ✅ Direct login attempt with created user
Route::get('/__test-login', function () {
    $email = request()->query('email');
    $password = request()->query('password');

    $user = User::where('email', $email)->first();
    if (!$user) {
        return response()->json(['error' => 'User not found '], 404);
    }

    if (!Hash::check($password, $user->password)) {
        return response()->json(['error' => 'Invalid password '], 401);
    }

    $token = $user->createToken('test-login')->plainTextToken;

    return response()->json([
        'message' => 'Login successful ',
        'token' => $token,
        'user' => $user->only(['id', 'email', 'name']),
    ]);
});

?>

