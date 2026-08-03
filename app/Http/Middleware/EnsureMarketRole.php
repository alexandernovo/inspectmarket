<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMarketRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('portal.login', ['role' => $roles[0] ?? 'tenant']);
        }

        $allowed = collect($roles)->contains(
            fn (string $role) => $user->isRole($role)
        );

        abort_unless($allowed, 403, 'Your account cannot access this portal.');

        return $next($request);
    }
}
