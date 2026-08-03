<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function register(Request $request, string $role)
    {
        $role = $this->validRole($role);

        return view('market.auth.account', [
            'mode' => 'register',
            'step' => 'phone',
            'role' => $role,
        ]);
    }

    public function sendRegistrationCode(Request $request, string $role)
    {
        $role = $this->validRole($role);
        $data = $request->validate([
            'phone_num' => ['required', 'string', 'max:30', 'unique:users,phone_num'],
        ]);

        $code = $this->issueCode($data['phone_num'], 'REGISTER');
        $request->session()->put([
            'account.phone' => $data['phone_num'],
            'account.role' => $role,
            'account.purpose' => 'REGISTER',
            'account.debug_code' => $code,
        ]);

        return redirect()->route('account.verify.form')
            ->with('success', 'Verification code sent. In local development, use the code shown below.');
    }

    public function verifyForm(Request $request)
    {
        abort_unless($request->session()->has('account.phone'), 419);

        return view('market.auth.account', [
            'mode' => strtolower($request->session()->get('account.purpose')),
            'step' => 'verify',
            'role' => $request->session()->get('account.role', 'TENANT'),
            'phone' => $request->session()->get('account.phone'),
            'debugCode' => $request->session()->get('account.debug_code'),
        ]);
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $phone = $request->session()->get('account.phone');
        $purpose = $request->session()->get('account.purpose');

        $verification = VerificationCode::query()
            ->where('phone_num', $phone)
            ->where('purpose', $purpose)
            ->where('code', $data['code'])
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $verification) {
            return back()->withErrors(['code' => 'The verification code is invalid or expired.']);
        }

        $verification->update(['used_at' => now()]);
        $request->session()->put('account.verified', true);

        return redirect()->route(
            $purpose === 'REGISTER' ? 'account.password.form' : 'account.reset.form'
        );
    }

    public function passwordForm(Request $request)
    {
        abort_unless($request->session()->get('account.verified') && $request->session()->get('account.purpose') === 'REGISTER', 419);

        return view('market.auth.account', [
            'mode' => 'register',
            'step' => 'password',
            'role' => $request->session()->get('account.role'),
            'phone' => $request->session()->get('account.phone'),
        ]);
    }

    public function createAccount(Request $request)
    {
        abort_unless($request->session()->get('account.verified') && $request->session()->get('account.purpose') === 'REGISTER', 419);

        $data = $request->validate([
            'firstname' => ['nullable', 'string', 'max:100'],
            'middlename' => ['nullable', 'string', 'max:100'],
            'lastname' => ['nullable', 'string', 'max:100'],
            'username' => ['nullable', 'string', 'max:100', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'address' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $role = $request->session()->get('account.role');
        $phone = $request->session()->get('account.phone');
        $user = User::create([
            ...$data,
            'firstname' => $data['firstname'] ?? ucfirst(strtolower($role)),
            'lastname' => $data['lastname'] ?? 'User',
            'username' => $data['username'] ?? strtolower($role).preg_replace('/\D+/', '', $phone),
            'address' => $data['address'] ?? 'Pandan, Antique',
            'phone_num' => $phone,
            'designation' => ucfirst(strtolower($role)),
            'usertype' => $role,
            'status' => $role === User::ROLE_TENANT ? 'ACTIVE' : 'INACTIVE',
            'phone_verified_at' => now(),
        ]);

        $request->session()->forget('account');

        if ($user->status === 'ACTIVE') {
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('tenant.dashboard')->with('success', 'Your tenant account is ready.');
        }

        return redirect()->route('portal.login', ['role' => strtolower($role)])
            ->with('success', 'Account created. An administrator must activate this staff account.');
    }

    public function forgot()
    {
        return view('market.auth.account', [
            'mode' => 'reset',
            'step' => 'phone',
            'role' => 'TENANT',
        ]);
    }

    public function sendResetCode(Request $request)
    {
        $data = $request->validate([
            'phone_num' => ['required', 'string', 'exists:users,phone_num'],
        ]);

        $code = $this->issueCode($data['phone_num'], 'RESET');
        $request->session()->put([
            'account.phone' => $data['phone_num'],
            'account.purpose' => 'RESET',
            'account.debug_code' => $code,
        ]);

        return redirect()->route('account.verify.form')
            ->with('success', 'Password reset code sent.');
    }

    public function resetForm(Request $request)
    {
        abort_unless($request->session()->get('account.verified') && $request->session()->get('account.purpose') === 'RESET', 419);

        return view('market.auth.account', [
            'mode' => 'reset',
            'step' => 'password',
            'role' => 'TENANT',
            'phone' => $request->session()->get('account.phone'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        abort_unless($request->session()->get('account.verified') && $request->session()->get('account.purpose') === 'RESET', 419);

        $data = $request->validate([
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::where('phone_num', $request->session()->get('account.phone'))->firstOrFail();
        $user->update(['password' => $data['password']]);
        $request->session()->forget('account');

        return redirect()->route('portal.login', ['role' => $user->role_slug])
            ->with('success', 'Password reset successfully.');
    }

    private function issueCode(string $phone, string $purpose): string
    {
        VerificationCode::where('phone_num', $phone)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $code = (string) random_int(100000, 999999);

        VerificationCode::create([
            'phone_num' => $phone,
            'code' => $code,
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(10),
        ]);

        return $code;
    }

    private function validRole(string $role): string
    {
        $role = strtoupper($role);

        validator(['role' => $role], [
            'role' => [Rule::in([
                User::ROLE_ADMINISTRATOR,
                User::ROLE_TREASURER,
                User::ROLE_CLERK,
                User::ROLE_INSPECTOR,
                User::ROLE_TENANT,
            ])],
        ])->validate();

        return $role;
    }
}
