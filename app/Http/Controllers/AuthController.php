<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Register user and send email with token
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'dob' => 'required|date',
            'languages' => 'required',
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
        ]);

        $token = Str::random(64);

        $user = User::create([
            ...$validated,
            'email_verified' => false,
            'email_token' => $token,
        ]);

        Mail::send('emails.verify', ['token' => $token], function ($message) use ($request) {
            $message->to($request->email)->subject('Verify your email');
        });

        return response()->json(['message' => 'Registration successful. Check your email to set password.']);
    }

    // Verify email and show frontend form via link
    public function verifyEmail($token)
    {
        $user = User::where('email_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid or expired token'], 400);
        }

        return redirect("http://localhost:3000/set-password/$token");
    }

    // Set password after email link
    public function setPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('email_token', $request->token)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 400);
        }

        $user->password = Hash::make($request->password);
        $user->email_verified = true;
        $user->email_token = null;
        $user->save();

        return response()->json(['message' => 'Password set successfully. You can now log in.']);
    }

    // Login
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->email_verified) {
            return response()->json(['message' => 'Please verify your email first'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json(['token' => $token]);
    }

    // Send OTP to email for reset password
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) return response()->json(['message' => 'Email not found'], 404);

        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->save();

        Mail::send('emails.otp', ['otp' => $otp], function ($message) use ($request) {
            $message->to($request->email)->subject('OTP for Reset Password');
        });

        return response()->json(['message' => 'OTP sent to your email']);
    }

    // Reset password using OTP
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('email', $request->email)->where('otp', $request->otp)->first();

        if (!$user) return response()->json(['message' => 'Invalid OTP'], 400);

        $user->password = Hash::make($request->password);
        $user->otp = null;
        $user->save();

        return response()->json(['message' => 'Password reset successful']);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
?>