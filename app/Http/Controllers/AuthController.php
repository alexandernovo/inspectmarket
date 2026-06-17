<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->status != "ACTIVE") {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This Account is not yet activated, Please contact the administrator.',
                ]);
            }
            if ($user->usertype == "ADMIN" && $request->typeLogin == "STAFF") {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This is an Admin account. Please log in through the Admin Portal.',
                ]);
            } else if ($user->usertype == "STAFF" && $request->typeLogin == "ADMIN") {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This is a Staff account. Please log in through the Staff Portal.',
                ]);
            }

            $request->session()->regenerate();

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful.',
                'redirect' => route('dashboard')
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid email or password.',
        ]);
    }

    public function logout(Request $request)
    {
        $request->session()->flush();

        return response()->json([
            'status' => 'success',
        ]);
    }
}
