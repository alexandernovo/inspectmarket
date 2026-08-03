<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MarketAccountController extends Controller
{
    public function profile(Request $request)
    {
        return view('market.portal.profile', [
            'pageTitle' => 'Profile',
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'firstname' => ['required', 'string', 'max:100'],
            'middlename' => ['nullable', 'string', 'max:100'],
            'lastname' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:100', Rule::unique('users')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'address' => ['required', 'string', 'max:255'],
            'phone_num' => ['required', 'string', 'max:30', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', 'min:8'],
            'profile_image' => ['nullable', 'image', 'max:4096'],
            'background_image' => ['nullable', 'image', 'max:8192'],
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        foreach (['profile_image' => 'profile', 'background_image' => 'background'] as $input => $column) {
            if ($request->hasFile($input)) {
                if ($user->{$column}) {
                    Storage::disk('public')->delete($user->{$column});
                }
                $data[$column] = $request->file($input)->store("profiles/{$user->id}", 'public');
            }
            unset($data[$input]);
        }

        $user->update($data);

        return back()->with('success', 'Profile updated.');
    }
}
