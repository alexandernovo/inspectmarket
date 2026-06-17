<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMessageMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    public function viewForgotPassword()
    {
        return view('forgotpassword.views.forgotpassword');
    }

    // Step 1: Send email with code (store in session)
    public function sendForgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Email not found.']);
        }

        // Generate token and 6-digit code
        $token = md5(uniqid() . time());
        $code = rand(100000, 999999);
        $expires = now()->addMinutes(30);

        // Store in session
        session([
            'fp_email' => $user->email,
            'fp_token' => $token,
            'fp_code' => $code,
            'fp_expires' => $expires
        ]);

        // Send email
        $content = "Hello <strong>{$user->firstname}</strong>,<br><br>You requested a password reset.<br>Your verification code is: <strong>{$code}</strong><br><br></a><br><br>Code expires in 30 minutes.
        ";

        Mail::to($user->email)->send(new SendMessageMail($content));

        return response()->json(['status' => true, 'message' => 'Password reset email sent.']);
    }

    // Step 2: Verify code from session
    public function verifyCode(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        if (session('fp_code') != $request->code || now()->gt(session('fp_expires'))) {
            return response()->json(['status' => false, 'message' => 'Invalid or expired code.']);
        }

        return response()->json(['status' => true, 'token' => session('fp_token')]);
    }

    // Step 3: Reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|confirmed|min:6'
        ]);

        if (session('fp_token') != $request->token || now()->gt(session('fp_expires'))) {
            return response()->json(['status' => false, 'message' => 'Invalid or expired token.']);
        }

        $user = User::where('email', session('fp_email'))->first();
        if (!$user) return response()->json(['status' => false, 'message' => 'User not found.']);

        $user->update(['password' => Hash::make($request->password)]);

        // Clear session
        session()->forget(['fp_email', 'fp_token', 'fp_code', 'fp_expires']);

        return response()->json(['status' => true, 'message' => 'Password successfully reset.']);
    }
}
