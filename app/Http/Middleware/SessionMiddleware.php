<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;

class SessionMiddleWare
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::user() || !Auth::user()->id) {
            if ($request->isMethod('get')) {
                return redirect('/');
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated Access',
                ], 200);
            }
        }

        return $next($request);
    }
}
