<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalAuthController extends Controller
{
    public function create(string $role)
    {
        $role = strtoupper($role);

        abort_unless(in_array($role, $this->roles(), true), 404);

        return view('market.auth.login', compact('role'));
    }

    public function store(Request $request, string $role)
    {
        $role = strtoupper($role);

        abort_unless(in_array($role, $this->roles(), true), 404);

        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return $this->redirectToRoleLogin($role, [
                'login_'.strtolower($role) => 'Username or password is invalid.',
            ])->onlyInput('username');
        }

        $request->session()->regenerate();
        $user = $request->user();

        if (! $user->isRole($role)) {
            Auth::logout();

            return $this->redirectToRoleLogin($role, [
                'login_'.strtolower($role) => "This account belongs to the {$user->role_slug} portal.",
            ])->onlyInput('username');
        }

        if (strtoupper((string) $user->status) !== 'ACTIVE') {
            Auth::logout();

            return $this->redirectToRoleLogin($role, [
                'login_'.strtolower($role) => 'This account is not active. Contact the administrator.',
            ]);
        }

        $user->update(['last_login_at' => now()]);

        return redirect()->intended(route("{$user->role_slug}.dashboard"));
    }

    public function redirect(Request $request)
    {
        $user = $request->user();

        return redirect()->route("{$user->role_slug}.dashboard");
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function roles(): array
    {
        return [
            User::ROLE_ADMINISTRATOR,
            User::ROLE_TREASURER,
            User::ROLE_CLERK,
            User::ROLE_INSPECTOR,
            User::ROLE_TENANT,
        ];
    }

    private function redirectToRoleLogin(string $role, array $errors)
    {
        $slug = strtolower($role);

        return redirect()->to(route('public.roles', ['mode' => 'login']).'#login-'.$slug)
            ->withErrors($errors);
    }
}
